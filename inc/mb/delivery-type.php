
<!-- delivery type -->
<br><br>
<strong class="title-strong ticket_options_valid">
Ticket delivery options
<div class="tooltip">
    <i class="fas fa-question-circle"></i>
    <span class="tooltiptext">
	    This defines the ticketing options your customers will have.
    Print-at-home will offer a digital ticket which they can print out or show on their phone. 
    Will call means their tickets will be held at the door with their name on a list. 
    We also can print and ship tickets for you directly to your customers. 
    Please note that customers will be responsible for paying the shipping charges.
    </span>
</div>
</strong>
<br><br>
<div class="wrapper-event-type wrap-delivery-opptions">
<label for="print_at_home">
    <input id="print_at_home"
           type="checkbox" class="cust_del print_at_home" style="margin-left: 20px;" name="delivery_home" <?php echo ($delivery_home) ? 'checked' : ''; ?>>
    <span class="text-label">Print-at-Home</span>
    <span class="input-checkmark"></span>
</label>
<label for="will_call">
    <input id="will_call" type="checkbox" class="cust_del will_call" style="margin-left: 20px;" name="delivery_wcall" <?php echo ($delivery_wcall) ? 'checked' : ''; ?>>
    <span class="text-label">Will Call</span>
    <span class="input-checkmark"></span>
</label>
<label for="first_class">
    <input id="first_class" type="checkbox" class="cust_del first_class" style="margin-left: 20px;" name="delivery_usps" <?php echo ($delivery_usps) ? 'checked' : ''; ?>>
    <span class="text-label">USPS First Class</span>
    <span class="input-checkmark"></span>
</label>
<label for="priority">
<input id="priority" type="checkbox" class="cust_del priority" style="margin-left: 20px;" name="delivery_prior" <?php echo ($delivery_prior) ? 'checked' : ''; ?>>
    <span class="text-label">USPS Priority</span>
    <span class="input-checkmark"></span>
</label>
<label for="express">
<input id="express" type="checkbox" class="cust_del express" style="margin-left: 20px;" name="delivery_expr" <?php echo ($delivery_expr) ? 'checked' : ''; ?>>
    <span class="text-label">USPS Express</span>
    <span class="input-checkmark"></span>
</label>
</div>


<script>
jQuery(document).ready(function($){
	$( ".cust_del").on("change", function(){
		let cb_array = {
			"print_at_home" : $( ".print_at_home").is(":checked"),
			"will_call" : $( ".will_call").is(":checked"),
			"first_class" : $( ".first_class").is(":checked"),
			"priority" : $( ".priority").is(":checked"),
			"express" : $( ".express").is(":checked"),
		};
		$(".hidden-deliv-types").val( JSON.stringify( cb_array ) );

		let delivery_type = false;
		$(".cust_del").each(function () {
			if ($(this).is(":checked")) {
				delivery_type = true;
			}
		});

		if ( delivery_type ) {
			$(".ticket_options_valid").removeClass("err_red");
		} else {
			$(".ticket_options_valid").addClass("err_red");
		}

	});
});
</script>
