<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pptec_events");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pp_sync_events");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pp_logs");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pp_stats");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pptec_wp_venue_to_pp_venue");

// remove options
delete_option("pptec_oauth_settings");
delete_option("pptec_widget_settings");
delete_option("pptec_events_last_requested_date");
delete_option("pptec_random_str");
delete_option("pptec_oauth_decrypted_client_id");
delete_option("pptec_oauth_refresh_token");
delete_option("pptec_data");
delete_option("pptec_wizard");
delete_option("pptec_stats_update_time");
delete_option("pptec_job_events_fetching");
delete_user_meta(get_current_user_id(), 'pptec_pp_user_id');
