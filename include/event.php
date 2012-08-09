<?php
// functions for events.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/updatestats.php");
require_once(e_PLUGIN."ebattles/include/updateteamstats.php");
require_once(e_PLUGIN."ebattles/include/brackets.php");

class Event extends DatabaseTable
{
	protected $tablename = TBL_EVENTS;
	protected $primary_key = "EventID";

	/***************************************************************************************
	Functions
	***************************************************************************************/
	function setDefaultFields()
	{
		$this->setField('Game', 1);
		$this->setField('Type', 'One Player Ladder');
		$this->setField('Format', 'Single Elimination');
		$this->setField('MatchType', '');
		$this->setField('nbr_games_to_rank', '1');
		$this->setField('nbr_team_games_to_rank', '1');
		$this->setField('ELO_default', ELO_DEFAULT);
		$this->setField('ELO_K', ELO_K);
		$this->setField('ELO_M', ELO_M);
		$this->setField('TS_default_mu', floatToSQL(TS_Mu0));
		$this->setField('TS_default_sigma', floatToSQL(TS_sigma0));
		$this->setField('TS_beta', floatToSQL(TS_beta));
		$this->setField('TS_epsilon', floatToSQL(TS_epsilon));
		$this->setField('IsChanged', '1');
		$this->setField('AllowDraw', '0');
		$this->setField('AllowForfeit', '0');
		$this->setField('ForfeitWinLossUpdate', '0');
		$this->setField('ForfeitWinPoints', PointsPerWin_DEFAULT);
		$this->setField('ForfeitLossPoints', PointsPerDraw_DEFAULT);
		$this->setField('AllowScore', '0');
		$this->setField('PointsPerWin', PointsPerWin_DEFAULT);
		$this->setField('PointsPerDraw', PointsPerDraw_DEFAULT);
		$this->setField('PointsPerLoss', PointsPerLoss_DEFAULT);
		$this->setField('match_report_userclass', eb_UC_EVENT_MODERATOR);
		$this->setField('match_replay_report_userclass', eb_UC_EVENT_PLAYER);
		$this->setField('quick_loss_report', '0');
		$this->setField('hide_ratings_column', '0');
		$this->setField('MatchesApproval', eb_UC_NONE);
		$this->setField('RankingType', 'Classic');
		$this->setField('Visibility', eb_UC_NONE);
		$this->setField('Status', 'draft');
		$this->setField('PlayersApproval', eb_UC_NONE);
		$this->setField('ChallengesEnable', '0');
		$this->setField('MaxDatesPerChallenge', eb_MAX_CHALLENGE_DATES);
		$this->setField('MaxMapsPerMatch', eb_MAX_MAPS_PER_MATCH);
		$this->setField('MaxNumberPlayers', '16');
	}

