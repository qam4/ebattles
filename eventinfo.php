<?php
/**
* EventInfo.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/show_array.php");
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/match.php");
require_once(e_PLUGIN."ebattles/include/gamer.php");
require_once(e_PLUGIN."ebattles/include/challenge.php");
require_once(e_PLUGIN."ebattles/include/brackets.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

$pages = new Paginator;

$text .= '
<script type="text/javascript" src="./js/event.js"></script>
';

if (!isset($_GET['orderby'])) $_GET['orderby'] = 1;
$orderby=$_GET['orderby'];

$sort = "DESC";
if(isset($_GET["sort"]) && !empty($_GET["sort"]))
{
	$sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
	$sort_type = ($_GET["sort"]=="ASC") ? SORT_ASC : SORT_DESC;
}

/* Event Name */
$event_id = $_GET['EventID'];

if (!$event_id)
{
	header("Location: ./events.php");
	exit();
}
else
{
	$file = 'cache/sql_cache_event_'.$event_id.'.txt';
	$file_team = 'cache/sql_cache_event_team_'.$event_id.'.txt';

	$q = "SELECT ".TBL_EVENTS.".*, "
	.TBL_GAMES.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_EVENTS.", "
	.TBL_GAMES.", "
	.TBL_USERS
	." WHERE (".TBL_EVENTS.".EventID = '$event_id')"
	."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
	."   AND (".TBL_USERS.".user_id = ".TBL_EVENTS.".Owner)";
	$result = $sql->db_Query($q);

	$event = new Event($event_id);

	$rounds = unserialize($event->getField('Rounds'));
	$egame = mysql_result($result,0 , TBL_GAMES.".Name");
	$egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
	$egameicon = mysql_result($result,0 , TBL_GAMES.".Icon");
	$eowner = mysql_result($result,0 , TBL_USERS.".user_id");
	$eownername = mysql_result($result,0 , TBL_USERS.".user_name");

	$eventIsChanged = $event->getField('IsChanged');
	$eventStatus = $event->getField('Status');

	$type = $event->getField('Type');
	switch($type)
	{
		case "One Player Ladder":
		case "Team Ladder":
		case "Clan Ladder":
		$event_type = 'Ladder';
		break;
		case "One Player Tournament":
		case "Team Tournament":
		$event_type = 'Tournament';
		default:
	}

	if ($pref['eb_update_delay_enable'] == 1)
	{
		$eneedupdate = 0;
	}
	else
	{
		// Force always update
		$eneedupdate = 1;
	}
	
	if (
	(($time > $nextupdate_timestamp_local) && ($eventIsChanged == 1))
	||(file_exists($file) == FALSE)
	||((file_exists($file_team) == FALSE) && (($event->getField('Type') == "Team Ladder")||($event->getField('Type') == "Clan Ladder")))
	)
	{
		$eneedupdate = 1;
	}
	
	if($event->getField('StartDateTime')!=0)
	{
		$startdatetime_local = $event->getField('StartDateTime') + TIMEOFFSET;
		$date_start = date("d M Y, h:i A",$startdatetime_local);
	}
	else
	{
		$date_start = "-";
	}
	if($event->getField('EndDateTime')!=0)
	{
		$enddatetime_local = $event->getField('EndDateTime') + TIMEOFFSET;
		$date_end = date("d M Y, h:i A",$enddatetime_local);
	}
	else
	{
		$date_end = "-";
	}
	
	/* Update event "status" */
	$checkinDuration = 0; // TODO: add this.
	$time_comment = '';
	
	if(($time > $event->getField('EndDateTime')) && ($event->getField('EndDateTime') != 0))
	{
		$q = "UPDATE ".TBL_EVENTS." SET Status = 'finished' WHERE (EventID = '$event_id')";
		$result = $sql->db_Query($q);
		$eventStatus = 'finished';
	}
	
	switch($eventStatus)
	{
		case 'draft':
		/*
		header("Location: ./events.php");
		exit();
		*/
		break;
		case 'signup':
		if($time < ($event->getField('StartDateTime') - $checkinDuration))
		{
			$time_comment = EB_EVENT_L2.'&nbsp;'.get_formatted_timediff($time, $event->getField('StartDateTime'));
		}
		else
		{
			$q = "UPDATE ".TBL_EVENTS." SET Status = 'checkin' WHERE (EventID = '$event_id')";
			$result = $sql->db_Query($q);
		}
		break;
		case 'checkin':
		if($time < $event->getField('StartDateTime'))
		{
			$time_comment = EB_EVENT_L2.'&nbsp;'.get_formatted_timediff($time, $event->getField('StartDateTime'));
		}
		else
		{
			$q = "UPDATE ".TBL_EVENTS." SET Status = 'active' WHERE (EventID = '$event_id')";
			$result = $sql->db_Query($q);
			$q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
			$result = $sql->db_Query($q);
			$eventIsChanged = 1;
		}
		break;
		case 'active':
		if ($event->getField('EndDateTime') != 0)
		{
			$time_comment = EB_EVENT_L3.'&nbsp;'.get_formatted_timediff($time, $event->getField('EndDateTime'));
		}
		break;
		case 'finished':
		$time_comment = EB_EVENT_L4;
		break;
	}

	/* Nbr players */
	$q = "SELECT COUNT(*) as NbrPlayers"
	." FROM ".TBL_PLAYERS
	." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbrplayers = $row['NbrPlayers'];
	
	$q = "SELECT COUNT(*) as NbrPlayers"
	." FROM ".TBL_PLAYERS
	." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
	."   AND (".TBL_PLAYERS.".Banned != 1)";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbrplayersNotBanned = $row['NbrPlayers'];
	
	/* Nbr Teams */
	$q = "SELECT COUNT(*) as NbrTeams"
	." FROM ".TBL_TEAMS
	." WHERE (Event = '$event_id')";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbrteams = $row['NbrTeams'];
	
	switch($event_type)
	{
		case 'Tournament':
		require_once(e_PLUGIN."ebattles/tournamentinfo.php");
		break;
		case 'Ladder':
		require_once(e_PLUGIN."ebattles/ladderinfo.php");
		break;
	}

	$ns->tablerender($event->getField('Name')." ($egame - ".eventTypeToString($event->getField('Type')).")", $text);
	require_once(FOOTERF);
	exit;
}

?>

