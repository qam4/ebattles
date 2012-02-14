<?php
/**
* matchreport.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/clan.php");

/*******************************************************************
********************************************************************/
// Specify if we use WYSIWYG for text areas
global $e_wysiwyg;
$e_wysiwyg = "match_comment";  // set $e_wysiwyg before including HEADERF
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

$text .= '
<script type="text/javascript">
';
$text .= "
<!--
function SwitchSelected(id)
{
var select = document.getElementById('rank'+id);
nbr_ranks = select.length
new_rank_txt = select.options[select.selectedIndex].text

for (k = 1; k <= nbr_ranks; k++)
{
old_rank_found=0
for (j = 1; j <= nbr_ranks; j++)
{
var select = document.getElementById('rank'+j);
rank_txt = select.options[select.selectedIndex].text
if (rank_txt == 'Team #'+k) {old_rank_found=1}
}
if (old_rank_found==0) {old_rank = k}
}

for (j = 1; j <= nbr_ranks; j++)
{
if (j!=id)
{
var select = document.getElementById('rank'+j);
rank_txt = select.options[select.selectedIndex].text
if (rank_txt == new_rank_txt) {select.selectedIndex=old_rank-1}
}
}
}
//-->
";
$text .= '
</script>
';
$text .= "
<script type='text/javascript'>
<!--//
// Forms
$(function() {
$('.timepicker').datetimepicker({
ampm: true,
timeFormat: 'hh:mm TT',
stepHour: 1,
stepMinute: 10,
minDate: 0
});
});
//-->
</script>
";

$text .= '
<script type="text/javascript">
<!--//
function clearDate(frm)
{
document.getElementById("f_date").value = ""
}
//-->
</script>
';
/* Event Name */
$event_id = $_GET['EventID'];
$match_id = $_GET['matchid'];

$event = new Event($event_id);

switch($event->getField('Type'))
{
	case "One Player Ladder":
	case "Team Ladder":
	case "One Player Tournament":
	$q = "SELECT ".TBL_PLAYERS.".*, "
	.TBL_USERS.".*, "
	.TBL_GAMERS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_GAMERS.", "
	.TBL_USERS
	." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
	." AND (".TBL_PLAYERS.".Banned != 1)"
	." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
	." ORDER BY ".TBL_GAMERS.".UniqueGameID";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	$players_id[0] = EB_MATCHR_L1;
	$players_uid[0] = EB_MATCHR_L1;
	$players_name[0] = EB_MATCHR_L1;
	for($i=0; $i<$num_rows; $i++){
		$pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
		$puid  = mysql_result($result,$i, TBL_USERS.".user_id");
		$prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
		$gamer_id = mysql_result($result,$i, TBL_PLAYERS.".Gamer");
		$gamer = new Gamer($gamer_id);
		$pname = $gamer->getField('Name');
		$pteam  = mysql_result($result,$i, TBL_PLAYERS.".Team");
		list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);
		if ($prank==0)
		$prank_txt = EB_EVENT_L54;
		else
		$prank_txt = "#$prank";

		$players_id[$i+1] = $pid;
		$players_uid[$i+1] = $puid;
		$players_name[$i+1] = $pclantag.$pname." ($prank_txt)";
	}
	break;
	case "Clan Ladder":
	case "Team Tournament":
	$q = "SELECT ".TBL_CLANS.".*, "
	.TBL_TEAMS.".*, "
	.TBL_DIVISIONS.".* "
	." FROM ".TBL_CLANS.", "
	.TBL_TEAMS.", "
	.TBL_DIVISIONS
	." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
	." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
	." AND (".TBL_TEAMS.".Event = '$event_id')"
	." ORDER BY ".TBL_CLANS.".Name";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	$players_id[0] = EB_MATCHR_L1;
	$players_uid[0] = EB_MATCHR_L1;
	$players_name[0] = EB_MATCHR_L1;
	for($i=0; $i<$num_rows; $i++){
		$pid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
		$puid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
		$prank  = mysql_result($result,$i, TBL_TEAMS.".Rank");
		$pname  = mysql_result($result,$i, TBL_CLANS.".Name");
		if ($prank==0)
		$prank_txt = EB_EVENT_L54;
		else
		$prank_txt = "#$prank";

		$players_id[$i+1] = $pid;
		$players_uid[$i+1] = $puid;
		$players_name[$i+1] = $pname." ($prank_txt)";
	}
	break;
	default:
}

$text .= '
<div class="spacer">
';