	function resetPlayers()
	{
		global $sql;

		$q = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Event = '".$this->fields['EventID']."')";
		$result = $sql->db_Query($q);
		$num_players = mysql_numrows($result);
		if ($num_players!=0)
		{
			for($j=0; $j< $num_players; $j++)
			{
				$PlayerID  = mysql_result($result,$j, TBL_PLAYERS.".PlayerID");
				$q2 = "UPDATE ".TBL_PLAYERS
				." SET ELORanking = '".$this->fields['ELO_default']."',"
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
		." WHERE (".TBL_TEAMS.".Event = '".$this->fields['EventID']."')";
		$result = $sql->db_Query($q);
		$num_teams = mysql_numrows($result);
		if ($num_teams!=0)
		{
			for($j=0; $j< $num_teams; $j++)
			{
				$TeamID  = mysql_result($result,$j, TBL_TEAMS.".TeamID");
				$q2 = "UPDATE ".TBL_TEAMS
				." SET ELORanking = '".$this->fields['ELO_default']."',"
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
		." WHERE (".TBL_MATCHS.".Event = '".$this->fields['EventID']."')";
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
		." WHERE (".TBL_CHALLENGES.".Event = '".$this->fields['EventID']."')";
		$result2 = $sql->db_Query($q2);
	}

	function deletePlayers()
	{
		global $sql;
		$q2 = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Event = '".$this->fields['EventID']."')";
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
		." WHERE (".TBL_TEAMS.".Event = '".$this->fields['EventID']."')";
		$result3 = $sql->db_Query($q3);
	}

	function deleteMods()
	{
		global $sql;
		$q3 = "DELETE FROM ".TBL_EVENTMODS
		." WHERE (".TBL_EVENTMODS.".Event = '".$this->fields['EventID']."')";
		$result3 = $sql->db_Query($q3);
	}

	function deleteStatsCats()
	{
		global $sql;
		$q3 = "DELETE FROM ".TBL_STATSCATEGORIES
		." WHERE (".TBL_STATSCATEGORIES.".Event = '".$this->fields['EventID']."')";
		$result3 = $sql->db_Query($q3);
	}

	function deleteEvent()
	{
		global $sql;
		$this->deleteMatches();
		$this->deleteChallenges();
		$this->deletePlayers();
		$this->deleteTeams();
		$this->deleteMods();
		$this->deleteStatsCats();
		$q3 = "DELETE FROM ".TBL_EVENTS
		." WHERE (".TBL_EVENTS.".EventID = '".$this->fields['EventID']."')";
		$result3 = $sql->db_Query($q3);
	}

	/**
	* eventScoresUpdate - Re-calculate the scores and players of an event
	*/
	function eventScoresUpdate($current_match)
	{
		global $sql;
		global $time;

		//echo "dbg: current_match $current_match<br>";

		$numMatchsPerUpdate = 10;

		$q = "SELECT ".TBL_MATCHS.".*"
		." FROM ".TBL_MATCHS
		." WHERE (".TBL_MATCHS.".Event = '".$this->fields['EventID']."')"
		." AND (".TBL_MATCHS.".Status = 'active')"
		." ORDER BY TimeReported";
		$result = $sql->db_Query($q);
		$num_matches = mysql_numrows($result);

		if ($current_match > $num_matches)
		{
			switch($this->fields['Type'])
			{
				case "One Player Ladder":
				updateStats($this->fields['EventID'], $time, true);
				break;
				case "Team Ladder":
				updateStats($this->fields['EventID'], $time, true);
				updateTeamStats($this->fields['EventID'], $time, true);
				break;
				case "Clan Ladder":
				updateTeamStats($this->fields['EventID'], $time, true);
				break;
				case "One Player Tournament":
				case "Clan Tournament":
				break;
				default:
			}
			echo "Done.";
			echo '<META HTTP-EQUIV="Refresh" Content="0; URL=eventmanage.php?eventid='.$this->fields['EventID'].'">';
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
					updateStats($this->fields['EventID'], $this->fields['StartDateTime'], false);
					break;
					case "Team Ladder":
					updateStats($this->fields['EventID'], $this->fields['StartDateTime'], false);
					updateTeamStats($this->fields['EventID'], $this->fields['StartDateTime'], false);
					break;
					case "Clan Ladder":
					updateTeamStats($this->fields['EventID'], $this->fields['getStartDateTime'], false);
					break;
					case "One Player Tournament":
					case "Clan Tournament":
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
						updateStats($this->fields['EventID'], $this->fields['StartDateTime'], false);
						break;
						case "Team Ladder":
						$match->match_players_update();
						updateStats($this->fields['EventID'], $this->fields['StartDateTime'], false);
						updateTeamStats($this->fields['EventID'], $this->fields['StartDateTime'], false);
						break;
						case "Clan Ladder":
						$match->match_teams_update();
						updateTeamStats($this->fields['EventID'], $this->fields['StartDateTime'], false);
						break;
						case "One Player Tournament":
						case "Clan Tournament":
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

			echo '<form name="updateform" action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$this->fields['EventID'].'" method="post">';
			echo '<input type="hidden" name="match" value="'.$next_match.'"/>';
			echo '<input type="hidden" name="eventupdatescores" value="1"/>';
			echo '</form>';
			echo '<script language="javascript">document.updateform.submit()</script>';

			ob_end_flush();
		}
		exit;
	}

	/**
	* eventAddPlayer - add a user to an event
	*/
	function eventAddPlayer($user, $team = 0, $notify)
	{
		global $sql;
		global $time;

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
			// Need to create gamer before coming here (i.e. when player joins a division.)
			// If the gamer does not exist, create one.
			$gamerID = updateGamer($user, $this->fields['Game'], $username, "");
		}
		else
		{
			$gamerID = mysql_result($result, 0, TBL_GAMERS.".GamerID");
		}

		// Is the user already signed up for the team?
		$q = "SELECT ".TBL_PLAYERS.".*"
		." FROM ".TBL_PLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_PLAYERS.".Event = '".$this->fields['EventID']."')"
		."   AND (".TBL_PLAYERS.".Team = '$team')"
		."   AND (".TBL_PLAYERS.".Gamer = '$gamerID')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		echo "num_rows: $num_rows<br>";
		if ($num_rows==0)
		{
			$q = " INSERT INTO ".TBL_PLAYERS."(Event,Gamer,Team,ELORanking,TS_mu,TS_sigma,Joined)
			VALUES (".$this->fields['EventID'].",$gamerID,$team,".$this->fields['ELO_default'].",".$this->fields['TS_default_mu'].",".$this->fields['TS_default_sigma'].",$time)";
			$sql->db_Query($q);
			echo "player created, query: $q<br>";
			$this->setFieldDB('IsChanged', 1);

			if ($notify)
			{
				$sendto = $user;
				$subject = SITENAME." ".$this->fields['Name'];
				$message = EB_EVENTS_L26.$username.EB_EVENTS_L27.$this->fields['Name'].EB_EVENTS_L29.EB_EVENTS_L31;
				sendNotification($sendto, $subject, $message, $fromid=0);

				// Send email
				//$message = EB_EVENTS_L26.$username.EB_EVENTS_L27.$this->fields['Name'].EB_EVENTS_L30."<a href='".SITEURLBASE.e_PLUGIN_ABS."ebattles/eventinfo.php?eventid=$this->fields['EventID']'>$this->fields['Name']</a>.".EB_EVENTS_L31.USERNAME.EB_EVENTS_L32;
				$message = EB_EVENTS_L26.$username.EB_EVENTS_L27.$this->fields['Name'].EB_EVENTS_L30.SITEURLBASE.e_PLUGIN_ABS."ebattles/eventinfo.php?eventid=".$this->fields['EventID'].EB_EVENTS_L31;
				require_once(e_HANDLER."mail.php");
				sendemail($useremail, $subject, $message);
			}
		}
	}


	/**
	* eventAddDivision - add a division to an event
	*/
	function eventAddDivision($div_id, $notify)
	{
		global $sql;
		global $time;

		//$add_players = ( $this->fields['Type'] == "Clan Ladder" ? false : true);
		$add_players = true;

		// Is the division signed up
		$q = "SELECT ".TBL_TEAMS.".*"
		." FROM ".TBL_TEAMS
		." WHERE (".TBL_TEAMS.".Event = '".$this->fields['EventID']."')"
		." AND (".TBL_TEAMS.".Division = '$div_id')";
		$result = $sql->db_Query($q);
		$numTeams = mysql_numrows($result);
		if($numTeams == 0)
		{
			$q = "INSERT INTO ".TBL_TEAMS."(Event,Division,ELORanking,TS_mu,TS_sigma)
			VALUES (".$this->fields['EventID'].",$div_id,".$this->fields['ELO_default'].",".$this->fields['TS_default_mu'].",".$this->fields['TS_default_sigma'].")";
			$sql->db_Query($q);
			$team_id =  mysql_insert_id();

			if ($add_players == true)
			{
				// All members of this division will automatically be signed up to this event
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
						$this->eventAddPlayer($user_id, $team_id, $notify);
					}
					$this->setFieldDB('IsChanged', 1);
				}
			}
		}
	}

	function updateResults($results) {
		$new_results = serialize($results);
		$this->setField('Results', $new_results);
	}

	function resetResults()
	{
		$this->setField('Results', '');
	}

	function updateRounds($rounds) {
		$new_rounds = serialize($rounds);
		$this->setField('Rounds', $new_rounds);
	}

	function updateMapPool($mapPool) {
		$i = 0;
		$mapString = '';
		foreach ($mapPool as $key=>$map)
		{
			if ($i > 0) $mapString .= ',';
			$mapString .= $map;
			$i++;
		}

		$this->setField('MapPool', $mapString);
	}

	function displayEventSettingsForm($create=false)
	{
		global $sql;
		
		if (e_WYSIWYG)
		{
			$insertjs = "rows='15'";
		}
		else
		{
			require_once(e_HANDLER."ren_help.php");
			$insertjs = "rows='5' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
		}

		/* Nbr players */
		$q = "SELECT COUNT(*) as NbrPlayers"
		." FROM ".TBL_PLAYERS
		." WHERE (".TBL_PLAYERS.".Event = '".$this->fields['EventID']."')";
		$result = $sql->db_Query($q);
		$row = mysql_fetch_array($result);
		$nbrplayers = $row['NbrPlayers'];
		
		/* Nbr Teams */
		$q = "SELECT COUNT(*) as NbrTeams"
		." FROM ".TBL_TEAMS
		." WHERE (Event = '".$this->fields['EventID']."')";
		$result = $sql->db_Query($q);
		$row = mysql_fetch_array($result);
		$nbrteams = $row['NbrTeams'];
	
		$type = $this->fields['Type'];
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

		$text .= "
		<script type='text/javascript'>
		<!--//
		// Forms
		$(function() {
		$( '#radio2' ).buttonset();
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
		function kick_player(v)
		{
		document.getElementById('kick_player').value=v;
		document.getElementById('playersform').submit();
		}
		function ban_player(v)
		{
		document.getElementById('ban_player').value=v;
		document.getElementById('playersform').submit();
		}
		function unban_player(v)
		{
		document.getElementById('unban_player').value=v;
		document.getElementById('playersform').submit();
		}
		function del_player_games(v)
		{
		document.getElementById('del_player_games').value=v;
		document.getElementById('playersform').submit();
		}
		function del_player_awards(v)
		{
		document.getElementById('del_player_awards').value=v;
		document.getElementById('playersform').submit();
		}
		//-->
		</script>
		";

		$text .= '<form id="form-event-settings" action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$this->getField('EventID').'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		//<!-- Event Name -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L15.'<span class="required">*</span></td>
		<td class="eb_td">
		<div><input class="tbox required" type="text" size="40" name="eventname" value="'.$this->getField('Name').'"/></div>
		</td>
		</tr>
		';

		//<!-- Event Password -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L16.'</td>
		<td class="eb_td">
		<div><input class="tbox" type="text" size="40" name="eventpassword" value="'.$this->getField('password').'"/></div>
		</td>
		</tr>
		';
		//<!-- Event Game -->
		$disabled_str = ($nbrplayers+$nbrteams==0) ? '' : 'disabled="disabled"';

		$q = "SELECT ".TBL_GAMES.".*"
		." FROM ".TBL_GAMES
		." WHERE (GameID = '".$this->getField('Game')."')";
		$result = $sql->db_Query($q);
		$gIcon  = mysql_result($result,$i, TBL_GAMES.".Icon");

		$q = "SELECT ".TBL_GAMES.".*"
		." FROM ".TBL_GAMES
		." ORDER BY Name";
		$result = $sql->db_Query($q);
		/* Error occurred, return given name by default */
		$numGames = mysql_numrows($result);
		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L17.'</td>';
		$text .= '<td class="eb_td">';
		$text .= '<img '.getGameIconResize($gIcon).'/>&nbsp;';
		$text .= '<select class="tbox" name="eventgame" '.$disabled_str.'>';
		for($i=0; $i<$numGames; $i++){
			$gname  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
			if ($this->getField('Game') == $gid)
			{
				$text .= '<option value="'.$gid.'" selected="selected">'.htmlspecialchars($gname).'</option>';
				$ematchtypes = explode(",", mysql_result($result,$i, TBL_GAMES.".MatchTypes"));
			}
			else
			{
				$text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
			}
		}
		$text .= '</select>';
		$text .= '</td></tr>';

		//<!-- Type -->
		$disabled_str = ($create==true) ? '' : 'disabled="disabled"';
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L18.'</td>
		<td class="eb_td"><select class="tbox" name="eventtype" '.$disabled_str.'>';
		$text .= '<option value="One Player Ladder" '.($this->getField('Type') == "One Player Ladder" ? 'selected="selected"' : '').'>'.EB_EVENTS_L22.'</option>';
		$text .= '<option value="Team Ladder" '.($this->getField('Type') == "Team Ladder" ? 'selected="selected"' : '').'>'.EB_EVENTS_L23.'</option>';
		$text .= '<option value="Clan Ladder" '.($this->getField('Type') == "Clan Ladder" ? 'selected="selected"' : '').'>'.EB_EVENTS_L25.'</option>';
		$text .= '<option value="One Player Tournament" '.($this->getField('Type') == "One Player Tournament" ? 'selected="selected"' : '').'>'.EB_EVENTS_L33.'</option>';
		$text .= '<option value="Clan Tournament" '.($this->getField('Type') == "Clan Tournament" ? 'selected="selected"' : '').'>'.EB_EVENTS_L35.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		if ($create==false)
		{
			switch($event_type)
			{
				case "Ladder":
				break;
				case "Tournament":
				//<!-- Format -->
				$disabled_str = ($this->getField('Status')!='active') ? '' : 'disabled="disabled"';
				$text .= '
				<tr>
				<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L152.'</td>
				<td class="eb_td"><select class="tbox" name="eventformat" '.$disabled_str.'>';
				$text .= '<option value="Single Elimination" '.($this->getField('Format') == "Single Elimination" ? 'selected="selected"' : '').'>'.EB_EVENTM_L153.'</option>';
				$text .= '<option value="Double Elimination" '.($this->getField('Format') == "Double Elimination" ? 'selected="selected"' : '').'>'.EB_EVENTM_L158.'</option>';
				$text .= '</select>
				</td>
				</tr>
				';
				break;
			}

			//<!-- Match Type -->
			//$disabled_str = ($this->getField('Status')!='active') ? '' : 'disabled="disabled"';
			$disabled_str = '';
			$text .= '
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L132.'</td>
			<td class="eb_td">
			<div>
			';
			$text .= '<select class="tbox" name="eventmatchtype" '.$disabled_str.'>';
			$text .= '<option value="" '.($this->getField('MatchType') == "" ? 'selected="selected"' : '') .'>-</option>';
			foreach($ematchtypes as $matchtype)
			{
				if ($matchtype!='') {
					$text .= '<option value="'.$matchtype.'" '.(($this->getField('MatchType') == $matchtype) ? 'selected="selected"' : '') .'>'.$matchtype.'</option>';
				}
			}
			$text .= '</select>
			</div>
			</td>
			</tr>';
		}

		if ($create==false)
		{
			//<!-- Max Number of Players -->
			$text .= '
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L126.'</td>
			<td class="eb_td">
			<div>
			';
			switch($event_type)
			{
				case "Ladder":
				$text .= '<input class="tbox" type="text" name="eventmaxnumberplayers" size="2" value="'.$this->getField('MaxNumberPlayers').'"/>';
				break;
				case "Tournament":
				$disabled_str = ($this->getField('Status')!='active') ? '' : 'disabled="disabled"';
				$text .= '<select class="tbox" name="eventmaxnumberplayers" '.$disabled_str.'>';
				switch ($this->getField('Format'))
				{
					case 'Double Elimination':
					$text .= '<option value="4" '.($this->getField('MaxNumberPlayers') == "4" ? 'selected="selected"' : '') .'>4</option>';
					$text .= '<option value="8" '.($this->getField('MaxNumberPlayers') == "8" ? 'selected="selected"' : '') .'>8</option>';
					break;
					case 'Single Elimination':
					$text .= '<option value="2" '.($this->getField('MaxNumberPlayers') == "2" ? 'selected="selected"' : '') .'>2</option>';
					$text .= '<option value="4" '.($this->getField('MaxNumberPlayers') == "4" ? 'selected="selected"' : '') .'>4</option>';
					$text .= '<option value="8" '.($this->getField('MaxNumberPlayers') == "8" ? 'selected="selected"' : '') .'>8</option>';
					$text .= '<option value="16" '.($this->getField('MaxNumberPlayers') == "16" ? 'selected="selected"' : '') .'>16</option>';
					$text .= '<option value="32" '.($this->getField('MaxNumberPlayers') == "32" ? 'selected="selected"' : '') .'>32</option>';
					$text .= '<option value="64" '.($this->getField('MaxNumberPlayers') == "64" ? 'selected="selected"' : '') .'>64</option>';
					$text .= '<option value="128" '.($this->getField('MaxNumberPlayers') == "128" ? 'selected="selected"' : '') .'>128</option>';
					default:
					break;
				}
	
				$text .= '</select>';
			}
			$text .= '
			</div>
			</td>
			</tr>';

			//<!-- Rating Type -->
			switch($event_type)
			{
				case "Ladder":
				$text .= '
				<tr>
				<td class="eb_td eb_tdc1 eb_w40" title="'.EB_EVENTM_L118.'">'.EB_EVENTM_L117.'</td>
				<td class="eb_td">
				<div id="radio2">
				';
				$text .= '<input class="tbox" type="radio" id="radio21" size="40" name="eventrankingtype" '.($this->getField('RankingType') == "Classic" ? 'checked="checked"' : '').' value="Classic" /><label for="radio21">'.EB_EVENTM_L119.'</label>';
				$text .= '<input class="tbox" type="radio" id="radio22" size="40" name="eventrankingtype" '.($this->getField('RankingType') == "CombinedStats" ? 'checked="checked"' : '').' value="CombinedStats" /><label for="radio22">'.EB_EVENTM_L120.'</label>';
				$text .= '
				</div>
				</td>
				</tr>
				';
				break;
			}
		}

		//<!-- Match report userclass -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L21.'</td>
		<td class="eb_td"><select class="tbox" name="eventmatchreportuserclass">';
		$text .= '<option value="'.eb_UC_EVENT_PLAYER.'" '.($this->getField('match_report_userclass') == eb_UC_EVENT_PLAYER ? 'selected="selected"' : '') .'>'.EB_EVENTM_L22.'</option>';
		$text .= '<option value="'.eb_UC_EVENT_MODERATOR.'" '.($this->getField('match_report_userclass') == eb_UC_EVENT_MODERATOR ? 'selected="selected"' : '') .'>'.EB_EVENTM_L23.'</option>';
		$text .= '<option value="'.eb_UC_EVENT_OWNER.'" '.($this->getField('match_report_userclass') == eb_UC_EVENT_OWNER ? 'selected="selected"' : '') .'>'.EB_EVENTM_L24.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		//<!-- Match replay report userclass -->
		/*
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L134.'</td>
		<td class="eb_td"><select class="tbox" name="eventmatchreplayreportuserclass">';
		$text .= '<option value="'.eb_UC_EVENT_PLAYER.'" '.($this->getField('match_replay_report_userclass') == eb_UC_EVENT_PLAYER ? 'selected="selected"' : '') .'>'.EB_EVENTM_L22.'</option>';
		$text .= '<option value="'.eb_UC_EVENT_MODERATOR.'" '.($this->getField('match_replay_report_userclass') == eb_UC_EVENT_MODERATOR ? 'selected="selected"' : '') .'>'.EB_EVENTM_L23.'</option>';
		$text .= '<option value="'.eb_UC_EVENT_OWNER.'" '.($this->getField('match_replay_report_userclass') == eb_UC_EVENT_OWNER ? 'selected="selected"' : '') .'>'.EB_EVENTM_L24.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';
		*/

		if ($create==false)
		{
			//<!-- Allow Quick Loss Report -->
			switch($event_type)
			{
				case "Ladder":
				$text .= '
				<tr>
				<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L25.'</td>
				<td class="eb_td">
				<div>
				';
				$text .= '<input class="tbox" type="checkbox" name="eventallowquickloss"';
				if ($this->getField('quick_loss_report') == true)
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
				break;
			}
		}
		//<!-- Allow Score -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L26.'</td>
		<td class="eb_td">
		<div>
		';
		$text .= '<input class="tbox" type="checkbox" name="eventallowscore"';
		if ($this->getField('AllowScore') == true)
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
		</tr>';

		//<!-- Match Approval -->
		$q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES
		." WHERE (".TBL_MATCHS.".Event = '".$this->getField('EventID')."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_MATCHS.".Status = 'pending')";
		$result = $sql->db_Query($q);
		$row = mysql_fetch_array($result);
		$nbrMatchesPending = $row['NbrMatches'];


		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40" title="'.EB_EVENTM_L109.'">'.EB_EVENTM_L108.'</td>
		<td class="eb_td">
		<div>';
		$text .= '<select class="tbox" name="eventmatchapprovaluserclass">';
		$text .= '<option value="'.eb_UC_NONE.'" '.(($this->getField('MatchesApproval') == eb_UC_NONE) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L113.'</option>';
		$text .= '<option value="'.eb_UC_EVENT_PLAYER.'" '.((($this->getField('MatchesApproval') & eb_UC_EVENT_PLAYER)!=0) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L112.'</option>';
		$text .= '<option value="'.eb_UC_EVENT_MODERATOR.'" '.((($this->getField('MatchesApproval') & eb_UC_EVENT_MODERATOR)!=0) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L111.'</option>';
		$text .= '<option value="'.eb_UC_EVENT_OWNER.'" '.((($this->getField('MatchesApproval') & eb_UC_EVENT_OWNER)!=0) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L110.'</option>';
		$text .= '</select>';
		$text .= ($nbrMatchesPending>0) ? '<div><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/>&nbsp;'.$nbrMatchesPending.'&nbsp;'.EB_EVENT_L64.'</div>' : '';
		$text .= '
		</div>
		</td>
		</tr>
		';

		if ($create==false)
		{
			//<!-- Allow Draws -->
			switch($event_type)
			{
				case "Ladder":
				$text .= '
				<tr>
				<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L27.'</td>
				<td class="eb_td">
				<div>';
				$text .= '<input class="tbox" type="checkbox" name="eventallowdraw"';
				if ($this->getField('AllowDraw') == true)
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
				<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L28.'</td>
				<td class="eb_td">
				<table class="table_left">
				<tr>
				<td>'.EB_EVENTM_L29.'</td>
				<td>'.EB_EVENTM_L30.'</td>
				<td>'.EB_EVENTM_L31.'</td>
				</tr>
				<tr>
				<td>
				<div><input class="tbox" type="text" name="eventpointsperwin" value="'.$this->getField('PointsPerWin').'"/></div>
				</td>
				<td>
				<div><input class="tbox" type="text" name="eventpointsperdraw" value="'.$this->getField('PointsPerDraw').'"/></div>
				</td>
				<td>
				<div><input class="tbox" type="text" name="eventpointsperloss" value="'.$this->getField('PointsPerLoss').'"/></div>
				</td>
				</tr>
				</table>
				</td>
				</tr>
				';
				break;
			}
		}
		if ($create==false)
		{
			//<!-- Allow Forfeits -->
			$text .= '
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L127.'</td>
			<td class="eb_td">
			<div>';
			$text .= '<input class="tbox" type="checkbox" name="eventallowforfeit"';
			if ($this->getField('AllowForfeit') == true)
			{
				$text .= ' checked="checked"/>';
			}
			else
			{
				$text .= '/>';
			}
			$text .= EB_EVENTM_L128;
			$text .= '</div>';
			switch($event_type)
			{
				case "Ladder":
				$text .= '<div>';
				$text .= '<input class="tbox" type="checkbox" name="eventForfeitWinLossUpdate"';
				if ($this->getField('ForfeitWinLossUpdate') == true)
				{
					$text .= ' checked="checked"/>';
				}
				else
				{
					$text .= '/>';
				}
				$text .= EB_EVENTM_L129;
				$text .= '</div>';
				$text .= '
				<div>
				<table class="table_left">
				<tr>
				<td>'.EB_EVENTM_L130.'</td>
				<td>'.EB_EVENTM_L131.'</td>
				</tr>
				<tr>
				<td>
				<div><input class="tbox" type="text" name="eventforfeitwinpoints" value="'.$this->getField('ForfeitWinPoints').'"/></div>
				</td>
				<td>
				<div><input class="tbox" type="text" name="eventforfeitlosspoints" value="'.$this->getField('ForfeitLossPoints').'"/></div>
				</td>
				</tr>
				</table>
				</div>
				';
				break;
			}
			$text .= '
			</td>
			</tr>
			';

			//<!-- Maps -->
			$text .= '
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L125.'</td>
			<td class="eb_td">
			<div>
			';
			$text .= '<input class="tbox" type="text" name="eventmaxmapspermatch" size="2" value="'.$this->getField('MaxMapsPerMatch').'"/>';
		}
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Start Date -->
		if($this->getField('StartDateTime')!=0)
		{
			$startdatetime_local = $this->getField('StartDateTime') + TIMEOFFSET;
			$date_start = date("m/d/Y h:i A", $startdatetime_local);
		}
		else
		{
			$date_start = "";
		}

		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L32.'<span class="required">*</span></td>
		<td class="eb_td">
		<table class="table_left">
		<tr>
		<td>
		<div><input class="button" type="button" value="'.EB_EVENTM_L34.'" onclick="clearStartDate(this.form);"/></div>
		</td>
		<td>
		<div><input class="tbox timepicker required" type="text" name="startdate" id="f_date_start" value="'.$date_start.'" readonly="readonly" /></div>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		';

		//<!-- End Date -->
		if($this->getField('EndDateTime')!=0)
		{
			$enddatetime_local = $this->getField('EndDateTime') + TIMEOFFSET;
			$date_end = date("m/d/Y h:i A", $enddatetime_local);
		}
		else
		{
			$date_end = "";
		}
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L35.'</td>
		<td class="eb_td">
		<table class="table_left">
		<tr>
		<td>
		<div><input class="button" type="button" value="'.EB_EVENTM_L34.'" onclick="clearEndDate(this.form);"/></div>
		</td>
		<td>
		<div><input class="tbox timepicker" type="text" name="enddate" id="f_date_end"  value="'.$date_end.'" readonly="readonly" /></div>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		';

		if ($create==false)
		{
			//<!-- Rounds -->
			switch($event_type)
			{
				case "Tournament":
				$matchups = $this->getMatchups();
				$nbrRounds = count($matchups);

				$text .= '
				<tr>
				<td class="eb_td eb_tdc1 eb_w40">'.($nbrRounds - 1).' '.EB_EVENTM_L4.'</td>
				<td class="eb_td">';

				$rounds = unserialize($this->getFieldHTML('Rounds'));
				if (!isset($rounds)) $rounds = array();
				$text .= '<table class="table_left"><tbody>';
				$text .= '<tr>';
				$text .= '<th>'.EB_EVENTM_L144.'</th>';
				$text .= '<th>'.EB_EVENTM_L145.'</th>';
				$text .= '<th>'.EB_EVENTM_L146.'</th>';
				$text .= '</tr>';
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

					$text .= '<tr>';
					$text .= '<td>'.EB_EVENTM_L144.' '.$round.'</td>';
					$text .= '<td><input class="tbox" type="text" size="40" name="round_title_'.$round.'" value="'.$rounds[$round]['Title'].'"/></td>';
					$text .= '<td><select class="tbox" name="round_bestof_'.$round.'">';
					$text .= '<option value="1" '.($rounds[$round]['BestOf'] == "1" ? 'selected="selected"' : '') .'>1</option>';
					$text .= '<option value="3" '.($rounds[$round]['BestOf'] == "3" ? 'selected="selected"' : '') .'>3</option>';
					$text .= '<option value="5" '.($rounds[$round]['BestOf'] == "5" ? 'selected="selected"' : '') .'>5</option>';
					$text .= '<option value="7" '.($rounds[$round]['BestOf'] == "7" ? 'selected="selected"' : '') .'>7</option>';
					$text .= '</select></td>';
					$text .= '</tr>';
				}
				$text .= '</tbody></table>';
				$text .= '</td></tr>';
				//var_dump($rounds);

				//<!-- Map Pool -->
				if ($this->getID() != 0)
				{
					$mapPool = explode(",", $this->getField('MapPool'));
					$nbrMapsInPool = count($mapPool);

					$text .= '
					<tr>
					<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L147.'</td>
					<td class="eb_td">';
					$text .= '<table class="table_left">';
					foreach($mapPool as $key=>$map)
					{
						if ($map!='')
						{
							$mapID = $map;
							$q_Maps = "SELECT ".TBL_MAPS.".*"
							." FROM ".TBL_MAPS
							." WHERE (".TBL_MAPS.".MapID = '$mapID')";
							$result_Maps = $sql->db_Query($q_Maps);
							$mapName  = mysql_result($result_Maps,0, TBL_MAPS.".Name");
							$text .= '<tr>';
							$text .= '<td>'.$mapName.'</td>';
							$text .= '<td>';
							$text .= '<div>';
							$text .= ebImageTextButton('eventdeletemap', 'delete.png', EB_EVENTM_L150, 'negative jq-button', '', '', 'value="'.$key.'"');
							$text .= '</div>';
							$text .= '</td>';
							$text .= '</tr>';
						} else {
							$text .= '<tr>';
							$text .= '<td><div>';
							$text .= EB_EVENTM_L148;
							$text .= '</div></td>';
							$text .= '</tr>';
						}
					}
					$text .= '</table>';

					// List of all Maps
					$q_Maps = "SELECT ".TBL_MAPS.".*"
					." FROM ".TBL_MAPS
					." WHERE (".TBL_MAPS.".Game = '".$this->getField('Game')."')";
					$result_Maps = $sql->db_Query($q_Maps);
					$numMaps = mysql_numrows($result_Maps);
					if ($numMaps > $nbrMapsInPool)
					{
						$text .= '
						<table class="table_left">
						<tr>';
						$text .= '<td><select class="tbox" name="map">';
						for($map=0;$map < $numMaps;$map++)
						{
							$mID = mysql_result($result_Maps,$map , TBL_MAPS.".MapID");
							$mImage = mysql_result($result_Maps,$map , TBL_MAPS.".Image");
							$mName = mysql_result($result_Maps,$map , TBL_MAPS.".Name");
							$mDescrition = mysql_result($result_Maps,$map , TBL_MAPS.".Description");

							$isMapInMapPool = false;
							foreach($mapPool as $poolmap)
							{
								if ($mID==$poolmap) {
									$isMapInMapPool = true;
								}
							}

							if($isMapInMapPool == false) {
								$text .= '<option value="'.$mID.'"';
								$text .= '>'.$mName.'</option>';
							}
						}
						$text .= '</select></td>';

						$text .= '
						<td>
						<div>
						'.ebImageTextButton('eventaddmap', 'add.png', EB_EVENTM_L149).'
						</div>
						</td>
						</tr>
						</table>
						';
					}
					$text .= '</td></tr>';
				}
				break;
			}
		}
		//<!-- Description -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L36.'</td>
		<td class="eb_td">
		';
		$text .= '<textarea class="tbox" id="eventdescription" name="eventdescription" cols="70" '.$insertjs.'>'.$this->getField('Description').'</textarea>';
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
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L38.'</td>
		<td class="eb_td">
		';
		$text .= '<textarea class="tbox" id="eventrules" name="eventrules" cols="70" '.$insertjs.'>'.$this->getField('Rules').'</textarea>';
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
		<table><tbody><tr><td>
		<div>
		'.ebImageTextButton('eventsettingssave', 'disk.png', EB_EVENTM_L37).'
		</div>
		</td></tr></tbody></table>
		</form>';

		return $text;
	}

	function initStats()
	{
		global $sql;

		$last_id = $this->id;
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'ELO')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMaxValue)
		VALUES ('$last_id', 'Skill', 4)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMaxValue, InfoOnly)
		VALUES ('$last_id', 'GamesPlayed', 1, 1)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMaxValue)
		VALUES ('$last_id', 'VictoryRatio', 3)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'WinDrawLoss')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'VictoryPercent')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'UniqueOpponents')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'OpponentsELO')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMaxValue, InfoOnly)
		VALUES ('$last_id', 'Streaks', 2, 1)";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'Score')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'ScoreAgainst')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'ScoreDiff')";
		$result = $sql->db_Query($q);
		$q =
		"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
		VALUES ('$last_id', 'Points')";
		$result = $sql->db_Query($q);
	}

	/*
	function brackets()
	inputs:
	- format: 'Single elimination', ...
	- maxNbrPlayers: max number of players
	- teams[player]
	 . Name
	 . PlayerID
	- results[round][matchup]
	 . winner: not played/top/bottom
	 . bye: true/false
	 . topWins
	 . bottomWins
	 . matchs[match]
	   . played
	   . match_id
	- rounds[round]
	 . Title
	 . BestOf

	variables:
	- $matchup[round][matchup][0(top)-1(bottom)] unserialized from file
	. '': empty
	. 'T1-16': team index
	. 'Wr,m': winner of matchup r/m
	. 'Lr,m': loser of matchup r/m
	. 'Pr,m': loser of matchup r/m if necessary
	- brackets[row][column] -> actual html content of a table cell
	- content[round][matchup][0(top)-1(bottom)]: content of the top/bottom cells for a matchup
	. same as $matchup
	. 'E': empty
	. 'N': not needed
	*/
	function brackets($scheduleNextMatches = false, $delete_match_id = 0) {
		global $sql;
		global $time;
		global $pref;
		global $tp;

		$type = $this->fields['Type'];
		$format = $this->fields['Format'];
		$event_id = $this->fields['EventID'];
		$teams = $this->getTeams();
		$results = unserialize($this->getFieldHTML('Results'));
		
		// TODO: check for error (return false)
		$rounds = unserialize($this->getFieldHTML('Rounds'));

		$nbrTeams=count($teams);

		$matchups = $this->getMatchups();
		$nbrRounds = count($matchups);
		$nbrRows = 4*count($matchups[1]);

		/* */
		$brackets = array();
		$content= array();
		// Initialize grid
		for ($row = 1; $row <= $nbrRows; $row ++){
			for ($column = 1; $column <= $nbrRounds; $column++){
				$brackets[$row][2*$column-1] = '<td class="grid empty"></td>';
				$brackets[$row][2*$column] = '<td class="grid border-none"></td>';
			}
		}
		
		$rowspan = 1;
		for ($round = 1; $round <= $nbrRounds; $round++){
			$nbrMatchups = count($matchups[$round]);
			$rounds[$round]['nbrMatchups'] = 0;

			if ($round < $nbrRounds)
			{
				for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
					if (!isset($results[$round][$matchup]['winner'])) $results[$round][$matchup]['winner'] = 'not played';
					if (!isset($results[$round][$matchup]['bye'])) $results[$round][$matchup]['bye'] = false;
					if (!isset($matchups[$round][$matchup]['deleted'])) $matchups[$round][$matchup]['deleted'] = false;
					
					$matchup_deleted = false;
					for($match = 0; $match < 2; $match++){
						$matchupString = $matchups[$round][$matchup][$match];
						$content[$round][$matchup][$match] = ($matchupString == '') ? 'E' : $matchupString;
						if ($matchupString == '')
						{
							$row = findRow($round, $matchup, $match);
							$matchupsRows[$round][$matchup][$match] = $row;
						} else
						{
							if ($matchupString[0]=='T') {
								$row = findRow($round, $matchup, $match);
								$matchupsRows[$round][$matchup][$match] = $row;
								$team = substr($matchupString,1);
								if ($team > $nbrTeams){
									$content[$round][$matchup][$match] = 'E';
								}
							}
							if ($matchupString[0]=='W') {
								$matchupArray = explode(',',substr($matchupString,1));
								$matchupRound = $matchupArray[0];
								$matchupMatchup = $matchupArray[1];

								// Get result of matchup
								$winner = $results[$matchupRound][$matchupMatchup]['winner'];
								$bye = $results[$matchupRound][$matchupMatchup]['bye'];
								$deleted = $matchups[$matchupRound][$matchupMatchup]['deleted'];

								$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
								$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
								$row = ($rowBottom - $rowTop)/2 + $rowTop;

								// If result is not a bye, we draw the grid
								if($bye != true){
									$brackets[$rowTop][2*$round-2] = '<td class="grid border-top"></td>';
									$brackets[$rowBottom][2*$round-2] = '<td class="grid border-bottom"></td>';
									for ($i = $rowTop+1; $i < $rowBottom; $i++){
										$brackets[$i][2*$round-2] = '<td class="grid border-vertical"></td>';
									}
									for ($i = $rowTop+2; $i < $rowBottom; $i++){
										$brackets[$i][2*$round-3] = '';
									}
									$brackets[$row][2*$round-2] = '<td class="grid border-middle"></td>';
								}
								
								if($deleted == true){
									$matchup_deleted = true;
								}

								$matchupsRows[$round][$matchup][$match] = $row;
								if ($winner == 'top') {
									$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
								}
								else if ($winner == 'bottom') {
									$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
								}
							}
							if (($matchupString[0]=='L')||($matchupString[0]=='P')) {
								$matchupArray = explode(',',substr($matchupString,1));
								$matchupRound = $matchupArray[0];
								$matchupMatchup = $matchupArray[1];

								// Get result of matchup
								$winner = $results[$matchupRound][$matchupMatchup]['winner'];
								$bye = $results[$matchupRound][$matchupMatchup]['bye'];
								$deleted = $matchups[$matchupRound][$matchupMatchup]['deleted'];

								$row = findRow($round, $matchup, $match);

								if($deleted == true){
									$matchup_deleted = true;
								}

								$matchupsRows[$round][$matchup][$match] = $row;
								if ($winner == 'top') {
									$loser = $content[$matchupRound][$matchupMatchup][1];
									if ($loser[0] == 'T')
									{
										$team = substr($loser,1);
										//echo "M$round,$matchup: L2: $team,".$teams[$team-1]['loss'].'<br>';
										if ($teams[$team-1]['loss'] > 1)
										{
											$content[$round][$matchup][$match] = 'N';
										}
										else
										{
											$content[$round][$matchup][$match] = $loser;
										}
									}
									else
									{
										$content[$round][$matchup][$match] = 'E';
									}
								}
								else if ($winner == 'bottom') {
									$loser = $content[$matchupRound][$matchupMatchup][0];
									if ($loser[0] == 'T')
									{
										$team = substr($loser,1);
										//echo "M$round,$matchup: L2: $team,".$teams[$team-1]['loss'].'<br>';
										if ($teams[$team-1]['loss'] > 1)
										{
											$content[$round][$matchup][$match] = 'N';
										}
										else
										{
											$content[$round][$matchup][$match] = $loser;
										}
									}
									else
									{
										$content[$round][$matchup][$match] = 'E';
									}
								}
								else {
								}
								//echo "L$matchupRound,$matchupMatchup: ".$content[$round][$matchup][$match]."<br>";
							}
						}

						switch ($content[$round][$matchup][$match])
						{
							case 'E':
							$results[$round][$matchup]['winner'] = ($match==0) ? 'bottom' : 'top';
							$results[$round][$matchup]['bye'] = true;
							break;
							case 'N':
							$results[$round][$matchup]['winner'] = ($match==0) ? 'bottom' : 'top';
							break;
						}
					}
					
					/* Nbr of matches in the matchup */
					$nbr_matchs = count($results[$round][$matchup]['matchs']);
					
					/* Match deletion*/
					if($nbr_matchs > 0)
					{
						$match_deleted = false;
						for($match=0;$match < $nbr_matchs; $match++)
						{
							if(($results[$round][$matchup]['matchs'][$match]['match_id'] == $delete_match_id)
							||($match_deleted == true)
							||($matchup_deleted == true))
							{
								/*
								var_dump($results[$round][$matchup]);
								var_dump($delete_match_id);
								var_dump($match_deleted);
								var_dump($matchup_deleted);
								*/
								
								$current_match_id = $results[$round][$matchup]['matchs'][$match]['match_id'];
								//echo "match ".$current_match_id." deleted (M$round,$matchup,$match)<br>";
								
								$current_match = new Match($current_match_id);
								$current_match->deleteMatchScores($event_id);
								
								$results[$round][$matchup]['winner'] = 'not played';
								unset($results[$round][$matchup]['matchs'][$match]);
								
								$match_deleted = true;
							}
						}
					}
					
					if ($match_deleted==true)
					{
						$matchups[$round][$matchup]['deleted'] = true;
						$nbr_matchs = count($results[$round][$matchup]['matchs']);
					}
					
					/* Update results */
					if(($scheduleNextMatches == true)
					&&($content[$round][$matchup][0][0]=='T')
					&&($content[$round][$matchup][1][0]=='T')
					&&($results[$round][$matchup]['winner'] == 'not played')) {
						// Matchup not finished yet
						/* Calculate:
						 $results[$round][$matchup]['topWins']
						 $results[$round][$matchup]['bottomWins']
						 $results[$round][$matchup]['matchs'][$match]['winner']
						 $results[$round][$matchup]['winner']
						*/
						
						$results[$round][$matchup]['topWins'] = 0;
						$results[$round][$matchup]['bottomWins'] = 0;
						if ($nbr_matchs > 0) 
						{
							for($match = 0; $match < $nbr_matchs; $match++)
							{
								$current_match_id =  $results[$round][$matchup]['matchs'][$match]['match_id'];
								$current_match_winner =  $results[$round][$matchup]['matchs'][$match]['winner'];
								
								if ($current_match_winner == 'not played')
								{
									$current_match = new Match($current_match_id);
									if ($current_match->getField('Status') == 'active')
									{
										// The match has been reported
										// Need to check who won.
										// Get the scores for this match
										switch($type)
										{
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
											." WHERE (".TBL_MATCHS.".MatchID = '$current_match_id')"
											." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
											." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
											." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
											." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
											." ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";
											break;
											case "Clan Tournament":
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
											." WHERE (".TBL_MATCHS.".MatchID = '$current_match_id')"
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
			
										if ($numScores>0)
										{
											$i = 0;
											switch($type)
											{
												case "One Player Tournament":
												$pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
												break;
												case "Clan Tournament":
												$pid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
												break;
												default:
											}
											$pscoreid  = mysql_result($result,$i, TBL_SCORES.".ScoreID");
											$prank  = mysql_result($result,$i, TBL_SCORES.".Player_Rank");
											$pMatchTeam  = mysql_result($result,$i, TBL_SCORES.".Player_MatchTeam");
			
											$teamTop    = substr($content[$round][$matchup][0],1);
											$teamBottom = substr($content[$round][$matchup][1],1);
			
											$teamTopID = $teams[$teamTop-1]['PlayerID'];
											$teamBottomID = $teams[$teamBottom-1]['PlayerID'];
			
											if ($teamTopID == $pid)
											{
												$current_match_winner = 'top';
											}
											else
											{
												$current_match_winner = 'bottom';
											}
											$results[$round][$matchup]['matchs'][$match]['winner'] = $current_match_winner;
										}
									}										
								}
											
								if($current_match_winner == 'top')
								{
									$results[$round][$matchup]['topWins'] += 1;
									if ($results[$round][$matchup]['topWins'] == ($rounds[$round]['BestOf'] + 1)/2)
									{
										$results[$round][$matchup]['winner'] = 'top';
										//echo "Match $matchs, top won<br>";
										if ($round == $nbrRounds-1)
										{
											// top has won the tournament
											$this->setFieldDB('Status', 'finished');

											// Award: player wins tournament
											switch($type)
											{
												case "One Player Tournament":
												$q_Award = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
												VALUES ($teamTopID,'PlayerWonTournament',$time)";
												break;
												case "Clan Tournament":
												$q_Award = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
												VALUES ($teamTopID,'TeamWonTournament',$time)";
												break;
												default:
											}
											$result_Award = $sql->db_Query($q_Award);
										}
									}
								}
								if($current_match_winner == 'bottom')
								{
									$results[$round][$matchup]['bottomWins'] += 1;
									if ($results[$round][$matchup]['bottomWins'] == ($rounds[$round]['BestOf'] + 1)/2)
									{
										$results[$round][$matchup]['winner'] = 'bottom';
										//echo "Match $matchs, bottom won<br>";
										if ($round == $nbrRounds-1)
										{
											// bottom has won the tournament
											$this->setFieldDB('Status', 'finished');

											// Award: player wins tournament
											switch($type)
											{
												case "One Player Tournament":
												$q_Award = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
												VALUES ($teamBottomID,'PlayerWonTournament',$time)";
												break;
												case "Clan Tournament":
												$q_Award = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
												VALUES ($teamBottomID,'TeamWonTournament',$time)";
												break;
												default:
											}
											$result_Award = $sql->db_Query($q_Award);
										}
									}
								}
							}
						}
					}

					$topWins = $results[$round][$matchup]['topWins'];
					$bottomWins = $results[$round][$matchup]['bottomWins'];
					if($topWins > $bottomWins)
					{
						$topWins .= '+';
						$bottomWins .= '-';
					}
					if($topWins < $bottomWins)
					{
						$topWins .= '-';
						$bottomWins .= '+';
					}

					if (($content[$round][$matchup][0]!='E')&&($content[$round][$matchup][1]!='E')) {
						if ($results[$round][$matchup]['winner'] == 'top') {
							$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], $topWins, 'winner');
							$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], $bottomWins, 'loser');
							$loser = $content[$round][$matchup][1];
							if ($loser[0] == 'T')
							{
								$team = substr($loser,1);
								$teams[$team-1]['loss'] += 1;
								//echo "M$round,$matchup: L1: $team,".$teams[$team-1]['loss'].'<br>';
							}
						} else if ($results[$round][$matchup]['winner'] == 'bottom') {
							$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], $topWins, 'loser');
							$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], $bottomWins, 'winner');
							$loser = $content[$round][$matchup][0];
							if ($loser[0] == 'T')
							{
								$team = substr($loser,1);
								$teams[$team-1]['loss'] += 1;
								//echo "M$round,$matchup: L1: $team,".$teams[$team-1]['loss'].'<br>';
							}
						} else {
							$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], $topWins);
							$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], $bottomWins);
						}
						$brackets[$matchupsRows[$round][$matchup][0]+1][2*$round-1] = '<td rowspan="'.$rowspan.'" class="match-details" title="'.'Matchup '.$round.','.$matchup.'"></td>';
						$rounds[$round]['nbrMatchups']++;
					}


					if(($scheduleNextMatches == true)
					&&($content[$round][$matchup][0][0]=='T')
					&&($content[$round][$matchup][1][0]=='T')
					&&($results[$round][$matchup]['winner'] == 'not played')) {
						$current_match = $results[$round][$matchup]['matchs'][$nbr_matchs-1];
						//var_dump($current_match);

						if((!isset($current_match)) || ($current_match['winner'] != 'not played'))
						{
							/*
							echo 'M'.$round.','.$matchup.':<br>';
							var_dump($results[$round][$matchup]);
							var_dump($content[$round][$matchup]);
							*/

							// Need to schedule the next match
							// Create Match ------------------------------------------
							$reported_by = $this->getField('Owner');
							$time_reported = $time;
							$comments = '';
							$time_scheduled = $time_reported;

							$q =
							"INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported, Comments, Status, TimeScheduled)
							VALUES ($event_id,'$reported_by', $time_reported, '$comments', 'scheduled', $time_scheduled)";
							$result = $sql->db_Query($q);

							$last_id = mysql_insert_id();
							$match_id = $last_id;

							// Create Scores ------------------------------------------
							$teamTop    = substr($content[$round][$matchup][0],1);
							$teamBottom = substr($content[$round][$matchup][1],1);

							switch($type)
							{
								case "One Player Tournament":
								$playerTopID = $teams[$teamTop-1]['PlayerID'];
								$playerBottomID = $teams[$teamBottom-1]['PlayerID'];
								$teamTopID = 0;
								$teamBottomID = 0;
								break;
								case "Clan Tournament":
								$playerTopID = 0;
								$playerBottomID = 0;
								$teamTopID = $teams[$teamTop-1]['PlayerID'];
								$teamBottomID = $teams[$teamBottom-1]['PlayerID'];
								break;
							}
							$q =
							"INSERT INTO ".TBL_SCORES."(MatchID,Player,Team,Player_MatchTeam,Player_Rank)
							VALUES ($match_id,$playerTopID,$teamTopID,1,1)
							";
							$result = $sql->db_Query($q);

							$q =
							"INSERT INTO ".TBL_SCORES."(MatchID,Player,Team,Player_MatchTeam,Player_Rank)
							VALUES ($match_id,$playerBottomID,$teamBottomID,2,2)
							";
							$result = $sql->db_Query($q);

							$match_array = array();
							$match_array['winner'] = 'not played';
							$match_array['match_id'] = $match_id;
							$results[$round][$matchup]['matchs'][$nbr_matchs] = $match_array;

							// Send notification to all the players.
							$fromid = 0;
							$subject = SITENAME." ".EB_MATCHR_L52;

							switch($type)
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

								break;
								case "Clan Ladder":
								case "Clan Tournament":
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

								break;
								default:
							}

							if($numPlayers > 0)
							{
								for($j=0; $j < $numPlayers; $j++)
								{
									$pname = mysql_result($result_Players, $j, TBL_USERS.".user_name");
									$pemail = mysql_result($result_Players, $j, TBL_USERS.".user_email");
									$message = EB_MATCHR_L53.$pname.EB_MATCHR_L54.EB_MATCHR_L55.$this->getField('Name').EB_MATCHR_L56;
									$sendto = mysql_result($result_Players, $j, TBL_USERS.".user_id");
									$sendtoemail = mysql_result($result_Players, $j, TBL_USERS.".user_email");
									if (check_class($pref['eb_pm_notifications_class']))
									{
										// Send PM
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
						}
					}
					
					/*
					echo 'M'.$round.','.$matchup.':<br>';
					var_dump($results[$round][$matchup]);
					var_dump($content[$round][$matchup]);
					echo '- matchup: top='.$matchups[$round][$matchup][0].', bottom='.$matchups[$round][$matchup][1].'<br>';
					echo '- content: top='.$content[$round][$matchup][0].', bottom='.$content[$round][$matchup][1].'<br>';
					echo '- winner='.$results[$round][$matchup]['winner'].', bye='.$results[$round][$matchup]['bye'].'<br>';
					*/
				}
			}
			else
			{
				/* Last round, no match */
				for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
					if (!isset($results[$round][$matchup]['winner'])) $results[$round][$matchup]['winner'] = '';
					if (!isset($results[$round][$matchup]['bye'])) $results[$round][$matchup]['bye'] = false;
					$match = 0;
					$matchupString = $matchups[$round][$matchup][$match];
					$content[$round][$matchup][$match] = ($matchupString == '') ? 'E' : $matchupString;
					if ($matchupString[$match]='W') {

						$matchupArray = explode(',',substr($matchupString,1));
						$matchupRound = $matchupArray[0];
						$matchupMatchup = $matchupArray[1];

						$winner = $results[$matchupRound][$matchupMatchup]['winner'];
						$bye = $results[$matchupRound][$matchupMatchup]['bye'];

						$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
						$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
						$row = ($rowBottom - $rowTop)/2 + $rowTop;

						if($bye != 'bye'){
							$brackets[$rowTop][2*$round-2] = '<td class="grid border-top"></td>';
							$brackets[$rowBottom][2*$round-2] = '<td class="grid border-bottom"></td>';
							for ($i = $rowTop+1; $i < $rowBottom; $i++){
								$brackets[$i][2*$round-2] = '<td class="grid border-vertical"></td>';
							}
							for ($i = $rowTop+2; $i < $rowBottom; $i++){
								$brackets[$i][2*$round-3] = '';
							}
							$brackets[$row][2*$round-2] = '<td class="grid border-middle"></td>';
						}

						$matchupsRows[$round][$matchup][$match] = $rowTop;
						if ($winner == 'top') {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
						} else if ($winner == 'bottom') {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
						}

						$topWins = $results[$round][$matchup]['topWins'];
						$bottomWins = $results[$round][$matchup]['bottomWins'];
						if($topWins > $bottomWins)
						{
							$topWins .= '+';
							$bottomWins .= '-';
						}
						if($topWins < $bottomWins)
						{
							$topWins .= '-';
							$bottomWins .= '+';
						}
						if ($content[$round][$matchup][0][0] != 'E') {
							$brackets[$row][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][$match], $topWins, 'victor');
						} else {
							$brackets[$row][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][$match], $bottomWins);
						}
					}
				}
			}
			$rowspan = 2*$rowspan + 1;
		}

		$bracket_html = '<div id="panel_brackets">';
		$bracket_html .= '<div id="brackets_frame" style="height: 400px;">';
		$bracket_html .= '<div id="brackets">';
		$bracket_html .= '<table class="brackets">';

		$bracket_html .= '<thead><tr>';
		for ($i = 1; $i < $nbrRounds; $i++) {
			if ($rounds[$i]['nbrMatchups'] != 0)
			{
				$bracket_html .= '<th colspan="2" title="'.EB_EVENTM_L146.' '.$rounds[$i]['BestOf'].'">'.$rounds[$i]['Title'].'</th>';
			}
			else
			{
				$bracket_html .= '<th colspan="2"></th>';
			}
		}
		$bracket_html .= '</tr></thead>';

		$bracket_html .= '<tbody>';
		for ($row = 1; $row <= $nbrRows; $row ++){
			$bracket_html .= '<tr>';
			for ($column = 1; $column <= 2*$nbrRounds; $column++){
				$bracket_html .= $brackets[$row][$column];
			}
			$bracket_html .= '</tr>';
		}
		$bracket_html .= '</tbody>';
		$bracket_html .= '</table>';
		$bracket_html .= '</div>'; // brackets
		$bracket_html .= '</div>'; // brackets_frame
		$bracket_html .= '<div class="clearer"></div>';
		$bracket_html .= '</div>'; // panel-brackets

		/*
		var_dump($rounds);
		var_dump($matchups);
		var_dump($results);
		var_dump($content);
		var_dump($teams);
		*/
		if($scheduleNextMatches == true) {
			$this->updateResults($results);
			$this->updateFieldDB('Results');
		}
		
		return array($bracket_html);

	}

	function eventTypeToString()
	{
		$type = $this->getField('Type');
		switch($type)
		{
			case "One Player Ladder":
			$text = EB_EVENTS_L22;
			break;
			case "Team Ladder":
			$text = EB_EVENTS_L23;
			break;
			case "Clan Ladder":
			$text = EB_EVENTS_L25;
			break;
			case "One Player Tournament":
			$text = EB_EVENTS_L33;
			break;
			case "Clan Tournament":
			$text = EB_EVENTS_L35;
			break;
			default:
			$text = $type;
		}
		return $text;
	}

	function eventStatusToString()
	{
		$status = $this->getField('Status');
		switch($status)
		{
			case 'draft':
			$text = EB_EVENTM_L136;
			break;
			case 'signup':
			$text = EB_EVENTM_L138;
			break;
			case 'checkin':
			$text = EB_EVENTM_L139;
			break;
			case 'active':
			$text = EB_EVENTM_L140;
			break;
			case 'finished':
			$text = EB_EVENTM_L141;
			break;
			default:
			$text = $status;
		}
		return $text;
	}

	function eventStatusToTimeComment()
	{
		global $time;

		$time_comment = '';
		switch($this->getField('Status'))
		{
			case 'draft':
			break;
			case 'signup':
			$time_comment = EB_EVENT_L2.'&nbsp;'.get_formatted_timediff($time, $this->getField('StartDateTime'));
			break;
			case 'checkin':
			$time_comment = EB_EVENT_L2.'&nbsp;'.get_formatted_timediff($time, $this->getField('StartDateTime'));
			break;
			case 'active':
			if ($this->getField('EndDateTime') != 0)
			{
				$time_comment = EB_EVENT_L3.'&nbsp;'.get_formatted_timediff($time, $this->getField('EndDateTime'));
			}
			break;
			case 'finished':
			$time_comment = EB_EVENT_L4;
			break;
		}
		return $time_comment;
	}

	function getTeams()
	{
		global $sql;

		$teams = array();
		$type = $this->getField('Type');
		switch($type)
		{
			case 'One Player Tournament':
			$q_Players = "SELECT ".TBL_GAMERS.".*, "
			.TBL_PLAYERS.".*"
			." FROM ".TBL_GAMERS.", "
			.TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_PLAYERS.".Event = '".$this->getField('EventID')."')"
			." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY ".TBL_PLAYERS.".Seed, ".TBL_PLAYERS.".Joined";
			$result = $sql->db_Query($q_Players);
			$nbrPlayers = mysql_numrows($result);
			for ($player = 0; $player < $nbrPlayers; $player++)
			{
				$playerID = mysql_result($result, $player, TBL_PLAYERS.".PlayerID");
				$gamerID = mysql_result($result, $player, TBL_GAMERS.".GamerID");
				$gamer = new Gamer($gamerID);

				$pname = $gamer->getField('Name');
				$pugid = $gamer->getField('UniqueGameID');
				$pavatar = mysql_result($result,$player, TBL_USERS.".user_image");
				$pteam  = mysql_result($result,$player , TBL_PLAYERS.".Team");
				list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);

				$teams[$player]['PlayerID'] = $playerID;
				$teams[$player]['Name'] = $pname;
				$teams[$player]['UniqueGameID'] = $pugid;
				$teams[$player]['Avatar'] = $pavatar;
			}
			break;
			case 'Clan Tournament':
			$q_Teams = "SELECT ".TBL_CLANS.".*, "
			.TBL_TEAMS.".*, "
			.TBL_DIVISIONS.".* "
			." FROM ".TBL_CLANS.", "
			.TBL_TEAMS.", "
			.TBL_DIVISIONS
			." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
			." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
			." AND (".TBL_TEAMS.".Event = '".$this->getField('EventID')."')"
			." ORDER BY ".TBL_TEAMS.".Seed, ".TBL_TEAMS.".Joined";
			$result = $sql->db_Query($q_Teams);
			$nbrTeams = mysql_numrows($result);
			for ($team = 0; $team < $nbrTeams; $team++)
			{
				$pteam  = mysql_result($result,$team, TBL_TEAMS.".TeamID");
				list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);

				$teams[$team]['PlayerID'] = $pteam;
				$teams[$team]['Name'] = $pclan;
				$teams[$team]['UniqueGameID'] = '';
				$teams[$team]['Avatar'] = $pavatar;
			}
			break;
		}
		//var_dump($teams);
		return $teams;
	}

	function shuffleSeeds()
	{
		global $sql;

		$type = $this->getField('Type');
		switch($type)
		{
			case 'One Player Tournament':
			$q_Players = "SELECT ".TBL_GAMERS.".*, "
			.TBL_PLAYERS.".*"
			." FROM ".TBL_GAMERS.", "
			.TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_PLAYERS.".Event = '".$this->getField('EventID')."')"
			." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY ".TBL_PLAYERS.".Seed, ".TBL_PLAYERS.".Joined";
			$result = $sql->db_Query($q_Players);
			$nbrPlayers = mysql_numrows($result);

			$array_sort = array();
			for ($player = 0; $player < $nbrPlayers; $player++)
			{
				$array_sort[$player] = $player + 1;
			}
			shuffle($array_sort);
			for ($player = 0; $player < $nbrPlayers; $player++)
			{
				$pid  = mysql_result($result,$player, TBL_PLAYERS.".PlayerID");
				$q_2 = "UPDATE ".TBL_PLAYERS." SET Seed = '".$array_sort[$player]."' WHERE (PlayerID = '".$pid."')";
				$result_2 = $sql->db_Query($q_2);
			}
			break;
			case 'Clan Tournament':
			$q_Teams = "SELECT ".TBL_CLANS.".*, "
			.TBL_TEAMS.".*, "
			.TBL_DIVISIONS.".* "
			." FROM ".TBL_CLANS.", "
			.TBL_TEAMS.", "
			.TBL_DIVISIONS
			." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
			." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
			." AND (".TBL_TEAMS.".Event = '".$this->getField('EventID')."')"
			." ORDER BY ".TBL_TEAMS.".Seed, ".TBL_TEAMS.".Joined";
			$result = $sql->db_Query($q_Teams);
			$nbrTeams = mysql_numrows($result);

			$array_sort = array();
			for ($team = 0; $team < $nbrTeams; $team++)
			{
				$array_sort[$team] = $team + 1;
			}
			shuffle($array_sort);
			for ($team = 0; $team < $nbrTeams; $team++)
			{
				$tid  = mysql_result($result,$team, TBL_TEAMS.".TeamID");
				$q_2 = "UPDATE ".TBL_TEAMS." SET Seed = '".$array_sort[$team]."' WHERE (TeamID = '".$tid."')";
				$result_2 = $sql->db_Query($q_2);
			}
			break;
		}
	}
	
	function getMatchups()
	{
		$maxNbrPlayers = $this->fields['MaxNumberPlayers'];
		switch ($this->getField('Format'))
		{
			case 'Double Elimination':
			$file = 'include/brackets/de-'.$maxNbrPlayers.'.txt';
			break;
			case 'Single Elimination':
			default:
			$file = 'include/brackets/se-'.$maxNbrPlayers.'.txt';
			break;
		}
		$matchups = unserialize(implode('',file($file)));
		return $matchups;
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
