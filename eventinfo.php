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

$text .= "
<script type='text/javascript'>
<!--//
function challenge_player_js(v)
{
document.getElementById('challenged_player_choice').value=v;
document.getElementById('challenge_player_form').submit();
}
function challenge_team_js(v)
{
document.getElementById('challenged_team_choice').value=v;
document.getElementById('challenge_team_form').submit();
}
//-->
</script>
";
		
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
	$eMaxNumberPlayers = $event->getField('MaxNumberPlayers');

	$type = $event->getField('Type');
	$competition_type = $event->getCompetitionType();

	/* Nbr players */
	$q = "SELECT COUNT(*) as NbrPlayers"
	." FROM ".TBL_PLAYERS
	." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbr_players = $row['NbrPlayers'];
	
	/* Nbr Teams */
	$q = "SELECT COUNT(*) as NbrTeams"
	." FROM ".TBL_TEAMS
	." WHERE (Event = '$event_id')";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbr_teams = $row['NbrTeams'];

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
		$checkin_end = false;
		
		if($event->getField('CheckinDuration') > 0)
		{
			// End 'checkin' at the beginning of the event, or when we've reached the max number of players
			$q = "SELECT ".TBL_TEAMS.".*"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Event = '$event_id')"
			." AND (".TBL_TEAMS.".CheckedIn = '0')";
			$result = $sql->db_Query($q);
			$nbr_teams_not_checked_in = mysql_numrows($result);
			$nbr_teams_checked_in = $nbr_teams - $nbr_teams_not_checked_in;

			if(($time > $event->getField('StartDateTime')) ||
			   ($nbr_teams_checked_in >= $eMaxNumberPlayers))
			{
				$checkin_end = true;
				// Delete teams who have not checked in
				for($i=0; $i<$nbr_teams_not_checked_in; $i++)
				{
					$tID = mysql_result($result, $i, TBL_TEAMS.".TeamID");
					deleteTeam($tID);
				}
			}

			$q = "SELECT ".TBL_PLAYERS.".*"
			." FROM ".TBL_PLAYERS
			." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
			." AND (".TBL_PLAYERS.".CheckedIn = '0')";
			$result = $sql->db_Query($q);
			$nbr_players_not_checked_in = mysql_numrows($result);
			$nbr_players_checked_in = $nbr_players - $nbr_players_not_checked_in;

			if(($time > $event->getField('StartDateTime')) ||
			   ($nbr_players_checked_in >= $eMaxNumberPlayers))
			{
				$checkin_end = true;
				// Delete players who have not checked in
				for($i=0; $i<$nbr_players_not_checked_in; $i++)
				{
					$pID = mysql_result($result, $i, TBL_PLAYERS.".PlayerID");
					deletePlayer($pID);
				}
			}
		}
		else
		{
			// no checkin
			if($time > $event->getField('StartDateTime'))
			{
				$checkin_end = true;
				
				if(($eMaxNumberPlayers > 0)||($nbr_teams > $eMaxNumberPlayers))
				{
					// Delete teams so that we are left with max number of teams
					$q = "SELECT ".TBL_TEAMS.".*"
					." FROM ".TBL_TEAMS
					." WHERE (".TBL_TEAMS.".Event = '$event_id')"
					." ORDER BY ".TBL_TEAMS.".Seed, ".TBL_TEAMS.".Joined";
					$result = $sql->db_Query($q);
					$nbr_teams = mysql_numrows($result);
					for($i=$eMaxNumberPlayers; $i<$nbr_teams; $i++)
					{
						$tID = mysql_result($result, $i, TBL_TEAMS.".TeamID");
						deleteTeam($tID);
					}
				}

				// Delete players so that we are left with max number of players
				if(($eMaxNumberPlayers > 0)||($nbr_players > $eMaxNumberPlayers))
				{
					$q = "SELECT ".TBL_PLAYERS.".*"
					." FROM ".TBL_PLAYERS
					." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
					." ORDER BY ".TBL_PLAYERS.".Seed, ".TBL_PLAYERS.".Joined";
					$result = $sql->db_Query($q);
					$nbr_players = mysql_numrows($result);
					for($i=$eMaxNumberPlayers; $i<$nbr_players; $i++)
					{
						$pID = mysql_result($result, $i, TBL_PLAYERS.".PlayerID");
						deletePlayer($pID);
					}
				}
			}
		}
		
		if($checkin_end == true)
		{
			$eventStatus = 'active';
			if($event->getField('FixturesEnable') == TRUE)
			{
				$event->updateSeeds();
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
			."   AND (".TBL_PLAYERS.".Rank = '1')";
			$result = $sql->db_Query($q);
			$numPlayers = mysql_numrows($result);
			//echo "numPlayers: $numPlayers<br>";			
			if($numPlayers == 1)
			{	
				$pid = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
				$q_Awards = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
				VALUES ($pid,'PlayerRankFirst',$time+2)";
				$result_Awards = $sql->db_Query($q_Awards);
				
				// gold
				if(is_gold_system_active() && ($event->getField('GoldWinningEvent')>0)) {
					$q = "SELECT ".TBL_PLAYERS.".*, "
					.TBL_GAMERS.".*"
					." FROM ".TBL_PLAYERS.", "
					.TBL_GAMERS
					." WHERE (".TBL_PLAYERS.".PlayerID = '$pid')"
					."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)";
					$uid = mysql_result($result, 0 , TBL_GAMERS.".User");

					$gold_param['gold_user_id'] = $uid;
					$gold_param['gold_who_id'] = 0;
					$gold_param['gold_amount'] = $event->getField('GoldWinningEvent');
					$gold_param['gold_type'] = EB_L1;
					$gold_param['gold_action'] = "credit";
					$gold_param['gold_plugin'] = "ebattles";
					$gold_param['gold_log'] = EB_GOLD_L8.": event=".$event_id.", user=".$uid;
					$gold_param['gold_forum'] = 0;
					$gold_obj->gold_modify($gold_param);
				}
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

				// gold
				if(is_gold_system_active() && ($event->getField('GoldWinningEvent')>0)) {
					// find team captain
					$q = "SELECT ".TBL_TEAMS.".*, "
					.TBL_DIVISIONS.".*"
					." FROM ".TBL_TEAMS.", "
					.TBL_DIVISIONS
					." WHERE (".TBL_TEAMS.".TeamID = '$pid')"
					."   AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)";
					$uid = mysql_result($result, 0 , TBL_DIVISIONS.".Captain");

					$gold_param['gold_user_id'] = $uid;
					$gold_param['gold_who_id'] = 0;
					$gold_param['gold_amount'] = $event->getField('GoldWinningEvent');
					$gold_param['gold_type'] = EB_L1;
					$gold_param['gold_action'] = "credit";
					$gold_param['gold_plugin'] = "ebattles";
					$gold_param['gold_log'] = EB_GOLD_L8.": event=".$event_id.", user=".$uid;
					$gold_param['gold_forum'] = 0;
					$gold_obj->gold_modify($gold_param);
				}	
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
	$nbr_players = $row['NbrPlayers'];
	
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
	$nbr_teams = $row['NbrTeams'];
	
	switch($competition_type)
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

