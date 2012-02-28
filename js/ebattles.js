jQuery(function() {
	/*
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
	*/
	
	$(function() {
		$('#brackets').draggable();
		$('.jq-button').button();
		$('.tbox').addClass("ui-widget-content ui-corner-all");
	});
	

}); // end main jQuery function start