<?php
/**
*TournamentCreate.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

$text .= '
<script type="text/javascript" src="./js/tournament.js"></script>
';

$tournament = new Tournament();

if ((!isset($_POST['createtournament']))||(!check_class($pref['eb_tournaments_create_class'])))
{
	$text .= '<br />'.EB_TOURNAMENTC_L2.'<br />';
}
else
{
	$text .= $tournament->displayTournamentSettingsForm();
}

$ns->tablerender(EB_TOURNAMENTC_L1, $text);
require_once(FOOTERF);
exit;
?>