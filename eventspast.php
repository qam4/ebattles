<?php
/**
* eventspast.php
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text .= '
<script type="text/javascript" src="./js/tabpane.js"></script>
';

/**
* Display Past Events
*/
$text .= '
<div class="tab-pane" id="tab-pane-9">
<div class="tab-page">
<div class="tab">'.EB_EVENTP_L2.'</div>
';
displayPastEvents();
$text .= '
</div>
</div>
';

$ns->tablerender(EB_EVENTP_L1, $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayEvents - Displays the events database table in
* a nicely formatted html table.
*/
function displayPastEvents(){
	global $sql;
	global $text;
	global $time;

	$pages = new Paginator;

	$array = array(
	'latest' => array(EB_EVENTS_L4,'EventID'),
	'name'   => array(EB_EVENTS_L5, TBL_EVENTS.'.Name'),
	'game'   => array(EB_EVENTS_L6, TBL_GAMES.'.Name'),
	'type'   => array(EB_EVENTS_L7, TBL_EVENTS.'.Type'),
	'start'  => array(EB_EVENTS_L8, TBL_EVENTS.'.Start_timestamp')
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

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_EVENTS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_GAMES.".MatchTypes LIKE '%$matchtype%')";

	// Drop down list to select Games to display
	$q_Games = "SELECT DISTINCT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_EVENTS
	." WHERE (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
	.$matchtype_string
	." ORDER BY Name";
	$result_Games = $sql->db_Query($q_Games);
	$num_Games = mysql_numrows($result_Games);

	// Drop down list to select Match type to display
	$q_mt = "SELECT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_EVENTS
	." WHERE (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
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
	$text .= '<td>'.EB_EVENTS_L9.'<br />';
	$text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($gameid == "All") ? 'selected="selected"' : '').'>'.EB_EVENTS_L10.'</option>';
	for($i=0; $i<$num_Games; $i++)
	{
		$gname  = mysql_result($result_Games,$i, TBL_GAMES.".Name");
		$gid  = mysql_result($result_Games,$i, TBL_GAMES.".GameID");
		$text .= '<option value="'.$gid.'" '.(($gameid == $gid) ? 'selected="selected"': '').'>'.htmlspecialchars($gname).'</option>';
	}
	$text .= '</select>';
	$text .= '</td>';
	// Match Types drop down
	$text .= '<td>'.EB_EVENTS_L32.'<br />';
	$text .= '<select class="tbox" name="matchtype" onchange="this.form.submit()">';
		$text .= '<option value="All" '.(($matchtype == "All") ? 'selected="selected"' : '').'>'.EB_EVENTS_L10.'</option>';

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

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_EVENTS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_EVENTS.".MatchType = '$matchtype')";

	$q = "SELECT count(*) "
	." FROM ".TBL_EVENTS
	." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
	."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
	.$game_string
	.$matchtype_string;
	$result = $sql->db_Query($q);
	$totalItems = mysql_result($result, 0);
	$pages->items_total = $totalItems;
	$pages->mid_range = eb_PAGINATION_MIDRANGE;
	$pages->paginate();

	$orderby_array = $array["$orderby"];
	$q = "SELECT ".TBL_EVENTS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_EVENTS.", "
	.TBL_GAMES
	." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
	."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
	."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
	.$game_string
	.$matchtype_string
	." ORDER BY $orderby_array[1] $sort, EventID DESC"
	." $pages->limit";

	$result = $sql->db_Query($q);
	/* Error occurred, return given name by default */
	$num_rows = mysql_numrows($result);
	if(!$result || ($num_rows < 0)) {
		$text .= EB_EVENTS_L11;
		return;
	}
	if($num_rows == 0)
	{
		$text .= EB_EVENTS_L12.'</div>';
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
		$text .= '<table class="fborder" style="width:95%"><tbody>';
		$text .= '<tr>
		<td class="forumheader"><b>'.EB_EVENTS_L13.'</b></td>
		<td colspan="2" class="forumheader"><b>'.EB_EVENTS_L14.'</b></td>
		<td class="forumheader"><b>'.EB_EVENTS_L15.'</b></td>
		<td class="forumheader"><b>'.EB_EVENTS_L16.'</b></td>
		<td class="forumheader"><b>'.EB_EVENTS_L17.'</b></td>
		<td class="forumheader"><b>'.EB_EVENTS_L18.'</b></td>
		<td class="forumheader"><b>'.EB_EVENTS_L19.'</b></td>
		</tr>';
		for($i=0; $i<$num_rows; $i++) {
			$gname  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");
			$eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
			$ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
			$etype = mysql_result($result,$i, TBL_EVENTS.".Type");
			$ematchtype = mysql_result($result,$i, TBL_EVENTS.".MatchType");
			$estart = mysql_result($result,$i, TBL_EVENTS.".Start_timestamp");
			$eend = mysql_result($result,$i, TBL_EVENTS.".End_timestamp");
			if($estart!=0)
			{
				$estart_local = $estart + TIMEOFFSET;
				$date_start = date("d M Y",$estart_local);
			}
			else
			{
				$date_start = "-";
			}
			if($eend!=0)
			{
				$eend_local = $eend + TIMEOFFSET;
				$date_end = date("d M Y",$eend_local);
			}
			else
			{
				$date_end = "-";
			}

			/* Nbr players */
			$q_2 = "SELECT COUNT(*) as NbrPlayers"
			." FROM ".TBL_PLAYERS
			." WHERE (Event = '$eid')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrplayers = $row['NbrPlayers'];

			/* Nbr Teams */
			$q_2 = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Event = '$eid')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrTeams = $row['NbrTeams'];

			/* Nbr matches */
			$q_2 = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
			." FROM ".TBL_MATCHS.", "
			.TBL_SCORES
			." WHERE (Event = '$eid')"
			." AND (".TBL_MATCHS.".Status = 'active')"
			." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrmatches = $row['NbrMatches'];

			switch($etype)
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
			($eend!=0)
			||($eend<=$time)
			)
			{
				$text .= '<tr>
				<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$eid.'">'.$ename.'</a></td>
				<td class="forumheader3"><img '.getGameIconResize($gicon).'/></td>
				<td class="forumheader3">'.$gname.'</td>
				<td class="forumheader3">'.(($ematchtype!='') ? $ematchtype.' - ' : '').eventType($etype).'</td>
				<td class="forumheader3">'.$date_start.'</td>
				<td class="forumheader3">'.$date_end.'</td>
				<td class="forumheader3">'.$nbrTeamPlayers.'</td>
				<td class="forumheader3">'.$nbrmatches.'</td>
				</tr>';
			}
		}
		$text .= '</tbody></table><br />';

		$text .= '<p>';
		$text .= EB_EVENTP_L3.' [<a href="'.e_PLUGIN.'ebattles/events.php">'.EB_EVENTP_L4.'</a>]';
		$text .= '</p>';
	}
}

?>


