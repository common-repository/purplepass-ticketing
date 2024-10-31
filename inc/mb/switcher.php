<div class="field field-switcher">
  <label class="field-title title-strong">Enable ticket sales for this event?</label>
  <div class="field-switcher-custom">
    <span>No</span>
    <div class="switch-btn <?php echo $class_closed; ?>"></div>
    <span>Yes</span>
  </div>

  <div class="switcher-prevent"></div>
  <input type="text" class="pp_enabled_ticket_sales" style="display: none;" name="pp_enabled_ticket_sales" value="<?php echo $pp_enabled_ticket_sales; ?>" >
</div>

<script>
  jQuery(document).ready(function($) {
      // Grey-out if event was canceled
      if ($('[name="pp_enabled_ticket_sales"]').val() == 0 && $('#original_post_status').val() === 'canceled') {
          $('#pp_events_add_fields').css('position', 'relative').prepend('<div class="pptec-overlay">This event has been cancelled</div>');
      }

      if ( "0" === $(".pp_enabled_ticket_sales").prop("value") ) {
          $(".metabox-wrapper-event").hide();
      } else {
          $(".metabox-wrapper-event").show();
      }

    $(document).find(".switch-btn").click(function(e){
      e.preventDefault();

      $(this).toggleClass("switch-on");

      if ( $(this).hasClass("switch-on") ) {
        $(".metabox-wrapper-event").show();
        $(".pp_enabled_ticket_sales").val(1);
      } else {
          $(".metabox-wrapper-event").hide();
          $(".pp_enabled_ticket_sales").val(0);
      }
      $(document).find(".show-hide-tribe-rec").remove();
    });
  });
</script>


<div class="metabox-wrapper-event" <?php echo !$pp_enabled_ticket_sales ? 'style="display: none;"': ''; ?>>
	<div class="middle-heading"><span>Basic Info</span></div>