<?php

include_once PPTEC_PLUGIN_PATH . 'inc/Exceptions/EventFormValidationException.php';
include_once PPTEC_PLUGIN_PATH . 'inc/Exceptions/VenueFormValidationException.php';
use PurplePass\Exceptions\EventFormValidationException;
use PurplePass\Exceptions\VenueFormValidationException;

/**
 * Main Application Class
 *
 * Class Purplepass_ECP
 */
class Purplepass_ECP {
	function __construct()
	{
		if ( false === wp_doing_ajax() ) {
			add_action( 'pptec_job_daily_actions', array( $this, 'pptec_daily_sync_events' ) );
		}

        add_action('wp_ajax_pptec_wp_event_form_validate_and_save', array($this, 'pptec_jx_event_validate_and_save'));
        add_action('wp_ajax_pptec_wp_venue_form_validate_and_save', array($this, 'pptec_jx_venue_validate_and_save'));
    }

	/**
	 * Create one Log row in DB
	 *
	 * @param $time
	 * @param $action
	 * @param $direction
	 * @param $details
	 * @param $status
	 */
	public function pptec_add_log( $time, $action, $direction, $details, $status ) {

		global $wpdb;
		$prefix = $wpdb->prefix;

		$wpdb->insert(
			$prefix . "pp_logs",
			array(
				'event_time' => $time,
				'event_action'   => $action,
				'event_direction'   => $direction,
				'event_details'   => $details,
				'event_status'   => $status,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
        );
    }

	/**
	 * Daily check whether anything went wrong during user-initialized fetch.
     * On fetch initialization, we add all entries to `pp_sync_events` table. After event is processed on WP side, it is deleted from that table.
     * If we still have entries in `pp_sync_events`, that means something went wrong (IC lost, server down etc.).
     * So we retry corresponding action for each of those events.
	 */
	public function pptec_daily_sync_events() {

		global $wpdb;

		// add notification to log system
		$this->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Daily sync', 'Inbound', 'Daily sync was initiated, receiving all events from Purplepass website', 'Successful' );

		$results = $wpdb->get_results( "SELECT * FROM  {$wpdb->prefix}pp_sync_events" );

		foreach( $results as $key => $item ) {
            $pp_user_id = pptec_get_pp_user_id_by_pp_event_id($item->event_id);
            if (!$pp_user_id) {
                continue;
            }

            $wp_user_id = pptec_get_wp_user_id_by_pp_user_id($pp_user_id);
            if (!$wp_user_id) {
                continue;
            }

			switch ( $item->action ) {
				case 'event_add':
				case 'event_update' :
					$this->pptec_create_update_event($wp_user_id, $item->event_id);
					break;

				case 'event_delete':
					$this->pptec_delete_event($item->event_id);
					break;

				case 'event_cancel':
					$this->pptec_cancel_event($item->event_id);
					break;
			}
		}
	}

	/**
	 * Generating random string
	 *
	 * @return string
	 */
	public function pptec_random_string() {
		$random_string = "";
		$valid_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789._~";
		$num_valid_chars = strlen($valid_chars);
		for ($i = 0; $i < 100; $i++) {
			$random_pick = mt_rand(1, $num_valid_chars);
			$random_char = $valid_chars[$random_pick-1];
			$random_string .= $random_char;
		}

		update_option( 'pptec_random_str', $random_string );

		return $random_string;
	}

	/**
	 * Create Auth PP request
	 */
	public function pptec_create_auth_request() {

		$client_id = 'WordPressECP';
		$code_verifier = $this->pptec_random_string();
		$code_challenge = strtr(rtrim(base64_encode(hash('sha256', $code_verifier, true)), '='), '+/', '-_');
		if ( !empty( $code_challenge ) ) {
			$scope         = 'events:read events:write events:webhooks stats';
			$redirect_uri  = get_site_url() . '/?event_pp_listener=true';
			// Authenticate Check and Redirect
			if ( ! isset( $_GET['code'] ) ) {
				$params = array(
					'oauth'          => 2,
					'response_type'  => 'code',
					'scope'          => $scope,
					'client_id'      => $client_id,
					'code_challenge' => $code_challenge,
					'redirect_uri'   => $redirect_uri,
				);
				$params = http_build_query( $params );
				wp_redirect( PPTEC_WEB_URL . '/?' . $params );
				exit;
			}
		}
    }

    public function pptec_jx_venue_validate_and_save() {
        $json = array();

        try {
            $form_data = array();
            if (!empty($_POST['form_data'])) {
                parse_str($_POST['form_data'], $form_data);
            }

            // Backend validation
            $errors = $this->pptec_validate_venue_form($form_data);
            if (!empty($errors)) {
                throw new VenueFormValidationException("", 0, null, $errors);
            }

            $pp_venue_id = pptec_get_pp_venue_id_by_wp_venue_id($form_data['post_ID']);
            if (!$pp_venue_id) {
                // This venue is not attached to any PP venue. Assume validation is successful, we won't send data to PP
                echo json_encode(array('success' => true));
                die;
            }

            // Detect timezone by zip
            //include_once PPTEC_PLUGIN_PATH . 'inc/zips.php';
            //$timezone_id = isset($pptec_zip_data[$form_data['venue']['Zip']]) ? $pptec_zip_data[$form_data['venue']['Zip']] : 0;

            // Detect timezone by related event
            global $wpdb;
            $results = $wpdb->get_results( "SELECT wp_event_id FROM  {$wpdb->prefix}pptec_events WHERE wp_venue_id = " . $form_data['post_ID'] . " ORDER BY wp_event_id DESC LIMIT 1" );
            $wp_event_id = isset($results[0]->wp_event_id) ? (int)$results[0]->wp_event_id : 0;
            if (!$wp_event_id) {
                // This venue is not attached to any event. Assume validation is successful, we won't send data to PP
                echo json_encode(array('success' => true));
                die;
            }

            $pp_event_metadata = get_post_meta($wp_event_id, 'pptec_event_meta_data', true);
            $timezone_id = isset($pp_event_metadata->data['timezone']) ? $pp_event_metadata->data['timezone'] : false;
            if (false === $timezone_id) {
                // Could not detect timezone. Assume validation is successful, we won't send data to PP
                echo json_encode(array('success' => true));
                die;
            }

            $state_id = -1;
            if ('United States' === $form_data['venue']['Country'] && isset($form_data['venue']['State'])) {
                $all_states_list = array_flip(pptec_get_us_states_list(true, true));
                $state_id = isset($all_states_list[$form_data['venue']['State']]) ? $all_states_list[$form_data['venue']['State']] : -1;
            } elseif ('Canada' === $form_data['venue']['Country'] && isset($form_data['venue']['Province'])) {
                $all_states_list = array_flip(pptec_get_canada_provinces_list(true));
                $state_id = isset($all_states_list[$form_data['venue']['Province']]) ? $all_states_list[$form_data['venue']['Province']] : -1;
            }

            $all_countries_list = array_flip(pptec_get_countries_list(true));
            $country_id = isset($all_countries_list[$form_data['venue']['Country']]) ? $all_countries_list[$form_data['venue']['Country']] : 1;

            $pp_venue_data = array(
                'timezone' => (int)$timezone_id,
                'name' => $form_data['post_title'],
                'addr' => $form_data['venue']['Address'],
                'city' => $form_data['venue']['City'],
                'state' => (int)$state_id,
                'zip' => $form_data['venue']['Zip'],
                'country' => (int)$country_id
            );

            list($pp_venue_updated, $pp_error) = $this->pptec_send_venue_to_pp($pp_venue_data, $pp_venue_id);

            if (!$pp_venue_updated && !empty($pp_error)) {
                $errors = array();

                $error_message_obj = json_decode($pp_error['message']);

                if (is_object($error_message_obj)) {
                    $error_message_arr = (array)$error_message_obj;
                    foreach ($error_message_arr as $err_id => $err_message) {
                        $errors[] = array(
                            'selector' => '.' . $err_id,
                            'message' => sanitize_text_field($err_message)
                        );
                    }
                } else {
                    $errors = array($pp_error);
                }

                throw new VenueFormValidationException("", 0, null, $errors);
            }

            $json['success'] = true;
        } catch (VenueFormValidationException $e) {
            $json['errors'] = $e->getErrors();
        }

        echo json_encode($json);
        die;
    }

	public function pptec_jx_event_validate_and_save()
    {
        $json = array();

        try {
            if (!current_user_can('edit_others_posts')) {
                throw new EventFormValidationException("", 0, null, array(
                    array('selector' => 'generic', 'message' => 'You have no permission to edit this event.')
                ));
            }

            $form_data = array();
            if (!empty($_POST['form_data'])) {
                parse_str($_POST['form_data'], $form_data);
            }

            if (empty($form_data)) {
                throw new EventFormValidationException("", 0, null, array(
                    array('selector' => 'generic', 'message' => 'Unable to retrieve form data.')
                ));
            }

            // Backend validation
            $errors = $this->pptec_validate_event_form($form_data);
            if (!empty($errors)) {
                throw new EventFormValidationException("", 0, null, $errors);
            }

            $wp_event_id = !empty($form_data['post_ID']) ? (int)$form_data['post_ID'] : 0;

            // Prepare data for database and for sending to PP

            // Prepare title, short_description and description
            $name = $form_data['post_title'];
            $description = !empty($form_data['content']) ? $form_data['content'] : ' ';
            $short_description = !empty($form_data['excerpt']) ? $form_data['excerpt'] : (!empty($form_data['content']) ? wp_trim_words($form_data['content'], 50) : ' ');

            // Prepare `startson` param
            $startson = date("Y-m-d H:i:s", strtotime(sanitize_text_field($form_data['EventStartDate']) . ' ' . sanitize_text_field($form_data['EventStartTime'])));
            if (!empty($form_data['pptec_skip_same_startson'])) {
                $_SESSION['pptec_skip_same_startson'] = 1;
            }

            // Prepare `endsat` param
            $endsat = date("Y-m-d H:i:s", strtotime(sanitize_text_field($form_data['EventEndDate']) . ' ' . sanitize_text_field($form_data['EventEndTime'])));

            // Prepare `doorsopen` param
            $doors_open = (isset($form_data['doors_open'])) ? date("H:i:s", strtotime(sanitize_text_field($form_data['doors_open']))) : '';

            // Prepare `sales_start` and `sales_stop` params
            $sales_start = (isset($form_data['sales_start'])) ? date("Y-m-d H:i:s", strtotime(sanitize_text_field($form_data['sales_start']))) : '';
            $sales_stop = (isset($form_data['sales_stop'])) ? date("Y-m-d H:i:s", strtotime(sanitize_text_field($form_data['sales_stop']))) : '';

            // Prepare venue location
            $wp_venue = $this->prepareVenueLocationData($form_data);
            $json['wp_venue'] = $wp_venue;

            // Prepare pricing
            $prices_sanitized = array();
            if (isset($form_data['pp_prices_data'])) {
                // Change incorrect escaping of "'" to make json_decode work
                $prices_json = str_replace('\\\'', '\\\\\'', $form_data['pp_prices_data']);
                $prices = json_decode($prices_json, ARRAY_A);

                if (!empty($prices)) {
                    foreach ($prices as $row_id => $price_row) {
                        foreach ($price_row as $key => $value) {
                            // Remove escaping of "'"
                            $prices_sanitized[$row_id][$key] = !is_array($value) ? str_replace('\\\'', '\'', $value) : $value;
                        }
                    }
                }
            }

            // Prepare taxes
            $pp_tax = '';
            if (1 === (int)$form_data['pp_add_tax_hidden'] && isset($form_data['pp_tax'])) {
                $pp_tax = (float)$form_data['pp_tax'];
            }

            // Prepare coupons
            $coupons_sanitized = array();
            if (isset($form_data['pp_coupons_hidden_rows'])) {
                $coupons_json = str_replace('\\\'', '\\\\\'', $form_data['pp_coupons_hidden_rows']);
                $coupons = json_decode($coupons_json, ARRAY_A);

                if (!empty($coupons)) {
                    foreach ($coupons as $row_id => $coupon_row) {
                        foreach ($coupon_row as $key => $value) {
                            // Remove escaping of "'"
                            $coupons_sanitized[$row_id][$key] = !is_array($value) ? str_replace('\\\'', '\'', $value) : $value;
                        }
                    }
                }
            }

            // Prepare questions
            $questions_sanitized = array();
            if (isset($form_data['pp_questions_hidden_rows'])) {
                $questions_json = str_replace('\\\'', '\\\\\'', $form_data['pp_questions_hidden_rows']);
                $questions = json_decode($questions_json, ARRAY_A);

                if (!empty($questions)) {
                    foreach ($questions as $row_id => $question_row) {
                        foreach ($question_row as $key => $value) {
                            // Remove escaping of "'"
                            $questions_sanitized[$row_id][$key] = !is_array($value) ? str_replace('\\\'', '\'', $value) : $value;
                        }
                    }
                }
            }

            // Final array of params
            $pp_event_data = array(
                'data' => array(
                    // Whether to show PP form in ECP event form
                    'pp_enabled_ticket_sales' => ( isset( $form_data['pp_enabled_ticket_sales'] ) ) ? sanitize_text_field( $form_data['pp_enabled_ticket_sales'] ) : 0,

                    // Basic info
                    'name' => str_replace('\\\'', '\'', $name),
                    'descr' => str_replace('\\\'', '\'', $description),
                    'short_descr' => str_replace('\\\'', '\'', $short_description),
                    'startson' => $startson,
                    'endsat' => $endsat,
                    'ages' => !empty( $form_data['ages'] ) ? sanitize_text_field( $form_data['ages'] ) : 'All Ages',
                    'sales_start' => $sales_start,
                    'sales_stop' => $sales_stop,
                    'doorsopen' => $doors_open,

                    // List as an upcoming event on Purplepass
                    'hidden' => isset($form_data['pp_hidden']) ? (int)$form_data['pp_hidden'] : 1,

                    // Require a password to purchase tickets for this event
                    'passwded' => isset($form_data['pp_passwded']) ? (int)$form_data['pp_passwded'] : 0,
                    'passwd' => isset($form_data['pp_pass']) ? sanitize_text_field( $form_data['pp_pass'] ) : '',

                    // Event category
                    'category_1' => isset($form_data['pp_categories_choosen']) ? sanitize_text_field( $form_data['pp_categories_choosen'] ) : 'art',

                    // Venue location
                    'venue_location_id' => (int)$wp_venue->pp_venue_id,
                    'venue' => !empty($wp_venue->name) ? $wp_venue->name : '',
                    'capacity' => isset($form_data['pp_capacity']) ? (int)$form_data['pp_capacity'] : 0,
                    'addr' => isset($wp_venue->addr) ? $wp_venue->addr : '',
                    'city' => isset($wp_venue->city) ? $wp_venue->city : '',
                    'state' => isset($wp_venue->state_id) ? $wp_venue->state_id : -1,
                    'zip' => isset($wp_venue->zip) ? $wp_venue->zip : '',
                    'country' => isset($wp_venue->country_id) ? $wp_venue->country_id : '',
                    'timezone' => isset($form_data['pptec_timezone_id']) ? sanitize_text_field($form_data['pptec_timezone_id']) : 0,

                    // Pricing @todo v2
                    'price_type' => ( isset( $form_data['pp_price_type'] ) ) ? sanitize_text_field( $form_data['pp_price_type'] ) : '',
                    'price' => $prices_sanitized,

                    // ASE
                    'venue_id'                => ( isset( $form_data['pp_venue_map'] ) ) ? sanitize_text_field( $form_data['pp_venue_map'] ) : '',
                    'venue_name'              => !empty($wp_venue->name) ? str_replace('\\\'', '\'', $wp_venue->name) : '',
                    'pp_venue_map_name'       => ( isset( $form_data['pp_venue_map_name'] ) ) ? str_replace('\\\'', '\'', $form_data['pp_venue_map_name']) : '',
                    'items_type'              => ( isset( $form_data['pp_being_sold'] ) ) ? sanitize_text_field( $form_data['pp_being_sold'] ) : 0,

                    // Ticket delivery options
                    'delivery_expr' => ( isset( $form_data['delivery_expr']) && 'on' === $form_data['delivery_expr'] ) ? '1' : '',
                    'delivery_prior' => ( isset( $form_data['delivery_prior']) && 'on' === $form_data['delivery_prior'] ) ? '1' : '',
                    'delivery_wcall' => ( isset( $form_data['delivery_wcall']) && 'on' === $form_data['delivery_wcall'] ) ? '1' : '',
                    'delivery_home' => ( isset( $form_data['delivery_home']) && 'on' === $form_data['delivery_home'] ) ? '1' : '',
                    'delivery_usps' => ( isset( $form_data['delivery_usps']) && 'on' === $form_data['delivery_usps'] ) ? '1' : '',
                    'delivery_custom' => ( isset( $form_data['delivery_custom']) && 'on' === $form_data['delivery_custom'] ) ? '1' : '',
                    'custom_delivery' => ( isset( $form_data['custom_delivery'] ) ) ? sanitize_text_field( $form_data['custom_delivery'] ) : '', // @todo v2: process object

                    // Sales Tax
                    'stax' => $pp_tax,
                    'ffee'                    => ( isset( $form_data['pp_fee'] ) ) ? sanitize_text_field( $form_data['pp_fee'] ) : '',
                    'ffee_flat'               => ( isset( $form_data['pp_fee_doll'] ) ) ? sanitize_text_field( $form_data['pp_fee_doll'] ) : '',
                    'ffee_perc'               => ( isset( $form_data['pp_fee_percent'] ) ) ? sanitize_text_field( $form_data['pp_fee_percent'] ) : '',
                    'ffee_min'                => ( isset( $form_data['pp_fee_min_doll'] ) ) ? sanitize_text_field( $form_data['pp_fee_min_doll'] ) : '',
                    'ffee_split'              => ( isset( $form_data['pp_fee_sep_hidden'] ) ) ? sanitize_text_field( $form_data['pp_fee_sep_hidden'] ) : '',
                    'stax_perc'               => ( isset( $form_data['pp_tax'] ) ) ? sanitize_text_field( $form_data['pp_tax'] ) : '',

                    // Coupons
                    'coupons' => $coupons_sanitized,

                    // Additional Options

                    // Custom notice or terms & conditions to the transaction
                    'terms_enable'            => ( isset( $form_data['pp_add_terms_hidden'] ) ) ? sanitize_text_field( $form_data['pp_add_terms_hidden'] ) : '',
                    'terms'                   => ( isset( $form_data['pp_term_text'] ) ) ? sanitize_text_field( $form_data['pp_term_text'] ) : '',
                    'terms_require'           => ( isset( $form_data['pp_term_req_hidden'] ) ) ? sanitize_text_field( $form_data['pp_term_req_hidden'] ) : '',

                    // Enable Facebook Options
                    'fb_page_url'             => ( isset( $form_data['pp_fb_url'] ) ) ? sanitize_text_field( $form_data['pp_fb_url'] ) : '',
                    'fb_login'                => ( isset( $form_data['pp_add_facebook_hidden'] ) ) ? sanitize_text_field( $form_data['pp_add_facebook_hidden'] ) : '',
                    'fb_checkin'              => ( isset( $form_data['pp_auto_check_in_hidden'] ) ) ? sanitize_text_field( $form_data['pp_auto_check_in_hidden'] ) : '',
                    'fb_tell'                 => ( isset( $form_data['pp_tell_opt_hidden'] ) ) ? sanitize_text_field( $form_data['pp_tell_opt_hidden'] ) : '',
                    'require_fb_like'         => ( isset( $form_data['pp_like_before_purch_hidden'] ) ) ? sanitize_text_field( $form_data['pp_like_before_purch_hidden'] ) : '',

                    // Personalized message
                    'receipt_enable'          => ( isset( $form_data['pp_pers_msg_hidden'] ) ) ? sanitize_text_field( $form_data['pp_pers_msg_hidden'] ) : '',
                    'receipt'                 => ( isset( $form_data['pp_msg_text'] ) ) ? sanitize_text_field( $form_data['pp_msg_text'] ) : '',

                    // Provide special note or instructions to Purplepass telephone support staff
                    'operator_info_enable'    => ( isset( $form_data['pp_spec_notes_hidden'] ) ) ? sanitize_text_field( $form_data['pp_spec_notes_hidden'] ) : '',
                    'operator_info'           => ( isset( $form_data['pp_spec_notes_text'] ) && !empty( $form_data['pp_spec_notes_hidden'] )) ? sanitize_text_field( $form_data['pp_spec_notes_text'] ) : '',

                    // Questions
                    'questions_enable' => ( isset( $form_data['pp_questions_hidden'] ) ) ? sanitize_text_field( $form_data['pp_questions_hidden'] ) : '',
                    'questions' => $questions_sanitized,

                    // Email templates
                    'custom_tpl_id'           => ( isset( $form_data['pp_email_template_hidden'] ) ) ? sanitize_text_field( $form_data['pp_email_template_hidden'] ) : '',
                    'custom_tpl_name'         => ( isset( $form_data['pp_email_template_name'] ) ) ? sanitize_text_field( $form_data['pp_email_template_name'] ) : '',

                    // Print-At-Home templates
                    'custom_pah_id'           => ( isset( $form_data['pp_pah_template_hidden'] ) ) ? sanitize_text_field( $form_data['pp_pah_template_hidden'] ) : '',
                    'custom_pah_name'         => ( isset( $form_data['pp_pah_template_name'] ) ) ? sanitize_text_field( $form_data['pp_pah_template_name'] ) : '',
                ),
            );

            // Send event data to PP
            if (!empty($form_data['pp_enabled_ticket_sales'])) {
                list($pp_event_updated, $pp_error) = $this->pptec_send_event_to_pp($pp_event_data['data'], $wp_event_id, $wp_venue->wp_venue_id, $startson);

                if (!$pp_event_updated && !empty($pp_error)) {
                    $errors = array();

                    $error_message_obj = json_decode($pp_error['message']);

                    if (is_object($error_message_obj)) {
                        $error_message_arr = (array)$error_message_obj;
                        foreach ($error_message_arr as $err_id => $err_message) {
                            $errors[] = array(
                                'selector' => '.' . $err_id,
                                'message' => sanitize_text_field($err_message)
                            );
                        }
                    } else {
                        $errors = array($pp_error);
                    }

                    throw new EventFormValidationException("", 0, null, $errors);
                }
            }

            update_post_meta( $wp_event_id, 'pptec_event_meta_data', (object)$pp_event_data );
            update_post_meta( $wp_event_id, 'pp_enabled_ticket_sales', $pp_event_data['data']['pp_enabled_ticket_sales'] );

            $json['success'] = true;
        } catch (EventFormValidationException $e) {
            $json['errors'] = $e->getErrors();
        }

        echo json_encode($json);
        die;
    }

    private function prepareVenueLocationData($form_data)
    {
        $wp_venue_id = !empty($form_data['venue']['VenueID'][0]) ? (int)$form_data['venue']['VenueID'][0] : -1;
        $wp_venue = null;

        // We receive an array of venue data and wp_venue_id.
        // If we receive wp_venue_id > 0, we need to update it. If we receive wp_venue_id === -1, we need to create a new one.

        $title = isset($form_data['venue']['Venue'][0]) ? str_replace('\\\'', '\'', $form_data['venue']['Venue'][0]) : 'Unnamed Venue';

        $postdata = array(
            'post_title' => $title,
            'post_content' => '',
            'post_name' => sanitize_title($title),
            'post_type' => 'tribe_venue',
            'post_status' => 'publish',
            'post_author' => get_current_user_id() // prepareVenueLocationData is only called via ajax, so it is ok to rely on session here
        );

        if (-1 === $wp_venue_id) {
            // Create a new venue
            $postdata['post_date'] = date('Y-m-d H:i:s');
            $postdata['post_date_gmt'] = gmdate('Y-m-d H:i:s');

            $wp_venue_id = wp_insert_post($postdata, true);
            $possible_error_message = 'Unable to create a new Venue Location. Please try again later.';
        } else {
            // Update existing venue
            $postdata['ID'] = $wp_venue_id;
            $postdata['post_modified'] = date('Y-m-d H:i:s');
            $postdata['post_modified_gmt'] = gmdate('Y-m-d H:i:s');

            $wp_venue_id = wp_update_post($postdata, true);
            $possible_error_message = 'Unable to update Venue Location. Please try again later.';
        }

        if (is_wp_error($wp_venue_id)) {
            throw new EventFormValidationException("", 0, null, array(
                array('selector' => '[name="venue[VenueID][]"] + .select2', 'message' => $possible_error_message)
            ));
        }

        // Get binding
        $pp_venue_id = pptec_get_pp_venue_id_by_wp_venue_id($wp_venue_id);

        // Get state (US) or province (Canada) id
        $state_id = -1;
        if (isset($form_data['venue']['Country'][0])) {
            if ('United States' === $form_data['venue']['Country'][0] && isset($form_data['venue']['State'])) {
                $all_states_list = array_flip(pptec_get_us_states_list(true, true));
                $state_id = isset($all_states_list[$form_data['venue']['State']]) ? $all_states_list[$form_data['venue']['State']] : -1;
            } elseif ('Canada' === $form_data['venue']['Country'][0] && isset($form_data['venue']['Province'][0])) {
                $all_states_list = array_flip(pptec_get_canada_provinces_list(true));
                $state_id = isset($all_states_list[$form_data['venue']['Province'][0]]) ? $all_states_list[$form_data['venue']['Province'][0]] : -1;
            }
        }

        if (-1 === (int)$state_id) {
            $state_code = '-';
            $province_name = '';
        } else {
            $state_code = isset($form_data['venue']['State']) ? $form_data['venue']['State'] : '';
            $province_name = isset($form_data['venue']['Province'][0]) ? $form_data['venue']['Province'][0] : '';
        }

        // Get pp country id
        $all_countries_list = array_flip(pptec_get_countries_list(true));

        $wp_venue = (object)array(
            'wp_venue_id' => (int)$wp_venue_id,
            'pp_venue_id' => (int)$pp_venue_id,
            'name' => $title,
            'addr' => isset($form_data['venue']['Address'][0]) ? str_replace('\\\'', '\'', $form_data['venue']['Address'][0]) : '',
            'city' => isset($form_data['venue']['City'][0]) ? str_replace('\\\'', '\'', $form_data['venue']['City'][0]) : '',
            'country' => isset($form_data['venue']['Country'][0]) ? str_replace('\\\'', '\'', $form_data['venue']['Country'][0]) : '',
            'country_id' => (!empty($form_data['venue']['Country'][0]) && !empty($all_countries_list[$form_data['venue']['Country'][0]])) ? (int)$all_countries_list[$form_data['venue']['Country'][0]] : 1,
            'state' => $state_code,
            'state_id' => $state_id,
            'province' => $province_name,
            'zip' =>  isset($form_data['venue']['Zip'][0]) ? $form_data['venue']['Zip'][0] : '',
        );

        // Update metadata in db
        update_post_meta($wp_venue_id, '_VenueOrigin', 'events-calendar');
        update_post_meta($wp_venue_id, '_VenueAddress', $wp_venue->addr);
        update_post_meta($wp_venue_id, '_VenueCity', $wp_venue->city);
        update_post_meta($wp_venue_id, '_VenueCountry', $wp_venue->country);
        update_post_meta($wp_venue_id, '_VenueState', $wp_venue->state);
        update_post_meta($wp_venue_id, '_VenueProvince', $wp_venue->province);
        update_post_meta($wp_venue_id, '_VenueStateProvince', $wp_venue->province);
        update_post_meta($wp_venue_id, '_VenueZip', $wp_venue->zip);

        return $wp_venue;
    }

    private function pptec_validate_venue_form($data)
    {
        $errors = array();

        if (empty($data['post_title'])) {
            $errors[] = array(
                'selector' => '#title',
                'message' => "You must enter venue name"
            );
        }

        if (empty($data['venue']['Address'])) {
            $errors[] = array(
                'selector' => '[name="venue[Address]"]',
                'message' => "You must enter `Address`"
            );
        }

        if (empty($data['venue']['City'])) {
            $errors[] = array(
                'selector' => '[name="venue[City]"]',
                'message' => "You must enter `City`"
            );
        }

        if (empty($data['venue']['Country'])) {
            $errors[] = array(
                'selector' => '[name="venue[Country]"] + .select2 .select2-selection',
                'message' => "You must select `Country`"
            );
        } else {
            // Validate State (US) or Province (Canada)
            if ('United States' === $data['venue']['Country'] && empty($data['venue']['State'])) {
                $errors[] = array(
                    'selector' => '[name="venue[State]"] + .select2 .select2-selection',
                    'message' => "You must select `State or Province` for venue location"
                );
            } elseif (empty($data['venue']['Province']) && 'Canada' === $data['venue']['Country']) {
                $errors[] = array(
                    'selector' => '[name="venue[Province]"]',
                    'message' => "You must select `State or Province`"
                );
            }
        }

        if (empty($data['venue']['Zip'])) {
            $errors[] = array(
                'selector' => '[name="venue[Zip]"]',
                'message' => "You must enter `Postal Code (ZIP)` for venue location"
            );
        }

        return $errors;
    }

    private function pptec_send_venue_to_pp($post_fields, $pp_venue_id)
    {
        $args = array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                'Authorization' => 'Bearer ' . pptec_get_access_token(),
            ),
            'body'        => $post_fields,
            'data_format' => 'body',
        );

