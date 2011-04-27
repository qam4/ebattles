jQuery(function() {
	jQuery().ajaxError(function(a, b, e) {
		throw e;
	});

	// JDR: our form submit and valiation
	var aform = $("#form-signup-getid").validate({

		// JDR: make sure we show/hide both blocks
		errorContainer: "#errorblock-div1, #errorblock-div2",

		// JDR: put all error messages in a UL
		errorLabelContainer: "#errorblock-div2 ul",

		// JDR: wrap all error messages in an LI tag
		wrapper: "li",

		// JDR: rules/messages are for the validation
		rules: {
			charactername: "required",
			code: {
				required: true,
				digits: true
			}
		},
		messages: {
			charactername: "Please enter your BBNET character name.",
			code: {
				required: "Please enter your BBNET code.",
				code: "Please enter a valid code."
			}
		},

		// JDR: our form submit
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				// JDR: the return target block
				target: '#client-script-return-data',

				// JDR: what to do on form submit success
				success: function() {
					$('#modal-form-signup-test').dialog('close');
					successEvents('#client-script-return-msg');
				}
			});
		}
	});

	// JDR: our modal dialog setup
	var amodal = $("#modal-form-signup-test").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		width: 300,
		modal: true,
		buttons: {
			'Submit': function()
			{
				// JDR: submit the form
				$("#form-signup-getid").submit();
			},
			Cancel: function()
			{
				// JDR: close the dialog, reset the form
				$(this).dialog('close');
				aform.resetForm();
			}
		}
	});

	// JDR: onclick action for our button
	var abutton = $('#sign-up').click(function() {
		$('#modal-form-signup-test').dialog('open');
	});

}); // JDR: end main jQuery function start

function successEvents(msg) {

	// JDR: microseconds to show return message block
	var defaultmessagedisplay = 10000;

	// JDR: fade in our return message block
	$(msg).fadeIn('slow');

	// JDR: remove return message block
	setTimeout(function() { $(msg).fadeOut('slow'); }, defaultmessagedisplay);
};