<?php
/**
 * Questions ans Stats Popup's
 */

if ( isset( $_GET['post'] ) ) {
	$pp_event_id = pptec_get_pp_event_id_by_wp_event_id( sanitize_text_field($_GET['post']) );
} else {
	$pp_event_id = false;
}

$access_token = pptec_get_access_token();

$stat_btn = '';

$code = html_entity_decode( $pp_questions_hidden );
$code = str_replace( ' "[', '[', $code );
$code = str_replace( ']" ', ']', $code );

$code = str_replace( '"[', '[', $code );
$code = str_replace( ']"', ']', $code );

$code = rtrim( $code,'"' );
$code = rtrim( $code,' ' );

$code = ltrim( $code,'"' );
$code = ltrim( $code,' ' );

$code2 = html_entity_decode( $pp_questions_hidden_rows );
$code2 = str_replace( ' "[', '[', $code2 );
$code2 = str_replace( ']" ', ']', $code2 );

$code2 = str_replace( '"[', '[', $code2 );
$code2 = str_replace( ']"', ']', $code2 );

$code2 = rtrim( $code2,'"' );
$code2 = rtrim( $code2,' ' );

$code2 = ltrim( $code2,'"' );
$code2 = ltrim( $code2,' ' );

if ( 1 === $pp_questions_hidden || '1' === $pp_questions_hidden ) {
	$checked = 'checked';
} else {
	$checked = '';
}
?>

<textarea id="pp_questions_hidden" data-checked="<?php echo $checked; ?>" style="display: none;" class="optional pp_questions_hidden" name="pp_questions_hidden"><?php echo $code; ?></textarea><br>
<textarea id="pp_questions_hidden_rows" style="display: none;" class="optional pp_questions_hidden_rows" name="pp_questions_hidden_rows"><?php echo $code2; ?></textarea><br>

<strong class="title-strong" style="display: block;">Add question to ask during check out
	<div class="tooltip">
		<i class="fas fa-question-circle"></i>
		<span class="tooltiptext">
			This allows you to create your own custom questions to ask customers during the check out process.
			This is helpful for finding out specific info like how they found out about your event, if there is a particular artist they are attending for,
			or whom they would like to see perform at a future event.
			You can create as many custom questions as you wish with a robust set of options.
		</span>
	</div>
</strong>
<br>
<div class="pass-evt wrapper-event-type visibility-evt" style="max-width: 504px;">
	<label for="questions_yes">
        <input id="questions_yes" type="checkbox" name="questions_yes" class="questions_yes">
		<span class="text-label"><strong class="title-strong">Yes</strong></span>
		<span class="input-checkmark"></span>
	</label>
	<label for="questions_no" style="margin-right: 310px;">
		<input id="questions_no" type="checkbox" name="questions_no" class="questions_no">
		<span class="text-label"><strong class="title-strong">No</strong></span>
		<span class="input-checkmark"></span>
	</label>
</div>
<br>
<div class="hid_questions_block" style="margin-left:45px; ">
	<i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>
	<a href="#" class="add-ticket-type2 questions-btn btn-iframes">Manage Questions</a>
	<?php echo $stat_btn; ?>
</div>
<br>
<br>

<script>
jQuery(document).ready(function($){

	$( "#questions_yes").on("click", function(e){
		if ($(this).is(":checked")) {
			$( "#questions_no").prop("checked", false);
			$(".hid_questions_block").show();
			$(".pp_questions_hidden").text('1');
		} else {
			e.preventDefault();
		}
	});

	$( "#questions_no").on("click", function(e){
		if ($(this).is(":checked")) {
			$( "#questions_yes").prop("checked", false);
			$(".hid_questions_block").hide();
			$(".pp_questions_hidden").text('0');
		} else {
			e.preventDefault();
		}
	});

	if ( '1' === $(".pp_questions_hidden").text() ) {
		$( "#questions_yes").trigger('click');
	} else {
		$( "#questions_no").trigger('click');
	}

});
</script>