if($match_id)
{
	$match = new Match($match_id);

	// If match_id is not null, fill up the form information from the database
	switch($event->getField('Type'))
	{
		case "One Player Ladder":
		case "Team Ladder":
		case "One Player Tournament":
		$q = "SELECT ".TBL_MATCHS.".*, "
		.TBL_SCORES.".*, "
		.TBL_PLAYERS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_GAMERS.", "
		.TBL_USERS
		." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
		." ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";
		break;
		case "Clan Ladder":
		case "Team Tournament":
		$q = "SELECT ".TBL_MATCHS.".*, "
		.TBL_SCORES.".*, "
		.TBL_CLANS.".*, "
		.TBL_TEAMS.".*, "
		.TBL_DIVISIONS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_CLANS.", "
		.TBL_TEAMS.", "
		.TBL_DIVISIONS
		." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
		." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
		." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
		." ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";
		break;
		default:
	}

	$result = $sql->db_Query($q);
	$numScores = mysql_numrows($result);

	if (!isset($_POST['nbr_players']))   $_POST['nbr_players'] = $numScores;
	if (!isset($_POST['reported_by'])&&!isset($_POST['matchscheduledreport']))   $_POST['reported_by'] = mysql_result($result,0, TBL_MATCHS.".ReportedBy");
	if (!isset($_POST['match_comment'])) $_POST['match_comment'] = mysql_result($result,0, TBL_MATCHS.".Comments");
	if (!isset($_POST['time_reported'])) $_POST['time_reported'] = mysql_result($result,0, TBL_MATCHS.".TimeReported");

	$time_scheduled = mysql_result($result,0, TBL_MATCHS.".TimeScheduled");
	$time_scheduled_local = $time_scheduled + TIMEOFFSET;
	$date_scheduled = date("m/d/Y h:i A",$time_scheduled_local);
	if (!isset($_POST['date_scheduled'])) $_POST['date_scheduled'] = $date_scheduled;

	$matchMaps = explode(",", mysql_result($result,0, TBL_MATCHS.".Maps"));
	$map = 0;
	foreach($matchMaps as $matchMap)
	{
		if (!isset($_POST['map'.$map]))           $_POST['map'.$map] = $matchMap;
		$map++;
	}

	$index = 1;
	$rank = 0;
	$matchteam = 0;
	$nbr_teams = 0;
	for($score=0;$score < $numScores;$score++)
	{
		switch($event->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			case "One Player Tournament":
			$pid  = mysql_result($result,$score, TBL_PLAYERS.".PlayerID");
			$puid  = mysql_result($result,$score, TBL_USERS.".user_id");
			$gamer_id = mysql_result($result,$score, TBL_PLAYERS.".Gamer");
			$gamer = new Gamer($gamer_id);
			$pname = $gamer->getField('Name');
			$pavatar = mysql_result($result,$score, TBL_USERS.".user_image");
			$pteam  = mysql_result($result,$score, TBL_PLAYERS.".Team");
			list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);
			break;
			case "Clan Ladder":
			case "Team Tournament":
			$pid  = mysql_result($result,$score, TBL_TEAMS.".TeamID");
			$pname  = mysql_result($result,$score, TBL_CLANS.".Name");
			$pavatar = mysql_result($result,$score, TBL_CLANS.".Image");
			$pteam  = mysql_result($result,$score, TBL_TEAMS.".TeamID");
			list($pclan, $pclantag, $pclanid) = getClanInfo($pteam); // Use this function to get other clan info like clan id?
			break;
			default:
		}
		$pscoreid  = mysql_result($result,$score, TBL_SCORES.".ScoreID");
		$prank  = mysql_result($result,$score, TBL_SCORES.".Player_Rank");
		$pMatchTeam  = mysql_result($result,$score, TBL_SCORES.".Player_MatchTeam");
		$pdeltaELO  = mysql_result($result,$score, TBL_SCORES.".Player_deltaELO");
		$pdeltaTS_mu  = mysql_result($result,$score, TBL_SCORES.".Player_deltaTS_mu");
		$pdeltaTS_sigma  = mysql_result($result,$score, TBL_SCORES.".Player_deltaTS_sigma");
		$pscore  = mysql_result($result,$score, TBL_SCORES.".Player_Score");
		$pOppScore  = mysql_result($result,$score, TBL_SCORES.".Player_ScoreAgainst");
		$ppoints  = mysql_result($result,$score, TBL_SCORES.".Player_Points");
		$pfaction  = mysql_result($result,$score, TBL_SCORES.".Faction");

		if ($pMatchTeam > $nbr_teams) $nbr_teams = $pMatchTeam;

		$i = $score + 1;
		if (!isset($_POST['team'.$i]))    $_POST['team'.$i] = 'Team #'.$pMatchTeam;
		if (!isset($_POST['player'.$i]))  $_POST['player'.$i] = $pid;
		if (!isset($_POST['score'.$i]))   $_POST['score'.$i] = $pscore;
		if (!isset($_POST['faction'.$i])) $_POST['faction'.$i] = $pfaction;

		if ($pMatchTeam != $matchteam)
		{
			if (!isset($_POST['rank'.$index])) $_POST['rank'.$index] = 'Team #'.$pMatchTeam;
			if(($prank == $rank)&&($prank!=0))
			{
				if (!isset($_POST['draw'.$index])) $_POST['draw'.$index] = 1;
			}
			else
			{
				$rank++;
			}
			$matchteam = $pMatchTeam;
			$index++;
		}
	}
	if (!isset($_POST['nbr_teams'])) $_POST['nbr_teams'] = $nbr_teams;
}

