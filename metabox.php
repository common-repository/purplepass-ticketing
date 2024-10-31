<?php
/**
 * Add a metabox to the main column
 */
function pp_add_event_metaboxes() {
    // @todo v2: unify in one helper method

	if (!empty($_GET['post'])) {
        $linked_pp_user_id = pptec_oauth_get_pp_user_id();
        $wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id($_GET['post']);
        if ($wp_event_pp_user_id && (int)$linked_pp_user_id !== (int)$wp_event_pp_user_id) {
            return;
        }
	}

	add_meta_box(
		'pp_events_add_fields',
		'SELL TICKETS',
		'pp_events_add_fields_func',
		'tribe_events',
		'normal',
		'high'
	);

	add_meta_box(
		'pp_events_stats',
		'View Stats',
		'pp_add_event_stats',
		'tribe_events',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'pp_add_event_metaboxes' );


/**
 * Add stats button to events page
 */
function pp_add_event_stats() {
	if ( isset( $_GET['post'] ) ) {
		$pp_event_id = pptec_get_pp_event_id_by_wp_event_id( sanitize_text_field($_GET['post']) );
	} else {
		$pp_event_id = false;
	}
	if ( $pp_event_id ) :
		echo '
			<br>
				<a href="#" class="stats-btn-single add-ticket-type2 btn-iframes btn-single-stats" data-event_id="'.$pp_event_id.'">View event stats</a>
			<br>';
	endif;
}


/**
 * Output the HTML for the metabox.
 */
function pp_events_add_fields_func() {

	global $post;

	$get_post_all_response = get_post_meta( $post->ID, 'pptec_event_meta_data', true );

	if ( !empty( $get_post_all_response->data ) ) {
		$data = (object)$get_post_all_response->data;
	} else {
		$data = array();
	}
	if ( !empty( $data->venue ) ) {
		$venue = $data->venue;
	} else {
		$venue = '';
	}

	$pp_enabled_ticket_sales = isset( $data->pp_enabled_ticket_sales) ? $data->pp_enabled_ticket_sales : '1'; // enable metabox
    $event_status = get_post_status();
    if ('canceled' === $event_status) {
        $pp_enabled_ticket_sales = 0;
    } elseif (!isset($_GET['post'])) {
        $pp_enabled_ticket_sales = 1;
    }

	$doors_open                  = ( ! empty( $data->doorsopen ) ) ? $data->doorsopen : '';
	$sales_start                 = ( ! empty( $data->sales_start ) ) ? $data->sales_start : '';
	$startson                    = ( ! empty( $data->startson ) ) ? $data->startson : '';
	$endsat                      = ( ! empty( $data->endsat ) ) ? $data->endsat : '';
	$sales_stop                  = ( ! empty( $data->sales_stop ) ) ? $data->sales_stop : '';
	$ages                        = ( ! empty( $data->ages ) ) ? esc_textarea($data->ages) : 'All Ages';
	$pp_short_desc               = ( ! empty( $data->short_descr ) ) ? $data->short_descr : '';

	$pp_address                  = ( ! empty( $data->addr ) ) ? $data->addr : '';
	$pp_city                     = ( ! empty( $data->city ) ) ? $data->city : '';
	$pp_zip                      = ( ! empty( $data->zip ) ) ? $data->zip : '';
	$pp_state_choosen            = ( ! empty( $data->state ) ) ? $data->state : '0';
	$pp_country_choosen          = ( ! empty( $data->country ) ) ? $data->country : '1';

	$pp_price_type               = ( ! empty( $data->price_type ) ) ? $data->price_type : '0';
	$delivery_expr               = ( ! empty( $data->delivery_expr ) ) ? $data->delivery_expr : '0'; // new
	$delivery_prior              = ( ! empty( $data->delivery_prior ) ) ? $data->delivery_prior : '0'; // new
	$delivery_wcall              = ( ! empty( $data->delivery_wcall ) ) ? $data->delivery_wcall : '0'; // new
	$delivery_home               = ( ! empty( $data->delivery_home ) ) ? $data->delivery_home : '0'; // new
	$delivery_usps               = ( ! empty( $data->delivery_usps ) ) ? $data->delivery_usps : '0'; // new
	$custom_delivery             = ( ! empty( $data->custom_delivery ) ) ? $data->custom_delivery : array(); // new
	$pp_passwded                 = ( ! empty( $data->passwded ) ) ? $data->passwded : '';
	$pp_pass                     = ( ! empty( $data->passwd ) ) ? $data->passwd : '';
	$pp_hidden                   = ( isset( $data->hidden ) ) ? (int)$data->hidden : 0;

	$pp_fee                      = ( ! empty( $data->ffee ) ) ? $data->ffee : '';
	$pp_tax                      = ( ! empty( $data->stax ) ) ? $data->stax : ''; // settings
	//$pp_add_tax_hidden         = ( ! empty( $data->stax ) ) ? $data->stax : ''; // settings
	$pp_fb_url                   = ( ! empty( $data->fb_page_url ) ) ? $data->fb_page_url : '';
	$pp_add_facebook_hidden      = ( ! empty( $data->fb_login ) ) ? $data->fb_login : '';
	$pp_auto_check_in_hidden     = ( ! empty( $data->fb_checkin ) ) ? $data->fb_checkin : '0';
	$pp_tell_opt_hidden          = ( ! empty( $data->fb_tell ) ) ? $data->fb_tell : '0';
	$pp_like_before_purch_hidden = ( ! empty( $data->require_fb_like ) ) ? $data->require_fb_like : '0';
	$pp_timezone_choosen         = ( ! empty( $data->timezone ) ) ? $data->timezone : '';
	$pp_add_terms_hidden         = ( ! empty( $data->terms_enable ) ) ? $data->terms_enable : '0';
	$pp_term_text                = ( ! empty( $data->terms ) ) ? $data->terms : '';
	$pp_term_req_hidden          = ( ! empty( $data->terms_require ) ) ? $data->terms_require : '0';
	$pp_pers_msg_hidden          = ( ! empty( $data->receipt_enable ) ) ? $data->receipt_enable : '0';
	$pp_msg_text                 = ( ! empty( $data->receipt ) ) ? $data->receipt : '';
	$pp_spec_notes_text          = ( ! empty( $data->operator_info ) ) ? $data->operator_info : '';
	$pp_spec_notes_hidden        = ( ! empty( $data->operator_info_enable ) ) ? $data->operator_info_enable : '0';
    $pp_email_template_hidden    = ( ! empty( $data->custom_tpl_id ) ) ? $data->custom_tpl_id : '';
    $pp_email_template_name      = ( ! empty( $data->custom_tpl_name ) ) ? $data->custom_tpl_name : '';
    $pp_pah_template_hidden      = ( ! empty( $data->custom_pah_id ) ) ? $data->custom_pah_id : '';
    $pp_pah_template_name        = ( ! empty( $data->custom_pah_name ) ) ? $data->custom_pah_name : '';
	$pp_categories_choosen       = ( ! empty( $data->category_1 ) ) ? $data->category_1 : 'Please select category';
	$pp_being_sold               = ( ! empty( $data->items_type ) ) ? $data->items_type : '0';
//	$pp_venue_map                = ( ! empty( $data->venue_name ) ) ? $data->venue_name : $venue;
	$pp_venue_map_name           = ( ! empty( $data->pp_venue_map_name ) ) ? $data->pp_venue_map_name : $venue;
	$pp_venue_map                = ( ! empty( $data->venue_id ) ) ? $data->venue_id : '';
	$pp_venue_id                 = ( ! empty( $data->venue_id ) ) ? $data->venue_id : ''; // @todo v2 remove
	$pp_questions_hidden         = ( ! empty( $data->questions_enable ) ) ? $data->questions_enable : '0'; // required
	$pp_tax                      = ( ! empty( $data->stax_perc ) ) ? $data->stax_perc : '0.00';
	$pp_events_types             = ( ! empty( $data->event_type ) ) ? $data->event_type : '0.00';
	$pp_capacity                 = ( ! empty( $data->capacity ) ) ? $data->capacity : '';
	$pp_fee_doll                 = ( ! empty( $data->ffee_flat ) ) ? $data->ffee_flat : '0';
	$pp_fee_percent              = ( ! empty( $data->ffee_perc ) ) ? $data->ffee_perc : '0';
	$pp_fee_min_doll             = ( ! empty( $data->ffee_min ) ) ? $data->ffee_min : '0';
	$pp_fee_sep_hidden           = ( ! empty( $data->ffee_split ) ) ? $data->ffee_split : '0';
	$prices_right                = ( ! empty( $data->price ) ) ? $data->price : array();

	// Original array of prices from PP
    $prices_sanitized = array();
    foreach ($prices_right as $row_id => $price_row) {
        foreach ($price_row as $key => $value) {
            // @todo v2: recursive sanitize if value is array
            $prices_sanitized[$row_id][$key] = !is_array($value) ? str_replace('\&#039;', '&#039;', html_entity_decode($value)) : $value;
        }
    }

    $coupons = !empty($data->coupons) ? maybe_unserialize($data->coupons) : array();
    $coupons_sanitized = array();
    foreach ($coupons as $row_id => $coupon_row) {
        $coupon_row['code'] = html_entity_decode($coupon_row['code']);
        $coupons_sanitized[$row_id] = $coupon_row;
    }
    $pp_coupons_hidden_rows = !empty($coupons_sanitized) ? json_encode($coupons_sanitized, JSON_UNESCAPED_SLASHES) : '';

    $questions = !empty($data->questions) ? maybe_unserialize($data->questions) : array();
    $questions_sanitized = array();
    foreach ($questions as $row_id => $question_row) {
        $question_row['title'] = html_entity_decode($question_row['title']);
        $questions_sanitized[$row_id] = $question_row;
    }
    $pp_questions_hidden_rows = !empty($questions_sanitized) ? json_encode($questions_sanitized, JSON_UNESCAPED_SLASHES) : '';

	/**
	 * Tech data processing
	 *
	 */
	if ( $sales_start ) {
		$sales_start_time_attr = date('H:i', strtotime(explode( ' ', $sales_start )[1]));
	} else {
		$sales_start_time_attr = '';
	}

	if ( 'undefined:undefined' == $sales_start_time_attr ) {
		$sales_start_time_attr = explode( " ", date( 'Y-m-d H:i:s' ) )[1];

		$sales_start_time_attr = explode( ":", $sales_start_time_attr )[0];
		$sales_start_time_attr = $sales_start_time_attr . ":00";

		$sales_start = explode( " ", $sales_start )[0];
		$sales_start = $sales_start . ' ' . $sales_start_time_attr . ":00";
	}

	if ( !empty($sales_stop) ) {
		$sales_stop_time_attr = explode( ' ', $sales_stop );
		if ( count($sales_stop_time_attr) > 1 ) {
			$sales_stop_time_attr = $sales_stop_time_attr[1];

			$sales_stop_time_attr = explode( ':', $sales_stop_time_attr );
			$sales_stop_time_attr = $sales_stop_time_attr[0] . ':' . $sales_stop_time_attr[1];
		}

	} else {
		$sales_stop_time_attr = '00:00';
	}

    // @todo v2: REFACTORING
	if ( $doors_open ) {
		$doors_open_time_attr = explode( ':', $doors_open );
        $doors_open_time_attr = $doors_open_time_attr[0] . ':' . $doors_open_time_attr[1];
	} else {
		$doors_open_time_attr = '00:00';
	}

	$class_closed = "";

	if ( !empty( $pp_enabled_ticket_sales ) || !isset( $_GET['post'] ) ) {
		$class_closed = "switch-on";
	} else {
	    $class_closed = "";
    }

	if ( isset( $_GET["post"] ) ) {
		$post_id = sanitize_text_field($_GET["post"]);
	} else {
		$post_id = 0;
	}
	$account_info = check_if_token_exists();
	if ( 'linked' === $account_info['account_status'] ) :

		require_once 'inc/mb/switcher.php';

		require_once 'inc/mb/age.php';

		//require_once 'inc/mb/short-descr.php';

		require_once 'inc/mb/dates.php';

		require_once 'inc/mb/hidden-status.php';

		require_once 'inc/mb/password.php';

		require_once 'inc/mb/category.php';

//		require_once 'inc/mb/location.php';

		if ( !isset( $_GET['post'] ) ) {
			require_once 'inc/mb/price-options.php';
		}

		require_once 'inc/mb/price-type.php';

		require_once 'blocks/price-table.php';

		require_once 'inc/mb/delivery-type.php';

		require_once 'inc/mb/tax.php';

		require_once 'inc/mb/fee.php';

		require_once 'inc/mb/coupones.php';

		require_once 'inc/mb/terms.php';

		//require_once 'inc/mb/facebook.php';

		require_once 'inc/mb/pers-messages.php';

		require_once 'inc/mb/instructions.php';

		require_once 'inc/mb/emails.php';

		require_once 'inc/mb/questions.php';

		require_once 'inc/mb/save-btn.php';

	echo "</div>";

		pptec_validation_rulers();

	else:
		echo '<div class="metabox-wrapper-event"><br>';
			echo '<div class="msg-metabox-unlinked" style="padding-bottom: 12px; padding-top: 12px;">';

			require 'inc/html/unlink-message.php';

			echo '</div>';
		echo '</div>';
	endif;

}

// setup date time picker
datepicker_js();


/**
 * Datepicker function - launch
 */
function datepicker_js(){
	if( is_admin() && isset( $_GET['post']) ) {
		$ptype = get_post_type( sanitize_text_field($_GET['post']) );
		if ( 'tribe_events' === $ptype ) {
			add_action('admin_footer', 'pptec_init_datepicker_custom', 99 );
		}
	} elseif( is_admin() && isset($_GET['post_type']) && 'tribe_events' === $_GET['post_type'] ){
		add_action('admin_footer', 'pptec_init_datepicker_custom', 99 );
	}
}


/**
 * Init date picker func
 */
function pptec_init_datepicker_custom(){
	?>
	<script type="text/javascript">

		jQuery(document).ready( function($) {
			$( ".sale_start_date, .sale_stop_date" ).datepicker({
				dateFormat: 'yy-mm-dd',
			});
		} );

	</script>
	<?php
}


/**
 * validation of metabox fields
 */
function pptec_validation_rulers() {

	ob_start();

	require_once 'inc/mb/validation.php';

	$all_code = ob_get_clean();

	echo $all_code;
}
