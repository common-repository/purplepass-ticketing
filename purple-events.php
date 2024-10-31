<?php
/**
Plugin Name: Purplepass plugin for The Events Calendar
Plugin URI: https://www.purplepass.com/Learn
Description: The Purplepass Ticketing plugin for Modern Tribe's Event Calendar allows you to add a robust ticketing system directly within your Wordpress website.
Author: Purplepass Ticketing
Author URI: https://www.purplepass.com
Version: 1.0.4
 */

define( 'PPTEC_EVENT_STATUS_CANCELLED', 0x01 );
define( 'PPTEC_OPT_EVENT_FFEE_SPLIT', 0x00000004 );
define( 'PPTEC_OPT_EVENT_TYPE_FREE', 0x00000008 );
define( 'PPTEC_OPT_EVENT_HIDDEN', 0x00000020 );
define( 'PPTEC_OPT_EVENT_PASSWDED', 0x00000040 );
define( 'PPTEC_OPT_EVENT_TYPE_RESERVATION_ONLY', 0x10000000 );
define( 'PPTEC_OPT_EVENT_FB_CHECKIN', 0x00800000 );
define( 'PPTEC_OPT_EVENT_FB_TELL', 0x01000000 );
define( 'PPTEC_OPT_EVENT_FB_LIKE', 0x00200000 );
define( 'PPTEC_OPT_EVENT_REQUIRE_FB_LIKE', 0x00400000 );
define( 'PPTEC_OPT_EVENT_RECURRING', 0x00000400 );
define( 'PPTEC_OPT_EVENT_MULTIDAY', 0x00000800 );
define( 'PPTEC_OPT_EVENT_IS_TPL_MASK', PPTEC_OPT_EVENT_RECURRING|PPTEC_OPT_EVENT_MULTIDAY );
define( 'PPTEC_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'PPTEC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PPTEC_WEB_URL', isset($_ENV['PPTEC_WEB_URL']) ? $_ENV['PPTEC_WEB_URL'] : 'https://www.purplepass.com' );
define( 'PPTEC_GET_EVENTS_LIMIT', 20 );
define( 'PPTEC_MIN_PHP_VERSION', '5.6');
define( 'PPTEC_MIN_WP_VERSION', '4.9.14');

register_activation_hook( __FILE__, 'pptec_check_wp_and_php_version' );

/**
  * Plugin Activation hook function to check for Minimum PHP and WordPress versions
  */
function pptec_check_wp_and_php_version() {
	global $wp_version;
	$wp = PPTEC_MIN_WP_VERSION;
	$php = PPTEC_MIN_PHP_VERSION;

    if ( version_compare( PHP_VERSION, $php, '<' ) )
        $flag = 'PHP';
    elseif
        ( version_compare( $wp_version, $wp, '<' ) )
        $flag = 'WordPress';
    else
		return;

    $version = 'PHP' == $flag ? $php : $wp;
    deactivate_plugins( basename( __FILE__ ) );
	echo "<div class='notice notice-error' style='padding-bottom: 12px; padding-top: 12px;'>
		<p><strong>Purplepass plugin for The Event Calendar</strong> requires $flag $version or higher.</p>
		<p>Contact your Host or your system administrator and ask to upgrade to the latest version of $flag.</p>
	</div>";
	exit;
}

/**
 * Refresh token if it will be expired soon
 */
function pptec_refresh_oauth_token() {
    if (!class_exists('Purplepass_ECP')) {
        require_once 'inc/Purplepass_ECP.php';
    }

	$pp_obj = new Purplepass_ECP();

	$client_id     = get_option( 'pptec_oauth_decrypted_client_id' );
	$refresh_token = get_option( 'pptec_oauth_refresh_token' );

	$server_url = PPTEC_WEB_URL . '/actions/oauth_token.php';
	$response   = wp_remote_post( $server_url, array(
		'method'      => 'POST',
		'timeout'     => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'body'        => array(
			'client_id'     => $client_id,
			'refresh_token' => $refresh_token,
			'grant_type'    => 'refresh_token',
		),
		'cookies'     => array(),
		'sslverify'   => false,
	) );

	if ( !empty( json_decode( $response['body'] )->error ) ) {
		$error_response = json_decode( $response['body'] );
		$pp_obj->pptec_add_log(
				date( 'Y-m-d H:i:s', time() ),
				'Refresh token',
				'Inbound',
			$error_response->error . ', ' . $error_response->error_description . ', ' . $error_response->hint . '. Please, try to reconnect plugin to your account ',
				'Failed' );
		return false;
	}

	if ( empty( $response['body'] ) ) {
		return false;
	}
	$refresh_token_obj = json_decode( $response['body'] );

	$plugin_option = get_option( 'pptec_oauth_settings' );
	$plugin_option = (array) json_decode( $plugin_option );
	if ( ! empty( $refresh_token_obj->access_token ) ) {
		$plugin_option['access_token']       = $refresh_token_obj->access_token;
		$plugin_option['expires_in']         = $refresh_token_obj->expires_in;
		$plugin_option['token_type']         = $refresh_token_obj->token_type;
		$plugin_option['token_created_time'] = time();
	}
	if ( ! empty( $refresh_token_obj->refresh_token ) ) {
		update_option( 'pptec_oauth_refresh_token', $refresh_token_obj->refresh_token );
	}

	update_option( 'pptec_oauth_settings', json_encode( $plugin_option ) );

	if ( !empty( $refresh_token_obj->access_token ) ) {
		return $refresh_token_obj->access_token;
	} else {
		return false;
	}
}


/**
 * Get Auth token
 *
 * @return bool|mixed
 */
function pptec_get_access_token(){

	$plugin_option = get_option( 'pptec_oauth_settings' );

	if (empty($plugin_option)) {
	    return false;
    }

	$plugin_option = (array) json_decode( $plugin_option );

	// refresh token if it time to live less then 10 minutes
	if ( !empty( $plugin_option['token_created_time'] ) && !empty( $plugin_option['expires_in'] ) ) {
		$time_to_expire_token = (int)$plugin_option['expires_in'] - 600; // 10 minutes
		$time_to_expire = (int)$plugin_option['token_created_time'] + (int)$time_to_expire_token;
		if ( time() >= $time_to_expire ) {
			$plugin_option['access_token'] = pptec_refresh_oauth_token();
		}
	}

	return ( !empty( $plugin_option['access_token'] ) ) ? $plugin_option['access_token'] : false;
}



/**
 * Remove all plugin data, it will be done when Unlink/Link account
 */
function pptec_remove_unlinked_account_data()
{
//	update_option( 'pptec_oauth_settings', array() );

	// Truncate events queue for daily cron syncing (in case something went wrong)
	global $wpdb;
    $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'pp_sync_events');

    delete_option( 'pptec_oauth_settings' );
    delete_option( "pptec_events_last_requested_date" );
	delete_option( "pptec_random_str" );
	delete_option( "pptec_oauth_decrypted_client_id" );
	delete_option( "pptec_oauth_refresh_token" );
	delete_option( "pptec_data" ); // @todo: change name
	delete_option( "pptec_wizard" );
	delete_option( "pptec_job_events_fetching" );
	delete_option( "pptec_stats_update_time" );
	delete_user_meta( get_current_user_id(), 'pptec_pp_user_id' );
}



/**
 * Get Purplepass user ID
 *
 * @return mixed
 */
function pptec_oauth_get_pp_user_id(){
    // @todo v2: get from user meta

	$plugin_option = get_option( 'pptec_oauth_settings' );
	$plugin_option = (array) json_decode( $plugin_option );

	return !empty($plugin_option['pp_user_id']) ? $plugin_option['pp_user_id'] : 0;
}


/**
 * Check, if token exists
 */
function check_if_token_exists(){

	$str_resp = '<strong class="green-true">(Account linked)</strong>';
	$status = 'linked';
	$access_token = pptec_get_access_token();

	if ( empty( $access_token ) ) {
		$str_resp = '<strong class="red-false">(Account unlinked)</strong>';
		$status = 'unlinked';
	}

	$response = array(
		'message' => $str_resp,
		'account_status' => $status
	);

	return $response;
}


if ( isset( $_GET['post'] ) ) {
	$ptype = get_post_type( sanitize_text_field($_GET['post']) );
} else {
	$ptype = false;
}

if ( ( ! empty( $_GET['post_type'] ) && $_GET['post_type'] === 'tribe_events' ) || 'tribe_events' === $ptype ) {
	$account_info = check_if_token_exists();
	if ( 'linked' !== $account_info['account_status'] ) {
		add_action( 'admin_notices', 'pptec_link_plugin_notice' );
	}

	require_once 'classic-editor.php';
}


