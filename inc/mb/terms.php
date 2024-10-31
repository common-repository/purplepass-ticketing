<div class="middle-heading"><span>Additional Options</span></div>

<!-- tax custom terms -->
<strong class="title-strong" style="display: block;">Add a custom notice or terms & conditions to the transaction
	<div class="tooltip">
		<i class="fas fa-question-circle"></i>
		<span class="tooltiptext"> You can add your own terms & conditions text</span>
	</div>
</strong>
    <br>
		<div class="pass-evt wrapper-event-type visibility-evt" style="max-width: 504px;">
		     <label for="add_termtext_yes">
				 <input id="add_termtext_yes" type="checkbox" name="add_termtext_yes" class="add_termtext_yes">
				 <span class="text-label"><strong class="title-strong">Yes</strong></span>
				 <span class="input-checkmark"></span>                
			 </label>
			 <label for="add_termtext_no" style="margin-right: 310px;">
				 <input id="add_termtext_no" type="checkbox" name="add_termtext_no" class="add_termtext_no ">
				 <span class="text-label"><strong class="title-strong">No</strong></span>
				 <span class="input-checkmark"></span>                
			</label>
        </div>
		<input id="pp_add_terms_hidden" type="text" style="display: none;" value="<?php echo $pp_add_terms_hidden; ?>" class="optional pp_add_terms_hidden" name="pp_add_terms_hidden"><br>
		<script>
			jQuery(document).ready(function($){
				$( "#add_termtext_yes").on("click", function(e){
					if ($(this).is(":checked")) {
						$(".pp_add_terms_hidden").prop("value", "1");

						$( "#add_termtext_no").prop("checked", false);
						$(".cust-term-data-hide").show();
					} else {
						e.preventDefault();
					}
				});
				$( "#add_termtext_no").on("click", function(e){
					if ($(this).is(":checked")) {
						$(".pp_add_terms_hidden").prop("value", "0");
						$( "#add_termtext_yes").prop("checked", false);

						$(".cust-term-data-hide").hide();
					} else {
						e.preventDefault();
					}
				});
				let pr_name = $(".pp_add_terms_hidden").val();
				if ( 0 === pr_name || "0" === pr_name ) {
				    $( "#add_termtext_yes").prop("checked", false);
				    $(".cust-term-data-hide").hide();
				    $( "#add_termtext_no").prop("checked", true);
				} else {
				    $( "#add_termtext_yes").prop("checked", true);
				    $( "#add_termtext_no").prop("checked", false);
				    $(".cust-term-data-hide").show();
				}
			});
		</script>

		<div class="cust-term-data-hide" style="margin-left: 45px;">
			<label for="pp_term_text" class="">
				<i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>
				<textarea id="pp_term_text" type="text" class="common-textarea optional pp_term_text" name="pp_term_text"style="width: 539px; height: 94px;"><?php echo $pp_term_text; ?></textarea>
			</label><br>
			<br>
			<div class="wrapper-event-type">
				<label for="pp_term_req" style="margin-left: 22px;">
					<input id="pp_term_req" type="checkbox" class="optional pp_term_req" name="pp_term_req" style="">
					<span class="input-checkmark"></span>
					<span class="text-label">Require the customer to agree to the terms (Checkbox)</span>
				</label>
			</div>
			<input id="pp_term_req_hidden" type="text" style="display: none;" value="<?php echo $pp_term_req_hidden; ?>" class="optional pp_term_req_hidden" name="pp_term_req_hidden"><br><br>
		</div>
		<script>
			jQuery(document).ready(function($){
				$(".cust-term-data-hide").hide();
				if ( $("#pp_add_terms_hidden").val() === "1" ) {
					$(".cust-term-data-hide").show();
					$("#pp_add_terms").attr("checked", true);
				} else {
					$(".cust-term-data-hide").hide();
					$("#pp_add_terms").attr("checked", false);
				}
				$( ".pp_add_terms").on("click", function() {
					let pp_add_terms = $( ".pp_add_terms").is(":checked");
					if ( pp_add_terms ) {
						$(".cust-term-data-hide").show();
						$(".pp_add_terms_hidden").val("1");
					} else {
						$(".cust-term-data-hide").hide();
						$(".pp_add_terms_hidden").val("0");
					}
				});
				if ( $("#pp_term_req_hidden").val() === "1" ) {
					$("#pp_term_req").attr("checked", true);
				} else {
					$("#pp_term_req").attr("checked", false);
				}
				$( ".pp_term_req").on("click", function() {
					let pp_term_req = $( ".pp_term_req").is(":checked");
					if ( pp_term_req ) {
						$(".pp_term_req_hidden").val("1");
					} else {
						$(".pp_term_req_hidden").val("0");
					}
				});
			});
		</script>