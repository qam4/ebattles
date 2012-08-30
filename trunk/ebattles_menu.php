<?php
if(!defined("e107_INIT")){ exit(); }

require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/event.php");

$ebattles_title = $pref['eb_links_menuheading'];
$events_link    = e_PLUGIN.'ebattles/events.php';
$teams_link     = e_PLUGIN.'ebattles/clans.php';
$profile_link   = e_PLUGIN.'ebattles/userinfo.php?user='.USERID;

$text  = '<table style="margin-left: 0px; margin-right: auto;">';
$text .= '<tr>';
$text .= '<td>';
$text .= '<a href="'.$events_link.'">';
$text .= EB_MENU_L2;
$text .= '</a>';
$text .= '</td>';

if(check_class($pref['eb_events_create_class']) && $pref['eb_links_showcreateevent'] == 1)
{
	$text .= '<td>';
	$text .= '<form action="'.e_PLUGIN.'ebattles/eventcreate.php" method="post">';
	$text .= '<div>';
	$text .= '<input type="hidden" name="userid" value="'.USERID.'"/>';
	$text .= '<input type="hidden" name="username" value="'.USERNAME.'"/>';
	$text .= '</div>';
	//$text .= ebImageTextButton('createevent', 'add.png', '', 'simple', '', EB_EVENTS_L20);
	$text .= '<div class="buttons"><button style="display:block; float:left; margin:0 0 0 0; padding:0 0 0 0; background-color:transparent; border:0px; font-size:100%; text-decoration:none; font-weight:bold; cursor:pointer;" type="submit" name="createevent" title="'.EB_EVENTS_L20.'"><img src="'.e_PLUGIN.'ebattles/images/add.png" alt="'.EB_EVENTS_L20.'" style="vertical-align:middle"/></button></div>
	<div style="clear:both"></div>';
	$text .= '</form>';
	$text .= '</td>';
}
$text .= '</tr>';

$text .= '<tr>';
$text .= '<td>';
$text .= '<a href="'.$teams_link.'">';
$text .= EB_MENU_L3;
$text .= '</a>';
$text .= '</td>';
if(check_class($pref['eb_teams_create_class']) && $pref['eb_links_showcreateteam'] == 1)
{
	$text .= '<td>';
	$text .= '<form action="'.e_PLUGIN.'ebattles/clancreate.php" method="post">';
	$text .= '<div>';
	$text .= '<input type="hidden" name="userid" value="'.USERID.'"/>';
	$text .= '<input type="hidden" name="username" value="'.USERNAME.'"/>';
	$text .= '</div>';
	//        $text .= ebImageTextButton('createteam', 'add.png', '', 'simple', '', EB_CLANS_L7);
	$text .= '<div class="buttons"><button style="display:block; float:left; margin:0 0 0 0; padding:0 0 0 0; background-color:transparent; border:0px; font-size:100%; text-decoration:none; font-weight:bold; cursor:pointer;" type="submit" name="createteam" title="'.EB_CLANS_L7.'"><img src="'.e_PLUGIN.'ebattles/images/add.png" alt="'.EB_CLANS_L7.'" style="vertical-align:middle"/></button></div>
	<div style="clear:both"></div>';
	$text .= '</form>';
	$text .= '</td>';
}
$text .= '</tr>';

if (check_class(e_UC_MEMBER))
{
	$text .= '<tr>';
	$text .= '<td>';
	$text .= '<a href="'.$profile_link.'">';
	$text .= EB_MENU_L4;
	$text .= '</a><br />';
	$text .= '</td>';
	$text .= '</tr>';

	/* Get User Information */
	$text .= displayUserInfo(USERID, $profile_link);
}

$text .= '</table>';

