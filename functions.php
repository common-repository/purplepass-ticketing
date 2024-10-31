<?php

require_once 'inc/PPTEC_CustomCrypt.php';
require_once 'inc/Cron.php';

function pptec_jx_delete_event()
{
	$json = array(
        'success' => array(),
        'failed' => array()
    );

    $pp_user_id = pptec_oauth_get_pp_user_id();

    $wp_event_ids = !empty($_POST['ids']) ? $_POST['ids'] : array();
    foreach ($wp_event_ids as $wp_event_id) {
        $pp_event_id = pptec_get_pp_event_id_by_wp_event_id($wp_event_id);
        $wp_event = get_post($wp_event_id);

        if ('canceled' === $wp_event->post_status) {
            $json['failed'][$wp_event_id] = 'Unable to cancel  "' . $wp_event->post_title . '": event is already cancelled.';
            continue;
        }

        $wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id($wp_event_id);
        if ($wp_event_pp_user_id && (int)$pp_user_id !== (int)$wp_event_pp_user_id) {
            $json['failed'][$wp_event_id] = 'Unable to cancel  "' . $wp_event->post_title . '": event does not belong to you.';
            continue;
        }

        $result = pptec_cancel_event_common($pp_event_id, $wp_event_id);
        if (empty($result['success'])) {
            $json['failed'][$wp_event_id] = 'Unable to cancel "' . $wp_event->post_title . '": ' . $result['message'];
        } else {
            $json['success'][$wp_event_id] = 'Successfully cancelled "' . $wp_event->post_title . '"';
        }
	}

    echo json_encode($json);
    die;
}
add_action('wp_ajax_pptec_jx_delete_event', 'pptec_jx_delete_event');

/**
 * Build proper data from a string
 *
 * @param $date
 *
 * @return false|int|string
 */
function pptec_build_proper_data( $date ) {
	$date = str_replace( 'undefined', '00', $date );
	$date = str_replace( '-', '/', $date );
	$date = strtotime( $date );
	$date = date( "Y-m-d H:i:s", $date );

	return $date;
}

function pp_email_template($value, $name) {
	$template = array();
	if ($value && $name) {
		$template['value'] = $value;
		$template['name'] = $name;
	} else {
		$template['value'] = 0;
		$template['name'] = "Default template (Purplepass)";
	}

	return $template;
}

/**
 * @param $post_fields
 *
 * @return array|WP_Error
 */
function pptec_get_remote_data( $post_fields ){

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

	return wp_remote_get( PPTEC_WEB_URL . '/api/', $args );
}


/**
 * @param $prices
 *
 * @return array
 */
function pptec_prices_adding_options( $prices ) {

	if ( is_array( $prices ) ) {
		foreach ( $prices as $key => $item ) {
			foreach ( $item as $k => $j ) {
				if ( is_numeric( $k ) ) {
					unset( $prices[ $key ][ $k ] );
				}

				// template id
				if ( ! isset( $prices[ $key ]['tpl_id'] ) ) {
					$prices[ $key ]['tpl_id'] = "0";
				}

				// data seed
				if ( ! isset( $prices[ $key ]['data_seed'] ) ) {
					$prices[ $key ]['data_seed'] = "seed_" . $key;
				}
				// id
				if ( ! isset( $prices[ $key ]['id'] ) ) {
					$prices[ $key ]['id'] = "seed_" . $key;
				}
				// venue ID
				if ( ! isset( $prices[ $key ]['status_message'] ) ) {
					$prices[ $key ]['status_message'] = "";
				}
				// status
				if ( ! isset( $prices[ $key ]['bg_color_from'] ) ) {
					$prices[ $key ]['bg_color_from'] = "";
				}
				// timezone
				if ( ! isset( $prices[ $key ]['bg_color_to'] ) ) {
					$prices[ $key ]['bg_color_to'] = "";
				}
				// sartson
				if ( ! isset( $prices[ $key ]['ffee_flat'] ) ) {
					$prices[ $key ]['ffee_flat'] = "0.00";
				}
				// andstat
				if ( ! isset( $prices[ $key ]['ffee_perc'] ) ) {
					$prices[ $key ]['ffee_perc'] = "0.00";
				}
				if ( ! isset( $prices[ $key ]['ffee_min'] ) ) {
					$prices[ $key ]['ffee_min'] = "0.00";
				}
				if ( ! isset( $prices[ $key ]['stax_perc'] ) ) {
					$prices[ $key ]['stax_perc'] = "0.00";
				}
				if ( ! isset( $prices[ $key ]['min'] ) ) {
					$prices[ $key ]['min'] = "0";
				}
				if ( ! isset( $prices[ $key ]['limit'] ) ) {
					$prices[ $key ]['min'] = "0";
				}
				if ( ! isset( $prices[ $key ]['def_ticket_color'] ) ) {
					$prices[ $key ]['def_ticket_color'] = "0";
				}
				if ( ! isset( $prices[ $key ]['reset_time'] ) ) {
					$prices[ $key ]['reset_time'] = "0000-00-00 00:00:00";
				}
				if ( ! isset( $prices[ $key ]['reset_time'] ) ) {
					$prices[ $key ]['reset_time'] = date( 'Y-m-d H:i:s', time() );
				}
				if ( ! isset( $prices[ $key ]['options'] ) ) {
					$prices[ $key ]['options'] = 0;
				}
				if ( ! isset( $prices[ $key ]['capacity'] ) ) {
					$prices[ $key ]['capacity'] = 0;
				}
				if ( ! isset( $prices[ $key ]['price_options'] ) ) {
					$prices[ $key ]['price_options'] = 0;
				}
				if ( ! isset( $prices[ $key ]['require_coupons'] ) ) {
					$prices[ $key ]['require_coupons'] = 0;
				}
				if ( ! isset( $prices[ $key ]['quantity_sold'] ) ) {
					$prices[ $key ]['quantity_sold'] = 0;
				}
				if ( ! isset( $prices[ $key ]['ages'] ) ) {
					$prices[ $key ]['ages'] = '';
				}
				if ( ! isset( $prices[ $key ]['startson'] ) ) {
					$prices[ $key ]['startson'] = '';
				}
				if ( ! isset( $prices[ $key ]['quantity_refunded'] ) ) {
					$prices[ $key ]['quantity_refunded'] = '0';
				}
				if ( ! isset( $prices[ $key ]['quantity_guestlist'] ) ) {
					$prices[ $key ]['quantity_guestlist'] = '0';
				}
				if ( ! isset( $prices[ $key ]['quantity_ticketstock'] ) ) {
					$prices[ $key ]['quantity_ticketstock'] = '0';
				}
				if ( ! isset( $prices[ $key ]['delivery'] ) ) {
					$prices[ $key ]['delivery'] = '0';
				}
				if ( ! isset( $prices[ $key ]['bo_color'] ) ) {
					$prices[ $key ]['bo_color'] = '';
				}
				if ( ! isset( $prices[ $key ]['custom_pah_name'] ) ) {
					$prices[ $key ]['custom_pah_name'] = null;
				}
				if ( ! isset( $prices[ $key ]['cma_id'] ) ) {
					$prices[ $key ]['cma_id'] = null;
				}
				if ( ! isset( $prices[ $key ]['d_rate'] ) ) {
					$prices[ $key ]['d_rate'] = null;
				}
				if ( ! isset( $prices[ $key ]['tr_fee'] ) ) {
					$prices[ $key ]['tr_fee'] = null;
				}
				if ( ! isset( $prices[ $key ]['ma_status'] ) ) {
					$prices[ $key ]['ma_status'] = null;
				}
				if ( ! isset( $prices[ $key ]['idx'] ) ) {
					$prices[ $key ]['idx'] = '4';
				}
				if ( ! isset( $prices[ $key ]['ssp_options'] ) ) {
					$prices[ $key ]['ssp_options'] = '0';
				}
				if ( ! isset( $prices[ $key ]['flex'] ) ) {
					$prices[ $key ]['flex'] = '0';
				}
				if ( ! isset( $prices[ $key ]['rules'] ) ) {
					$prices[ $key ]['rules'] = array();
				}
				if ( ! isset( $prices[ $key ]['group'] ) ) {
					$prices[ $key ]['group'] = array();
				}
				if ( ! isset( $prices[ $key ]['sp'] ) ) {
					$prices[ $key ]['sp'] = array();
				}
				if ( ! isset( $prices[ $key ]['require_tts'] ) ) {
					$prices[ $key ]['require_tts'] = array();
				}
				if ( ! isset( $prices[ $key ]['epp'] ) ) {
					$prices[ $key ]['epp'] = false;
				}
			}
		}
	}

	return $prices;
}


/**
 * Save widget settings from Plugin settings page, ajax processing
 */
function pptec_save_widget_settings()
{
    // ajax checking
    $nonce = $_POST['nonce'];
    if (!wp_verify_nonce($nonce, 'ajax-nonce')) {
        die ('Incorrect nonce!');
    }

    $plugin_option = get_option('pptec_widget_settings');
    $plugin_option['widget_color'] = !empty($_POST['widget_color']) ? sanitize_text_field($_POST['widget_color']) : 'cccccc';
    $plugin_option['widget_help_text'] = !empty($_POST['widget_help_text']) ? sanitize_text_field($_POST['widget_help_text']) : '';
    $plugin_option['widget_width'] = !empty($_POST['widget_width']) ? sanitize_text_field($_POST['widget_width']) : '';
    $plugin_option['enabled_cart'] = !empty($_POST['enabled_cart']) ? sanitize_text_field($_POST['enabled_cart']) : false;
    $plugin_option['cbx_replace'] = !empty($_POST['cbx_replace']) && 'true' === $_POST['cbx_replace'] ? true : false;
    update_option('pptec_widget_settings', $plugin_option);

    echo '<span class="green">Saved</span>';
    die();
}
add_action( 'wp_ajax_save_widget_settings', 'pptec_save_widget_settings' );

