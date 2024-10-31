<?php
/**
 * Coupones, Questions ans Stats Popup's
 */
if ( isset( $_GET['post'] ) ) {
	$pp_event_id = pptec_get_pp_event_id_by_wp_event_id( sanitize_text_field($_GET['post']) );
} else {
	$pp_event_id = false;
}
?>
<br>
<a href="#" style="text-decoration: none;" data-post_id="<?php echo $pp_event_id; ?>" class="add-ticket-type2 coupon-btn btn-iframes">
	<i class="fas fa-plus" style="margin-right:10px;"></i>Add/Edit Coupon Codes
</a>
<br>
<textarea id="pp_coupons_hidden_rows" style="display: none;" class="optional pp_coupons_hidden_rows" name="pp_coupons_hidden_rows"><?php echo $pp_coupons_hidden_rows; ?></textarea><br>