jQuery(function() {
	
	$.fn.themeswitcher && $('<div/>').css({
		position: "absolute",
		left: 10,
		top: 10
	}).appendTo(document.body).themeswitcher();
	
	
	// onclick action for our button
	var abutton = $('#sign-up').click(function() {
	    $('#my-modal-form').dialog('open');
	});
	
	// this sets up a hover effect for all buttons
    var abuttonglow = $(".ui-button:not(.ui-state-disabled)")
	.hover(
		function() {
		    $(this).addClass("ui-state-hover");
		},
		function() {
		    $(this).removeClass("ui-state-hover");
		}
	).mousedown(function() {
	    $(this).addClass("ui-state-active");
	})
	.mouseup(function() {
	    $(this).removeClass("ui-state-active");
	});	
	
}); // end main jQuery function start