<?php
/**
 * Sales start
 */
if ( empty( $sales_start ) ) {
	$dat = time();
	$sales_start = date( 'Y/m/d', $dat );
}

$s_start[0] = date('Y-m-d', strtotime($sales_start));

if ( isset($s_start[1]) ) {
	$s_start_time = explode( ':', $s_start[1] );
}

ob_start();
foreach( pptec_get_time_list() as $key => $item ) {
	echo '<option value="'.$key.'">' . $item . '</option>';
}
$sales_start_time = ob_get_clean();

/**
 * Sales stop
 */
$s_stop = explode(' ', $sales_stop );
if ( isset( $s_stop[1] ) ) {
	$s_stop_time = explode( ':', $s_stop[1] );
} else {
	$s_stop_time = '';
}
if ( '0' === $s_stop[0] ) {
	$s_stop[0] = '';
}

ob_start();
foreach( pptec_get_time_list() as $key => $item ) {
	echo '<option value="'.$key.'">' . $item . '</option>';
}
$sales_stop_time = ob_get_clean();

ob_start();
foreach( pptec_get_time_list() as $key => $item ) {
	echo '<option value="'.$key.'">' . $item . '</option>';
}
$doors_open_time = ob_get_clean();
?>


<div class="group-fields field-ticket-sales wrap-sales">
  <div class="field field-ticket-start">
    <label class="field-title title-strong" for="sale_start_date">
      Ticket Sales Start at: <span class="red-ast">*</span>
      <b class="tooltip">
        <i class="fas fa-question-circle"></i>
        <span class="tooltiptext">This is when ticket sales will go live online and allow customers to begin buying tickets.</span>
      </b>
    </label>

    <div class="wrap-date">
				<input type="text" name="sale_start_date" class="sale_start_date" value="<?php echo $s_start[0]; ?>">
				<div class="wrap-sales-select">
					<i class="fas fa-sort-down"></i>
					 <div class="control-group">
						 <select id="select-start-time" class="demo-default" placeholder="Time">
							 <option value="">Time</option>
							 <?php echo $sales_start_time; ?>
						 </select>
					 </div>
					<script>
						jQuery(document).ready(function($) {
							let nowDate = new Date();
							let year = nowDate.getFullYear();
							let month = (nowDate.getMonth()+1);
							if ( month < 10 ) {
								month = "0" + month;
							}
							let day = nowDate.getDate();
							if ( day < 10 ) {
								day = "0" + day;
							}
							var hours = new Date();
							hours = hours.toLocaleTimeString().replace(/:\d+ /, "");
							hours = hours.toLowerCase();
							let date = year + "-" + month + "-" + day;
							if ( "/wp-admin/post-new.php" === window.location.pathname ) {
								if ( date ) {
									$(".sale_start_date").val(date);
								}
								setTimeout(function() {
								  $("#select-start-time").next().find("div.item").text(hours);
								  let h2 = hours.replace("am", "").replace("pm", "");
								  $("#select-start-time").next().find("div.item").prop("data-value", h2);
								}, 1000);
							}
						});

						var $select = jQuery("#select-start-time").selectize({
							create: true,
							dropdownParent: "body"
						});
					</script>
				</div>
				<input style="display: none;" type="text" class="sales_start filter-date" name="sales_start" value="<?php echo $sales_start; ?>" data-time="<?php echo $sales_start_time_attr; ?>" />
			    <script>
					/**
					*  Auto select time
					*/
					if ( $select.length > 0 ) {
						var selectize = $select[0].selectize;
						let ss_time = jQuery(".sales_start").attr("data-time");

						selectize.setValue(ss_time);
					}
				 </script>
			</div>

			<?php
			if ( !isset( $_GET['post'] ) ) { ?>
				<script>
					let now = new Date();
					hour = "" + now.getHours();
					if ( hour.length == 1 ) {
						hour = "0" + hour;
					}

					let set_hour = hour+":00";
					selectize.setValue(set_hour);
				</script>
				<?php
			}
			?>

			<script>
				jQuery(document).ready(function($) {
					$(document).on("change", ".sale_start_date, #select-start-time", function() {
						let sales_start_date = $(".sale_start_date").val();
						let sale_start_time = $("#select-start-time").val();
						let full_date = sales_start_date + " " + sale_start_time + ":00";
						$(".sales_start").attr("value", full_date);
					});
				});
			</script>
  </div>

  <div class="field field-ticket-stop">
    <label class="field-title title-strong" for="">
      Ticket Sales Stop at: <span class="red-ast">*</span>
      <b class="tooltip">
        <i class="fas fa-question-circle"></i>
        <span class="tooltiptext">This is when the online ticket sales will stop so customers can no longer buy tickets online.</span>
			</b>
    </label>

    <div class="wrap-date">
			<input type="text" name="sale_stop_date" class="sale_stop_date" value="<?php echo $s_stop[0]; ?>">
      <div class="control-group">
        <select id="select-stop-time" class="demo-default" placeholder="Time">
          <option value="">Time</option>
          <?php echo $sales_stop_time; ?>
        </select>
      </div>
      
      <script>
        var $select_2 = jQuery("#select-stop-time").selectize({
          create: true,
          dropdownParent: "body"
        });
      </script>

			<input style="display: none;" type="text" class="sales_stop filter-date" name="sales_stop" data-time="<?php echo $sales_stop_time_attr; ?>" value="<?php echo $sales_stop; ?>"/>
      
      <script>
        /**
        *  Auto select time
        */
        var selectize_2 = $select_2[0].selectize;
        let ss_time_2 = jQuery(".sales_stop").attr("data-time");
        selectize_2.setValue(ss_time_2);
        selectize_2.setValue(ss_time_2);
        jQuery(document).ready(function($) {
          let ss_time_2 = jQuery(".sales_stop").attr("data-time");
          $(document).find(".wrap-sales #select-stop-time option:selected").val(ss_time_2);
          $(document).on("change", ".sale_stop_date, #select-stop-time", function() {
            let sale_stop_date = $(".sale_stop_date").val();
            let sale_stop_time = $("#select-stop-time").val();
            let full_date = sale_stop_date + " " + sale_stop_time + ":00";
            $(".sales_stop").attr("value", full_date);
          });
        });
      </script>
    </div>
  </div>

  <div class="field field-doors-open">
    <label class="field-title title-strong" for="">
      Doors open at: 
      <b class="tooltip">
        <i class="fas fa-question-circle"></i>
        <span class="tooltiptext">This is when you plan on opening the doors to start letting people in.
        For example, if your show starts at 8:00pm, you might plan on opening the doors at 7:00pm to start admitting guests early.</span>
      </b>
    </label>

    <div class="wrap-date">
        <div class="control-group">
            <select id="select-doors-open-time" class="demo-default" placeholder="Time">
              <option value="">Time</option>
              <?php echo $doors_open_time; ?>
            </select>
        </div>
      
      <script>
        var $select_3 = jQuery("#select-doors-open-time").selectize({
          create: true,
          dropdownParent: "body"
        });
      </script>

			<input style="display: none;" type="text" class="doors_open filter-date <?php if (!empty($doors_open)) { ?>stored<?php } ?>" name="doors_open" data-time="<?php echo $doors_open_time_attr; ?>" value="<?php echo $doors_open_time_attr; ?>"/>
        
      <script>
        /**
        *  Auto select time
        */
        var selectize_3 = $select_3[0].selectize;
        let ss_time_3 = jQuery(".doors_open").attr("data-time");
        selectize_3.setValue(ss_time_3);
        function time_from_12_to_24( time ){
          if ( time.length > 1 ) {
            var hours = Number(time.match(/^(\d+)/)[1]);
          } else {
            var hours = "00";
          }
          if ( time.length > 1 ) {
            var minutes = Number(time.match(/:(\d+)/)[1]);
          } else {
            var minutes = "00";
          }
          var AMPM = time.match(/\s(.*)$/);
          if ( AMPM ) {
            AMPM = AMPM[1];
          }
          if(AMPM == "PM" && hours<12) hours = hours+12;
          if(AMPM == "AM" && hours==12) hours = hours-12;
          var sHours = hours.toString();
          var sMinutes = minutes.toString();
          if(hours<10) sHours = "0" + sHours;
          if(minutes<10) sMinutes = "0" + sMinutes;
          let ready_time = sHours + ":" + sMinutes;
          return ready_time;
        }
				jQuery(document).ready(function($) {
					$(document).on("change", "#select-doors-open-time", function() {
						let doors_open_time = $("#select-doors-open-time").val() + ":00";
						$(".doors_open").attr("value", doors_open_time);
					});
					/**
					*   If Event PRO time changed - change Selectizer too
					*/
					$(".tribe-field-start_time").on("change", function (e) {
						e.preventDefault();

						// Do nothing if doors opened value has been already stored
						if ($('.doors_open').hasClass('stored')) {
                            return;
                        }

                        let start_time = $(".tribe-field-start_time").val();
                        start_time = start_time.replace("am", " AM");
                        start_time = start_time.replace("pm", " PM");
                        start_time = time_from_12_to_24( start_time );
                        start_time = start_time +":00";

                        let sep_time = start_time.split(":");
                        let hrs = sep_time[0];
                        let mns = sep_time[1];

                        let hrs_sales_stop = Number(hrs) - 1;

                        var today = new Date();
                        let hrs_sales_start_new = Number(today.getHours());

                        if ( "/wp-admin/post-new.php" === window.location.pathname ) {

                          if ( Number(hrs_sales_start_new) < 10 ) {
                            selectize.setValue("0"+Number(hrs_sales_start_new)+":"+mns);
                          } else {
                            selectize.setValue(hrs_sales_start_new+":"+mns);
                          }
                        }
                        if ( Number(hrs_sales_stop) < 10 ) {
                          selectize_2.setValue("0"+Number(hrs_sales_stop)+":"+mns);
                        } else {
                          selectize_2.setValue(hrs_sales_stop+":"+mns);
                        }
                        if ( Number(hrs) < 10 ) {
                          selectize_3.setValue("0"+Number(hrs)+":"+mns);
                        } else {
                          selectize_3.setValue(hrs+":"+mns);
                        }
					});
				});
			</script>
		</div>
  </div>
</div>
