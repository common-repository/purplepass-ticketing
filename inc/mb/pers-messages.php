<!--  Include a personalized message on the order receipt -->
<br><br>
<strong class="title-strong" style="display: block;">Include a personalized message on the order receipt
	<div class="tooltip">
		<i class="fas fa-question-circle"></i>
		<span class="tooltiptext">Include a personalized message on the order receipt</span>
	</div>
</strong>
<br>
<div class="pass-evt wrapper-event-type visibility-evt" style="max-width: 504px;">
     <label for="pers_msg_yes">
		 <input id="pers_msg_yes" type="checkbox" name="pers_msg_yes" class="pers_msg_yes">
		 <span class="text-label"><strong class="title-strong">Yes</strong></span>
		 <span class="input-checkmark"></span>
	 </label>
	 <label for="pers_msg_no" style="margin-right: 310px;">
		 <input id="pers_msg_no" type="checkbox" name="pers_msg_no" class="pers_msg_no">
		 <span class="text-label"><strong class="title-strong">No</strong></span>
		 <span class="input-checkmark"></span>
	</label>
</div>
<input id="pp_pers_msg_hidden" type="text" style="display: none;" value="<?php echo $pp_pers_msg_hidden; ?>" class="optional pp_pers_msg_hidden" name="pp_pers_msg_hidden"><br>
<script>
	jQuery(document).ready(function($){
		$(".cust-message-hide").hide();
		$( "#pers_msg_yes").on("click", function(e){
			if ($(this).is(":checked")) {
				$(".pp_pers_msg_hidden").prop("value", "1");
				$( "#pers_msg_no").prop("checked", false);
				$(".cust-message-hide").show();
			} else {
				e.preventDefault();
			}
		});
		$( "#pers_msg_no").on("click", function(e){
			if ($(this).is(":checked")) {
				$(".pp_pers_msg_hidden").prop("value", "0");
				$( "#pers_msg_yes").prop("checked", false);
				$(".cust-message-hide").hide();
			} else {
				e.preventDefault();
			}
		});
		let pr_name = $(".pp_pers_msg_hidden").val();
		if ( 0 === pr_name || "0" === pr_name ) {
		    $( "#pers_msg_yes").prop("checked", false);
		    $(".cust-message-hide").hide();
		    $( "#pers_msg_no").prop("checked", true);
		} else {
		    $( "#pers_msg_yes").prop("checked", true);
		    $( "#pers_msg_no").prop("checked", false);
		    $(".cust-message-hide").show();
		}
	});
</script>
<!--<input id="pp_pers_msg_hidden" type="text" style="display: none;" value="--><?php //echo $pp_pers_msg_hidden; ?><!--" class="optional pp_pers_msg_hidden" name="pp_pers_msg_hidden"><br>-->
<div class="cust-message-hide" style="margin-left:45px;">
	<i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>
	<label for="pp_msg_text" class="">
		<textarea id="pp_msg_text" type="text" class="common-textarea optional pp_msg_text" name="pp_msg_text" style="width: 539px; height: 94px;"><?php echo $pp_msg_text; ?></textarea>
	</label>
	<br>
	<br>
</div>
<script>
	jQuery(document).ready(function($){
		$(".cust-message-hide").hide();
		if ( $("#pp_pers_msg_hidden").val() === "1" ) {
			$(".cust-message-hide").show();
			$("#pp_pers_msg").attr("checked", true);
		} else {
			$(".cust-message-hide").hide();
			$("#pp_pers_msg").attr("checked", false);
		}
		$( ".pp_pers_msg").on("click", function() {
			let pp_pers_msg = $( ".pp_pers_msg").is(":checked");
			if ( pp_pers_msg ) {
				$(".cust-message-hide").show();
				$(".pp_pers_msg_hidden").val("1");
			} else {
				$(".cust-message-hide").hide();
				$(".pp_pers_msg_hidden").val("0");
			}
		});
	});
</script>