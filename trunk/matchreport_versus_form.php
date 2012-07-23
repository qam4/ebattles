<?php
// function to output form and hold previously entered values.
function user_form($players_id, $players_name, $event_id, $match_id, $allowDraw, $allowForfeit, $allowScore, $userclass) {
	global $sql;
	global $text;
	global $tp;
	global $time;

	/* Event Info */
	$event = new Event($event_id);

	if (e_WYSIWYG)
	{
		$insertjs = "rows='15'";
	}
	else
	{
		require_once(e_HANDLER."ren_help.php");
		$insertjs = "rows='5' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
	}

	/*
	//dbg form
	echo "<br>_POST: ";
	var_dump($_POST);
	echo "<br>_GET: ";
	var_dump($_GET);
	*/

	$match_str = '';
	$matchreport_str = EB_MATCHR_L31;

	if(isset($_POST['matchedit']))
	{
		$matchreport_str = EB_MATCHR_L46;

		$text .= '<div>';
		$text .= EB_MATCHR_L45." $match_id<br />";
		$text .= '<img src="'.e_PLUGIN.'ebattles/images/exclamation.png"/>';
		$text .= EB_MATCHR_L47;
		$text .= '</div><br />';
	}
	if(isset($_POST['matchschedule']))
	{
		$matchreport_str = EB_MATCHR_L48;
	}


	if($match_id)
	{
		$match_str = '&amp;matchid='.$match_id;
	}

	// Assign values to POST if not set
	// fm: check might not be correct here
	// should check if isset($_POST['matchedit']) only?
	if ((!isset($_POST['time_reported']))
	||isset($_POST['matchscheduledreport'])) $_POST['time_reported'] = $time;
	$time_reported = $_POST['time_reported'];

	if(!isset($_POST['reported_by'])) $_POST['reported_by'] = USERID;
	$reported_by = $_POST['reported_by'];

	if(isset($_POST['match_comment']))
	{
		$comment = $tp->toDB($_POST['match_comment']);
	} else {
		$comment = '';
	}

	for ($matchMap = 0; $matchMap<min($numMaps, $event->getField('MaxMapsPerMatch')); $matchMap++)
	{
		if (!isset($_POST['map'.$matchMap])) $_POST['map'.$matchMap] = 0;
	}

	// if vars are not set, set them as empty.
	if (!isset($_POST['nbr_players'])) $_POST['nbr_players'] = 2;
	if (!isset($_POST['nbr_teams'])) $_POST['nbr_teams'] = 2;

	// now to output the form HTML.
	$max_nbr_players = count($players_id)-1;

	$nbr_players = $_POST['nbr_players'];
	$nbr_teams = $_POST['nbr_teams'];
	$nbr_players_per_team = $_POST['nbr_players'] / $_POST['nbr_teams'];


	$i = 1;	// player index
	for($t=1;$t<=$nbr_teams;$t++)
	{
		if (!isset($_POST['rank'.$t])) $_POST['rank'.$t] = 'Team #'.$t;

		for($p=1;$p<=$nbr_players_per_team;$p++)
		{
			if (!isset($_POST['team'.$i])) $_POST['team'.$i] = 'Team #'.$t;
			if (!isset($_POST['score'.$i])) $_POST['score'.$i] = 0;
			if (!isset($_POST['faction'.$i])) $_POST['faction'.$i] = 0;

			$i++;
		}
	}

	$result = 1;
	if(($_POST['rank1'] == 'Team #2')&&
	($_POST['rank2'] == 'Team #1'))
	{
		$result = 2;
	}
	if($_POST['draw2'] == 1)
	{
		$result = 3;
	}
	if(($_POST['rank1'] == 'Team #2')&&
	($_POST['rank2'] == 'Team #1')&&
	($_POST['forfeit2'] == 1))
	{
		$result = 4;
	}
	if(($_POST['rank1'] == 'Team #1')&&
	($_POST['rank2'] == 'Team #2')&&
	($_POST['forfeit2'] == 1))
	{
		$result = 5;
	}
	if (!isset($_POST['result'])) $_POST['result'] = $result;
	//var_dump($result);

	/////////////////
	/// MAIN FORM ///
	/////////////////
	// TODO: Forfeit????
	$text .= '<form id="matchreport" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'?eventid='.$event_id.$match_str.'" method="post">';
	// TABLE - Players/Teams Selection
	//----------------------------------
	// List of all Factions
	$q_Factions = "SELECT ".TBL_FACTIONS.".*"
	." FROM ".TBL_FACTIONS
	." WHERE (".TBL_FACTIONS.".Game = '".$event->getField('Game')."')";
	$result_Factions = $sql->db_Query($q_Factions);
	$numFactions = mysql_numrows($result_Factions);


	// TABLE - Match result
	if ($nbr_players > 2)
	{
		$array_result = array(
		1 => array(EB_MATCHR_L60, true),
		2 => array(EB_MATCHR_L61, true),
		3 => array(EB_MATCHR_L62, ($allowDraw == TRUE) ? true : false),
		4 => array(EB_MATCHR_L63, ($allowForfeit == TRUE) ? true : false),
		5 => array(EB_MATCHR_L64, ($allowForfeit == TRUE) ? true : false)
		);
	}
	else
	{
		$array_result = array(
		1 => array(EB_MATCHR_L65, true),
		2 => array(EB_MATCHR_L66, true),
		3 => array(EB_MATCHR_L67, ($allowDraw == TRUE) ? true : false),
		4 => array(EB_MATCHR_L68, ($allowForfeit == TRUE) ? true : false),
		5 => array(EB_MATCHR_L69, ($allowForfeit == TRUE) ? true : false)
		);
	}


	$text .= '<table id="matchresult_selectresult" class="table_left"><tbody>';
	$text .= '<tr>';
	$text .= '<td>'.EB_MATCHR_L59.'</td>';
	$text .= '<td>';
	$text .= '<select class="tbox" name="result">';
	foreach($array_result as $opt=>$opt_array)
	{
		if($opt_array[1] == true)
		{
			$selected_str = ($_POST['result'] == $opt) ? 'selected="selected"' : '';
			$text .= '<option value="'.$opt.'" '.$selected_str.'>'.$opt_array[0].'</option>';
		}
	}
	$text .= '</select>';
	$text .= '</td>';
	$text .= '</tr>';
	$text .= '</tbody></table>';

	// TABLE - Teams
	$p = 1; // player index
	//$text .= EB_MATCHR_L20;
	for($t=1; $t<=$nbr_teams; $t++)
	{
		$text .= '<table id="matchresult_teams'.$t.'" style="text-align:center" class="table_left">';
		$text .= '<thead>';
		$text .= '<tr>';
		$text .= '<th class="eb_th2">';
		$text .= ($nbr_players > 2) ? EB_MATCHR_L10.$t : '';
		$text .= '</th>';
		$text .= '</tr>';
		$text .= '</thead>';
		$text .= '<tbody>';
		$text .= '<tr>';
		$text .= '<td>';
		$text .= '<table><thead>';
		$text .= '<tr>';
		$text .= '<th class="eb_th1"></th>';	// blank
		//$text .= '<th class="eb_th1">'.EB_MATCHR_L58.'</th>';	// Name
		$text .= '<th class="eb_th1"></th>';	// Name
		$text .= '<th class="eb_th1"></th>';	// Team
		if (!isset($_POST['matchschedule'])&&($allowScore == TRUE)) $text .= '<th class="eb_th1">'.EB_MATCHR_L26.'</th>';	// Score
		if (!isset($_POST['matchschedule'])&&($numFactions > 0)) $text .= '<th class="eb_th1">'.EB_MATCHR_L41.'</th>';	// Faction
		$text .= '</tr>';
		$text .= '</thead>';

		$text .= '<tbody>';
		$select_disabled_str = (isset($_POST['matchscheduledreport'])) ? 'disabled="disabled"' : '';
		for($i=1;$i<=$nbr_players;$i++)
		{
			if ($_POST['team'.$i] == 'Team #'.$t)
			{
				$text .= '<tr><td>'.EB_MATCHR_L23.$p.'</td>';
	
				$text .= '<td><select class="tbox" name="player'.$i.'" '.$select_disabled_str.'>';
				for($j=1;$j <= $max_nbr_players+1;$j++)
				{
					$text .= '<option value="'.$players_id[($j-1)].'"';
					if (strtolower($_POST['player'.$i]) == strtolower($players_id[($j-1)])) $text .= ' selected="selected"';
					$text .= '>'.$players_name[($j-1)].'</option>';
				}
				$text .= '</select></td>';
				$text .= '<td><input type="hidden" name="team'.$i.'" value="Team #'.$t.'"/></td>';
				if (!isset($_POST['matchschedule']))
				{
					if ($allowScore == TRUE)
					{
						$text .= '<td>';
						$text .= '<input class="tbox" type="text" size="3" name="score'.$i.'" value="'.$_POST['score'.$p].'"/>';
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
				}
				$p++;
			}
			$text .= '</tr>';
		}
		$text .= '</tbody></table>';
		$text .= '</td>';
		$text .= '</tr>';
		$text .= '</tbody>';
		$text .= '</table>';
	}

	$text .= '<br />';

	// Map Selection
	//----------------------------------
	// List of all Maps
	$q_Maps = "SELECT ".TBL_MAPS.".*"
	." FROM ".TBL_MAPS
	." WHERE (".TBL_MAPS.".Game = '".$event->getField('Game')."')";
	$result_Maps = $sql->db_Query($q_Maps);
	$numMaps = mysql_numrows($result_Maps);

	if ($numMaps > 0)
	{
		$text .= EB_MATCHR_L42;
		$text .= '<table id="matchresult_selectMap" class="table_left"><tbody>';

		for ($matchMap = 0; $matchMap<min($numMaps, $event->getField('MaxMapsPerMatch')); $matchMap++)
		{
			$text .= '<tr>';

			$text .= '<td><select class="tbox" name="map'.$matchMap.'">';
			$text .= '<option value="0"';
			$text .= '>'.EB_MATCHR_L43.'</option>';
			for($map=0;$map < $numMaps;$map++)
			{
				$mID = mysql_result($result_Maps,$map , TBL_MAPS.".MapID");
				$mImage = mysql_result($result_Maps,$map , TBL_MAPS.".Image");
				$mName = mysql_result($result_Maps,$map , TBL_MAPS.".Name");
				$mDescrition = mysql_result($result_Maps,$map , TBL_MAPS.".Description");

				$text .= '<option value="'.$mID.'"';
				if (strtolower($_POST['map'.$matchMap]) == $mID) $text .= ' selected="selected"';
				$text .= '>'.$mName.'</option>';
			}
			$text .= '</select></td>';
			$text .= '</tr>';
		}

		$text .= '</tbody></table>';
		$text .= '<br />';
	}

	if(!isset($_POST['matchschedule']))
	{
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
	}

	if(isset($_POST['matchschedule']))
	{
		//<!-- Date Selection -->
		$text .= EB_MATCHR_L49;
		$text .= '
		<table>
		<tr>
		<td>
		<table>
		<tr>
		<td>
		<div><input class="tbox timepicker" type="text" name="date_scheduled" id="f_date"  value="'.$date_scheduled.'" readonly="readonly" /></div>
		</td>
		<td>
		<div><input class="button" type="button" value="'.EB_MATCHR_L51.'" onclick="clearDate(this.form);"/></div>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		';
	}

	$text .= '<div style="display:table; margin-left:auto; margin-right:auto;">';
	$text .= '<input type="hidden" name="nbr_players" value="'.$nbr_players.'"/>';
	$text .= '<input type="hidden" name="nbr_teams" value="'.$nbr_teams.'"/>';
	$text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
	$text .= '<input type="hidden" name="reported_by" value="'.$reported_by.'"/>';
	$text .= '<input type="hidden" name="time_reported" value="'.$time_reported.'"/>';
	if(isset($_POST['matchscheduledreport']))
	{
		$text .= '<input type="hidden" name="matchscheduledreport" value="1"/>';
	}
	if(isset($_POST['matchschedule']))
	{
		$text .= '<input type="hidden" name="matchschedule" value="1"/>';
	}
	$text .= '<input class="button" type="submit" value="'.$matchreport_str.'" name="submit"/>';
	$text .= '</div>';
	$text .= '<br /><br />';
	$text .= '</div>';
	$text .= '</form>';
}

?>