// assuming we saved the above function in "functions.php", let's make sure it's available
require_once(e_PLUGIN.'ebattles/matchreport_functions.php');

// has the form been submitted?
if (isset($_POST['submit']))
{
	// the form has been submitted
	// perform data checks.
	$error_str = ''; // initialise $error_str as empty

	$reported_by = $_POST['reported_by'];
	$userclass = $_POST['userclass'];
	$time_reported = $_POST['time_reported'];

	//$text .= "reported by: $reported_by<br />";

	$comments = $tp->toDB($_POST['match_comment']);

	$nbr_players = $_POST['nbr_players'];
	$nbr_teams = $_POST['nbr_teams'];
	$userIsPlaying = 0;
	$userIsCaptain = 0;
	$userIsTeamMember = 0;
	// Map
	// List of all Maps
	$q_Maps = "SELECT ".TBL_MAPS.".*"
	." FROM ".TBL_MAPS
	." WHERE (".TBL_MAPS.".Game = '".$event->getField('Game')."')";
	$result_Maps = $sql->db_Query($q_Maps);
	$numMaps = mysql_numrows($result_Maps);
	$map = '';
	for ($matchMap = 0; $matchMap<min($numMaps, $event->getField('MaxMapsPerMatch')); $matchMap++)
	{
		if (!isset($_POST['map'.$matchMap])) $_POST['map'.$matchMap] = '0';
		if ($matchMap > 0) $map .= ',';
		$map .= $_POST['map'.$matchMap];
	}

	for($i=1;$i<=$nbr_players;$i++)
	{
		$pid = $_POST['player'.$i];
		$pMatchTeam = $_POST['team'.$i];

		// Check if a player is not selected
		if ($pid == $players_name[0])
		$error_str .= '<li>'.EB_MATCHR_L2.$i.'&nbsp;'.EB_MATCHR_L3.'</li>';

		// Check if a score is not a number
		if (!isset($_POST['score'.$i])) $_POST['score'.$i] = 0;
		if(!preg_match("/^\d+$/", $_POST['score'.$i]))
		$error_str .= '<li>'.EB_MATCHR_L12.$i.'&nbsp;'.EB_MATCHR_L13.'&nbsp;'.$_POST['score'.$i].'</li>';

		// Faction
		if (!isset($_POST['faction'.$i])) $_POST['faction'.$i] = 0;

		switch($event->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			case "One Player Tournament":
			$q =
			"SELECT ".TBL_USERS.".*, "
			.TBL_PLAYERS.".*"
			." FROM ".TBL_USERS.", "
			.TBL_PLAYERS.", "
			.TBL_GAMERS
			." WHERE (".TBL_PLAYERS.".PlayerID = '$pid')"
			."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			."   AND (".TBL_GAMERS.".User     = ".TBL_USERS.".user_id)";
			$result = $sql->db_Query($q);
			$row = mysql_fetch_array($result);
			$puid = $row['user_id'];
			$pTeam = $row['Team'];

			if ($puid == $reported_by) $userIsPlaying = 1;

			// Check if 2 players are the same user
			// Check if 2 players of same team are playing against each other
			for($j=$i+1;$j<=$nbr_players;$j++)
			{
				//if ($_POST['player'.$i] == $_POST['player'.$j])
				$pjid = $_POST['player'.$j];
				$q =
				"SELECT ".TBL_USERS.".*, "
				.TBL_PLAYERS.".*"
				." FROM ".TBL_USERS.", "
				.TBL_PLAYERS.", "
				.TBL_GAMERS
				." WHERE (".TBL_PLAYERS.".PlayerID = '$pjid')"
				." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				."   AND (".TBL_GAMERS.".User   = ".TBL_USERS.".user_id)";
				$result = $sql->db_Query($q);
				$row = mysql_fetch_array($result);
				$pjuid = $row['user_id'];
				$pjTeam = $row['Team'];
				$pjMatchTeam = $_POST['team'.$j];

				if ($puid == $pjuid)
				$error_str .= '<li>'.EB_MATCHR_L4.$i.'&nbsp;'.EB_MATCHR_L5.$j.'</li>';
				if (($pTeam == $pjTeam)&&($pMatchTeam != $pjMatchTeam)&&($pTeam != 0))
				$error_str .= '<li>'.EB_MATCHR_L6.$i.'&nbsp;'.EB_MATCHR_L7.$j.' '.EB_MATCHR_L8.'</li>';
			}
			break;
			case "Clan Ladder":
			case "Team Tournament":
			// Check if user is the team captain
			$q = "SELECT ".TBL_DIVISIONS.".*, "
			.TBL_TEAMS.".*"
			." FROM ".TBL_DIVISIONS.", "
			.TBL_TEAMS
			." WHERE (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
			." AND (".TBL_TEAMS.".TeamID = '$pid')";
			$result = $sql->db_Query($q);
			$row = mysql_fetch_array($result);
			$dcaptain = $row['Captain'];
			if ($dcaptain == $reported_by) $userIsCaptain = 1;

			// Check if user is a team's member
			$q = "SELECT ".TBL_DIVISIONS.".*, "
			.TBL_MEMBERS.".*, "
			.TBL_TEAMS.".*"
			." FROM ".TBL_DIVISIONS.", "
			.TBL_MEMBERS.", "
			.TBL_TEAMS
			." WHERE (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
			." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
			." AND (".TBL_TEAMS.".TeamID = '$pid')";
			$result = $sql->db_Query($q);
			$numMembers = mysql_numrows($result);
			for($member=0; $member < $numMembers; $member++)
			{
				$muid  = mysql_result($result,$member, TBL_MEMBERS.".User");
				$dcaptain  = mysql_result($result,$member, TBL_DIVISIONS.".Captain");

				if ($dcaptain == $reported_by) $userIsCaptain = 1;
				if ($muid == $reported_by) $userIsTeamMember = 1;
			}

			// Check if 2 teams are the same
			for($j=$i+1;$j<=$nbr_players;$j++)
			{
				if ($_POST['player'.$i] == $_POST['player'.$j])
				$error_str .= '<li>'.EB_MATCHR_L39.$i.'&nbsp;'.EB_MATCHR_L40.$j.'</li>';
			}
			break;
			default:
		}
	}

	if(isset($_POST['matchschedule']))
	{
		if($_POST['date_scheduled'] == '')
		{
			$error_str .= '<li>'.EB_CHALLENGE_L10.'&nbsp;'.EB_CHALLENGE_L11.'</li>';
		}
		else
		{
			$date_scheduled = $_POST['date_scheduled'];
			$time_scheduled_local = strtotime($date_scheduled);
			$time_scheduled = $time_scheduled_local - TIMEOFFSET;	// Convert to GMT time
		}
	}

	if(!isset($_POST['matchschedule']))
	{
		switch($event->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			case "One Player Tournament":
			// Check if the reporter played in the match
			if (($userclass == eb_UC_EVENT_PLAYER) && ($userIsPlaying == 0))
			$error_str .= '<li>'.EB_MATCHR_L9.'</li>';
			break;
			case "Clan Ladder":
			case "Team Tournament":
			// Check if the reporter's team played in the match
			if (($userclass == eb_UC_EVENT_PLAYER) && ($userIsCaptain == 0) && ($userIsTeamMember == 0))
			$error_str .= '<li>'.EB_MATCHR_L37.'</li>';
			break;
			default:
		}
	}

	for($i=1;$i<=$nbr_teams;$i++)
	{
		if (!isset($_POST['rank'.$i])) $_POST['rank'.$i] = 'Team #'.$i;
	}

	// Check if a team has no player
	if(!isset($_POST['matchschedule']))
	{
		for($i=1;$i<=$nbr_teams;$i++)
		{
			$team_players = 0;
			for($j=1;$j<=$nbr_players;$j++)
			{
				if ($_POST['team'.$j] == 'Team #'.$i)
				$team_players ++;
			}
			if ($team_players == 0)
			$error_str .= '<li>'.EB_MATCHR_L10.$i.'&nbsp;'.EB_MATCHR_L11.'</li>';
		}
	}
	// we could do more data checks, but you get the idea.
	// we could also strip any HTML from the variables, convert it to entities, have a maximum character limit on the values, etc etc, but this is just an example.
	// now, have any of these errors happened? We can find out by checking if $error_str is empty

	//$error_str = 'test';

	/*
	//dbg form
	echo "<br>_POST: ";
	print_r($_POST);    // show $_POST
	echo "<br>_GET: ";
	print_r($_GET);     // show $_GET
	exit;
	*/

	if (!empty($error_str)) {
		// show form again
		user_form($players_id, $players_name, $event_id, $match_id, $event->getField('AllowDraw'), $event->getField('AllowForfeit'), $event->getField('AllowScore'),$userclass);
		// errors have occured, halt execution and show form again.
		$text .= '<p style="color:red">'.EB_MATCHR_L14;
		$text .= '<ul style="color:red">'.$error_str.'</ul></p>';
	}
	else
	{
		$text .= "OK<br />";
		if($match_id)
		{
			// Match Edit, Need to delete the match scores and re-create new ones.
			$match->deleteMatchScores($event_id);
		}

		$nbr_players = $_POST['nbr_players'];

		$actual_rank[1] = 1;
		for($i=1;$i<=$nbr_teams;$i++)
		{
			$text .= 'Rank #'.$i.': '.$_POST['rank'.$i];
			$text .= '<br />';
			// Calculate actual rank based on draws checkboxes
			if ($_POST['draw'.$i] != "")
			$actual_rank[$i] = $actual_rank[$i-1];
			else
			$actual_rank[$i] = $i;
		}

		$text .= '--------------------<br />';

		$text .= 'Comments: '.$tp->toHTML($comments).'<br />';

		if($match_id)
		{
			// Edit Match --------------------------------------------
			$q =
			"UPDATE ".TBL_MATCHS
			." SET ReportedBy = '$reported_by',"
			."       TimeReported = '$time_reported',"
			."       Comments = '$comments',"
			."       Status= 'pending',"
			."       Maps = '$map'"
			." WHERE (MatchID = '$match_id')";

			$result = $sql->db_Query($q);
		}
		else
		{
			// Create Match ------------------------------------------
			if(isset($_POST['matchschedule']))
			{
				$q =
				"INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported, Comments, Status, TimeScheduled)
				VALUES ($event_id,'$reported_by', $time_reported, '$comments', 'scheduled', $time_scheduled)";
			}
			else
			{
				$q =
				"INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported,Comments, Status, Maps)
				VALUES ($event_id,'$reported_by', '$time_reported', '$comments', 'pending', '$map')";
			}
			$result = $sql->db_Query($q);
			$last_id = mysql_insert_id();
			$match_id = $last_id;
			$match = new Match($match_id);
		}

		// Create Scores ------------------------------------------
		for($i=1;$i<=$nbr_players;$i++)
		{
			$pid = $_POST['player'.$i];
			$pteam = str_replace("Team #","",$_POST['team'.$i]);

			for($j=1;$j<=$nbr_teams;$j++)
			{
				if( $_POST['rank'.$j] == "Team #".$pteam)
				$prank = $actual_rank[$j];
			}

			$pscore = $_POST['score'.$i];
			$pfaction = $_POST['faction'.$i];
			if ($_POST['forfeit'.$i] != "") {
				$pforfeit = 1;
			} else {
				$pforfeit = 0;
			}

			switch($event->getField('Type'))
			{
				case "One Player Ladder":
				case "Team Ladder":
				case "One Player Tournament":
				$q =
				"INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_Score,Player_Rank,Player_Forfeit, Faction)
				VALUES ($match_id,$pid,$pteam,$pscore,$prank,$pforfeit,$pfaction)
				";
				break;
				case "Clan Ladder":
				case "Team Tournament":
				$q =
				"INSERT INTO ".TBL_SCORES."(MatchID,Team,Player_MatchTeam,Player_Score,Player_Rank,Player_Forfeit, Faction)
				VALUES ($match_id,$pid,$pteam,$pscore,$prank,$pforfeit,$pfaction)
				";
				break;
				default:
			}
			$result = $sql->db_Query($q);
		}
		$text .= '--------------------<br />';

		// Update scores stats
		if(isset($_POST['matchschedule']))
		{
			// Send notification to all the players.
			$fromid = 0;
			$subject = SITENAME." ".EB_MATCHR_L52;

			switch($event->getField('Type'))
			{
				case "One Player Ladder":
				case "Team Ladder":
				case "One Player Tournament":
				$q_Players = "SELECT DISTINCT ".TBL_USERS.".*"
				." FROM ".TBL_MATCHS.", "
				.TBL_SCORES.", "
				.TBL_PLAYERS.", "
				.TBL_GAMERS.", "
				.TBL_USERS
				." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
				." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
				." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
				." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				." AND (".TBL_GAMERS.".User = ".TBL_USERS.".user_id)";
				$result_Players = $sql->db_Query($q_Players);
				$numPlayers = mysql_numrows($result_Players);
				echo "numPlayers: $numPlayers<br>";

				break;
				case "Clan Ladder":
				case "Team Tournament":
				$q_Players = "SELECT DISTINCT ".TBL_USERS.".*"
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
				." AND (".TBL_GAMERS.".User = ".TBL_USERS.".user_id)";
				$result_Players = $sql->db_Query($q_Players);
				$numPlayers = mysql_numrows($result_Players);
				echo "numPlayers: $numPlayers<br>";

				break;
				default:
			}

			if($numPlayers > 0)
			{
				for($j=0; $j < $numPlayers; $j++)
				{
					$pname = mysql_result($result_Players, $j, TBL_USERS.".user_name");
					$pemail = mysql_result($result_Players, $j, TBL_USERS.".user_email");
					$message = EB_MATCHR_L53.$pname.EB_MATCHR_L54.EB_MATCHR_L55.$event->getField('Name').EB_MATCHR_L56;
					$sendto = mysql_result($result_Players, $j, TBL_USERS.".user_id");
					$sendtoemail = mysql_result($result_Players, $j, TBL_USERS.".user_email");
					if (check_class($pref['eb_pm_notifications_class']))
					{
						sendNotification($sendto, $subject, $message, $fromid);
					}
					if (check_class($pref['eb_email_notifications_class']))
					{
						// Send email
						require_once(e_HANDLER."mail.php");
						sendemail($sendtoemail, $subject, $message);
					}
				}
			}

			header("Location: eventinfo.php?EventID=$event_id");
		}
		else
		{
			$match->match_scores_update();

			// Automatically Update Players stats only if Match Approval is Disabled
			if ($event->getField('MatchesApproval') == eb_UC_NONE)
			{
				switch($event->getField('Type'))
				{
					case "One Player Ladder":
					case "Team Ladder":
					$match->match_players_update();
					break;
					case "One Player Tournament":
					$match->match_players_update();
					$event->scheduleNextMatches();
					break;
					case "Clan Ladder":
					$match->match_teams_update();
					break;
					case "Team Tournament":
					$match->match_teams_update();
					$event->scheduleNextMatches();
					break;
					default:
				}

				$event->setFieldDB('IsChanged', 1);
			}
			header("Location: matchinfo.php?matchid=$match_id");
		}

		exit();
	}
	// if we get here, all data checks were okay, process information as you wish.
} else {

	if (!isset($_POST['matchreport'])&&!isset($_POST['matchedit'])&&!isset($_POST['matchscheduledreport'])&&!isset($_POST['matchschedule']))
	{
		$text .= '<p>'.EB_MATCHR_L33.'</p>';
		$text .= '<p>'.EB_MATCHR_L34.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?EventID='.$event_id.'">Event</a>]</p>';
	}
	else if (!check_class(e_UC_MEMBER))
	{
		$text .= '<p>'.EB_MATCHR_L36.'</p>';
		$text .= '<p>'.EB_MATCHR_L34.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?EventID='.$event_id.'">Event</a>]</p>';
	}
	else
	{
		$userclass = $_POST['userclass'];
		// the form has not been submitted, let's show it
		user_form($players_id, $players_name, $event_id, $match_id, $event->getField('AllowDraw'), $event->getField('AllowForfeit'), $event->getField('AllowScore'),$userclass);
	}
}

$text .= '
</div>
';

$ns->tablerender($event->getField('Name')." (".$event->eventTypeToString().") - ".EB_MATCHR_L32, $text);
require_once(FOOTERF);
exit;
?>
