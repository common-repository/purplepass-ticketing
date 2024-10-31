jQuery( function($) {
	const confirm_full_delete_text = 'Delete Event\n\nCONFIRMATION - THIS CANNOT BE UNDONE.\n\n' +
		'If you delete this event, all orders will be refunded. Are you sure you want to delete this event? \n\n' +
		'NOTE: Service fees are non-refundable as per the terms of service.';

	const confirm_error_text = "Are you sure you want to remove the event?";

	transferExcerpt();

	$('.edit-php a.submitdelete, .post-php a.submitdelete').click( function( event ) {
		// If user has not linked his PP account, we proceed with default behavior
		if (parseInt(pp_js.pp_user_id) !== 0) {
			const $btn = $(this);

			event.preventDefault();

			const answer = isTrashStatus() ? false : confirm(confirm_full_delete_text);
			const event_status = $("#original_post_status").val();

			if (event_status === 'canceled') {
				const title = $("#title").val();
				const message = "Unable to cancel " + title + ": event is already cancelled.";
				alert(message);
				return;
			}

			if (answer) {
				// Delete on PP
				const wp_event_id = eventId($(this).attr('href'));

				jxDeleteOnPP(wp_event_id).then(function (results) {
					if (results.hasOwnProperty('failed') && typeof results.failed[wp_event_id] != "undefined") {
						alert(results.failed[wp_event_id]);
					} else {
						window.location.href = $btn.attr('href');
					}
				});
			}

			if (isTrashStatus()) {
				const errorAnswer = confirm(confirm_error_text);
				if (errorAnswer) {
					window.location.href = $btn.attr('href');
				}
			}
		}
	});

	function transferExcerpt() {
		$("#postexcerpt h2 span").text("Short description");
		$("#postdivrich").append($("#postexcerpt"));
	}

	$("#doaction").on('click', function (event) {
		// If user has not linked his PP account, we proceed with default behavior
		if (parseInt(pp_js.pp_user_id) !== 0) {
			let $btn = $(this);
			if ($('#bulk-action-selector-top option:selected').val() === 'trash') {
				event.preventDefault();

				const answer = isTrashStatus() ? false : confirm(confirm_full_delete_text);
				if (answer) {
					jxDeleteOnPP().then(function (results) {
						let error_message = '';
						$('[name="post[]"]:checked').each(function () {
							let wp_event_id = parseInt($(this).val());

							if (results.hasOwnProperty('failed') && typeof results.failed[wp_event_id] != "undefined") {
								error_message += ('- ' + results.failed[wp_event_id] + '\n');

								// Uncheck failed events, so WP won't delete them
								$(this).prop('checked', false);
							}
						});

						if (error_message) {
							alert('Some events can not be cancelled:\n' + error_message + '\nThese events will not be cancelled on PurplePass.');
						}

						$btn.closest('form').submit();
					});
				}

				if (isTrashStatus()) {
					const errorAnswer = confirm(confirm_error_text);
					if (errorAnswer) {
						$btn.closest('form').submit();
					}
				}
			}
		}
	});



	function isTrashStatus() {
		const relativeUrl = window.location.search.split('?')[1];
		const searchParams = new URLSearchParams(relativeUrl);
		return searchParams.get("post_status") === 'trash';
	}

	function eventId(link) {
		const relativeUrl = link.split('?')[1];
		const searchParams = new URLSearchParams(relativeUrl);
		return searchParams.get("post");
	}

	function jxDeleteOnPP(wp_event_id) {
		wp_event_id = wp_event_id || false;

		return new Promise(function (resolve, reject) {
			let ids = [];

			if (wp_event_id) {
				ids.push(parseInt(wp_event_id));
			} else {
				$('[name="post[]"]:checked').each(function () {
					ids.push(parseInt($(this).val()));
				});
			}

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					'action': 'pptec_jx_delete_event',
					'ids': ids
				},
				dataType: 'json',
				success: function (results) {
					resolve(results);
				}
			});
		});
	}

});