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
			startdate: "required"
		},
		messages: {
			tournamentname: "Please enter the tournament name.",
			startdate: "Please enter the start date"
		}
	});

	// our form submit and valiation
	var aform = $("#form-signup").validate({

		// make sure we show/hide both blocks
		errorContainer: "#errorblock-div1, #errorblock-div2",

		// put all error messages in a UL
		errorLabelContainer: "#errorblock-div2 ul",

		// wrap all error messages in an LI tag
		wrapper: "li",

		ignore: ".ignore",

		// rules/messages are for the validation
		rules: {
			joinTournamentPassword: "required",
			charactername: "required",
			code: {
				required: true,
				digits: true
			}
		},
		messages: {
			joinTournamentPassword: "Please enter the tournament password.",
			charactername: "Please enter your BBNET character name.",
			code: {
				required: "Please enter your BBNET code.",
				digits: "Please enter a valid 3 digits BBNET code."
			}
		},
	});

	// our modal dialog setup
	var amodal = $("#modal-form-signup").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		width: 300,
		modal: true,
		buttons: {
			'Submit': function()
			{
				// submit the form
				$("#form-signup").submit();
			},
			Cancel: function()
			{
				// close the dialog, reset the form
				$(this).dialog('close');
				aform.resetForm();
			}
		}
	});

	// onclick action for our button
	var abutton = $('#jointournament').click(function() {
		$('#modal-form-signup').dialog('open');
	});

}); // end main jQuery function start

