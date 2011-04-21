<?php
// functions for ladders.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/updatestats.php");
require_once(e_PLUGIN."ebattles/include/updateteamstats.php");

class Ladder extends DatabaseTable
{
	protected $tablename = TBL_LADDERS;
	protected $primary_key = "LadderID";

	/***************************************************************************************
	Functions
	***************************************************************************************/
	function resetPlayers()
	{
		global $sql;

		$q = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Ladder = '".$this->fields['LadderID']."')";
		$result = $sql->db_Query($q);
		$num_players = mysql_numrows($result);
		if ($num_players!=0)
		{
			for($j=0; $j< $num_players; $j++)
			{
				$PlayerID  = mysql_result($result,$j, TBL_PLAYERS.".PlayerID");
				$q2 = "UPDATE ".TBL_PLAYERS
				." SET ELORanking = '$this->fields['ELO_default']',"
				."     TS_mu = '".floatToSQL($this->fields['TS_default_mu'])."',"
				."     TS_sigma = '".floatToSQL($this->fields['TS_default_sigma'])."',"
				."     GamesPlayed = 0,"
				."     Loss = 0,"
				."     Win = 0,"
				."     Draw = 0,"
				."     Score = 0,"
				."     ScoreAgainst = 0,"
				."     Points = 0,"
				."     Rank = 0,"
				."     RankDelta = 0,"
				."     OverallScore = 0,"
				."     Streak = 0,"
				."     Streak_Best = 0,"
				."     Streak_Worst = 0"
				." WHERE (PlayerID = '$PlayerID')";
				$result2 = $sql->db_Query($q2);

				deletePlayerAwards($PlayerID);
			}
		}
	}

	function resetTeams()
	{
		global $sql;
		$q = "SELECT ".TBL_TEAMS.".*"
		." FROM ".TBL_TEAMS
		." WHERE (".TBL_TEAMS.".Ladder = '".$this->fields['LadderID']."')";
		$result = $sql->db_Query($q);
		$num_teams = mysql_numrows($result);
		if ($num_teams!=0)
		{
			for($j=0; $j< $num_teams; $j++)
			{
				$TeamID  = mysql_result($result,$j, TBL_TEAMS.".TeamID");
				$q2 = "UPDATE ".TBL_TEAMS
				." SET ELORanking = '$this->fields['ELO_default']',"
				."     TS_mu = '".floatToSQL($this->fields['TS_default_mu'])."',"
				."     TS_sigma = '".floatToSQL($this->fields['TS_default_mu'])."',"
				."     GamesPlayed = 0,"
				."     Loss = 0,"
				."     Win = 0,"
				."     Draw = 0,"
				."     Score = 0,"
				."     ScoreAgainst = 0,"
				."     Points = 0,"
				."     Streak = 0,"
				."     Streak_Best = 0,"
				."     Streak_Worst = 0"
				." WHERE (TeamID = '$TeamID')";
				$result2 = $sql->db_Query($q2);
			}
		}
	}

	function deleteMatches()
	{
		global $sql;
		$q2 = "SELECT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS
		." WHERE (".TBL_MATCHS.".Ladder = '".$this->fields['LadderID']."')";
		$result2 = $sql->db_Query($q2);
		$num_matches = mysql_numrows($result2);
		if ($num_matches!=0)
		{
			for($j=0; $j<$num_matches; $j++)
			{
				$match_id  = mysql_result($result2,$j, TBL_MATCHS.".MatchID");
				$q3 = "DELETE FROM ".TBL_SCORES
				." WHERE (".TBL_SCORES.".MatchID = '$match_id')";
				$result3 = $sql->db_Query($q3);
				$q3 = "DELETE FROM ".TBL_MATCHS
				." WHERE (".TBL_MATCHS.".MatchID = '$match_id')";
				$result3 = $sql->db_Query($q3);
			}
		}
	}

	function deleteChallenges()
	{
		global $sql;
		$q2 = "DELETE FROM ".TBL_CHALLENGES
		." WHERE (".TBL_CHALLENGES.".Ladder = '".$this->fields['LadderID']."')";
		$result2 = $sql->db_Query($q2);
	}

	function deletePlayers()
	{
		global $sql;
		$q2 = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Ladder = '".$this->fields['LadderID']."')";
		$result2 = $sql->db_Query($q2);
		$num_players = mysql_numrows($result2);
		if ($num_players!=0)
		{
			for($j=0; $j<$num_players; $j++)
			{
				$pID  = mysql_result($result2,$j, TBL_PLAYERS.".PlayerID");
				deletePlayerAwards($pID);
				deletePlayer($pID);
			}
		}
	}

	function deleteTeams()
	{
		global $sql;
		$q3 = "DELETE FROM ".TBL_TEAMS
		." WHERE (".TBL_TEAMS.".Ladder = '".$this->fields['LadderID']."')";
		$result3 = $sql->db_Query($q3);
	}

	function deleteMods()
	{
		global $sql;
		$q3 = "DELETE FROM ".TBL_LADDERMODS
		." WHERE (".TBL_LADDERMODS.".Ladder = '".$this->fields['LadderID']."')";
		$result3 = $sql->db_Query($q3);
	}

	function deleteStatsCats()
	{
		global $sql;
		$q3 = "DELETE FROM ".TBL_STATSCATEGORIES
		." WHERE (".TBL_STATSCATEGORIES.".Ladder = '".$this->fields['LadderID']."')";
		$result3 = $sql->db_Query($q3);
	}

 	function deleteLadder()
	{
		global $sql;
		$this->deleteMatches();
		$this->deleteChallenges();
		$this->deletePlayers();
		$this->deleteTeams();
		$this->deleteMods();
		$this->deleteStatsCats();
		$q3 = "DELETE FROM ".TBL_LADDERS
		." WHERE (".TBL_LADDERS.".LadderID = '".$this->fields['LadderID']."')";
		$result3 = $sql->db_Query($q3);
	}

	/**
	* ladderScoresUpdate - Re-calculate the scores and players of an ladder
	*/
	function ladderScoresUpdate($current_match)
	{
		global $sql;
		global $time;

		//echo "dbg: current_match $current_match<br>";

		$numMatchsPerUpdate = 10;

		$q = "SELECT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS
		." WHERE (".TBL_MATCHS.".Ladder = '".$this->fields['LadderID']."')"
		." AND (".TBL_MATCHS.".Status = 'active')"
		." ORDER BY TimeReported";
		$result = $sql->db_Query($q);
		$num_matches = mysql_numrows($result);
	
		if ($current_match > $num_matches)
		{
			switch($this->fields['Type'])
			{
				case "One Player Ladder":
				updateStats($this->fields['LadderID'], $time, TRUE);
				break;
				case "Team Ladder":
				updateStats($this->fields['LadderID'], $time, TRUE);
				updateTeamStats($this->fields['LadderID'], $time, TRUE);
				break;
				case "ClanWar":
				updateTeamStats($this->fields['LadderID'], $time, TRUE);
				break;
				default:
			}
			echo "Done.";
			echo '<META HTTP-EQUIV="Refresh" Content="0; URL=laddermanage.php?LadderID='.$this->fields['LadderID'].'">';
		}
		else
		{
			$next_match = 1;
			if ($current_match == 0)
			{
				// Reset players stats
				$this->resetPlayers();
				$this->resetTeams();

				switch($this->fields['Type'])
				{
					case "One Player Ladder":
					updateStats($this->fields['LadderID'], $this->fields['Start_timestamp'], FALSE);
					break;
					case "Team Ladder":
					updateStats($this->fields['LadderID'], $this->fields['Start_timestamp'], FALSE);
					updateTeamStats($this->fields['LadderID'], $this->fields['Start_timestamp'], FALSE);
					break;
					case "ClanWar":
					updateTeamStats($this->fields['LadderID'], $this->fields['getStart_timestamp'], FALSE);
					break;
					default:
				}
			}
			else
			{
				if (ob_get_level() == 0) {
					ob_start();
				}
				// Output a 'waiting message'
				echo str_pad('Please wait while this task completes... ',4096)."<br />\n";

				// Update matchs scores
				for($j=$current_match - 1; $j < min($current_match + $numMatchsPerUpdate - 1, $num_matches); $j++)
				{
					set_time_limit(10);

					$next_match = $j + 2;
					$match_id  = mysql_result($result,$j, TBL_MATCHS.".MatchID");
					$match = new Match($match_id);
					
					$time_reported  = mysql_result($result,$j, TBL_MATCHS.".TimeReported");

					//echo "dbg: match: $match_id<br>";
					//echo "dbg: etype: $this->fields['Type']<br>";

					$match->match_scores_update();

					switch($this->fields['Type'])
					{
						case "One Player Ladder":
						$match->match_players_update();
						updateStats($this->fields['LadderID'], $this->fields['Start_timestamp'], FALSE);
						break;
						case "Team Ladder":
						$match->match_players_update();
						updateStats($this->fields['LadderID'], $this->fields['Start_timestamp'], FALSE);
						updateTeamStats($this->fields['LadderID'], $this->fields['Start_timestamp'], FALSE);
						break;
						case "ClanWar":
						$match->match_teams_update();
						updateTeamStats($this->fields['LadderID'], $this->fields['Start_timestamp'], FALSE);
						break;
						default:
					}

					//echo 'match '.$j.': '.$match_id.'<br>';
					//echo '<div class="percents">match '.$j.': '.$match_id.'</div>';
					echo '<div class="percents">' . number_format(100*($j+1)/$num_matches, 0, '.', '') . '%&nbsp;complete</div>';
					echo str_pad('',4096)."\n";
					ob_flush();
					flush();
				}
			}

			echo '<form name="updateform" action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$this->fields['LadderID'].'" method="post">';
			echo '<input type="hidden" name="match" value="'.$next_match.'"/>';
			echo '<input type="hidden" name="ladderupdatescores" value="1"/>';
			echo '</form>';
			echo '<script language="javascript">document.updateform.submit()</script>';

			ob_end_flush();
		}
		exit;
	}

	/**
	* ladderAddPlayer - add a user to an ladder
	*/
	function ladderAddPlayer($user, $team = 0, $notify)
	{
		global $sql;

		$q = "SELECT ".TBL_USERS.".*"
		." FROM ".TBL_USERS
		." WHERE (".TBL_USERS.".user_id = '$user')";
		$result = $sql->db_Query($q);
		$username = mysql_result($result, 0, TBL_USERS.".user_name");
		$useremail = mysql_result($result, 0, TBL_USERS.".user_email");
		//dbg:echo "user: $user, $username<br>";
		//dbg:echo "ladder_id: team: $team, user: $user<br>";

		// Is the user already signed up for the team?
		$q = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Ladder = '".$this->fields['LadderID']."')"
		."   AND (".TBL_PLAYERS.".Team = '$team')"
		."   AND (".TBL_PLAYERS.".User = '$user')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		//dbg:echo "num_rows: $num_rows<br>";
		if ($num_rows==0)
		{
			$q = " INSERT INTO ".TBL_PLAYERS."(Ladder,User,Team,ELORanking,TS_mu,TS_sigma)
			VALUES (".$this->fields['LadderID'].",$user,$team,".$this->fields['ELO_default'].",".$this->fields['TS_default_mu'].",".$this->fields['TS_default_sigma'].")";
			$sql->db_Query($q);
			echo "player created, query: $q<br>";
			$q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '".$this->fields['LadderID']."')";
			$sql->db_Query($q);

			if ($notify)
			{
				$sendto = $user;
				$subject = SITENAME." $ename";
				$message = EB_LADDERS_L26.$username.EB_LADDERS_L27.$ename.EB_LADDERS_L29.EB_LADDERS_L31.USERNAME;
				sendNotification($sendto, $subject, $message, $fromid=0);

				// Send email
				//$message = EB_LADDERS_L26.$username.EB_LADDERS_L27.$ename.EB_LADDERS_L30."<a href='".SITEURLBASE.e_PLUGIN_ABS."ebattles/ladderinfo.php?LadderID=$this->fields['LadderID']'>$ename</a>.".EB_LADDERS_L31.USERNAME.EB_LADDERS_L32;
				$message = EB_LADDERS_L26.$username.EB_LADDERS_L27.$ename.EB_LADDERS_L30.SITEURLBASE.e_PLUGIN_ABS."ebattles/ladderinfo.php?LadderID=$this->fields['LadderID']".EB_LADDERS_L31.USERNAME;
				require_once(e_HANDLER."mail.php");
				sendemail($useremail, $subject, $message);
			}
		}
	}


	/**
	* ladderAddDivision - add a division to an ladder
	*/
	function ladderAddDivision($div_id, $notify)
	{
		global $sql;

		//$add_players = ( $this->fields['Type'] == "ClanWar" ? FALSE : TRUE);
		$add_players = TRUE;

		// Is the division signed up
		$q = "SELECT ".TBL_TEAMS.".*"
		." FROM ".TBL_TEAMS
		." WHERE (".TBL_TEAMS.".Ladder = '".$this->fields['LadderID']."')"
		." AND (".TBL_TEAMS.".Division = '$div_id')";
		$result = $sql->db_Query($q);
		$numTeams = mysql_numrows($result);
		if($numTeams == 0)
		{
			$q = "INSERT INTO ".TBL_TEAMS."(Ladder,Division,ELORanking,TS_mu,TS_sigma)
			VALUES (".$this->fields['LadderID'].",$div_id,".$this->fields['ELO_default'].",".$this->fields['TS_default_mu'].",".$this->fields['TS_default_sigma'].")";
			$sql->db_Query($q);
			$team_id =  mysql_insert_id();

			if ($add_players == TRUE)
			{
				// All members of this division will automatically be signed up to this ladder
				$q_2 = "SELECT ".TBL_DIVISIONS.".*, "
				.TBL_MEMBERS.".*, "
				.TBL_USERS.".*"
				." FROM ".TBL_DIVISIONS.", "
				.TBL_USERS.", "
				.TBL_MEMBERS
				." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
				." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
				." AND (".TBL_USERS.".user_id = ".TBL_MEMBERS.".User)";
				$result_2 = $sql->db_Query($q_2);
				$num_rows_2 = mysql_numrows($result_2);
				if($num_rows_2 > 0)
				{
					for($j=0; $j<$num_rows_2; $j++)
					{
						$match_id  = mysql_result($result_2,$j, TBL_USERS.".user_id");
						$this->ladderAddPlayer($match_id, $team_id, $notify);
					}
					$q4 = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '".$this->fields['LadderID']."')";
					$result = $sql->db_Query($q4);
				}
			}
		}
	}
}

