
<!-- tax facebook -->
<strong class="title-strong" style="display: block;">Enable Facebook Options (Auto check-in, sharing w/ friends, etc)
	<div class="tooltip">
		<i class="fas fa-question-circle"></i>
		<span class="tooltiptext">Enable Facebook Options (Auto check-in, sharing w/ friends, etc)</span>
	</div>
</strong>
<br>
<div class="pass-evt wrapper-event-type visibility-evt" style="max-width: 504px;">
	<label for="add_facebook_yes">
		<input id="add_facebook_yes" type="checkbox" name="add_facebook_yes" class="add_facebook_yes">
		<span class="text-label"><strong class="title-strong">Yes</strong></span>
		<span class="input-checkmark"></span>
	</label>

	<label for="add_facebook_no" style="margin-right: 310px;">
		<input id="add_facebook_no" type="checkbox" name="add_facebook_no" class="add_facebook_no ">
		<span class="text-label"><strong class="title-strong">No</strong></span>
		<span class="input-checkmark"></span>
	</label>
</div>

<input id="pp_add_facebook_hidden" type="text" style="display: none;" value="<?php echo $pp_add_facebook_hidden; ?>" class="optional pp_add_facebook_hidden" name="pp_add_facebook_hidden">

<script>
jQuery(document).ready(function($){

$(".fb_fields").hide();

$( "#add_facebook_yes").on("click", function(e){
	if ($(this).is(":checked")) {
		$(".pp_add_facebook_hidden").prop("value", "1");

		$( "#add_facebook_no").prop("checked", false);
		$(".fb_fields").show();
		$(".class-facebook-input").removeAttr("style");
	} else {
		e.preventDefault();
	}
});

$( "#add_facebook_no").on("click", function(e){
	if ($(this).is(":checked")) {
		$(".pp_add_facebook_hidden").prop("value", "0");
		$( "#add_facebook_yes").prop("checked", false);

		$(".fb_fields").hide();
	} else {
		e.preventDefault();
	}
});

let pr_name = $(".pp_add_facebook_hidden").val();
if ( 0 === pr_name || "0" === pr_name || "" === pr_name ) {
    $( "#add_facebook_yes").prop("checked", false);
    $(".fb_fields").hide();
    $( "#add_facebook_no").prop("checked", true);
} else {
    $( "#add_facebook_yes").prop("checked", true);
    $( "#add_facebook_no").prop("checked", false);
    $(".fb_fields").show();
}

});
</script>


