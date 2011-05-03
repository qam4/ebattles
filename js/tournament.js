jQuery(function() {
	jQuery().ajaxError(function(a, b, e) {
		throw e;
	});

	// our form submit and valiation
	var aform = $("#form-tournament-settings").validate({

		// make sure we show/hide both blocks
		errorContainer: "#errorblock-div1",

		ignore: ".ignore",

		// rules/messages are for the validation
		rules: {
			tournamentname: "required",
			startdate: "required",
		},
		messages: {
			tournamentname: "Please enter the tournament name.",
			startdate: "Please enter the start date",
		},
	});

}); // end main jQuery function start