/**
 * Cron job. Checks if there is unfinished fetching.
 * We assume fetching process will not run longer that 5 minutes.
 * Therefore this cron runs every 5 minutes after the process started.
 * If we find fetching still running, that means something went wrong and we should start another fetch.
 * Otherwise, we remove this cron job from queue.
 */
function pptec_checking_unfinished_background_jobs($wp_user_id) {
	$job_events_fetching = get_option( 'pptec_job_events_fetching' );

	if ( !empty($job_events_fetching['status']) && 'finished' !== $job_events_fetching['status'] ) {
		pptec_fetch_event_processing($wp_user_id);
	} else {
		wp_unschedule_event( wp_next_scheduled( 'pptec_check_bg_failed_process' ), 'pptec_check_bg_failed_process' );
	}
}

//// Bump request timeout
//add_action('http_api_curl', 'sar_custom_curl_timeout', 9999, 1);
//function sar_custom_curl_timeout($handle)
//{
//	curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 60 );
//	curl_setopt( $handle, CURLOPT_TIMEOUT, 60 );
//}
//
//add_filter( 'http_request_timeout', 'pptec_bump_request_timeout', 9999 );
//function pptec_bump_request_timeout()
//{
//	return 60;
//}
//
//add_filter('http_request_args', 'sar_custom_http_request_args', 9999, 1);
//function sar_custom_http_request_args($r)
//{
//	$r['timeout'] = 60;
//	return $r;
//}


/**
 * Sync method, Get all events from PP account
 */
function pptec_get_events_from_pp() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!');
	}

	$pp_obj        = new Purplepass_ECP();
	$user_id       = get_current_user_id();
	$access_token  = pptec_get_access_token();

	if ( ! empty( $access_token ) ) {
		update_option( 'pptec_job_events_fetching', array(
			'status'  => 'initialized',
			'time'    => time(),
			'total' => -1,
			'added' => -1,
			'updated' => -1,
			'message' => 'Initializing...',
		) );

		$res = false;

		// fetching events
        $cron_args = array($user_id);
        if (!wp_next_scheduled('pptec_cron_fetch_event_processing')) {
            $res = wp_schedule_single_event(time(), 'pptec_cron_fetch_event_processing', $cron_args);
        }

//        if (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON) {
//			$key = md5( serialize( $cron_args ) );
//			$cron_spawned = pptec_force_cron_spawn('pptec_cron_fetch_event_processing', $key);
//        }

		echo json_encode( array('message' => $res ? 'Initializing...' : '') );
		die;
	} else {
		// add notification to log system
		$pp_obj->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Synchronization', 'Inbound', 'Synchronization failed. Access token expired or not exists', 'Failed' );

		$job_events_fetching = array(
            'status' => 'failed',
            'time' => time(),
			'total' => 0,
			'added' => 0,
			'updated' => 0,
            'message' => 'Invalid access token'
		);
		update_option( 'pptec_job_events_fetching', $job_events_fetching );

		echo json_encode( $job_events_fetching );
		die;
	}
}
add_action( 'wp_ajax_get_events_from_pp', 'pptec_get_events_from_pp' );


function pptec_filter_ready_cron_jobs()
{
    if (!isset($_GET['pptec_filter_ready_cronjobs'])) {
        return null; // null because then WP can proceed with default routine in wp-cron.php
    }

	$crons = _get_cron_array();

	if ( false === $crons ) {
		return array();
	}

	$gmt_time = microtime( true );
	$keys     = array_keys( $crons );
	if ( isset( $keys[0] ) && $keys[0] > $gmt_time ) {
		return array();
	}

	$results = array();
	foreach ( $crons as $timestamp => $cronhooks ) {
		if ( $timestamp > $gmt_time ) {
			break;
		}

		if ( isset( $cronhooks['pptec_cron_fetch_event_processing'] ) ) {
			$results[ $timestamp ] = $cronhooks;
		}
	}

	return $results;
}
add_filter('pre_get_ready_cron_jobs', 'pptec_filter_ready_cron_jobs');

/**
 * Listener for the PP website callbacks
 */
function pptec_listener_for_events()
{
    if (isset($_REQUEST['event_id']) && isset($_REQUEST['action'])) {
        $pp_event_id = sanitize_text_field($_REQUEST['event_id']);
        $sent_event_action = sanitize_text_field($_REQUEST['action']);

        if (!empty($pp_event_id) && !empty($sent_event_action)) {
            global $wpdb;

            // load all events from PP, if not exists - add to site
            $results = $wpdb->get_results("SELECT * FROM  {$wpdb->prefix}pp_sync_events WHERE event_id='$pp_event_id' AND `action`='$sent_event_action'");
            if (empty($results)) {
                $wpdb->insert(
                    $wpdb->prefix . "pp_sync_events",
                    array(
                        'event_id' => $pp_event_id,
                        'action' => $sent_event_action,
                    ),
                    array(
                        '%d',
                        '%s',
                    )
                );
            }

            $pp_obj = new Purplepass_ECP();

            $pp_user_id = pptec_get_pp_user_id_by_pp_event_id($pp_event_id);
            if (!$pp_user_id) {
                echo json_encode(array('success' => false));
                die;
            }

            $wp_user_id = pptec_get_wp_user_id_by_pp_user_id($pp_user_id);
            if (!$wp_user_id) {
                echo json_encode(array('success' => false));
                die;
            }

            switch ($sent_event_action) {
                case 'event_add':
                case 'event_update':
                    $pp_obj->pptec_create_update_event($wp_user_id, $pp_event_id);
                    break;

                case 'event_delete':
                    $pp_obj->pptec_delete_event($pp_event_id);
                    break;

                case 'event_cancel':
                    $pp_obj->pptec_cancel_event($pp_event_id);
                    break;
            }
        }

        echo json_encode(array('success' => true));
        die;
    }
}
if ( isset( $_REQUEST['event_pp_listener'] ) ) {
	pptec_listener_for_events();
    pptec_listener_for_token();
}



/**
 * Get token listener
 *
 * @return bool|void
 */
function pptec_listener_for_token(){
	// Handle the callback from the server is there is one.
	if ( isset( $_GET['code'] ) && isset( $_GET['client_id'] ) ) {
		$pp_obj         = new Purplepass_ECP();
		$code_verifier  = get_option( 'pptec_random_str' );
		if (empty($code_verifier)) {
			$pp_obj->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Getting token', 'Inbound', 'There was some issue with receiving token. Please, try to reconnect plugin to your account. (Error code: verifier)', 'Failed' );
			header( 'Location: ' . admin_url( 'admin.php?page=purplepass&token_errors=1' ) );
			exit();
        }

		$code_challenge = strtr( rtrim( base64_encode( hash( 'sha256', $code_verifier, true ) ), '=' ), '+/', '-_' );
		$client_id      = PPTECCustomCrypt::pptec_decrypt( sanitize_text_field( $_GET['client_id'] ), $code_challenge );

		if (empty($client_id)) {
			$pp_obj->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Getting token', 'Inbound', 'There was some issue with receiving token. Please, try to reconnect plugin to your account. (Error code: client_id)', 'Failed' );
			header( 'Location: ' . admin_url( 'admin.php?page=purplepass&token_errors=1' ) );
			exit();
        }

		update_option( 'pptec_oauth_decrypted_client_id', $client_id );
		$redirect_uri = get_site_url() . '/?event_pp_listener=true';
		$code         = sanitize_text_field( $_GET['code'] );
		$server_url   = PPTEC_WEB_URL . '/actions/oauth_token.php';
		$response     = wp_remote_post( $server_url, array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => array(
				'grant_type'    => 'authorization_code',
				'code'          => $code,
				'client_id'     => $client_id,
				'code_verifier' => $code_verifier,
				'redirect_uri'  => $redirect_uri,
			),
			'cookies'     => array(),
			'sslverify'   => false,
		) );

		if ( is_wp_error( $response ) ) {
			$pp_obj->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Getting token', 'Inbound', 'There was some issue with receiving token. Please, try to reconnect plugin to your account. ' . json_encode($response), 'Failed' );
			header( 'Location: ' . admin_url( 'admin.php?page=purplepass&token_errors=1' ) );
			exit();
		}
		$token_response = '';
		if ( !is_wp_error( $response ) && !empty( $response["body"] ) ) {
			$token_response = json_decode($response["body"]);
			if ( !empty( $token_response->error ) ) {
				$pp_obj->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Getting token', 'Inbound', $token_response->error . ', ' . $token_response->hint . ', ' . $token_response->message . '. Please, try to reconnect plugin to your account ', 'Failed' );
				header( 'Location: ' . admin_url( 'admin.php?page=purplepass&token_errors=1' ) );
				exit();
			}
		}
		if ( empty( $response["body"] ) || empty( $token_response ) ) {
			$pp_obj->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Getting token', 'Inbound', 'There was some issue with receiving token. Please, try to reconnect plugin to your account. ' . json_encode($response), 'Failed' );
			header( 'Location: ' . admin_url( 'admin.php?page=purplepass&token_errors=1' ) );
			exit();
		}

		$access_token                        = ( ! empty( $token_response->access_token ) ) ? $token_response->access_token : '';
		$expires_in                          = ( ! empty( $token_response->expires_in ) ) ? $token_response->expires_in : '';
		$token_type                          = ( ! empty( $token_response->token_type ) ) ? $token_response->token_type : '';
		$pp_user_id                          = ( ! empty( $token_response->user_id ) ) ? $token_response->user_id : '';
		$email                               = ( ! empty( $token_response->email ) ) ? $token_response->email : '';
		$plugin_option                       = get_option( 'pptec_oauth_settings' );
		if ( !empty( $plugin_option ) ) {
			$plugin_option = (array)json_decode( $plugin_option );
		} else {
			$plugin_option = array();
		}
		$plugin_option['access_token']       = $access_token;
		$plugin_option['expires_in']         = $expires_in;
		$plugin_option['token_created_time'] = time();
		$plugin_option['token_type']         = $token_type;
		$plugin_option['pp_user_id']         = $pp_user_id;
		$plugin_option['pp_user_email']      = $email;

		if ( empty( $token_response->refresh_token ) ) {
			$pp_obj->pptec_add_log( date( 'Y-m-d H:i:s', time() ), 'Getting token', 'Inbound', 'There was some issue with receiving token, please, try to authorize again', 'Failed' );

			return;
		}

		update_option( 'pptec_oauth_refresh_token', $token_response->refresh_token );
		update_option( 'pptec_oauth_settings', json_encode( $plugin_option ) );

		// Link pp_user_id to wp_user_id
		if ($pp_user_id) {
            update_user_meta(get_current_user_id(), 'pptec_pp_user_id', $pp_user_id);
        }

		header( 'Location: ' . admin_url( 'admin.php?page=purplepass&start_load_events=1' ) );
		exit();
	}
}