<div class="fb_fields" style="margin-left: 45px;">
    <br>

   <label for="pp_tax" class="class-facebook-input">
   <i style="transform: rotate(180deg); float:left;" class="fas fa-reply arrow-custom-deg"></i>
   <strong class="title-strong">Facebook URL</strong><br>
	  <input id="pp_fb_url" type="text" class="common-input optional pp_fb_url" name="pp_fb_url" style="" value="<?php echo $pp_fb_url; ?>">
   </label>
   <br>
   <br>
   <div class="wrapper-event-type additional-options">

   <label for="pp_auto_check_in" class="class-auto-check-in">
	<input id="pp_auto_check_in" type="checkbox" class="optional pp_auto_check_in" name="pp_auto_check_in" style="" value="">
	<span class="input-checkmark"></span>
	<span class="text-label">
	    Give your guests the option to automatically check in on Facebook when they arrive at your event
	</span>
	 <input id="pp_auto_check_in_hidden" type="text" style="display: none;" value="<?php echo $pp_auto_check_in_hidden; ?>" class="optional pp_auto_check_in_hidden" name="pp_auto_check_in_hidden">
	 <br>
	 <script>
		 jQuery(document).ready(function($){
		    $( "#pp_auto_check_in").on("click", function() {

		        let fb_url_val = $("#pp_fb_url").val();
		        if ( fb_url_val.length < 10 ) {
		            alert("The auto-check feature cannot be used with the Facebook URL you entered since the page does not have a valid physical address");
		            $("#pp_auto_check_in").prop("checked", false);
		        }

				let pp_auto_check = $( "#pp_auto_check_in").is(":checked");

				if ( pp_auto_check ) {
					$("#pp_auto_check_in_hidden").val("1");
				} else {
					$("#pp_auto_check_in_hidden").val("0");
				}

			});
		        if ( $("#pp_auto_check_in_hidden").val() === "1" ) {
					$("#pp_auto_check_in").prop("checked", true);
				} else {
					$("#pp_auto_check_in").prop("checked", false);
				}
		 });
	 </script>
   </label>
   </div>


   <div class="wrapper-event-type additional-options">
       <label for="pp_tell_opt" class="">
        <input id="pp_tell_opt" type="checkbox" class="optional pp_tell_opt" name="pp_tell_opt" style="" value="">
        <span class="input-checkmark"></span>
        <span class="text-label">Give your guests the option to tell their friends
        on Facebook about their ticket purchase</span>
         <input id="pp_tell_opt_hidden" type="text" style="display: none;" value="<?php echo $pp_tell_opt_hidden; ?>" class="optional pp_tell_opt_hidden" name="pp_tell_opt_hidden">
         <script>
             jQuery(document).ready(function($){
                $( "#pp_tell_opt").on("click", function() {
                    let pp_tell_opt_hidden = $( "#pp_tell_opt").is(":checked");

                    if ( pp_tell_opt_hidden ) {
                        $("#pp_tell_opt_hidden").val("1");
                    } else {
                        $("#pp_tell_opt_hidden").val("0");
                    }

                });

                if ( $("#pp_tell_opt_hidden").val() === "1" ) {
                    $("#pp_tell_opt").prop("checked", true);
                } else {
                    $("#pp_tell_opt").prop("checked", false);
                }

             });
         </script>
       </label>
   </div>

   <br>

   <div class="wrapper-event-type additional-options">
        <label for="pp_like_before_purch" class="">
        <input id="pp_like_before_purch" type="checkbox" class="optional pp_like_before_purch" name="pp_like_before_purch" style="" value="">
        <span class="input-checkmark"></span>
        <spna class="text-label">Request your guests to "Like" your
        Facebook page before purchasing tickets</spna>
         <input id="pp_like_before_purch_hidden" type="text" style="display: none;" value="<?php echo $pp_like_before_purch_hidden; ?>" class="optional pp_like_before_purch_hidden" name="pp_like_before_purch_hidden">
         <script>
             jQuery(document).ready(function($){
                $( "#pp_like_before_purch").on("click", function() {
                    let pp_like_before_purch_hidden = $( "#pp_like_before_purch").is(":checked");

                    if ( pp_like_before_purch_hidden ) {
                        $("#pp_like_before_purch_hidden").val("1");
                        $("#pp_like_before_buy").prop("checked", false);
                    } else {
                        $("#pp_like_before_purch_hidden").val("0");
                    }

                });
                if ( $("#pp_like_before_purch_hidden").val() === "1" ) {
                    $("#pp_like_before_purch").prop("checked", true);
                    $("#pp_like_before_buy").prop("checked", false);
                } else {
                    $("#pp_like_before_purch").prop("checked", false);
                }

             });
         </script>
       </label>
   </div>

   <br>


</div>

<script>
  jQuery(document).ready(function($){
    $(".class-facebook-input").hide();

	if ( $("#pp_add_facebook_hidden").val() === "1" ) {
		$(".class-facebook-input").show();
		$(".fb_fields").show();
		$("#pp_add_facebook").attr("checked", true);
	} else {
		$(".class-facebook-input").hide();
		$(".fb_fields").hide();
		$("#pp_add_facebook").attr("checked", false);
	}

	$( ".pp_add_facebook").on("click", function() {
		let pp_fee_sep = $( ".pp_add_facebook").is(":checked");

		if ( pp_fee_sep ) {
			$(".class-facebook-input").show();
			$(".fb_fields").show();
			$(".pp_add_facebook_hidden").val("1");
		} else {
			$(".class-facebook-input").hide();
			$(".fb_fields").hide();
			$(".pp_add_facebook_hidden").val("0");
		}
	});

  });

</script>
