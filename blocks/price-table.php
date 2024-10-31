<?php
if ( isset( $_GET['post'] ) ) {
	$pp_event_id = pptec_get_pp_event_id_by_wp_event_id( sanitize_text_field( $_GET['post'] ) );
} else {
	$pp_event_id = false;
}

$access_token  = pptec_get_access_token();
$pp_user_id = get_user_meta(get_current_user_id(), 'pptec_pp_user_id', true);

?>

<div class="wrap-current-price for-clone" style="display: none;" data-seed="" data-first_str="no">
	<div class="prices-fields">
		<ul class="list-prices-field">
			<li>
				<strong class="title-strong">Item Type</strong>
				<div class="wrap-price-select">
					<i class="fas fa-sort-down"></i>
					<select name="" class="one-type-select" >
						<option  value="2">Custom</option>
						<option  value="0">General Admission</option>
						<option  value="1">VIP</option>
						<option  value="4">Donations</option>
						<option  value="5" style="display: none;">Group</option>
						<option  value="6" style="display: none;">Divider</option>
						<option  value="32773" style="display: none;">Season/Series Pass</option>
					</select>
				</div>
			</li>
			<li>
				<strong class="title-strong name-price-title">Name <span class="red-ast">*</span></strong>
				<input type="text" placeholder="Name" class="optional new-price-name" value="">
			</li>
			<li>
				<strong class="title-strong">Price <span class="red-ast">*</span></strong>
				<input type="text" placeholder="Price" value="" class="new-price-price">
			</li>
			<li>
				<strong class="title-strong">Qty to Sell</strong>
				<input type="text" placeholder="QTY" class="optional new-price-qty" value="">
			</li>
        </ul>
    
		<div class="wrap-price-description">
			<i style="transform: rotate(180deg);" class="fas fa-reply"></i>
			<span class="title-strong">&nbsp; Description</span>
			<input type="text" class="optional new-price-desc" value="">
		</div>
  </div>
  
	<div class="prices-btn">
		<button class="option-price cust-btn-new-design">
			<i class="fas fa-cog"></i>
			<span>Options</span>
		</button>
		<button class="delete-price cust-btn-new-design">
			<i class="fas fa-times-circle"></i>
			<span>Delete</span>
		</button>
	</div>
</div>

<style>
	.cust-btn-new-design:hover{
		cursor: pointer;
	}
	.wrap-current-price{
		margin-top: 10px;
		margin-bottom: 10px;
	}
