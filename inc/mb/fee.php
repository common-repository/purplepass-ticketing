<!-- Add a Facility Fee -->

<strong class="title-strong" style="display: block;">Add a Facility Fee
	<div class="tooltip">
	    <i class="fas fa-question-circle"></i>
	    <span class="tooltiptext">Add a Facility Fee</span>
	</div>
</strong><br>
<div class="pass-evt wrapper-event-type visibility-evt" style="max-width: 504px;">
	<label for="add_ffe_yes">
		<input id="add_ffe_yes" type="checkbox" name="add_ffe_yes" class="add_ffe_yes">
		<span class="text-label"><strong class="title-strong">Yes</strong></span>
		<span class="input-checkmark"></span>
	</label>

	<label for="add_ffe_no" style="margin-right: 310px;">
		<input id="add_ffe_no" type="checkbox" name="add_ffe_no" class="add_ffe_no ">
		<span class="text-label"><strong class="title-strong">No</strong></span>
		<span class="input-checkmark"></span>
	</label>
</div>
<br>

<input type="text" class="pp_fee_sep_hidden" style="display:none;" name="pp_fee_sep_hidden" value="<?php echo esc_textarea( $pp_fee_sep_hidden ); ?>" >
<input type="text" class="pp_fee" style="display:none;" name="pp_fee" value="<?php echo esc_textarea( $pp_fee ); ?>" >

<div class="fee_group">
	<br>
	<label style="display: inline-block; margin-left: 45px;">
		<strong class="title-strong"><i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>Facility fee ($):</strong><br>
		<input type="text" class="common-input optional" style="" name="pp_fee_doll" value="<?php echo $pp_fee_doll; ?>" >
	</label><br><br>
	<label style="display: inline-block; margin-left: 45px;">
		<strong class="title-strong">Facility fee (%):</strong><br>
		<input type="text" class="common-input optional" style="" name="pp_fee_percent" value="<?php echo $pp_fee_percent; ?>" >
	</label><br><br>
	<label style="display: none;">
		<strong class="title-strong">Facility fee minimum ($):</strong><br>
		<input type="text" class="common-input optional" name="pp_fee_min_doll" value="<?php echo $pp_fee_min_doll; ?>" >
	</label>
	<div class="wrapper-event-type" style="display: inline-block; margin-left: 45px;">
		<label for="pp_fee_sep">
			<input id="pp_fee_sep" type="checkbox" class="optional pp_fee_sep" name="pp_fee_sep" style="" value="">
			<span class="input-checkmark"></span>
			<span class="text-label">Display facility fee and service fee separately</span>
		</label>
	</div>
</div>
<br>

<script>
jQuery(document).ready(function($){

  $( "#add_ffe_yes").on("click", function(e){
		if ($(this).is(":checked")) {
			$(".pp_fee").prop("value", "1");
			$(".fee_group").show();
			$( "#add_ffe_no").prop("checked", false);
		} else {
			e.preventDefault();
		}
	});

	$( "#add_ffe_no").on("click", function(e){
		if ($(this).is(":checked")) {
			$(".pp_fee").prop("value", "0");
			$( "#add_ffe_yes").prop("checked", false);
			$(".fee_group").hide();
		} else {
			e.preventDefault();
		}
	});
    $(".fee_group").hide();

	let pr_name = $(".pp_fee").val();

    if ( 1 === pr_name || "1" === pr_name ) {
        $( "#add_ffe_yes").prop("checked", true);
        $( "#add_ffe_yes").trigger("click");
        $( "#add_ffe_no").prop("checked", false);
        $(".fee_group").show();
    } else {

        $( "#add_ffe_no").prop("checked", true);
        $( "#add_ffe_no").trigger("click");
        $( "#add_ffe_yes").prop("checked", false);
        $(".fee_group").hide();
    }

	$( ".pp_fee_sep").on("click", function(e){
		if ($(this).is(":checked")) {
			$(".pp_fee_sep_hidden").prop("value", "1");
			$(".pp_fee_sep_hidden").attr("value", "1");
		} else {
			$(".pp_fee_sep_hidden").prop("value", "0");
			$(".pp_fee_sep_hidden").attr("value", "0");
		}
	});
    let sep_val = $(".pp_fee_sep_hidden").val();
    if ( '1' == sep_val ) {
		$( "#pp_fee_sep").prop("checked", true);
	} else {
		$( "#pp_fee_sep").prop("checked", false);
	}

});
</script>