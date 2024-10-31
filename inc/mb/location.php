<div class="middle-heading">
  <span>Venue Location</span>
</div>

<p class="field field-adress">
  <label class="field-title title-strong">Address <span class="red-ast">*</span></label>

  <textarea id="pp_address" style="height:100px;" size="100" name="pp_address"><?php echo esc_textarea( $pp_address ); ?></textarea>
</p>

<div class="group-fields field-location wrap-location">
  <p class="field field-city">
    <label class="field-title title-strong" for="pp_city">City <span class="red-ast">*</span></label>

    <input id="pp_city" type="text" size="40" name="pp_city" value="<?php echo esc_textarea( $pp_city ); ?>">
  </p>

  <p class="field field-state">
    <label class="field-title title-strong states-label" for="pp_state_choosen">State <span class="red-ast">*</span></label>
    
    <i class="fas fa-sort-down"></i>
    <select name="pp_state_choosen" id="pp_state_choosen">
        <?php echo pptec_get_us_states_list(); ?>
    </select>

    <input type="text" class="optional hidden-state-choosen" style="display:none;" name="pp_state_choosen" value="<?php echo esc_textarea( $pp_state_choosen ); ?>" >
  </p>
</div>
   
<script>
  jQuery(document).ready(function($){
    $( "#pp_state_choosen").on("change", function(){
      let pp_state_choosen = $( "#pp_state_choosen option:selected" ).val();

      if ( "-" === pp_state_choosen ) {
          $( "#pp_state_choosen").addClass("err_red");
      } else {
          $( "#pp_state_choosen").removeClass("err_red");
      }
      $(".hidden-state-choosen").val(pp_state_choosen);
    });
  });
</script>

<div class="group-fields field-location wrap-location">
  <p class="field field-zip">
    <label class="field-title title-strong" for="pp_zip">Zip <span class="red-ast">*</span></label>
    <input id="pp_zip" type="text" size="15" name="pp_zip" value="<?php echo esc_textarea( $pp_zip ); ?>">
  </p>

  <p class="field field-country">
    <label class="field-title title-strong country-label" for="pp_country_choosen">Country <span class="red-ast">*</span></label>

    <i class="fas fa-sort-down"></i>
		<select name="pp_country_choosen" id="pp_country_choosen">
		    <?php echo pptec_get_countries_list(); ?>
    </select>
    
    <input type="text" class="optional hidden-country-choosen" style="display:none;" name="pp_country_choosen" value="<?php echo esc_textarea( $pp_country_choosen ); ?>">
  </p>
</div>

<script>
	jQuery(document).ready(function($){
		$( "#pp_country_choosen").on("change", function(){
			let pp_country_choosen = $( "#pp_country_choosen option:selected" ).val();
			$(".hidden-country-choosen").val(pp_country_choosen);
		});
		let country_c = $(".hidden-country-choosen").val();
		$("#pp_country_choosen option").each(function() {
			if ( $(this).val() === country_c ) {
				$(this).attr("selected",true);
			}
		});
	});
</script>

<style>
	.zip-preloader-cust img{
		width: 80px !important;
	}
	.zip-preloader-cust{
		width: 80px;
	    margin-left: -26px;
	    margin-top: -30px;
	    margin-right: -20px;
	    float: left;

	    display: none;
	}
</style>

<div class="zip-preloader-cust"><img src="<?php echo PPTEC_PLUGIN_DIR; ?>/img/preloader.gif"></div>
    <span class="timezone-response"></span><br><br>
    <div style="clear:both;"></div>
    <div class="wrap-location" style="display: none;">
		<label class="timezone-label" for="">
			<strong class="title-strong">Timezone</strong> <br>
			<i class="fas fa-sort-down"></i>
			<select name="" id="pp_timezone_choosen">
			    <?php echo pptec_get_timezone_list(); ?>
			</select>
		</label>
    </div><br>
    <input type="text" class="optional pp_timezone_choosen_hidden" style="display:none;" name="pp_timezone_choosen" value="<?php echo esc_textarea( $pp_timezone_choosen ); ?>" >
	<script>
		jQuery(document).ready(function($){
			$( "#pp_timezone_choosen").on("change", function(){
				let pp_timezone_choosen = $( "#pp_timezone_choosen option:selected" ).val();
				$(".pp_timezone_choosen_hidden").val(pp_timezone_choosen);
			});
			let val_tz = $(".pp_timezone_choosen_hidden").val();
			$("#pp_timezone_choosen option").each(function() {
				if ( $(this).val() == val_tz ) {
					$(this).attr("selected",true);
				}
			});
		});
	</script>