</style>
<div class="prices-wrapper-design">
	<?php
	if ( isset( $_GET['post'] ) ) {
		$counter_price = get_post_meta( sanitize_text_field( $_GET['post'] ), 'current_post_row_counter_price', true );
		if ( empty( $counter_price ) ) {
			$counter_price = 0;
		}
	} else {
		$counter_price = 0;
	}
	?>
	<div class="wrap-price full-price-table-design">
		<strong class="title-strong">Prices:
			<div class="tooltip">
				<i class="fas fa-question-circle"></i>
				<span class="tooltiptext">You can choose the type of ticket, its face value, and how many tickets you want to sell. Optionally, you can limit the number of each ticket type a customer can purchase per order. To add an additional ticket type (Such as VIP), click the “Add ticket type” button.</span>
			</div>
		</strong>
		<div class="ase-remove-wrapper" data-venue_detect="<?php echo esc_attr($pp_price_type); ?>" data-venue_id="<?php echo $pp_venue_map; ?>">
			<?php
			$flag_ga       = 0;
			$flag_vip      = 0;
			$flag_custom   = 0;

			$types_prices  = array(
				0     => 'General Admission',
				1     => 'VIP',
				2     => 'Custom',
				4     => 'Donations',
				5     => 'Group',
				6     => 'Divider',
				32773 => 'Season/Series Pass',
			);

			if ( !empty( $prices_right ) ) :
				$count = 0;

				foreach ( $prices_right as $one_price ) : ?>

			<?php
                $price_key = 'seed_' . $count;
                $price_key_prev = 'seed_' . ($count - 1);

				$one_price = (array)$one_price;

				// @todo v2: specific case for AS
				if (!isset($one_price['type'])) {
				    $one_price['type'] = 2;
                }

				if ( !empty( $one_price['section_id'] ) ) {
					$section_id = 'data-section_id="' . $one_price['section_id'] . '"';
				} else {
					$section_id = '';
				}
			?>

				<?php if ( !empty( $types_prices[$one_price['type']] ) && 'Season/Series Pass' === $types_prices[$one_price['type']] ) {
					$style = "style='position: absolute; left: -10000px;'";
                } else {
                    $style = "";
                } ?>

                    <?php if ( 'venue' === $pp_price_type ) : ?>

						<?php
							$plugin_option  = get_option( 'pptec_data' );
							$venue_to_build = '';
							if ( !empty( $plugin_option['venues_list'] ) ) {
								$venues         = $plugin_option['venues_list'];
								foreach ( $venues as $key => $val ) {
									if ( $val->name === $pp_venue_map ) {
										$venue_to_build = $venues[ $key ];
									}
								}
							}
							$venue_id     = ( !empty( $pp_venue_map ) ) ? $pp_venue_map : '0';
							$section_id   = $one_price['section_id'];
							if ( empty($one_price['color']) ) {
								$one_price['color'] = '';
							}

                            $color        = ( !empty($one_price['row_color']) ) ? $one_price['row_color'] : $one_price['color'];
							$section_name = $one_price['section_name'];

							$first_str = 0;
							if (empty($prices_right[$price_key_prev]['section_id']) || ($prices_right[$price_key]['section_id'] !== $prices_right[$price_key_prev]['section_id'])) {
								$first_str = 1;
                            }
							if ( isset($prices_right[$price_key]['section_id']) && '0' == $prices_right[$price_key]['section_id'] ) {
                                $first_str = 'no';
							}

						?>
							<div class="wrap-current-price one-repeater-item wrap-price-type-<?php echo $one_price['type']; ?>" <?php echo $style; ?>
								 data-first_str="<?php echo $first_str; ?>"
								 data-venue_id="<?php echo $venue_id; ?>"
								 data-section_id="<?php echo $section_id; ?>"
								 data-section_name="<?php echo $section_name; ?>"
								 data-color="<?php echo $color; ?>"
							 	 data-seed=""
							 	 data-price_id="<?php echo (!empty($one_price['id'])) ? $one_price['id'] : rand(50, 1500); ?>">
							<div class="prices-fields">
								<ul class="list-prices-field">
									<?php if ( $color ) : ?>
										<li>
											<div style="width: 25px; height:25px; border-radius: 50%; background:<?php echo $color; ?>"></div>
										</li>
									<?php endif; ?>
									<li>
										<strong class="title-strong">Item Type</strong>
										<div class="wrap-price-select">
											<i class="fas fa-sort-down"></i>
											<select name="" class="one-type-select" >
												<?php foreach ( $types_prices as $index => $name ) : ?>
													<?php
													$is_selected = '';
													if ( (int)$index === (int)$one_price['type'] ) {
														$is_selected = ' selected';
														if ( $one_price['type'] === 0 ) { // General Admission
															$flag_ga = 1;
														}
														if ( $one_price['type'] === 1 ) { // VIP
															$flag_vip = 1;
														}
													}
													?>
													<option <?php echo $is_selected; ?> value="<?php echo $index; ?>" <?php echo $index === 5 || $index === 6 ? ' style="display: none;"' : ''; ?>><?php echo $name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</li>
									<li>
										<strong class="title-strong name-price-title">Name <span class="red-ast">*</span></strong> <!-- Name was here -->
										<input type="text" placeholder="Name" class="optional new-price-name" value="<?php echo (isset($one_price['name'])) ? $one_price['name'] : ''; ?>">
									</li>
									<li>
										<strong class="title-strong">Price <span class="red-ast">*</span></strong>
										<input type="text" placeholder="Price" class="new-price-price" value="<?php echo (isset($one_price['price'])) ? $one_price['price'] : ''; ?>" >
									</li>
									<li>
										<strong class="title-strong">Qty to Sell</strong>
										<input type="text" placeholder="QTY" class="optional new-price-qty" value="<?php echo (isset($one_price['quantity'])) ? $one_price['quantity'] : ''; ?>">
									</li>
								</ul>
								<div class="wrap-price-description">
									<i style="transform: rotate(180deg);" class="fas fa-reply"></i>
									<span class="title-strong"> &nbsp; Description</span>
									<input type="text" class="optional new-price-desc" value="<?php echo (isset($one_price['descr'])) ? $one_price['descr'] : ''; ?>">
								</div>
								<?php
								if ( isset($one_price) ) {
									$pr_options = json_encode($one_price);
								} else {
									$pr_options = '[]'; // second item has all options
								}
								?>
							</div>
							<div class="prices-btn">
								<button class="option-price cust-btn-new-design" style="background-color: #2a8fe9;">
									<i class="fas fa-cog"></i>
									<span>Options</span>
								</button>

									<button class="delete-price cust-btn-new-design" style="display: none; background-color: #ff3e6d;">
										<i class="fas fa-times-circle"></i>
										<span>Delete</span>
									</button>
									<?php
									$prices_right[$price_key] = (array)$prices_right[$price_key];
									if ( empty($prices_right[$price_key_prev]) || ($prices_right[$price_key]['section_id'] !== $prices_right[$price_key_prev]['section_id']) ) : ?>
										<button class="add-new-price-assigned cust-btn-new-design" data-color="<?php echo str_replace("#", "", $one_price['color']); ?>"
												style="width: 50px;
												background: #78bf42;">
											<i class="fas fa-plus"></i>
										</button>

									<?php else: ?>
										<button class="delete-price cust-btn-new-design">
											<i class="fas fa-times-circle"></i>
											<span>Delete</span>
										</button>
									<?php endif; ?>

							</div>
						</div>

                    <?php else: ?>

							<div class="wrap-current-price one-repeater-item wrap-price-type-<?php echo $one_price['type']; ?>" <?php echo $section_id . " " . $style; ?>
								 data-color="<?php echo $one_price['def_ticket_color']; ?>"
								 data-section_name="<?php echo $one_price['section_name']; ?>"
								 data-seed=""
								 data-price_id="<?php echo (!empty($one_price['id'])) ? $one_price['id'] : rand(50, 1500); ?>">
								 <div class="prices-fields">
									<ul class="list-prices-field">
										<?php
										if ( !empty( $one_price['section_name'] ) ) {
											$section_name = $one_price['section_name'];
										} else {
											$section_name = false;
										}
										?>
										<li>
											<strong class="title-strong">Item Type</strong>
											<div class="wrap-price-select">
												<i class="fas fa-sort-down"></i>
												<select name="" class="one-type-select" >
													<?php foreach ( $types_prices as $index => $name ) : ?>
														<?php
														$is_selected = '';
														if ( (int)$index === (int)$one_price['type'] ) {
															$is_selected = ' selected';
															if ( $one_price['type'] === 0 ) { // General Admission
																$flag_ga = 1;
															}
															if ( $one_price['type'] === 1 ) { // VIP
																$flag_vip = 1;
															}
														}

														if ($is_selected == '' && ($index == 5 || $index == 6)) continue;
														?>
														<option <?php echo $is_selected; ?> value="<?php echo $index; ?>" <?php echo $index === 5 || $index === 6 ? ' style="display: none;"' : ''; ?>><?php echo $name; ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</li>
										<li>
											<strong class="title-strong name-price-title">Name <span class="red-ast">*</span></strong> <!-- Name was here -->
											<input type="text" placeholder="Name" class="optional new-price-name" value="<?php echo (isset($one_price['name'])) ? $one_price['name'] : ''; ?>">
										</li>
										<li>
											<strong class="title-strong">Price <span class="red-ast">*</span></strong>
											<input type="text" placeholder="Price" class="new-price-price" value="<?php echo (isset($one_price['price'])) ? $one_price['price'] : ''; ?>" >
										</li>
										<li>
											<strong class="title-strong">Qty to Sell</strong>
											<input type="text" placeholder="QTY" class="optional new-price-qty" value="<?php echo (isset($one_price['quantity'])) ? $one_price['quantity'] : ''; ?>">
										</li>
									</ul>
									<div class="wrap-price-description">
										<i style="transform: rotate(180deg);" class="fas fa-reply"></i>
										<span class="title-strong"> &nbsp; Description</span>
										<input type="text" class="optional new-price-desc" value="<?php echo (isset($one_price['descr'])) ? $one_price['descr'] : ''; ?>">
									</div>
									<?php
									if ( isset($one_price) ) {
										$pr_options = json_encode($one_price);
									} else {
										$pr_options = '[]'; // second item has all options
									}
									?>
								</div>
								<div class="prices-btn">
									<button class="option-price cust-btn-new-design" style="background-color: #2a8fe9;">
										<i class="fas fa-cog"></i>
										<span>Options</span>
									</button>

										<?php if ( 0 !== $count ) : ?>
											<button class="delete-price cust-btn-new-design" style="background-color: #ff3e6d;">
												<i class="fas fa-times-circle"></i>
												<span>Delete</span>
											</button>
										<?php endif; ?>

								</div>
							</div>

                    <?php endif; ?>

					<?php
					$count++;
					?>
				<?php endforeach; ?>
			<?php endif; ?>
			<span class="insert-price-row-before"></span>
		</div>
		<button class="add-ticket-type cust-btn-new-design add-price-row-design"><i class="fas fa-plus" style="margin-right: 10px;"></i>Add Ticket Type</button>
	</div>
    <textarea id="pp_prices_data" style="display: none;" class="optional pp_prices_data" name="pp_prices_data"><?php echo !empty($prices_sanitized) ? json_encode( $prices_sanitized ) : ''; ?></textarea><br>
	<input type="text" name="pp_venue_id" class="pp_venue_id" style="display: none" value="<?php echo ( $pp_venue_map ) ? $pp_venue_map : ''; ?>">
