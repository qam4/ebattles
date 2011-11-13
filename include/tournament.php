<?php
// functions for tournaments.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');

class Tournament extends DatabaseTable
{
	protected $tablename = TBL_TOURNAMENTS;
	protected $primary_key = "TournamentID";

	/***************************************************************************************
	Functions
	***************************************************************************************/
	function setDefaultFields()
	{
		$this->setField('Game', 1);
		$this->setField('Type', 'Single Elimination');
		$this->setField('MatchType', '1v1');
		$this->setField('MaxNumberPlayers', 16);
		$this->setField('match_report_userclass', eb_UC_LADDER_MODERATOR);
		$this->setField('match_replay_report_userclass', eb_UC_LADDER_PLAYER);
		$this->setField('Status', 'draft');
	}

	/**
	* tournamentAddPlayer - add a user to a tournament
	*/
	function tournamentAddPlayer($user, $team = 0, $notify)
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
			// TODO: need to create gamer before coming here (i.e. when player joins a division.)
			echo "Error: no gamer";
			return;
		}
		else
		{
			$gamerID = mysql_result($result, 0, TBL_GAMERS.".GamerID");
		}

		// Is the user already signed up for the team?
		$q = "SELECT ".TBL_TPLAYERS.".*"
		." FROM ".TBL_TPLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_TPLAYERS.".Tournament = '".$this->fields['TournamentID']."')"
		."   AND (".TBL_TPLAYERS.".Team = '$team')"
		."   AND (".TBL_TPLAYERS.".Gamer = '$gamerID')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		echo "num_rows: $num_rows<br>";
		if ($num_rows==0)
		{
			$q = " INSERT INTO ".TBL_TPLAYERS."(Tournament,Gamer,Team,Joined)
			VALUES (".$this->fields['TournamentID'].",$gamerID,$team,$time)";
			$sql->db_Query($q);
			echo "player created, query: $q<br>";
			$q = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '".$this->fields['TournamentID']."')";
			$sql->db_Query($q);

			if ($notify)
			{
				$sendto = $user;
				$subject = SITENAME.$this->fields['Name'];
				$message = EB_TOURNAMENTS_L26.$username.EB_TOURNAMENTS_L27.$this->fields['Name'].EB_TOURNAMENTS_L29.EB_TOURNAMENTS_L31.USERNAME;
				sendNotification($sendto, $subject, $message, $fromid=0);

				// Send email
				//$message = EB_TOURNAMENTS_L26.$username.EB_TOURNAMENTS_L27.$this->fields['Name'].EB_TOURNAMENTS_L30."<a href='".SITEURLBASE.e_PLUGIN_ABS."ebattles/tournamentinfo.php?TournamentID=$this->fields['TournamentID']'>$this->fields['Name']</a>.".EB_TOURNAMENTS_L31.USERNAME.EB_TOURNAMENTS_L32;
				$message = EB_TOURNAMENTS_L26.$username.EB_TOURNAMENTS_L27.$this->fields['Name'].EB_TOURNAMENTS_L30.SITEURLBASE.e_PLUGIN_ABS."ebattles/tournamentinfo.php?TournamentID=$this->fields['TournamentID']".EB_TOURNAMENTS_L31.USERNAME;
				require_once(e_HANDLER."mail.php");
				sendemail($useremail, $subject, $message);
			}
		}
	}


	/**
	* tournamentAddDivision - add a division to a tournament
	*/
	function tournamentAddDivision($div_id, $notify)
	{
		global $sql;
		global $time;

		//$add_players = ( $this->fields['Type'] == "ClanWar" ? FALSE : TRUE);
		$add_players = TRUE;

		// Is the division signed up
		$q = "SELECT ".TBL_TTEAMS.".*"
		." FROM ".TBL_TTEAMS
		." WHERE (".TBL_TTEAMS.".Tournament = '".$this->fields['TournamentID']."')"
		." AND (".TBL_TTEAMS.".Division = '$div_id')";
		$result = $sql->db_Query($q);
		$numTeams = mysql_numrows($result);
		if($numTeams == 0)
		{
			$q = "INSERT INTO ".TBL_TTEAMS."(Tournament,Division,Joined)
			VALUES (".$this->fields['TournamentID'].",$div_id,$time)";
			$sql->db_Query($q);
			$team_id =  mysql_insert_id();

			if ($add_players == TRUE)
			{
				// All members of this division will automatically be signed up to this tournament
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
						$this->tournamentAddPlayer($user_id, $team_id, $notify);
					}
					$q4 = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '".$this->fields['TournamentID']."')";
					$result = $sql->db_Query($q4);
				}
			}
		}
	}

	function updateResults($results) {
		global $sql;

		$new_results = serialize($results);
		$this->setField('Results', $new_results);
	}

	function updateRounds($rounds) {
		global $sql;
		
		$new_rounds = serialize($rounds);
		$this->setField('Rounds', $new_rounds);
	}

	function updateMapPool($mapPool) {
		global $sql;

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

	function displayTournamentSettingsForm()
	{
		global $sql;
		// Specify if we use WYSIWYG for text areas
		global $e_wysiwyg;
		$e_wysiwyg	= "tournamentdescription,tournamentrules";  // set $e_wysiwyg before including HEADERF
		if (e_WYSIWYG)
		{
			$insertjs = "rows='25'";
		}
		else
		{
			require_once(e_HANDLER."ren_help.php");
			$insertjs = "rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
		}

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

		$text .= '<form id="form-tournament-settings" action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$this->getField('TournamentID').'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		//<!-- Tournament Name -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L15.'</td>
		<td class="eb_td">
		<div><input class="tbox" type="text" size="40" name="tournamentname" value="'.$this->getField('Name').'"/></div>
		</td>
		</tr>
		';

		//<!-- Tournament Password -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L16.'</td>
		<td class="eb_td">
		<div><input class="tbox" type="text" size="40" name="tournamentpassword" value="'.$this->getField('password').'"/></div>
		</td>
		</tr>
		';
		//<!-- Tournament Game -->

		$q = "SELECT ".TBL_GAMES.".*"
		." FROM ".TBL_GAMES
		." ORDER BY Name";
		$result = $sql->db_Query($q);
		/* Error occurred, return given name by default */
		$numGames = mysql_numrows($result);
		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L17.'</td>';
		$text .= '<td class="eb_td"><select class="tbox" name="tournamentgame">';
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
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L18.'</td>
		<td class="eb_td"><select class="tbox" name="tournamenttype">';
		$text .= '<option value="Single Elimination" '.($this->getField('Type') == "Single Elimination" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L19.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		//<!-- Match Type -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L126.'</td>
		<td class="eb_td"><select class="tbox" name="tournamentmatchtype">';
		$text .= '<option value="1v1" '.($this->getField('MatchType') == "1v1" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L127.'</option>';
		$text .= '<option value="2v2" '.($this->getField('MatchType') == "2v2" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L128.'</option>';
		$text .= '<option value="FFA" '.($this->getField('MatchType') == "FFA" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L131.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		//<!-- Max Number of Players -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L132.'</td>
		<td class="eb_td"><select class="tbox" name="tournamentmaxnumberplayers">';
		$text .= '<option value="4" '.($this->getField('MaxNumberPlayers') == "4" ? 'selected="selected"' : '') .'>4</option>';
		$text .= '<option value="8" '.($this->getField('MaxNumberPlayers') == "8" ? 'selected="selected"' : '') .'>8</option>';
		$text .= '<option value="16" '.($this->getField('MaxNumberPlayers') == "16" ? 'selected="selected"' : '') .'>16</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		/* for now
		//<!-- Match report userclass -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L21.'</td>
		<td class="eb_td"><select class="tbox" name="tournamentmatchreportuserclass">';
		$text .= '<option value="'.eb_UC_TOURNAMENT_PLAYER.'" '.($this->getField('match_report_userclass') == eb_UC_TOURNAMENT_PLAYER ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L22.'</option>';
		$text .= '<option value="'.eb_UC_TOURNAMENT_MODERATOR.'" '.($this->getField('match_report_userclass') == eb_UC_TOURNAMENT_MODERATOR ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L23.'</option>';
		$text .= '<option value="'.eb_UC_TOURNAMENT_OWNER.'" '.($this->getField('match_report_userclass') == eb_UC_TOURNAMENT_OWNER ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L24.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';
		//<!-- Match replay report userclass -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L134.'</td>
		<td class="eb_td"><select class="tbox" name="tournamentmatchreplayreportuserclass">';
		$text .= '<option value="'.eb_UC_TOURNAMENT_PLAYER.'" '.($this->getField('match_replay_report_userclass') == eb_UC_TOURNAMENT_PLAYER ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L22.'</option>';
		$text .= '<option value="'.eb_UC_TOURNAMENT_MODERATOR.'" '.($this->getField('match_replay_report_userclass') == eb_UC_TOURNAMENT_MODERATOR ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L23.'</option>';
		$text .= '<option value="'.eb_UC_TOURNAMENT_OWNER.'" '.($this->getField('match_replay_report_userclass') == eb_UC_TOURNAMENT_OWNER ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L24.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';
		*/

		/* for now
		//<!-- Match Approval -->
		$q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES
		." WHERE (".TBL_MATCHS.".Tournament = '".$this->getField('TournamentID')."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_MATCHS.".Status = 'pending')";
		$result = $sql->db_Query($q);
		$row = mysql_fetch_array($result);
		$nbrMatchesPending = $row['NbrMatches'];

		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L108.'<div class="smalltext">'.EB_TOURNAMENTM_L109.'</div></td>
		<td class="eb_td">
		<div>';
		$text .= '<select class="tbox" name="tournamentmatchapprovaluserclass">';
		$text .= '<option value="'.eb_UC_NONE.'" '.(($this->getField('MatchesApproval') == eb_UC_NONE) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L113.'</option>';
		$text .= '<option value="'.eb_UC_TOURNAMENT_PLAYER.'" '.((($this->getField('MatchesApproval') & eb_UC_TOURNAMENT_PLAYER)!=0) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L112.'</option>';
		$text .= '<option value="'.eb_UC_TOURNAMENT_MODERATOR.'" '.((($this->getField('MatchesApproval') & eb_UC_TOURNAMENT_MODERATOR)!=0) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L111.'</option>';
		$text .= '<option value="'.eb_UC_TOURNAMENT_OWNER.'" '.((($this->getField('MatchesApproval') & eb_UC_TOURNAMENT_OWNER)!=0) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L110.'</option>';
		$text .= '</select>';
		$text .= ($nbrMatchesPending>0) ? '<div><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/>&nbsp;'.$nbrMatchesPending.'&nbsp;'.EB_TOURNAMENT_L64.'</div>' : '';
		$text .= '
		</div>
		</td>
		</tr>
		';
		*/

		//<!-- Start Date -->
		if($this->getField('StartDateTime')!=0)
		{
			$StartDateTime_local = $this->getField('StartDateTime') + TIMEOFFSET;
			$date_start = date("m/d/Y h:i A", $StartDateTime_local);
		}
		else
		{
			$date_start = "";
		}

		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L32.'</td>
		<td class="eb_td">
		<table class="table_left">
		<tr>
		<td>
		<div><input class="tbox timepicker" type="text" name="startdate" id="f_date_start"  value="'.$date_start.'" readonly="readonly" /></div>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		';

		/*
		$text .= '
		<p>Test date/time field: <input id="test" /></p>
		';
		*/

		//<!-- Rounds -->
		switch ($this->getField('Type'))
		{
			default:
			$file = 'include/brackets/se-'.$this->getField('MaxNumberPlayers').'.txt';
			break;
		}
		$matchups = unserialize(implode('',file($file)));
		$nbrRounds = count($matchups);

		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.($nbrRounds - 1).' '.EB_TOURNAMENTM_L4.'</td>
		<td class="eb_td">';

		$rounds = unserialize($this->getField('Rounds'));
		if (!isset($rounds)) $rounds = array();
		$text .= '<table class="table_left"><tbody>';
		$text .= '<tr>';
		$text .= '<th>'.EB_TOURNAMENTM_L25.'</th>';
		$text .= '<th>'.EB_TOURNAMENTM_L26.'</th>';
		$text .= '<th>'.EB_TOURNAMENTM_L27.'</th>';
		$text .= '</tr>';
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

			$text .= '<tr>';
			$text .= '<td>'.EB_TOURNAMENTM_L25.' '.$round.'</td>';
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
		$text .= '</td"></tr>';
		//var_dump($rounds);

		//<!-- Map Pool -->
		if ($this->getID() != 0)
		{
			$mapPool = explode(",", $this->getField('MapPool'));
			$nbrMapsInPool = count($mapPool);

			$text .= '
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L28.'</td>
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
					$text .= ebImageTextButton('tournamentdeletemap', 'delete.png', EB_TOURNAMENTM_L31, 'negative jq-button', '', '', 'value="'.$key.'"');
					$text .= '</div>';
					$text .= '</td>';
					$text .= '</tr>';
				} else {
					$text .= EB_TOURNAMENTM_L29;
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

					$isMapInMapPool = FALSE;
					foreach($mapPool as $poolmap)
					{
						if ($mID==$poolmap) {
							$isMapInMapPool = TRUE;
						}
					}

					if($isMapInMapPool == FALSE) {
						$text .= '<option value="'.$mID.'"';
						$text .= '>'.$mName.'</option>';
					}
				}
				$text .= '</select></td>';

				$text .= '
				<td>
				<div>
				'.ebImageTextButton('tournamentaddmap', 'add.png', EB_TOURNAMENTM_L30).'
				</div>
				</td>
				</tr>
				</table>
				';
			}
			$text .= '</td"></tr>';
		}

		//<!-- Description -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L36.'</td>
		<td class="eb_td">
		';
		$text .= '<textarea class="tbox" id="tournamentdescription" name="tournamentdescription" cols="70" '.$insertjs.'>'.$this->getField('Description').'</textarea>';
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
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L38.'</td>
		<td class="eb_td">
		';
		$text .= '<textarea class="tbox" id="tournamentrules" name="tournamentrules" cols="70" '.$insertjs.'>'.$this->getField('Rules').'</textarea>';
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
		'.ebImageTextButton('tournamentsettingssave', 'disk.png', EB_TOURNAMENTM_L37).'
		</div>
		</td></tr></table>

		</form>';

		return $text;
	}

	function scheduleNextMatches() {
		global $sql;

		$type = $this->fields['Type'];
		$teams = array();
		switch($this->fields['MatchType'])
		{
			default:
			$q_Players = "SELECT ".TBL_GAMERS.".*"
			." FROM ".TBL_TPLAYERS.", "
			.TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_TPLAYERS.".Tournament = '".$this->fields['TournamentID']."')"
			." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY ".TBL_TPLAYERS.".Joined";
			$result = $sql->db_Query($q_Players);
			$nbrPlayers = mysql_numrows($result);
			for ($player = 0; $player < $nbrPlayers; $player++)
			{
				$gamerID = mysql_result($result,$player , TBL_GAMERS.".GamerID");
				$gamer = new Gamer($gamerID);
				$teams[$player]['Name'] = $gamer->getField('UniqueGameID');
			}
		}
		$nbrPlayers = $this->fields['MaxNumberPlayers'];
		$results = unserialize($this->fields['Results']);
		$rounds = unserialize($this->fields['Rounds']);

		$nbrTeams=count($teams);

		switch ($type)
		{
			default:
			$file = 'include/brackets/se-'.$nbrPlayers.'.txt';
			break;
		}
		$matchups = unserialize(implode('',file($file)));
		$nbrRounds = count($matchups);

		/* */
		$content= array();

		$rowspan = 1;
		for ($round = 1; $round <= $nbrRounds; $round++){
			$nbrMatchups = count($matchups[$round]);
			if ($round == 1) {
				/* Round 1 */
				for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
					$teamTop    = substr($matchups[$round][$matchup][0],1);
					$teamBottom = substr($matchups[$round][$matchup][1],1);
					if (!$results[$round][$matchup]['winner']) $results[$round][$matchup]['winner'] = '';

					$content[$round][$matchup][0] = '0';
					if ($teamTop <= $nbrTeams){
						$content[$round][$matchup][0] = $teamTop;
					} else {
						$results[$round][$matchup]['winner'] = 'bye';
					}
					$content[$round][$matchup][1] = '0';
					if ($teamBottom <= $nbrTeams){
						$content[$round][$matchup][1] = $teamBottom;
					} else {
						$results[$round][$matchup]['winner'] = 'bye';
					}

					if(($content[$round][$matchup][0]!='0')&&($content[$round][$matchup][1]!='0')){
						if ($results[$round][$matchup]['winner'] == '') {
							// Matchup not finished, no winner yet
						}
					}
				}
			}
			else if ($round < $nbrRounds)
			{
				for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
					if (!$results[$round][$matchup]['winner']) $results[$round][$matchup]['winner'] = '';
					for($match = 0; $match < 2; $match++){
						$matchupString = $matchups[$round][$matchup][$match];
						if ($matchupString[0]='W') {
							$matchupArray = explode(',',substr($matchupString,1));
							$matchupRound = $matchupArray[0];
							$matchupMatchup = $matchupArray[1];

							// Get result of matchup
							$result = $results[$matchupRound][$matchupMatchup]['winner'];

							if (($result == 'top')||($result == 'bye')) {
								$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
							}
							else if ($result == 'bottom') {
								$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
							}
							else {
								$content[$round][$matchup][$match] = 'not played';
							}
						}
					}
					if (($content[$round][$matchup][0]!='0')
					    &&($content[$round][$matchup][1]!='0')
					    &&($content[$round][$matchup][0]!='not played')
					    &&($content[$round][$matchup][1]!='not played')) {
						if ($results[$round][$matchup]['winner'] == '') {
							// Matchup not finished yet
							$matchPlayed = count($results[$round][$matchup]['matchs']);

							$match = array();
							//$match['']
							
							$results[$round][$matchup]['matchs'][$matchPlayed] = $match;
							
							echo "R$round M$matchup: Matchs played=$matchPlayed<br>";
							var_dump($results[$round][$matchup]);


						}
					}
					if (($content[$round][$matchup][0]=='0')||($content[$round][$matchup][1]=='0')) {
						$results[$round][$matchup]['winner'] = 'bye';
					}
				}
			}
			else
			{
				/* Last round, no match */
			}
		}

		/*
		var_dump($matchups);
		var_dump($results);
		var_dump($content);
		var_dump($teams);
		*/

		//return array($bracket_html);

	}
}

function tournamentTypeToString($type)
{
	switch($type)
	{
		case "Single Elimination":
		return EB_TOURNAMENTS_L22;
		break;
		default:
		return $type;
	}
}

function deleteTPlayer($player_id)
{
	global $sql;
	$q = "DELETE FROM ".TBL_TPLAYERS
	." WHERE (".TBL_TPLAYERS.".TPlayerID = '$player_id')";
	$result = $sql->db_Query($q);
}

?>