/**
 * Replace standard ECP (Event Calendar PRO) content on single event page by Purplepass widget
 * This settings located on the Purplepass events settings page
 */
add_filter( 'the_content', 'replace_ecp_event_content_with_purplepass_widget' );
function replace_ecp_event_content_with_purplepass_widget($content)
{
    if (is_singular(array('tribe_events'))) {
        $pptec_widget_settings = get_option('pptec_widget_settings');
        $should_replace = isset($pptec_widget_settings['cbx_replace']) ? (bool)$pptec_widget_settings['cbx_replace'] : false;
        $wp_event_id = get_the_ID();
        if (!empty($wp_event_id) && $should_replace) {
            $pp_event_id = pptec_get_pp_event_id_by_wp_event_id($wp_event_id);
            $content = '[pp_event event_id=' . $pp_event_id . ']';
        }
    }

	return $content;
}


/**
 * Disabled revision for Event calendar PRO
 */
add_action( 'admin_init', 'pptec_disable_revisions' );
function pptec_disable_revisions() {
	remove_post_type_support( 'tribe_events', 'revisions' );
}


/**
 * Validate FB url
 */
function pptec_validate_facebook_url() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!' );
	}

	$fb_page_url     = sanitize_text_field( $_REQUEST['fb_page_url'] );
	$post_fields     = 'section=events&action=check_fb_url_location&fb_page_url=' . $fb_page_url;
	$remote_response = pptec_get_remote_data( $post_fields );
	if ( is_wp_error( $remote_response ) || empty( $remote_response["body"] ) ) {
		return false;
	}
	$request = json_decode( $remote_response["body"] );

	if ( false === $request->success ) {
		wp_send_json( 'We are not able to access this Facebook URL due to its security settings.' );
	} else {
		wp_send_json( 'Facebook URL successfully checked!' );
	}
	die();

}

add_action( 'wp_ajax_validate_facebook_url', 'pptec_validate_facebook_url' );


/**
 * Get custom email templates list
 */
function pptec_get_email_templates() {
	$pp_event_id = 0;
	if ( isset($_POST['event_id']) && !empty( $_POST['event_id'] ) ) {
		$pp_event_id = pptec_get_pp_event_id_by_wp_event_id( $_POST['event_id'] );
	}
	if ( empty( $pp_event_id ) || false === $pp_event_id ) {
		$pp_event_id = 0;
	}
	$post_fields = 'section=events&action=list_tpl&event_user_id=' . pptec_oauth_get_pp_user_id() . '&event_id=' . $pp_event_id;
	$remote_data_response = pptec_get_remote_data( $post_fields );
	if ( is_wp_error( $remote_data_response ) || empty( $remote_data_response["body"] ) ) {
		return false;
	}
	$request = json_decode( $remote_data_response["body"] );
	if ( ! empty( $request ) && ! empty( $request->success ) ) {
//		$plugin_option['email_templates'] = $request->items;
//		update_option( 'pptec_data', $plugin_option );

        $selected_id = !empty($_POST['selected_id']) ? $_POST['selected_id'] : 0;

		$options_html = '<option value="0">Default template (Purplepass)</option>';
		foreach ( $request->items as $key => $item ) {
			$options_html .= '<option value="' . $item->id . '"' . ($selected_id === $item->id ? ' selected="selected"' : '') . '>' . $item->name . '</option>';
		}
		$options_html .= '<option value="create">## Create New Template ##</option>';
		echo $options_html;
	} else {
		echo '<option value="0" selected="selected">There no templates found</option>';
	}
	die;
}
add_action( 'wp_ajax_pptec_get_email_templates', 'pptec_get_email_templates' );


/**
 * Get print at home templates list
 */
function pptec_get_print_at_home_templates() {
	$pp_event_id = 0;
	if ( isset($_POST['event_id']) && !empty( $_POST['event_id'] ) ) {
		$pp_event_id = pptec_get_pp_event_id_by_wp_event_id( $_POST['event_id'] );
	}
	if ( empty( $pp_event_id ) || false === $pp_event_id ) {
		$pp_event_id = 0;
	}
	$post_fields          = 'section=events&action=list_pah&event_user_id=' . pptec_oauth_get_pp_user_id() . '&event_id=' . $pp_event_id;
	$remote_data_response = pptec_get_remote_data( $post_fields );
	if ( is_wp_error( $remote_data_response ) || empty( $remote_data_response["body"] ) ) {
		return false;
	}
	$request = json_decode( $remote_data_response["body"] );
	if ( ! empty( $request ) && ! empty( $request->success ) ) {
        $selected_id = !empty($_POST['selected_id']) ? $_POST['selected_id'] : 0;

        $options_html = '<option value="0">Default template (Purplepass)</option>';
		foreach ( $request->items as $key => $item ) {
            $options_html .= '<option value="' . $item->id . '"' . ($selected_id === $item->id ? ' selected="selected"' : '') . '>' . $item->name . '</option>';
		}
		$options_html .= '<option value="create">## Create New Template ##</option>';
		echo $options_html;
//		$plugin_option                  = get_option( 'pptec_data' );
//		$plugin_option['pah_templates'] = $request->items;
//		update_option( 'pptec_data', $plugin_option );
	} else {
		echo '<option value="0" selected="selected">There no templates found</option>';
	}
	die;
}
add_action( 'wp_ajax_pptec_get_print_at_home_templates', 'pptec_get_print_at_home_templates' );

/**
 * Generate categories list
 */
function pptec_get_categories_list_options() {

	$event_categories = array(
		'art'                   => 'Art Galleries & Exhibits',
		'business'              => 'Business & Networking',
		'comedy'                => 'Comedy',
		'music'                 => 'Concerts & Tour Dates',
		'conference'            => 'Conferences & Tradeshows',
		'learning_education'    => 'Education',
		'festivals_parades'     => 'Festivals',
		'movies_film'           => 'Film',
		'food'                  => 'Food & Wine',
		'fundraisers'           => 'Fundraising & Charity',
		'support'               => 'Health & Wellness',
		'holiday'               => 'Holiday',
		'family_fun_kids'       => 'Kids & Family',
		'books'                 => 'Literary & Books',
		'attractions'           => 'Museums & Attractions',
		'community'             => 'Neighborhood',
		'singles_social'        => 'Nightlife & Singles',
		'clubs_associations'    => 'Organizations & Meetups',
		'other'                 => 'Other & Miscellaneous',
		'outdoors_recreation'   => 'Outdoors & Recreation',
		'performing_arts'       => 'Performing Arts',
		'animals'               => 'Pets',
		'politics_activism'     => 'Politics & Activism',
		'religion_spirituality' => 'Religion & Spirituality',
		'sales'                 => 'Sales & Retail',
		'science'               => 'Science',
		'sports'                => 'Sports',
		'technology'            => 'Technology',
		'schools_alumni'        => 'University & Alumni',
	);

	$categories_list = '';
	foreach ( $event_categories as $key => $item ) {
		$categories_list .= '<option value="' . $key . '">' . $item . '</option>';
	}

	return $categories_list;
}

/**
	* Check upper limit in short description
*/
function pptec_check_upper_limit($s){
    $upper = 0;
    $arr = str_split($s);
    foreach ($arr as $symbol)
        $upper += ctype_upper($symbol);
    if ($upper * 100 / count($arr) > 30)
        return true;
}


/**
 * Get token ajax
 */
function pptec_get_access_token_ajax() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!' );
	}

	echo pptec_get_access_token();
	die();
}

add_action( 'wp_ajax_get_access_token_ajax', 'pptec_get_access_token_ajax' );


/**
 * Processing map venue rows
 */
