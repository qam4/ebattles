<?php
/**
*LadderProcess.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/ladder.php');

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

$ladder_id = $_GET['LadderID'];
$ladder = new Ladder($ladder_id);

//var_dump($_POST);
//var_dump($ladder);
//exit;

$can_manage = 0;
if (check_class($pref['eb_mod_class'])) $can_manage = 1;
if (USERID==$ladder->getField('Owner')) $can_manage = 1;
if ($can_manage == 0)
{
	header("Location: ./ladderinfo.php?LadderID=$ladder_id");
	exit();
}
else{

	$q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
	$result = $sql->db_Query($q);

	if(isset($_POST['ladderchangeowner']))
	{
		$ladder_owner = $_POST['ladderowner'];

		/* Ladder Owner */
		$q2 = "UPDATE ".TBL_LADDERS." SET Owner = '$ladder_owner' WHERE (LadderID = '$ladder_id')";
		$result2 = $sql->db_Query($q2);

		//echo "-- ladderchangeowner --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderdeletemod']))
	{
		$laddermod = $_POST['laddermod'];
		$q2 = "DELETE FROM ".TBL_MODS
		." WHERE (".TBL_MODS.".Ladder = '$ladder_id')"
		."   AND (".TBL_MODS.".User = '$laddermod')";
		$result2 = $sql->db_Query($q2);

		//echo "-- ladderdeletemod --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderaddmod']))
	{
		$laddermod = $_POST['mod'];

		$q2 = "SELECT ".TBL_MODS.".*"
		." FROM ".TBL_MODS
		." WHERE (".TBL_MODS.".Ladder = '$ladder_id')"
		."   AND (".TBL_MODS.".User = '$laddermod')";
		$result2 = $sql->db_Query($q2);
		$num_rows_2 = mysql_numrows($result2);
		if ($num_rows_2==0)
		{
			$q2 = "INSERT INTO ".TBL_MODS."(Ladder,User,Level)"
			." VALUES ('$ladder_id','$laddermod',1)";
			$result2 = $sql->db_Query($q2);
		}
		//echo "-- ladderaddmod --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}

	if(isset($_POST['laddersettingssave']))
	{
		/* Ladder Name */
		$new_laddername = $_POST['laddername'];
		if ($new_laddername != '')
		{
			$ladder->setField('Name', $new_laddername);
		}

		/* Ladder Password */
		$ladder->setField('password', $_POST['ladderpassword']);

		/* Ladder Type */
		// Can change only if no players are signed up
		// TODO: should disable the select button.
		$q2 = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')";
		$result2 = $sql->db_Query($q2);
		$num_rows_2 = mysql_numrows($result2);
		if ($num_rows_2==0)
		{
			$new_laddertype = $_POST['laddertype'];

			switch($new_laddertype)
			{
				case 'Individual':
				$ladder->setField('Type', 'One Player Ladder');
				break;
				case 'Team':
				$ladder->setField('Type', 'Team Ladder');
				break;
				case 'ClanWar':
				$ladder->setField('Type', 'ClanWar');
				break;
				default:
			}
		}

		/* Ladder MatchType */
		// Can change only if no players are signed up
		$q2 = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')";
		$result2 = $sql->db_Query($q2);
		$num_rows_2 = mysql_numrows($result2);
		if ($num_rows_2==0)
		{
			$ladder->setField('MatchType', $_POST['laddermatchtype']);
		}

		/* Ladder Ranking Type */
		$ladder->setField('RankingType', $_POST['ladderrankingtype']);

		/* Ladder Match report userclass */
		$ladder->setField('match_report_userclass', $_POST['laddermatchreportuserclass']);

		/* Ladder Match replay report userclass */
		$ladder->setField('match_replay_report_userclass', $_POST['laddermatchreplayreportuserclass']);

		/* Ladder Quick Loss Report */
		if ($_POST['ladderallowquickloss'] != "")
		{
			$ladder->setField('quick_loss_report', 1);
		}
		else
		{
			$ladder->setField('quick_loss_report', 0);
		}

		/* Ladder Allow Score */
		if ($_POST['ladderallowscore'] != "")
		{
			$ladder->setField('AllowScore', 1);
		}
		else
		{
			$ladder->setField('AllowScore', 0);
		}

		/* Ladder Allow Draw */
		if ($_POST['ladderallowdraw'] != "")
		{
			$ladder->setField('AllowDraw', 1);
		}
		else
		{
			$ladder->setField('AllowDraw', 0);
		}

		/* Ladder Match Approval */
		$ladder->setField('MatchesApproval', $_POST['laddermatchapprovaluserclass']);

		/* Points */
		$new_ladderpointsperwin = htmlspecialchars($_POST['ladderpointsperwin']);
		if (preg_match("/^\d+$/", $new_ladderpointsperwin))
		{
			$ladder->setField('PointsPerWin', $new_ladderpointsperwin);
		}
		$new_ladderpointsperdraw = htmlspecialchars($_POST['ladderpointsperdraw']);
		if (preg_match("/^\d+$/", $new_ladderpointsperdraw))
		{
			$ladder->setField('PointsPerDraw', $new_ladderpointsperdraw);
		}
		$new_ladderpointsperloss = htmlspecialchars($_POST['ladderpointsperloss']);
		if (preg_match("/^-?\d+$/", $new_ladderpointsperloss))
		{
			$ladder->setField('PointsPerLoss', $new_ladderpointsperloss);
		}

		/* Ladder Max number of Maps Per Match */
		$new_laddermaxmapspermatch = htmlspecialchars($_POST['laddermaxmapspermatch']);
		if (preg_match("/^\d+$/", $new_laddermaxmapspermatch))
		{
			$ladder->setField('MaxMapsPerMatch', $new_laddermaxmapspermatch);
		}

		/* Ladder Game */
		$ladder->setField('Game', $_POST['laddergame']);

		/* Ladder Start Date */
		$new_ladderstartdate = $_POST['startdate'];
		if ($new_ladderstartdate != '')
		{
			$new_ladderstart_local = strtotime($new_ladderstartdate);
			$new_ladderstart = $new_ladderstart_local - TIMEOFFSET;	// Convert to GMT time
		}
		else
		{
			$new_ladderstart = 0;
		}
		$ladder->setField('Start_timestamp', $new_ladderstart);

		/* Ladder End Date */
		$new_ladderenddate = $_POST['enddate'];
		if ($new_ladderenddate != '')
		{
			$new_ladderend_local = strtotime($new_ladderenddate);
			$new_ladderend = $new_ladderend_local - TIMEOFFSET;	// Convert to GMT time
		}
		else
		{
			$new_ladderend = 0;
		}
		if ($new_ladderend < $new_ladderstart)
		{
			$new_ladderend = $new_ladderstart;
		}
		$ladder->setField('End_timestamp', $new_ladderend);

		/* Ladder Description */
		$ladder->setField('Description', $_POST['ladderdescription']);

		/* Ladder Rules */
		$ladder->setField('Rules', $_POST['ladderrules']);

		//var_dump($ladder);
		//exit;

		if ($ladder_id) {
			// Need to update the ladder in database
			$ladder->updateDB();

		} else {
			// Need to create a ladder.
			$ladder->setField('Owner', USERID);
			$ladder_id = $ladder->insert();
			$ladder->initStats();
		}

		//echo "-- laddersettingssave --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderaddplayer']))
	{
		$player = $_POST['player'];
		$notify = (isset($_POST['ladderaddplayernotify'])? TRUE: FALSE);
		$ladder->ladderAddPlayer($player, 0, $notify);

		//echo "-- ladderaddplayer --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderaddteam']))
	{
		$division = $_POST['division'];
		$notify = (isset($_POST['ladderaddteamnotify'])? TRUE: FALSE);
		$ladder->ladderAddDivision($ladder_id, $division, $notify);

		//echo "-- ladderaddteam --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ban_player']) && $_POST['ban_player']!="")
	{
		$playerid = $_POST['ban_player'];
		$q2 = "UPDATE ".TBL_PLAYERS." SET Banned = '1' WHERE (PlayerID = '$playerid')";
		$result2 = $sql->db_Query($q2);
		updateStats($ladder_id, $time, TRUE);
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['unban_player']) && $_POST['unban_player']!="")
	{
		$playerid = $_POST['unban_player'];
		$q2 = "UPDATE ".TBL_PLAYERS." SET Banned = '0' WHERE (PlayerID = '$playerid')";
		$result2 = $sql->db_Query($q2);
		updateStats($ladder_id, $time, TRUE);
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['kick_player']) && $_POST['kick_player']!="")
	{
		$playerid = $_POST['kick_player'];
		deletePlayer($playerid);
		updateStats($ladder_id, $time, TRUE);
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['del_player_games']) && $_POST['del_player_games']!="")
	{
		$playerid = $_POST['del_player_games'];
		deletePlayerMatches($playerid);
		updateStats($ladder_id, $time, TRUE);
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['del_player_awards']) && $_POST['del_player_awards']!="")
	{
		$playerid = $_POST['del_player_awards'];
		deletePlayerAwards($playerid);
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderresetscores']))
	{
		$ladder->resetPlayers();
		$ladder->resetTeams();
		$ladder->deleteMatches();

		//echo "-- ladderresetscores --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderresetladder']))
	{
		$ladder->deleteMatches();
		$ladder->deleteChallenges();
		$ladder->deletePlayers();
		$ladder->deleteTeams();

		//echo "-- ladderresetladder --<br />";
		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderdelete']))
	{
		$ladder->deleteLadder();

		//echo "-- ladderdelete --<br />";
		header("Location: ladders.php");
		exit();
	}
	if(isset($_POST['ladderupdatescores']))
	{
		if (!isset($_POST['match'])) $_POST['match'] = 0;
		$current_match = $_POST['match'];
		$ladder->ladderScoresUpdate($current_match);
	}
	if(isset($_POST['ladderstatssave']))
	{
		//echo "-- ladderstatssave --<br />";
		$cat_index = 0;

		/* Ladder Min games to rank */
		if ($ladder->getField('Type') != "ClanWar")
		{
			$new_ladderGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
			if (is_numeric($new_ladderGamesToRank))
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET nbr_games_to_rank = '$new_ladderGamesToRank' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			$cat_index++;
		}

		if (($ladder->getField('Type') == "Team Ladder")||($ladder->getField('Type') == "ClanWar"))
		{
			/* Ladder Min Team games to rank */
			$new_ladderTeamGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
			if (is_numeric($new_ladderTeamGamesToRank))
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET nbr_team_games_to_rank = '$new_ladderTeamGamesToRank' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			$cat_index++;
		}

		$q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
		." FROM ".TBL_STATSCATEGORIES
		." WHERE (".TBL_STATSCATEGORIES.".Ladder = '$ladder_id')";

		$result_1 = $sql->db_Query($q_1);
		$numCategories = mysql_numrows($result_1);

		for($i=0; $i<$numCategories; $i++)
		{
			$cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");

			$new_ladderStat = htmlspecialchars($_POST['sliderValue'.$cat_index]);
			if (is_numeric($new_ladderStat))
			{
				$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_ladderStat' WHERE (Ladder = '$ladder_id') AND (CategoryName = '$cat_name')";
				$result2 = $sql->db_Query($q2);
			}

			// Display Only
			if ($_POST['infoonly'.$i] != "")
			$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 1 WHERE (Ladder = '$ladder_id') AND (CategoryName = '$cat_name')";
			else
			$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 0 WHERE (Ladder = '$ladder_id') AND (CategoryName = '$cat_name')";
			$result2 = $sql->db_Query($q2);

			$cat_index ++;
		}

		// Hide ratings column
		if ($_POST['hideratings'] != "")
		$q2 = "UPDATE ".TBL_LADDERS." SET hide_ratings_column = 1 WHERE (LadderID = '$ladder_id')";
		else
		$q2 = "UPDATE ".TBL_LADDERS." SET hide_ratings_column = 0 WHERE (LadderID = '$ladder_id')";
		$result2 = $sql->db_Query($q2);

		$q4 = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
		$result = $sql->db_Query($q4);

		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
	if(isset($_POST['ladderchallengessave']))
	{
		/* Ladder Challenges enable/disable */
		if ($_POST['ladderchallengesenable'] != "")
		{
			$q2 = "UPDATE ".TBL_LADDERS." SET ChallengesEnable = 1 WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);
		}
		else
		{
			$q2 = "UPDATE ".TBL_LADDERS." SET ChallengesEnable = 0 WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);
		}

		/* Ladder Max Dates per Challenge */
		$new_ladderdatesperchallenge = htmlspecialchars($_POST['ladderdatesperchallenge']);
		if (preg_match("/^\d+$/", $new_ladderdatesperchallenge))
		{
			$q2 = "UPDATE ".TBL_LADDERS." SET MaxDatesPerChallenge = '$new_ladderdatesperchallenge' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);
		}

		header("Location: laddermanage.php?LadderID=$ladder_id");
		exit();
	}
}

header("Location: laddermanage.php?LadderID=$ladder_id");
exit;

?>
