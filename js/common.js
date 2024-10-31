/**
 * Check if JSON
 */
function if_json(str) {
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
}

function removeUrlParam(key, sourceURL) {
	var rtn = sourceURL.split("?")[0],
		param,
		params_arr = [],
		queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
	if (queryString !== "") {
		params_arr = queryString.split("&");
		for (var i = params_arr.length - 1; i >= 0; i -= 1) {
			param = params_arr[i].split("=")[0];
			if (param === key) {
				params_arr.splice(i, 1);
			}
		}
		rtn = rtn + "?" + params_arr.join("&");
	}
	return rtn;
}

jQuery(document).ready(function ($) {

	if ($("#post_type").val() === 'tribe_venue') {
		$('#publish').on('click', validateAndStorePPVenueData);

		function validateAndStorePPVenueData(e, isValidated) {
			isValidated = isValidated || false;
			if (!isValidated) {
					e.preventDefault();

					// Proceed with ajax validation

					// We need to update post content from tinymce
					$('#content').val(tinymce.activeEditor.getContent());

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'pptec_wp_venue_form_validate_and_save',
							form_data: $('form#post').serialize()
						},
						dataType: 'json',
						beforeSend: function () {
							$('.err_red').removeClass('err_red');
						},
						success: function (json) {
							if (json.hasOwnProperty('errors')) {
								let msg = '';
								$.map(json.errors, function (error) {
									$(error.selector).addClass('err_red');
									msg += ('- ' + error.message + '\n');
								})

								alert(msg);
							} else if (json.hasOwnProperty('success')) {
								validateAndStorePPVenueData(e, true);
							}
						}
					});
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

		$(document).on('change', '#EventCountry', function () {
			$(this).find(' + .select2 .select2-selection').removeClass('err_red');
		})
		$(document).on('keyup', '#eventDetails input, #title', function () {
			$(this).removeClass('err_red');
		})
	}

	$(".wp-list-table .another-account-event .column-title .row-title").attr('title', 'This event was added from another account. You can edit it, but changes will not be pushed to Purplepass!')

	/**
	 * Prevent click on Fetch button twice
	 */
	if ( $(".prevent-fetching-twice").length !== 0 ) {
		$(".prevent-fetching-twice").hide();
	}


	/**
	 * Core for the Replace all function
	 */
	function escapeRegExp(string) {
		return string.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
	}


	/**
	 * Custom replace al function
	 */
	function replaceAll(str, find, replace) {
		return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
	}


	/**
	 * Add publish button correct text
	 */
	let publish_btn_text = $("#publishing-action #publish").val();
	setTimeout(function () {
		jQuery(".save-publish-btn-event a").text( publish_btn_text +" event" );
	}, 700);


	/**
	 * Add white logo on settings page
	 */
	var plugin_url = pp_js.plugin_url;
	var log_img = plugin_url + "img/pp-log.png";
	$('#pp_events_add_fields h2').append('<img class="pp_logo_white_right" src="' + log_img + '">');


	/**
	 * Set up selectize
	 */
	$('#select-start-time-selectized').css({
		'width' : '100px',
		'height' : '48px',
	});


	/**
	 * Choose auto delivery type
	 */
	let checked_cb = 0;
	$(".wrap-delivery-opptions input[type=checkbox]").each(function () {
		if ( $(this).is(':checked') ) {
			checked_cb = 1;
		}
	});
	if ( 0 === checked_cb ) {
		$('#print_at_home').click();
		$('#will_call').click();
		$(".hidden-deliv-types").prop("value", "{\"print_at_home\":true,\"will_call\":true,\"first_class\":false,\"priority\":false,\"express\":false}");
	}


	/**
	 * Change plugin Created By row in plugins list
	 */

	$('.wp-first-item').each(function () {
		let that = $(this).parent();
		if ( $(this).text() === 'Purplepass' ) {
			$(this).find('a').text('Settings');
		}
		let sett_item = $(this).remove();
		that.append(sett_item);
	});


	/**
	 * Sales start set up
	 */
	setTimeout(function () {
		var currentTime = new Date();
		var hours = currentTime.getHours();
		hours = Number(hours) - 1;
		var minutes = currentTime.getMinutes();
		if (minutes < 10) {
			minutes = "0" + minutes;
		}
		let old_val = $('.sales-start').val();
		if ( typeof old_val !== "undefined" ) {
			let split_val = old_val.split(':');
			let new_val = old_val + hours + ':' + minutes + ':00';
			new_val = new_val.replace(/\//gi, '-');
			if ( split_val.length === 1 ) {
				$('.sales-start').attr('value', new_val);
			}
		}
	}, 1000 );


	/**
	 * Saving widget settings data
	 */
	$('.save-ajax-widget-settings').on('click', function (e) {
		e.preventDefault();
		let widget_color = $('.selected-color-select').attr("data-selected_color");
		let widget_help_text = $("#add_custom_widget_text").val();
		let widget_width = $("#add_custom_widget_width").val();
		let enabled_cart = $("#pp_enabled_widget_cart").val();
		let cbx_replace = $('.replace_by_widgets').is(':checked');

		let all_data = {
			'action' : 'save_widget_settings',
			'widget_color' : widget_color,
			'widget_help_text' : widget_help_text,
			'widget_width' : widget_width,
			'enabled_cart' : enabled_cart,
			'cbx_replace' : cbx_replace,
			'nonce' : pp_js.nonce, // ajax nonce
		};
		$.ajax({
			url: ajaxurl,
			data: all_data,
			type: 'POST',
			success: function (data) {
				$('.ajax-widget-settings-result').html(data);
				setTimeout(function () {
					$('.ajax-widget-settings-result').text('');
				}, 5000);
			}
		});
	});


	/**
	 * Stats processing
	 */
	var ajax_stat_clicked = false;
	$('.get-stats-ajax').on('click', function (e) {
		e.preventDefault();

		if ( false === ajax_stat_clicked ) {
			$('.img-stat-preloader').show();
			getStatsAjax("stats");
			ajax_stat_clicked = true;
		}
	});


	/**
	 * Load More for Log File
	 */
	let load_page_qty = 0;
	$('.get-log-ajax').on('click', function (e) {
		$('.img-log-preloader').show();
		e.preventDefault();
		load_page_qty++;

		let all_data = {
			'action' : 'get_log_ajax',
			'load_page_qty' : load_page_qty,
			'nonce' : pp_js.nonce, // ajax nonce
		};
		$.ajax({
			url: ajaxurl,
			data: all_data,
			type: 'POST',
			success: function (data) {
				if ( if_json( data ) ) {
					data = JSON.parse( data );
					$('.img-log-preloader').hide();
					$(".insert-append").append( data.logs );
					if ( data.is_finish ) {
						$('.get-log-ajax').hide();
					}
				}
			}
		});
	});


	/**
	 * Load More for stats page
	 */
	let load_stats_page_qty = 0;
	$('.get-stats-ajax-loadmore').on('click', function (e) {
		$('.img-stats-preloader').show();
		e.preventDefault();
		load_stats_page_qty++;

		let all_data = {
			'action' : 'get_stats_ajax_loadmore',
			'load_stats_page_qty' : load_stats_page_qty,
			'nonce' : pp_js.nonce, // ajax nonce
		};
		$.ajax({
			url: ajaxurl,
			data: all_data,
			type: 'POST',
			success: function (data) {
				if ( if_json( data ) ) {
					data = JSON.parse( data );
					$('.img-stats-preloader').hide();
					$(".insert-append").append( data.stats );
					if ( data.is_finish ) {
						$('.get-stats-ajax-loadmore').hide();
					}
				}
			}
		});
	});


	/**
	 * Clean counters to open fetch stats button and other
	 */
	function pptec_reset_events_fetching_progress(){
		$(document).find('.setting-fieldset .manual-fetch-events-from-pp').addClass('spinner-active');
		var all_data_clean = {
			'action' : 'pptec_reset_events_fetching_progress',
			'nonce' : pp_js.nonce, // ajax nonce
		};
		$.ajax({
			url: ajaxurl,
			data: all_data_clean,
			type: 'POST',
			success: function (data) {
				//
			},
		});
	}


	/**
	 * Sync button interval processing with reloading page and getting Events
	 */
	function sync_btn_processing(){
		$(document).find('.setting-fieldset .manual-fetch-events-from-pp').addClass('spinner-active');
		$(document).find('.setting-fieldset .green-cron').addClass('spinner-active');
		$(document).find('.setting-fieldset .img-fetch-preloader').addClass('spinner-active');

		let init_requests_counter = 1;
		var checking_response_id = setInterval( pptec_check_fetching_response, 2000 );

		function pptec_check_fetching_response(){
			let timenow = Date.now();
			let url = ajaxurl;// + '?doing_wp_cron=' + timenow;

			$.ajax({
				url: url,
				data: {
					'action' : 'pptec_get_events_fetching_progress',
					'nonce' : pp_js.nonce, // ajax nonce
					'init_requests_counter': init_requests_counter
				},
				type: 'POST',
				success: function (json) {
					if ( if_json( json ) ) {
						let events_fetching_progress = JSON.parse( json );

						if ( events_fetching_progress.clear_interval ) {
							clearInterval(checking_response_id);

							$(document).find('.setting-fieldset .manual-fetch-events-from-pp').removeClass('spinner-active');
							$(document).find('.setting-fieldset .green-cron').removeClass('spinner-active');
							$(document).find('.setting-fieldset .img-fetch-preloader').removeClass('spinner-active');

							$('.event-hid-data .time-fetching').html(events_fetching_progress.time);
						}

						$('.event-hid-data').attr('data-status', events_fetching_progress.status);
						$(document).find('.sync-messages').html('<span style="display: block;">' + events_fetching_progress.message + '</span>');

						if (events_fetching_progress.status === 'initialized') {
							init_requests_counter++;

							if (pp_js.is_alternate_wp_cron === "1") {
								$.get(pp_js.site_url+'/wp-cron.php?pptec_filter_ready_cronjobs=1');
							}
						}
					}
				},
			});
		}
	}

	/**
	 * Get events from PP data
	 */
	$(document).on('click', '.manual-fetch-events-from-pp', function (e) {
		e.preventDefault();

		$(document).find('.setting-fieldset .manual-fetch-events-from-pp').addClass('spinner-active');
		$(document).find('.setting-fieldset .green-cron').addClass('spinner-active');
		$(document).find('.setting-fieldset .img-fetch-preloader').addClass('spinner-active');

		$.ajax({
			url: ajaxurl,
			data: {
				'action' : 'get_events_from_pp',
				'nonce' : pp_js.nonce,
			},
			type: 'POST',
			success: function (json) {
				if ( if_json( json ) ) {
					let events_fetching_progress = JSON.parse( json );
					$(document).find('.sync-messages').html('<span style="display: block;">' + events_fetching_progress.message + '</span>');
					sync_btn_processing();
				}
			},
		});
	});

	/*
	 * Get stats from pp
	*/
	function getStatsAjax(from = "stats") {
		let all_data = {
			'action' : 'get_stats_ajax',
			'time_data' : new Date(),
			'nonce' : pp_js.nonce, // ajax nonce
		};
		$.ajax({
			url: ajaxurl,
			data: all_data,
			type: 'POST',
			success: function (data) {
				if ( from === "stats") window.location.reload();
			}
		});
	}


	/**
	 * Prices repeater processing
	 */
	let num_rows = $(document).find('.one-repeater-item').length;
	if ( num_rows >= 3 ) {
		$('.item-repeater-delete').show();
	} else {
		$('.item-repeater-delete').hide();
	}


	/**
	 * Prices repeater add row
	 */
	$('.add-repeater-row').on('click', function () {
		let num_rows = $(document).find('.one-repeater-item').length;
		if ( num_rows >= 2 ) {
			$('.item-repeater-delete').show();
		} else {
			$('.item-repeater-delete').hide();
		}
		$('.prices_options_valid').addClass('err_red');
		let one_row = $(document).find('.elem-for-clone').clone();
		one_row = one_row.show().removeClass('elem-for-clone').attr('data-row_num', num_rows);
		$('.repeater-wrapper').append(one_row);
		$('.save-repeater').show();
		$(document).find('.repeater-wrapper .one-repeater-item:last-child').find('.input-price-name').removeClass('err_red');
	});


	/**
	 * Prices repeater delete row
	 */
	$(document).on('click', '.item-repeater-delete', function () {
		let num_rows = 0;
		num_rows = $(document).find('.one-repeater-item').length;
		num_rows = num_rows - 2;
		if ( num_rows >= 2 ) {
			$(document).find('.item-repeater-delete').show();
		} else {
			$(document).find('.item-repeater-delete').hide();
		}
		// restrict double General Admission, VIP for 1 event
		let curr_selected = $(this).parent().parent().find('option:selected').text();
		$('.save-repeater').show();
		$(this).parent().parent().parent().parent().parent().remove();
		if ( $(document).find('.one-repeater-item').length < 2 ) {
			$('.save-repeater').hide();
		}
	});

	/**
	 * Prices repeater saving
	 */
	$(document).on('click', '.save-repeater', function () {
		window.onbeforeunload = null;

		let prices_data_json = $(document).find(".pp_prices_data").text()
		let prices_data = if_json(prices_data_json) ? JSON.parse(prices_data_json) : {};

		$(document).find('.wrap-price.full-price-table-design .ase-remove-wrapper .one-repeater-item').each(function (key, item) {
			let price_type = $(item).find('.one-type-select option:selected');
			let seed = $(item).attr('data-seed');

			let one_row = {
				'price'        : $(item).find('.new-price-price').val(),
				'type'         : price_type ? $(price_type).val() : 2,
				'name'         : $(item).find('.new-price-name').val(),
				'quantity'     : $(item).find('.new-price-qty').val(),
				'descr'        : $(item).find('.wrap-price-description input[type=text]').val(),
				'section_id'   : typeof $(item).attr('data-section_id') !== 'undefined' ? $(item).attr('data-section_id') : '',
				'row_color'    : typeof $(item).attr("data-color") !== 'undefined' ? $(item).attr("data-color") : '',
				'section_name' : typeof $(item).attr("data-section_name") !== 'undefined' ? $(item).attr("data-section_name") : ''
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

		$(document).find(".pp_prices_data").text(JSON.stringify(prices_data));
	});
	setTimeout(function () {
		let num_rows = 0;
		num_rows = $(document).find('.one-repeater-item').length;
		num_rows = num_rows - 1;
		if ( num_rows == 0 ) {
			$('.add-repeater-row').trigger('click');
		}
	}, 1500 );


	/**
	 * Check facebook URL
	 */
	$("#pp_fb_url").blur(function () {
		let all_data = {
			'action' : 'validate_facebook_url',
			'fb_page_url' : $(this).val(),
			'nonce' : pp_js.nonce, // ajax nonce
		};
		$.ajax({
			url: ajaxurl,
			data: all_data,
			type: 'POST',
			success: function (data) {
				alert( data );
			},
			error: function (data) {
				alert( data );
			},
		});
	});


	/**
	 * Prices rows processing fields and dependencies
	 */
	$(document).find('.new-price-name').prop('disabled', true).hide();
	$('.name-price-title').hide();
	// when change Price Type - add name field
	$(document).on('change', '.one-type-select', function () {
		let price_type = $(this).find('option:selected').text();
		let that = $(this);
		if ( 'Custom' === price_type ) {
			$(this).parent().parent().next().find('input[type=text]').prop('disabled', false).show();
			$(this).parent().parent().next().find('.name-price-title').show();
			$(this).parent().parent().next().find('.new-price-name').show().removeClass('err_red').removeClass('optional');
			$(this).parent().parent().next().find('.new-price-name').parent().show();

			// show all fields after Donation was hidden
			$(this).parent().parent().next().next().next().find('.new-price-qty').parent().show();
			$(this).parent().parent().next().next().find('.new-price-price').parent().show();
		}

		if ( 'General Admission' === price_type || 'VIP' === price_type ) {
			// show all fields after Donation was hidden
			$(this).parent().parent().parent().find('.new-price-qty').parent().show();
			$(this).parent().parent().parent().find('.new-price-qty').show();
			$(this).parent().parent().parent().find('.new-price-price').parent().show();
			$(this).parent().parent().parent().find('.new-price-price').show();
			$(this).parent().parent().parent().find('.name-price-title').parent().hide();
			$(this).parent().parent().parent().find('.new-price-name').prop('disabled', true).hide();
			$(this).parent().parent().parent().find('.new-price-qty').prop('disabled', false).show();

			let selected_val = [];
			$(document).find('#pp_events_add_fields').find('.one-repeater-item').filter(':visible').find('.one-type-select').each(function () {
				selected_val.push( $(this).find('option:selected').text() );
			});
			selected_val = selected_val.join();

			let GA_Exists = false;
			var count_ga = (selected_val.match(/General Admission/g) || []).length;
			if ( count_ga > 1 ) {
				GA_Exists = true;
			}

			let VIP_Exists = false;
			var count_vip = (selected_val.match(/VIP/g) || []).length;
			if ( count_vip > 1 ) {
				VIP_Exists = true;
			}

			if ( price_type === 'General Admission' && GA_Exists ) {
				alert('You can use General Admission type once only!');
				$(that).find('option:last-child').prop('selected', true);
			}
			if ( price_type === 'VIP' && VIP_Exists ) {
				alert('You can use VIP type once only!');
				$(that).find('option:last-child').prop('selected', true);
			}

		} else if ( 'Donations' === price_type ){

			$(this).parent().parent().next().find('.new-price-name').removeClass('err_red');
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

			$(this).parent().parent().next().find('.new-price-name').prop('disabled', false).show();
			$(this).parent().parent().next().next().find('.new-price-price').prop('disabled', false);
			$(this).parent().parent().next().next().next().find('.new-price-qty').prop('disabled', false);

			let price_name_val = $(this).parent().parent().next().find('.new-price-name').val();
			if ( price_name_val.length < 3 && ( 'Donations' !== price_type ) ) {
				// $(this).parent().parent().next().find('.new-price-name').addClass('err_red');
			} else {
				$(this).parent().parent().next().find('.new-price-name').removeClass('err_red');
			}
		}
	});


	/**
	 * Prices rows checking if price type is Custom
	 */
	function check_if_custom( that = false ) {
		if ( false === that ) {
			$(document).find('.one-type-select').each(function () {
				let select_value = $(this).find('option:selected').text();
				if ( select_value === 'Custom' ) {
					$(this).parent().parent().next().find('input[type=text]').prop('disabled', false).show().removeAttr('style');
					$(this).parent().parent().next().find('.name-price-title').show().removeAttr('style');
				} else {
					$(this).parent().parent().next().find('.name-price-title').hide();
					$(this).parent().parent().next().find('.new-price-name').prop('disabled', true).hide().removeClass('err_red');
				}
			});
		} else {
			let select_value = that.find('option:selected').text();
			if ( select_value !== 'Custom' ) {
				that.parent().parent().next().find('.name-price-title').hide();
				that.parent().parent().next().find('.new-price-name').prop('disabled', true).hide().removeClass('err_red');
			}
		}
	}
	check_if_custom();


	/**
	 * Prices row identifier will be added on page is loaded
	 */
	var row_counter = 0;
	$(document).find('.prices-wrapper-design .one-repeater-item').each(function () {
		$(this).attr('data-seed', "seed_"+row_counter);
		row_counter++;
	});

	// Store seed number of last price row
	setTimeout(function () {
		let last_seed_num = -1;
		let last_price_row = $(document).find('.prices-wrapper-design .one-repeater-item:last');
		if (last_price_row.length) {
			last_seed_num = last_price_row.attr('data-seed').split('_')[1];
		}
		localStorage.setItem('last_seed_num', last_seed_num);
	});


	/**
	 * Add price row
	 */
	$(document).find('.add-ticket-type').on('click', function (e) {
		e.preventDefault();
		let one_row = $('.wrap-current-price.for-clone').clone();
		one_row.removeClass('for-clone');
		one_row.addClass('one-repeater-item');

		let last_seed_num = parseInt(localStorage.getItem('last_seed_num')) + 1;
		one_row.attr('data-seed', "seed_"+last_seed_num);
		localStorage.setItem('last_seed_num', last_seed_num);

		one_row.removeAttr('style');
		one_row.find('input[type=text]').each(function () {
			$(this).val('').removeClass("err_red");
		});

		one_row.find('.one-type-select').closest('li').show();

		let price_type = one_row.find('.one-type-select').find('option:selected').val();
		if ( 2 == price_type ) {
			one_row.find('.one-type-select').parent().parent().next().find('input[type=text]').prop('disabled', false).show();
			one_row.find('.one-type-select').parent().parent().next().find('.name-price-title').show();
		} else {
			one_row.find('.one-type-select').parent().parent().next().find('input[type=text]').prop('disabled', true).hide().removeClass('err_red');
			one_row.find('.one-type-select').parent().parent().next().find('.name-price-title').hide();
		}

		// check if there is class optional - removing err_red - new-price-name
		one_row.find('input[type=text]').each(function () {
			if ( $(this).hasClass("optional") ) {
				$(this).removeClass("err_red");
			}
		});
		$(document).find('.insert-price-row-before').before(one_row);

		// check - if there is 1 row - then it will have GA price type
		if ( 1 === $(document).find('.prices-wrapper-design .one-repeater-item').length ) {
			$(document).find('.prices-wrapper-design .one-repeater-item').find('.new-price-name').parent().hide();
		}
		check_if_custom( $(this) );

		row_counter++;
	});


	/**
	 * Remove validation error on disabled input type text fields
	 */
	$(document).find('input[type=text]').each(function () {
		if ( $(this).prop('disabled') ) {
			$(this).removeClass('err_red');
		}
	});


	/**
	 * Show save button, when repeater was changed
	 */
	$(document).on('change', 'input[name=event-price], input[name=name-price]', function () {
		$(document).find('input[type=text]').each(function () {
			if ( $(this).prop('disabled') ) {
				$(this).removeClass('err_red');
			}
		});
	});


	/**
	 * Remove validation error on disabled select fields
	 */
	$(document).on('change', '.one-type-select', function () {
		$(document).find('input[type=text]').each(function () {
			if ( $(this).prop('disabled') ) {
				$(this).removeClass('err_red');
			}
		});
	});


	/**
	 * Date processing, date time start
	 */
	let start_date, start_time, end_date, end_time;
	$('.tribe-field-start_date, .tribe-field-start_time').on('change, blur', function () {
		start_date = $('.tribe-field-start_date').val();
		start_date = replaceAll( start_date, '/', '-' );

		var dateObj = new Date(start_date);

		var year = dateObj.getFullYear();
		var month = dateObj.getMonth();
		month = Number(month) + 1;
		if ( Number(month) < 10 ) {
			month = "0" + month;
		}
		var day = dateObj.getDate();
		if ( Number(day) < 10 ) {
			day = "0" + day;
		}

		var new_st_date = year + "-" + month + "-" + day;
		start_date = new_st_date;

		start_time = $('.tribe-field-start_time').val();
		start_time = start_time.replace('am', ' AM');
		start_time = start_time.replace('pm', ' PM');
		start_time = time_from_12_to_24( start_time );
		start_time = start_time +':00';

		let sep_time = start_time.split(':');
		let hrs = sep_time[0];
		let mns = sep_time[1];

		// let hrs_sales_stop = Number(hrs) + 1;
		let hrs_sales_stop = Number(hrs) + 1;
		let hrs_sales_start_new = Number(hrs) + 2;

		$('#select_start_time option').each(function () {
			if ( parseInt($(this).text()) == hrs_sales_start_new + ":" + mns) {
				$(this).prop('selected', true);
			}
		});

		$('#doors_open_hours option').each(function () {
			if ( parseInt($(this).text()) == hrs ) {
				$(this).prop('selected', true);
			}
		});
		$('#doors_open_min option').each(function () {
			if ( parseInt($(this).text()) == mns ) {
				$(this).prop('selected', true);
			}
		});

		$('.sales_start').val(start_date);
		$('.sale_stop_date').val(start_date);

		if ( $('.sale_start_date').val() ){
			$('.sale_start_date').removeClass('err_red');
		}

		if ( $('.sale_stop_date').val() ){
			$('.sale_stop_date').removeClass('err_red');
		}

		if ( Number(hrs_sales_stop) <= 9 ) {
			hrs_sales_stop = '0'+hrs_sales_stop;
		}
		$('#select-stop-time option').each(function () {
			if ( parseInt($(this).text()) == hrs_sales_stop + ":" + mns) {
				$(this).prop('selected', true);
			}
		});

		// @todo v2: refactoring
		/**
		 * Trigger Sales start filing field hidden
		 */
		let sales_start_date = $(".sale_start_date").val();
		let sale_start_time = $("#select-start-time option:selected").val();
		let full_date_1 = sales_start_date + " " + sale_start_time;
		$(".sales_start").val(full_date_1);


		/**
		 * Trigger Sales stop filing field hidden
		 */
		let sale_stop_date = $(".sale_stop_date").val();
		let sale_stop_time = $("#select-stop-time option:selected").val();
		let full_date_2 = sale_stop_date + " " + sale_stop_time;
		$(".sales_stop").val(full_date_2);


		/**
		 * Trigger Doors open filing field hidden
		 */
		let doors_open_hours = $("#doors_open_hours option:selected").val();
		let doors_open_min = $("#doors_open_min option:selected").val();
		let full_date_3 = doors_open_hours + ":" + doors_open_min + ":00";
		$(".doors_open").val(full_date_3);
	});


	/**
	 * Validation textarea
	 */
	$('#pp_events_add_fields').find('textarea').on('blur', function () {
		if ( $(this).val().length < 1 ) {
			if ( $(this).hasClass('optional') ) {
				// do nothing
			} else {
				$(this).addClass('err_red');
			}
		} else {
			$(this).removeClass('err_red');
		}
	});


	/**
	 * Convert time from 12 to 24 format
	 */
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


	/**
	 * Convert time from ms to time
	 */
	function msToTime(duration) {
		var milliseconds = parseInt((duration%1000)/100)
			, seconds = parseInt((duration/1000)%60)
			, minutes = parseInt((duration/(1000*60))%60)
			, hours = parseInt((duration/(1000*60*60) )%24);
		hours = Number(hours) + Number(3);
		hours = (hours < 10) ? "0" + hours : hours;
		minutes = (minutes < 10) ? "0" + minutes : minutes;
		seconds = (seconds < 10) ? "0" + seconds : seconds;

		return hours + ":" + minutes;
	}


	/**
	 * Add ticket type processing
	 */
	$(document).find('.add-ticket-type').on('click', function () {
		setTimeout(function () {
			let cont_it = 0;
			$(document).find('.wrap-price.full-price-table-design .wrap-current-price.one-repeater-item').each(function () {
				cont_it++;
			});
			if ( 1 === cont_it ) {
				$(document).find('.wrap-price.full-price-table-design .wrap-current-price.one-repeater-item:eq(0)')
					.find('.one-type-select option[value=0]').attr('selected', 'selected');
			}
		}, 2000);

		setTimeout(function () {
			check_double_ga_vip();
		}, 1000);

		// check if there is GA
		$(document).find('.one-repeater-item').each(function () {
			if ( $(this).is(":visible") ) {
				if ( 'General Admission' === $(this).find(".one-type-select option:selected").text() ) {
					if ( $(".wrap-current-price.for-clone").find(".one-type-select option:first-child").text() === 'General Admission' ) {
						$(".wrap-current-price.for-clone").find(".one-type-select option").each(function () {
							if ( $(this).text() === 'General Admission' ) {
								$(this).hide();
							}
						});
					}
				}
				if ( 'VIP' === $(this).find(".one-type-select option:selected").text() ) {
					if ( $(".wrap-current-price.for-clone").find(".one-type-select option:first-child").text() === 'VIP' ) {
						$(".wrap-current-price.for-clone").find(".one-type-select option").each(function () {
							if ( $(this).text() === 'VIP' ) {
								$(this).hide();
							}
						});
					}
				}
			}
		});
	});


	/**
	 * Checking GA and VIP prices types, them should be only 1 time in a list
	 */
	function check_double_ga_vip(){
		let flag_is_ga = 0;
		let flag_is_vip = 0;
		let count_flag = 0;
		let count_flag_vip = 0;
		$(document).find(".one-repeater-item").each(function () {
			if ( 'General Admission' === $(this).find("option:selected").text() ) {
				flag_is_ga = 1;
				count_flag++;
			}
			if ( 'VIP' === $(this).find("option:selected").text() ) {
				flag_is_vip = 1;
				count_flag_vip++;
			}
		});

		if ( count_flag === 0 ) {
			flag_is_ga = 0;
		}

		if ( count_flag_vip === 0 ) {
			flag_is_vip = 0;
		}

	}

	// Show venue name input. If the default value is selected
	const selectedValue = $('#saved_tribe_venue option:selected').val();
	if (selectedValue == -1) {
		setTimeout(function () {
			$(".saved-linked-post + .linked-post.venue").show();
		}, 500);
	}

	// Change timezone on ECP Location venue change
	if ($('#saved_tribe_venue').length) {
		$('#saved_tribe_venue').change(function () {
			let $this = $(this);

			// Show venue name if user selected to create a new one
			if (parseInt($this.val()) === -1 || $this.find('option:selected')[0].hasAttribute('data-select2-tag')) {
				$('.tribe_venue-name').closest('tr.venue').addClass('show-tr');
			} else {
				$('.tribe_venue-name').closest('tr.venue').removeClass('show-tr');
			}

			$.get(ajaxurl, {'action' : 'pptec_jx_get_wp_venue_data', 'wp_venue_id' : $(this).val()}, function (json) {
				$.each(json, function (key, item) {
					if (key === 'timezone_name') {
						$(item.selector).text(item.value);
					} else if ($(item.selector).length) {
						$(item.selector).val(item.value).trigger('change');
					}
				});
			}, 'json');
		})
	}

	// Change timezone on `Postal Code` field change
	setTimeout(function () {
		// We need to wait a bit until ECP refreshes its fields
		if ($('#EventZip').length) {
			$('#EventZip').keyup(_.debounce(function () {
				$.get(ajaxurl, {'action' : 'pptec_jx_get_timezone_by_zip', 'zip' : $(this).val()}, function (tz_id) {
					$('[name="pptec_timezone_id"]').val(tz_id);
				});
			}, 500));
		}
	}, 2000);

	/**
	 * Stats button using ajax for single event
	 */
	$(".stats-btn-single").on("click", function(e) {
		e.preventDefault();
		var event_id = $(this).attr("data-event_id");
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				'action' : 'single_event_stats',
				'nonce' : pp_js.nonce, // ajax nonce
			},
			success: function (access_token) {
				PP.window({
					token: access_token,
					params: {
						view: "stats",
						event_id: event_id,
					}
				}, {mask: '00000060'});
			}
		});
	});


	/**
	 * When page is loaded - processing fields
	 */
	setTimeout(function () {
		$(document).find('.wrap-price.full-price-table-design .one-type-select').each(function () {
			let select_value = $(this).find('option:selected').val();

			if ( 2 == select_value ) {

				$(this).parent().parent().next().find('input[type=text]').prop('disabled', false).show().removeAttr('style');
				$(this).parent().parent().next().find('.name-price-title').show().removeAttr('style');
				$(this).parent().parent().next().find('.new-price-name').show();
			}

			if ( 4 == select_value ) {
				// $(this).parent().parent().next().find('.new-price-name').addClass('err_red');
				$(this).parent().parent().next().find('.new-price-name').removeAttr('style').removeAttr('disabled').show();
				$(this).parent().parent().next().find('.name-price-title').removeAttr('style').show();
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
			}

			if ( 1 == select_value || 0 == select_value ) {
				$(this).parent().parent().parent().find('.new-price-name').removeClass('err_red');
				$(this).parent().parent().parent().find('.new-price-name').parent().hide();
			}
		});

		if ( $("#as_seating").is(':checked') ) {
			$(document).find('.ase-remove-wrapper .wrap-current-price.one-repeater-item').each(function () {
				let data_first_str = $(this).attr('data-first_str');
				if ( '1' == data_first_str ) {
					$(this).attr('data-hidden_qty', '1');
					$(this).find('.new-price-qty').prop('disabled', true);
					$(this).find('.new-price-qty').removeClass('err_red');
					$(this).find('.new-price-qty').parent().hide();
				}
				if ( 'no' == data_first_str ) {
					$(this).find('.add-new-price-assigned').hide();
				}
			});
		}



	}, 800);


	/**
	 * Trigger category on page is load
	 */
	let selected_opt = $("#pp_categories_choosen option:selected").text();
	$("#pp_categories_choosen option").each(function () {
		if ( $(this).text() === 'Please select category' ) {
			$(this).prop('selected', true);
		}
		if ( $(this).text() === selected_opt ) {
			$(this).prop('selected', true);
		}
	});


	/**
	 * Changing type
	 */
	setTimeout(function () {
		$(document).on('change', '.wrap-price.full-price-table-design .one-repeater-item .one-type-select', function () {
			let price_type = $(this).find('option:selected').val();
			if ( 4 == price_type ) {
				$(this).parent().parent().next().find('.new-price-name').removeClass('optional');
				$(this).parent().parent().next().next().find('.new-price-price').attr('title', 'This allows you to set a minimum amount that must be donated');
				$(this).parent().parent().next().next().find('.new-price-price').attr('placeholder', 'Min. Donation');
				$(this).parent().parent().next().next().find('.title-strong').text('Min. Donation');

				$(this).parent().parent().next().find('.new-price-name').removeAttr('style').removeAttr('disabled').show();
				$(this).parent().parent().next().find('.name-price-title').removeAttr('style').show();
			} else {
				$(this).parent().parent().next().next().find('.new-price-price').removeAttr('title');
				$(this).parent().parent().next().next().find('.new-price-price').attr('placeholder', 'Price');
				$(this).parent().parent().next().next().find('.title-strong').html('Price <span class="red-ast">*</span>');
			}
		});
	}, 2000);


	/**
	 * Fetching events from purplepass
	 */
	if ( '?page=purplepass&start_load_events=1' === window.document.location.search ) {
		/*let is_process_working = $('.event-hid-data').attr('data-processed');

		if ( 0 === parseInt( is_process_working ) ) {
			if ( $('.login-linked-account-section .green-true').length > 0 && '(Account linked)' === $('.login-linked-account-section .green-true').text() ) {
				setTimeout(function () {
					var answer = confirm("You have successfully linked your account to Purplepass. Do you want to fetch your events from Purplepass now?");
					if (answer === true) {
						$("html, body").animate({ scrollTop: $(document).height() }, 1000);
						$('.manual-fetch-events-from-pp').trigger('click').addClass('spinner-active');
					}

					let page_url = removeUrlParam('start_load_events', window.location.href);
					let page_title = document.title;

					// Replace current state in browser
					// We do not want to ask user again if he wants to run autofetch
					if (history.replaceState) {
						window.history.replaceState("object or string", page_title, page_url);
					} else {
						window.location.href = page_url;
					}
				}, 1000);
			}
		}*/

		if ( $('.login-linked-account-section .green-true').length > 0 && '(Account linked)' === $('.login-linked-account-section .green-true').text() ) {
			setTimeout(function () {
				var answer = confirm("You have successfully linked your account to Purplepass. Do you want to fetch your events from Purplepass now?");
				if (answer === true) {
					$("html, body").animate({ scrollTop: $(document).height() }, 1000);
					$('.manual-fetch-events-from-pp').trigger('click').addClass('spinner-active');
				}

				let page_url = removeUrlParam('start_load_events', window.location.href);
				let page_title = document.title;

				// Replace current state in browser
				// We do not want to ask user again if he wants to run autofetch
				if (history.replaceState) {
					window.history.replaceState("object or string", page_title, page_url);
				} else {
					window.location.href = page_url;
				}
			}, 1000);
		}
	}

	// Check syncing process
	let events_status = $(document).find('.event-hid-data').attr('data-status');
	if ( events_status && 'finished' !== events_status ) {
		sync_btn_processing();
	}

	/**
	 * If there were issues on receiving token process, inform user
	 */
	if ( '?page=purplepass&token_errors=1' === window.document.location.search ) {
		setTimeout(function () {
			alert("There were issues with connecting the account, please, try again later. A detailed description of the error is in the plugin logs section.");
		}, 1000);
	}


	/**
	 * If there no any Venues in Event plugin - and there is Venue address field - add it to ECP
	 */
	if ( $('.tribe-linked-type-venue-address') ) {
		$(document).on('blur', '#normal-sortables #tribe_events_event_details #event_tribe_venue .tribe-linked-type-venue-address input', function () {
			let address = $(this).val();
			$(document).find('#pp_address').text( address );
		});
	}

	/**
	 * If there no City in ECP - end it editing on Event plugin - it wil be passed to Purplepass
	 */
	if ( $('.tribe-linked-type-venue-city') ) {
		$(document).on('blur', '#normal-sortables #tribe_events_event_details #event_tribe_venue .tribe-linked-type-venue-city input', function () {
			let city = $(this).val();
			$(document).find('#pp_city').val( city );
		});
	}

	/**
	 * If there no Zip in ECP - end it editing on Event plugin - it wil be passed to Purplepass
	 */
	if ( $('.tribe-linked-type-venue-zip') ) {
		$(document).on('blur', '#normal-sortables #tribe_events_event_details #event_tribe_venue .tribe-linked-type-venue-zip input', function () {
			let zip = $(this).val();
			$(document).find('#pp_zip').val( zip );
		});
	}

	/**
	 * If there no Country in ECP - end it editing on Event plugin - it wil be passed to Purplepass
	 */
	if ( $('.tribe-linked-type-venue-country') ) {
		$(document).on('change', '#normal-sortables #tribe_events_event_details #event_tribe_venue .tribe-linked-type-venue-country select', function () {
			let country = $(this).find('option:selected').val();
			$('#pp_country_choosen option').each(function () {
				if ( $(this).text() === country ) {
					$(this).prop('selected', true);
				}
			});
		});
	}

	/**
	 * If there no State in ECP - end it editing on Event plugin - it wil be passed to Purplepass
	 */
	if ( $('.tribe-linked-type-venue-state-province') ) {
		$(document).on('change', '#normal-sortables #tribe_events_event_details #event_tribe_venue .tribe-linked-type-venue-state-province select', function () {
			let state = $(this).find('option:selected').text();
			$('#pp_state_choosen option').each(function () {
				if ( $(this).text() === state ) {
					$(this).prop('selected', true);
				}
			});
		});
	}


	/**
	 * State choose on page is loaded
	 */
	setTimeout(function () {
		let val_stat = $(".hidden-state-choosen").val();
		$("#pp_state_choosen option").each(function() {
			if ( $(this).val() === val_stat ) {
				$(this).prop('selected', true);
			}
		});
	}, 3000 );


	/**
	 * Unlink account
	 */
	$('.unlink-account').on('click', function (e) {
		e.preventDefault();

		const isConfirmed = confirm("Are you sure you want to unlink your Purplepass account?  This will stop new events and stats from syncing to and from Purplepass.");

		if ( !isConfirmed ) return;

		let all_data = {
			'action' : 'pptec_unlink_account',
			'nonce' : pp_js.nonce, // ajax nonce
		};
		$.ajax({
			url: ajaxurl,
			data: all_data,
			type: 'POST',
			success: function (data) {
				let page_url = removeUrlParam('ppauth', window.location.href);
				window.location.href = page_url;
			}
		});
	});


	/**
	 * Question enable click processing
	 */
	let questions_data_1 = $(document).find(".pp_questions_hidden").attr('data-checked');
	if ( "1" === questions_data_1 || 1 === questions_data_1 || '' === questions_data_1 ) {
		$("#questions_yes").click();
		$(".hid_questions_block").prop("style", "margin-left: 45px; display:none;");
	} else {
		$("#questions_no").click();
		$(".hid_questions_block").prop("style", "margin-left: 45px; display:block;");
	}


	/**
	 * On click on email templates dropdown - get by ajax emails templates from Purplepass
	 */

	var get_email_clicked = false;
	$(document).on("click", "#pp_email_template_default_div", function(e){
		if ( false === get_email_clicked ) {
			$('.img-log-preloader.email-wr-spinner').show();
			$('#pp_email_template_default_div span').remove();
			let event_id = $("#post_ID").val();
			let all_data = {
				"action": "pptec_get_email_templates",
				'event_id': event_id,
				'selected_id': $('.pp_email_template_hidden').val()
			};

			$.ajax({
				url: ajaxurl,
				data: all_data,
				type: "POST",
				success: function (data) {
					if ( data ) {
						$(document).find("#pp_email_template").html(data);
						$('.img-log-preloader.email-wr-spinner').hide();
						$('#pp_email_template_default').remove();
						$('#pp_email_template_default_div').remove();

						$('#pp_email_template').show();

						let i = $('#pp_email_template').select2();
						i.select2('open');
					}
				}
			});
		} else {
			$('.img-log-preloader.email-wr-spinner').hide();
		}
		get_email_clicked = true;
	});


	/**
	 * On click on print at home dropdown - get by ajax print at home templates from Purplepass
	 */
	var get_pah_clicked = false;
	$(document).on("click", "#pp_pah_template_default_div", function(e){
		if ( false === get_pah_clicked ) {
			$('.img-log-preloader.pah-wr-spinner').show();
			let event_id = $("#post_ID").val();
			let all_data = {
				"action": "pptec_get_print_at_home_templates",
				'event_id': event_id,
				'selected_id': $('.pp_pah_template_hidden').val()
			};
			$('#pp_pah_template_default_div span').remove();
			$.ajax({
				url: ajaxurl,
				data: all_data,
				type: "POST",
				success: function (data) {
					if ( data ) {
						$(document).find("#pp_pah_template").html('');
						$(document).find("#pp_pah_template").append(data);
						$('.img-log-preloader.pah-wr-spinner').hide();
						$('#pp_pah_template_default_div').remove();
						$('#pp_pah_template_default').remove();

						$('#pp_pah_template').show();
						let pp_pah_template_select2 = $('#pp_pah_template').select2();
						pp_pah_template_select2.select2('open');
					}
				}
			});
		} else {
			$('.img-log-preloader.pah-wr-spinner').hide();
		}
		get_pah_clicked = true;
	});




});