function pptec_venue_map_processing() {

	$selected_venue_id = sanitize_text_field( $_POST['map_selected'] );
	$plugin_option       = get_option( 'pptec_data' );
	$venues              = $plugin_option['venues_list'];
	$venue_to_build      = '';
	foreach ( $venues as $key => $val ) {
		if ( $val->id === $selected_venue_id ) {
			$venue_to_build = $venues[ $key ];
		}
	}

	ob_start();
	$first_str = 0;
	foreach ( $venue_to_build->sections as $key => $item ) {
        if ( 0 === $key ) {
			$first_str = 1;
		}
		if ( $key > 0 && ( $venue_to_build->sections[$key]->section_id !== $venue_to_build->sections[$key-1]->section_id ) ) {
			$first_str = 1;
		}
		?>
		<div class="wrap-current-price one-repeater-item map-venue-added color_<?php echo str_replace( "#", "", $item->color ); ?>"
			 data-first_str="<?php echo $first_str; ?>"
			 data-venue_id="<?php echo $venue_to_build->id; ?>"
			 data-section_id="<?php echo $item->section_id; ?>"
			 data-color="<?php echo $item->color; ?>"
			 data-seed=""
			 data-price_id="<?php echo ( $key ) ? $key : 0; ?>"
			 data-section_name="<?php echo $item->section_name; ?>">
			<div class="prices-fields">
				<ul class="list-prices-field">
					<?php if ( $item->color && $item->section_name ) : ?>
						<li>
							<div style="width: 25px; height:25px; border-radius: 50%; background:<?php echo $item->color; ?>"></div>
						</li>
					<?php endif; ?>
					<?php if ( $item->type ) : ?>
						<li>
							<strong class="title-strong">Item Type</strong>
							<div class="wrap-price-select">
								<i class="fas fa-sort-down"></i>
								<select name="" class="one-type-select" >
									<option value="2">Custom</option>
									<option value="0">General Admission</option>
									<option value="1">VIP</option>
									<option value="4">Donations</option>
								</select>
							</div>
						</li>
					<?php endif; ?>
					<li>
						<strong class="title-strong name-price-title"><?php echo $item->section_name; ?></strong>
						<?php $sect_name = ( $item->name ) ? $item->name : $item->section_name; ?>
						<input type="text" placeholder="Name" class="optional new-price-name"
							   value="<?php echo $sect_name; ?>">
					</li>
					<li>
						<strong class="title-strong">Price <span class="red-ast">*</span></strong>
						<input type="text" placeholder="Price" value="" class="new-price-price">
					</li>
					<?php if ( $item->quantity ) : ?>
						<li>
							<strong class="title-strong">Qty to Sell</strong>
							<input type="text" placeholder="QTY" class="optional new-price-qty" value="">
						</li>
					<?php endif; ?>
				</ul>
				<div class="wrap-price-description">
					<i style="transform: rotate(180deg);" class="fas fa-reply"></i>
					<span class="title-strong">&nbsp; Description</span>
					<input type="text" class="optional new-price-desc" value="">
				</div>
			</div>
			<div class="prices-btn">
				<button class="option-price cust-btn-new-design">
					<i class="fas fa-cog"></i>
					<span>Options</span>
				</button>
				<button class="delete-price cust-btn-new-design" style="background-color: #ff3e6d; display: none;">
					<i class="fas fa-times-circle"></i>
					<span>Delete</span>
				</button>
				<button class="add-new-price-assigned cust-btn-new-design"
						data-color="<?php echo str_replace( "#", "", $item->color ); ?>"
						style="width: 50px;
						background: #78bf42;
						margin-top: 9px;">
					<i class="fas fa-plus"></i>
				</button>
			</div>
		</div>
    <?php }
	$prices_html = ob_get_clean();

	echo $prices_html;
	die();

}

add_action( 'wp_ajax_venue_map_processing', 'pptec_venue_map_processing' );


/**
 * Get ajax Venue Map - For Select
 */
function pptec_get_ajax_venue_map() {

	$post_fields          = 'section=events&action=list_venues';
	$remote_data_response = pptec_get_remote_data( $post_fields );
	if ( is_wp_error( $remote_data_response ) || empty( $remote_data_response["body"] ) ) {
		$remote_data_response = pptec_get_remote_data( $post_fields );
	}
	$request = json_decode( $remote_data_response["body"] );
	$html_venues = '<option value="Please, select venue">Select seating chart</option>';
	if ( isset( $request->success ) && true === $request->success ) {
		$plugin_option = get_option( 'pptec_data' );
		$plugin_option['venues_list'] = $request->venues;
		update_option( 'pptec_data', $plugin_option );
		if ( !empty( $request->venues ) ) {
			foreach ( $request->venues as $item ) {
				$html_venues .= '<option value="' . $item->id . '">' . $item->name . '</option>';
			}
		}
	}

	echo $html_venues;
	die;
}
add_action( 'wp_ajax_venue_map_processing_get', 'pptec_get_ajax_venue_map' );


/**
 * DEPRECATED.
 *
 * get venue data by ID - ajax processing
 */
/*function pptec_get_venue_data() {

	$venue_id = sanitize_text_field( $_POST['current_venue_id'] );
	$address  = get_post_meta( $venue_id, '_VenueAddress', true );
	$city     = get_post_meta( $venue_id, '_VenueCity', true );
	$country  = get_post_meta( $venue_id, '_VenueCountry', true );
	$province = get_post_meta( $venue_id, '_VenueProvince', true );
	$state    = get_post_meta( $venue_id, '_VenueState', true );
	$zip      = get_post_meta( $venue_id, '_VenueZip', true );
	$phone    = get_post_meta( $venue_id, '_VenuePhone', true );
	$url      = get_post_meta( $venue_id, '_VenueURL', true );

	$venue_data = array(
		'address'  => $address,
		'city'     => $city,
		'country'  => $country,
		'province' => $province,
		'state'    => pptec_get_state_name_by_abbr( $state ),
		'zip'      => $zip,
		'phone'    => $phone,
		'url'      => $url,
	);

	$venue_data = json_encode( $venue_data );

	echo $venue_data;
	die();

}
add_action( 'wp_ajax_get_venue_data', 'pptec_get_venue_data' );*/


/**
 *
 * Return states names by abbr
 *
 * @param $abbr
 *
 * @return mixed
 */
function pptec_get_state_name_by_abbr( $abbr ) {

	$states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
	);

	if ( ! empty( $states[ $abbr ] ) ) {
		$name = $states[ $abbr ];
	} else {
		$name = $states['AL'];
	}

	return $name;
}


/**
 * Get stats from Purplepass
 */
function pptec_get_stats_ajax() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!' );
	}

	$data = pptec_get_events_statistic_from_pp();

	if ( $data->success ) {
		foreach ( $data->stats as $key => $item ) {
			$post_id = pptec_get_wp_event_id_by_pp_event_id( $key );

			if ($post_id) {
				global $wpdb;
				$prefix = $wpdb->prefix;
				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$prefix}pp_stats (tickets_sold, revenue, wp_event_id, pp_event_id) VALUES ( %s, %s, %d, %d ) ON DUPLICATE KEY UPDATE wp_event_id = %d, pp_event_id = %d",
						$item->quantity,
						$item->revenue,
						$post_id,
						$key,
						$post_id,
						$key
					)
				);
            }
		}

		$timezone_data  = sanitize_text_field( $_POST['time_data'] );
		$timezone_new   = explode( '(', $timezone_data );
		$timezone_new   = $timezone_new[1];
		$timezone_new   = str_replace( ')', '', $timezone_new );
		$time_zone_abbr = pptec_time_zones_list( $timezone_new );
		if ( $data->cnt > 0 ) {
			update_option( 'pptec_stats_update_time', time() );
			echo '1';
		} else {
			echo 0;
		}

	} else {
		echo 'Stats can not be received, Please, try to reconnect your site to Purplepass';
	}

	die();
}

add_action( 'wp_ajax_get_stats_ajax', 'pptec_get_stats_ajax' );


/**
 * Get Log Data ajax
 */
function pptec_get_log_ajax() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!' );
	}

	if ( isset( $_POST['load_page_qty'] ) ) {
		$page = sanitize_text_field( $_POST['load_page_qty'] );
	} else {
		$page = false;
	}

	$results = false;
	$offset  = 0;

	$gmt_offset = get_option('gmt_offset');
	$timezone_offset = $gmt_offset >= 0 ? "+" . $gmt_offset : $gmt_offset;

	if ( $page ) {

		$offset = 50 * $page;

		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pp_logs ORDER BY id DESC LIMIT 50 OFFSET $offset" );
	}
	$new_key = $offset;

	if ( count( $results ) < 50 ) {
		$finish = true;
	} else {
		$finish = false;
	}

	ob_start(); ?>

	<?php if ( $results ) : ?>
		<?php foreach ( $results as $key => $item ) : ?>
			<tr>
				<?php
				$new_key ++;
				?>
				<td><?php echo $new_key; ?></td>
				<td>
					<span style="display: none;"><?php echo esc_html(strtotime( $item->event_time )); ?></span>
					<span>
						<?php
							$time = strtotime($item->event_time);
							echo esc_html( date("F jS, Y \a\\t g:ia " . pptec_take_timezone_from_offset($timezone_offset), strtotime($timezone_offset . ' hours', $time)) );
						?>
					</span>
				</td>

				<?php
				$item->event_action = esc_html( $item->event_action );
				$status = '';
				if ( 'Create' === $item->event_action ) {
					$status = '<span class="log_blue">Create</span>';
				} else if ( 'Update' === $item->event_action ) {
					$status = '<span class="log_green">Update</span>';
				} else if ( 'Delete' === $item->event_action ) {
					$status = '<span class="log_red">Delete</span>';
				} else if ( 'Daily sync' === $item->event_action ) {
					$status = '<span class="log_orange">Daily sync</span>';
				} else {
					$status = '<span>' . $item->event_action . '</span>';
				}
				?>

				<td><?php echo $status; ?></td>
				<td><?php echo esc_html($item->event_direction); ?></td>
				<td><?php echo esc_html($item->event_details); ?></td>
				<?php
				if ( 'Failed' === $item->event_status ) {
					$ulr = PPTEC_PLUGIN_DIR . 'img/failed.png';
					$alt = 'Error';
				} else {
					$ulr = PPTEC_PLUGIN_DIR . 'img/success.png';
					$alt = 'Success';
				}
				?>
				<td class="last-log-status" style="text-align: center;"><img style="width: 20px;"
																			 alt="<?php echo esc_attr($alt); ?>"
																			 title="<?php echo esc_attr($alt); ?>"
																			 src="<?php echo esc_url($ulr); ?>"><span><?php echo esc_html($item->event_status); ?></span>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php
	$log  = ob_get_clean();
	$data = array(
		'logs'      => $log,
		'is_finish' => $finish,
	);
	echo json_encode( $data );
	wp_die();
}

