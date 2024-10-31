
<!-- // event type field -->
<div class="middle-heading">
	<span>Ticket Pricing & Options</span>
</div>
<strong style="display: none;" class="title-strong event_types_options_valid">Event type:
	<i class="fas fa-question-circle" title="We ask for your age only for statistical purposes."></i>
</strong>
<br>
<div class="wrapper-event-type" style="display: none;">
    <label for="pp_paid">
        <input id="pp_paid" type="radio" class="tickets_type_2 pp_paid" style="margin-left: 20px;" data-n="paid" name="t_type" checked>
        <span class="text-label">Paid</span>
        <span class="input-checkmark"></span>
    </label>
            
    <label for="pp_free">
        <input id="pp_free" type="radio" class="tickets_type_2 pp_free" style="margin-left: 20px;" data-n="free" name="t_type">
        <span class="text-label">Free</span>
        <span class="input-checkmark"></span>
    </label>

    <label for="pp_res_only">
        <input id="pp_res_only" type="radio" class="tickets_type_2 pp_res_only" style="margin-left: 20px;" data-n="reservation_only" name="t_type">
        <span class="text-label"> Reservation Only</span>
        <span class="input-checkmark"></span>
    </label>
</div>
<input type="text" class="hidden-events-types" style="display:none;" name="pp_events_types" value="<?php echo $pp_events_types; ?>">
<script>
jQuery(document).ready(function ($) {
	let ticket_types = false;
	$(".tickets_type_2").each(function () {
		if ($(this).is(":checked")) {
			ticket_types = true;
		}
	});

	if (ticket_types) {
		$(".event_types_options_valid").removeClass("err_red");
	} else {
		$(".event_types_options_valid").addClass("err_red");
	}

	$(".tickets_type_2").on("change, click", function () {
		$(".tickets_type_2").each(function () {
			if ($(this).is(":checked")) {
				ticket_types = true;
			}
		});

		if (ticket_types) {
			$(".event_types_options_valid").removeClass("err_red");
		} else {
			$(".event_types_options_valid").addClass("err_red");
		}
	});

	$(".cust_del2").each(function () {
		if ($(this).attr("data-n") === $(".hidden-events-types").val()) {
			$(this).prop("checked", true);
		}
	});

	$(".cust_del2").on("change", function () {

		$(".cust_del2").each(function () {
			$(this).prop("checked", false);
		});
		$(this).prop("checked", true);
		let clicked_attr = $(this).attr("data-n");

		$(".hidden-events-types").val(clicked_attr);

		let delivery_type2 = false;
		$(".cust_del2").each(function () {
			if ($(this).is(":checked")) {
				delivery_type2 = true;
			}
		});

		if (delivery_type2) {
			$(".ticket_options_valid").removeClass("err_red");
		} else {
			$(".ticket_options_valid").addClass("err_red");
		}
	});
});
</script>