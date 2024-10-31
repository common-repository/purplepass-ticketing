<p class="field field-category wrap-select">
  <label class="field-title categories-label states-label title-strong" for="pp_categories_choosen">
    Event category
    <span class="red-ast">*</span>
    <b class="tooltip">
      <i class="fas fa-question-circle"></i>
      <span class="tooltiptext">This will be used for categorizing your event for marketing purposes.
      If you choose to make this event public, this is the category your event will be placed in on the Purplepass.com website.
      We will also use this category when blasting your event to upcoming "Things to do" and event listing sites to make sure all
      potential customers will be able to easily find it.</span>
    </b>
  </label>

  <i class="fas fa-sort-down"></i>
  <select name="pp_categories_choosen" id="pp_categories_choosen" >
    <option value="Please select category">Please select category</option>
    <?php echo pptec_get_categories_list_options(); ?>
  </select>

  <input type="text" class="optional hidden-categories-choosen" style="display:none;" name="pp_categories_choosen" value="<?php echo esc_textarea( $pp_categories_choosen ); ?>">
</p>

<script>
	jQuery(document).ready(function($){
		$( "#pp_categories_choosen").on("change", function(){
			$(this).removeClass("err_red");
				let pp_categories_choosen = $( "#pp_categories_choosen option:selected" ).val();
			$(".hidden-categories-choosen").val(pp_categories_choosen);

			if ( 'Please select category' === $("#pp_categories_choosen option:selected").text() ) {
				$("#pp_categories_choosen").addClass("err_red");
			}
		});
		let cat_c = $(".hidden-categories-choosen").val();
		$("#pp_categories_choosen option").each(function() {
			if ( $(this).val() === cat_c ) {
				$(this).attr("selected",true);
			}
		});
	});
</script>