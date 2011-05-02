/*----------------------------------------------------------------------------\
|                               Tab Pane                                      |
|-----------------------------------------------------------------------------|
*/

/*
$(function() {
	$( "#tabs" ).tabs();
});
*/

$(function() {
	$( "#tabs" ).tabs({
		cookie: {
			// store cookie for a day, without, it would be a session cookie
			expires: 1
		}
	});
});