/**
 * Display an error message when purplepass account unlinked
 */
function pptec_link_plugin_notice() {
	echo '<div class="notice notice-error" style="padding-bottom: 12px; padding-top: 12px;">';
	require_once 'inc/html/unlink-message.php';
	echo '</div>';
}

// hooks
register_activation_hook(__FILE__, 'pptec_check_event_plugin_activating');
function pptec_check_event_plugin_activating()
{
    add_option('Activated_Plugin', 'purplepass-events');

    // Default widget settings
    update_option('pptec_widget_settings', array(
        'widget_color' => 'cccccc',
        'widget_help_text' => '',
        'widget_width' => '',
        'enabled_cart' => false,
        'cbx_replace' => true
    ));

    pptec_create_db_schema();

    // cron job for sync events daily
    if (!wp_next_scheduled('pptec_job_daily_actions')) {
        wp_schedule_event(time(), 'daily', 'pptec_job_daily_actions');
    }
}

// create categories
add_action( 'init', 'pptec_init_plugin_core', 11 );

// Register cron handler for Events fetching
add_action('pptec_cron_fetch_event_processing', 'pptec_fetch_event_processing', 10);

/**
 * Get events from Purplepass.
 *
 * @param $wp_user_id
 * @return array|bool|mixed|void
 */
function pptec_fetch_event_processing($wp_user_id) {
	if (!class_exists('Purplepass_ECP')) {
		require_once 'inc/Purplepass_ECP.php';
	}

	// Check there is no running process
    $job_events_fetching = get_option('pptec_job_events_fetching');
    if (!empty($job_events_fetching['status']) && 'initialized' !== $job_events_fetching['status']) {
        return false;
    }

    $pp_obj = new Purplepass_ECP();

	/**
	 * Add cron job for checking background process failing
	 */
	if( !wp_next_scheduled('pptec_check_bg_failed_process', array($wp_user_id) ) ){
		wp_schedule_event( time(), 'pptec_every_5_min', 'pptec_check_bg_failed_process', array($wp_user_id) );
		add_action( 'pptec_check_bg_failed_process', 'pptec_checking_unfinished_background_jobs', 10 );
	}

	$access_token = pptec_get_access_token();
	$args = array(
		'timeout' => 45,
		'headers' => array(
			'Authorization' => 'Bearer ' . $access_token,
		),
	);

	// check if there is Requested option
	$last_requested_date = get_option( 'pptec_events_last_requested_date' );

	// set initial counters for set interval steps
	$start = 0;
	$paged = 0;

	// Events counters
	$total = 0;
	$added = 0;
	$updated = 0;

	// Array where we store pp_venue_id to wp_venue_id binding. Needed to decrease load on database.
    $venues_bindings = array();

	$loop_finish = false;

	// PP events ids that require stats to be fetched after data fetch
	$pp_events_ids_fetch_stats = array();

	while ( false === $loop_finish ) {
		$query_string = '/api/events/list?start=' . $start . '&limit=' . PPTEC_GET_EVENTS_LIMIT . '&sort=startson&dir=desc&events_filter=current' . ((false !== $last_requested_date) ? '&requested=' . $last_requested_date : '');

		$remote_data = wp_remote_get( PPTEC_WEB_URL . $query_string, $args );

		if ( is_wp_error( $remote_data ) || empty( $remote_data["body"] ) ) {
			break;
		}

		$events_array = json_decode( $remote_data["body"] );

		$total = !empty($events_array->cnt) ? (int)$events_array->cnt : 0;
		$requested_time = !empty($events_array->requested) ? $events_array->requested : '';

		// Set initial progress values
		if ( 0 === $paged ) {
			update_option( 'pptec_job_events_fetching', array(
				'status'  => 'started',
				'time'    => time(),
				'total'   => $total,
				'added'   => 0,
				'updated' => 0,
				'message' => 'Fetching...',
			) );
		}

		if ( 0 === $total ) {
			$loop_finish = true;
			break;
		}

		foreach ( $events_array->events as $event_data ) {
			$processing_result = $pp_obj->pptec_create_update_event( $wp_user_id, $event_data->id, $event_data, $venues_bindings );
			$added += (int)$processing_result['added'];
			$updated += (int)$processing_result['updated'];

			if (1 === (int)$processing_result['added']) {
			    $pp_events_ids_fetch_stats[] = $event_data->id;
            }
		}

		update_option( 'pptec_job_events_fetching', array(
			'status'  => 'running',
			'time'    => time(),
			'total'   => $total,
			'added'   => $added,
			'updated' => $updated,
			'message' => ($added + $updated) . " out of $total events processed..."
		) );

		if ( $paged >= floor( ((int)$events_array->cnt / PPTEC_GET_EVENTS_LIMIT )) ) {
			$loop_finish = true;
			break;
		}

		$start += PPTEC_GET_EVENTS_LIMIT;
		$paged++;
	}

	if ( !empty($requested_time) ) {
		update_option( 'pptec_events_last_requested_date', $requested_time );
	}

	$job_events_fetching = array(
		'status'  => 'finished',
		'time'    => time(),
		'total'   => $total,
		'added'   => $added,
		'updated' => $updated,
		'message' => 0 === $total ? 'No new events found' : "Results: $added added, $updated updated",
	);
	update_option( 'pptec_job_events_fetching', $job_events_fetching );

	if (!empty($pp_events_ids_fetch_stats)) {
        pptec_fill_stats_after_fetching($pp_events_ids_fetch_stats);
    }

	wp_unschedule_event(wp_next_scheduled('pptec_cron_fetch_event_processing'), 'pptec_cron_fetch_event_processing');
    wp_unschedule_event(wp_next_scheduled('pptec_check_bg_failed_process'), 'pptec_check_bg_failed_process');

	return $job_events_fetching;
}

function pptec_fill_stats_after_fetching($pp_events_ids) {
	$data = pptec_get_events_statistic_from_pp($pp_events_ids);

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

		if ( $data->cnt > 0 ) {
			update_option( 'pptec_stats_update_time', time() );
			echo '1';
		} else {
			echo 0;
		}
	}
}

function pptec_get_wp_event_id_by_pp_event_id($pp_event_id)
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT `wp_event_id` FROM `{$wpdb->prefix}pptec_events` WHERE `pp_event_id` = " . (int)$pp_event_id );

	return !empty($results[0]->wp_event_id) ? (int)$results[0]->wp_event_id : false;
}

function pptec_get_pp_event_id_by_wp_event_id($wp_event_id)
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT `pp_event_id` FROM `{$wpdb->prefix}pptec_events` WHERE `wp_event_id` = " . (int)$wp_event_id );

	return !empty($results[0]->pp_event_id) ? (int)$results[0]->pp_event_id : false;
}

function pptec_get_pp_user_id_by_wp_event_id($wp_event_id)
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT `pp_user_id` FROM `{$wpdb->prefix}pptec_events` WHERE `wp_event_id` = " . (int)$wp_event_id );

	return !empty($results[0]->pp_user_id) ? (int)$results[0]->pp_user_id : false;
}

function pptec_get_pp_user_id_by_pp_event_id($pp_event_id)
{
    global $wpdb;
    $results = $wpdb->get_results("SELECT `pp_user_id` FROM `{$wpdb->prefix}pptec_events` WHERE `pp_event_id` = " . (int)$pp_event_id );

    return !empty($results[0]->pp_user_id) ? (int)$results[0]->pp_user_id : false;
}

function pptec_get_wp_user_id_by_pp_user_id($pp_user_id)
{
    global $wpdb;
    $results = $wpdb->get_results("SELECT `user_id` FROM `{$wpdb->prefix}usermeta` WHERE `meta_key` = 'pptec_pp_user_id' AND `meta_value` = " . (int)$pp_user_id );

    return !empty($results[0]->user_id) ? (int)$results[0]->user_id : false;
}

function pptec_get_wp_venue_id_by_pp_venue_id($pp_venue_id)
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT `wp_venue_id` FROM `{$wpdb->prefix}pptec_wp_venue_to_pp_venue` WHERE `pp_venue_id` = " . (int)$pp_venue_id );

	return !empty($results[0]->wp_venue_id) ? (int)$results[0]->wp_venue_id : false;
}