        $pp_response = wp_remote_get( PPTEC_WEB_URL . '/api/locations/' . (int)$pp_venue_id, $args );
        if (is_wp_error($pp_response) || empty($pp_response["body"])) {
            return array(false, array('selector' => '.pp_generic', 'message' => 'Unable to connect to Purplepass, please try again later.'));
        }

        $response_body = json_decode( $pp_response["body"] );

        if ( !empty($response_body->success) ) {
            $this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Update', 'Outbound', 'Venue "' . $post_fields['name'] . '" - updated successfully on Purplepass website', 'Successful');

            return array(true, array());
        } else {
            if (!empty($response_body->errors)) {
                $error_message  = json_encode((array)$response_body->errors);

                $this->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Update', 'Outbound', 'Update venue action failed. ' . $error_message, 'Failed' );

                return array(false, array('selector' => '.pp_generic', 'message' => $error_message));
            }

            return array(false, array('selector' => '.pp_generic', 'message' => 'Purplepass returned unknown error, please try again later.'));
        }
    }

    private function pptec_validate_event_form($data)
    {
        $errors = array();

        // Validate event name
        if (empty($data['post_title'])) {
            $errors[] = array(
                'selector' => '#title',
                'message' => "You must enter event name"
            );
        }

        // Validate event start date and time
        if (empty($data['EventStartDate'])) {
            $errors[] = array(
                'selector' => '#EventStartDate',
                'message' => "You must select event's start date"
            );
        }
        if (empty($data['EventStartTime'])) {
            $errors[] = array(
                'selector' => '#EventStartTime',
                'message' => "You must select event's start time"
            );
        }

        // Validate event end date and time
        if (empty($data['EventEndDate'])) {
            $errors[] = array(
                'selector' => '#EventStartDate',
                'message' => "You must select event's end date"
            );
        }
        if (empty($data['EventEndTime'])) {
            $errors[] = array(
                'selector' => '#EventEndTime',
                'message' => "You must select event's end time"
            );
        }

        // Validate primary category
        if (empty($data['pp_categories_choosen'])) {
            $errors[] = array(
                'selector' => '#pp_categories_choosen',
                'message' => "You must select event's category"
            );
        }

        // Validate description
        if (empty($data['content'])) {
            $errors[] = array(
                'selector' => '#wp-content-editor-container',
                'message' => "You must enter event's description"
            );
        }

        // Validate ECP venue data
        if (empty($data['venue']['Address'][0])) {
            $errors[] = array(
                'selector' => '[name="venue[Address][]"]',
                'message' => "You must enter `Address` for venue location"
            );
        }

        if (empty($data['venue']['City'][0])) {
            $errors[] = array(
                'selector' => '[name="venue[City][]"]',
                'message' => "You must enter `City` for venue location"
            );
        }

        if (empty($data['venue']['Country'][0])) {
            $errors[] = array(
                'selector' => '[name="venue[Country][]"] + .select2',
                'message' => "You must select `Country` for venue location"
            );
        }

        if (empty($data['venue']['State']) && (!empty($data['venue']['Country'][0]) && 'United States' === $data['venue']['Country'][0])) {
            $errors[] = array(
                'selector' => '[name="venue[State]"] + .select2',
                'message' => "You must select `State or Province` for venue location"
            );
        }

        if (empty($data['venue']['Zip'][0])) {
            $errors[] = array(
                'selector' => '[name="venue[Zip][]"]',
                'message' => "You must enter `Postal Code (ZIP)` for venue location"
            );
        }

        // Validate ticket sales start
        if (empty($data['sales_start'])) {
            $errors[] = array(
                'selector' => '.sale_start_date',
                'message' => "You must select when Ticket Sales start"
            );
        }

        // Ticket sales stop
        if (empty($data['sale_start_date'])) {
            $errors[] = array(
                'selector' => '.sale_start_date',
                'message' => "You must select when Ticket Sales start"
            );
        }

        // Ticket sales stop
        if (empty($data['sales_stop'])) {
            $errors[] = array(
                'selector' => '.sale_stop_date',
                'message' => "You must select when Ticket Sales stop"
            );
        }

        // Ticket sales stop
        if (empty($data['sale_stop_date'])) {
            $errors[] = array(
                'selector' => '.sale_stop_date',
                'message' => "You must select when Ticket Sales stop"
            );
        }

        if (empty($data['excerpt'])) {
            $errors[] = array(
                'selector' => '#excerpt',
                'message' => "You must enter event's short description"
            );
        }

        if (pptec_check_upper_limit($data['excerpt'])) {
            $errors[] = array(
                'selector' => '#excerpt',
                'message' => "There are too many capitalized letters in the short description"
            );
            //$short_description = ucfirst(mb_strtolower($short_description));
        }

        if (
            !empty($data['EventStartDate']) && !empty($data['EventStartTime']) &&
            !empty($data['EventEndDate']) && !empty($data['EventEndTime']) &&
            !empty($data['sales_start']) && !empty($data['sales_stop'])
        ) {
            $current_date_time = strtotime(date('Y-m-d'));
            $startson_time = strtotime($data['EventStartDate'] . " " . $data['EventStartTime']);
            $endsat_time = strtotime($data['EventEndDate'] . " " . $data['EventEndTime']); 
            $sales_start_time = strtotime($data['sales_start']);
            $sales_stop_time = strtotime($data['sales_stop']);

            // Check start date
            if ($startson_time < $current_date_time && $data['post_status'] === 'draft') {
                $errors[] = array(
                    'selector' => '#EventStartDate',
                    'message' => "Date in past is not allowed for Event Start Date"
                );
            }

            // Check sale start date
            if ($sales_start_time < strtotime('-5 years', time())) {
                $errors[] = array(
                    'selector' => '.sale_start_date',
                    'message' => "Ticket Sales Start is too far in the past"
                );
            }

            // Check ticket sales start must be before ticket sales stop
            if ($sales_start_time > $sales_stop_time) {
                $errors[] = array(
                    'selector' => '.sale_start_date',
                    'message' => "Ticket Sales Start must be before Ticket Sales Stop"
                );
            }

            // Check sales stop must be before event end
            if ($endsat_time < $sales_stop_time) {
                $errors[] = array(
                    'selector' => '.sale_stop_date',
                    'message' => "You cannot continue selling tickets online after the event has ended. Please update the Ticket Sales Stop time to be before the event ends"
                );
            }
        }

        // @todo v2
        // Prices
        // 1. General admission - price, qty for each
        // 2. Assigned seating - price for each

        return $errors;
    }

    private function pptec_send_event_to_pp($post_fields, $wp_event_id, $wp_venue_id, $startson)
    {
        $pp_event_id = pptec_get_pp_event_id_by_wp_event_id(sanitize_text_field($wp_event_id));

        if (!empty($pp_event_id)) {
            // Update existing PP event

            // Check whether current event belongs to current user
            $pp_user_id = pptec_oauth_get_pp_user_id();
            $wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id($wp_event_id);
            if ($wp_event_pp_user_id && $pp_user_id != $wp_event_pp_user_id) {
                // set up alert message for user - that he tried to edit or update not his event
                return array(false, array('selector' => '.pp_generic', 'message' => 'This event does not belong to you, changes will not be pushed to Purplepass.'));
            }

            $action = 'event_update';
            $post_fields['id'] = $pp_event_id;
        } else {
            // Create a new PP event

            $action = 'event_add';
            $post_fields['create_id'] = time();
            $post_fields['type'] = !empty($post_fields['event_type']) ? $post_fields['event_type'] : 0;
        }

        $post_fields['section'] = 'events';
        $post_fields['action'] = $action;

        if (!empty($_SESSION['pptec_skip_same_startson'])) {
            $post_fields['skip_same_startson'] = 1;
            unset($_SESSION['pptec_skip_same_startson']);
        }

        // Send request
        $pp_response = pptec_get_remote_data(http_build_query($post_fields));
        if (is_wp_error($pp_response) || empty($pp_response["body"])) {
            return array(false, array('selector' => '.pp_generic', 'message' => 'Unable to connect to Purplepass, please try again later.'));
        }

        $response_body = json_decode( $pp_response["body"] );

        if ( !empty($response_body->success) ) {
            $pp_event_id = isset($response_body->id) ? (int)$response_body->id : false;

            // Bind (or rebind) pp event to wp event
            if ($pp_event_id && 'event_add' === $action) {
                pptec_bind_pp_event_id_to_wp_event_id($pp_event_id, $wp_event_id);
            }

            // Bind pp_venue_id to wp_venue_id. We must receive venue_location_id in response if we sent venue_location_id = 0 on request.
            if (!empty($response_body->venue_location_id) && !empty($wp_venue_id) && $wp_venue_id > 0 && !empty($pp_event_id)) {
                pptec_bind_pp_venue_id_to_wp_venue_id($response_body->venue_location_id, $wp_venue_id, $pp_event_id, $wp_event_id);
            }

            if ('event_update' === $action) {
                $this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Update', 'Outbound', 'Event "' . $post_fields['name'] . '" - updated successfully on Purplepass website', 'Successful');
            } elseif ('event_add' === $action) {
                $this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Create', 'Outbound', 'Event "' . $post_fields['name'] . '" - published successfully on Purplepass website', 'Successful');
            }

            return array(true, array());
        } else {
            if ( !empty($response_body->same_startson) ) {
                $this->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Create', 'Outbound', 'Event with the same startsOn ' . $startson . ' already exists on Purplepass', 'Failed' );

                return array(false, array('selector' => '.pp_generic', 'same_startson' => true, 'message' => 'There is already an event created that begins on ' . date('F jS, Y \a\t g:ia', strtotime($startson)) . '. Are you sure you want to create another event with the same date and time?'));
            } elseif (!empty($response_body->errors)) {
                $error_message  = json_encode((array)$response_body->errors);

                if ( 'event_update' === $action ) {
                    $this->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Update', 'Outbound', 'Update action failed. ' . $error_message, 'Failed' );
                } else {
                    $this->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Create', 'Outbound', $error_message, 'Failed' );
                }

                return array(false, array('selector' => '.pp_generic', 'message' => $error_message));
            }

            return array(false, array('selector' => '.pp_generic', 'message' => 'Purplepass returned unknown error, please try again later.'));
        }
    }

	public function pptec_delete_event_on_purple_pass($event_id)
	{
		if (!current_user_can('edit_others_posts')) {
			return false;
		}

		global $wpdb;

		$pp_event_id = pptec_get_pp_event_id_by_wp_event_id($event_id);

		/**
		 * Check if user wants to delete his event, if no - process will be finished
		 */
		$pp_user_id = pptec_oauth_get_pp_user_id();
		$wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id($event_id);

		$prefix = $wpdb->prefix;
		$wpdb->delete($prefix . "pptec_events", array('pp_event_id' => $pp_event_id));

		if (!$pp_user_id || !$wp_event_pp_user_id) {
			return false;
		}

		if ((int)$pp_user_id !== (int)$wp_event_pp_user_id) {
			// set up alert message for user - that he tried to edit or update not his event
			return false;
		}

		// Delete from PP
		$post_fields = array(
			'section' => 'events',
			'action' => 'event_delete',
			'id' => $pp_event_id,
		);

		$post_fields = http_build_query($post_fields);
		$remote_data_response = pptec_get_remote_data($post_fields);
		if (is_wp_error($remote_data_response) || empty($remote_data_response["body"])) {
			return false;
		}
		$request = json_decode($remote_data_response["body"]);

		if (!empty($request->success)) {
			// add notification to log system
			$this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Delete', 'Outbound', 'Event "' . get_post($event_id)->post_title . '" - was successfully deleted from Purplepass website', 'Successful');

		}
	}

	public function pptec_create_update_event($wp_user_id, $pp_event_id, $event_data = array(), &$venues_bindings = array())
    {
        $processing_result = array(
            'added' => 0,
            'updated' => 0,
        );

        if (empty($event_data)) {
            // @todo v2: change to /events/list
            $post_fields = 'section=events&action=event_read&id=' . $pp_event_id;
            $remote_data = pptec_get_remote_data($post_fields);

            // If request went wrong
            if (is_wp_error($remote_data) || empty($remote_data['body'])) {
                return $processing_result;
            }

            $event_response = json_decode($remote_data['body']);
        } else {
            $event_response = (object)array('data' => $event_data);
        }

        // Parent recurring event filter
        if (isset($event_response->data->options) && ($event_response->data->options & PPTEC_OPT_EVENT_IS_TPL_MASK)) {
            return $processing_result;
        }

        // If event is not retrieved from PP
        if (empty($event_response->data->id) || empty($event_response->data->name)) {
            return $processing_result;
        }

        $pp_event_id = $event_response->data->id;

        // Check if pp_event_id is already linked to some wp_event_id
        $wp_event_id = pptec_get_wp_event_id_by_pp_event_id($pp_event_id);

        // Whether PP ticket sales enabled for this event
        $pp_enabled_ticket_sales = 1;

        $action = !empty($wp_event_id) ? 'event_update' : 'event_add';
        switch ($action) {
            case 'event_add':
                $insert_args = array(
                    'post_title' => $event_response->data->name,
                    'post_type' => 'tribe_events',
                    'post_content' => ($event_response->data->descr) ? $event_response->data->descr : '',
                    'post_status' => 'publish',
                    'post_author' => $wp_user_id,
                );

                // We must cancel event on WP if it was canceled on PP
                if (PPTEC_EVENT_STATUS_CANCELLED & $event_response->data->status) {
                    $insert_args['post_status'] = 'canceled';
                }

                $post = wp_insert_post($insert_args, true);

                if (is_wp_error($post)) {
                    $this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Create FETCH ADD', 'Inbound', 'Something went wrong during the event creation (' . $event_response->data->name . '): ' . $post->get_error_message(), 'Failed');
                    return $processing_result;
                } else {
                    $wp_event_id = $post;
                    $processing_result['added'] = 1;
                    $this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Create FETCH ADD', 'Inbound', 'Added event: ' . $event_response->data->name, 'Successful');

                    // Bind pp_event_id to wp_event_id
                    pptec_bind_pp_event_id_to_wp_event_id($pp_event_id, $wp_event_id);
                }

                break;

            case 'event_update':
                $update_args = array(
                    'ID' => $wp_event_id,
                    'post_title' => $event_response->data->name,
                    'post_content' => ($event_response->data->descr) ? $event_response->data->descr : ''
                );

                // We must cancel event on WP if it was canceled on PP
                if (PPTEC_EVENT_STATUS_CANCELLED & $event_response->data->status) {
                    $update_args['post_status'] = 'canceled';
                }

                $post = wp_update_post($update_args, true);

                if (is_wp_error($post)) {
                    $this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Update FETCH ADD', 'Inbound', 'Something went wrong during the event update (' . $event_response->data->name . '): ' . $post->get_error_message(), 'Failed');
                    return $processing_result;
                } else {
                    $wp_event_id = $post;
                    $processing_result['updated'] = 1;
                    $this->pptec_add_log(date('Y-m-d H:i:s', time()), 'Update FETCH UPDATE', 'Inbound', 'Updated event: ' . $event_response->data->name, 'Successful');
                }

                $pp_enabled_ticket_sales = (int)get_post_meta($wp_event_id, 'pp_enabled_ticket_sales', true);

                break;
        }

        // Process venue location
        $pp_venue_id = $event_response->data->venue_location_id;
        $wp_venue_id = $this->pptec_process_venue_locations($wp_user_id, $event_response->data, $venues_bindings);
        if ($wp_venue_id) {
            update_post_meta($wp_event_id, '_EventVenueID', $wp_venue_id);
            pptec_bind_pp_venue_id_to_wp_venue_id($pp_venue_id, $wp_venue_id, $pp_event_id, $wp_event_id);
        }

        // Sanitize prices array
        $prices = !empty($event_response->data->price) ? $this->sanitizeArray($event_response->data->price) : array();

        // Sanitize custom delivery object
        $custom_delivery = !empty($event_response->data->custom_delivery) ? $this->sanitizeArray($event_response->data->custom_delivery) : array();

        // Sanitize coupons array
        $coupons = !empty($event_response->data->coupons) ? $this->sanitizeArray($event_response->data->coupons) : array();

        // Sanitize questions array
        $questions = !empty($event_response->data->questions) ? $this->sanitizeArray($event_response->data->questions) : array();

        // @todo v2: unify this to remove code duplicates in this file
        $pp_event_data = array(
            'data' => array(
                // Whether to show PP form in ECP event form
                'pp_enabled_ticket_sales' => $pp_enabled_ticket_sales,

                // Basic info
                'name' => !empty($event_response->data->name) ? $event_response->data->name : '',
                'descr' => !empty($event_response->data->descr) ? $event_response->data->descr : '',
                'short_descr' => !empty($event_response->data->short_descr) ? $event_response->data->short_descr : '',
                'startson' => !empty($event_response->data->startson) ? sanitize_text_field($event_response->data->startson) : '',
                'endsat' => !empty($event_response->data->endsat) ? sanitize_text_field($event_response->data->endsat) : '',
                'ages' => !empty($event_response->data->ages) ? sanitize_text_field($event_response->data->ages) : 'All Ages',
                'sales_start' => !empty($event_response->data->sales_start) ? sanitize_text_field($event_response->data->sales_start) : '',
                'sales_stop' => !empty($event_response->data->sales_stop) ? sanitize_text_field($event_response->data->sales_stop) : '',
                'doorsopen' => !empty($event_response->data->doorsopen) ? sanitize_text_field($event_response->data->doorsopen) : '',

                // List as an upcoming event on Purplepass
                'hidden' => !empty($event_response->data->hidden) ? (int)$event_response->data->hidden : 0,

                // Require a password to purchase tickets for this event
                'passwded' => !empty($event_response->data->passwded) ? (int)$event_response->data->passwded : 0,
                'passwd' => !empty($event_response->data->passwd) ? esc_html($event_response->data->passwd) : '',

                // Event category
                'category_1' => !empty($event_response->data->category_1) ? sanitize_text_field($event_response->data->category_1) : '',

                // Venue location
                'venue_location_id' => (int)$pp_venue_id,
                'venue' => !empty($event_response->data->venue) ? $event_response->data->venue : '',
                'capacity' => !empty($event_response->data->capacity) ? (int)$event_response->data->capacity : 0,
                'addr' => !empty($event_response->data->addr) ? $event_response->data->addr : '',
                'city' => !empty($event_response->data->city) ? $event_response->data->city : '',
                'state' => !empty($event_response->data->state) ? (int)$event_response->data->state : 0,
                'zip' => !empty($event_response->data->zip) ? $event_response->data->zip : 0,
                'country' => !empty($event_response->data->country) ? (int)$event_response->data->country : 1,
                'timezone' => !empty($event_response->data->timezone) ? (int)$event_response->data->timezone : 0,

                // Pricing
                'price_type' => !empty($event_response->data->price_type) ? esc_html($event_response->data->price_type) : 0,
                'price' => $prices,

                // ASE
                'venue_id' => !empty($event_response->data->venue_id) ? (int)$event_response->data->venue_id : 0,
                'venue_name' => !empty($event_response->data->venue_name) ? $event_response->data->venue_name : '',
                'items_type' => !empty($event_response->data->items_type) ? (int)$event_response->data->items_type : 0,
                'pp_venue_map_name' => (!empty($event_response->data->venue_name)) ? $event_response->data->venue_name : '',

                // Ticket delivery options
                'delivery_expr' => !empty($event_response->data->delivery_expr) ? (int)$event_response->data->delivery_expr : 0,
                'delivery_prior' => !empty($event_response->data->delivery_prior) ? (int)$event_response->data->delivery_prior : 0,
                'delivery_wcall' => !empty($event_response->data->delivery_wcall) ? (int)$event_response->data->delivery_wcall : 0,
                'delivery_home' => !empty($event_response->data->delivery_home) ? (int)$event_response->data->delivery_home : 0,
                'delivery_usps' => !empty($event_response->data->delivery_usps) ? (int)$event_response->data->delivery_usps : 0,
                'delivery_custom' => !empty($event_response->data->delivery_custom) ? (int)$event_response->data->delivery_custom : 0,
                'custom_delivery' => (object)$custom_delivery,

                // Sales Tax
                'stax' => !empty($event_response->data->stax) ? (int)$event_response->data->stax : 0,
                'ffee' => !empty($event_response->data->ffee) ? (int)$event_response->data->ffee : 0,
                'ffee_split' => !empty($event_response->data->ffee_split) ? (int)$event_response->data->ffee_split : 0,
                'ffee_flat' => !empty($event_response->data->ffee_flat) ? (float)$event_response->data->ffee_flat : 0,
                'ffee_perc' => !empty($event_response->data->ffee_perc) ? (float)$event_response->data->ffee_perc : 0,
                'ffee_min' => !empty($event_response->data->ffee_min) ? (float)$event_response->data->ffee_min : 0,
                'stax_perc' =>  !empty($event_response->data->stax_perc) ? (float)$event_response->data->stax_perc : 0,

                // Coupons
                'require_coupons' => !empty($event_response->data->require_coupons) ? (int)$event_response->data->require_coupons : 0,
                'coupons' => $coupons,

                // Additional Options

                // Custom notice or terms & conditions to the transaction
                'terms_enable' => !empty($event_response->data->terms_enable) ? (int)$event_response->data->terms_enable : 0,
                'terms_require' => !empty($event_response->data->terms_require) ? (int)$event_response->data->terms_require : 0,
                'terms' => !empty($event_response->data->terms) ? esc_html($event_response->data->terms) : '',

                // Enable Facebook Options
                'fb_page_url' => !empty($event_response->data->fb_page_url) ? esc_url($event_response->data->fb_page_url) : '',
                'fb_login' => !empty($event_response->data->fb_login) ? (int)$event_response->data->fb_login : 0,
                'fb_checkin' => !empty($event_response->data->fb_checkin) ? (int)$event_response->data->fb_checkin : 0,
                'fb_tell' => !empty($event_response->data->fb_tell) ? (int)$event_response->data->fb_tell : 0,
                'require_fb_like' => !empty($event_response->data->require_fb_like) ? (int)$event_response->data->require_fb_like : 0,

                // Personalized message
                'receipt_enable' => !empty($event_response->data->receipt_enable) ? (int)$event_response->data->receipt_enable : 0,
                'receipt' => !empty($event_response->data->receipt) ? esc_html($event_response->data->receipt) : '',

                // Provide special note or instructions to Purplepass telephone support staff
                'operator_info_enable' => !empty($event_response->data->operator_info_enable) ? (int)$event_response->data->operator_info_enable : 0,
                'operator_info' => !empty($event_response->data->operator_info) ? esc_html($event_response->data->operator_info) : '',

                // Questions
                'questions_enable' => !empty($event_response->data->questions_enable) ? (int)$event_response->data->questions_enable : 0,
                'questions' => $questions,

                // Email templates
                'custom_tpl_id' => !empty($event_response->data->custom_tpl_id) ? (int)$event_response->data->custom_tpl_id : 0,
                'custom_tpl_name' => !empty($event_response->data->custom_tpl_name) ? esc_html($event_response->data->custom_tpl_name) : '',

                // Print-At-Home templates
                'custom_pah_id' => !empty($event_response->data->custom_pah_id) ? (int)$event_response->data->custom_pah_id : 0,
                'custom_pah_name' => !empty($event_response->data->custom_pah_name) ? esc_html($event_response->data->custom_pah_name) : '',
            ),
        );

		update_post_meta($wp_event_id, 'pptec_event_meta_data', (object)$pp_event_data);
        update_post_meta( $wp_event_id, 'pp_enabled_ticket_sales', $pp_event_data['data']['pp_enabled_ticket_sales'] );
		update_post_meta($wp_event_id, '_EventStartDate', esc_html($event_response->data->startson));
		update_post_meta($wp_event_id, '_EventEndDate', esc_html($event_response->data->endsat));

		// Remove event from queue
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . "pp_sync_events", array( 'event_id' => $pp_event_id ) );

        return $processing_result;
	}

	public function pptec_delete_event( $pp_event_id ) {

		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM  {$wpdb->prefix}pptec_events WHERE pp_event_id={$pp_event_id}" );

		if ( $results[0]->wp_event_id ) {
			wp_delete_post( $results[0]->wp_event_id, true );
			// add notification to log system
			$this->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Delete', 'Inbound', 'Deleted event with Purplepass ID: ' . $pp_event_id, 'Successful' );
		}

		$wpdb->delete( $wpdb->prefix . "pp_sync_events", array( 'event_id' => $pp_event_id ) );
	}

	public function pptec_cancel_event($pp_event_id)
	{
		global $wpdb;
		$results = $wpdb->get_results("SELECT `wp_event_id` FROM `{$wpdb->prefix}pptec_events` WHERE `pp_event_id` = " . (int)$pp_event_id);

		if (!empty($results[0]->wp_event_id)) {
			$wpdb->update($wpdb->posts, array('post_status' => 'canceled'), array('ID' => (int)$results[0]->wp_event_id));
		}

        // Remove event from queue
        $wpdb->delete( $wpdb->prefix . "pp_sync_events", array( 'event_id' => $pp_event_id ) );
	}

	/**
	 * Update one event, which going from PP to Plugin website
	 *
	 * @param $pp_event_id
	 * @param $access_token
	 * @param $wp_event_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function pptec_technical_update_one_event( $pp_event_id, $access_token, $wp_event_id )
    {
        if (empty($pp_event_id) || empty($access_token) || empty($wp_event_id)) {
            return false;
        }

        $post_fields = 'section=events&action=event_read&id=' . $pp_event_id;
        $remote_data = pptec_get_remote_data($post_fields);
        if (is_wp_error($remote_data) || empty($remote_data["body"])) {
            return false;
        }
        $events_response = json_decode($remote_data["body"]);

        if (empty($events_response->data->id)) {
            return false;
        }

        wp_update_post(array(
            'ID' => $wp_event_id,
            'post_title' => addslashes($events_response->data->name),
            'post_content' => addslashes($events_response->data->descr),
            'post_excerpt' => addslashes($events_response->data->short_descr)
        ));

        // Sanitize prices array
        $prices_inc = !empty($events_response->data->price) ? $this->sanitizeArray($events_response->data->price) : array();
        $prices = array();
        foreach ($prices_inc as $key => $item) {
            $prices['seed_' . $key] = $item;
        }

        // Sanitize custom delivery object
        $custom_delivery = !empty($events_response->data->custom_delivery) ? $this->sanitizeArray(addslashes($events_response->data->custom_delivery)) : array();

        // Sanitize coupons array
        $coupons = !empty($events_response->data->coupons) ? $this->sanitizeArray($events_response->data->coupons) : array();

        // Sanitize questions array
        $questions = !empty($events_response->data->questions) ? $this->sanitizeArray($events_response->data->questions) : array();

        $pp_venue_id = !empty($events_response->data->venue_location_id) ? (int)$events_response->data->venue_location_id : 0;

        $pp_enabled_ticket_sales = get_post_meta($wp_event_id, 'pp_enabled_ticket_sales', true);
        if ('' === $pp_enabled_ticket_sales) {
            $pp_enabled_ticket_sales = 1;
        }

        $pp_event_data = array(
            'data' => array(
                // Whether to show PP form in ECP event form
                'pp_enabled_ticket_sales' => (int)$pp_enabled_ticket_sales,

                // Basic info
                'name' => !empty($events_response->data->name) ? addslashes($events_response->data->name) : '',
                'descr' => !empty($events_response->data->descr) ? addslashes($events_response->data->descr) : '',
                'short_descr' => !empty($events_response->data->short_descr) ? addslashes($events_response->data->short_descr) : '',
                'startson' => !empty($events_response->data->startson) ? sanitize_text_field(addslashes($events_response->data->startson)) : '',
                'endsat' => !empty($events_response->data->endsat) ? sanitize_text_field(addslashes($events_response->data->endsat)) : '',
                'ages' => !empty($events_response->data->ages) ? sanitize_text_field(addslashes($events_response->data->ages)) : 'All Ages',
                'sales_start' => !empty($events_response->data->sales_start) ? sanitize_text_field(addslashes($events_response->data->sales_start)) : '',
                'sales_stop' => !empty($events_response->data->sales_stop) ? sanitize_text_field(addslashes($events_response->data->sales_stop)) : '',
                'doorsopen' => !empty($events_response->data->doorsopen) ? sanitize_text_field(addslashes($events_response->data->doorsopen)) : '',

                // List as an upcoming event on Purplepass
                'hidden' => !empty($events_response->data->hidden) ? (int)$events_response->data->hidden : 0,

                // Require a password to purchase tickets for this event
                'passwded' => !empty($events_response->data->passwded) ? (int)$events_response->data->passwded : 0,
                'passwd' => !empty($events_response->data->passwd) ? esc_html(addslashes($events_response->data->passwd)) : '',

                // Event category
                'category_1' => !empty($events_response->data->category_1) ? sanitize_text_field(addslashes($events_response->data->category_1)) : '',

                // Venue location
                'venue_location_id' => $pp_venue_id,
                'venue' => !empty($events_response->data->venue) ? addslashes($events_response->data->venue) : '',
                'capacity' => !empty($events_response->data->capacity) ? (int)$events_response->data->capacity : 0,
                'addr' => !empty($events_response->data->addr) ? addslashes($events_response->data->addr) : '',
                'city' => !empty($events_response->data->city) ? addslashes($events_response->data->city) : '',
                'state' => !empty($events_response->data->state) ? (int)$events_response->data->state : -1,
                'zip' => !empty($events_response->data->zip) ? addslashes($events_response->data->zip) : 0,
                'country' => !empty($events_response->data->country) ? (int)$events_response->data->country : 1,
                'timezone' => !empty($events_response->data->timezone) ? (int)$events_response->data->timezone : 0,

                // Pricing
                'price_type' => !empty($events_response->data->price_type) ? esc_html($events_response->data->price_type) : 0,
                'price' => $prices,

                // ASE
                'venue_id' => !empty($events_response->data->venue_id) ? (int)$events_response->data->venue_id : 0,
                'venue_name' => !empty($events_response->data->venue_name) ? addslashes($events_response->data->venue_name) : '',
                'items_type' => !empty($events_response->data->items_type) ? (int)$events_response->data->items_type : 0,
                'pp_venue_map_name' => (!empty($events_response->data->venue_name)) ? addslashes($events_response->data->venue_name) : '',

                // Ticket delivery options
                'delivery_expr' => !empty($events_response->data->delivery_expr) ? (int)$events_response->data->delivery_expr : 0,
                'delivery_prior' => !empty($events_response->data->delivery_prior) ? (int)$events_response->data->delivery_prior : 0,
                'delivery_wcall' => !empty($events_response->data->delivery_wcall) ? (int)$events_response->data->delivery_wcall : 0,
                'delivery_home' => !empty($events_response->data->delivery_home) ? (int)$events_response->data->delivery_home : 0,
                'delivery_usps' => !empty($events_response->data->delivery_usps) ? (int)$events_response->data->delivery_usps : 0,
                'delivery_custom' => !empty($events_response->data->delivery_custom) ? (int)$events_response->data->delivery_custom : 0,
                'custom_delivery' => (object)$custom_delivery,

                // Sales Tax
                'stax' => !empty($events_response->data->stax) ? (int)$events_response->data->stax : 0,
                'ffee' => !empty($events_response->data->ffee) ? (int)$events_response->data->ffee : 0,
                'ffee_split' => !empty($events_response->data->ffee_split) ? (int)$events_response->data->ffee_split : 0,
                'ffee_flat' => !empty($events_response->data->ffee_flat) ? (float)$events_response->data->ffee_flat : 0,
                'ffee_perc' => !empty($events_response->data->ffee_perc) ? (float)$events_response->data->ffee_perc : 0,
                'ffee_min' => !empty($events_response->data->ffee_min) ? (float)$events_response->data->ffee_min : 0,
                'stax_perc' =>  !empty($events_response->data->stax_perc) ? (float)$events_response->data->stax_perc : 0,

                // Coupons
                'require_coupons' => !empty($events_response->data->require_coupons) ? (int)$events_response->data->require_coupons : 0,
                'coupons' => $coupons,

                // Additional Options

                // Custom notice or terms & conditions to the transaction
                'terms_enable' => !empty($events_response->data->terms_enable) ? (int)$events_response->data->terms_enable : 0,
                'terms_require' => !empty($events_response->data->terms_require) ? (int)$events_response->data->terms_require : 0,
                'terms' => !empty($events_response->data->terms) ? esc_html(addslashes($events_response->data->terms)) : '',

                // Enable Facebook Options
                'fb_page_url' => !empty($events_response->data->fb_page_url) ? esc_url(addslashes($events_response->data->fb_page_url)) : '',
                'fb_login' => !empty($events_response->data->fb_login) ? (int)$events_response->data->fb_login : 0,
                'fb_checkin' => !empty($events_response->data->fb_checkin) ? (int)$events_response->data->fb_checkin : 0,
                'fb_tell' => !empty($events_response->data->fb_tell) ? (int)$events_response->data->fb_tell : 0,
                'require_fb_like' => !empty($events_response->data->require_fb_like) ? (int)$events_response->data->require_fb_like : 0,

                // Personalized message
                'receipt_enable' => !empty($events_response->data->receipt_enable) ? (int)$events_response->data->receipt_enable : 0,
                'receipt' => !empty($events_response->data->receipt) ? esc_html(addslashes($events_response->data->receipt)) : '',

                // Provide special note or instructions to Purplepass telephone support staff
                'operator_info_enable' => !empty($events_response->data->operator_info_enable) ? (int)$events_response->data->operator_info_enable : 0,
                'operator_info' => !empty($events_response->data->operator_info) ? esc_html(addslashes($events_response->data->operator_info)) : '',

                // Questions
                'questions_enable' => !empty($events_response->data->questions_enable) ? (int)$events_response->data->questions_enable : 0,
                'questions' => $questions,

                // Email templates
                'custom_tpl_id' => !empty($events_response->data->custom_tpl_id) ? (int)$events_response->data->custom_tpl_id : 0,
                'custom_tpl_name' => !empty($events_response->data->custom_tpl_name) ? esc_html(addslashes($events_response->data->custom_tpl_name)) : '',

                // Print-At-Home templates
                'custom_pah_id' => !empty($events_response->data->custom_pah_id) ? (int)$events_response->data->custom_pah_id : 0,
                'custom_pah_name' => !empty($events_response->data->custom_pah_name) ? esc_html(addslashes($events_response->data->custom_pah_name)) : '',
            ),
        );

        update_post_meta( $wp_event_id, 'pptec_event_meta_data', (object)$pp_event_data );
        update_post_meta( $wp_event_id, 'pp_enabled_ticket_sales', $pp_event_data['data']['pp_enabled_ticket_sales'] );
		update_post_meta( $wp_event_id, '_EventStartDate', $events_response->data->startson );
		update_post_meta( $wp_event_id, '_EventEndDate', $events_response->data->endsat );
        $venues_bindings = array();
        $wp_venue_id = $this->pptec_process_venue_locations(get_current_user_id(), $events_response->data, $venues_bindings);
        if ($wp_venue_id && $pp_venue_id) {
            update_post_meta($wp_event_id, '_EventVenueID', $wp_venue_id);
            pptec_bind_pp_venue_id_to_wp_venue_id($pp_venue_id, $wp_venue_id, $pp_event_id, $wp_event_id);
        }
	}

    private function sanitizeArray($array)
    {
        if (!is_array($array)) {
            $array = (array)$array;
        }

        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $array[$key] = $this->sanitizeArray((array)$value);
            } else {
                $array[$key] = esc_html(addslashes($value));
            }
        }

        return $array;
    }



	public function pptec_process_venue_locations($wp_user_id, $pp_event_data, &$venues_bindings)
	{
		$pp_venue_id = !empty($pp_event_data->venue_location_id) ? $pp_event_data->venue_location_id : null;

		if (empty($pp_venue_id)) {
			return false;
		}

        // Get wp country and state ids by string values from PP event
        $country_id = (int)$pp_event_data->country > 0 ? $pp_event_data->country : 1;
        $state_id = (int)$pp_event_data->state >= 0 ? $pp_event_data->state : -1;

        // Source countries
        $country_array = pptec_get_countries_list(true);

        // State/Province name
        if (-1 === (int)$state_id) {
            $state_code = '-';
            $province_name = '';
        } else {
            // Source states (US) or provinces (Canada)
            if (1 === (int)$country_id) { // US
                $states_array = pptec_get_us_states_list(true);
                $states_array_shortcodes = pptec_get_us_states_list(true, true);
            } elseif (8 === (int)$country_id) { // Canada
                $states_array = pptec_get_canada_provinces_list(true);
                $states_array_shortcodes = pptec_get_canada_provinces_list(true, true);
            }

            $state_code = !empty($states_array_shortcodes[$state_id]) ? $states_array_shortcodes[$state_id] : '-';
            $province_name = !empty($states_array[$state_id]) ? $states_array[$state_id] : '';
        }

        // Prepare venue metadata coming from PP
        $wp_venue_metadata_inc = array(
            '_VenueName' => !empty($pp_event_data->venue) ? addcslashes(str_replace('\\\'', '\'', $pp_event_data->venue), '\\') : '',
            '_VenueAddress' => !empty($pp_event_data->addr) ? addcslashes(str_replace('\\\'', '\'', $pp_event_data->addr), '\\') : '',
            '_VenueCity' => !empty($pp_event_data->city) ? addcslashes(str_replace('\\\'', '\'', $pp_event_data->city), '\\') : '',
            '_VenueZip' => !empty($pp_event_data->zip) ? $pp_event_data->zip : '',
            '_VenueCountry' => !empty($country_array[$country_id]) ? $country_array[$country_id] : '',
            '_VenueState' => $state_code,
            '_VenueProvince' => $province_name
        );

		// Get linked wp_venue_id if exists
		$wp_venue_id = pptec_get_wp_venue_id_by_pp_venue_id($pp_venue_id);

		if (empty($wp_venue_id)) {
            // Create a new wp_venue
            $wp_venue_id = $this->pptec_create_wp_venue($wp_user_id, $pp_event_data);
            if ($wp_venue_id) {
                $wp_venue_metadata = array_merge($wp_venue_metadata_inc, array('id' => $wp_venue_id));

                // Store venue metadata in WP
                $this->pptec_update_wp_venue_metadata($wp_venue_metadata);

                // Store venue data in bindings array
                $venues_bindings[$pp_venue_id] = $wp_venue_metadata;
            }
		} else {
            // Compare the data about venue we received with PP event and the data stored in WP database.
            // If they are different, we will update WP database.

            // Firstly, check if this pp_venue_id has been already processed during current `pptec_create_update_event` call
            $wp_venue_metadata_local = !empty($venues_bindings[$pp_venue_id]) ? $venues_bindings[$pp_venue_id] : array();

            if (empty($wp_venue_metadata_local)) {
                // If not, get metadata from WP db
                $wp_venue_metadata_local_temp = get_post_meta($wp_venue_id);
                foreach ($wp_venue_metadata_local_temp as $key => $value) {
                    $wp_venue_metadata_local[$key] = $value[0];
                }

                $wp_venue_metadata_local['_VenueName'] = get_the_title($wp_venue_id);
            }

            // Now compare wp and pp venue data
            if (
                (isset($wp_venue_metadata_local['_VenueName']) && $wp_venue_metadata_local['_VenueName'] !== $wp_venue_metadata_inc['_VenueName'])
                || (isset($wp_venue_metadata_local['_VenueAddress']) && $wp_venue_metadata_local['_VenueAddress'] !== $wp_venue_metadata_inc['_VenueAddress'])
                || (isset($wp_venue_metadata_local['_VenueCity']) && $wp_venue_metadata_local['_VenueCity'] !== $wp_venue_metadata_inc['_VenueCity'])
                || (isset($wp_venue_metadata_local['_VenueZip']) && $wp_venue_metadata_local['_VenueZip'] !== $wp_venue_metadata_inc['_VenueZip'])
                || (isset($wp_venue_metadata_local['_VenueCountry']) && $wp_venue_metadata_local['_VenueCountry'] !== $wp_venue_metadata_inc['_VenueCountry'])
                || (isset($wp_venue_metadata_local['_VenueState']) && $wp_venue_metadata_local['_VenueState'] !== $wp_venue_metadata_inc['_VenueState'])
                || (isset($wp_venue_metadata_local['_VenueProvince']) && $wp_venue_metadata_local['_VenueProvince'] !== $wp_venue_metadata_inc['_VenueProvince'])
            ) {
                // Data is not identical. Update existing WP venue
                // We use incoming venue metadata. Metadata stored in WP db will be overwritten
                $wp_venue_metadata = array_merge($wp_venue_metadata_inc, array('id' => $wp_venue_id));

                $this->pptec_update_wp_venue_metadata($wp_venue_metadata);

                // Store venue data in bindings array
                $venues_bindings[$pp_venue_id] = $wp_venue_metadata;
            }
        }

		return $wp_venue_id;
	}

	public function pptec_create_wp_venue($wp_user_id, $pp_event_data)
	{
		$post_data = array(
			'post_title'   => $pp_event_data->venue,
			'post_type'    => 'tribe_venue',
			'post_content' => ' ',
			'post_status'  => 'publish',
			'post_author'  => (int)$wp_user_id,
		);

		$wp_venue_id = wp_insert_post( $post_data );

		return $wp_venue_id;
	}

	public function pptec_update_wp_venue_metadata($venue_data)
	{
        if (isset($venue_data['_VenueName'])) {
            wp_update_post(array(
                'ID' => $venue_data['id'],
                'post_title' => addslashes($venue_data['_VenueName']),
            ));
        }
		if (isset($venue_data['_VenueAddress'])) {
			update_post_meta($venue_data['id'], '_VenueAddress', $venue_data['_VenueAddress']);
		}
		if (isset($venue_data['_VenueCity'])) {
			update_post_meta($venue_data['id'], '_VenueCity', $venue_data['_VenueCity']);
		}
		if (isset($venue_data['_VenueCountry'])) {
			update_post_meta($venue_data['id'], '_VenueCountry', $venue_data['_VenueCountry']);
		}
		if (isset($venue_data['_VenueState'])) {
			update_post_meta($venue_data['id'], '_VenueState', $venue_data['_VenueState']);
		}
        if (isset($venue_data['_VenueProvince'])) {
            update_post_meta($venue_data['id'], '_VenueProvince', $venue_data['_VenueProvince']);
            update_post_meta($venue_data['id'], '_VenueStateProvince', $venue_data['_VenueProvince']);
        }
        if (isset($venue_data['_VenueZip'])) {
			update_post_meta($venue_data['id'], '_VenueZip', $venue_data['_VenueZip']);
        }

        if (isset($venue_data['_VenueName'])) {
            wp_update_post(array(
                'ID' => $venue_data['id'],
                'post_title' => $venue_data['_VenueName'],
                'post_name' => $venue_data['_VenueName']
            ));
        }
	}
}

new Purplepass_ECP();