add_action( 'wp_ajax_get_log_ajax', 'pptec_get_log_ajax' );


/**
 * Get stats Data ajax load more
 */
function pptec_get_stats_ajax_loadmore() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!' );
	}

	if ( isset( $_POST['load_stats_page_qty'] ) ) {
		$page = sanitize_text_field( $_POST['load_stats_page_qty'] );
	} else {
		$page = false;
	}

	$results = false;
	$offset  = 0;

	if ( $page ) {

		$offset = 10 * $page;

		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pp_stats ORDER BY wp_event_id ASC LIMIT 10 OFFSET $offset" );
	}
	$new_key = $offset;

	if ( count( $results ) < 10 ) {
		$finish = true;
	} else {
		$finish = false;
	}

	ob_start(); ?>

	<?php if ( $results ) : ?>

		<?php foreach ( $results as $id => $stat_item ) : ?>
			<?php
			$event_link   = '#';
			$event_object = get_post( $stat_item->wp_event_id );
			if ( ! empty( $event_object->ID ) ) {
				$event_link = '/wp-admin/post.php?post=' . $event_object->ID . '&action=edit';
			}
			if ( ! empty( $event_object->post_title ) ) {
				$post_title = $event_object->post_title;
			}
			if ( empty( $post_title ) ) {
				continue;
			}
			?>
			<tr>
				<?php
				$str_date  = get_post_meta( $stat_item->wp_event_id, '_EventStartDate' );
				$post_data = get_post_meta($stat_item->wp_event_id, 'pptec_event_meta_data', true);
				$timezone_id = $post_data->data['timezone'];
				$timezone = pptec_get_timezone_short($timezone_id);

				if ( isset( $str_date[0] ) ) {
					$date_str = date('l, M jS, Y \a\t g:ia', strtotime($str_date[0]))  . " " . $timezone;
				} else {
					$date_str = false;
				}
				?>
				<td><?php echo $new_key + $id + 1; ?>. </td>
				<td><a href="<?php echo esc_attr($event_link); ?>" class="stats_link_popup"
					   data-event_id="<?php echo esc_attr($stat_item->pp_event_id); ?>"
					   target="_blank"><?php echo esc_html($post_title); ?></a></td>
				<td><?php echo esc_html(number_format( $stat_item->tickets_sold )); ?></td>
				<td>$<?php echo esc_html(number_format( (float) $stat_item->revenue, 2, '.', '' )); ?></td>
				<td><?php echo esc_html($date_str); ?>
				</td>
				<td>
					<img style="width: 26px; margin-right: 5px; margin-top: 10px;"
						 src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/detailedstats.png">
					<a href="#" class="stats-btn" style="color:#3673ff; text-decoration: none;"
					   data-ppid="<?php echo esc_attr($stat_item->pp_event_id); ?>">
						<span style="margin-bottom:10px;font-size: 15px;">Detailed Stats</span>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>

	<?php endif; ?>

	<?php
	$log  = ob_get_clean();
	$data = array(
		'stats'     => $log,
		'is_finish' => $finish,
	);
	echo json_encode( $data );
	wp_die();
}

add_action( 'wp_ajax_get_stats_ajax_loadmore', 'pptec_get_stats_ajax_loadmore' );


/**
 * Search time zone abbr
 *
 * @param $tzone
 *
 * @return bool
 */
function pptec_time_zones_list( $tzone ) {

	$time_zones = array(
		'EEST'  => 'Eastern European Summer Time',
		'ACDT'  => 'Australian Central Daylight Savings Time',
		'ACST'  => 'Australian Central Standard Time',
		'ACT'   => 'ASEAN Common Time (unofficial)',
		'ACWST' => 'Australian Central Western Standard Time (unofficial)',
		'ADT'   => 'Atlantic Daylight Time',
		'AEDT'  => 'Australian Eastern Daylight Savings Time',
		'AEST'  => 'Australian Eastern Standard Time',
		'AFT'   => 'Afghanistan Time',
		'AKDT'  => 'Alaska Daylight Time',
		'AKST'  => 'Alaska Standard Time',
		'ALMT'  => 'Alma-Ata Time',
		'AMST'  => 'Amazon Summer Time (Brazil)',
		'AMT'   => 'Amazon Time (Brazil)',
		'ANAT'  => 'Anadyr Time',
		'AQTT'  => 'Aqtobe Time',
		'ART'   => 'Argentina Time',
		'AST'   => 'Atlantic Standard Time',
		'AWST'  => 'Australian Western Standard Time',
		'AZOST' => 'Azores Summer Time',
		'AZOT'  => 'Azores Standard Time',
		'AZT'   => 'Azerbaijan Time',
		'BDT'   => 'Brunei Time',
		'BIOT'  => 'British Indian Ocean Time',
		'BIT'   => 'Baker Island Time',
		'BOT'   => 'Bolivia Time',
		'BRST'  => 'Brasília Summer Time',
		'BRT'   => 'Brasília Time',
		'BST'   => 'British Summer Time',
		'BTT'   => 'Bhutan Time',
		'CAT'   => 'Central Africa Time',
		'CCT'   => 'Cocos Islands Time',
		'CDT'   => 'Central Daylight Time (North America)',
		'CEST'  => 'Central European Summer Time (Cf. HAEC)',
		'CET'   => 'Central European Time',
		'CHADT' => 'Chatham Daylight Time',
		'CHAST' => 'Chatham Standard Time',
		'CHOT'  => 'Choibalsan Standard Time',
		'CHOST' => 'Choibalsan Summer Time',
		'CHST'  => 'Chamorro Standard Time',
		'CHUT'  => 'Chuuk Time',
		'CIST'  => 'Clipperton Island Standard Time',
		'CIT'   => 'Central Indonesia Time',
		'CKT'   => 'Cook Island Time',
		'CLST'  => 'Chile Summer Time',
		'CLT'   => 'Chile Standard Time',
		'COST'  => 'Colombia Summer Time',
		'COT'   => 'Colombia Time',
		'CST'   => 'Central Standard Time',
		'CT'    => 'China Time',
		'CVT'   => 'Cape Verde Time',
		'CWST'  => 'Central Western Standard Time (Australia) unofficial',
		'CXT'   => 'Christmas Island Time',
		'DAVT'  => 'Davis Time',
		'DDUT'  => 'Dumont Urville Time',
		'DFT'   => 'AIX-specific equivalent of Central European Time',
		'EASST' => 'Easter Island Summer Time',
		'EAST'  => 'Easter Island Standard Time',
		'EAT'   => 'East Africa Time',
		'ECT'   => 'Eastern Caribbean Time',
		'EDT'   => 'Eastern Daylight Time',
		'EET'   => 'Eastern European Time',
		'EGST'  => 'Eastern Greenland Summer Time',
		'EGT'   => 'Eastern Greenland Time',
		'EIT'   => 'Eastern Indonesian Time',
		'EST'   => 'Eastern Standard Time (North America)',
		'FET'   => 'Further-eastern European Time',
		'FJT'   => 'Fiji Time',
		'FKST'  => 'Falkland Islands Summer Time',
		'FKT'   => 'Falkland Islands Time',
		'FNT'   => 'Fernando de Noronha Time',
		'GALT'  => 'Galápagos Time',
		'GAMT'  => 'Gambier Islands Time',
		'GET'   => 'Georgia Standard Time',
		'GFT'   => 'French Guiana Time',
		'GILT'  => 'Gilbert Island Time',
		'GIT'   => 'Gambier Island Time',
		'GMT'   => 'Greenwich Mean Time',
		'GST'   => 'South Georgia and the South Sandwich Islands Time',
		'GYT'   => 'Guyana Time',
		'HDT'   => 'Hawaii–Aleutian Daylight Time',
		'HAEC'  => 'Heure Avancée Europe Centrale French-language name for CEST',
		'HST'   => 'Hawaii–Aleutian Standard Time',
		'HKT'   => 'Hong Kong Time',
		'HMT'   => 'Heard and McDonald Islands Time',
		'HOVST' => 'Hovd Summer Time (not used from 2017-present)',
		'HOVT'  => 'Hovd Time',
		'ICT'   => 'Indochina Time',
		'IDLW'  => 'International Day Line West time zone',
		'IDT'   => 'Israel Daylight Time',
		'IOT'   => 'Indian Ocean Time',
		'IRDT'  => 'Iran Daylight Time',
		'IRKT'  => 'Irkutsk Time',
		'IRST'  => 'Iran Standard Time',
		'IST'   => 'Indian Standard Time',
		'JST'   => 'Japan Standard Time',
		'KALT'  => 'Kaliningrad Time',
		'KGT'   => 'Kyrgyzstan Time',
		'KOST'  => 'Kosrae Time',
		'KRAT'  => 'Krasnoyarsk Time',
		'KST'   => 'Korea Standard Time',
		'LHST'  => 'Lord Howe Standard Time',
		'LINT'  => 'Line Islands Time',
		'MAGT'  => 'Magadan Time',
		'MART'  => 'Marquesas Islands Time',
		'MAWT'  => 'Mawson Station Time',
		'MDT'   => 'Mountain Daylight Time (North America)',
		'MET'   => 'Middle European Time Same zone',
		'MEST'  => 'Middle European Summer Time Same zone',
		'MHT'   => 'Marshall Islands Time',
		'MIST'  => 'Macquarie Island Station Time',
		'MIT'   => 'Marquesas Islands Time',
		'MMT'   => 'Myanmar Standard Time',
		'MSK'   => 'Moscow Time',
		'MST'   => 'Malaysia Standard Time',
		'MUT'   => 'Mauritius Time',
		'MVT'   => 'Maldives Time',
		'MYT'   => 'Malaysia Time',
		'NCT'   => 'New Caledonia Time',
		'NDT'   => 'Newfoundland Daylight Time',
		'NFT'   => 'Norfolk Island Time',
		'NOVT'  => 'Novosibirsk Time',
		'NPT'   => 'Nepal Time',
		'NST'   => 'Newfoundland Standard Time',
		'NT'    => 'Newfoundland Time',
		'NUT'   => 'Niue Time',
		'NZDT'  => 'New Zealand Daylight Time',
		'NZST'  => 'New Zealand Standard Time',
		'OMST'  => 'Omsk Time',
		'ORAT'  => 'Oral Time',
		'PDT'   => 'Pacific Daylight Time',
		'PET'   => 'Peru Time',
		'PETT'  => 'Kamchatka Time',
		'PGT'   => 'Papua New Guinea Time',
		'PHOT'  => 'Phoenix Island Time',
		'PHT'   => 'Philippine Time',
		'PKT'   => 'Pakistan Standard Time',
		'PMDT'  => 'Saint Pierre and Miquelon Daylight Time',
		'PMST'  => 'Saint Pierre and Miquelon Standard Time',
		'PONT'  => 'Pohnpei Standard Time',
		'PST'   => 'Pacific Standard Time (North America)',
		'PYST'  => 'Paraguay Summer Time[10]',
		'PYT'   => 'Paraguay Time[11]',
		'RET'   => 'Réunion Time',
		'ROTT'  => 'Rothera Research Station Time',
		'SAKT'  => 'Sakhalin Island Time',
		'SAMT'  => 'Samara Time',
		'SAST'  => 'South African Standard Time',
		'SBT'   => 'Solomon Islands Time',
		'SCT'   => 'Seychelles Time',
		'SDT'   => 'Samoa Daylight Time',
		'SGT'   => 'Singapore Time',
		'SLST'  => 'Sri Lanka Standard Time',
		'SRET'  => 'Srednekolymsk Time',
		'SRT'   => 'Suriname Time',
		'SST'   => 'Samoa Standard Time',
		'SYOT'  => 'Showa Station Time',
		'TAHT'  => 'Tahiti Time',
		'THA'   => 'Thailand Standard Time',
		'TFT'   => 'French Southern and Antarctic Time[12]',
		'TJT'   => 'Tajikistan Time',
		'TKT'   => 'Tokelau Time',
		'TLT'   => 'Timor Leste Time',
		'TMT'   => 'Turkmenistan Time',
		'TRT'   => 'Turkey Time',
		'TOT'   => 'Tonga Time',
		'TVT'   => 'Tuvalu Time',
		'ULAST' => 'Ulaanbaatar Summer Time',
		'ULAT'  => 'Ulaanbaatar Standard Time',
		'UTC'   => 'Coordinated Universal Time',
		'UYST'  => 'Uruguay Summer Time',
		'UYT'   => 'Uruguay Standard Time',
		'UZT'   => 'Uzbekistan Time',
		'VET'   => 'Venezuelan Standard Time',
		'VLAT'  => 'Vladivostok Time',
		'VOLT'  => 'Volgograd Time',
		'VOST'  => 'Vostok Station Time',
		'VUT'   => 'Vanuatu Time',
		'WAKT'  => 'Wake Island Time',
		'WAST'  => 'West Africa Summer Time',
		'WAT'   => 'West Africa Time',
		'WEST'  => 'Western European Summer Time',
		'WET'   => 'Western European Time',
		'WIT'   => 'Western Indonesian Time',
		'WST'   => 'Western Standard Time',
		'YAKT'  => 'Yakutsk Time',
		'YEKT'  => 'Yekaterinburg Time',
	);

	$time_zones = array_flip( $time_zones );

	if ( isset( $time_zones[ $tzone ] ) ) {
		return $time_zones[ $tzone ];
	} else {
		return false;
	}

}


