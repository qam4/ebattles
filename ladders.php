<?php
/**
* ladders.php
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/ladder.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

$text .= "
<script type='text/javascript'>
<!--//
function buttonval(v)
{
document.getElementById('sort').value=v;
document.getElementById('submitform').submit();
}
//-->
</script>
";

$text .= '<div id="tabs">';
$text .= '<ul>';
$text .= '<li><a href="#tabs-1">'.EB_LADDERS_L2.'</a></li>';
$text .= '<li><a href="#tabs-2">'.EB_LADDERS_L3.'</a></li>';
$text .= '</ul>';
/**
* Display Current Ladders
*/
$text .= '<div id="tabs-1">';
displayCurrentLadders();
$text .= '</div>';

/**
* Display Recent Ladders
*/
$text .= '<div id="tabs-2">';
displayRecentLadders();
$text .= '
</div>
</div>
';

$text .= disclaimer();

$ns->tablerender(EB_LADDERS_L1, $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayLadders - Displays the ladders database table in
* a nicely formatted html table.
*/
function displayCurrentLadders(){
	global $pref;
	global $sql;
	global $text;
	global $time;
	$pages = new Paginator;

	if(check_class($pref['eb_ladders_create_class']))
	{
		$text .= '<form action="'.e_PLUGIN.'ebattles/laddercreate.php" method="post">';
		$text .= '<div>';
		$text .= '<input type="hidden" name="userid" value="'.USERID.'"/>';
		$text .= '<input type="hidden" name="username" value="'.USERNAME.'"/>';
		$text .= '</div>';
		$text .= ebImageTextButton('createladder', 'add.png', EB_LADDERS_L20);
		$text .= '</form><br />';
	}
	else
	{
		//$text .= '<div>'.EB_LADDERC_L2.'</div>';
	}

	$array = array(
	'latest' => array(EB_LADDERS_L4,'LadderID'),
	'name'   => array(EB_LADDERS_L5, TBL_LADDERS.'.Name'),
	'game'   => array(EB_LADDERS_L6, TBL_GAMES.'.Name'),
	'type'   => array(EB_LADDERS_L7, TBL_LADDERS.'.Type'),
	'start'  => array(EB_LADDERS_L8, TBL_LADDERS.'.Start_timestamp')
	);
	if (!isset($_GET['gameid'])) $_GET['gameid'] = "All";
	$gameid = $_GET['gameid'];

	if (!isset($_GET['matchtype'])) $_GET['matchtype'] = "All";
	$matchtype = $_GET['matchtype'];

	if (!isset($_GET['orderby'])) $_GET['orderby'] = 'game';
	$orderby=$_GET['orderby'];

	$sort = "ASC";
	if(isset($_GET["sort"]) && !empty($_GET["sort"]))
	{
		$sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
	}

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_LADDERS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_GAMES.".MatchTypes LIKE '%$matchtype%')";

	// Drop down list to select Games to display
	$q_Games = "SELECT DISTINCT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_LADDERS
	." WHERE (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	.$matchtype_string
	." ORDER BY Name";
	$result_Games = $sql->db_Query($q_Games);
	$numGames = mysql_numrows($result_Games);

	// Drop down list to select Match type to display
	$q_mt = "SELECT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_LADDERS
	." WHERE (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	.$game_string;
	$result_mt = $sql->db_Query($q_mt);
	$num_mt = mysql_numrows($result_mt);
	$gmatchtypes = '';
	for($i=0; $i<$num_mt; $i++)
	{
		$gmatchtypes  .= ','.mysql_result($result_mt,$i, TBL_GAMES.".MatchTypes");
	}

	$text .= '<form id="submitform" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">';
	$text .= '<div>';
	$text .= '<table>';
	$text .= '<tr>';
	// Games drop down
	$text .= '<td>'.EB_LADDERS_L9.'<br />';
	$text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($gameid == "All") ? 'selected="selected"' : '').'>'.EB_LADDERS_L10.'</option>';
	for($i=0; $i<$numGames; $i++)
	{
		$gname  = mysql_result($result_Games,$i, TBL_GAMES.".Name");
		$gid  = mysql_result($result_Games,$i, TBL_GAMES.".GameID");
		$text .= '<option value="'.$gid.'" '.(($gameid == $gid) ? 'selected="selected"': '').'>'.htmlspecialchars($gname).'</option>';
	}
	$text .= '</select>';
	$text .= '</td>';
	// Match Types drop down
	$text .= '<td>'.EB_LADDERS_L32.'<br />';
	$text .= '<select class="tbox" name="matchtype" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($matchtype == "All") ? 'selected="selected"' : '').'>'.EB_LADDERS_L10.'</option>';

	$gmatchtypes  = explode(",", $gmatchtypes);
	$gmatchtypes = array_unique($gmatchtypes);
	sort($gmatchtypes);
	foreach($gmatchtypes as $gmatchtype)
	{
		if ($gmatchtype!='') {
			$text .= '<option value="'.$gmatchtype.'" '.(($gmatchtype == $matchtype) ? 'selected="selected"' : '').'>'.htmlspecialchars($gmatchtype).'</option>';
		}
	}
	$text .= '</select>';
	$text .= '</td>';
	$text .= '</tr>';
	$text .= '</table>';
	$text .= '<br />';

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_LADDERS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_LADDERS.".MatchType = '$matchtype')";

	$q = "SELECT count(*) "
	." FROM ".TBL_LADDERS
	." WHERE (   (".TBL_LADDERS.".End_timestamp = '')"
	."        OR (".TBL_LADDERS.".End_timestamp > $time)) "
	."   AND (".TBL_LADDERS.".Status != 'draft')"
	.$game_string
	.$matchtype_string;
	$result = $sql->db_Query($q);
	$totalItems = mysql_result($result, 0);
	$pages->items_total = $totalItems;
	$pages->mid_range = eb_PAGINATION_MIDRANGE;
	$pages->paginate();

	$orderby_array = $array["$orderby"];
	$q = "SELECT ".TBL_LADDERS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_LADDERS.", "
	.TBL_GAMES
	." WHERE (   (".TBL_LADDERS.".End_timestamp = '')"
	."        OR (".TBL_LADDERS.".End_timestamp > $time)) "
	."   AND (".TBL_LADDERS.".Status != 'draft')"
	.$game_string
	.$matchtype_string
	." ORDER BY $orderby_array[1] $sort, LadderID DESC"
	." $pages->limit";

	$result = $sql->db_Query($q);
	$numLadders = mysql_numrows($result);
	if(!$result || ($numLadders < 0))
	{
		/* Error occurred, return given name by default */
		$text .= EB_LADDERS_L11.'</div>';
		$text .= '</form><br/>';
	} else if($numLadders == 0)
	{
		$text .= EB_LADDERS_L12.'</div>';
		$text .= '</form><br/>';
	}
	else
	{
		// Paginate & Sorting
		$items = '';
		foreach($array as $opt=>$opt_array)	$items .= ($opt == $orderby) ? '<option selected="selected" value="'.$opt.'">'.$opt_array[0].'</option>':'<option value="'.$opt.'">'.$opt_array[0].'</option>';

		// Paginate
		$text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
		$text .= '<span style="float:right">';
		// Sort By
		$text .= EB_PGN_L6;
		$text .= '<select class="tbox" name="orderby" onchange="this.form.submit()">';
		$text .= $items;
		$text .= '</select>';
		// Up/Down arrow
		$text .= '<input type="hidden" id="sort" name="sort" value=""/>';
		if ($sort =="ASC")
		{
			$text .= '<a href="javascript:buttonval(\'ASC\');" title="Ascending"><img src="'.e_PLUGIN.'ebattles/images/sort_asc.gif" alt="Asc" style="vertical-align:middle; border:0"/></a>';
		}
		else
		{
			$text .= '<a href="javascript:buttonval(\'DESC\');" title="Descending"><img src="'.e_PLUGIN.'ebattles/images/sort_desc.gif" alt="Desc" style="vertical-align:middle; border:0"/></a>';

		}

		$text .= '&nbsp;&nbsp;&nbsp;';
		// Go To Page
		$text .= $pages->display_jump_menu();
		$text .= '&nbsp;&nbsp;&nbsp;';
		// Items per page
		$text .= $pages->display_items_per_page();
		$text .= '</span>';
		$text .= '</div>';
		$text .= '</form><br/><br/>';

		/* Display table contents */
		$text .= '<table class="eb_table" style="width:95%"><tbody>';
		$text .= '<tr>
		<th class="eb_th2">'.EB_LADDERS_L13.'</th>
		<th colspan="2" class="eb_th2">'.EB_LADDERS_L14.'</th>
		<th class="eb_th2">'.EB_LADDERS_L15.'</th>
		<th class="eb_th2">'.EB_LADDERS_L16.'</th>
		<th class="eb_th2">'.EB_LADDERS_L17.'</th>
		<th class="eb_th2">'.EB_LADDERS_L18.'</th>
		<th class="eb_th2">'.EB_LADDERS_L19.'</th>
		</tr>';
		for($i=0; $i<$numLadders; $i++)
		{
			$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gIcon  = mysql_result($result,$i, TBL_GAMES.".Icon");
			$ladder_id  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$ladder = new Ladder($ladder_id);
			if($ladder->getField('Start_timestamp')!=0)
			{
				$start_timestamp_local = $ladder->getField('Start_timestamp') + TIMEOFFSET;
				$date_start = date("d M Y", $start_timestamp_local);
			}
			else
			{
				$date_start = "-";
			}
			if($ladder->getField('End_timestamp')!=0)
			{
				$end_timestamp_local = $ladder->getField('End_timestamp') + TIMEOFFSET;
				$date_end = date("d M Y", $end_timestamp_local);
			}
			else
			{
				$date_end = "-";
			}

			/* Nbr players */
			$q_2 = "SELECT COUNT(*) as NbrPlayers"
			." FROM ".TBL_PLAYERS
			." WHERE (Ladder = '$ladder_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrplayers = $row['NbrPlayers'];

			/* Nbr Teams */
			$q_2 = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Ladder = '$ladder_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrTeams = $row['NbrTeams'];

			/* Nbr matches */
			$q_2 = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
			." FROM ".TBL_MATCHS.", "
			.TBL_SCORES
			." WHERE (Ladder = '$ladder_id')"
			." AND (".TBL_MATCHS.".Status = 'active')"
			." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrmatches = $row['NbrMatches'];

			switch($ladder->getField('Type'))
			{
				case "One Player Ladder":
				$nbrTeamPlayers = $nbrplayers;
				break;
				case "Team Ladder":
				$nbrTeamPlayers = $nbrTeams.'/'.$nbrplayers;
				break;
				case "ClanWar":
				$nbrTeamPlayers = $nbrTeams;
				break;
				default:
			}

			if(
			($ladder->getField('End_timestamp')==0)
			||($ladder->getField('End_timestamp')>=$time)
			)
			{
				$text .= '<tr>
				<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.$ladder->getField('Name').'</a></td>
				<td class="eb_td"><img '.getGameIconResize($gIcon).'/></td>
				<td class="eb_td">'.$gName.'</td>
				<td class="eb_td">'.(($ladder->getField('MatchType')!='') ? $ladder->getField('MatchType').' - ' : '').ladderTypeToString($ladder->getField('Type')).'</td>
				<td class="eb_td">'.$date_start.'</td>
				<td class="eb_td">'.$date_end.'</td>
				<td class="eb_td">'.$nbrTeamPlayers.'</td>
				<td class="eb_td">'.$nbrmatches.'</td>
				</tr>';
			}
		}
		$text .= '</tbody></table><br />';
	}
}

