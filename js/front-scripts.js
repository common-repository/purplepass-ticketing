jQuery(document).ready(function($){

	let current_widget_position = $(".tribe-events-single-event-description.tribe-events-content").remove();
	$(".tribe_events.type-tribe_events.status-publish.hentry").append( current_widget_position );

});