<script>
	jQuery(document).ready(function ($) { // document ready
        // @todo v2: refactor whole validation
		/**
		 * on submit - checking all required fields
		 */
        $('#publish').on('click', validateAndStorePPEventData);

		function validateAndStorePPEventData(e, isValidated) {
		    isValidated = isValidated || false;

            if ( $(".switch-btn").hasClass("switch-on")) {
                if (!isValidated) {
                    e.preventDefault();

                    $('.img-pp-post-preloader').addClass('show');

                    // JS validation before ajax
                    let errors = check_required_fields_activate_deactivate_publish();

                    if (errors.length) {
						let message = "";
						errors.forEach(function(error) {
							message += error + "\n";
						});
                        $('.img-pp-post-preloader').removeClass('show');

						alert(message);
                    } else {
                        // Proceed with ajax validation

                        // We need to update post content from tinymce
                        $('#content').val(tinymce.activeEditor.getContent());

                        // We also need to update Venue Location name
                        if ($('#saved_tribe_venue').length && !$('[name="venue[Venue][]"]').val().length) {
                            $('[name="venue[Venue][]"]').val($('#saved_tribe_venue option:selected').text().trim());
                        }

                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pptec_wp_event_form_validate_and_save',
                                form_data: $('form#post').serialize()
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                $('.err_red').removeClass('err_red');
                            },
                            success: function (json) {
                                if (json.hasOwnProperty('errors')) {
                                    $('.img-pp-post-preloader').removeClass('show');

                                    let msg = '';
                                    $.map(json.errors, function (error) {
                                        if (error.hasOwnProperty('same_startson')) {
                                            let skip_same_startson = confirm(error.message);
                                            if (skip_same_startson) {
                                                $('#post').append('<input type="hidden" name="pptec_skip_same_startson" value="1" />');
                                                validateAndStorePPEventData(e);
                                            }
                                            return;
                                        } else {
                                            $(error.selector).addClass('err_red');
                                            msg += ('- ' + error.message + '\n');
                                        }
                                    })

                                    if (msg) {
                                        alert(msg);
                                    }
                                } else if (json.hasOwnProperty('success')) {
                                    if (json.hasOwnProperty('wp_venue')) {
                                        let $venue_select = $('[name="venue[VenueID][]"');
                                        $venue_select.find('option:selected').removeProp('selected');
                                        $venue_select.append('<option value="' + json.wp_venue.wp_venue_id + '" selected="selected">' + json.wp_venue.name + '</option>');
                                    }

                                    validateAndStorePPEventData(e, true);
                                }
                            }
                        });
                    }
                } else {
                    $(window).off('beforeunload');

                    // Set status to published
                    let $post_status = $('#post_status');
                    let $original_post_status = $('#original_post_status');

                    if (('draft' === $original_post_status.val() || 'auto-draft' === $original_post_status.val()) && 'draft' === $post_status.val()) {
                        $post_status.append('<option value="publish">Published</option>');
                        $('option[value="publish"]', $post_status).prop('selected', true);
                    }

                    $('form#post').unbind('submit').submit();
                }

            }
        }

		// hide most use tab for tags
		$('.hide-if-no-js').hide();


		/**
		 * For Countries
		 *
		 * on page load - check, if checkbox is checked, if Yes - then disable all other
		 */
		let if_1_checked_country = false;
		$('#tribe_countrychecklist li').each(function () {
			if ( $(this).find('input[type=checkbox]').is(':checked') ) {
				if_1_checked_country = true;
			} else {
				$(this).find('input[type=checkbox]').attr('disabled', true);
			}
		});

		if ( if_1_checked_country ) {
			$('#tribe_countrydiv').removeClass('err_red');
			let usa_cb = $('#tribe_country-266').remove();
			let canada_cb = $('#tribe_country-273').remove();
			$('#tribe_countrychecklist').prepend('<hr>');
			$('#tribe_countrychecklist').prepend(canada_cb);
			$('#tribe_countrychecklist').prepend(usa_cb);
		} else {

			$('#tribe_country-266').find('input').attr('checked', true); // USA
			let usa_cb = $('#tribe_country-266').remove();
			let canada_cb = $('#tribe_country-273').remove();
			$('#tribe_countrychecklist').prepend('<hr>');
			$('#tribe_countrychecklist').prepend(canada_cb);
			$('#tribe_countrychecklist').prepend(usa_cb);

			$('#tribe_countrychecklist li').each(function () {
				$(this).find('input[type=checkbox]').attr('disabled', false);
			});
		}

		// on click on checked checkbox
		$('#tribe_countrychecklist li input[type=checkbox]').on('change', function () {
			if (  $(this).is(':checked')  ) {
				$('#tribe_countrychecklist li').each(function () {
					$(this).find('input[type=checkbox]').attr('disabled', true);
				});
				$(this).attr('disabled', false);
				$('#tribe_countrydiv').removeClass('err_red');
			} else {
				$('#tribe_countrychecklist li').each(function () {
					$(this).find('input[type=checkbox]').attr('disabled', false);
				});
				$('#tribe_countrydiv').addClass('err_red');
			}

		});


		/**
		 * For States
		 *
		 * on page load - check, if checkbox is checked, if Yes - then disable all other
		 */
		let if_1_checked_state = false;
		$('#tribe_statechecklist li').each(function () {
			if ( $(this).find('input[type=checkbox]').is(':checked') ) {
				if_1_checked_state = true;
			} else {
				$(this).find('input[type=checkbox]').attr('disabled', true);
			}
		});

		if ( if_1_checked_state ) {
			$('#tribe_statediv').removeClass('err_red');
		} else {
			$('#tribe_statediv').addClass('err_red');
			$('#tribe_statechecklist li').each(function () {
				$(this).find('input[type=checkbox]').attr('disabled', false);
			});
		}

		// on click on checked checkbox
		$('#tribe_statechecklist li input[type=checkbox]').on('change', function () {
			if (  $(this).is(':checked')  ) {
				$('#tribe_statechecklist li').each(function () {
					$(this).find('input[type=checkbox]').attr('disabled', true);
				});
				$(this).attr('disabled', false);
				$('#tribe_statediv').removeClass('err_red');
			} else {
				$('#tribe_statechecklist li').each(function () {
					$(this).find('input[type=checkbox]').attr('disabled', false);
				});
				$('#tribe_statediv').addClass('err_red');
			}

		});


		/**
		 * checking delivery types
		 */
		let delivery_type = false;
		$('.cust_del').each(function () {
			if ($(this).is(':checked')) {
				delivery_type = true;
			}
		});

		if ( delivery_type ) {
			$('.ticket_options_valid').removeClass('err_red');
		} else {
			$('.ticket_options_valid').addClass('err_red');
		}


		/**
		 * checking prices
		 */
		let repeater_data = [];
		$(document).find('.one-repeater-item').each(function () {
			let one_row = [];
			one_row.push($(this).find('input[name=event-price]').val());
			one_row.push($(this).find('.one-type-select option:selected').text());
			repeater_data.push(one_row);
		});
		repeater_data.shift();
		if ( repeater_data.length === 0 ) {
			$('.prices_options_valid').addClass('err_red');
		} else {
			$('.prices_options_valid').removeClass('err_red');
		}

		/**
		 * Get data from main fields
		 *
		 * on page load - check, if data is not empty
		 */
		function check_required_fields_activate_deactivate_publish() {
		    let errors = [];

			// validation
            let prices_validation_failed = false;
			$('.full-price-table-design .one-repeater-item .new-price-price').filter(':visible').each(function () {
				if ( $(this).val() === '' ) {
					let price_type_id = $(this).closest('.one-repeater-item').find('.one-type-select option:selected').val();
					// if Price item type != Donations
					if ( price_type_id != 4 ) {
						$(this).addClass('err_red');
					}
				} else {
					$(this).removeClass('err_red');
				}
			});

            $('.full-price-table-design .one-repeater-item .new-price-name').filter(':visible').each(function () {
                if ( $(this).val() === '' ) {
                    $(this).addClass('err_red');
                } else {
                    $(this).removeClass('err_red');
                }
            });

            if ($('.full-price-table-design .err_red').length) {
                errors.push('Not all required fields for ticket prices are set');
            }

			if ( $('.prices-wrapper-design #pp_capacity').val() === '' ) {
				$('.prices-wrapper-design #pp_capacity').addClass('err_red');
                errors.push('Incorrect capacity');
            } else {
				$('.prices-wrapper-design #pp_capacity').removeClass('err_red');
			}

			if ( $(".as_seating").is(':checked') ) {
				let map_selected = $( "#pp_venue_map_select option:selected" ).text();
				if ( map_selected === 'Select seating chart' ) {
					$( "#pp_venue_map_select" ).addClass( 'err_red' );
                    errors.push('You must select seating chart');
                } else {
					$( "#pp_venue_map_select" ).removeClass( 'err_red' );
				}
			} else {
				$( "#pp_venue_map_select" ).removeClass( 'err_red' );
			}

			// delivery types
			let delivery_type = false;
			$('.cust_del').each(function () {
				if ($(this).is(':checked')) {
					delivery_type = true;
				}
			});

			if ( 'Please select category' === $("#pp_categories_choosen option:selected").val() ) {
				$("#pp_categories_choosen").addClass("err_red");
                errors.push('You must select event category');
            }

			// repeater
			let repeater_data = [];
			$(document).find('.one-repeater-item').each(function () {
				let one_row = [];
				one_row.push($(this).find('input[name=event-price]').val());
				one_row.push($(this).find('.one-type-select option:selected').text());
				repeater_data.push(one_row);
			});
			repeater_data.shift();

            return errors;
		}

		jQuery(document).on('change', '.one-type-select', function () {
			let price_type_id = jQuery(this).find('option:selected').val();
			if ( price_type_id.length > 0 ) {
				jQuery(this).removeClass('err_red');
			} else {
				//jQuery(this).addClass('err_red');
			}

			if ( price_type_id == 0 || price_type_id == 1 ) {
				$(this).parent().next().find('.new-price-name').removeClass('err_red');
			} else {
				let name_val = $(this).parent().next().find('.new-price-name').val();
				if ( typeof name_val !== 'undefined' && name_val.length > 0 ) {
					$(this).parent().next().find('.new-price-name').removeClass('err_red');
				}
			}
			jQuery('.save-repeater').trigger('click');
		});


		/**
		 * Validation tex fields
		 */
		function validate_text_input_fields(that){
			let val_price_data = $(that).val();
			if ( val_price_data ) {
				$(that).removeClass('err_red');
			} else {
				if ( $(that).hasClass('optional') ) {

				} else {
					$(that).addClass('err_red');
				}
			}
			// check if there is err_red classes
			let is_err = $(document).find('.repeater-wrapper').find('.err_red').length;
			if ( 2 === is_err ) {
				$('.prices_options_valid').removeClass('err_red')
			}
			// $('.save-repeater').trigger('click');
		}

		jQuery(document).on('blur, keyup', '.new-price-name', function () {
			validate_text_input_fields($(this));
		});
		jQuery(document).on('blur, keyup', '.new-price-price', function () {
			let price_type_id = $(this).closest('.one-repeater-item').find('.one-type-select option:selected').val();
			// if Price item type != Donations
			if ( price_type_id != 4 ) {
				validate_text_input_fields($(this));
			}
		});
		jQuery(document).on('blur, keyup', '.new-price-qty', function () {
			validate_text_input_fields($(this));
		});
		jQuery(document).on('blur, keyup', '.new-price-desc', function () {
			validate_text_input_fields($(this));
		});


		jQuery(document).on('click', '.item-repeater-delete, .delete-price', function () {
		    let seed = $(this).closest('.one-repeater-item').attr('data-seed');
		    new Promise(function (resolve) {
                let prices_data = JSON.parse($(document).find(".pp_prices_data").text());
                if (typeof prices_data[seed] !== "undefined") {
                    delete prices_data[seed];
                }
                $(document).find(".pp_prices_data").text(JSON.stringify(prices_data));
                resolve(true);
            }).then(function (result) {
                jQuery('.save-repeater').trigger('click');
            });
        });


		// jQuery(document).on('blur', '.new-price-desc', function () {
		// 	jQuery('.save-repeater').trigger('click');
		// });
		// jQuery(document).on('mouseout', '.wrap-price.full-price-table-design .one-repeater-item .new-price-desc', function () {
		// 	setTimeout(function () {
		// 		jQuery('.save-repeater').trigger('click');
		// 	}, 500);
		// });

		jQuery(document).on('blur', '.wrap-price.full-price-table-design .one-repeater-item .new-price-desc, .wrap-price.full-price-table-design .one-repeater-item .new-price-price, .wrap-price.full-price-table-design .one-repeater-item .new-price-name, .wrap-price.full-price-table-design .one-repeater-item .new-price-qty', function () {
            jQuery('.save-repeater').trigger('click');
		});
		jQuery(document).on('click', '.wrap-price.full-price-table-design .one-repeater-item .cust-btn-new-design', function () {
            jQuery('.save-repeater').trigger('click');
		});

	}); // document ready

	jQuery(document).ready(function ($) {
		$(document).find('.doors-open, .sales-start, .sales-stop, #pp_city, #pp_zip, #pp_pass, .name-qty, #excerpt').on('change, blur', function () {
			let this_text = $(this).val();
			if ( this_text.length < 1 ) {
				if ( $(this).hasClass('optional') ) {
				} else {
					$(this).addClass('err_red');
				}
			} else {
				$(this).removeClass('err_red');
			}
		});


		/**
		 * Chcking if string is JSON
		 */
		function if_json_str(str) {
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		}

		/**
		 * Add address from the Venue location, when page is loaded
		 */
		/*function set_venue_data_to_event() {
			let current_venue_id = $(document).find("#saved_tribe_venue").val();
			let id2 = $(document).find("#saved_tribe_venue option:selected").val();

			if ( current_venue_id && id2 ) {
				// checkbox Replace by pp widgets
				let all_data = {
					'action' : 'get_venue_data',
					'current_venue_id' : current_venue_id,
				};
				$.ajax({
					url: ajaxurl,
					data: all_data,
					type: 'POST',
					success: function (data) {

						if( if_json_str(data) ) {
							let ready_data = JSON.parse(data);

							if ( ready_data.address && ready_data.address.length > 2 ) {
								$("#pp_address").text( ready_data.address ).removeClass('err_red');
							} else {
								//$("#pp_address").addClass("err_red");
							}
							if ( ready_data.city && ready_data.city.length > 2 ) {
								$("#pp_city").val( ready_data.city ).removeClass('err_red');
							} else {
								//$("#pp_city").addClass("err_red");
							}
							if ( ready_data.zip ) {
								$("#pp_zip").val( ready_data.zip ).removeClass('err_red');
								$('#pp_zip').trigger('blur');
							} else {
								//$("#pp_zip").addClass("err_red");
							}
							if ( ready_data.state ) {
								$("#pp_state_choosen option").each(function () {
									if ( $(this).text() === ready_data.state ) {
										$(this).prop('selected', true);
									}
								});
								$("#pp_state_choosen").removeClass("err_red");
							} else {
								//$("#pp_state_choosen").addClass("err_red");
							}

							if ( ready_data.country ) {
								$("#pp_country_choosen option").each(function () {
									if ( $(this).text() === ready_data.country ) {
										$(this).prop('selected', true);
									}
								});
								$("#pp_country_choosen").removeClass("err_red");
							} else {
								//$("#pp_country_choosen").addClass("err_red");
							}
						}
					}
				});
			}
		}
		set_venue_data_to_event();

		setTimeout(function () {
			// let container = document.getElementById ("select2-chosen-2");
			let container = document.getElementById ("select2-saved_tribe_venue-container");
			if ( container ) {
				if (container.addEventListener) {
					container.addEventListener('DOMSubtreeModified', OnSubtreeModified, false);
				}
			}
			function OnSubtreeModified () {
				set_venue_data_to_event();
			}
		}, 500);*/
	});
</script>