jQuery( function($) {

	var confirm_text = 'Are you sure you want to delete venue(s)?';

	$('.edit-php a.submitdelete, .post-php a.submitdelete').click( function( event ) {
		var answer = confirm( confirm_text );
		if( !answer ) {
			event.preventDefault();
		}
	});

	$("#doaction").on('click', function (event) {
		if ( $('#bulk-action-selector-top option:selected').val() === 'trash' ) {
			var answer = confirm( confirm_text );
			if( !answer ) {
				event.preventDefault();
			}
		}
	});

});