/**
 * Coupons
 */
jQuery(document).ready(function($) {

	$(".coupon-btn").on("click", function(e) {
		e.preventDefault();

		let prices_arr = [];

		$(document).find(".ase-remove-wrapper .one-repeater-item").each(function() {
			let type_id = $(this).find(".one-type-select option:selected").val();
			if ( typeof type_id === "undefined" ) {
				type_id = 2;
			}

			// @todo v2: validate this - do not allow prices without name if it is required
			let price_name = $(this).find(".new-price-name").val();
			if ( !price_name ) {
				price_name = "Custom";
			}

			let seed = $(this).attr("data-seed");
			let price_id = $(this).attr("data-price_id");

			let prices_item = {
				id: price_id,
				seed: seed,
				type: type_id,
				name: price_name
			}

			prices_arr.push( prices_item );
		});

		let coupons_data_json = $(document).find(".pp_coupons_hidden_rows").text();

		// get token ajax
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
						edit: "coupons",
					},
					data: {
						coupons: if_json(coupons_data_json) ? JSON.parse( coupons_data_json ) : '',
						usable_prices: prices_arr
					},
					onSave: function(data){
						let dat = JSON.stringify(data.coupons);
						$(document).find(".pp_coupons_hidden_rows").text(dat);
					}
				});
			},
			error: function(xhr, textStatus, errorThrown){
                alert('request failed->'+textStatus);
            }
		});
	});
});


