<?php
/**
*LadderCreate.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/ladder.php");
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

$text .= '
<script type="text/javascript" src="./js/ladder.js"></script>
';

$ladder = new Ladder();

if ((!isset($_POST['createladder']))||(!check_class($pref['eb_ladders_create_class'])))
{
	$text .= '<br />'.EB_LADDERC_L2.'<br />';
}
else
{
	$text .= $ladder->displayLadderSettingsForm();
}

$ns->tablerender(EB_LADDERC_L1, $text);
require_once(FOOTERF);
exit;
?>