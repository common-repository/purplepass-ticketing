<?php
global $wpdb;
$results = $wpdb->get_results( "SELECT * FROM  {$wpdb->prefix}pp_stats ORDER BY `wp_event_id` DESC LIMIT 10" );
$access_token = pptec_get_access_token();
$job_events_fetching = get_option( 'pptec_job_events_fetching' );
?>

<?php if ( empty( $access_token ) ) { ?>
    <p class="stats-message"><?php pptec_admin_notice( PPTEC_PLUGIN_PATH . 'inc/html/unlinked-stats-message.php', $class = 'notice-error' ); ?></p>
<?php } elseif( !empty( $job_events_fetching['status'] ) && 'finished' !== $job_events_fetching['status'] ) { ?>
    <p class="stats-message">Fetching events in progress - Please check back again later.</p>
<?php } else { ?>
    <a class="setting-btn get-stats-ajax button button-primary button-large fetch-big-btn" href="#" style="padding: 7px 0;">
        <img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/Refresh_Stats_16.png" style="margin-bottom:-2px;">&nbsp;Fetch latest stats
    </a>
<?php } ?>

<?php
	$gmt_offset = get_option('gmt_offset');
	$timezone_offset = $gmt_offset >= 0 ? "+" . $gmt_offset : $gmt_offset;

	$pptec_stats_update_time = get_option( 'pptec_stats_update_time' );

	if ( !empty( $pptec_stats_update_time ) ) {
		$update_date = date('n/j/Y \a\t\ g:ia ' . pptec_take_timezone_from_offset($timezone_offset), strtotime($timezone_offset . ' hours', $pptec_stats_update_time) );
		echo "<div class='upd-row'>Last updated " . $update_date . "</div>";
	}
	?>
<div class="img-stat-preloader">
	<img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif" >
</div>
<br>
<br>
<div class="tab-sort-wr">
	<table id='sort' class='sort stats-page-table'>
		<thead>
		<div style="background: #dbdbdb; width: 100%; padding: 15px; color: #ffffff; font-size: 22px; margin-bottom:-10px;">
			<span style="color:#666666;">Stats</span>
            <img style="width: 100px; float:right;" src="<?php echo PPTEC_PLUGIN_DIR . 'img/pp-log.png'; ?>">
		</div>
		<tr>
			<th style="width: 5%;">#</th>
			<th style="width: 31%;">Event Name</th>
			<th style="width: 8%;">Tickets Sold</th>
			<th style="width: 8%;">Revenue</th>
			<th style="width: 34%;">Date/Time</th>
			<th style="width: 14%;">Details</th>
		</tr>
		</thead>

		<?php if ( !empty($results) && is_array( $results ) && !empty($access_token) ) : ?>
			<tbody class="insert-append">
				<?php foreach( $results as $index => $stat_item ) : ?>
					<?php
					$id = $index + 1;
					$event_link = '#';
					$event_object = get_post( $stat_item->wp_event_id );
					if ( !empty( $event_object->ID ) ) {
						$event_link = '/wp-admin/post.php?post=' . $event_object->ID . '&action=edit';
					}
					if( !empty( $event_object->post_title ) ){
						$post_title = $event_object->post_title;
					}
					if ( empty( $post_title ) ) {
						continue;
					}
					?>
					<tr>
						<?php
						$str_date  = get_post_meta( $stat_item->wp_event_id, '_EventStartDate' );
						if ( isset( $str_date[0] ) ) {
							$post_data = get_post_meta($stat_item->wp_event_id, 'pptec_event_meta_data', true);
							$timezone_id = $post_data->data['timezone'];
							$timezone = pptec_get_timezone_short($timezone_id);
							$date_str = date('l, M jS, Y \a\t g:ia', strtotime($str_date[0]))  . " " . $timezone;
						} else {
							$date_str = false;
						}

						?>
						<td><?php echo $id; ?>. </td>
						<td><a href="<?php echo esc_attr($event_link); ?>" class="stats_link_popup" data-event_id="<?php echo esc_attr($stat_item->pp_event_id); ?>" target="_blank"><?php echo esc_html(esc_html($post_title)); ?></a></td>
						<td><?php echo esc_html(number_format( $stat_item->tickets_sold )); ?></td>
						<td>$<?php echo esc_html(number_format((float)$stat_item->revenue, 2, '.', '')); ?></td>
						<td><?php echo esc_html($date_str); ?>
						</td>
						<td>
							<img style="width: 26px; margin-right: 5px; margin-top: 10px;" src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/detailedstats.png">
							<a href="#" class="stats-btn" style="color:#3673ff; text-decoration: none;" data-ppid="<?php echo esc_attr($stat_item->pp_event_id); ?>">
								<span style="margin-bottom:10px;font-size: 15px;">Detailed Stats</span>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		<?php endif; ?>
		<script>
			jQuery(document).ready(function($) {
				$(document).on("click", ".stats-btn", function(e) {
					let ppid = $(this).attr('data-ppid');
          			e.preventDefault();

					$.ajax({
						url: ajaxurl,
						data: {
							"action" : "get_access_token_ajax",
							"nonce" : pp_js.nonce, // ajax nonce
						},
						type: "POST",
						success: function (token) {
							PP.window({
								token: token,
								params: {
									view: "stats",
									event_id: ppid,
								}
							},  {mask: '00000060'});
						},
						error: function(xhr, textStatus, errorThrown){
							alert('request failed->'+textStatus);
						}
					});
				});
			});
		</script>
		<?php if ( !empty( $results ) && !empty($access_token) ) : ?>
		<tfoot>
		<tr>
			<td colspan="6" style="text-align: center;">
				<br>
				<div class="img-stats-preloader" style="width: 100%; text-align: center">
					<img src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
				</div>
				<br>
				<?php if ($id >= 10) : ?>
					<a class="setting-btn get-stats-ajax-loadmore button button-primary button-large" href="#">
						Load more
					</a>
				<?php endif; ?>
				<br>
				<br>
			</td>
		</tr>
		</tfoot>
		<?php endif; ?>
	</table>
</div>