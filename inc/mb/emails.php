	<!-- email templates -->
	<br><br>
          <div class="wrap-select template-wrap-select">
              <label for="pp_email_template">
              <strong class="title-strong">Email templates
	              <div class="tooltip">
				      <i class="fas fa-question-circle"></i>
				      <span class="tooltiptext">You can create and use your own custom designed receipts and emails.
				      To create your own custom email design, go to Purplepass.com,
				      log into your account, and then go to Tools -> Custom Templates.</span>
	              </div>
              </strong><br>
			<i class="fas fa-sort-down"></i>
				<?php 
					$pp_email_template = pp_email_template($pp_email_template_hidden, $pp_email_template_name);
					$pp_pah_template = pp_email_template($pp_pah_template_hidden, $pp_pah_template_name);
				?>

				<input id="pp_email_template_default" type="text" value="<?php echo $pp_email_template['value']; ?>">
	    		<div id="pp_email_template_default_div"><span><?php echo $pp_email_template['name']; ?></span></div>
				
                <select id="pp_email_template">
                </select>
				  <div class="img-log-preloader email-wr-spinner" style="width: 100%; text-align: center">
					  <img class="email-template-spinner" src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
				  </div>
              </label>
		  </div>
		    <br><br>
            <input type="text" class="pp_email_template_hidden" style="display:none;" name="pp_email_template_hidden" value="<?php echo esc_attr( $pp_email_template_hidden ); ?>" >
            <input type="text" class="pp_email_template_name" style="display:none;" name="pp_email_template_name" value="<?php echo esc_attr( $pp_email_template_name ); ?>" >
			<script>
			jQuery(document).ready(function($){
				$(document).on("change", "#pp_email_template", function(){
					let tpl_selected = $( "#pp_email_template option:selected" ).val();
                    let tpl_selected_name = $( "#pp_pah_template option:selected" ).text();
                    if ( "create" === tpl_selected  ) {
					    alert("To create your own customized email template, log into your account directly at Purplepass.com and go to Tools -> Email Templates.  Once created on Purplepass, you will be able to select it from your list of templates.");
					    $( "#pp_email_template" ).prop("selectedIndex", 0);
					}
					$(".pp_email_template_hidden").attr('value', tpl_selected);
					$(".pp_email_template_hidden").prop('value', tpl_selected);
                    $(".pp_email_template_name").attr('value', tpl_selected_name);
                    $(".pp_email_template_name").prop('value', tpl_selected_name);
				});
				let val_ages = $(".pp_email_template_hidden").val();
				$("#pp_email_template option").each(function() {
					if ( $(this).val() === val_ages ) {
						$(this).attr("selected",true);
					}
				});
			});
			</script>

<!-- email templates -->

<div class="wrap-select template-wrap-select">
    <label for="pp_pah_template">
	    <strong class="title-strong">Print-At-Home templates:
	    <div class="tooltip">
			<i class="fas fa-question-circle"></i>
			<span class="tooltiptext"> You can customize your print-at-home tickets to upload your own graphics, add sponsors,
				or even define your own custom terms and conditions. This template will apply to all ticket types on this event.</span>
		</div>
	    </strong><br>
	    <i class="fas fa-sort-down"></i>
		<input id="pp_pah_template_default" type="text" value="<?php echo $pp_pah_template['value']; ?>">
	    <div id="pp_pah_template_default_div"><span><?php echo $pp_pah_template['name']; ?></span></div>
		<select id="pp_pah_template">
	    </select>
		<div class="img-log-preloader pah-wr-spinner" style="width: 100%; text-align: center">
			<img class="pah-template-spinner" src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
		</div>
  </label>
</div>
	<br><br>
    <input type="text" class="pp_pah_template_hidden" style="display:none;" name="pp_pah_template_hidden" value="<?php echo esc_attr( $pp_pah_template_hidden ); ?>" >
    <input type="text" class="pp_pah_template_name" style="display:none;" name="pp_pah_template_name" value="<?php echo esc_attr( $pp_pah_template_name ); ?>" >
	<script>
	jQuery(document).ready(function($){
		$(document).on("change", "#pp_pah_template", function(){
			let tpl_selected = $( "#pp_pah_template option:selected" ).val();
			let tpl_selected_name = $( "#pp_pah_template option:selected" ).text();
			if ( "create" === tpl_selected  ) {
				alert("To create your own customized print-at-home ticket template, log into your account directly at Purplepass.com and go to Tools -> Custom Print-at-Home. Once created on Purplepass, you will be able to select it from your list of templates.");
				$( "#pp_pah_template" ).prop("selectedIndex", 0);
			}
			$(document).find(".pp_pah_template_hidden").attr('value', tpl_selected);
			$(document).find(".pp_pah_template_hidden").prop('value', tpl_selected);
            $(document).find(".pp_pah_template_name").attr('value', tpl_selected_name);
            $(document).find(".pp_pah_template_name").prop('value', tpl_selected_name);
		});
		let val_pah = $(".pp_pah_template_hidden").val();
		$("#pp_pah_template option").each(function() {
			if ( $(this).val() === val_pah ) {
				$(this).attr("selected",true);
			}
		});
	});
	</script>
