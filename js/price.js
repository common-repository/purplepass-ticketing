jQuery(document).ready(function ($) {




	/**
	 * Delete price row delete-price
	 */
	$(document).on('click', '.delete-price', function (e) {
		e.preventDefault();

		check_existing_assigned_questions( $(this) );
		check_existing_assigned_coupones( $(this) );

		let removed_type = $(this).parent().parent().find(".one-type-select option:selected").text();
		if ( 'General Admission' === removed_type ) {
			return_back_ga();
		}
		if ( 'VIP' === removed_type ) {
			return_back_vip();
		}
		$(this).parent().parent().remove();

	});


	function return_back_ga() {
		//$(document).find(".wrap-current-price.for-clone").find(".one-type-select").prepend('<option value="0"></option>');
		let flag = 0;
		$(document).find(".wrap-current-price.for-clone").find(".one-type-select option").each(function () {
			if ( $(this).text() === 'General Admission' ) {
				flag = 1;
			}
		});
		if ( flag === 0 ) {
			$(document).find(".wrap-current-price.for-clone").find(".one-type-select option:nth-child(1)").after('<option value="0">General Admission</option>');
		}
	}

	function return_back_vip() {
		let flag = 0;
		$(document).find(".wrap-current-price.for-clone").find(".one-type-select option").each(function () {
			if ( $(this).text() === 'VIP' ) {
				flag = 1;
			}
		});
		if ( flag === 0 ) {
			$(document).find(".wrap-current-price.for-clone").find(".one-type-select option:nth-child(1)").after('<option value="1">VIP</option>');
		}

	}


	function if_json(str) {
		try {
			JSON.parse(str);
		} catch (e) {
			return false;
		}
		return true;
	}



	function check_existing_assigned_questions( that ){
		// check if there are questions, assigned to the deleted price
		let deleted_seed = that.parent().parent().attr('data-seed');
		let jsn = $(document).find(".pp_questions_hidden_rows").text();
		jsn = jsn.replace('"[{', '[{');
		jsn = jsn.replace('}]"', '}]');
		if ( if_json( jsn ) ) {
			let parsed_arr = JSON.parse(jsn);
			for (let key in parsed_arr) {
				if (parsed_arr[key]['seed'] === deleted_seed) {
					parsed_arr.splice(key, 1);
				}
			}
			$(document).find(".pp_questions_hidden_rows").text(JSON.stringify(parsed_arr));
		}

		// check if there are questions, assigned to the deleted price
		let jsn_q2 = $(document).find(".pp_questions_hidden").text();
		jsn_q2 = jsn_q2.replace('"[{', '[{');
		jsn_q2 = jsn_q2.replace('}]"', '}]');
		if ( if_json( jsn_q2 ) ) {
			let parsed_arr_2 = JSON.parse(jsn_q2);
			for (let key in parsed_arr_2) {
				if (parsed_arr_2[key]['price_seed'] === deleted_seed) {
					parsed_arr_2.splice(key, 1);
				}
			}
			$(document).find(".pp_questions_hidden").text(JSON.stringify(parsed_arr_2));
		}

	}

	function check_existing_assigned_coupones( that ){
		// check if there are coupons, assigned to the deleted price
		let deleted_seed = that.parent().parent().attr('data-seed');
		let jsn_coupons = $(document).find(".pp_coupons_hidden_rows").text();
		jsn_coupons = jsn_coupons.replace('"[{', '[{');
		jsn_coupons = jsn_coupons.replace('}]"', '}]');

		if ( if_json( jsn_coupons ) ) {
			let parsed_arr_coupons = JSON.parse(jsn_coupons);
			for (let key in parsed_arr_coupons) {
				if (parsed_arr_coupons[key]['seed'] == deleted_seed) {
					parsed_arr_coupons.splice(key, 1);
				}
			}
			$(document).find(".pp_coupons_hidden_rows").text(JSON.stringify(parsed_arr_coupons));
		}
	}



	/**
	 * Hide first price row delete button
	 */
	let counter = 0;
	$(document).find('.wrap-current-price').each(function () {
		if ( 1 === counter ) {
			$(this).find('.delete-price').hide();
		}

		counter++;
	});

});