/**
 * Get events stats from Purplepass
 *
 * @param array $pp_events_ids
 * @return bool|mixed|object
 */
function pptec_get_events_statistic_from_pp($pp_events_ids = array())
{
    try {
        if (empty($pp_events_ids)) {
            // Get all events from db
            global $wpdb;
            $pp_events_result = $wpdb->get_results( "SELECT pp_event_id FROM  {$wpdb->prefix}pptec_events" );

            if (empty($pp_events_result)) {
                throw new Exception('Could not retrieve events from db');
            }

            foreach ( $pp_events_result as $item ) {
                $pp_events_ids[] = $item->pp_event_id;
            }
        }

        $post_fields = 'section=stats&action=get_basic_stats&event_id=' . implode(',', $pp_events_ids);

        $remote_data_response = pptec_get_remote_data( $post_fields );
        if ( is_wp_error( $remote_data_response ) || empty( $remote_data_response["body"] ) ) {
            throw new Exception('Internal error');
        }

        $request = json_decode( $remote_data_response["body"] );
    } catch (Exception $e) {
        $request = (object)array( 'success' => false, 'msg' => $e->getMessage() );
    }

    return $request;
}


/**
 * Get time list
 *
 * @return array
 */
function pptec_get_time_list() {

	$time_list = array(

		'00:00' => '12:00am',
		'00:15' => '12:15am',
		'00:30' => '12:30am',
		'00:45' => '12:45am',
		'01:00' => '1:00am',
		'01:15' => '1:15am',
		'01:30' => '1:30am',
		'01:45' => '1:45am',
		'02:00' => '2:00am',
		'02:15' => '2:15am',
		'02:30' => '2:30am',
		'02:45' => '2:45am',
		'03:00' => '3:00am',
		'03:15' => '3:15am',
		'03:30' => '3:30am',
		'03:45' => '3:45am',
		'04:00' => '4:00am',
		'04:15' => '4:15am',
		'04:30' => '4:30am',
		'04:45' => '4:45am',
		'05:00' => '5:00am',
		'05:15' => '5:15am',
		'05:30' => '5:30am',
		'05:45' => '5:45am',
		'06:00' => '6:00am',
		'06:15' => '6:15am',
		'06:30' => '6:30am',
		'06:45' => '6:45am',
		'07:00' => '7:00am',
		'07:15' => '7:15am',
		'07:30' => '7:30am',
		'07:45' => '7:45am',
		'08:00' => '8:00am',
		'08:15' => '8:15am',
		'08:30' => '8:30am',
		'08:45' => '8:45am',
		'09:00' => '9:00am',
		'09:15' => '9:15am',
		'09:30' => '9:30am',
		'09:45' => '9:45am',
		'10:00' => '10:00am',
		'10:15' => '10:15am',
		'10:30' => '10:30am',
		'10:45' => '10:45am',
		'11:00' => '11:00am',
		'11:15' => '11:15am',
		'11:30' => '11:30am',
		'11:45' => '11:45am',
		'12:00' => '12:00pm',
		'12:15' => '12:15pm',
		'12:30' => '12:30pm',
		'12:45' => '12:45pm',
		'13:00' => '1:00pm',
		'13:15' => '1:15pm',
		'13:30' => '1:30pm',
		'13:45' => '1:45pm',
		'14:00' => '2:00pm',
		'14:15' => '2:15pm',
		'14:30' => '2:30pm',
		'14:45' => '2:45pm',
		'15:00' => '3:00pm',
		'15:15' => '3:15pm',
		'15:30' => '3:30pm',
		'15:45' => '3:45pm',
		'16:00' => '4:00pm',
		'16:15' => '4:15pm',
		'16:30' => '4:30pm',
		'16:45' => '4:45pm',
		'17:00' => '5:00pm',
		'17:15' => '5:15pm',
		'17:30' => '5:30pm',
		'17:45' => '5:45pm',
		'18:00' => '6:00pm',
		'18:15' => '6:15pm',
		'18:30' => '6:30pm',
		'18:45' => '6:45pm',
		'19:00' => '7:00pm',
		'19:15' => '7:15pm',
		'19:30' => '7:30pm',
		'19:45' => '7:45pm',
		'20:00' => '8:00pm',
		'20:15' => '8:15pm',
		'20:30' => '8:30pm',
		'20:45' => '8:45pm',
		'21:00' => '9:00pm',
		'21:15' => '9:15pm',
		'21:30' => '9:30pm',
		'21:45' => '9:45pm',
		'22:00' => '10:00pm',
		'22:15' => '10:15pm',
		'22:30' => '10:30pm',
		'22:45' => '10:45pm',
		'23:00' => '11:00pm',
		'23:15' => '11:15pm',
		'23:30' => '11:30pm',
		'23:45' => '11:45pm',
	);

	return $time_list;
}

/**
 * Get timezone short list.
 *
 * @param integer $offset
 * @return string
 */
function pptec_take_timezone_from_offset($offset) {
	$timezones = array(
		-4 => "\E\D\T",
		-5 => "\E\S\T",
		-6 => "\C\S\T",
		-7 => "\P\D\T",
		-8 => "\P\S\T",
		-9 => "\A\K\S\T",
		-10 => "\H\S\T",
		-11 => "\S\S\T",
	);

	return isset($timezones[$offset]) ? $timezones[$offset] : "\U\T\C".$offset;
}

/**
 * Get timezone short list.
 *
 * @param integer $timezone_id
 * @return string
 */

