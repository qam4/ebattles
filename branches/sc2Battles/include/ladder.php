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
	function setDefaultFields()
	{
		$this->setField('Game', 1);
		$this->setField('Type', 'One Player Ladder');
		$this->setField('MatchType', '1v1');
		$this->setField('RankingType', 'Classic');
		$this->setField('PointsPerWin', '3');
		$this->setField('PointsPerDraw', '2');
		$this->setField('PointsPerLoss', '1');
		$this->setField('MaxMapsPerMatch', '1');
	}

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
		$q3 = "DELETE FROM ".TBL_MODS
		." WHERE (".TBL_MODS.".Ladder = '".$this->fields['LadderID']."')";
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
	* ladderScoresUpdate - Re-calculate the scores and players of a ladder
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
	* ladderAddPlayer - add a user to a ladder
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

		// Find gamer for that user
		$q = "SELECT ".TBL_GAMERS.".*"
		." FROM ".TBL_GAMERS
		." WHERE (".TBL_GAMERS.".Game = '".$this->fields['Game']."')"
		."   AND (".TBL_GAMERS.".User = '$user')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		if ($num_rows==0)
		{
			// FIXME: error here, add dialog
			$q = " INSERT INTO ".TBL_GAMERS."(User,Game,UniqueGameID)
			VALUES ($user,".$this->fields['Game'].",'".$username."')";
			$sql->db_Query($q);
			$last_id = mysql_insert_id();
			$gamerID = $last_id;
		}
		else
		{
			$gamerID = mysql_result($result, 0, TBL_GAMERS.".GamerID");
		}

		// Is the user already signed up for the team?
		$q = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_PLAYERS.".Ladder = '".$this->fields['LadderID']."')"
		."   AND (".TBL_PLAYERS.".Team = '$team')"
		."   AND (".TBL_PLAYERS.".Gamer = '$gamerID')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		echo "num_rows: $num_rows<br>";
		if ($num_rows==0)
		{
			$q = " INSERT INTO ".TBL_PLAYERS."(Ladder,Gamer,Team,ELORanking,TS_mu,TS_sigma)
			VALUES (".$this->fields['LadderID'].",$gamerID,$team,".$this->fields['ELO_default'].",".$this->fields['TS_default_mu'].",".$this->fields['TS_default_sigma'].")";
			$sql->db_Query($q);
			echo "player created, query: $q<br>";
			$q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '".$this->fields['LadderID']."')";
			$sql->db_Query($q);

			if ($notify)
			{
				$sendto = $user;
				$subject = SITENAME.$this->fields['Name'];
				$message = EB_LADDERS_L26.$username.EB_LADDERS_L27.$this->fields['Name'].EB_LADDERS_L29.EB_LADDERS_L31.USERNAME;
				sendNotification($sendto, $subject, $message, $fromid=0);

				// Send email
				//$message = EB_LADDERS_L26.$username.EB_LADDERS_L27.$this->fields['Name'].EB_LADDERS_L30."<a href='".SITEURLBASE.e_PLUGIN_ABS."ebattles/ladderinfo.php?LadderID=$this->fields['LadderID']'>$this->fields['Name']</a>.".EB_LADDERS_L31.USERNAME.EB_LADDERS_L32;
				$message = EB_LADDERS_L26.$username.EB_LADDERS_L27.$this->fields['Name'].EB_LADDERS_L30.SITEURLBASE.e_PLUGIN_ABS."ebattles/ladderinfo.php?LadderID=$this->fields['LadderID']".EB_LADDERS_L31.USERNAME;
				require_once(e_HANDLER."mail.php");
				sendemail($useremail, $subject, $message);
			}
		}
	}


	/**
	* ladderAddDivision - add a division to a ladder
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
						$user_id  = mysql_result($result_2,$j, TBL_USERS.".user_id");
						$this->ladderAddPlayer($user_id, $team_id, $notify);
					}
					$q4 = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '".$this->fields['LadderID']."')";
					$result = $sql->db_Query($q4);
				}
			}
		}
	}
	function displayLadderSettingsForm()
	{
		global $sql;
		// Specify if we use WYSIWYG for text areas
		global $e_wysiwyg;
		$e_wysiwyg	= "ladderdescription,ladderrules";  // set $e_wysiwyg before including HEADERF
		if (e_WYSIWYG)
		{
			$insertjs = "rows='25'";
		}
		else
		{
			require_once(e_HANDLER."ren_help.php");
			$insertjs = "rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
		}

		$text .= '
		<!-- main calendar program -->
		<script type="text/javascript" src="./js/calendar/calendar.js"></script>
		<!-- language for the calendar -->
		<script type="text/javascript" src="./js/calendar/lang/calendar-en.js"></script>
		<!-- the following script defines the Calendar.setup helper function, which makes
		adding a calendar a matter of 1 or 2 lines of code. -->
		<script type="text/javascript" src="./js/calendar/calendar-setup.js"></script>
		<script type="text/javascript">
		<!--//
		function clearStartDate(frm)
		{
		frm.startdate.value = ""
		}
		function clearEndDate(frm)
		{
		frm.enddate.value = ""
		}
		//-->
		</script>
		';
		$text .= "
		<script type='text/javascript'>
		<!--//
		// Forms
		$(function() {
		$( '#radio1' ).buttonset();
		$( '#radio2' ).buttonset();
		});
		//-->
		</script>
		";

		$text .= '<form id="form-ladder-settings" action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$this->getField('LadderID').'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		//<!-- Ladder Name -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L15.'</b></td>
		<td class="eb_td1">
		<div><input class="tbox" type="text" size="40" name="laddername" value="'.$this->getField('Name').'"/></div>
		</td>
		</tr>
		';

		//<!-- Ladder Password -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L16.'</b></td>
		<td class="eb_td1">
		<div><input class="tbox" type="text" size="40" name="ladderpassword" value="'.$this->getField('password').'"/></div>
		</td>
		</tr>
		';
		//<!-- Ladder Game -->

		$q = "SELECT ".TBL_GAMES.".*"
		." FROM ".TBL_GAMES
		." ORDER BY Name";
		$result = $sql->db_Query($q);
		/* Error occurred, return given name by default */
		$numGames = mysql_numrows($result);
		$text .= '<tr>';
		$text .= '<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L17.'</b></td>';
		$text .= '<td class="eb_td1"><select class="tbox" name="laddergame">';
		for($i=0; $i<$numGames; $i++){
			$gname  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
			if ($this->getField('Game') == $gid)
			{
				$text .= '<option value="'.$gid.'" selected="selected">'.htmlspecialchars($gname).'</option>';
			}
			else
			{
				$text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
			}
		}
		$text .= '</select>';
		$text .= '</td></tr>';

		//<!-- Type -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L18.'</b></td>
		<td class="eb_td1">
		<div id="radio1">
		';
		$text .= '<input class="tbox" type="radio" id="radio11" size="40" name="laddertype" '.($this->getField('Type') == "One Player Ladder" ? 'checked="checked"' : '').' value="Individual" /><label for="radio11">'.EB_LADDERM_L19.'</label>';
		$text .= '<input class="tbox" type="radio" id="radio12" size="40" name="laddertype" '.($this->getField('Type') == "Team Ladder" ? 'checked="checked"' : '').' value="Team" /><label for="radio12">'.EB_LADDERM_L20.'</label>';
		$text .= '<input class="tbox" type="radio" id="radio13" size="40" name="laddertype" '.($this->getField('Type') == "ClanWar" ? 'checked="checked"' : '').' value="ClanWar" /><label for="radio13">'.EB_LADDERM_L116.'</label>';

		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Match Type -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L126.'</b></td>
		<td class="eb_td1"><select class="tbox" name="laddermatchtype">';
		$text .= '<option value="1v1" '.($this->getField('MatchType') == "1v1" ? 'selected="selected"' : '') .'>'.EB_LADDERM_L127.'</option>';
		$text .= '<option value="2v2" '.($this->getField('MatchType') == "2v2" ? 'selected="selected"' : '') .'>'.EB_LADDERM_L128.'</option>';
		$text .= '<option value="FFA" '.($this->getField('MatchType') == "FFA" ? 'selected="selected"' : '') .'>'.EB_LADDERM_L131.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		//<!-- Rating Type -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L117.'</b><div class="smalltext">'.EB_LADDERM_L118.'</div></td>
		<td class="eb_td1">
		<div id="radio2">
		';
		$text .= '<input class="tbox" type="radio" id="radio21" size="40" name="ladderrankingtype" '.($this->getField('RankingType') == "Classic" ? 'checked="checked"' : '').' value="Classic" /><label for="radio21">'.EB_LADDERM_L119.'</label>';
		$text .= '<input class="tbox" type="radio" id="radio22" size="40" name="ladderrankingtype" '.($this->getField('RankingType') == "CombinedStats" ? 'checked="checked"' : '').' value="CombinedStats" /><label for="radio22">'.EB_LADDERM_L120.'</label>';
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Match report userclass -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L21.'</b></td>
		<td class="eb_td1"><select class="tbox" name="laddermatchreportuserclass">';
		$text .= '<option value="'.eb_UC_LADDER_PLAYER.'" '.($this->getField('match_report_userclass') == eb_UC_LADDER_PLAYER ? 'selected="selected"' : '') .'>'.EB_LADDERM_L22.'</option>';
		$text .= '<option value="'.eb_UC_LADDER_MODERATOR.'" '.($this->getField('match_report_userclass') == eb_UC_LADDER_MODERATOR ? 'selected="selected"' : '') .'>'.EB_LADDERM_L23.'</option>';
		$text .= '<option value="'.eb_UC_LADDER_OWNER.'" '.($this->getField('match_report_userclass') == eb_UC_LADDER_OWNER ? 'selected="selected"' : '') .'>'.EB_LADDERM_L24.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		//<!-- Allow Quick Loss Report -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L25.'</b></td>
		<td class="eb_td1">
		<div>
		';
		$text .= '<input class="tbox" type="checkbox" name="ladderallowquickloss"';
		if ($this->getField('quick_loss_report') == TRUE)
		{
			$text .= ' checked="checked"/>';
		}
		else
		{
			$text .= '/>';
		}
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Allow Score -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L26.'</b></td>
		<td class="eb_td1">
		<div>
		';
		$text .= '<input class="tbox" type="checkbox" name="ladderallowscore"';
		if ($this->getField('AllowScore') == TRUE)
		{
			$text .= ' checked="checked"/>';
		}
		else
		{
			$text .= '/>';
		}
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Match Approval -->
		$q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES
		." WHERE (".TBL_MATCHS.".Ladder = '".$this->getField('LadderID')."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_MATCHS.".Status = 'pending')";
		$result = $sql->db_Query($q);
		$row = mysql_fetch_array($result);
		$nbrMatchesPending = $row['NbrMatches'];


		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L108.'</b><div class="smalltext">'.EB_LADDERM_L109.'</div></td>
		<td class="eb_td1">
		<div>';
		$text .= '<select class="tbox" name="laddermatchapprovaluserclass">';
		$text .= '<option value="'.eb_UC_NONE.'" '.(($this->getField('MatchesApproval') == eb_UC_NONE) ? 'selected="selected"' : '') .'>'.EB_LADDERM_L113.'</option>';
		$text .= '<option value="'.eb_UC_LADDER_PLAYER.'" '.((($this->getField('MatchesApproval') & eb_UC_LADDER_PLAYER)!=0) ? 'selected="selected"' : '') .'>'.EB_LADDERM_L112.'</option>';
		$text .= '<option value="'.eb_UC_LADDER_MODERATOR.'" '.((($this->getField('MatchesApproval') & eb_UC_LADDER_MODERATOR)!=0) ? 'selected="selected"' : '') .'>'.EB_LADDERM_L111.'</option>';
		$text .= '<option value="'.eb_UC_LADDER_OWNER.'" '.((($this->getField('MatchesApproval') & eb_UC_LADDER_OWNER)!=0) ? 'selected="selected"' : '') .'>'.EB_LADDERM_L110.'</option>';
		$text .= '</select>';
		$text .= ($nbrMatchesPending>0) ? '<div><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/>&nbsp;<b>'.$nbrMatchesPending.'&nbsp;'.EB_LADDER_L64.'</b></div>' : '';
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Allow Draws -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L27.'</b></td>
		<td class="eb_td1">
		<div>
		';
		$text .= '<input class="tbox" type="checkbox" name="ladderallowdraw"';
		if ($this->getField('AllowDraw') == TRUE)
		{
			$text .= ' checked="checked"/>';
		}
		else
		{
			$text .= '/>';
		}
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Points -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L28.'</b></td>
		<td class="eb_td1">
		<table class="table_left">
		<tr>
		<td>'.EB_LADDERM_L29.'</td>
		<td>'.EB_LADDERM_L30.'</td>
		<td>'.EB_LADDERM_L31.'</td>
		</tr>
		<tr>
		<td>
		<div><input class="tbox" type="text" name="ladderpointsperwin" value="'.$this->getField('PointsPerWin').'"/></div>
		</td>
		<td>
		<div><input class="tbox" type="text" name="ladderpointsperdraw" value="'.$this->getField('PointsPerDraw').'"/></div>
		</td>
		<td>
		<div><input class="tbox" type="text" name="ladderpointsperloss" value="'.$this->getField('PointsPerLoss').'"/></div>
		</td>
		</tr>
		</table>
		';
		$text .= '
		</td>
		</tr>
		';

		//<!-- Maps -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L125.'</b></td>
		<td class="eb_td1">
		<div>
		';
		$text .= '<input class="tbox" type="text" name="laddermaxmapspermatch" size="2" value="'.$this->getField('MaxMapsPerMatch').'"';
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Start Date -->
		if($this->getField('Start_timestamp')!=0)
		{
			$start_timestamp_local = $this->getField('Start_timestamp') + TIMEOFFSET;
			$date_start = date("m/d/Y h:i A", $start_timestamp_local);
		}
		else
		{
			$date_start = "";
		}

		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L32.'</b></td>
		<td class="eb_td1">
		<table class="table_left">
		<tr>
		<td>
		<div><input class="button" type="button" value="'.EB_LADDERM_L34.'" onclick="clearStartDate(this.form);"/></div>
		</td>
		<td>
		<img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_start" style="cursor: pointer; border: 1px solid red;" title="'.EB_LADDERM_L33.'"
		';
		$text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
		$text .= '
		</td>
		<td>
		<div><input class="tbox" type="text" name="startdate" id="f_date_start"  value="'.$date_start.'" readonly="readonly" /></div>
		</td>
		</tr>
		</table>
		';
		$text .= '
		<script type="text/javascript">
		Calendar.setup({
		inputField     :    "f_date_start",      // id of the input field
		ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
		showsTime      :    true,            // will display a time selector
		button         :    "f_trigger_start",   // trigger for the calendar (button ID)
		singleClick    :    true,           // single-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
		});
		</script>
		</td>
		</tr>
		';

		//<!-- End Date -->
		if($this->getField('End_timestamp')!=0)
		{
			$end_timestamp_local = $this->getField('End_timestamp') + TIMEOFFSET;
			$date_end = date("m/d/Y h:i A", $end_timestamp_local);
		}
		else
		{
			$date_end = "";
		}
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L35.'</b></td>
		<td class="eb_td1">
		<table class="table_left">
		<tr>
		<td>
		<div><input class="button" type="button" value="'.EB_LADDERM_L34.'" onclick="clearEndDate(this.form);"/></div>
		</td>
		<td>
		<img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_end" style="cursor: pointer; border: 1px solid red;" title="'.EB_LADDERM_L33.'"
		';
		$text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
		$text .= '
		</td>
		<td>
		<div><input class="tbox" type="text" name="enddate" id="f_date_end"  value="'.$date_end.'" readonly="readonly" /></div>
		</td>
		</tr>
		</table>
		';
		$text .= '
		<script type="text/javascript">
		Calendar.setup({
		inputField     :    "f_date_end",      // id of the input field
		ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
		showsTime      :    true,            // will display a time selector
		button         :    "f_trigger_end",   // trigger for the calendar (button ID)
		singleClick    :    true,           // single-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
		});
		</script>
		</td>
		</tr>
		';

		//<!-- Description -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L36.'</b></td>
		<td class="eb_td1">
		';
		$text .= '<textarea class="tbox" id="ladderdescription" name="ladderdescription" cols="70" '.$insertjs.'>'.$this->getField('Description').'</textarea>';
		if (!e_WYSIWYG)
		{
			$text .= '<br />'.display_help("helpb",1);
		}
		$text .= '
		</td>
		</tr>';

		//<!-- Rules -->
		$text .= '
		<tr>
		<td class="eb_td1 eb_w40"><b>'.EB_LADDERM_L38.'</b></td>
		<td class="eb_td1">
		';
		$text .= '<textarea class="tbox" id="ladderrules" name="ladderrules" cols="70" '.$insertjs.'>'.$this->getField('Rules').'</textarea>';
		if (!e_WYSIWYG)
		{
			$text .= '<br />'.display_help("helpb",1);
		}
		$text .= '
		</td>
		</tr>
		</tbody>
		</table>
		';

		//<!-- Save Button -->
		$text .= '
		<table><tr><td>
		<div>
		'.ebImageTextButton('laddersettingssave', 'disk.png', EB_LADDERM_L37).'
		</div>
		</td></tr></table>

		</form>';

		return $text;
	}

	function initStats()
	{
		global $sql;

		$last_id = $this->id;
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'ELO')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue)
		VALUES ('$last_id', 'Skill', 4)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue, InfoOnly)
		VALUES ('$last_id', 'GamesPlayed', 1, 1)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue)
		VALUES ('$last_id', 'VictoryRatio', 3)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'WinDrawLoss')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'VictoryPercent')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'UniqueOpponents')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'OpponentsELO')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue, InfoOnly)
		VALUES ('$last_id', 'Streaks', 2, 1)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'Score')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'ScoreAgainst')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'ScoreDiff')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
		VALUES ('$last_id', 'Points')";
		$result = $sql->db_Query($q);
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