$ns->tablerender($ebattles_title,$text);

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayUserInfo - Displays user information
*/
function displayUserInfo($req_user, $profile_link){
	global $pref;
	global $sql;

	$text = '<tr><td>';

	if($pref['eb_links_showmatchsplayed'] == 1)
	{
		/* Display Active Matches */
		$q = "SELECT count(*) "
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_MATCHS.".Status = 'active')"
		." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
		." AND   (".TBL_PLAYERS.".Team != 0)))"
		." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		." AND (".TBL_GAMERS.".User = '$req_user')";
		$result = $sql->db_Query($q);
		$numMatches = mysql_result($result, 0);

		if ($numMatches > 0)
		{
			$text .= $numMatches.'&nbsp;'.EB_EVENT_L59;
			$text .= '<br />';
		}
	}

	if($pref['eb_links_showmatchstoapprove'] == 1)
	{
		/* Display Matches which need user approval */
		$matchArray = array();
		// events owned
		$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_EVENTS
		." WHERE (".TBL_EVENTS.".Owner = '$req_user')"
		."   AND (".TBL_MATCHS.".Event = ".TBL_EVENTS.".EventID)"
		."   AND (".TBL_MATCHS.".Status = 'pending')";
		$result = $sql->db_Query($q);
		$numMatches = mysql_numrows($result);
		for ($match = 0; $match < $numMatches; $match++)
		{
			// For each match played by user
			$matchArray[]  = mysql_result($result,$match, TBL_MATCHS.".MatchID");
		}

		// events mod
		$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_EVENTMODS
		." WHERE (".TBL_EVENTMODS.".User = '$req_user')"
		."   AND (".TBL_MATCHS.".Event = ".TBL_EVENTMODS.".Event)"
		."   AND (".TBL_MATCHS.".Status = 'pending')";
		$result = $sql->db_Query($q);
		$numMatches = mysql_numrows($result);
		for ($match = 0; $match < $numMatches; $match++)
		{
			// For each match played by user
			$matchArray[]  = mysql_result($result,$match, TBL_MATCHS.".MatchID");
		}

		// opps
		// Check if you can approve the match, for each of the pending match
		$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_MATCHS.".Status = 'pending')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
		." AND   (".TBL_PLAYERS.".Team != 0)))"
		." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		." AND (".TBL_GAMERS.".User = '$req_user')"
		." ORDER BY ".TBL_MATCHS.".TimeReported DESC";
		$result = $sql->db_Query($q);
		$numMatches = mysql_numrows($result);
		for ($match = 0; $match < $numMatches; $match++)
		{
			// For each match played by user
			$match_id  = mysql_result($result,$match, TBL_MATCHS.".MatchID");
			$mPlayerMatchTeam  = mysql_result($result,$match, TBL_SCORES.".Player_MatchTeam");

			$userclass = 0;
			$can_approve = 0;

			// Get event information
			$q_event = "SELECT ".TBL_EVENTS.".*, "
			.TBL_GAMES.".*, "
			.TBL_MATCHS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_EVENTS.", "
			.TBL_GAMES.", "
			.TBL_MATCHS.", "
			.TBL_USERS
			." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
			."   AND (".TBL_EVENTS.".EventID = ".TBL_MATCHS.".Event)"
			."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
			."   AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)";

			$result_event = $sql->db_Query($q_event);
			$event_id = mysql_result($result_event,0 , TBL_EVENTS.".EventID");
			$event = new Event($event_id);
			$reported_by  = mysql_result($result_event,0, TBL_MATCHS.".ReportedBy");

			switch($event->getField('Type'))
			{
				case "One Player Ladder":
				case "Team Ladder":
				case "One Player Tournament":
				$reporter_matchteam = 0;
				$q_Reporter = "SELECT DISTINCT ".TBL_SCORES.".*"
				." FROM ".TBL_MATCHS.", "
				.TBL_SCORES.", "
				.TBL_PLAYERS.", "
				.TBL_GAMERS.", "
				.TBL_USERS
				." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
				." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
				." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
				." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				." AND (".TBL_GAMERS.".User = '$reported_by')";
				$result_Reporter = $sql->db_Query($q_Reporter);
				$numRows = mysql_numrows($result_Reporter);
				if ($numRows>0)
				{
					$reporter_matchteam = mysql_result($result_Reporter,0, TBL_SCORES.".Player_MatchTeam");
				}

				// Is the user an opponent of the reporter?
				$q_Opps = "SELECT DISTINCT ".TBL_SCORES.".*"
				." FROM ".TBL_MATCHS.", "
				.TBL_SCORES.", "
				.TBL_PLAYERS.", "
				.TBL_GAMERS.", "
				.TBL_USERS
				." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
				." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
				." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
				." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
				." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				." AND (".TBL_GAMERS.".User = ".USERID.")";
				$result_Opps = $sql->db_Query($q_Opps);
				$numOpps = mysql_numrows($result_Opps);
				break;
				case "Clan Ladder":
				case "Clan Tournament":
				$reporter_matchteam = 0;
				$q_Reporter = "SELECT DISTINCT ".TBL_SCORES.".*"
				." FROM ".TBL_MATCHS.", "
				.TBL_SCORES.", "
				.TBL_TEAMS.", "
				.TBL_PLAYERS.", "
				.TBL_GAMERS.", "
				.TBL_USERS
				." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
				." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
				." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
				." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
				." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				." AND (".TBL_GAMERS.".User = '$reported_by')";
				$result_Reporter = $sql->db_Query($q_Reporter);
				$numRows = mysql_numrows($result_Reporter);
				if ($numRows>0)
				{
					$reporter_matchteam = mysql_result($result_Reporter,0, TBL_SCORES.".Player_MatchTeam");
				}

				// Is the user an opponent of the reporter?
				$q_Opps = "SELECT DISTINCT ".TBL_SCORES.".*"
				." FROM ".TBL_MATCHS.", "
				.TBL_SCORES.", "
				.TBL_TEAMS.", "
				.TBL_PLAYERS.", "
				.TBL_GAMERS.", "
				.TBL_USERS
				." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
				." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
				." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
				." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
				." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
				." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				." AND (".TBL_GAMERS.".User = ".USERID.")";
				$result_Opps = $sql->db_Query($q_Opps);
				$numOpps = mysql_numrows($result_Opps);
				break;
				default:
			}

			if ($numOpps>0)
			{
				$userclass |= eb_UC_EVENT_PLAYER;
				$can_approve = 1;
			}
			if($userclass < $event->getField('MatchesApproval')) $can_approve = 0;
			if($event->getField('MatchesApproval') == eb_UC_NONE) $can_approve = 0;

			if ($can_approve == 1)
			{
				$matchArray[]  = $match_id;
			}
		}

		$numMatches = count(array_unique($matchArray));

		if ($numMatches > 0)
		{
//			$text .= '<span class="badge">'.$numMatches.'</span>&nbsp;'.EB_EVENT_L73;
			$text .= '<span style="background-color:red;	color:white;	font-family: Helvetica,Arial,sans-serif;	font-size: 9px;	line-height: 18px;	font-weight: bold;	padding:3px 6px;	margin:1px;	-moz-border-radius: 10px;	-khtml-border-radius: 10px;	-webkit-border-radius: 10px;	border-radius: 10px;">'.$numMatches.'</span>';
			$text .= '<a href="'.$profile_link.'#tabs-4">';
			$text .= '&nbsp;'.EB_EVENT_L73;
			$text .= '</a>';
			$text .= '<br />';
		}
	}

	if($pref['eb_links_showmatchspending'] == 1)
	{
		/* Display Pending Matches */

		$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_MATCHS.".Status = 'pending')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
		." AND   (".TBL_PLAYERS.".Team != 0)))"
		." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		." AND (".TBL_GAMERS.".User = '$req_user')"
		." ORDER BY ".TBL_MATCHS.".TimeReported DESC";
		$result = $sql->db_Query($q);
		$numMatches = mysql_numrows($result);

		if ($numMatches > 0)
		{
			$text .= $numMatches.'&nbsp;'.EB_EVENT_L64;
			$text .= '<br />';
		}
	}

	if($pref['eb_links_showmatchesscheduled'] == 1)
	{
		/* Display Scheduled Matches */
		$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_MATCHS.".Status = 'scheduled')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
		." AND   (".TBL_PLAYERS.".Team != 0)))"
		." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		." AND (".TBL_GAMERS.".User = '$req_user')"
		." ORDER BY ".TBL_MATCHS.".TimeReported DESC";
		$result = $sql->db_Query($q);
		$numMatches = mysql_numrows($result);

		if ($numMatches > 0)
		{
//			$text .= '<span class="badge">'.$numMatches.'</span>&nbsp;'.EB_EVENT_L70;
			$text .= '<span style="background-color:red;	color:white;	font-family: Helvetica,Arial,sans-serif;	font-size: 9px;	line-height: 18px;	font-weight: bold;	padding:3px 6px;	margin:1px;	-moz-border-radius: 10px;	-khtml-border-radius: 10px;	-webkit-border-radius: 10px;	border-radius: 10px;">'.$numMatches.'</span>';
			$text .= '<a href="'.$profile_link.'#tabs-4">';
			$text .= '&nbsp;'.EB_EVENT_L70;
			$text .= '</a>';
			$text .= '<br />';
		}
	}

	if($pref['eb_links_showchallengesrequested'] == 1)
	{
		/* Display Requested Challenges */
		$q = "SELECT DISTINCT ".TBL_CHALLENGES.".*"
		." FROM ".TBL_CHALLENGES
		." WHERE (".TBL_CHALLENGES.".Status = 'requested')"
		." AND (".TBL_CHALLENGES.".ReportedBy = '$req_user')"
		." ORDER BY ".TBL_CHALLENGES.".TimeReported DESC";
		$result = $sql->db_Query($q);
		$numChallenges = mysql_numrows($result);

		if ($numChallenges > 0)
		{
			$text .= $numChallenges.'&nbsp;'.EB_EVENT_L66;
			$text .= '<br />';
		}
	}
	if($pref['eb_links_showchallengesunconfirmed'] == 1)
	{
		/* Display Unconfirmed Challenges */
		$q = "SELECT DISTINCT ".TBL_CHALLENGES.".*"
		." FROM ".TBL_CHALLENGES.", "
		.TBL_PLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_CHALLENGES.".Status = 'requested')"
		."   AND ((".TBL_PLAYERS.".PlayerID = ".TBL_CHALLENGES.".ChallengedPlayer)"
		."    OR  ((".TBL_PLAYERS.".Team = ".TBL_CHALLENGES.".ChallengedTeam)"
		."   AND   (".TBL_PLAYERS.".Team != 0)))"
		."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		."   AND (".TBL_GAMERS.".User = '$req_user')"
		." ORDER BY ".TBL_CHALLENGES.".TimeReported DESC";
		$result = $sql->db_Query($q);
		$numChallenges = mysql_numrows($result);

		if ($numChallenges > 0)
		{
//			$text .= '<span class="badge">'.$numChallenges.'</span>&nbsp;'.EB_EVENT_L67;
			$text .= '<span style="background-color:red;	color:white;	font-family: Helvetica,Arial,sans-serif;	font-size: 9px;	line-height: 18px;	font-weight: bold;	padding:3px 6px;	margin:1px;	-moz-border-radius: 10px;	-khtml-border-radius: 10px;	-webkit-border-radius: 10px;	border-radius: 10px;">'.$numChallenges.'</span>';
			$text .= '<a href="'.$profile_link.'#tabs-4">';
			$text .= '&nbsp;'.EB_EVENT_L67;
			$text .= '</a>';
	}
	}
	$text .= '</td></tr>';

	return $text;

}

?>
