<?php
// functions for events.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');

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
		$this->setField('Type', 'Single Elimination');
		$this->setField('MatchType', '1v1');
		$this->setField('AllowForfeit', '0');
		$this->setField('AllowScore', '0');
		$this->setField('match_report_userclass', eb_UC_EVENT_MODERATOR);
		$this->setField('match_replay_report_userclass', eb_UC_EVENT_PLAYER);
		$this->setField('Visibility', eb_UC_NONE);
		$this->setField('Status', 'draft');
		$this->setField('MaxNumberPlayers', 16);
	}

	/**
	* eventAddPlayer - add a user to a event
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
		." WHERE (".TBL_TPLAYERS.".Event = '".$this->fields['EventID']."')"
		."   AND (".TBL_TPLAYERS.".Team = '$team')"
		."   AND (".TBL_TPLAYERS.".Gamer = '$gamerID')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		echo "num_rows: $num_rows<br>";
		if ($num_rows==0)
		{
			$q = " INSERT INTO ".TBL_TPLAYERS."(Event,Gamer,Team,Joined)
			VALUES (".$this->fields['EventID'].",$gamerID,$team,$time)";
			$sql->db_Query($q);
			echo "player created, query: $q<br>";
			$q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '".$this->fields['EventID']."')";
			$sql->db_Query($q);

			if ($notify)
			{
				$sendto = $user;
				$subject = SITENAME.$this->fields['Name'];
				$message = EB_EVENTS_L26.$username.EB_EVENTS_L27.$this->fields['Name'].EB_EVENTS_L29.EB_EVENTS_L31.USERNAME;
				sendNotification($sendto, $subject, $message, $fromid=0);

				// Send email
				//$message = EB_EVENTS_L26.$username.EB_EVENTS_L27.$this->fields['Name'].EB_EVENTS_L30."<a href='".SITEURLBASE.e_PLUGIN_ABS."ebattles/eventinfo.php?EventID=$this->fields['EventID']'>$this->fields['Name']</a>.".EB_EVENTS_L31.USERNAME.EB_EVENTS_L32;
				$message = EB_EVENTS_L26.$username.EB_EVENTS_L27.$this->fields['Name'].EB_EVENTS_L30.SITEURLBASE.e_PLUGIN_ABS."ebattles/eventinfo.php?EventID=$this->fields['EventID']".EB_EVENTS_L31.USERNAME;
				require_once(e_HANDLER."mail.php");
				sendemail($useremail, $subject, $message);
			}
		}
	}


	/**
	* eventAddDivision - add a division to a event
	*/
	function eventAddDivision($div_id, $notify)
	{
		global $sql;
		global $time;

		//$add_players = ( $this->fields['Type'] == "Clan Ladder" ? FALSE : TRUE);
		$add_players = TRUE;

		// Is the division signed up
		$q = "SELECT ".TBL_TTEAMS.".*"
		." FROM ".TBL_TTEAMS
		." WHERE (".TBL_TTEAMS.".Event = '".$this->fields['EventID']."')"
		." AND (".TBL_TTEAMS.".Division = '$div_id')";
		$result = $sql->db_Query($q);
		$numTeams = mysql_numrows($result);
		if($numTeams == 0)
		{
			$q = "INSERT INTO ".TBL_TTEAMS."(Event,Division,Joined)
			VALUES (".$this->fields['EventID'].",$div_id,$time)";
			$sql->db_Query($q);
			$team_id =  mysql_insert_id();

			if ($add_players == TRUE)
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
					$q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '".$this->fields['EventID']."')";
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

	function displayEventSettingsForm()
	{
		global $sql;
		// Specify if we use WYSIWYG for text areas
		global $e_wysiwyg;
		$e_wysiwyg	= "eventdescription,eventrules";  // set $e_wysiwyg before including HEADERF
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

		$text .= '<form id="form-event-settings" action="'.e_PLUGIN.'ebattles/eventprocess.php?EventID='.$this->getField('EventID').'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		//<!-- Event Name -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L15.'</td>
		<td class="eb_td">
		<div><input class="tbox" type="text" size="40" name="eventname" value="'.$this->getField('Name').'"/></div>
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

		$q = "SELECT ".TBL_GAMES.".*"
		." FROM ".TBL_GAMES
		." ORDER BY Name";
		$result = $sql->db_Query($q);
		/* Error occurred, return given name by default */
		$numGames = mysql_numrows($result);
		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L17.'</td>';
		$text .= '<td class="eb_td"><select class="tbox" name="eventgame">';
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
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L18.'</td>
		<td class="eb_td"><select class="tbox" name="eventtype">';
		$text .= '<option value="Single Elimination" '.($this->getField('Type') == "Single Elimination" ? 'selected="selected"' : '') .'>'.EB_EVENTM_L19.'</option>';
		$text .= '</select>
		</td>
		</tr>
		';

		//<!-- Match Type -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L126.'</td>
		<td class="eb_td">
		<div>
		';
		$text .= '<select class="tbox" name="eventmatchtype">';
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
		</tr>
		';

		//<!-- Max Number of Players -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L132.'</td>
		<td class="eb_td">
		<div>
		';
		$text .= '<select class="tbox" name="eventmaxnumberplayers">';
		$text .= '<option value="4" '.($this->getField('MaxNumberPlayers') == "4" ? 'selected="selected"' : '') .'>4</option>';
		$text .= '<option value="8" '.($this->getField('MaxNumberPlayers') == "8" ? 'selected="selected"' : '') .'>8</option>';
		$text .= '<option value="16" '.($this->getField('MaxNumberPlayers') == "16" ? 'selected="selected"' : '') .'>16</option>';
		$text .= '</select>';
		$text .= '
		</div>
		</td>
		</tr>
		';

		/* TODO:
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

		//<!-- Allow Score -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L20.'</td>
		<td class="eb_td">
		<div>
		';
		$text .= '<input class="tbox" type="checkbox" name="eventallowscore"';
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

		/* for now
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
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L108.'<div class="smalltext">'.EB_EVENTM_L109.'</div></td>
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
		*/

		//<!-- Allow Forfeits -->
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L127.'</td>
		<td class="eb_td">
		<div>';
		$text .= '<input class="tbox" type="checkbox" name="eventallowforfeit"';
		if ($this->getField('AllowForfeit') == TRUE)
		{
			$text .= ' checked="checked"/>';
		}
		else
		{
			$text .= '/>';
		}
		$text .= EB_EVENTM_L128;
		$text .= '
		</div>
		</td>
		</tr>
		';

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
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L32.'</td>
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
		<td class="eb_td eb_tdc1 eb_w40">'.($nbrRounds - 1).' '.EB_EVENTM_L4.'</td>
		<td class="eb_td">';

		$rounds = unserialize($this->getField('Rounds'));
		if (!isset($rounds)) $rounds = array();
		$text .= '<table class="table_left"><tbody>';
		$text .= '<tr>';
		$text .= '<th>'.EB_EVENTM_L25.'</th>';
		$text .= '<th>'.EB_EVENTM_L26.'</th>';
		$text .= '<th>'.EB_EVENTM_L27.'</th>';
		$text .= '</tr>';
		for ($round = 1; $round < $nbrRounds; $round++) {
			if (!isset($rounds[$round])) {
				$rounds[$round] = array();
			}
			if (!isset($rounds[$round]['Title'])) {
				$rounds[$round]['Title'] = EB_EVENTM_L25.' '.$round;
			}
			if (!isset($rounds[$round]['BestOf'])) {
				$rounds[$round]['BestOf'] = 1;
			}

			$text .= '<tr>';
			$text .= '<td>'.EB_EVENTM_L25.' '.$round.'</td>';
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
			<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L28.'</td>
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
					$text .= ebImageTextButton('eventdeletemap', 'delete.png', EB_EVENTM_L31, 'negative jq-button', '', '', 'value="'.$key.'"');
					$text .= '</div>';
					$text .= '</td>';
					$text .= '</tr>';
				} else {
					$text .= '<tr>';
					$text .= '<td><div>';
					$text .= EB_EVENTM_L29;
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
				'.ebImageTextButton('eventaddmap', 'add.png', EB_EVENTM_L30).'
				</div>
				</td>
				</tr>
				</table>
				';
			}
			$text .= '</td></tr>';
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
		<table><tr><td>
		<div>
		'.ebImageTextButton('eventsettingssave', 'disk.png', EB_EVENTM_L37).'
		</div>
		</td></tr></table>

		</form>';

		return $text;
	}

	function scheduleNextMatches() {
		global $sql;
		global $time;
		//dbg
		global $tp;

		$teams = array();
		$type = $this->fields['MatchType'];
		switch($type)
		{
			default:
			$q_Players = "SELECT ".TBL_GAMERS.".*, "
			.TBL_TPLAYERS.".*"
			." FROM ".TBL_GAMERS.", "
			.TBL_TPLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_TPLAYERS.".Event = '".$this->fields['EventID']."')"
			." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY ".TBL_TPLAYERS.".Joined";
			$result = $sql->db_Query($q_Players);
			$nbrPlayers = mysql_numrows($result);
			for ($player = 0; $player < $nbrPlayers; $player++)
			{
				$playerID = mysql_result($result, $player, TBL_TPLAYERS.".TPlayerID");
				$gamerID = mysql_result($result, $player, TBL_GAMERS.".GamerID");
				$gamer = new Gamer($gamerID);
				$teams[$player]['Name'] = $gamer->getField('UniqueGameID');
				$teams[$player]['PlayerID'] = $playerID;
			}
		}
		$nbrPlayers = $this->fields['MaxNumberPlayers'];
		$results = unserialize($this->getField('Results'));
		// TODO: check for error (return false)
		$rounds = unserialize($this->getField('Rounds'));

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
							// TODO: Round 1
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
					&&($content[$round][$matchup][1]!='not played')
					&&($results[$round][$matchup]['winner'] == '')) {
						// Matchup not finished yet
						$matchs = count($results[$round][$matchup]['matchs']);
						
						$current_match = $results[$round][$matchup]['matchs'][$matchs-1];
						//var_dump($current_match);
						if((!isset($current_match)) || ($current_match['played'] == true))
						{
							// Need to schedule the next match
							// Create Match ------------------------------------------
							$event_id = $this->fields['EventID'];
							$reported_by = ADMINID;
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
 						 	$teamTop    = $content[$round][$matchup][0];
 						 	$teamBottom = $content[$round][$matchup][1];
 						 	
 						 	$teamTopID = $teams[$teamTop-1]['PlayerID'];
 						 	$teamBottomID = $teams[$teamBottom-1]['PlayerID'];
 						 	
 							switch($type)
							{
								default:
									// 1v1
									// TODO: 2v2...
									echo "hello";
									$q =
									"INSERT INTO ".TBL_SCORES."(MatchID,Player,Team,Player_MatchTeam,Player_Rank)
									VALUES ($match_id,$teamTopID,0,1,1)
									";
									$result = $sql->db_Query($q);
						
									$q =
									"INSERT INTO ".TBL_SCORES."(MatchID,Player,Team,Player_MatchTeam,Player_Rank)
									VALUES ($match_id,$teamBottomID,0,2,2)
									";
									$result = $sql->db_Query($q);
							}
							
							$match = array();
							$match['played'] = false;
							$match['match_id'] = $match_id;
							$results[$round][$matchup]['matchs'][$matchs] = $match;
							
							$this->updateResults($results);
							$this->updateDB($results);
						}
						echo "R$round M$matchup: Nbr of matchs=$matchs<br>";
						var_dump($results[$round][$matchup]);
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

function eventTypeToString($type)
{
	switch($type)
	{
		case "Single Elimination":
		return EB_EVENTS_L22;
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