function ladderTypeToString($type)
{
	switch($type)
	{
		case "One Player Ladder":
		return EB_LADDERS_L22;
		break;
		case "Team Ladder":
		return EB_LADDERS_L23;
		break;
		case "ClanWar":
		return EB_LADDERS_L25;
		break;
		default:
		return $type;
	}
}

function deletePlayerMatches($player_id)
{
	global $sql;

	$q = "SELECT ".TBL_MATCHS.".*, "
	.TBL_SCORES.".*"
	." FROM ".TBL_MATCHS.", "
	.TBL_SCORES
	." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
	." AND (".TBL_SCORES.".Player = '$player_id')";
	$result = $sql->db_Query($q);
	$num_matches = mysql_numrows($result);
	echo "<br>player_id $player_id";
	echo "<br>num_matches $num_matches";
	if ($num_matches!=0)
	{
		for($j=0; $j<$num_matches; $j++)
		{
			set_time_limit(10);
			$match_id  = mysql_result($result,$j, TBL_MATCHS.".MatchID");
			$match = new Match($match_id);
			$match->deletePlayersMatchScores();
		}
	}
}

function deletePlayer($player_id)
{
	global $sql;
	$q = "DELETE FROM ".TBL_PLAYERS
	." WHERE (".TBL_PLAYERS.".PlayerID = '$player_id')";
	$result = $sql->db_Query($q);
}

function deletePlayerAwards($player_id)
{
	global $sql;
	$q3 = "DELETE FROM ".TBL_AWARDS
	." WHERE (".TBL_AWARDS.".Player = '$player_id')";
	$result3 = $sql->db_Query($q3);
}

?>
