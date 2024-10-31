<!-- tax add -->
<br><br>
<strong class="title-strong" style="display: block;">Add Sales Tax
	<div class="tooltip">
        <i class="fas fa-question-circle"></i>
        <span class="tooltiptext">Add Sales Tax</span>
    </div>
</strong><br>
<div class="pass-evt wrapper-event-type visibility-evt" style="max-width: 504px;">
     <label for="ass_tax_yes">
		 <input id="ass_tax_yes" type="checkbox" name="ass_tax_yes" class="ass_tax_yes">
		 <span class="text-label"><strong class="title-strong">Yes</strong></span>
		 <span class="input-checkmark"></span>
	 </label>

	 <label for="ass_tax_no" style="margin-right: 310px;">
		 <input id="ass_tax_no" type="checkbox" name="ass_tax_no" class="ass_tax_no ">
		 <span class="text-label"><strong class="title-strong">No</strong></span>
		 <span class="input-checkmark"></span>
	</label>
</div>
<br>
<?php

	if ( !empty( (int)$pp_tax ) ) {
		$pp_add_tax_hidden = 1;
	} else {
		$pp_add_tax_hidden = 0;
	}
?>
<input id="pp_add_tax_hidden" type="text" style="display: none;" value="<?php echo $pp_add_tax_hidden; ?>" class="optional pp_add_tax_hidden" name="pp_add_tax_hidden"><br>
<script>
	jQuery(document).ready(function($){
		$( "#ass_tax_yes").on("click", function(e){
			if ($(this).is(":checked")) {
				$(".pp_add_tax_hidden").prop("value", "1");
				$(".pp_add_tax_hidden").attr("value", "1");
				$( "#ass_tax_no").prop("checked", false);
				$(".class-tax-input").show();
			} else {
				e.preventDefault();
			}
		});
		$( "#ass_tax_no").on("click", function(e){
			if ($(this).is(":checked")) {
				$(".pp_add_tax_hidden").prop("value", "0");
				$(".pp_add_tax_hidden").attr("value", "0");
				$( "#ass_tax_yes").prop("checked", false);

				$(".class-tax-input").hide();
			} else {
				e.preventDefault();
			}
		});
		let pr_name = $(".pp_add_tax_hidden").val();
		if ( 1 === pr_name || "1" === pr_name ) {
		    $( "#ass_tax_yes").prop("checked", true);
		    $( "#ass_tax_no").prop("checked", false);
		    $(".class-tax-input").show();

		} else {
		    $( "#ass_tax_yes").prop("checked", false);
		    $(".class-tax-input").hide();
		    $( "#ass_tax_no").prop("checked", true);
		}
	});
</script>
<label for="pp_tax" class="class-tax-input" style="display: inline-block; margin-left: 45px;">
	<strong class="title-strong"><i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>Tax Rate (%)</strong><br>
    <input id="pp_tax" type="text" class="optional pp_tax" name="pp_tax" style="" value="<?php echo $pp_tax; ?>"><br><br>
</label>
<?php
if ( !empty( $_GET['post'] ) ) {
	$p_meta = get_post_meta( sanitize_text_field($_GET['post']), 'pp_events_types', true);
	if ( 'free' === $p_meta ) { ?>
		<script>
			jQuery(document).ready(function($){
				$(".prices_options_valid").removeClass('err_red');
				$(".prices_options_valid").hide();
				$(".add-repeater-row").hide();
				$(".repeater-wrapper").hide();
				$(".price_wr").hide();
			});
		</script>
	<?php
	} else {
	?>
		<script>
			jQuery(document).ready(function($){
				$(".prices_options_valid").show();
				$(".add-repeater-row").show();
				$(".repeater-wrapper").show();
				$(".price_wr").show();
			});
		</script>
		<?php
	}
}
?>