function pptec_get_pp_venue_id_by_wp_venue_id($wp_venue_id)
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT `pp_venue_id` FROM `{$wpdb->prefix}pptec_wp_venue_to_pp_venue` WHERE `wp_venue_id` = " . (int)$wp_venue_id );

	return !empty($results[0]->pp_venue_id) ? (int)$results[0]->pp_venue_id : false;
}

function pptec_bind_pp_venue_id_to_wp_venue_id($pp_venue_id, $wp_venue_id, $pp_event_id, $wp_event_id)
{
	global $wpdb;
	$prefix = $wpdb->prefix;

	// @todo v2: remove
	$wpdb->query($wpdb->prepare("
        INSERT INTO `{$prefix}pptec_events`
            (`wp_event_id`, `pp_event_id`, `wp_venue_id`, `pp_venue_id`)
            VALUES ( %d, %d, %d, %d )
        ON DUPLICATE KEY UPDATE
            `wp_venue_id` = %d,
            `pp_venue_id` = %d
        ", $wp_event_id, $pp_event_id, $wp_venue_id, $pp_venue_id, $wp_venue_id, $pp_venue_id
	));

	// Also store binding in separate table, as we are going to remove venue_ids from `pptec_events`
	$wpdb->query($wpdb->prepare("
        INSERT INTO `{$prefix}pptec_wp_venue_to_pp_venue`
            (`wp_venue_id`, `pp_venue_id`)
            VALUES ( %d, %d )
        ON DUPLICATE KEY UPDATE
            `wp_venue_id` = %d,
            `pp_venue_id` = %d
        ", $wp_venue_id, $pp_venue_id, $wp_venue_id, $pp_venue_id
	));
}

/**
 * Insert Purplepass event ID to WP and connect po PostID
 *
 * @param $pp_id
 * @param $post_id
 */
function pptec_bind_pp_event_id_to_wp_event_id($pp_event_id, $wp_event_id)
{
	$pp_user_id = pptec_oauth_get_pp_user_id();

	global $wpdb;
	$prefix = $wpdb->prefix;
	$wpdb->query($wpdb->prepare("
        INSERT INTO `{$prefix}pptec_events`
            (`wp_event_id`, `pp_event_id`, `pp_user_id`)
            VALUES ( %d, %d, %d )
        ON DUPLICATE KEY UPDATE
            `wp_event_id` = %d,
            `pp_event_id` = %d
        ", $wp_event_id, $pp_event_id, $pp_user_id, $wp_event_id, $pp_event_id
    ));
}

/**
 * Redirect on settings page if plugin activated
 *
 * @param $plugin
 */
function pptec_cyb_activation_redirect( $plugin ) {
	if( $plugin == plugin_basename( __FILE__ ) ) {
		header('Location: ' . admin_url('admin.php?page=purplepass&wizard=1'));
		exit();
	}
}
add_action( 'activated_plugin', 'pptec_cyb_activation_redirect' );


/**
 * Create plugin tables for events relationship
 */
function pptec_create_db_schema()
{
    if (!function_exists('dbDelta')) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	}

	global $wpdb;

	$prefix = $wpdb->prefix;

	// @todo v2: remove wp_venue_id and pp_venue_id from pptec_events. They can be retrieved by wp_event_id
	$sql = "CREATE TABLE IF NOT EXISTS `{$prefix}pptec_events` (
	`wp_event_id` int(11) NOT NULL,
	`pp_event_id` int(11) NOT NULL,
	`pp_user_id` int(11) DEFAULT NULL,
	`wp_venue_id` int(11) DEFAULT NULL,
	`pp_venue_id` int(11) DEFAULT NULL,
	PRIMARY KEY(`wp_event_id`, `pp_event_id`)
	);";
	dbDelta( $sql );

	$venue_sql = "CREATE TABLE IF NOT EXISTS `{$prefix}pptec_wp_venue_to_pp_venue` (
	`wp_venue_id` int(11) NOT NULL,
	`pp_venue_id` int(11) NOT NULL,
	PRIMARY KEY(`wp_venue_id`, `pp_venue_id`)
	);";
	dbDelta( $venue_sql );

	$sql_pp = "CREATE TABLE IF NOT EXISTS " . $prefix . "pp_sync_events" . " (
	`id` mediumint(9) NOT NULL AUTO_INCREMENT,
	`event_id` int(11) NOT NULL,
	`action` varchar(50) NOT NULL,
	UNIQUE KEY id (id)
	);";
	dbDelta( $sql_pp );

	// Log table
	$logs_pp = "CREATE TABLE IF NOT EXISTS " . $prefix . "pp_logs" . " (
	`id` bigint NOT NULL AUTO_INCREMENT,
	`event_time` varchar(50) NOT NULL,
	`event_action` varchar(50) NOT NULL,
	`event_direction` varchar(50) NOT NULL,
	`event_details` TEXT NOT NULL,
	`event_status` varchar(50) NOT NULL,
	UNIQUE KEY id (id)
	);";
	dbDelta( $logs_pp );

	// Stats table
	$stats_pp = "CREATE TABLE IF NOT EXISTS " . $prefix . "pp_stats" . " (
	`pp_event_id` int(11) NOT NULL,
	`wp_event_id` int(11) NOT NULL,
	`tickets_sold` varchar(50) NOT NULL,
	`revenue` varchar(50) NOT NULL,
	PRIMARY KEY(wp_event_id, pp_event_id)
	);";
	dbDelta( $stats_pp );
}


/**
 * Enqueue admin scripts and styles
 */
function pptec_load_custom_wp_admin_style() {
	$ver = time();
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script( 'admin_pp_js_pp_init', PPTEC_WEB_URL . '/js/pp/init.js' );
	wp_enqueue_style( 'admin_pp_css_datetimepicker', plugins_url('css/jquery.datetimepicker.css', __FILE__), $ver );
	wp_enqueue_style( 'admin_pp_custom_events', plugins_url('css/admin-event-purple.css', __FILE__), $ver );
	wp_enqueue_style( 'admin_pp_jquery_ui_css_events', plugins_url('css/jquery-ui.css', __FILE__), $ver );
	wp_enqueue_style( 'admin_pp_steps_css', plugins_url('css/jquery.steps.css', __FILE__), $ver );

	if ( isset( $_GET['post'] ) ) {
		$ptype = get_post_type( sanitize_text_field($_GET['post']) );
	} else {
		$ptype = false;
	}

	if ( ( ! empty( $_GET['post_type'] ) && $_GET['post_type'] === 'tribe_events' ) || 'tribe_events' === $ptype ) {
		wp_enqueue_style( 'admin_pp_selectize_css', plugins_url('css/selectize.default.css', __FILE__), $ver );
		wp_enqueue_script( 'admin_pp_js_selectize', plugins_url('js/selectize.js', __FILE__), array('jquery'), $ver );
		wp_enqueue_script( 'admin_pp_js_cust-header-scripts', plugins_url('js/event-header-scripts.js', __FILE__), array('jquery'), $ver );
	}

    if ( ( ! empty( $_GET['post_type'] ) && $_GET['post_type'] === 'tribe_venue' ) || 'tribe_venue' === $ptype ) {
        wp_enqueue_script( 'admin_pp_js_ecp_venues', plugins_url('js/ecp_venues.js', __FILE__), array('jquery'), $ver );
    }

    if ( !empty( $_GET['page'] ) ) {
		if ( $_GET['page'] === 'scanning-selling-gear'
		     || $_GET['page'] === 'help-support'
		     || $_GET['page'] === 'order-custom-tickets'
		     || $_GET['page'] === 'purplepass-stats'
		     || $_GET['page'] === 'payments' ) {
			wp_enqueue_style( 'admin_pp_pages_css', plugins_url('inc/html/css/pages.css', __FILE__), $ver );
		}
	}

	wp_enqueue_style( 'admin_pp_awesome_css', plugins_url('inc/html/font-awesome-4.7.0/css/font-awesome.min.css', __FILE__), $ver );
	wp_enqueue_style( 'admin_pp_google_css', 'https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap', $ver );
	wp_enqueue_style( 'admin_pp_fnt_css', 'https://use.fontawesome.com/releases/v5.6.1/css/all.css', $ver );

	if ( !empty( $_GET['page']  ) && $_GET['page'] === "scanning-selling-gear" ) {
		wp_enqueue_style( 'admin_pp_pp_gear_css', plugins_url('inc/html/css/gear.css', __FILE__), $ver );
	}

	wp_enqueue_script( 'admin_pp_js_repeater', plugins_url('js/repeater.js', __FILE__), array('jquery'), $ver );
	wp_enqueue_script( 'admin_pp_js_steps', plugins_url('js/jquery.steps.min.js', __FILE__), array('jquery'), $ver );

	wp_enqueue_script( 'admin_pp_js_common', plugins_url( 'js/common.js', __FILE__ ),
		array(
			'jquery',
			'admin_pp_js_repeater',
			'admin_pp_js_pp_init',
		),
		$ver );
	wp_enqueue_script( 'admin_pp_js_price', plugins_url('js/price.js', __FILE__), array('admin_pp_js_repeater'), $ver );
	wp_enqueue_script( 'admin_pp_js_tablesort', plugins_url('js/log-sort/tablesort.js', __FILE__), array('admin_pp_js_repeater'), $ver );

	wp_localize_script( 'admin_pp_js_common', 'pp_js', array(
		'site_url' => esc_js(site_url('', 'relative')),
		'url'   => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('ajax-nonce'),
		'plugin_url' => PPTEC_PLUGIN_DIR,
		'is_alternate_wp_cron' => defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON,
        'pp_user_id' => pptec_oauth_get_pp_user_id()
	) );

}
add_action( 'admin_enqueue_scripts', 'pptec_load_custom_wp_admin_style' );


