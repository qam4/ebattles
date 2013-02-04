<?php
/**
* matchdelete.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/match.php");
require_once(e_PLUGIN."ebattles/include/event.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

global $sql;

$text = '';

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    if (!isset($_POST['deletematch']))
    {
        $text .= '<br />'.EB_MATCHDEL_L2.'<br />';
    }
    else
    {
		$event = new Event($event_id);
		$type = $event->getField('Type');
		switch($type)
		{
			case "One Player Ladder":
			case "Team Ladder":
			case "Clan Ladder":
			$event_type = 'Ladder';
			break;
			case "One Player Tournament":
			case "Clan Tournament":
			$event_type = 'Tournament';
			default:
		}
		if($event_type=='Tournament') $event->setField('FixturesEnable', TRUE);

        $match_id = $_POST['matchid'];
        $match = new Match($match_id);
        
		if($event->getField('FixturesEnable') == TRUE)
		{
			$event->brackets(true, $match_id);
		}
        else
        {
			$match->deleteMatchScores($event_id);
        }
       	$text .= '<br />'.EB_MATCHDEL_L3.'<br />';
    }
    $text .= '<br />'.EB_MATCHDEL_L4.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.EB_MATCHDEL_L5.'</a>]<br />';
}
$ns->tablerender(EB_MATCHDEL_L1, $text);
require_once(FOOTERF);
exit;
?>