</div>
<script>
	jQuery(document).ready(function($){

		// setTimeout(function () {
		// 	jQuery('.save-repeater').trigger('click');
		// }, 1000);

		let basic_opion_json = '{"id":"0","pr_id":"0","tpl_id":"0","section_id":"0","event_id":"196674","type":0,"price":"500.00","status_message":"","bg_color_from":"","bg_color_to":"","ffee_flat":"0.00","ffee_perc":"0.00","ffee_min":"0.00","stax_perc":"0.00","quantity":5000,"min":"0","limit":"0","name":"","descr":"desc desc desc desc","def_ticket_color":"0","reset_time":"0000-00-00 00:00:00","updated":"2019-12-22 11:47:15","user_id":"48064","options":0,"event_name":"Test capacity","capacity":157,"sales_start":"2019-12-22 00:00:00","sales_stop":"2020-01-17 00:00:00","sales_start_diff":"-42438","sales_stop_diff":"2203962","price_options":"0","require_coupons":"0","ages":"","startson":"","quantity_sold":0,"quantity_refunded":"0","quantity_guestlist":"0","quantity_ticketstock":"0","delivery":"0","bo_color":"","custom_pah_name":null,"cma_id":null,"d_rate":null,"tr_fee":null,"ma_status":null,"_section_id":null,"section_name":null,"color":null,"idx":"4","ssp_options":"0","flex":"0","ouser_id":0,"rules":[],"group":[],"sp":[],"require_tts":[],"epp":false}';

		function IsJsonString(str) {
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		}

		// valid only numbers in price field
		$(document).on('keyup paste', '.ase-remove-wrapper .one-repeater-item .new-price-price', function(){
			this.value = this.value.replace(/[^0-9\.]/g, '');
		});
		// valid only numbers in quantity field
		$(document).on('keyup paste', '.ase-remove-wrapper .one-repeater-item .new-price-qty', function(){
			this.value = this.value.replace(/[^0-9]/g, '');
		});

		localStorage.setItem("current_clicked_btn", '' );
		localStorage.setItem("price_options", '' );

        // Detach from main thread
        setTimeout(function () {
            // Show price row boilerplate, if it is a new event
            if (!$('.ase-remove-wrapper .wrap-current-price').length) {
                $('.add-ticket-type').trigger('click');
            }
        })

		function save_prices_options( price_seed, data ){
			let prices_data_json = $(document).find('.pp_prices_data').text();
            let prices_data = IsJsonString(prices_data_json) ? JSON.parse(prices_data_json) : [];

            prices_data[price_seed] = data.price;

			$(document).find('.pp_prices_data').text(JSON.stringify(prices_data));
			setTimeout(function () {
				jQuery('.save-repeater').trigger('click');
			}, 500);
		}

		$(document).on("click", '.ase-remove-wrapper .one-repeater-item .option-price', function(e) {
			e.preventDefault();
            let price_seed = $(this).closest('.one-repeater-item').attr('data-seed');

            let prices_data_json = $(".pp_prices_data").text();
            let prices_data = IsJsonString(prices_data_json) ? JSON.parse(prices_data_json) : [];

            $(document).find(".ase-remove-wrapper .one-repeater-item").each(function() {
                let price_type = $(this).find('.one-type-select option:selected');
                let seed = $(this).attr('data-seed');

                let one_row = {
                    id: $(this).attr("data-price_id"),
                    seed: seed,
                    type: price_type ? $(price_type).val() : 2,
                    name: $(this).find('.new-price-name').val(),
                    price: $(this).find('.new-price-price').val(),
                    quantity: $(this).find('.new-price-qty').val(),
                    descr: $(this).find('.new-price-desc').val(),
                    section_id: typeof $(this).attr('data-section_id') !== 'undefined' ? $(this).attr('data-section_id') : '',
                    row_color: typeof $(this).attr("data-color") !== 'undefined' ? $(this).attr("data-color") : '',
                    section_name: typeof $(this).attr("data-section_name") !== 'undefined' ? $(this).attr("data-section_name") : ''
                }

                if (typeof prices_data[seed] !== 'undefined') {
                    prices_data[seed]['price'] = one_row.price;
                    prices_data[seed]['type'] = one_row.type;
                    prices_data[seed]['name'] = one_row.name;
                    prices_data[seed]['quantity'] = one_row.quantity;
                    prices_data[seed]['descr'] = one_row.descr;
                    prices_data[seed]['section_id'] = one_row.section_id;
                    prices_data[seed]['row_color'] = one_row.row_color;
                    prices_data[seed]['section_name'] = one_row.section_name;
                } else {
                    prices_data[seed] = one_row;
                }
            });

            let current_price_data = typeof prices_data[price_seed] !== "undefined" ? prices_data[price_seed] : {};

            $.ajax({
                url: ajaxurl,
                data: {
                    "action" : "get_access_token_ajax",
                    "nonce" : pp_js.nonce, // ajax nonce
                },
                type: "POST",
                success: function (data) {
                    PP.window({
                        token: data,
                        params: {
                            edit: 'price_options',
                            event_id: <?php echo ($pp_event_id) ? $pp_event_id : "0"; ?>,
                            user_id: <?php echo $pp_user_id; ?>
                        },
                        data: {
                            price: current_price_data,
                            usable_prices: prices_data
                        },
                        onSave: function(data){
                            save_prices_options( price_seed, data );
                        }
                    }, {mask: '00000060'});
                },
                error: function(xhr, textStatus, errorThrown){
                    alert('request failed->'+textStatus);
                }
            });
		});
	});


	jQuery(document).ready(function ($) {

		/**
		 * GA validation, removing
		 */
		function valid_ga_vip_item(){

			let ga_exists = 0;
			let ga_count  = 0;

			let vip_exists = 0;
			let vip_count  = 0;

			$(document).find(".wrap-price .wrap-current-price.one-repeater-item .one-type-select").each(function () {
				if ( $(this).is(":visible") ) {
					// GA
					if ( 0 == $(this).find('option:selected').val() ) {
						ga_exists = 1;
						ga_count++;
					}
					// VIP
					if ( 1 == $(this).find('option:selected').val() ) {
						vip_exists = 1;
						vip_count++;
					}
				}
			});
			// GA
			if ( ga_exists === 1  ) {
				$(document).find(".wrap-price .wrap-current-price.one-repeater-item .one-type-select").find('option[value="0"]').each(function () {
					if ( false === $(this).prop("selected") ) {
						$(this).hide();
					}
				});
			} else {
				$(document).find(".wrap-price .wrap-current-price.one-repeater-item .one-type-select").find('option[value="0"]').each(function () {
					$(this).show();
				});
			}
			// VIP
			if ( vip_exists === 1  ) {
				$(document).find(".wrap-price .wrap-current-price.one-repeater-item .one-type-select").find('option[value="1"]').each(function () {
					if ( false === $(this).prop("selected") ) {
						$(this).hide();
					}
				});
			} else {
				$(document).find(".wrap-price .wrap-current-price.one-repeater-item .one-type-select").find('option[value="1"]').each(function () {
					$(this).show();
				});
			}

		}
		valid_ga_vip_item();

		$(document).on("change", ".wrap-price .wrap-current-price.one-repeater-item .one-type-select", function () {
			valid_ga_vip_item();
		});

		$(document).on('click', '.add-ticket-type', function () {
			setTimeout(function () {
				valid_ga_vip_item();
			}, 100);
		});


		$(document).find('.prices-wrapper-design .one-type-select').each(function () {
			let select_value = $(this).find('option:selected').val();

			if ( 4 == select_value ) {
				// $(this).parent().parent().next().find('.new-price-name').addClass('err_red');
				$(this).parent().parent().next().find('.new-price-name').parent().show();
                $(this).parent().parent().next().next().find('.new-price-price').attr('title', 'This allows you to set a minimum amount that must be donated');
                $(this).parent().parent().next().next().find('.new-price-price').attr('placeholder', 'Min. Donation');
                $(this).parent().parent().next().next().find('.title-strong').text('Min. Donation');

				$(this).parent().parent().next().next().next().find('.new-price-qty').prop('disabled', true);
				$(this).parent().parent().next().next().next().find('.new-price-qty').removeClass('err_red');
				$(this).parent().parent().next().next().next().find('.new-price-qty').parent().hide();
			} else {
				$(this).parent().parent().next().next().find('.new-price-price').removeAttr('title');
                $(this).parent().parent().next().next().find('.new-price-price').attr('placeholder', 'Price');
                $(this).parent().parent().next().next().find('.title-strong').html('Price <span class="red-ast">*</span>');
				// show all fields after Donation was hidden
				$(this).parent().parent().next().next().next().find('.new-price-qty').parent().show();
				$(this).parent().parent().next().next().find('.new-price-price').parent().show();
			}
		});

	});


	jQuery(document).ready(function ($) {
		if ( $('.hidden-price-type').val() === 'venue' ) {
			$('.wrap-price-select').parent().hide();
		} else {
			$('.wrap-price-select').parent().show();
		}
	});

