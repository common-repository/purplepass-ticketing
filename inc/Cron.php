<?php
/**
 * Add custom cron interval
 */
add_filter( 'cron_schedules', 'pptec_cron_interval_5min');
function pptec_cron_interval_5min( $all_intervals ) {
	$all_intervals['pptec_every_5_min'] = array(
		'interval' => 320,
		'display' => 'Every 5 minutes' // отображаемое имя
	);
	return $all_intervals;
}

