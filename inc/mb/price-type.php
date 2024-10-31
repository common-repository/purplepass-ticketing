<?php if ( 'venue' === $pp_price_type ) : ?>
	<script>
		jQuery(document).ready(function ($) {
			setTimeout(function () {
				$('.prices-wrapper-design .wrap-price-select').parent().hide();
				//$('.prices-wrapper-design .new-price-qty').parent().hide();
			}, 500);
		});
	</script>
<?php endif; ?>

<p class="field field-venue-capacity">
  <label class="field-title title-strong" for="pp_capacity">
    Venue Capacity
    <b class="tooltip">
      <i class="fas fa-question-circle"></i>
      <span class="tooltiptext">This allows you to set the overall max capacity of your venue. So if you have multiple ticket types available for sale,
    once you reach your total capacity, online sales will be shut off automatically so you do not exceed your overall venue capacity.</span>
    </b>
  </label>

  <input id="pp_capacity" type="text" size="15" name="pp_capacity" value=" <?php esc_attr_e($pp_capacity); ?>">
</p>

<div class="group-fields field-seating-type">
  <p class="group-fields-title title-strong">
    Seating type
    <b class="tooltip">
      <i class="fas fa-question-circle"></i>
      <span class="tooltiptext">
        Choose General Admission if customers will not have specific seats for this event.
        If you plan on allowing customers to choose their exact seat at your event, then choose Assigned Seating.
        Please note that if you choose assigned seating, you will need to send Purplepass your seating chart so we can build it for you.
        Please select Assigned Seating for instructions on how to send us your seating chart.
      </span>
    </b>
  </p>

  <div class="group-fields-content pass-evt wrapper-event-type visibility-evt">
    <p class="field field-general-admission">
      <label for="ga_seating">
        <input id="ga_seating" type="checkbox" name="ga_seating" class="ga_seating">
        <span class="text-label"><strong class="title-strong">General Admission</strong></span>
        <span class="input-checkmark"></span>                
      </label>
    </p>

    <p class="field fueld-assigned-seating">
      <label for="as_seating">
        <input id="as_seating" type="checkbox" name="as_seating" class="as_seating ">
        <span class="text-label"><strong class="title-strong">Assigned Seating </strong></span>
        <span class="input-checkmark"></span>                
      </label>
    </p>
  </div>

  <div class="seating-hide-bg" style="display: block; width:800px; height: 40px; z-index: 100; margin-top: -30px; position: relative;">&nbsp</div>
</div>

<br>

