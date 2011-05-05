jQuery(function() {
	jQuery().ajaxError(function(a, b, e) {
		throw e;
	});

	// our form submit and valiation
	var aform = $("#form-ladder-settings").validate({

		// make sure we show/hide both blocks
		errorContainer: "#errorblock-div1",

		ignore: ".ignore",

		// rules/messages are for the validation
		rules: {
			laddername: "required"
		},
		messages: {
			laddername: "Please enter the ladder name."
		}
	});

}); // end main jQuery function start