</script>


<script>
	jQuery(document).ready(function ($) {
		$(document).on("click", ".add-new-price-assigned", function (e) {
			e.preventDefault();

			let cloned_item = $(this).parent().parent().clone();
			cloned_item.find(".delete-price").show();
			cloned_item.find(".add-new-price-assigned").hide();

            let last_seed_num = parseInt(localStorage.getItem('last_seed_num')) + 1;
            cloned_item.attr('data-seed', 'seed_' + last_seed_num);
            cloned_item.attr('data-price_id', last_seed_num);
            cloned_item.attr('data-first_str', 0);
            cloned_item.find('.new-price-qty').attr('disabled', false).parent().show();
            localStorage.setItem('last_seed_num', last_seed_num);

			$(this).parent().parent().after( cloned_item );
		});


		setTimeout(function () {
			$(document).find('.prices-wrapper-design .one-type-select').each(function () {
				let select_value = $(this).find('option:selected').text();
				if ( 'General Admission' === select_value || 'VIP' === select_value ) {
					$(this).parent().parent().next().find('.new-price-name').addClass('optional').removeClass('err_red');
					$(this).parent().parent().next().hide();
				}
			});
		}, 1000);




	});
</script>

<input class="save-repeater" type="button" value="Save" style="display: none;"/>