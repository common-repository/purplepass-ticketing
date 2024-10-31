<!-- Provide special note or instructions to Purplepass telephone support staff -->

	 <strong class="title-strong" style="display: block;">Provide special note or instructions to Purplepass telephone support staff
	 <div class="tooltip">
         <i class="fas fa-question-circle"></i>
         <span class="tooltiptext">Purplepass provides 24/7/365 telephone support for your customers.
         This allows you to pass a message to our phone support team so they will always have the latest information about your event to help your customers.
         For example, if the parking lot to your event is closed for repair, you can leave a message to tell customers to park in the parking lot behind the building.
         This way, when customers call in asking where they should park, our staff will be able to quickly help them with up-to-date accurate information.</span>
     </div>
     </strong>
    <br>
	<div class="pass-evt wrapper-event-type visibility-evt" style="max-width: 504px;">
	     <label for="spec_note_yes">
			 <input id="spec_note_yes" type="checkbox" name="spec_note_yes" class="spec_note_yes">
			 <span class="text-label"><strong class="title-strong">Yes</strong></span>
			 <span class="input-checkmark"></span>
		 </label>

		 <label for="spec_note_no" style="margin-right: 310px;">
			 <input id="spec_note_no" type="checkbox" name="spec_note_no" class="spec_note_no">
			 <span class="text-label"><strong class="title-strong">No</strong></span>
			 <span class="input-checkmark"></span>
		</label>
    </div>
        
    <input type="text" style="display: none;" value="<?php echo $pp_spec_notes_hidden; ?>" class="optional pp_spec_notes_hidden" name="pp_spec_notes_hidden"><br>
		  
	<script>
	jQuery(document).ready(function($){

		$(".cust-spec-notes-hide").hide();

		$( "#spec_note_yes").on("click", function(e){
			if ($(this).is(":checked")) {
				$(".pp_spec_notes_hidden").prop("value", "1");

				$( "#spec_note_no").prop("checked", false);
				$(".cust-spec-notes-hide").show();

			} else {
				e.preventDefault();
			}
		});

		$( "#spec_note_no").on("click", function(e){
			if ($(this).is(":checked")) {
				$(".pp_spec_notes_hidden").prop("value", "0");
				$( "#spec_note_yes").prop("checked", false);

				$(".cust-spec-notes-hide").hide();
			} else {
				e.preventDefault();
			}
		});

		let pr_name = $(".pp_spec_notes_hidden").val();
		if ( 0 === pr_name || "0" === pr_name ) {
		    $( "#spec_note_yes").prop("checked", false);
		    $(".cust-spec-notes-hide").hide();
		    $( "#spec_note_no").prop("checked", true);
		} else {
		    $( "#spec_note_yes").prop("checked", true);
		    $( "#spec_note_no").prop("checked", false);
		    $(".cust-spec-notes-hide").show();
		}

	});
	</script>

		  <input type="text" style="display: none;" value="<?php echo $pp_spec_notes_hidden; ?>" class="optional pp_spec_notes_hidden" name="pp_spec_notes_hidden"><br>

		  <div class="cust-spec-notes-hide" style="margin-left:45px;">
			  <label for="pp_spec_notes_text" class="">
			<i style="transform: rotate(180deg);" class="fas fa-reply arrow-custom-deg"></i>
				  <textarea id="pp_spec_notes_text" type="text" class="common-textarea optional pp_spec_notes_text" name="pp_spec_notes_text" style="width: 539px; height: 94px;"><?php echo $pp_spec_notes_text; ?></textarea>
			   </label><br>
		  </div>
		  <script>
			  jQuery(document).ready(function($){
				$(".cust-spec-notes-hide").hide();

				if ( $(".pp_spec_notes_hidden").val() === "1" ) {
					$(".cust-spec-notes-hide").show();
					$("#pp_spec_notes").attr("checked", true);
				} else {
					$(".cust-spec-notes-hide").hide();
					$("#pp_spec_notes").attr("checked", false);
				}

				$( ".pp_spec_notes").on("click", function() {
					let pp_spec_notes = $( ".pp_spec_notes").is(":checked");

					if ( pp_spec_notes ) {
						$(".cust-spec-notes-hide").show();
						$(".pp_spec_notes_hidden").val("1");
					} else {
						$(".cust-spec-notes-hide").hide();
						$(".pp_spec_notes_hidden").val("0");
					}
				});
			  });
			</script>