/**
 * Frontend scripts
 */
function pptec_load_frontend_scripts(){
	wp_enqueue_script( 'fr_pp_js_init', PPTEC_WEB_URL . '/js/pp/init.js' );
}
add_action( 'wp_enqueue_scripts', 'pptec_load_frontend_scripts' );

/**
 * Enqueue admin scripts and styles
 */
function pptec_load_front_scripts()
{
    $pptec_widget_settings = get_option('pptec_widget_settings');
	$should_replace = isset($pptec_widget_settings['cbx_replace']) ? (bool)$pptec_widget_settings['cbx_replace'] : false;
    $wp_event_id = get_the_ID();
    if (!empty($wp_event_id) && $should_replace) {
        wp_enqueue_script('admin_pp_js_price', plugins_url('js/front-scripts.js', __FILE__), array('jquery'), time());
    }
}
add_action( 'wp_enqueue_scripts', 'pptec_load_front_scripts' );


/**
 * Checking if dependent plugins loaded
 */
function pptec_load_plugin() {

	if ( is_admin() && get_option( 'Activated_Plugin' ) == 'purplepass-events' ) {
		delete_option( 'Activated_Plugin' );

		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			add_action( 'admin_notices', 'pptec_self_deactivate_notice' );

			//Deactivate our plugin
			deactivate_plugins( plugin_basename( __FILE__ ) );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}

}

add_action('admin_init', 'pptec_load_plugin');

/**
 * Display an error message when dependent plugins are missing
 */
function pptec_self_deactivate_notice() {
	?>
	<div class="notice notice-error" style="padding-bottom: 12px; padding-top: 12px;">
		To activate the Purplepass plugin for The Event Calendar, you need to first install and activate <a target="_blank" href="https://wordpress.org/plugins/the-events-calendar/">The Events Calendar</a>
	</div>
	<?php
}


add_action('admin_menu', 'pptec_purple_settings_page');
function pptec_purple_settings_page() {
	add_menu_page('Purplepass Settings', 'Purplepass', 'administrator', 'purplepass', 'pptec_purple_settings_page_data', PPTEC_PLUGIN_DIR.'img/icon.png' );
	add_submenu_page('purplepass', 'Stats', 'Stats', 'administrator', 'purplepass-stats', 'pptec_purple_stats' );
	add_submenu_page('purplepass', 'Order Custom Tickets', 'Order Custom Tickets', 'administrator', 'order-custom-tickets', 'pptec_order_custom_tickets' );
	add_submenu_page('purplepass', 'Payments', 'Payments', 'administrator', 'payments', 'pptec_payments' );
	add_submenu_page('purplepass', 'Apps & Equipment', 'Apps & Equipment', 'administrator', 'scanning-selling-gear', 'pptec_scanning_selling_gear' );
	add_submenu_page('purplepass', 'Log Info', 'Log info', 'administrator', 'log-info', 'pptec_purple_log_info' );
	add_submenu_page('purplepass', 'Help & Feedback', 'Help & Feedback', 'administrator', 'help-support', 'pptec_help_support' );

	//add_submenu_page('purplepass', 'Events Synchronization', 'Sync Events', 'administrator', 'redirect-pp-page', 'pptec_purple_redirect_page' );
}


/**
 * Plugin settings pagepurple_settings_page_data
 */