/**
 * Questions
 */
jQuery(document).ready(function($) {
	$(".hid_questions_block").hide();
	$(".questions-btn").on("click", function(e) {
		e.preventDefault();

		let prices_arr = [];

		$(document).find(".ase-remove-wrapper .one-repeater-item").each(function() {
			let type_id = $(this).find(".one-type-select option:selected").val();
			if ( typeof type_id === "undefined" ) {
				type_id = 2;
			}

			// @todo v2: validate this - do not allow prices without name if it is required
			let price_name = $(this).find(".new-price-name").val();
			if ( !price_name ) {
				price_name = "Custom";
			}

			let seed = $(this).attr("data-seed");
			let price_id = $(this).attr("data-price_id");

			let prices_item = {
				id: price_id,
				seed: seed,
				type: type_id,
				name: price_name
			}

			prices_arr.push( prices_item );
		});

		let questions_data_json = $(document).find(".pp_questions_hidden_rows").text();

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
						edit: "questions",
					},
					data: {
						questions: if_json(questions_data_json) ? JSON.parse( questions_data_json ) : '',
						usable_prices: prices_arr
					},
					onSave: function(data){
						let dat = JSON.stringify(data.questions);
						$(document).find(".pp_questions_hidden").text("1");
						$(document).find(".pp_questions_hidden_rows").text(dat);
					}
				});
			}
		});
	});


});