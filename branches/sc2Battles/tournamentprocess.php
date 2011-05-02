<?php
/**
*TournamentProcess.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/tournament.php');

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

//var_dump($_POST);
//exit;
$tournament_id = $_GET['TournamentID'];
if (!$tournament_id)
{
	header("Location: ./tournaments.php");
	exit();
}
else
{
	$tournament = new Tournament($tournament_id);
	
	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$tournament->getField('Owner')) $can_manage = 1;
	if ($can_manage == 0)
	{
		header("Location: ./tournamentinfo.php?TournamentID=$tournament_id");
		exit();
	}
	else{
		switch ($tournament->getField('Type'))
		{
			default:
			$file = 'include/brackets/se-'.$tournament->getField('MaxNumberPlayers').'.txt';
			break;
		}
		$matchups = unserialize(implode('',file($file)));
		$nbrRounds = count($matchups);

		$q = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '$tournament_id')";
		$result = $sql->db_Query($q);

		if(isset($_POST['tournamentchangeowner']))
		{
			$tournament_owner = $_POST['tournamentowner'];

			/* Tournament Owner */
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET Owner = '$tournament_owner' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);

			//echo "-- tournamentchangeowner --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentdeletemod']))
		{
			$tournamentmod = $_POST['tournamentmod'];
			$q2 = "DELETE FROM ".TBL_MODS
			." WHERE (".TBL_MODS.".Tournament = '$tournament_id')"
			."   AND (".TBL_MODS.".User = '$tournamentmod')";
			$result2 = $sql->db_Query($q2);

			//echo "-- tournamentdeletemod --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentaddmod']))
		{
			$tournamentmod = $_POST['mod'];

			$q2 = "SELECT ".TBL_MODS.".*"
			." FROM ".TBL_MODS
			." WHERE (".TBL_MODS.".Tournament = '$tournament_id')"
			."   AND (".TBL_MODS.".User = '$tournamentmod')";
			$result2 = $sql->db_Query($q2);
			$num_rows_2 = mysql_numrows($result2);
			if ($num_rows_2==0)
			{
				$q2 = "INSERT INTO ".TBL_MODS."(Tournament,User,Level)"
				." VALUES ('$tournament_id','$tournamentmod',1)";
				$result2 = $sql->db_Query($q2);
			}
			//echo "-- tournamentaddmod --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}

		if(isset($_POST['tournamentsettingssave']))
		{
			/* Tournament Name */
			$new_tournamentname = htmlspecialchars($_POST['tournamentname']);
			if ($new_tournamentname != '')
			{
				$q2 = "UPDATE ".TBL_TOURNAMENTS." SET Name = '$new_tournamentname' WHERE (TournamentID = '$tournament_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Tournament Password */
			$new_tournamentpassword = htmlspecialchars($_POST['tournamentpassword']);
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET Password = '$new_tournamentpassword' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);



			/* Tournament Type */
			// Can change only if no players are signed up
			$q2 = "SELECT ".TBL_TPLAYERS.".*"
			." FROM ".TBL_TPLAYERS
			." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')";
			$result2 = $sql->db_Query($q2);
			$num_rows_2 = mysql_numrows($result2);
			if ($num_rows_2==0)
			{
				$new_tournamenttype = $_POST['tournamenttype'];

				switch($new_tournamenttype)
				{
					case 'Single Elimination':
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET Type = 'Single Elimination' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
					break;
					default:
				}
			}
			
			/* Tournament MatchType */
			// Can change only if no players are signed up
			$q2 = "SELECT ".TBL_TPLAYERS.".*"
			." FROM ".TBL_TPLAYERS
			." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')";
			$result2 = $sql->db_Query($q2);
			$num_rows_2 = mysql_numrows($result2);
			if ($num_rows_2==0)
			{
				$new_tournamentmatchtype = $_POST['tournamentmatchtype'];

				switch($new_tournamentmatchtype)
				{
					case '1v1':
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MatchType = '1v1' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
					break;
					case '2v2':
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MatchType = '2v2' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
					break;
					case '3v3':
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MatchType = '3v3' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
					break;
					case '4v4':
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MatchType = '4v4' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
					break;
					case 'FFA':
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MatchType = 'FFA' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
					break;
					default:
				}
			}

			/* Tournament Max Number of Players */
			$new_tournamentmaxnumberplayers = htmlspecialchars($_POST['tournamentmaxnumberplayers']);
			if ($new_tournamentmaxnumberplayers != '')
			{
				$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MaxNumberPlayers = '$new_tournamentmaxnumberplayers' WHERE (TournamentID = '$tournament_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Tournament Match report userclass */
			$new_tournamentmatchreportuserclass = $_POST['tournamentmatchreportuserclass'];
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET match_report_userclass = '$new_tournamentmatchreportuserclass' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);

			/* Tournament Match Approval */
			$new_MatchesApproval = $_POST['tournamentmatchapprovaluserclass'];
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MatchesApproval = '$new_MatchesApproval' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);

			/* Tournament Game */
			$new_tournamentgame = $_POST['tournamentgame'];
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET Game = '$new_tournamentgame' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);

			/* Tournament Start Date */
			$new_tournamentstartdate = $_POST['startdate'];
			if ($new_tournamentstartdate != '')
			{
				$new_tournamentstart_local = strtotime($new_tournamentstartdate);
				$new_tournamentstart = $new_tournamentstart_local - TIMEOFFSET;	// Convert to GMT time
			}
			else
			{
				$new_tournamentstart = 0;
			}
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET StartDateTime = '$new_tournamentstart' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);
			//echo "$new_tournamentstart, $new_tournamentstartdate";


			/* Tournament Rounds */
			$rounds = unserialize($tournament->getField('Rounds'));
			if (!isset($rounds)) $rounds = array();
			for ($round = 1; $round < $nbrRounds; $round++) {
				if (!isset($rounds[$round])) {
					$rounds[$round] = array();
				}
				if (!isset($rounds[$round]['Title'])) {
					$rounds[$round]['Title'] = EB_TOURNAMENTM_L25.' '.$round;
				}
				if (!isset($rounds[$round]['BestOf'])) {
					$rounds[$round]['BestOf'] = 1;
				}
				$rounds[$round]['Title'] = $tp->toDB($_POST['round_title_'.$round]);
				$rounds[$round]['BestOf'] = $tp->toDB($_POST['round_bestof_'.$round]);
			}
			$tournament->updateRounds($rounds);
			
			/* Tournament Description */
			$new_tournamentdescription = $tp->toDB($_POST['tournamentdescription']);
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET Description = '$new_tournamentdescription' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);

			/* Tournament Rules */
			$new_tournamentrules = $tp->toDB($_POST['tournamentrules']);
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET Rules = '$new_tournamentrules' WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);

			//echo "-- tournamentsettingssave --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentrulessave']))
		{
			//echo "-- tournamentrulessave --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentdeletemap']))
		{
			$tournamentmap = $_POST['tournamentdeletemap'];
			$mapPool = explode(",", $tournament->getField('MapPool'));
		    unset($mapPool[$tournamentmap]);
		    $tournament->updateMapPool($mapPool);

			//echo "-- tournamentdeletemap --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentaddmap']))
		{
			$tournamentmap = $_POST['map'];
			$maps = $tournament->getField('MapPool');
			$mapPool = array();
			if ($maps)	$mapPool = explode(",", $tournament->getField('MapPool'));
			if (!in_array($tournamentmap, $mapPool)) {
			    array_push($mapPool, $tournamentmap);
			    $tournament->updateMapPool($mapPool);
			}
			//echo "-- tournamentaddmap --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentaddplayer']))
		{
			$player = $_POST['player'];
			$notify = (isset($_POST['tournamentaddplayernotify'])? TRUE: FALSE);
			$tournament->tournamentAddPlayer($player, 0, $notify);

			//echo "-- tournamentaddplayer --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentaddteam']))
		{
			$division = $_POST['division'];
			$notify = (isset($_POST['tournamentaddteamnotify'])? TRUE: FALSE);
			$tournament->tournamentAddDivision($tournament_id, $division, $notify);

			//echo "-- tournamentaddteam --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['ban_player']) && $_POST['ban_player']!="")
		{
			$playerid = $_POST['ban_player'];
			$q2 = "UPDATE ".TBL_TPLAYERS." SET Banned = '1' WHERE (TPlayerID = '$playerid')";
			$result2 = $sql->db_Query($q2);
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['unban_player']) && $_POST['unban_player']!="")
		{
			$playerid = $_POST['unban_player'];
			$q2 = "UPDATE ".TBL_TPLAYERS." SET Banned = '0' WHERE (TPlayerID = '$playerid')";
			$result2 = $sql->db_Query($q2);
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['kick_player']) && $_POST['kick_player']!="")
		{
			$playerid = $_POST['kick_player'];
			deleteTPlayer($playerid);
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentresetscores']))
		{
			$tournament->resetPlayers();
			$tournament->resetTeams();
			$tournament->deleteMatches();

			//echo "-- tournamentresetscores --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentresettournament']))
		{
			$tournament->deleteMatches();
			$tournament->deleteChallenges();
			$tournament->deletePlayers();
			$tournament->deleteTeams();

			//echo "-- tournamentresettournament --<br />";
			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentdelete']))
		{
			$tournament->deleteTournament();

			//echo "-- tournamentdelete --<br />";
			header("Location: tournaments.php");
			exit();
		}
		if(isset($_POST['tournamentupdatescores']))
		{
			if (!isset($_POST['match'])) $_POST['match'] = 0;
			$current_match = $_POST['match'];
			$tournament->tournamentScoresUpdate($current_match);
		}
		if(isset($_POST['tournamentstatssave']))
		{
			//echo "-- tournamentstatssave --<br />";
			$cat_index = 0;

			/* Tournament Min games to rank */
			if ($tournament->getField('Type') != "ClanWar")
			{
				$new_tournamentGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
				if (is_numeric($new_tournamentGamesToRank))
				{
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET nbr_games_to_rank = '$new_tournamentGamesToRank' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
				}
				$cat_index++;
			}

			if (($tournament->getField('Type') == "Team Tournament")||($tournament->getField('Type') == "ClanWar"))
			{
				/* Tournament Min Team games to rank */
				$new_tournamentTeamGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
				if (is_numeric($new_tournamentTeamGamesToRank))
				{
					$q2 = "UPDATE ".TBL_TOURNAMENTS." SET nbr_team_games_to_rank = '$new_tournamentTeamGamesToRank' WHERE (TournamentID = '$tournament_id')";
					$result2 = $sql->db_Query($q2);
				}
				$cat_index++;
			}

			$q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
			." FROM ".TBL_STATSCATEGORIES
			." WHERE (".TBL_STATSCATEGORIES.".Tournament = '$tournament_id')";

			$result_1 = $sql->db_Query($q_1);
			$numCategories = mysql_numrows($result_1);

			for($i=0; $i<$numCategories; $i++)
			{
				$cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");

				$new_tournamentStat = htmlspecialchars($_POST['sliderValue'.$cat_index]);
				if (is_numeric($new_tournamentStat))
				{
					$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_tournamentStat' WHERE (Tournament = '$tournament_id') AND (CategoryName = '$cat_name')";
					$result2 = $sql->db_Query($q2);
				}

				// Display Only
				if ($_POST['infoonly'.$i] != "")
				$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 1 WHERE (Tournament = '$tournament_id') AND (CategoryName = '$cat_name')";
				else
				$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 0 WHERE (Tournament = '$tournament_id') AND (CategoryName = '$cat_name')";
				$result2 = $sql->db_Query($q2);

				$cat_index ++;
			}

			// Hide ratings column
			if ($_POST['hideratings'] != "")
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET hide_ratings_column = 1 WHERE (TournamentID = '$tournament_id')";
			else
			$q2 = "UPDATE ".TBL_TOURNAMENTS." SET hide_ratings_column = 0 WHERE (TournamentID = '$tournament_id')";
			$result2 = $sql->db_Query($q2);

			$q4 = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '$tournament_id')";
			$result = $sql->db_Query($q4);

			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
		if(isset($_POST['tournamentchallengessave']))
		{
			/* Tournament Challenges enable/disable */
			if ($_POST['tournamentchallengesenable'] != "")
			{
				$q2 = "UPDATE ".TBL_TOURNAMENTS." SET ChallengesEnable = 1 WHERE (TournamentID = '$tournament_id')";
				$result2 = $sql->db_Query($q2);
			}
			else
			{
				$q2 = "UPDATE ".TBL_TOURNAMENTS." SET ChallengesEnable = 0 WHERE (TournamentID = '$tournament_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Tournament Max Dates per Challenge */
			$new_tournamentdatesperchallenge = htmlspecialchars($_POST['tournamentdatesperchallenge']);
			if (preg_match("/^\d+$/", $new_tournamentdatesperchallenge))
			{
				$q2 = "UPDATE ".TBL_TOURNAMENTS." SET MaxDatesPerChallenge = '$new_tournamentdatesperchallenge' WHERE (TournamentID = '$tournament_id')";
				$result2 = $sql->db_Query($q2);
			}

			header("Location: tournamentmanage.php?TournamentID=$tournament_id");
			exit();
		}
	}
}

header("Location: tournamentmanage.php?TournamentID=$tournament_id");
exit;

?>