function pptec_purple_settings_page_data() {
	$pptec_wizard = get_option( 'pptec_wizard' );

	if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) {

		if ( !empty($_GET['wizard']) && empty($pptec_wizard) ) :

			?>

			<div class="settings-wr">
				<br>
				<h1>Purplepass plugin for The Event Calendar</h1>
				<br>
				<fieldset class="setting-fieldset">
					<p>
						Now that the Purplepass plugin is installed, we just need to link it to your existing Purplepass account.
						Click the button below to be taken to Purplepass so you can login and grant access to the plugin.
					</p>
					<a class="setting-btn button button-primary button-large" href="<?php echo site_url(); ?>/wp-admin/admin.php?page=purplepass&amp;ppauth=true">
						<img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/Login_icon_16.png" style="margin-bottom:0px;">&nbsp;Link Purplepass account</a>
					<br>
				</fieldset>
			</div>

			<script>
				jQuery("#example-basic").steps({
					headerTag: "h3",
					bodyTag: "section",
					transitionEffect: "slideLeft",
					autoFocus: true
				});
			</script>

		<?php else: ?>
            <?php
                $pptec_widget_settings = get_option('pptec_widget_settings');
				$widget_color     = !empty( $pptec_widget_settings['widget_color'] ) ? $pptec_widget_settings['widget_color'] : 'cccccc';
                $widget_help_text = !empty( $pptec_widget_settings['widget_help_text'] ) ? $pptec_widget_settings['widget_help_text'] : '';
                $widget_width     = !empty( $pptec_widget_settings['widget_width'] ) ? $pptec_widget_settings['widget_width'] : '';
                $enabled_cart     = !empty( $pptec_widget_settings['enabled_cart'] ) ? $pptec_widget_settings['enabled_cart'] : false;
                $cbx_replace      = !empty( $pptec_widget_settings['cbx_replace'] ) ? $pptec_widget_settings['cbx_replace'] : false;
            ?>

			<div class="wrapper-pp-settings">
				<div style="background: #dbdbdb; width: 100%; padding: 15px; color: #ffffff; font-size: 22px; margin-bottom:-10px;">
					<span style="color:#666666;">Settings</span>
					<img style="width: 100px; float:right;" src="<?php echo PPTEC_PLUGIN_DIR . 'img/pp-log.png'; ?>">
				</div>

				<br>
				<br>
				<fieldset class="setting-fieldset">
					<br>
					<h2>Widget settings:</h2>

					<!-- Enable / Disable widget in content -->

					<div class="wrapper-event-type additional-options">
						<table>
							<tr>
								<td>
									<label>
										<input type="checkbox" <?php echo $cbx_replace ? 'checked' : ''; ?>
											   name="replace_by_widgets"
											   class="replace_by_widgets">
										<span class="input-checkmark"></span>
										<span class="text-label setting-label">Automatically add the Purplepass ticket purchase widget when you create a new event</span><br>
										<span class="pp_evt_repl"></span>
									</label>
								</td>
							</tr>

						</table>

					</div>
					<br>


					<!-- widget settings -->

					<div class="wrapper-event-type custom_fields additional-options settings-page-wr">
						<table>
							<tr>
								<td>
									<label for="add_custom_widget_color">
										<style>

											#add_custom_widget_color > option:hover {
												background: #a80a05 !important;
											}

											#add_custom_widget_color option{
												font-size: 150%;
												padding-top:5px;
												padding-bottom:5px;
												color:#ffffff;
											}
											#add_custom_widget_color option:hover{
												background: none;
											}

											.add_custom_widget_text.widget-txt-fields, .add_custom_widget_width{
												/*width: 500px !important;*/
												height: 50px !important;
												margin-right: 11px !important;
												border-radius: 5px !important;
												background-color: #f8f8f8 !important;
												border: 1px solid #ebebeb !important;
												color: #5a677a !important;
												font-size: 14px !important;
												font-weight: 400 !important;
												letter-spacing: .7px !important;
												padding: 13px;
												text-align: left;
											}
											#add_custom_widget_color:hover{
												cursor: pointer;
											}
											.add_custom_widget_text:hover{
												cursor: default;
											}

											.select2-results__option--highlighted {
												background-color: #BADA55 !important;
											}

											.selected-color-select{
												cursor: pointer;
												width: 410px;
												background: #ccc;
												margin-top: 20px;
												padding: 15px;
												font-size: 16px;
												border-radius: 4px;
												color: #ffffff;
											}

											.add_custom_widget_color_c div{
												width: 410px;
												padding: 7px;
												color:#ffffff;
											}
											.add_custom_widget_color_c div:hover{
												color:#424242;
												font-weight:bold;
											}

										</style>

										<span style="display: block" class="setting-label">Widget color theme</span>
										<div style="display: none;" class="selected-color-select" data-selected_color="<?php echo $widget_color; ?>"></div>
										<div class="horizontal-color">
											<div data-value="cccccc"><span style="width:40px; background: #cccccc; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="000000"><span style="width:40px; background: #000000; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="000099"><span style="width:40px; background: #000099; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="009900"><span style="width:40px; background: #009900; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="01b2aa"><span style="width:40px; background: #01b2aa; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="336633"><span style="width:40px; background: #336633; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="5dbfed"><span style="width:40px; background: #5dbfed; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="a350cd"><span style="width:40px; background: #a350cd; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="b20101"><span style="width:40px; background: #b20101; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="b49d7e"><span style="width:40px; background: #b49d7e; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="cca2e0"><span style="width:40px; background: #cca2e0; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="cccc33"><span style="width:40px; background: #cccc33; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="efa038"><span style="width:40px; background: #efa038; height:40px; display: block; border-radius: 50%;"></span></div>
											<div data-value="ff00a8"><span style="width:40px; background: #ff00a8; height:40px; display: block; border-radius: 50%;"></span></div>
										</div>

										<script>
											jQuery(document).ready(function($) {

												let saved_color = 'cccccc';
												let sel_col = $(".selected-color-select").attr("data-selected_color");
												if ( sel_col ) {
													saved_color = sel_col;
												}

												$('.horizontal-color div').each(function () {
													if ( $(this).attr('data-value') === saved_color ) {
														$(this).find("span").addClass("active-color");
													}
												});

												$('.horizontal-color div').on('click', function () {
													$('.horizontal-color div').each(function () {
														$(this).find("span").removeClass("active-color");
													});
													$(this).find("span").addClass("active-color");
													let current_color = $(this).attr("data-value");
													$(".selected-color-select").attr("data-selected_color", current_color);
												});

											});
										</script>

									</label>

									<br>
									<br>
									<span style="display: block" class="setting-label">Help text (Appears above the widget)</span>
									<div class="wrap-location">
										<label for="add_custom_widget_text">
											<input type="text" id="add_custom_widget_text"
												   name="add_custom_widget_text"
												   style="width:900px !important;"
												   value="<?php echo $widget_help_text; ?>"
												   class="widget-txt-fields add_custom_widget_text">
										</label>
									</div>

									<br>
									<br>
									<span style="display: block" class="setting-label">Widget width in px (If blank, width will be 100%)</span>
									<div class="wrap-location">
										<label for="add_custom_widget_width">
											<input type="text" id="add_custom_widget_width"
												   name="add_custom_widget_width"
												   style="width:100px !important;"
												   value="<?php echo $widget_width; ?>"
												   class="widget-txt-fields add_custom_widget_width">
										</label>
                  </div>
                  
                  <div class="field field-enable-shopping-cart">
                    <p class="field-title title-strong">
                      Enable shopping cart feature
                      <b class="tooltip">
                        <i class="fas fa-question-circle"></i>
                        <span class="tooltiptext" style="text-align: left; padding: 20px;">
                          This is a useful feature if you plan on having multiple events on sale at the same time.  When enabled, your guests can make a selection from one event,
                          add it to their shopping cart, continue adding items from other events, and when they are ready, they can check out in one single transaction.
                        </span>
                      </b>
                    </p>

                    <div class="field-switcher-custom">
                      <span>Off</span> 
                      <div class="switch-btn"></div>
											<span>On</span>
                    </div>
                    
										<input type="text" id="pp_enabled_widget_cart" class="pp_enabled_widget_cart" style="display:none;" name="pp_enabled_widget_cart" value="<?php echo esc_textarea( $enabled_cart ); ?>" >
                  </div>
                  
									<a class="setting-btn save-ajax-widget-settings button button-primary button-large" href="#"> <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/Save_Icon_16.png" style="margin-bottom:0px;">&nbsp; Save data</a>
									<br>
									<span class="ajax-widget-settings-result"></span>

									<script>
										jQuery(document).ready(function($) {
											$(".switch-btn").click(function(e){
												e.preventDefault();
												$(this).toggleClass("switch-on");
												if ( $(this).hasClass("switch-on") ) {
													$(".pp_enabled_widget_cart").prop("value", "1");
												} else {
													$(".pp_enabled_widget_cart").prop("value", "0");
												}
											});
											let cart_enabled = $(".pp_enabled_widget_cart").val();
											if ( '1' === cart_enabled || 1 === cart_enabled ) {
												$(".switch-btn").toggleClass("switch-on");
											}
										});
									</script>
									<div style="clear: both;"></div>

								</td>
							</tr>

						</table>

					</div>
					<br>

				</fieldset>

				<?php $access_token = pptec_get_access_token(); ?>
				<?php if ( !empty( $access_token ) && !isset( $_GET['token_errors'] ) ) : ?>
					<br><br>
					<span class="plugin-url-span" style="display: none;" data-plugin_url="<?php echo PPTEC_PLUGIN_DIR; ?>"></span>
					<fieldset class="setting-fieldset">
						<?php

						$job_events_fetching = get_option('pptec_job_events_fetching');

						$last_fetching_date = !empty($job_events_fetching['time']) ? $job_events_fetching['time'] : '';
						$status = !empty($job_events_fetching['status']) ? $job_events_fetching['status'] : '';
						$total = !empty($job_events_fetching['total']) ? $job_events_fetching['total'] : 0;
						$message = !empty($job_events_fetching['message']) ? $job_events_fetching['message'] : '';

						?>
						<br>
						<h2 class="event-hid-data" data-status="<?php echo esc_html($status); ?>" data-total="<?php echo (int)$total; ?>">
                            Manually Sync Events
                            <span class="time-fetching"><?php
							if ( !empty( $job_events_fetching['status'] ) && 'finished' === $job_events_fetching['status'] ) {
								if ( !empty( $last_fetching_date ) ) {
									// date format: June 7th, 2020 at 2:06am UTC+0
									$gmt_offset = get_option('gmt_offset');
									$timezone_offset = $gmt_offset >= 0 ? "+" . $gmt_offset : $gmt_offset;
									$timezone_utc = $last_fetching_date;
									echo '  (Last sync: ' . date( 'F jS, Y \a\t g:ia ' . pptec_take_timezone_from_offset($timezone_offset)  , strtotime($timezone_offset . ' hours', $timezone_utc)  ) . ')';
								}
							}
							?>
                            </span>
						</h2>
                        <br>
                        <a class="manual-fetch-events-from-pp button button-large <?php if ('running' === $status) { ?>spinner-active<?php } ?>" href="#">
                            <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/Refresh_Stats_16.png">
                            <div>Fetch events from Purplepass</div>
                        </a>
                        <br>
                        <div class="img-fetch-preloader <?php if ('running' === $status) { ?>spinner-active<?php } ?>">
                            <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
                        </div>
                        <p class="green-cron <?php if ('running' === $status) { ?>spinner-active<?php } ?>">Please wait while your events are loaded</p>
						<div class="sync-messages"><?php echo ('running' === $status) && $message ? $message : ''; ?></div>
						<br>
					</fieldset>
				<?php endif; ?>
				<br><br>

				<fieldset class="setting-fieldset login-linked-account-section">

					<?php require_once 'blocks/login-settings.php';?>

				</fieldset>
				<br>

				<br>
			</div>

		<?php endif; ?>

		<?php

	} else {
		?>
		<style>
			.wp-has-submenu.wp-has-current-submenu.toplevel_page_purplepass {
				display: none !important;
			}
		</style>
		<?php
	}

}



/**
 * Scanning selling gear
 */
function pptec_scanning_selling_gear(){
	require_once ("inc/html/gear.php");
}

/**
 * Help & Feedback
 */
function pptec_help_support(){
	require_once ("inc/html/help-support.php");
}

/**
 * Order custom tickets
 */