function displayRecentLadders(){
	global $pref;
	global $sql;
	global $text;
	global $time;

	$pages = new Paginator;

	// how many rows to show per page
	$rowsPerPage = $pref['eb_default_items_per_page'];

	if (!isset($_GET['gameid'])) $_GET['gameid'] = "All";
	$gameid = $_GET['gameid'];

	if (!isset($_GET['matchtype'])) $_GET['matchtype'] = "All";
	$matchtype = $_GET['matchtype'];

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_LADDERS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_GAMES.".MatchTypes LIKE '%$matchtype%')";

	// Drop down list to select Games to display
	$q_Games = "SELECT DISTINCT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_LADDERS
	." WHERE (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	.$matchtype_string
	." ORDER BY Name";
	$result_Games = $sql->db_Query($q_Games);
	$numGames = mysql_numrows($result_Games);

	// Drop down list to select Match type to display
	$q_mt = "SELECT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_LADDERS
	." WHERE (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	.$game_string;
	$result_mt = $sql->db_Query($q_mt);
	$num_mt = mysql_numrows($result_mt);
	$gmatchtypes = '';
	for($i=0; $i<$num_mt; $i++)
	{
		$gmatchtypes  .= ','.mysql_result($result_mt,$i, TBL_GAMES.".MatchTypes");
	}
	$text .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">';
	$text .= '<table>';
	$text .= '<tr>';
	// Games drop down
	$text .= '<td>'.EB_LADDERS_L9.'<br />';
	$text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($gameid == "All") ? 'selected="selected"' : '').'>'.EB_LADDERS_L10.'</option>';
	for($i=0; $i<$numGames; $i++)
	{
		$gname  = mysql_result($result_Games,$i, TBL_GAMES.".Name");
		$gid  = mysql_result($result_Games,$i, TBL_GAMES.".GameID");
		$text .= '<option value="'.$gid.'" '.(($gameid == $gid) ? 'selected="selected"': '').'>'.htmlspecialchars($gname).'</option>';
	}
	$text .= '</select>';
	$text .= '</td>';
	// Match Types drop down
	$text .= '<td>'.EB_LADDERS_L32.'<br />';
	$text .= '<select class="tbox" name="matchtype" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($matchtype == "All") ? 'selected="selected"' : '').'>'.EB_LADDERS_L10.'</option>';

	$gmatchtypes  = explode(",", $gmatchtypes);
	$gmatchtypes = array_unique($gmatchtypes);
	sort($gmatchtypes);
	foreach($gmatchtypes as $gmatchtype)
	{
		if ($gmatchtype!='') {
			$text .= '<option value="'.$gmatchtype.'" '.(($gmatchtype == $matchtype) ? 'selected="selected"' : '').'>'.htmlspecialchars($gmatchtype).'</option>';
		}
	}
	$text .= '</select>';
	$text .= '</td>';
	$text .= '</tr>';
	$text .= '</table>';
	$text .= '</form>';
	$text .= '<br />';

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_LADDERS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_LADDERS.".MatchType = '$matchtype')";

	$q = "SELECT ".TBL_LADDERS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_LADDERS.", "
	.TBL_GAMES
	." WHERE (   (".TBL_LADDERS.".End_timestamp != '')"
	."       AND (".TBL_LADDERS.".End_timestamp < $time)) "
	.$game_string
	.$matchtype_string
	." LIMIT 0, $rowsPerPage";

	$result = $sql->db_Query($q);
	$numLadders = mysql_numrows($result);
	if(!$result || ($numLadders < 0))
	{
		/* Error occurred, return given name by default */
		$text .= '<div>'.EB_LADDERS_L11.'</div>';
	} else if($numLadders == 0)
	{
		$text .= '<div>'.EB_LADDERS_L12.'</div>';
	}
	else
	{
		/* Display table contents */
		$text .= '<table class="eb_table" style="width:95%"><tbody>';
		$text .= '<tr>
		<th class="eb_th2">'.EB_LADDERS_L13.'</th>
		<th colspan="2" class="eb_th2">'.EB_LADDERS_L14.'</th>
		<th class="eb_th2">'.EB_LADDERS_L15.'</th>
		<th class="eb_th2">'.EB_LADDERS_L16.'</th>
		<th class="eb_th2">'.EB_LADDERS_L17.'</th>
		<th class="eb_th2">'.EB_LADDERS_L18.'</th>
		<th class="eb_th2">'.EB_LADDERS_L19.'</th>
		</tr>';
		for($i=0; $i<$numLadders; $i++)
		{
			$gName  = mysql_result($result,$i, TBL_GAMES.".name");
			$gIcon  = mysql_result($result,$i, TBL_GAMES.".Icon");
			$ladder_id  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$ladder = new Ladder($ladder_id);

			if($ladder->getField('Start_timestamp')!=0)
			{
				$start_timestamp_local = $ladder->getField('Start_timestamp') + TIMEOFFSET;
				$date_start = date("d M Y", $start_timestamp_local);
			}
			else
			{
				$date_start = "-";
			}
			if($ladder->getField('End_timestamp')!=0)
			{
				$end_timestamp_local = $ladder->getField('End_timestamp') + TIMEOFFSET;
				$date_end = date("d M Y", $end_timestamp_local);
			}
			else
			{
				$date_end = "-";
			}

			/* Nbr players */
			$q_2 = "SELECT COUNT(*) as NbrPlayers"
			." FROM ".TBL_PLAYERS
			." WHERE (Ladder = '$ladder_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrplayers = $row['NbrPlayers'];

			/* Nbr Teams */
			$q_2 = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Ladder = '$ladder_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrTeams = $row['NbrTeams'];

			/* Nbr matches */
			$q_2 = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
			." FROM ".TBL_MATCHS.", "
			.TBL_SCORES
			." WHERE (Ladder = '$ladder_id')"
			." AND (".TBL_MATCHS.".Status = 'active')"
			." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrmatches = $row['NbrMatches'];

			switch($ladder->getField('Type'))
			{
				case "One Player Ladder":
				$nbrTeamPlayers = $nbrplayers;
				break;
				case "Team Ladder":
				$nbrTeamPlayers = $nbrTeams.'/'.$nbrplayers;
				break;
				case "ClanWar":
				$nbrTeamPlayers = $nbrTeams;
				break;
				default:
			}

			if(
			($ladder->getField('End_timestamp')!=0)
			&&($ladder->getField('End_timestamp')<$time)
			)
			{
				$text .= '<tr>
				<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.$ladder->getField('Name').'</a></td>
				<td class="eb_td"><img '.getGameIconResize($gIcon).'/></td>
				<td class="eb_td">'.$gName.'</td>
				<td class="eb_td">'.(($ladder->getField('MatchType')!='') ? $ladder->getField('MatchType').' - ' : '').ladderTypeToString($ladder->getField('Type')).'</td>
				<td class="eb_td">'.$date_start.'</td>
				<td class="eb_td">'.$date_end.'</td>
				<td class="eb_td">'.$nbrTeamPlayers.'</td>
				<td class="eb_td">'.$nbrmatches.'</td>
				</tr>';
			}
		}
		$text .= '</tbody></table><br />';
	}

	$text .= '<p>';
	$text .= '[<a href="'.e_PLUGIN.'ebattles/ladderspast.php">'.EB_LADDERS_L21.'</a>]';
	$text .= '</p>';
}

?>