function pptec_get_timezone_short($timezone_id) {
	$timezones = array(
		0 => "PST",
		1 => "IDLW",
		2 => "SST",
		3 => "HST",
		4 => "AKST",
		6 => "MST",
		7 => "MST",
		8 => "DST",
		9 => "CST",
		10 => "CST",
		11 => "CDT",
		12 => "CST",
		13 => "EST",
		14 => "DST",
		15 => "EST",
		16 => "VET",
		17 => "CLT",
		18 => "AST",
		19 => "NST",
		20 => "ART",
		21 => "WGT",
		22 => "BRT",
		23 => "GMT-2",
		24 => "CVT",
		25 => "AZOST",
		26 => "WEST",
		27 => "GMT",
		28 => "WAT",
		29 => "CEST",
		30 => "CEST",
		31 => "CEST",
		32 => "CEST",
		33 => "CAT",
		34 => "IDT",
		35 => "EEST",
		36 => "EEST",
		37 => "EET",
		38 => "EEST",
		39 => "EAT",
		40 => "AST",
		41 => "MSK",
		42 => "AST",
		43 => "IRDT",
		44 => "GST",
		45 => "AZT",
		46 => "AFT",
		47 => "PKT",
		48 => "YEKT",
		49 => "IST",
		50 => "NPT",
		51 => "IST",
		52 => "ALMT",
		53 => "NOVT",
		54 => "MMT",
		55 => "ICT",
		56 => "KRAT",
		57 => "AWST",
		58 => "CST",
		59 => "MYT",
		60 => "CST",
		61 => "IRKT",
		62 => "JST",
		63 => "KST",
		64 => "YAKT",
		65 => "ACST",
		66 => "ACST",
		67 => "ChST",
		68 => "AEST",
		69 => "VLAT",
		70 => "AEST",
		71 => "AEST",
		72 => "MAGT",
		73 => "FJT",
		74 => "NZST"
	);

	return $timezones[$timezone_id];
}
/**
 * Get timezone list.
 *
 * @param bool $pptec_timezone_id
 * @return mixed|string
 */
function pptec_get_timezone_list($pptec_timezone_id = false, $return_array = false) {

	$tz_arr = array(
		1  => '(GMT-12:00) International Date Line West',
		2  => '(GMT-11:00) Midway Island, Samoa',
		3  => '(GMT-10:00) Hawaii',
		4  => '(GMT-09:00) Alaska *',
		0  => '(GMT-08:00) Pacific Time (US & Canada); Tijuana *', //SERVER_TIME_ZONE
		6  => '(GMT-07:00) Arizona',
		7  => '(GMT-07:00) Mountain Time (US & Canada) *',
		8  => '(GMT-07:00) Chihuahua, La Paz, Mazatlan *',
		9  => '(GMT-06:00) Central America',
		10 => '(GMT-06:00) Saskatchewan',
		11 => '(GMT-06:00) Guadalajara, Mexico City, Monterrey *',
		12 => '(GMT-06:00) Central Time (US & Canada) *',
		13 => '(GMT-05:00) Indiana (East)',
		14 => '(GMT-05:00) Bogota, Lima, Quito',
		15 => '(GMT-05:00) Eastern Time (US & Canada) *',
		16 => '(GMT-04:00) Caracas, La Paz',
		17 => '(GMT-04:00) Santiago *',
		18 => '(GMT-04:00) Atlantic Time (Canada) *',
		19 => '(GMT-03:30) Newfoundland *',
		20 => '(GMT-03:00) Buenos Aires, Georgetown',
		21 => '(GMT-03:00) Greenland *',
		22 => '(GMT-03:00) Brasilia *',
		23 => '(GMT-02:00) Mid-Atlantic *',
		24 => '(GMT-01:00) Cape Verde Is.',
		25 => '(GMT-01:00) Azores *',
		26 => '(GMT) Casablanca, Monrovia',
		27 => '(GMT) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London *',
		28 => '(GMT+01:00) West Central Africa',
		29 => '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna *',
		30 => '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris *',
		31 => '(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb *',
		32 => '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague *',
		33 => '(GMT+02:00) Harare, Pretoria',
		34 => '(GMT+02:00) Jerusalem',
		35 => '(GMT+02:00) Athens, Istanbul, Minsk *',
		36 => '(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius *',
		37 => '(GMT+02:00) Cairo *',
		38 => '(GMT+02:00) Bucharest *',
		39 => '(GMT+03:00) Nairobi',
		40 => '(GMT+03:00) Kuwait, Riyadh',
		41 => '(GMT+03:00) Moscow, St. Petersburg, Volgograd *',
		42 => '(GMT+03:00) Baghdad *',
		43 => '(GMT+03:30) Tehran *',
		44 => '(GMT+04:00) Abu Dhabi, Muscat',
		45 => '(GMT+04:00) Baku, Tbilisi, Yerevan *',
		46 => '(GMT+04:30) Kabul',
		47 => '(GMT+05:00) Islamabad, Karachi, Tashkent',
		48 => '(GMT+05:00) Ekaterinburg *',
		49 => '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi',
		50 => '(GMT+05:45) Kathmandu',
		51 => '(GMT+06:00) Sri Jayawardenepura',
		52 => '(GMT+06:00) Astana, Dhaka',
		53 => '(GMT+06:00) Almaty, Novosibirsk *',
		54 => '(GMT+06:30) Rangoon',
		55 => '(GMT+07:00) Bangkok, Hanoi, Jakarta',
		56 => '(GMT+07:00) Krasnoyarsk *',
		57 => '(GMT+08:00) Perth',
		58 => '(GMT+08:00) Taipei',
		59 => '(GMT+08:00) Kuala Lumpur, Singapore',
		60 => '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi',
		61 => '(GMT+08:00) Irkutsk, Ulaan Bataar *',
		62 => '(GMT+09:00) Osaka, Sapporo, Tokyo',
		63 => '(GMT+09:00) Seoul',
		64 => '(GMT+09:00) Yakutsk *',
		65 => '(GMT+09:30) Darwin',
		66 => '(GMT+09:30) Adelaide *',
		67 => '(GMT+10:00) Guam, Port Moresby',
		68 => '(GMT+10:00) Brisbane',
		69 => '(GMT+10:00) Vladivostok *',
		70 => '(GMT+10:00) Hobart *',
		71 => '(GMT+10:00) Canberra, Melbourne, Sydney *',
		72 => '(GMT+11:00) Magadan, Solomon Is., New Caledonia',
		73 => '(GMT+12:00) Fiji, Kamchatka, Marshall Is.',
		74 => '(GMT+12:00) Auckland, Wellington *',
	);

	if (false !== $return_array) {
	    return $tz_arr;
    }

	if (false !== $pptec_timezone_id) {
	    return isset($tz_arr[$pptec_timezone_id]) ? $tz_arr[$pptec_timezone_id] : 'Unable to retrieve timezone.';
    }

	$tz_list = '';
	foreach ( $tz_arr as $key => $item ) {

		$new_item   = explode( ')', $item );
		$new_item_2 = str_replace( array( '(', 'GMT', ':00' ), '', $new_item[0] );
		if ( 10 > str_replace( array( '+', '-' ), '', $new_item_2 ) ) {
			$new_item_2 = str_replace( '0', '', $new_item_2 );
		}
		$new_item_2 = explode( ':', $new_item_2 );
		$new_item_2 = $new_item_2[0];

		$tz_list .= '<option data-hours="' . $new_item_2 . '" value="' . $key . '">' . $item . '</option>';

	}

	return $tz_list;
}

/**
 * Get token for stats single event
 *
 * @param $zip
 *
 * @return array
 */
function pptec_single_event_stats() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!' );
	}

	echo pptec_get_access_token();
	die();
}
add_action( 'wp_ajax_single_event_stats', 'pptec_single_event_stats' );


/**
 * Unlink account from Purplepass
 */
function pptec_unlink_account() {

	// ajax checking
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Incorrect nonce!' );
	}

	// remove options
	pptec_remove_unlinked_account_data();

	die();
}
add_action( 'wp_ajax_pptec_unlink_account', 'pptec_unlink_account' );


/**
 * Get venues options list
 *
 * @return string
 */
function pptec_get_venues_options_list() {

	$all_list = '';

	$plugin_option = get_option( 'pptec_data' );
	if ( !empty( $plugin_option['venues_list'] ) && is_array( $plugin_option['venues_list'] ) ) {
        foreach ( $plugin_option['venues_list'] as $key => $item ) {
			$all_list .= '<option value="' . $item->id . '">' . $item->name . '</option>';
		}
	} else {
		$all_list .= '<option value="Please, select venue">Select seating chart</option>';
	}

	return $all_list;
}



/**
 * Get ajax fetching events data on set interval
 */
function pptec_get_events_fetching_progress() {
    $status = '';
	$time = '';
	$clear_interval = false;
	$message = '';

	// Count how many init requests we receive. If there are more than 30 (1 min), that means something went wrong.
    $init_requests_counter = isset($_POST['init_requests_counter']) ? (int)$_POST['init_requests_counter'] : 0;
    if ($init_requests_counter >= 30) {
        delete_option('pptec_job_events_fetching');

        echo json_encode( [
            'status' => 'failed',
            'time' => '',
            'clear_interval' => true,
            'message' => "Unable to initialize fetching process. That means, wp-cron is not working properly on your hosting environment. Please, contact your hosting support. Alternatively, the issue can be fixed by enabling `ALTERNATE_WP_CRON` in `wp-config.php` file of your site."
        ] );
        die;
    }

	$job_events_fetching = get_option( 'pptec_job_events_fetching' );

	if (!empty($job_events_fetching)) {
		$status = !empty($job_events_fetching['status']) ? $job_events_fetching['status'] : 'initialized';
		$total = !empty($job_events_fetching['total']) ? $job_events_fetching['total'] : -1;
		$message = !empty($job_events_fetching['message']) ? $job_events_fetching['message'] : 'Something went wrong.';

		if (0 === $total) {
			$clear_interval = true;
		}

        if ('finished' === $status) {
            $clear_interval = true;

            if (!empty($job_events_fetching['time'])) {
				// date format: June 7th, 2020 at 2:06am UTC+0
				$gmt_offset = get_option('gmt_offset');
				$timezone_offset = $gmt_offset >= 0 ? "+" . $gmt_offset : $gmt_offset;
				$timezone_utc = $job_events_fetching['time'];
				$time = '  (Last sync: ' . date( 'F jS, Y \a\t g:ia ' . pptec_take_timezone_from_offset($timezone_offset) , strtotime($timezone_offset . ' hours', $timezone_utc)  ) . ')';
            }
        }
    } else {
	    $clear_interval = true;
    }

	echo json_encode( [
        'status' => $status,
        'time' => $time ? $time : '',
        'clear_interval' => $clear_interval,
        'message' => $message
    ] );
	die;
}
add_action( 'wp_ajax_pptec_get_events_fetching_progress', 'pptec_get_events_fetching_progress' );