function pptec_order_custom_tickets(){
	require_once ("inc/html/order-custom-tickets.php");
}

/**
 * Payments
 */
function pptec_payments(){
	require_once ("inc/html/payments.php");
}



function pptec_add_action_links( $links ) {

	$account_info = check_if_token_exists();

	if ( 'linked' === $account_info['account_status'] ) {
		$links = array_merge( $links, array(
			'<a class="unlink-account" style="position:relative !important; margin:0px !important;" href="javascript:;">' . __( 'Unlink account', 'pptec' ) . '</a>'
		) );
	} else {
		$links = array_merge( $links, array(
			'<a href="' . site_url() . '/wp-admin/admin.php?page=purplepass&ppauth=true">' . __( 'Link Purplepass account', 'pptec' ) . '</a>'
		) );
	}

	$links = array_merge( $links, array(
		'<a href="' . esc_url( admin_url( '/?page=purplepass' ) ) . '">' . __( 'Settings', 'pptec' ) . '</a>'
	) );

	$links = array_merge( $links, array(
		'<a href="/wp-admin/admin.php?page=help-support">' . __( 'Help', 'pptec' ) . '</a>'
	) );

	return $links;

}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pptec_add_action_links' );


/**
 * Log page
 */
function pptec_purple_log_info() {
	?>
	<div class="wrap">
		<br><br>
		<?php
		require_once 'blocks/logs-table.php';
		?>
	</div>
	<?php
}


/**
 * Stats page
 */
function pptec_purple_stats() {
	?>
	<div class="wrap" style="font-family: 'Poppins', sans-serif;">
		<br><br>
		<?php
		require_once 'blocks/stats-table.php';
		?>
	</div>
	<?php
}


add_action('init', 'pptec_check_redirect_url');
function pptec_check_redirect_url() {
	if ( isset( $_GET['ppauth'] ) ) {

        $account_info = check_if_token_exists();
        if (!empty($account_info['account_status']) && 'unlinked' === $account_info['account_status']) {
			update_option( 'pptec_wizard', 'yes' );

			require_once 'inc/Purplepass_ECP.php';
			$pp_object = new Purplepass_ECP();

			$pp_object->pptec_create_auth_request();
        }
	}
}


/**
 * Create terms when plugin activated
 */
function pptec_init_plugin_core() {

	require_once 'inc/Purplepass_ECP.php';
	require_once 'functions.php';
	require_once 'metabox.php';

}


/**
 * PP events shortcode - Show all events
 *
 * @param $atts
 *
 * @return string
 */
function pptec_pp_event_shortcode( $atts ){

	$uid = $atts['user_id'];

	$script = '<pp:tickets width="100%" event-all="'.$uid.'"></pp:tickets>';

	return $script;
}
add_shortcode('pp_all_events', 'pptec_pp_event_shortcode');


/**
 * PP events shortcode - Show one event by ID
 *
 * @param $atts
 *
 * @return string
 */
function pptec_pp_one_event_shortcode( $atts ){

    $pptec_widget_settings = get_option('pptec_widget_settings');
	$widget_color     = !empty($pptec_widget_settings['widget_color']) ? $pptec_widget_settings['widget_color'] : "";
	$widget_help_text = !empty($pptec_widget_settings['widget_help_text']) ? $pptec_widget_settings['widget_help_text'] : "";
	$widget_width     = !empty($pptec_widget_settings['widget_width']) ? $pptec_widget_settings['widget_width'] : "";
	$enabled_cart     = !empty($pptec_widget_settings['enabled_cart']) ? $pptec_widget_settings['enabled_cart'] : "";

	if ( $widget_width ) {
		$w_width = ' width="' . trim($widget_width) . '" ';
	} else {
		$w_width = ' width="100%" ';
	}

	if ( $widget_color ) {
		$w_theme = 'data-etheme="' . trim($widget_color) . '"';
	} else {
		$w_theme = '';
	}

	if ( $enabled_cart ) {
		$w_cart = 'data-mco="' . trim($enabled_cart) . '"';
	} else {
		$w_cart = '';
	}

	if ( $widget_help_text ) {
		$w_help = 'data-top-box="' . trim($widget_help_text) . '"';
	} else {
		$w_help = '';
	}

	if ( !empty( $atts['event_id'] ) ) {
		$event_id = $atts['event_id'];

		$script = '<pp:tickets ' . $w_width . ' event="'.$event_id . '"   ' . $w_theme . '  ' . $w_cart . '  ' . $w_help . '></pp:tickets>';

	} else {
		$script = '';
	}

	return $script;
}
add_shortcode('pp_event', 'pptec_pp_one_event_shortcode');


/**
 * Add custom post status
 */
function pptec_cancel_status_creation(){
	register_post_status( 'canceled', array(
		'label'                     => _x( 'Cancelled', 'post' ),
		'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>'),
		'public'                    => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true
	));
}
add_action( 'init', 'pptec_cancel_status_creation' );

function pptec_cancel_status_creation_quick_edit() {
	echo "<script>
        jQuery(document).ready( function($) {
            jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"canceled\">Cancelled</option>' );      
        }); 
        </script>";
}
add_action('admin_footer-edit.php','pptec_cancel_status_creation_quick_edit');

function pptec_cancel_status_creation_post_page() {
	echo "<script>
        jQuery(document).ready( function($) {        
            jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"canceled\">Cancelled</option>' );
            if ( $('#hidden_post_status').val() === 'canceled' ) {
            	$('.misc-pub-section.misc-pub-post-status #post-status-display').text('Cancelled');
            }
        });
        </script>";
}
add_action('admin_footer-post.php', 'pptec_cancel_status_creation_post_page');
add_action('admin_footer-post-new.php', 'pptec_cancel_status_creation_post_page');


/**
 * Cancel event query
 */
function pptec_cancel_event() {
	if ( isset( $_POST['post_id'] ) ) {
		$post_id = $_POST['post_id'];
		$pp_id   = pptec_get_pp_event_id_by_wp_event_id( $post_id );

		/**
		 * Check if user wants to delete his event, if no - process will be finished
		 */
		$pp_user_id = pptec_oauth_get_pp_user_id();
        $wp_event_pp_user_id = pptec_get_pp_user_id_by_wp_event_id( $post_id );
		if ($wp_event_pp_user_id && $pp_user_id != $wp_event_pp_user_id ) {
			// set up alert message for user - that he tried to edit or update not his event
			echo json_encode(array('success' => false, 'message' => "You tried to cancel event that does not belong to you. It will not be cancelled on Purplepass."));
			die;
		}

		$result = pptec_cancel_event_common( $pp_id, $_POST['post_id'] );
		if (!empty($result['message'])) {
		    echo json_encode($result);
		    die;
        }
	}
	die;
}
add_action( 'wp_ajax_pptec_cancel_event', 'pptec_cancel_event' );

function pptec_cancel_event_common($pp_event_id, $wp_event_id)
{
    try {
        if (empty($wp_event_id)) {
            throw new Exception('Invalid event id.');
        }

        $access_token = pptec_get_access_token();

        if (empty($access_token)) {
            throw new Exception('Account is not linked to Purplepass.');
        }

        $response = pptec_get_remote_data('section=events&action=event_cancel&id=' . $pp_event_id);
        if (is_wp_error($response) || empty($response["body"])) {
            throw new Exception('Invalid response from Purplepass.');
        }

        $response_body = json_decode($response["body"]);
        if (empty($response_body)) {
            throw new Exception('Invalid response from Purplepass.');
        }

        if (empty($response_body->success)) {
            // Prepare error message
            $msg = 'Invalid response from Purplepass.';
            if (!empty($response_body->msg)) {
                $msg = $response_body->msg;
            } elseif (!empty($response_body->errors->reason)) {
                $msg = $response_body->errors->reason;
            }

            throw new Exception($msg);
        }

        global $wpdb;
        $wpdb->update($wpdb->posts, array('post_status' => 'canceled'), array('ID' => $wp_event_id));

        $json = array('success' => true, 'message' => 'Event was successfully cancelled on Purplepass.');
    } catch (Exception $e) {
        $json = array('success' => false, 'message' => $e->getMessage());
    }

    return $json;
}


// @todo v2: why is it triggered on fetch?
add_action( "draft_to_canceled", function( $post ) {
	$pp_id = pptec_get_pp_event_id_by_wp_event_id( $post->ID );
	pptec_cancel_event_common( $pp_id, $post->ID );
} );

add_action( "publish_to_canceled", function( $post ) {
	$pp_id = pptec_get_pp_event_id_by_wp_event_id( $post->ID );
	pptec_cancel_event_common( $pp_id, $post->ID );
} );

