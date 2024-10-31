<div class="group-fields field-password">
  <p style="display: none;" class="wrapper-event-type">
    <label for="pp_passwded_cb">
      <input id="pp_passwded_cb" type="checkbox" class="optional pp_passwded_cb" name="pp_passwded_cb">
      <strong></strong>
      <span class="text-label"><strong class="title-strong">Require a password to purchase tickets for this event</strong></span>
      <span class="input-checkmark"></span>
    </label>
  </p>

  <p class="group-fields-title title-strong">
    Require a password to purchase tickets for this event
    <b class="tooltip">
      <i class="fas fa-question-circle"></i>
      <span class="tooltiptext">This allows you to set a password that the customer will be required to use in order to purchase tickets.</span>
    </b>
  </p>

  <div class="group-fields-content pass-evt wrapper-event-type visibility-evt">
    <p class="field field-yes">
      <label for="pass_yes">
        <input id="pass_yes" type="checkbox" name="pass_yes" class="pass_yes">
        <span class="text-label"><strong class="title-strong">Yes</strong></span>
        <span class="input-checkmark"></span>
      </label>
    </p>
    <p class="field field-no">
      <label for="pass_now">
        <input id="pass_now" type="checkbox" name="pass_now" class="pass_now ">
        <span class="text-label"><strong class="title-strong">No</strong></span>
        <span class="input-checkmark"></span>
      </label>
    </p>

    <input type="text" class="hidden-passwded optional" style="display:none;" name="pp_passwded" value="<?php echo esc_textarea( $pp_passwded ); ?>">
  </div>

  <p class="field field-hide-password">
    <label class="pass-title" for="pp_pass">
      <i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>
      Password:
    </label>
    <input id="pp_pass" type="text" size="40" name="pp_pass" value="<?php echo esc_textarea( $pp_pass ); ?>">
  </p>
</div>

<script>
  jQuery(document).ready(function($){
    $( "#pass_yes").on("click", function(e){
      if ($(this).is(":checked")) {
        $("#pp_passwded_cb").trigger("click");
        $( "#pass_now").prop("checked", false);
      } else {
        e.preventDefault();
      }
    });
    $( "#pass_now").on("click", function(e){
      if ($(this).is(":checked")) {
        if ( $("#pp_passwded_cb").is(":checked") ) {
          $("#pp_passwded_cb").trigger("click");
        }
        $( "#pass_yes").prop("checked", false);
      } else {
        e.preventDefault();
      }
    });
    $("#pp_pass").hide();
    $( "#pp_passwded_cb").on("click", function(){
      let passwded_selected = 0;
      if ($(this).is(":checked")) {
        passwded_selected = 1;
      }
      $(".hidden-passwded").val(passwded_selected);
      let val_passwded = $(".hidden-passwded").val()
      if ( "0" == passwded_selected || 0 == passwded_selected ) {
        $("#pp_pass").hide();
        $(".pass-title").hide();
        $("#pp_pass").addClass("optional");
        $("#pp_pass").removeClass("err_red");
      } else {
        $("#pp_pass").show();
        $(".pass-title").show();
        if ( val_passwded.length === 0 ) {
          $("#pp_pass").removeClass("optional");
          $("#pp_pass").addClass("err_red");
        } else {
          $("#pp_pass").addClass("optional");
          $("#pp_pass").removeClass("err_red");
        }
      }
    });
    let val_passwded = $(".hidden-passwded").val();
    if ( "0" == val_passwded || val_passwded.length === 0 ) {
      $("#pp_pass").hide();
      $(".pass-title").hide();
      $("#pp_pass").addClass("optional");
      $("#pp_pass").removeClass("err_red");
    } else {
      $("#pp_pass").show();
      $(".pass-title").show();
      $("#pp_pass").removeClass("optional");
      $("#pp_pass").addClass("err_red");
    }

    if ( 1 === val_passwded || "1" === val_passwded ) {
      $("#pp_passwded_cb").prop("checked", true);
      $( "#pass_yes").prop("checked", true);
      $( "#pass_now").prop("checked", false);
    } else {
      $( "#pass_yes").prop("checked", false);
      $( "#pass_now").prop("checked", true);
    }
  });
</script>

<!-- <br>
<label for="pp_pass" style="display: inline-block; margin-left: 45px;" class="pass-title">
	<strong class="title-strong"><i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>Password:</strong><br>
	<input id="pp_pass" type="text" size="40" name="pp_pass" value="<?php echo esc_textarea( $pp_pass ); ?>"><br><br>
</label> -->