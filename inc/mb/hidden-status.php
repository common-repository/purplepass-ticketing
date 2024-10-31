<div class="group-fields field-hidden-status">
  <p class="group-fields-title title-strong">
    List as an upcoming event on Purplepass
    <b class="tooltip">
      <i class="fas fa-question-circle"></i>
      <span class="tooltiptext">Have your event shown on search engines and listed on the main Purplepass.com website
      as an upcoming event to gain more exposure and help sell more tickets.  If your event is private or not available to the public,
      choose No so that it will only be available directly through your website.</span>
    </b>
  </p>

  <div class="group-fields-content wrapper-event-type visibility-evt">
    <p class="field field-yes">
      <label for="event_show">
        <input id="event_show" type="radio" value="0" name="pp_hidden" class="cbx_hide_event pb_event" <?php echo 0 === $pp_hidden ? 'checked="checked"': ''; ?>>
        <span class="text-label"><strong class="title-strong">Yes</strong></span>
        <span class="input-checkmark"></span>
      </label>
    </p>
    <p class="field field-no">
      <label for="event-public">
        <input id="event-public" type="radio" value="1" name="pp_hidden" class="cbx_hide_event hidden_event" <?php echo 1 === $pp_hidden ? 'checked="checked"': ''; ?>>
        <span class="text-label"><strong class="title-strong">No (Hidden/Private)</strong></span>
        <span class="input-checkmark"></span>
      </label>
    </p>
  </div>
</div>