add_action('trash_tribe_venue', function ($wp_venue_id) {
    pptec_cleanup_on_wp_venue_deletion($wp_venue_id);
});

/**
 * Forbid to publish Cancelled events
 */
add_action( 'transition_post_status', 'pptec_canceled_to_publish', 10, 3 );
function pptec_canceled_to_publish( $new, $old, $post )
{
    $post_type = get_post_type($post->ID);
    if ('tribe_events' === $post_type && $old === 'canceled') {
        // Do not allow any changes to event status if it once has been canceled.
        global $wpdb;
        $wpdb->update($wpdb->posts, array('post_status' => 'canceled'), array('ID' => $post->ID));
    }
}


function pptec_admin_notice( $path_to_include, $class = 'notice-error' ){
	echo '<div class="notice '.$class.'" style="padding-bottom: 12px; padding-top: 12px;">';
	if ( !empty( $path_to_include ) ) {
		include($path_to_include);
	}
	echo '</div>';
}

/**
 * Generate countries list
 */
function pptec_get_countries_list( $show_array = false ) {

	$countries = array(
		1   => 'United States',
		2   => 'Anguilla',
		3   => 'Argentina',
		4   => 'Australia',
		5   => 'Austria',
		6   => 'Belgium',
		7   => 'Brazil',
		8   => 'Canada',
		9   => 'Chile',
		10  => 'China',
		11  => 'Costa Rica',
		12  => 'Denmark',
		13  => 'Dominican Republic',
		14  => 'Ecuador',
		15  => 'Finland',
		16  => 'France',
		17  => 'Germany',
		18  => 'Greece',
		19  => 'Hong Kong',
		20  => 'Iceland',
		21  => 'India',
		22  => 'Ireland',
		23  => 'Israel',
		24  => 'Italy',
		25  => 'Jamaica',
		26  => 'Japan',
		27  => 'Luxembourg',
		28  => 'Malaysia',
		29  => 'Mexico',
		30  => 'Monaco',
		31  => 'Netherlands',
		32  => 'New Zealand',
		33  => 'Norway',
		34  => 'Portugal',
		35  => 'Singapore',
		36  => 'South Korea',
		37  => 'Spain',
		38  => 'Sweden',
		39  => 'Switzerland',
		40  => 'Taiwan',
		41  => 'Thailand',
		42  => 'Turkey',
		43  => 'United Kingdom',
		44  => 'Uruguay',
		45  => 'Venezuela',
		46  => 'Nigeria',
		47  => 'Falkland Islands',
		48  => 'French Southern Territories',
		50  => 'Saint Helena',
		51  => 'South Africa',
		52  => 'Lesotho',
		53  => 'Namibia',
		54  => 'French Polynesia',
		55  => 'Paraguay',
		56  => 'Swaziland',
		58  => 'Botswana',
		59  => 'Mozambique',
		60  => 'Madagascar',
		62  => 'Bolivia',
		63  => 'New Caledonia',
		64  => 'Zimbabwe',
		65  => 'Cook Islands',
		66  => 'Reunion',
		67  => 'Tonga',
		68  => 'Mauritius',
		69  => 'Vanuatu',
		70  => 'Fiji',
		71  => 'Peru',
		72  => 'Zambia',
		73  => 'Angola',
		74  => 'Malawi',
		75  => 'American Samoa',
		76  => 'Wallis and Futuna',
		77  => 'Mayotte',
		78  => 'Comoros',
		80  => 'Congo',
		81  => 'Solomon Islands',
		82  => 'Tanzania',
		83  => 'Papua New Guinea',
		84  => 'Indonesia',
		85  => 'Tokelau',
		86  => 'Tuvalu',
		87  => 'Timor-Leste',
		88  => 'Kenya',
		89  => 'Colombia',
		90  => 'Burundi',
		91  => 'Gabon',
		92  => 'Kiribati',
		93  => 'Rwanda',
		94  => 'Equatorial Guinea',
		95  => 'Uganda',
		96  => 'Somalia',
		97  => 'Maldives',
		98  => 'Nauru',
		99  => 'Unknown',
		100 => 'Cameroon',
		101 => 'Palau',
		102 => 'French Guiana',
		103 => 'Guyana',
		104 => 'Central African Republic',
		105 => 'Ethiopia',
		106 => 'Micronesia',
		107 => 'Sudan',
		108 => 'Liberia',
		109 => 'Ivory Coast',
		110 => 'Brunei Darussalam',
		111 => 'Marshall Islands',
		112 => 'Philippines',
		113 => 'Ghana',
		114 => 'Suriname',
		115 => 'Sri Lanka',
		116 => 'Togo',
		117 => 'Benin',
		118 => 'Sierra Leone',
		119 => 'Panama',
		120 => 'Chad',
		121 => 'Vietnam',
		122 => 'Burkina Faso',
		123 => 'Trinidad and Tobago',
		124 => 'Cambodia',
		125 => 'Nicaragua',
		126 => 'Djibouti',
		127 => 'Guinea-Bissau',
		128 => 'Grenada',
		129 => 'Netherlands Antilles',
		130 => 'Myanmar',
		131 => 'Senegal',
		132 => 'Yemen',
		133 => 'Saint Vincent and The Grenadines',
		134 => 'Eritrea',
		135 => 'Barbados',
		136 => 'Honduras',
		137 => 'Gambia',
		138 => 'El Salvador',
		139 => 'Guam',
		140 => 'Saint Lucia',
		141 => 'Guatemala',
		142 => 'Northern Mariana Islands',
		143 => 'Martinique',
		144 => 'Cape Verde',
		145 => 'Laos',
		146 => 'Mauritania',
		147 => 'Guadeloupe',
		148 => 'Belize',
		149 => 'Saudi Arabia',
		150 => 'Oman',
		151 => 'Antigua and Barbuda',
		152 => 'Saint Kitts and Nevis',
		153 => 'Virgin Islands of the United States',
		154 => 'Puerto Rico',
		155 => 'Haiti',
		156 => 'British Virgin Islands',
		157 => 'Cayman Islands',
		158 => 'Algeria',
		159 => 'Cuba',
		160 => 'Bangladesh',
		161 => 'Bahamas',
		162 => 'Turks and Caicos Islands',
		163 => 'Western Sahara',
		164 => 'Egypt',
		165 => 'Pakistan',
		166 => 'Libya',
		167 => 'United Arab Emirates',
		168 => 'Qatar',
		169 => 'Iran',
		170 => 'Bahrain',
		171 => 'Nepal',
		172 => 'Bhutan',
		173 => 'Morocco',
		174 => 'Kuwait',
		175 => 'Jordan',
		176 => 'Iraq',
		177 => 'Afghanistan',
		178 => 'Palestine',
		179 => 'Tunisia',
		180 => 'Bermuda',
		181 => 'Syria',
		182 => 'Lebanon',
		184 => 'Cyprus',
		185 => 'Malta',
		186 => 'Uzbekistan',
		187 => 'Tajikistan',
		188 => 'Turkmenistan',
		189 => 'Korea (North)',
		190 => 'Azerbaijan',
		191 => 'Armenia',
		192 => 'Albania',
		193 => 'Kyrgyzstan',
		194 => 'Kazakhstan',
		195 => 'Macedonia',
		196 => 'Georgia',
		197 => 'Russian Federation',
		198 => 'Bulgaria',
		199 => 'Serbia',
		200 => 'Andorra',
		201 => 'Croatia',
		202 => 'Bosnia and Herzegovina',
		203 => 'Mongolia',
		204 => 'Romania',
		205 => 'San Marino',
		206 => 'Ukraine',
		207 => 'Slovenia',
		208 => 'Moldova',
		209 => 'Hungary',
		210 => 'Saint Pierre and Miquelon',
		211 => 'Liechtenstein',
		212 => 'Slovakia',
		213 => 'Czech Republic',
		214 => 'Jersey',
		215 => 'Poland',
		216 => 'Guernsey',
		217 => 'Belarus',
		218 => 'Lithuania',
		219 => 'Isle of Man',
		220 => 'Latvia',
		221 => 'Estonia',
		222 => 'Greenland',
		223 => 'Faroe Islands',
		224 => 'Svalbard and Jan Mayen',
		225 => 'St. Maarten',
		226 => 'Aruba',
	);

	if ( false === $show_array ) {
		$countries_list = '';
		foreach ( $countries as $key => $item ) {
			$countries_list .= '<option value="' . $key . '">' . $item . '</option>';
		}

		return $countries_list;
	} else {
		return $countries;
	}
}

