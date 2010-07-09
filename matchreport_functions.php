<?php
// function to output form and hold previously entered values.
function user_form($players_id, $players_name, $event_id, $match_id, $allowDraw, $allowScore, $userclass) {
	global $sql;
	global $text;
	global $tp;
    global $time;

	/* Event Info */
	$q = "SELECT ".TBL_EVENTS.".*"
	." FROM ".TBL_EVENTS
	." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
	$result = $sql->db_Query($q);
	$etype = mysql_result($result,0 , TBL_EVENTS.".Type");
	$eGame = mysql_result($result,0 , TBL_EVENTS.".Game");

	if (e_WYSIWYG)
	{
		$insertjs = "rows='15'";
	}
	else
	{
		require_once(e_HANDLER."ren_help.php");
		$insertjs = "rows='5' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
	}
	//dbg form
	//echo "<br>_POST: ";
	//print_r($_POST);    // show $_POST
	//echo "<br>_GET: ";
	//print_r($_GET);     // show $_GET

	$match_str = '';
	$matchreport_str = EB_MATCHR_L31;
	if($match_id)
	{
		$text .= '<div>';
		$text .= EB_MATCHR_L45." $match_id<br>";
		$text .= '<img src="'.e_PLUGIN.'ebattles/images/exclamation.png"/>';
		$text .= EB_MATCHR_L47;
		$text .= '</div><br>';

		$match_str = '&amp;matchid='.$match_id;
		$matchreport_str = EB_MATCHR_L46;
		
		// Get the scores for this match
		switch($etype)
		{
			case "One Player Ladder":
			case "Team Ladder":
			$q = "SELECT ".TBL_MATCHS.".*, "
			.TBL_SCORES.".*, "
			.TBL_PLAYERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_MATCHS.", "
			.TBL_SCORES.", "
			.TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
			." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
			." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
			." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
			." ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";
			break;
			case "ClanWar":
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
		if (!isset($_POST['map']))           $_POST['map'] = mysql_result($result,0, TBL_MATCHS.".Map");
		if (!isset($_POST['reported_by']))   $_POST['reported_by'] = mysql_result($result,0, TBL_MATCHS.".ReportedBy");
		if (!isset($_POST['match_comment'])) $_POST['match_comment'] = mysql_result($result,0, TBL_MATCHS.".Comments");
		if (!isset($_POST['time_reported'])) $_POST['time_reported'] = mysql_result($result,0, TBL_MATCHS.".TimeReported");

		$index = 1;
		$rank = 0;
		$matchteam = 0;
		$nbr_teams = 0;
		for($score=0;$score < $numScores;$score++)
		{
			switch($etype)
			{
				case "One Player Ladder":
				case "Team Ladder":
				$pid  = mysql_result($result,$score, TBL_PLAYERS.".PlayerID");
				$puid  = mysql_result($result,$score, TBL_USERS.".user_id");
				$pname  = mysql_result($result,$score, TBL_USERS.".user_name");
				$pavatar = mysql_result($result,$score, TBL_USERS.".user_image");
				$pteam  = mysql_result($result,$score, TBL_PLAYERS.".Team");
				list($pclan, $pclantag, $pclanid) = getClanName($pteam);
				break;
				case "ClanWar":
				$pid  = mysql_result($result,$score, TBL_TEAMS.".TeamID");
				$pname  = mysql_result($result,$score, TBL_CLANS.".Name");
				$pavatar = mysql_result($result,$score, TBL_CLANS.".Image");
				$pteam  = mysql_result($result,$score, TBL_TEAMS.".TeamID");
				list($pclan, $pclantag, $pclanid) = getClanName($pteam); // Use this function to get other clan info like clan id?
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
				if($prank == $rank)
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

		//dbg form
		//echo "<br>_POST: ";
		//print_r($_POST);    // show $_POST
		//echo "<br>_GET: ";
		//print_r($_GET);     // show $_GET
	}
	// Assign values to POST if not set
	if (!isset($_POST['time_reported'])) $_POST['time_reported'] = $time;
	$time_reported = $_POST['time_reported'];
	
	if(!isset($_POST['reported_by'])) $_POST['reported_by'] = USERID;
	$reported_by = $_POST['reported_by'];

	if(isset($_POST['match_comment']))
	{
		$comment = $tp->toDB($_POST['match_comment']);
	} else {
		$comment = '';
	}

	if (!isset($_POST['map'])) $_POST['map'] = 0;

	$max_nbr_players = count($players_id)-1;

	// if vars are not set, set them as empty.
	if (!isset($_POST['nbr_players'])) $_POST['nbr_players'] = 2;
	if (!isset($_POST['nbr_teams'])) $_POST['nbr_teams'] = 2;

	// now to output the form HTML.
	$nbr_players = $_POST['nbr_players'];
	$nbr_teams = $_POST['nbr_teams'];

	if (isset($_POST['addPlayer']))
	{
		$nbr_players++;
	}
	if (isset($_POST['removePlayer']))
	{
		if ($nbr_players==$nbr_teams)
		{
			$nbr_teams--;
		}
		$nbr_players--;
	}
	$_POST['nbr_players']=$nbr_players;

	for($i=1;$i<=$nbr_players;$i++)
	{
		if (!isset($_POST['player'.$i])) $_POST['player'.$i] = $players_id[0];
		//debug - echo "Player #".$i.": ".$_POST['player'.$i]."<br />";
	}

	if (isset($_POST['addTeam']))
	{
		$nbr_teams++;
	}
	if (isset($_POST['removeTeam']))
	{
		$nbr_teams--;
	}
	$_POST['nbr_teams']=$nbr_teams;
	for($i=1;$i<=$nbr_players;$i++)
	{
		if (!isset($_POST['team'.$i])) $_POST['team'.$i] = 'Team #'.$i;
		if (!isset($_POST['score'.$i])) $_POST['score'.$i] = 0;
		if (!isset($_POST['faction'.$i])) $_POST['faction'.$i] = 0;
	}

	for($i=1;$i<=$nbr_teams;$i++)
	{
		if (!isset($_POST['rank'.$i])) $_POST['rank'.$i] = 'Team #'.$i;
	}

	/////////////////
	/// MAIN FORM ///
	/////////////////	
	$text .= '<form id="matchreport" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'?eventid='.$event_id.$match_str.'" method="post">';
	$text .= '<div>';
	// TABLE - Player/Teams Add/Remove
	//----------------------------------
	$text .= EB_MATCHR_L15;
	$text .= '<table id="matchresult_nbrPlayersTeams"><tbody>';
	$text .= '<tr><td><input type="hidden" name="matchreport" value="1"/></td></tr>';
	// Players
	$text .= '<tr><td>'.$nbr_players.'&nbsp;'.EB_MATCHR_L21.'</td>';
	$text .= '<td><input type="hidden" name="nbr_players" value="'.$_POST['nbr_players'].'"/>';
	// Add Player
	if ($nbr_players < $max_nbr_players)
	{
		$text .= '<input class="button" type="submit" value="'.EB_MATCHR_L16.'" name="addPlayer"/></td>';
	}
	else
	{
		$text .= '<input class="button_disabled" type="submit" value="'.EB_MATCHR_L16.'" name="addPlayer" disabled="disabled"/></td>';
	}
	// Remove Player
	if ($nbr_players>2)
	{
		$text .= '<td><input class="button" type="submit" value="'.EB_MATCHR_L17.'" name="removePlayer"/></td>';
	}
	else
	{
		$text .= '<td><input class="button_disabled" type="submit" value="'.EB_MATCHR_L17.'" name="removePlayer" disabled="disabled"/></td>';
	}
	$text .= '</tr>';

	// Teams
	$text .= '<tr><td>'.$nbr_teams.'&nbsp;'.EB_MATCHR_L22.'</td>';
	$text .= '<td><input type="hidden" name="nbr_teams" value="'.$_POST['nbr_teams'].'"/>';
	// Add Team
	if ($nbr_teams<$nbr_players)
	{
		$text .= '<input class="button" type="submit" value="'.EB_MATCHR_L18.'" name="addTeam"/></td>';
	}
	else
	{
		$text .= '<input class="button_disabled" type="submit" value="'.EB_MATCHR_L18.'" name="addTeam" disabled="disabled"/></td>';
	}
	// Remove Team
	if ($nbr_teams>2)
	{
		$text .= '<td><input class="button" type="submit" value="'.EB_MATCHR_L19.'" name="removeTeam"/></td>';
	}
	else
	{
		$text .= '<td><input class="button_disabled" type="submit" value="'.EB_MATCHR_L19.'" name="removeTeam" disabled="disabled"/></td>';
	}
	$text .= '</tr>';
	$text .= '</tbody></table>';

	//$text .= '<p><input class="inspector" type="button" value="Inspect" onclick="junkdrawer.inspectListOrder(\'matchresultlist\')"/></p>';
	$text .= '<br />';

	// TABLE - Players/Teams Selection
	//----------------------------------
	// List of all Factions
	$q_Factions = "SELECT ".TBL_FACTIONS.".*"
	." FROM ".TBL_FACTIONS
	." WHERE (".TBL_FACTIONS.".Game = '$eGame')";
	$result_Factions = $sql->db_Query($q_Factions);
	$numFactions = mysql_numrows($result_Factions);

	$text .= EB_MATCHR_L20;
	$text .= '<table id="matchresult_selectPlayersTeams"><tbody>';
	$text .= '<tr><td></td><td>'.EB_MATCHR_L38.'</td>';
	$text .= '<td>'.EB_MATCHR_L25.'</td>';
	if ($allowScore == TRUE) $text .= '<td>'.EB_MATCHR_L26.'</td>';
	if ($numFactions > 0) $text .= '<td>'.EB_MATCHR_L41.'</td>';
	$text .= '</tr>';
	for($i=1;$i<=$nbr_players;$i++)
	{
		$text .= '<tr><td>'.EB_MATCHR_L23.$i.':</td>';

		$text .= '<td><select class="tbox" name="player'.$i.'">';
		for($j=1;$j <= $max_nbr_players+1;$j++)
		{
			$text .= '<option value="'.$players_id[($j-1)].'"';
			if (strtolower($_POST['player'.$i]) == strtolower($players_id[($j-1)])) $text .= ' selected="selected"';
			$text .= '>'.$players_name[($j-1)].'</option>';
		}
		$text .= '</select></td>';

		$text .= '<td><select class="tbox" name="team'.$i.'">';
		for($j=1;$j<=$nbr_teams;$j++)
		{
			$text .= '<option value="Team #'.$j.'"';
			if (strtolower($_POST['team'.$i]) == 'team #'.$j) $text .= ' selected="selected"';
			$text .= '>'.EB_MATCHR_L29.$j.'</option>';
		}
		$text .= '</select></td>';
		if ($allowScore == TRUE)
		{
			$text .= '<td>';
			$text .= '<input class="tbox" type="text" name="score'.$i.'" value="'.$_POST['score'.$i].'"/>';
			$text .= '</td>';
		}
		if ($numFactions > 0)
		{
			$text .= '<td><select class="tbox" name="faction'.$i.'">';
			$text .= '<option value="0"';
			$text .= '>'.EB_MATCHR_L43.'</option>';
			for($faction=1;$faction<=$numFactions;$faction++)
			{
				$fID = mysql_result($result_Factions,$faction - 1 , TBL_FACTIONS.".FactionID");
				$fIcon = mysql_result($result_Factions,$faction - 1, TBL_FACTIONS.".Icon");
				$fName = mysql_result($result_Factions,$faction - 1, TBL_FACTIONS.".Name");
				$text .= '<option value="'.$fID.'"';
				if (strtolower($_POST['faction'.$i]) == $fID) $text .= ' selected="selected"';
				$text .= '>'.$fName.'</option>';
			}
			$text .= '</select></td>';
		}
		$text .= '</tr>';
	}
	$text .= '</tbody></table>';
	$text .= '<br />';

	// TABLE - Teams Rank Selection
	//----------------------------------
	$text .= EB_MATCHR_L24;
	$text .= '<table id="matchresult_rankTeams"><tbody>';
	$text .= '<tr><td></td><td>'.EB_MATCHR_L25.'</td>';
	if ($allowDraw == TRUE) $text .= '<td>'.EB_MATCHR_L27.'</td>';
	$text .= '</tr>';

	for($i=1;$i<=$nbr_teams;$i++)
	{
		$text .= '<tr>';
		$text .= '<td>';
		$text .= EB_MATCHR_L28.$i.':';
		$text .= '</td>';
		$text .= '<td><select class="tbox" name="rank'.$i.'" id="rank'.$i.'" onchange = "SwitchSelected('.$i.')">';
		for($j=1;$j<=$nbr_teams;$j++)
		{
			$text .= '<option value="Team #'.$j.'"';
			if (strtolower($_POST['rank'.$i]) == 'team #'.$j) $text .= ' selected="selected"';
			$text .= '>'.EB_MATCHR_L29.$j.'</option>';
		}
		$text .= '</select></td>';
		if ($allowDraw == TRUE)
		{
			$text .= '<td>';
			if ($i>1)
			{
				$text .= '<input class="tbox" type="checkbox" name="draw'.$i.'" value="1"';
				if (strtolower($_POST['draw'.$i]) != "") $text .= ' checked="checked"';
				$text .= '/>';
			}
			$text .= '</td>';
		}
		$text .= '</tr>';
	}
	$text .= '</tbody></table>';

	// Map Selection
	//----------------------------------
	// List of all Maps
	$q_Maps = "SELECT ".TBL_MAPS.".*"
	." FROM ".TBL_MAPS
	." WHERE (".TBL_MAPS.".Game = '$eGame')";
	$result_Maps = $sql->db_Query($q_Maps);
	$numMaps = mysql_numrows($result_Maps);

	if ($numMaps > 0)
	{
		$text .= EB_MATCHR_L42;
		$text .= '<table id="matchresult_selectMap"><tbody>';
		$text .= '<tr>';

		$text .= '<td><select class="tbox" name="map">';
		$text .= '<option value="0"';
		$text .= '>'.EB_MATCHR_L43.'</option>';
		for($map=0;$map < $numMaps;$map++)
		{
			$mID = mysql_result($result_Maps,$map , TBL_MAPS.".MapID");
			$mImage = mysql_result($result_Maps,$map , TBL_MAPS.".Image");
			$mName = mysql_result($result_Maps,$map , TBL_MAPS.".Name");
			$mDescrition = mysql_result($result_Maps,$map , TBL_MAPS.".Description");

			$text .= '<option value="'.$mID.'"';
			if (strtolower($_POST['map']) == $mID) $text .= ' selected="selected"';
			$text .= '>'.$mName.'</option>';
		}
		$text .= '</select></td>';
		$text .= '</tr>';
		$text .= '</tbody></table>';
		$text .= '<br />';
	}

	// Comments
	//----------------------------------
	$text .= '<br />';
	$text .= '<div style="display:table; margin-left:auto; margin-right:auto;">';
	$text .= EB_MATCHR_L30.'<br />';
	$text .= '<textarea class="tbox" id="match_comment" name="match_comment" style="width:500px" cols="70" '.$insertjs.'>'.$comment.'</textarea>';
	if (!e_WYSIWYG)
	{
		$text .= '<br />'.display_help("helpb","comment");
	}
	$text .= '</div>';
	$text .= '<br />';
	$text .= '<div style="display:table; margin-left:auto; margin-right:auto;">';
	$text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
	$text .= '<input type="hidden" name="reported_by" value="'.$reported_by.'"/>';
	$text .= '<input type="hidden" name="time_reported" value="'.$time_reported.'"/>';
	$text .= '<input class="button" type="submit" value="'.$matchreport_str.'" name="submit"/>';
	$text .= '</div>';
	$text .= '<br /><br />';
	$text .= '</div>';
	$text .= '</form>';
}

?>
