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
$event_id = $_GET['eventid'];

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

	$rounds = unserialize($event->getFieldHTML('Rounds'));
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
		case "Clan Tournament":
		$event_type = 'Tournament';
		default:
	}
	if($event_type=='Tournament') $event->setField('FixturesEnable', TRUE);

	if ($pref['eb_events_update_delay_enable'] == 1)
	{
		$eneedupdate = 0;
	}
	else
	{
		// Force always update
		$eneedupdate = 1;
	}
	
	if (
	(($time > $event->getField('NextUpdate_timestamp')) && ($eventIsChanged == 1))
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
	$checkinDuration = INT_MINUTE*$event->getField('CheckinDuration');
	
	if($eventStatus=='signup')
	{
		if($time > ($event->getField('StartDateTime') - $checkinDuration))
		{
			$eventStatus = 'checkin';
		}
	}
	if($eventStatus=='checkin')
	{
		if($time > $event->getField('StartDateTime'))
		{
			if($event->getField('CheckinDuration') > 0)
			{
				// Delete teams who have not checked in
				$q = "SELECT ".TBL_TEAMS.".*"
				." FROM ".TBL_TEAMS
				." WHERE (".TBL_TEAMS.".Event = '$event_id')"
				." AND (".TBL_TEAMS.".CheckedIn = '0')";
				$result = $sql->db_Query($q);
				$nbrTeams = mysql_numrows($result);
				for($i=0; $i<$nbrTeams; $i++)
				{
					$tID = mysql_result($result, $i, TBL_TEAMS.".TeamID");
					deleteTeam($tID);
				}

				// Delete players who have not checked in
				$q = "SELECT ".TBL_PLAYERS.".*"
				." FROM ".TBL_PLAYERS
				." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
				." AND (".TBL_PLAYERS.".CheckedIn = '0')";
				$result = $sql->db_Query($q);
				$nbrPlayers = mysql_numrows($result);
				for($i=0; $i<$nbrPlayers; $i++)
				{
					$pID = mysql_result($result, $i, TBL_PLAYERS.".PlayerID");
					deletePlayer($pID);
				}
			}

			$eventStatus = 'active';
			if($event->getField('FixturesEnable') == TRUE)
			{
				$event->brackets(true);
			}			
			$event->setFieldDB('IsChanged', 1);
		}
	}
	if(($time > $event->getField('EndDateTime')) && ($event->getField('EndDateTime') != 0) && ($eventStatus!='finished'))
	{
		$eventStatus = 'finished';
		if (($type == "One Player Ladder") || ($type == "Team Ladder") )
		{
			$q = "SELECT ".TBL_PLAYERS.".*"
			." FROM ".TBL_PLAYERS
			." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
			. "AND (".TBL_PLAYERS.".Rank = '1')";
			$result = $sql->db_Query($q);
			$numPlayers = mysql_numrows($result);
			//echo "numPlayers: $numPlayers<br>";			
			if($numPlayers == 1)
			{	
				$pid = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
				$q_Awards = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
				VALUES ($pid,'PlayerRankFirst',$time+2)";
				$result_Awards = $sql->db_Query($q_Awards);
			}
			
			$q = "SELECT ".TBL_PLAYERS.".*"
			." FROM ".TBL_PLAYERS
			." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
			. "AND (".TBL_PLAYERS.".Rank = '2')";
			$result = $sql->db_Query($q);
			$numPlayers = mysql_numrows($result);
			if($numPlayers == 1)
			{				
				$pid = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
				$q_Awards = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
				VALUES ($pid,'PlayerRankSecond',$time+1)";
				$result_Awards = $sql->db_Query($q_Awards);
			}

			$q = "SELECT ".TBL_PLAYERS.".*"
			." FROM ".TBL_PLAYERS
			." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
			. "AND (".TBL_PLAYERS.".Rank = '3')";
			$result = $sql->db_Query($q);
			$numPlayers = mysql_numrows($result);
			if($numPlayers == 1)
			{				
				$pid = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
				$q_Awards = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
				VALUES ($pid,'PlayerRankThird',$time)";
				$result_Awards = $sql->db_Query($q_Awards);
			}
		}			
		if (($type == "Clan Ladder") || ($type == "Team Ladder") )
		{
			$q = "SELECT ".TBL_TEAMS.".*"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Event = '$event_id')"
			. "AND (".TBL_TEAMS.".Rank = '1')";
			$result = $sql->db_Query($q);
			$numTeams = mysql_numrows($result);
			if($numTeams == 1)
			{				
				$pid = mysql_result($result,0 , TBL_TEAMS.".TeamID");
				$q_Awards = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
				VALUES ($pid,'TeamRankFirst',$time+2)";
				$result_Awards = $sql->db_Query($q_Awards);
			}
			
			$q = "SELECT ".TBL_TEAMS.".*"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Event = '$event_id')"
			. "AND (".TBL_TEAMS.".Rank = '2')";
			$result = $sql->db_Query($q);
			$numTeams = mysql_numrows($result);
			if($numTeams == 1)
			{				
				$pid = mysql_result($result,0 , TBL_TEAMS.".TeamID");
				$q_Awards = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
				VALUES ($pid,'TeamRankSecond',$time+1)";
				$result_Awards = $sql->db_Query($q_Awards);
			}

			$q = "SELECT ".TBL_TEAMS.".*"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Event = '$event_id')"
			. "AND (".TBL_TEAMS.".Rank = '3')";
			$result = $sql->db_Query($q);
			$numTeams = mysql_numrows($result);
			if($numTeams == 1)
			{				
				$pid = mysql_result($result,0 , TBL_TEAMS.".TeamID");
				$q_Awards = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
				VALUES ($pid,'TeamRankThird',$time)";
				$result_Awards = $sql->db_Query($q_Awards);
			}
		}	
		
		
	}

	$event->setFieldDB('Status', $eventStatus);

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

	$ns->tablerender($event->getField('Name')." ($egame - ".$event->eventTypeToString().")", $text);
	require_once(FOOTERF);
	exit;
}

?>

