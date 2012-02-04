<?php
/**
*EventCreate.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

$text .= '
<script type="text/javascript" src="./js/event.js"></script>
';

$event = new Event();

if ((!isset($_POST['createevent']))||(!check_class($pref['eb_events_create_class'])))
{
	$text .= '<br />'.EB_EVENTC_L2.'<br />';
}
else
{
	$text .= $event->displayEventSettingsForm(true);
}

$ns->tablerender(EB_EVENTC_L1, $text);
require_once(FOOTERF);
exit;
?>