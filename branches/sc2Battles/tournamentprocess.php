<?php
/**
*EventProcess.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/event.php');

/*******************************************************************
********************************************************************/
echo '
<html>
<head>
<style type="text/css">
<!--
.percents {
background: #FFF;
position:absolute;
text-align: center;
}
-->
</style>
</head>
<body>
';

$event_id = $_GET['EventID'];
$event = new Event($event_id);

//var_dump($_POST);
//var_dump($event);
//exit;

$can_manage = 0;
if (check_class($pref['eb_mod_class'])) $can_manage = 1;
if (USERID==$event->getField('Owner')) $can_manage = 1;
if (!$event_id) $can_manage = 1;	// event creation
if ($can_manage == 0)
{
	header("Location: ./eventinfo.php?EventID=$event_id");
	exit();
}
else{
	$q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
	$result = $sql->db_Query($q);

	if(isset($_POST['eventpublish']))
	{
		/* Event Status */
		$q2 = "UPDATE ".TBL_EVENTS." SET Status = 'signup' WHERE (EventID = '$event_id')";
		$result2 = $sql->db_Query($q2);

		//echo "-- eventpublish --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventchangeowner']))
	{
		$event_owner = $_POST['eventowner'];

		/* Event Owner */
		$q2 = "UPDATE ".TBL_EVENTS." SET Owner = '$event_owner' WHERE (EventID = '$event_id')";
		$result2 = $sql->db_Query($q2);

		//echo "-- eventchangeowner --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventdeletemod']))
	{
		$eventmod = $_POST['eventmod'];
		$q2 = "DELETE FROM ".TBL_MODS
		." WHERE (".TBL_MODS.".Event = '$event_id')"
		."   AND (".TBL_MODS.".User = '$eventmod')";
		$result2 = $sql->db_Query($q2);

		//echo "-- eventdeletemod --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventaddmod']))
	{
		$eventmod = $_POST['mod'];

		$q2 = "SELECT ".TBL_MODS.".*"
		." FROM ".TBL_MODS
		." WHERE (".TBL_MODS.".Event = '$event_id')"
		."   AND (".TBL_MODS.".User = '$eventmod')";
		$result2 = $sql->db_Query($q2);
		$num_rows_2 = mysql_numrows($result2);
		if ($num_rows_2==0)
		{
			$q2 = "INSERT INTO ".TBL_MODS."(Event,User,Level)"
			." VALUES ('$event_id','$eventmod',1)";
			$result2 = $sql->db_Query($q2);
		}
		//echo "-- eventaddmod --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}

	if(isset($_POST['eventsettingssave']))
	{
		/* Event Name */
		$new_eventname = $_POST['eventname'];
		if ($new_eventname != '')
		{
			$event->setField('Name', $new_eventname);
		}

		/* Event Password */
		$event->setField('password', $_POST['eventpassword']);

		/* Event Type */
		// Can change only if no players are signed up
		// TODO: should disable the select button.
		$q2 = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
		$result2 = $sql->db_Query($q2);
		$num_rows_2 = mysql_numrows($result2);
		if ($num_rows_2==0)
		{
			$new_eventtype = $_POST['eventtype'];

			switch($new_eventtype)
			{
				case 'One Player Tournament':
				$event->setField('Type', $_POST['eventtype']);
				break;
				default:
			}
		}

		/* Event MatchType */
		// Can change only if no players are signed up
		// TODO: should disable the select button, and fix this for teams.
		$q2 = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
		$result2 = $sql->db_Query($q2);
		$num_rows_2 = mysql_numrows($result2);
		if ($num_rows_2==0)
		{
			$event->setField('MatchType', $_POST['eventmatchtype']);
		}

		/* Event Max Number of Players */
		$event->setField('MaxNumberPlayers', $_POST['eventmaxnumberplayers']);

		/* Event Match report userclass */
		$event->setField('match_report_userclass', $_POST['eventmatchreportuserclass']);

		/* Event Match report userclass */
		$event->setField('match_replay_report_userclass', $_POST['eventmatchreplayreportuserclass']);

		/* Event Allow Score */
		if ($_POST['eventallowscore'] != "")
		{
			$event->setField('AllowScore', 1);
		}
		else
		{
			$event->setField('AllowScore', 0);
		}

		/* Event Forfeit */
		if ($_POST['eventallowforfeit'] != "")
		{
			$event->setField('AllowForfeit', 1);
		}
		else
		{
			$event->setField('AllowForfeit', 0);
		}

		/* Event Match Approval */
		$event->setField('MatchesApproval', $_POST['eventmatchapprovaluserclass']);

		/* Event Game */
		$event->setField('Game', $_POST['eventgame']);

		/* Event Start Date */
		$new_eventstartdate = $_POST['startdate'];
		if ($new_eventstartdate != '')
		{
			$new_eventstart_local = strtotime($new_eventstartdate);
			$new_eventstart = $new_eventstart_local - TIMEOFFSET;	// Convert to GMT time
		}
		else
		{
			$new_eventstart = 0;
		}
		$event->setField('StartDateTime', $new_eventstart);


		/* Event Rounds */
		switch ($event->getField('Type'))
		{
			default:
			$file = 'include/brackets/se-'.$event->getField('MaxNumberPlayers').'.txt';
			break;
		}
		$matchups = unserialize(implode('',file($file)));
		$nbrRounds = count($matchups);

		$rounds = unserialize($event->getField('Rounds'));
		if (!isset($rounds)) $rounds = array();
		for ($round = 1; $round < $nbrRounds; $round++) {
			if (!isset($rounds[$round])) {
				$rounds[$round] = array();
			}
			if (!isset($rounds[$round]['Title'])) {
				$rounds[$round]['Title'] = EB_EVENTM_L144.' '.$round;
			}
			if (!isset($rounds[$round]['BestOf'])) {
				$rounds[$round]['BestOf'] = 1;
			}
			$rounds[$round]['Title'] = $tp->toDB($_POST['round_title_'.$round]);
			$rounds[$round]['BestOf'] = $tp->toDB($_POST['round_bestof_'.$round]);
		}
		$event->updateRounds($rounds);

		/* Event Description */
		$event->setField('Description', $_POST['eventdescription']);

		/* Event Rules */
		$event->setField('Rules', $_POST['eventrules']);

		if ($event_id) {
			// Need to update the event in database
			$event->updateDB();

		} else {
			// Need to create a event.
			$event->setField('Owner', USERID);
			$event_id = $event->insert();
		}
		
		//echo "-- eventsettingssave --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventdeletemap']))
	{
		$eventmap = $_POST['eventdeletemap'];
		$mapPool = explode(",", $event->getField('MapPool'));
		unset($mapPool[$eventmap]);
		$event->updateMapPool($mapPool);
		$event->updateDB();

		//echo "-- eventdeletemap --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventaddmap']))
	{
		$eventmap = $_POST['map'];
		$maps = $event->getField('MapPool');
		$mapPool = array();
		if ($maps)	$mapPool = explode(",", $event->getField('MapPool'));
		if (!in_array($eventmap, $mapPool)) {
			array_push($mapPool, $eventmap);
			$event->updateMapPool($mapPool);
			$event->updateDB();
		}
		//echo "-- eventaddmap --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventaddplayer']))
	{
		$player = $_POST['player'];
		$notify = (isset($_POST['eventaddplayernotify'])? TRUE: FALSE);
		$event->eventAddPlayer($player, 0, $notify);

		//echo "-- eventaddplayer --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventaddteam']))
	{
		$division = $_POST['division'];
		$notify = (isset($_POST['eventaddteamnotify'])? TRUE: FALSE);
		$event->eventAddDivision($event_id, $division, $notify);

		//echo "-- eventaddteam --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['ban_player']) && $_POST['ban_player']!="")
	{
		$playerid = $_POST['ban_player'];
		$q2 = "UPDATE ".TBL_PLAYERS." SET Banned = '1' WHERE (PlayerID = '$playerid')";
		$result2 = $sql->db_Query($q2);
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['unban_player']) && $_POST['unban_player']!="")
	{
		$playerid = $_POST['unban_player'];
		$q2 = "UPDATE ".TBL_PLAYERS." SET Banned = '0' WHERE (PlayerID = '$playerid')";
		$result2 = $sql->db_Query($q2);
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['kick_player']) && $_POST['kick_player']!="")
	{
		$playerid = $_POST['kick_player'];
		deletePlayer($playerid);
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventresetscores']))
	{
		$event->resetPlayers();
		$event->resetTeams();
		$event->deleteMatches();

		//echo "-- eventresetscores --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventresetevent']))
	{
		$event->deleteMatches();
		$event->deleteChallenges();
		$event->deletePlayers();
		$event->deleteTeams();

		//echo "-- eventresetevent --<br />";
		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventdelete']))
	{
		$event->deleteEvent();

		//echo "-- eventdelete --<br />";
		header("Location: events.php");
		exit();
	}
	if(isset($_POST['eventupdatescores']))
	{
		if (!isset($_POST['match'])) $_POST['match'] = 0;
		$current_match = $_POST['match'];
		$event->eventScoresUpdate($current_match);
	}
	if(isset($_POST['eventstatssave']))
	{
		//echo "-- eventstatssave --<br />";
		$cat_index = 0;

		/* Event Min games to rank */
		if ($event->getField('Type') != "Clan Ladder")
		{
			$new_eventGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
			if (is_numeric($new_eventGamesToRank))
			{
				$q2 = "UPDATE ".TBL_EVENTS." SET nbr_games_to_rank = '$new_eventGamesToRank' WHERE (EventID = '$event_id')";
				$result2 = $sql->db_Query($q2);
			}
			$cat_index++;
		}

		if (($event->getField('Type') == "Team Ladder")||($event->getField('Type') == "Clan Ladder"))
		{
			/* Event Min Team games to rank */
			$new_eventTeamGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
			if (is_numeric($new_eventTeamGamesToRank))
			{
				$q2 = "UPDATE ".TBL_EVENTS." SET nbr_team_games_to_rank = '$new_eventTeamGamesToRank' WHERE (EventID = '$event_id')";
				$result2 = $sql->db_Query($q2);
			}
			$cat_index++;
		}

		$q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
		." FROM ".TBL_STATSCATEGORIES
		." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')";

		$result_1 = $sql->db_Query($q_1);
		$numCategories = mysql_numrows($result_1);

		for($i=0; $i<$numCategories; $i++)
		{
			$cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");

			$new_eventStat = htmlspecialchars($_POST['sliderValue'.$cat_index]);
			if (is_numeric($new_eventStat))
			{
				$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventStat' WHERE (Event = '$event_id') AND (CategoryName = '$cat_name')";
				$result2 = $sql->db_Query($q2);
			}

			// Display Only
			if ($_POST['infoonly'.$i] != "")
			$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 1 WHERE (Event = '$event_id') AND (CategoryName = '$cat_name')";
			else
			$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 0 WHERE (Event = '$event_id') AND (CategoryName = '$cat_name')";
			$result2 = $sql->db_Query($q2);

			$cat_index ++;
		}

		// Hide ratings column
		if ($_POST['hideratings'] != "")
		$q2 = "UPDATE ".TBL_EVENTS." SET hide_ratings_column = 1 WHERE (EventID = '$event_id')";
		else
		$q2 = "UPDATE ".TBL_EVENTS." SET hide_ratings_column = 0 WHERE (EventID = '$event_id')";
		$result2 = $sql->db_Query($q2);

		$q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
		$result = $sql->db_Query($q4);

		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
	if(isset($_POST['eventchallengessave']))
	{
		/* Event Challenges enable/disable */
		if ($_POST['eventchallengesenable'] != "")
		{
			$q2 = "UPDATE ".TBL_EVENTS." SET ChallengesEnable = 1 WHERE (EventID = '$event_id')";
			$result2 = $sql->db_Query($q2);
		}
		else
		{
			$q2 = "UPDATE ".TBL_EVENTS." SET ChallengesEnable = 0 WHERE (EventID = '$event_id')";
			$result2 = $sql->db_Query($q2);
		}

		/* Event Max Dates per Challenge */
		$new_eventdatesperchallenge = htmlspecialchars($_POST['eventdatesperchallenge']);
		if (preg_match("/^\d+$/", $new_eventdatesperchallenge))
		{
			$q2 = "UPDATE ".TBL_EVENTS." SET MaxDatesPerChallenge = '$new_eventdatesperchallenge' WHERE (EventID = '$event_id')";
			$result2 = $sql->db_Query($q2);
		}

		header("Location: eventmanage.php?EventID=$event_id");
		exit();
	}
}

header("Location: eventmanage.php?EventID=$event_id");
exit;

?>