/**
 * Clean Fetch Events counters after clear interval
 */
function pptec_reset_events_fetching_progress() {
	delete_option('pptec_job_events_fetching');
}
add_action( 'wp_ajax_pptec_reset_events_fetching_progress', 'pptec_reset_events_fetching_progress' );

/**
 * In Editing mode quick update event data
 */

function pptec_event_update_on_form_opened()
{
    if (is_admin() && !empty($_GET['post']) && !empty($_GET['action']) && 'edit' === $_GET['action'] && 'tribe_events' === get_post_type($_GET['post'])) {
        // Check if event belongs to currently linked user
        $pp_user_id = pptec_oauth_get_pp_user_id();
        $wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id($_GET['post']);
        $access_token = pptec_get_access_token();
        if ($wp_event_pp_user_id && (int)$pp_user_id === (int)$wp_event_pp_user_id && !empty($access_token)) {
            $ecp_obj = new Purplepass_ECP();
            $pp_event_id = pptec_get_pp_event_id_by_wp_event_id($_GET['post']);
            $ecp_obj->pptec_technical_update_one_event($pp_event_id, $access_token, $_GET['post']);
        }
    }
}
add_action('admin_init', 'pptec_event_update_on_form_opened', 99999);


/**
 * Check - if string is JSON
 *
 * @param $string
 *
 * @return bool
 */
function pptec_is_string_JSON( $string ) {

	return is_string( $string ) && is_array( json_decode( $string, true ) ) ? true : false;
}


/**
 * Add to events admin table custom column - Post Owner
 */
if ( !empty( $_GET['post_type'] ) && 'tribe_events' === $_GET['post_type'] ) {
	add_filter('post_class', 'pptec_another_account_event_add_class');
}


/**
 * Add custom class to post titles in posts list, if event was loaded from another account
 *
 * @param $classes
 *
 * @return mixed
 */
function pptec_another_account_event_add_class($classes) {
	global $post;

	$pp_user_id = pptec_oauth_get_pp_user_id();
	$wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id( $post->ID );
	if ($wp_event_pp_user_id && (int)$pp_user_id !== (int)$wp_event_pp_user_id) {
		$classes[] = 'another-account-event';
	}

	return $classes;
}


/**
 * Escape special symbols in titles (anti XSS)
 *
 * @param $title
 *
 * @return string
 */
function pptec_title_filter( $title ) {
	return esc_html($title);
}
add_filter( 'the_title', 'pptec_title_filter', 1 );

// Cleanup after wp_event deleted
function pptec_cleanup_on_wp_event_deletion($wp_event_id)
{
	$post_type = get_post_type($wp_event_id);
	if ('tribe_events' === $post_type) {
		$purple = new Purplepass_ECP();
		$purple->pptec_delete_event_on_purple_pass($wp_event_id);
	}
}
add_action('delete_post', 'pptec_cleanup_on_wp_event_deletion', 10);

// Cleanup after wp_venue deleted
function pptec_cleanup_on_wp_venue_deletion($wp_venue_id)
{
    $post_type = get_post_type($wp_venue_id);
    if ('tribe_venue' === $post_type) {
        // Delete wp_venue_id to pp_venue_id binding from pptec_wp_venue_to_pp_venue
        global $wpdb;
        $wpdb->delete($wpdb->prefix . "pptec_wp_venue_to_pp_venue", array('wp_venue_id' => $wp_venue_id));
        $wpdb->update($wpdb->prefix . "pptec_events", array('wp_venue_id' => null), array('wp_venue_id' => $wp_venue_id));
    }
}
add_action('delete_post', 'pptec_cleanup_on_wp_venue_deletion', 10);

if ( empty( pptec_get_access_token() ) ) {
    // Account is not linked to PP

	add_action( 'delete_post', 'pptec_reset_last_requested_date', 10 );
	add_action( 'delete_post', 'pptec_delete_event_statistics', 10 );

	/**
	 * Reset events last requested date on WP event deletion, only if account is unlinked.
	 * @param $event_id
	 */
	function pptec_reset_last_requested_date( $event_id ){
		$post_type = get_post_type( $event_id );
		if ( 'tribe_events' === $post_type ) {
			delete_option("pptec_events_last_requested_date");
		}
	}

	/**
	 * Delete PP event statistics on WP event deletion, only if account is unlinked.
	 * @param $event_id
	 */
	function pptec_delete_event_statistics( $event_id ){
		$post_type = get_post_type( $event_id );
		if ( 'tribe_events' === $post_type ) {
		    global $wpdb;
			$wpdb->delete( $wpdb->prefix . "pp_stats", array( 'wp_event_id' => $event_id ) );
		}
	}
}

function pptec_add_timezone_selector($wp_event_id)
{
	if (isset($_GET['post'])) {
		$linked_pp_user_id = pptec_oauth_get_pp_user_id();
		$wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id($_GET['post']);
		if ($wp_event_pp_user_id && (int)$linked_pp_user_id !== (int)$wp_event_pp_user_id) {
			return;
		}
	}

    $pp_event_data = get_post_meta($wp_event_id, 'pptec_event_meta_data', true);

    $stored_tz_id = !empty($pp_event_data->data['timezone']) ? (int)$pp_event_data->data['timezone'] : 0;
    $timezones_list = pptec_get_timezone_list(false, true);

    $timezone_html  = '<tr>';
    $timezone_html .= '  <td>Timezone (by Venue):</td>';
    $timezone_html .= '  <td id="pptec_timezone">';
    $timezone_html .= '    <select name="pptec_timezone_id">';
    foreach ($timezones_list as $tz_id => $tz_name) {
        $timezone_html .= ('  <option value="' . $tz_id . '" ' . ($tz_id === $stored_tz_id ? 'selected="selected"' : ''). '>' . $tz_name . '</option>');
    }
    $timezone_html .= '    </select>';
    $timezone_html .= '  </td>';
    $timezone_html .= '</tr>';

    echo $timezone_html;
}
add_action('tribe_events_date_display', 'pptec_add_timezone_selector');

function pptec_jx_get_wp_venue_data()
{
    $json = array();

    try {
        if (empty($_GET['wp_venue_id'])) {
            throw new Exception('Please select venue.');
        }

		/*
        $json['name'] = array(
            'selector' => '[name="venue[Venue][]"]',
            'value' => get_the_title($_GET['wp_venue_id'])
		);
		*/

        // Get zip code of wp_venue
        $wp_venue_meta = get_post_meta($_GET['wp_venue_id']);

        if (is_array($wp_venue_meta)) {
            foreach ($wp_venue_meta as $key => $value) {
                if ('_VenueAddress' === $key) {
                    $json['addr'] = array(
                        'selector' => '[name="venue[Address][]"]',
                        'value' => $value[0]
                    );
                }

                if ('_VenueCity' === $key) {
                    $json['city'] = array(
                        'selector' => '[name="venue[City][]"]',
                        'value' => $value[0]
                    );
                }

                if ('_VenueCountry' === $key) {
                    $json['country'] = array(
                        'selector' => '[name="venue[Country][]"]',
                        'value' => $value[0]
                    );
                }

                if ('_VenueProvince' === $key) {
                    $json['province'] = array(
                        'selector' => '[name="venue[Province][]"]',
                        'value' => $value[0]
                    );
                }

                if ('_VenueState' === $key) {
                    $json['state'] = array(
                        'selector' => '[name="venue[State]"]',
                        'value' => $value[0]
                    );
                }

                if ('_VenueZip' === $key) {
                    $json['zip'] = array(
                        'selector' => '[name="venue[Zip][]"]',
                        'value' => $value[0]
                    );
                }
            }
        }

        // Get timezone by zip
        include_once PPTEC_PLUGIN_PATH . 'inc/zips.php';
        $pptec_timezone_id = (!empty($json['zip']['value']) && isset($pptec_zip_data[$json['zip']['value']])) ? $pptec_zip_data[$json['zip']['value']] : 0;

        $json['timezone_id'] = array(
            'selector' => '[name="pptec_timezone_id"]',
            'value' => $pptec_timezone_id
        );
    } catch (Exception $e) {
        $json['error'] = $e->getMessage();
    }

    echo json_encode($json);
    die;
}
add_action('wp_ajax_pptec_jx_get_wp_venue_data', 'pptec_jx_get_wp_venue_data');

function pptec_jx_get_timezone_by_zip()
{
    include_once PPTEC_PLUGIN_PATH . 'inc/zips.php';
    echo isset($_GET['zip']) && isset($pptec_zip_data[$_GET['zip']]) ? $pptec_zip_data[$_GET['zip']] : 0;
    die;
}
add_action('wp_ajax_pptec_jx_get_timezone_by_zip', 'pptec_jx_get_timezone_by_zip');

function pptec_update_tribe_us_states_list($states)
{
    $states['AS'] = 'Samoa';
    $states['AA'] = 'Armed Forces (AA)';
    $states['AE'] = 'Armed Forces (AE)';
    $states['AP'] = 'Armed Forces (AP)';

    natsort($states);

    $states['-'] = 'Not Applicable';

    return $states;
}
add_filter('tribe_us_states', 'pptec_update_tribe_us_states_list');