/**
 * Generate states list
 */
function pptec_get_us_states_list( $show_array = false, $short_code = false ) {

    if ($short_code) {
		$states = array(
			'-'  => '-', // Not Applicable
			0   => 'AL',
			1   => 'AK',
			2   => 'AS',
			3   => 'AZ',
			4   => 'AR',
			63  => 'AA',
			64  => 'AE',
			65  => 'AP',
			6   => 'CA',
			7   => 'CO',
			8   => 'CT',
			9   => 'DE',
			10  => 'DC',
			11  => 'FL',
			12  => 'GA',
			13  => 'HI',
			14  => 'ID',
			15  => 'IL',
			16  => 'IN',
			17  => 'IA',
			18  => 'KS',
			19  => 'KY',
			20  => 'LA',
			21  => 'ME',
			23  => 'MD',
			24  => 'MA',
			25  => 'MI',
			26  => 'MN',
			27  => 'MS',
			28  => 'MO',
			29  => 'MT',
			30  => 'NE',
			31  => 'NV',
			33  => 'NH',
			34  => 'NJ',
			35  => 'NM',
			36  => 'NY',
			38  => 'NC',
			39  => 'ND',
			42  => 'OH',
			43  => 'OK',
			45  => 'OR',
			46  => 'PA',
			49  => 'RI',
			51  => 'SC',
			52  => 'SD',
			53  => 'TN',
			54  => 'TX',
			55  => 'UT',
			56  => 'VT',
			57  => 'VA',
			58  => 'WA',
			59  => 'WV',
			60  => 'WI',
			61  => 'WY',
		);
    } else {
        $states = array(
            '-'  => '-', // Not Applicable
            0   => 'Alabama',
            1   => 'Alaska',
            2   => 'Samoa',
            3   => 'Arizona',
            4   => 'Arkansas',
            63  => 'Armed Forces (AA)',
            64  => 'Armed Forces (AE)',
            65  => 'Armed Forces (AP)',
            6   => 'California',
            7   => 'Colorado',
            8   => 'Connecticut',
            9   => 'Delaware',
            10  => 'District of Columbia',
            11  => 'Florida',
            12  => 'Georgia',
            13  => 'Hawaii',
            14  => 'Idaho',
            15  => 'Illinois',
            16  => 'Indiana',
            17  => 'Iowa',
            18  => 'Kansas',
            19  => 'Kentucky',
            20  => 'Louisiana',
            21  => 'Maine',
            23  => 'Maryland',
            24  => 'Massachusetts',
            25  => 'Michigan',
            26  => 'Minnesota',
            27  => 'Mississippi',
            28  => 'Missouri',
            29  => 'Montana',
            30  => 'Nebraska',
            31  => 'Nevada',
            33  => 'New Hampshire',
            34  => 'New Jersey',
            35  => 'New Mexico',
            36  => 'New York',
            38  => 'North Carolina',
            39  => 'North Dakota',
            42  => 'Ohio',
            43  => 'Oklahoma',
            45  => 'Oregon',
            46  => 'Pennsylvania',
            49  => 'Rhode Island',
            51  => 'South Carolina',
            52  => 'South Dakota',
            53  => 'Tennessee',
            54  => 'Texas',
            55  => 'Utah',
            56  => 'Vermont',
            57  => 'Virginia',
            58  => 'Washington',
            59  => 'West Virginia',
            60  => 'Wisconsin',
            61  => 'Wyoming',
        );
	}

	if ( false === $show_array ) {
		$states_list = '';
		foreach ( $states as $key => $item ) {
			$states_list .= '<option value="' . $key . '">' . $item . '</option>';
		}

		return $states_list;
	} else {
		return $states;
	}
}

function pptec_get_canada_provinces_list($show_array = false, $short_code = false)
{
    if ($short_code) {
        $states = array(
            -1  => 'Not Applicable',
            201 => 'ON',
            202 => 'QC',
            203 => 'BC',
            204 => 'AB',
            205 => 'MB',
            206 => 'SK',
            207 => 'NS',
            208 => 'NB',
            209 => 'NL',
            210 => 'PE',
            211 => 'NT',
            212 => 'YT',
            213 => 'NU'
        );
    } else {
        $states = array(
            -1  => 'Not Applicable',
            201 => 'Ontario',
            202 => 'Quebec',
            203 => 'British Columbia',
            204 => 'Alberta',
            205 => 'Manitoba',
            206 => 'Saskatchewan',
            207 => 'Nova Scotia',
            208 => 'New Brunswick',
            209 => 'Newfoundland and Labrador',
            210 => 'Prince Edward Island',
            211 => 'Northwest Territories',
            212 => 'Yukon',
            213 => 'Nunavut'
        );
    }

    if ( false === $show_array ) {
        $states_list = '';
        foreach ( $states as $key => $item ) {
            $states_list .= '<option value="' . $key . '">' . $item . '</option>';
        }

        return $states_list;
    } else {
        return $states;
    }
}

function pptec_force_cron_spawn($hookname, $key)
{
	$crons = _get_cron_array();

	foreach ( $crons as $time => $cron ) {
		if ( isset( $cron[ $hookname ][ $key ] ) ) {
			$args = $cron[ $hookname ][ $key ]['args'];
//			delete_transient( 'doing_cron' );
//			$scheduled = pptec_force_schedule_single_event( $hookname, $args ); // UTC
//
//			if ( false === $scheduled ) {
//				return $scheduled;
//			}

			add_filter( 'cron_request', function( array $cron_request_array ) {
				$cron_request_array['url'] = add_query_arg( 'pptec_force_job_events_fetching', 1, $cron_request_array['url'] );
				return $cron_request_array;
			} );

			spawn_cron();

			sleep( 1 );

			return true;
		}
	}
}

function pptec_force_schedule_single_event( $hook, $args = array() ) {
	$event = (object) array(
		'hook'      => $hook,
		'timestamp' => 1,
		'schedule'  => false,
		'args'      => $args,
	);
	$crons = (array) _get_cron_array();
	$key   = md5( serialize( $event->args ) );

	$crons[ $event->timestamp ][ $event->hook ][ $key ] = array(
		'schedule' => $event->schedule,
		'args'     => $event->args,
	);
	uksort( $crons, 'strnatcasecmp' );

	return _set_cron_array( $crons );
}

/**
 * Clears the doing cron status when an event is unscheduled.
 *
 * What on earth does this function do, and why?
 *
 * Good question. The purpose of this function is to prevent other overdue cron events from firing when an event is run
 * manually with the "Run Now" action. WP Crontrol works very hard to ensure that when cron event runs manually that it
 * runs in the exact same way it would run as part of its schedule - via a properly spawned cron with a queued event in
 * place. It does this by queueing an event at time `1` (1 second into 1st January 1970) and then immediately spawning
 * cron (see the `Event\run()` function).
 *
 * The problem this causes is if other events are due then they will all run too, and this isn't desirable because if a
 * site has a large number of stuck events due to a problem with the cron runner then it's not desirable for all those
 * events to run when another is manually run. This happens because WordPress core will attempt to run all due events
 * whenever cron is spawned.
 *
 * The code in this function prevents multiple events from running by changing the value of the `doing_cron` transient
 * when an event gets unscheduled during a manual run, which prevents wp-cron.php from iterating more than one event.
 *
 * The `pre_unschedule_event` filter is used for this because it's just about the only hook available within this loop.
 *
 * Refs:
 * - https://core.trac.wordpress.org/browser/trunk/src/wp-cron.php?rev=47198&marks=127,141#L122
 *
 * @param mixed $pre The pre-flight value of the event unschedule short-circuit. Not used.
 * @return mixed Thee unaltered pre-flight value.
 */
function pptec_maybe_clear_doing_cron( $pre ) {
	if ( defined( 'DOING_CRON' ) && DOING_CRON && isset( $_GET['pptec_force_job_events_fetching'] ) ) {
		delete_transient( 'doing_cron' );
	}

	return $pre;
}
add_filter( 'pre_unschedule_event', 'pptec_maybe_clear_doing_cron' );

function pptec_event_form_submit($wp_event_id)
{
    if (isset($_POST['pp_enabled_ticket_sales'])) {
        update_post_meta($wp_event_id, 'pp_enabled_ticket_sales', (int)$_POST['pp_enabled_ticket_sales']);
    }
}
add_action('save_post', 'pptec_event_form_submit');