<div class="wrap-location being-sold-wrapper">
    
      <!-- venue Map -->
    <label class="wrap-select venue-cust-cl" for="pp_venue_map_select" style="margin-right:25px;">
        <strong class="title-strong">Venue Map</strong>
        <div class="ven-map-preloader" style="
            width: 60px;
      height: 60px;
      position: absolute;
      z-index: 10000000;
      overflow: hidden;
      margin-left: 160px;
        margin-top: 13px;
      ">
    <img style="width: 60px;" src="<?php echo PPTEC_PLUGIN_DIR; ?>/img/preloader.gif">
  </div>
            <br>
            <i class="fas fa-sort-down"></i>
			<select id="pp_venue_map_select">
				<option value="Please, select venue">Select seating chart</option>
  				<?php echo pptec_get_venues_options_list(); ?>
				<?php if ( $pp_venue_map ) : ?>
					<option value="<?php echo esc_attr($pp_venue_map); ?>"><?php echo esc_attr($pp_venue_map_name); ?></option>
				<?php endif; ?>
			</select>
			<div class="img-log-preloader venuemap-wr-spinner" style="width: 100%; text-align: center">
				<img class="venuemap-template-spinner" src="<?php echo PPTEC_PLUGIN_DIR . '/'; ?>img/preloader.gif">
			</div>
		</label>
		<br><br>
		<input type="text" class="hidden-venue-map" style="display:none;" id="pp_venue_map" name="pp_venue_map" value="<?php echo esc_textarea( $pp_venue_map ); ?>" >
		<input type="text" class="pp_venue_map_name" style="display:none;" name="pp_venue_map_name" value="<?php echo esc_textarea( $pp_venue_map_name ); ?>" >
		<script>
			jQuery(document).ready(function ($) {
				function validation_assigned_select() {
					if ($("#pp_venue_map_select option:selected").text() === "Select seating chart") {
						$("#pp_venue_map_select").addClass("err_red");
					} else {
						$("#pp_venue_map_select").removeClass("err_red");
					}
				}
				$(".ven-map-preloader").hide();

				// get venues list from PP ajax
				var assigned_was_clicked = false;
				$(document).on("click", "#as_seating", function(e){
					validation_assigned_select();
					if ( false === assigned_was_clicked ) {
						$('.img-log-preloader.venuemap-wr-spinner').show();
						let all_data = {
							"action": "venue_map_processing_get",
						};
						$.ajax({
							url: ajaxurl,
							data: all_data,
							type: "POST",
							success: function (data) {
								if ( data ) {
									$(document).find("#pp_venue_map_select").html(data);
									$('.img-log-preloader.venuemap-wr-spinner').hide();
								}
							}
						});
						assigned_was_clicked = true;
					}

					localStorage.setItem("assigned_clicked", "yes");

					let col_rows = $("document").find(".ase-remove-wrapper .one-repeater-item").length;
					let at_least_1_prise_exists = 0;
					let at_least_1_qt_exists = 0;
					let at_least_1_desc_exists = 0;

					$(document).find(".ase-remove-wrapper .one-repeater-item").each(function() {
						let pr_text = $(this).find(".new-price-price").val();
						if ( pr_text.length > 0 ) {
							at_least_1_prise_exists = 1;
						}
						let qt_text = $(this).find(".new-price-qty").val();
						if ( qt_text.length > 0 ) {
							at_least_1_qt_exists  = 1;
						}
						let desc_text = $(this).find(".new-price-desc").val();
						if ( desc_text.length > 0 ) {
							at_least_1_desc_exists  = 1;
						}
					});

					let is_yes_change_seating_type = true;
					if ( 1 === at_least_1_prise_exists || 1 === at_least_1_qt_exists || 1 === at_least_1_desc_exists || col_rows > 1 ) {
						is_yes_change_seating_type = confirm("Changing to assigned seating will cause you to lose your current ticket types.  Are you sure you want to clear your current ticket options and switch to assigned seating?");
					}

					if ( is_yes_change_seating_type ) {

						$(document).find(".hidden-price-type").prop("value", "venue");
						$(document).find(".hidden-price-type").attr("value", "venue");

						$( "#ga_seating").prop("checked", false);
						$( "#as_seating").prop("checked", true);

						$(".being-sold-wrapper").show();
						$(document).find(".wrap-price.full-price-table-design .one-type-select").parent().parent().hide();

						// Clear all previously saved prices
						$(".ase-remove-wrapper").html("<span class='insert-price-row-before'></span>");
						$('.pp_prices_data').text('');

						$(this).parent().parent().parent().find(".new-price-name").parent().show();
						$(this).parent().parent().parent().find(".new-price-name").show();

						$(this).parent().parent().parent().find(".new-price-price").parent().show();
						$(this).parent().parent().parent().find(".new-price-price").show();

						$(this).parent().parent().parent().find(".new-price-name").removeAttr("style").removeAttr("disabled").show();
						$(this).parent().parent().parent().find(".name-price-title").removeAttr("style").show();

						$(this).parent().parent().parent().find(".new-price-qty").parent().hide();
						$(this).parent().parent().parent().find(".new-price-qty").hide();

					} else {
						$(".seating-hide-bg").hide();
						$(".being-sold-wrapper").hide();
						$(".seating-msg").hide();
						$( "#as_seating").prop("checked", false);
						$( "#ga_seating").prop("checked", true);
					}

					$("#pp_venue_map_select").prop("disabled", false);
					$("#pp_venue_map_select option:first-child").prop("selected", true);
				});


				// is checked assigned seating checkbox - then validation
				setTimeout(function () {
					validation_assigned_select();
				}, 1500);

				$(document).on("change", "#pp_venue_map_select", function () {

					validation_assigned_select();

					let selected_map_name = $("#pp_venue_map_select option:selected").text();
					$('.pp_venue_map_name').val(selected_map_name);
					let venue_map_id = $("#pp_venue_map_select option:selected").val();
					$(document).find('.ase-remove-wrapper').attr('data-venue_id', venue_map_id);
					$(document).find('.ase-remove-wrapper').prop('data-venue_id', venue_map_id);
					$('.pp_venue_id').attr('value', venue_map_id);
					$('.pp_venue_id').prop('value', venue_map_id);
					let temp_data = "";

					$(".hidden-venue-map").val(venue_map_id);
					$(".hidden-venue-map").attr('value', venue_map_id);

					if ( $(".as_seating").is(':checked') ) {
						if ("Please, select venue" === venue_map_id) {
							$("#pp_venue_map_select").addClass('err_red');
						} else {
							$("#pp_venue_map_select").removeClass('err_red');
						}
					}
					else {
						$("#pp_venue_map_select").removeClass('err_red');
					}
					$(".ase-remove-wrapper").html("<span class='insert-price-row-before'></span>");
					$(".venue-preloader-cust").show();

					// add custom functionality for venue map dynamic processing and adding rows
					let all_data = {
						"action": "venue_map_processing",
						"map_selected": venue_map_id,
					};
					$.ajax({
						url: ajaxurl,
						data: all_data,
						type: "POST",
						success: function (data) {
						    let new_price_row = $(data);

							$(document).find(".ase-remove-wrapper").html("<span class='insert-price-row-before'></span>");

							if (new_price_row.length) {
							    new_price_row.each(function (key, item) {
                                    let last_seed_num = parseInt(localStorage.getItem('last_seed_num')) + 1;
                                    $(item).attr('data-seed', "seed_"+last_seed_num);
                                    localStorage.setItem('last_seed_num', last_seed_num);

                                    $(document).find(".prices-wrapper-design").find(".insert-price-row-before").before($(item)[0].outerHTML);
                                });
                            }

                            $(".venue-preloader-cust").hide();
                        }
					});
				});

				let val_map = $(".hidden-venue-map").val();
				$(".seating-hide-bg").hide();
				$("#pp_venue_map_select option").each(function () {
					if ($(this).val() === val_map) {
						$(this).attr("selected", true);
						let msg = "The seating type and venue map cannot be changed once the event has been created. If you need to modify the layout, please contact Purplepass.";
						$("#pp_venue_map_select").prop("disabled", true);
						setTimeout(function () {
							$(".seating-msg").text(msg);
							$(".seating-hide-bg").show();
						}, 1000);
					}
				});

				// if venue map id exits
				if ( val_map ) {
					setTimeout(function () {
						$('.ase-remove-wrapper .wrap-current-price.one-repeater-item').each(function () {
							if ( '0' == $(this).attr('data-section_id') ) {
								$(this).find('.list-prices-field li:first-child').removeAttr('style');
							}
						});
					}, 1100 );
				}
			});
		</script>

        <!-- What is being sold -->
        <label class="wrap-select " for="pp_being_sold" style="display:none;">
            <strong class="title-strong">What is being sold ?</strong>
            <br>
            <i class="fas fa-sort-down"></i>
			<select class="pp_being_sold">
  				<option value="0">Individual seats</option>
  				<option value="1">Whole tables</option>
			</select>
		 </label>
		 
		 <div class="notices-box">
            <strong class="title-strong">Creating a New Seating Chart</strong>
            <p>
				If you need a seating chart created for your venue, please contact Purplepass at <a href="mailto:support@purplepass.com" >support@purplepass.com</a> or 800-316-8559 Option 3.
				We will quickly build your map for free and add it to your account so it appears here in this list of available venue maps.
			</p>
		 </div>
		  
		  <br><br>
		  <input type="text" class="hidden-being-sold" style="display:none;" name="pp_being_sold" value="<?php echo esc_textarea( $pp_being_sold ); ?>" >
		  <script>
			jQuery(document).ready(function($){
				$( ".pp_being_sold").on("change", function(){
					let sold_selected = $( ".pp_being_sold option:selected" ).val();
					$(".hidden-being-sold").val(sold_selected);
				});
				let val_sold = $(".hidden-being-sold").val();
				$(".pp_being_sold option").each(function() {
					if ( $(this).val() === val_sold ) {
						$(this).attr("selected",true);
					}
				});
			});
		  </script>
		  <div style="clear:both;"></div>
		  <br>
        	
        </div>
        <div class="seating-msg" style="color:#ff0000; margin-top:10px; display: block;"></div>
        
        <div class="venue-preloader-cust" 
        style="display:none;
        margin-bottom:50px;
        max-width: 1089px;
        margin-top: 50px;
        text-align: center !important;
        height: 120px;
    	background: #f5f5f5;
    	padding-top: 17px;
    		-webkit-box-shadow: 0px 0px 5px 1px rgba(107,107,107,1);
			-moz-box-shadow: 0px 0px 5px 1px rgba(107,107,107,1);
			box-shadow: 0px 0px 5px 1px rgba(107,107,107,1);
			opacity: 0.5;
			border-radius: 10px;
    	">
			<div style="width:300px; margin:0 auto;">
				<img style="width: 85px; margin-left: -215px;" src="<?php echo PPTEC_PLUGIN_DIR; ?>/img/preloader.gif">
				<span style="font-size:16px; display: block; margin-left: 78px;
			margin-top: -55px; text-align: left;">Fetching Venue Details</span>
			</div>
    	</div>
			
		  <input type="text" class="hidden-price-type" style="display:none;" name="pp_price_type" value="<?php echo esc_textarea( $pp_price_type ); ?>" >
		  
		  <script>
		  jQuery(document).ready(function($){
		  	$(".seating-msg").hide();
		  	$(".wrap-price.full-price-table-design").addClass("anti-margin-60");
		  	
		  		function show_hide_type_select(){
					$(document).find(".prices-wrapper-design").find(".one-repeater-item").find(".one-type-select").each(function() {
					  	if ( $(this).text() === "Custom" ) {
					  		$(this).parent().parent().next().find(".new-price-name").show().prop("disabled", false).addClass("optional");
					        $(this).parent().parent().next().find(".new-price-name").parent().show();
					        $(this).parent().parent().next().find(".new-price-name").parent().find(".name-price-title").show();
					  	} else {
					  		$(this).parent().parent().next().find(".new-price-name").hide().prop("disabled", true).removeClass("optional");
					        $(this).parent().parent().next().find(".new-price-name").parent().hide();
					        $(this).parent().parent().next().find(".new-price-name").parent().find(".name-price-title").hide();
					  	}
					});
				}
		  	
		  	if ( $( "#as_seating").prop("checked", false) && $( "#ga_seating").prop("checked", false) ) {
		  		$( "#ga_seating").prop("checked", true);
		  		 $(".seating-hide-bg").hide();
		  	}
		  	
			  $("#ga_seating").on("click", function(e){
			  	 $(".seating-hide-bg").hide();
					$(document).find(".wrap-price.full-price-table-design .one-type-select").show();
					$(document).find(".wrap-price.full-price-table-design .one-type-select").parent().parent().show();
					
					$(".hidden-price-type").prop("value", "0");
					$( "#as_seating").prop("checked", false);
					$( "#ga_seating").prop("checked", true);
					$(".being-sold-wrapper").hide();
					
					$(document).find(".wrap-price.full-price-table-design .one-type-select").each(function () {
						let select_value = $(this).find("option:selected").text();
			
						if ( select_value === "Custom" ) {
							$(this).parent().parent().next().find("input[type=text]").prop("disabled", false).show().removeAttr("style");
							$(this).parent().parent().next().find(".name-price-title").show().removeAttr("style");
							$(this).parent().parent().next().find(".new-price-name").show();
							
							$(this).parent().parent().parent().find(".title-strong").show().removeAttr("style");
							$(this).parent().parent().parent().find(".new-price-qty").show();
							$(this).parent().parent().parent().find(".new-price-qty").parent().show();
						} 
						
						if ( select_value === "VIP" || select_value === "General Admission" ) {
							$(this).parent().parent().parent().find(".new-price-name").parent().hide();
							$(this).parent().parent().parent().find(".new-price-name").hide();
						
							$(this).parent().parent().parent().find(".new-price-qty").parent().show();
							$(this).parent().parent().parent().find(".new-price-qty").show();
						}
			
						if ( "Donations" === select_value ) {
							$(this).parent().parent().next().next().find('.new-price-price').attr('title', 'This allows you to set a minimum amount that must be donated');
                            $(this).parent().parent().next().next().find('.new-price-price').attr('placeholder', 'Min. Donation');
                            $(this).parent().parent().next().next().find('.title-strong').text('Min. Donation');

							$(this).parent().parent().next().find(".new-price-name");
							$(this).parent().parent().next().find(".new-price-name").removeAttr("style").removeAttr("disabled").show();
							$(this).parent().parent().next().find(".name-price-title").removeAttr("style").show();
						} else {
							$(this).parent().parent().next().next().find('.new-price-price').removeAttr('title');
                            $(this).parent().parent().next().next().find('.new-price-price').attr('placeholder', 'Price');
                            $(this).parent().parent().next().next().find('.title-strong').html('Price <span class="red-ast">*</span>');
						}
					});	
					
					let is_row_exists = $(document).find(".ase-remove-wrapper .map-venue-added").length;
					if ( is_row_exists > 0 ) {
						$(document).find(".ase-remove-wrapper .map-venue-added").remove();
					}
					is_row_exists = $(document).find(".ase-remove-wrapper .map-venue-added").length;
					
					if ( "yes" === localStorage.getItem("assigned_clicked") && (is_row_exists === 0) ) {
						localStorage.setItem("assigned_clicked", "no");
						$("#pp_venue_map_select option:first-child").prop("selected", true);
						$(document).find(".ase-remove-wrapper").html("<span class='insert-price-row-before'></span>");
					}
						
						$(".seating-msg").hide();
					
				});
				

				
				setTimeout(function() {
				  if ( true === $( "#as_seating").prop("checked") ) {
						$(document).find(".prices-wrapper-design").find(".one-repeater-item").each(function() {
							$(this).find(".new-price-name").show().prop("disabled", false);
							$(this).find(".new-price-name").parent().show();
							$(this).find(".new-price-name").parent().find(".name-price-title").show();
							
							$(this).find(".new-price-price").show().prop("disabled", false);
							$(this).find(".new-price-price").parent().show();
							
							$(this).find(".new-price-qty").show().prop("disabled", false);
							$(this).find(".new-price-qty").parent().show();
							
						});
						 $(".seating-hide-bg").show();
				  } else {
				  	 $(".seating-hide-bg").hide();
				  }
				  
				}, 500);

				setTimeout(function() {
				  	if( $("#ga_seating").is(":checked") ) {
						$(".seating-hide-bg").hide();
					}
				}, 1500);
				
				let pr_name = $(".hidden-price-type").val();
				if ( 0 === pr_name || "0" === pr_name ) {
					$( "#ga_seating").prop("checked", true);
					$( "#as_seating").prop("checked", false);
				} else {
					$( "#as_seating").prop("checked", true);
					$( "#ga_seating").prop("checked", false);
				}

				setTimeout(function() {
				  	if ( true === $( "#as_seating").prop("checked") ) {
						$(".new-price-name").show().prop("disabled", false).removeClass("optional");
						$(".new-price-name").parent().show();
						$(".new-price-name").parent().find(".name-price-title").show(); 
					}
					
					// if checked Assigned Seating - show select
					if ( $( "#as_seating").prop("checked") ) {
						$(".being-sold-wrapper").show();
					} else {
						$(".being-sold-wrapper").hide();
					}
					
				}, 1000);

				if ( $(".as_seating ").is(":checked") ) {  
					$(".wrap-price.full-price-table-design").addClass("additional-margin");
          // $("#ga_seating").parent().hide();
          $("#ga_seating").parents('.field-general-admission').hide();
				} else {
					$(".wrap-price.full-price-table-design").removeClass("additional-margin");
          // $("#ga_seating").parent().show();
          $("#ga_seating").parents('.field-general-admission').show();
				}

		  });
	</script>
<br>