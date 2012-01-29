<?php
/**
* tournaments.php
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
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
$text .= '<li><a href="#tabs-1">'.EB_TOURNAMENTS_L2.'</a></li>';
$text .= '<li><a href="#tabs-2">'.EB_TOURNAMENTS_L3.'</a></li>';
$text .= '</ul>';
/**
* Display Current Tournaments
*/
$text .= '<div id="tabs-1">';
displayCurrentTournaments();
$text .= '</div>';

/**
* Display Recent Tournaments
*/
$text .= '<div id="tabs-2">';
displayRecentTournaments();
$text .= '
</div>
</div>
';

$text .= disclaimer();

$ns->tablerender(EB_TOURNAMENTS_L1, $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayTournaments - Displays the tournaments database table in
* a nicely formatted html table.
*/
function displayCurrentTournaments(){
	global $pref;
	global $sql;
	global $text;
	global $time;
	$pages = new Paginator;

	if(check_class($pref['eb_tournaments_create_class']))
	{
		$text .= '<form action="'.e_PLUGIN.'ebattles/tournamentcreate.php" method="post">';
		$text .= '<div>';
		$text .= '<input type="hidden" name="userid" value="'.USERID.'"/>';
		$text .= '<input type="hidden" name="username" value="'.USERNAME.'"/>';
		$text .= '</div>';
		$text .= ebImageTextButton('createtournament', 'add.png', EB_TOURNAMENTS_L20);
		$text .= '</form><br />';
	}
	else
	{
		//$text .= '<div>'.EB_TOURNAMENTC_L2.'</div>';
	}

	$array = array(
	'latest' => array(EB_TOURNAMENTS_L4,'TournamentID'),
	'name'   => array(EB_TOURNAMENTS_L5, TBL_TOURNAMENTS.'.Name'),
	'game'   => array(EB_TOURNAMENTS_L6, TBL_GAMES.'.Name'),
	'type'   => array(EB_TOURNAMENTS_L7, TBL_TOURNAMENTS.'.Type'),
	'start'  => array(EB_TOURNAMENTS_L8, TBL_TOURNAMENTS.'.StartDateTime')
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

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_TOURNAMENTS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_TOURNAMENTS.".MatchTypes LIKE '%$matchtype%')";

	// Drop down list to select Games to display
	$q_Games = "SELECT DISTINCT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_TOURNAMENTS
	." WHERE (".TBL_TOURNAMENTS.".Game = ".TBL_GAMES.".GameID)"
	.$matchtype_string
	." ORDER BY Name";
	$result_Games = $sql->db_Query($q_Games);
	$numGames = mysql_numrows($result_Games);

	// Drop down list to select Match type to display
	$q_mt = "SELECT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_TOURNAMENTS
	." WHERE (".TBL_TOURNAMENTS.".Game = ".TBL_GAMES.".GameID)"
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
	$text .= '<td>'.EB_TOURNAMENTS_L9.'<br />';
	$text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($gameid == "All") ? 'selected="selected"' : '').'>'.EB_TOURNAMENTS_L10.'</option>';
	for($i=0; $i<$numGames; $i++)
	{
		$gname  = mysql_result($result_Games,$i, TBL_GAMES.".Name");
		$gid  = mysql_result($result_Games,$i, TBL_GAMES.".GameID");
		$text .= '<option value="'.$gid.'" '.(($gameid == $gid) ? 'selected="selected"': '').'>'.htmlspecialchars($gname).'</option>';
	}
	$text .= '</select>';
	$text .= '</td>';
	// Match Types drop down
	$text .= '<td>'.EB_TOURNAMENTS_L32.'<br />';
	$text .= '<select class="tbox" name="matchtype" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($matchtype == "All") ? 'selected="selected"' : '').'>'.EB_TOURNAMENTS_L10.'</option>';

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

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_TOURNAMENTS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_TOURNAMENTS.".MatchType = '$matchtype')";

	$q = "SELECT count(*) "
	." FROM ".TBL_TOURNAMENTS
	." WHERE (".TBL_TOURNAMENTS.".Status != 'finished')"
	.$game_string
	.$matchtype_string;
	$result = $sql->db_Query($q);
	$totalItems = mysql_result($result, 0);
	$pages->items_total = $totalItems;
	$pages->mid_range = eb_PAGINATION_MIDRANGE;
	$pages->paginate();

	$orderby_array = $array["$orderby"];
	$q = "SELECT ".TBL_TOURNAMENTS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_TOURNAMENTS.", "
	.TBL_GAMES
	." WHERE (".TBL_TOURNAMENTS.".Status != 'finished')"
	."   AND (".TBL_TOURNAMENTS.".Status != 'draft')"
	.$game_string
	.$matchtype_string
	." ORDER BY $orderby_array[1] $sort, TournamentID DESC"
	." $pages->limit";

	$result = $sql->db_Query($q);
	$numTournaments = mysql_numrows($result);
	if(!$result || ($numTournaments < 0))
	{
		/* Error occurred, return given name by default */
		$text .= EB_TOURNAMENTS_L11.'</div>';
		$text .= '</form><br/>';
	} else if($numTournaments == 0)
	{
		$text .= EB_TOURNAMENTS_L12.'</div>';
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
		<th class="eb_th2">'.EB_TOURNAMENTS_L13.'</th>
		<th colspan="2" class="eb_th2">'.EB_TOURNAMENTS_L14.'</th>
		<th class="eb_th2">'.EB_TOURNAMENTS_L15.'</th>
		<th class="eb_th2">'.EB_TOURNAMENTS_L16.'</th>
		<th class="eb_th2">'.EB_TOURNAMENTS_L18.'</th>
		<th class="eb_th2">'.EB_TOURNAMENTS_L33.'</th>
		</tr>';
		for($i=0; $i < $numTournaments; $i++)
		{
			$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gIcon  = mysql_result($result,$i, TBL_GAMES.".Icon");
			$tournament_id  = mysql_result($result,$i, TBL_TOURNAMENTS.".TournamentID");
			$tournament = new Tournament($tournament_id);
			if($tournament->getField('StartDateTime')!=0)
			{
				$startdatetime_local = $tournament->getField('StartDateTime') + TIMEOFFSET;
				$date_start = date("d M Y", $startdatetime_local);
			}
			else
			{
				$date_start = "-";
			}

			// TODO: get the number of players correct
			/* Nbr players */
			$q_2 = "SELECT COUNT(*) as NbrPlayers"
			." FROM ".TBL_TPLAYERS
			." WHERE (Tournament = '$tournament_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrplayers = $row['NbrPlayers'];

			/* Nbr Teams */
			$q_2 = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TTEAMS
			." WHERE (".TBL_TTEAMS.".Tournament = '$tournament_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrTeams = $row['NbrTeams'];

			switch($tournament->getField('MatchType'))
			{
				case "1v1":
				$nbrTeamPlayers = $nbrplayers.'/'.$tournament->getField('MaxNumberPlayers');
				break;
				default:
				$nbrTeamPlayers = $nbrTeams.'/'.$tournament->getField('MaxNumberPlayers');
				break;
			}

			$text .= '<tr>
			<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'">'.$tournament->getField('Name').'</a></td>
			<td class="eb_td"><img '.getGameIconResize($gIcon).'/></td>
			<td class="eb_td">'.$gName.'</td>
			<td class="eb_td">'.(($tournament->getField('MatchType')!='') ? $tournament->getField('MatchType').' - ' : '').tournamentTypeToString($tournament->getField('Type')).'</td>
			<td class="eb_td">'.$date_start.'</td>
			<td class="eb_td">'.$nbrTeamPlayers.'</td>
			<td class="eb_td">'.$tournament->getField('Status').'</td>
			</tr>';
			// TODO: status2string
		}
		$text .= '</tbody></table><br />';
	}
}

function displayRecentTournaments(){
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

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_TOURNAMENTS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_GAMES.".MatchTypes LIKE '%$matchtype%')";

	// Drop down list to select Games to display
	$q_Games = "SELECT DISTINCT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_TOURNAMENTS
	." WHERE (".TBL_TOURNAMENTS.".Game = ".TBL_GAMES.".GameID)"
	." ORDER BY Name";
	$result_Games = $sql->db_Query($q_Games);
	$numGames = mysql_numrows($result_Games);

	// Drop down list to select Match type to display
	$q_mt = "SELECT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_TOURNAMENTS
	." WHERE (".TBL_TOURNAMENTS.".Game = ".TBL_GAMES.".GameID)"
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
	$text .= '<td>'.EB_TOURNAMENTS_L9.'<br />';
	$text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($gameid == "All") ? 'selected="selected"' : '').'>'.EB_TOURNAMENTS_L10.'</option>';
	for($i=0; $i<$numGames; $i++)
	{
		$gname  = mysql_result($result_Games,$i, TBL_GAMES.".Name");
		$gid  = mysql_result($result_Games,$i, TBL_GAMES.".GameID");
		$text .= '<option value="'.$gid.'" '.(($gameid == $gid) ? 'selected="selected"': '').'>'.htmlspecialchars($gname).'</option>';
	}
	$text .= '</select>';
	$text .= '</td>';
	// Match Types drop down
	$text .= '<td>'.EB_TOURNAMENTS_L32.'<br />';
	$text .= '<select class="tbox" name="matchtype" onchange="this.form.submit()">';
	$text .= '<option value="All" '.(($matchtype == "All") ? 'selected="selected"' : '').'>'.EB_TOURNAMENTS_L10.'</option>';

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

	$game_string = ($gameid == "All") ? "" : "   AND (".TBL_TOURNAMENTS.".Game = '$gameid')";
	$matchtype_string = ($matchtype == "All") ? "" : "   AND (".TBL_TOURNAMENTS.".MatchType = '$matchtype')";

	$q = "SELECT ".TBL_TOURNAMENTS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_TOURNAMENTS.", "
	.TBL_GAMES
	." WHERE (".TBL_TOURNAMENTS.".Status = 'finished')"
	.$game_string
	.$matchtype_string
	." LIMIT 0, $rowsPerPage";

	$result = $sql->db_Query($q);
	$numTournaments = mysql_numrows($result);
	if(!$result || ($numTournaments < 0))
	{
		/* Error occurred, return given name by default */
		$text .= '<div>'.EB_TOURNAMENTS_L11.'</div>';
	} else if($numTournaments == 0)
	{
		$text .= '<div>'.EB_TOURNAMENTS_L12.'</div>';
	}
	else
	{
		/* Display table contents */
		$text .= '<table class="eb_table" style="width:95%"><tbody>';
		$text .= '<tr>
		<th class="eb_th2">'.EB_TOURNAMENTS_L13.'</th>
		<th colspan="2" class="eb_th2">'.EB_TOURNAMENTS_L14.'</th>
		<th class="eb_th2">'.EB_TOURNAMENTS_L15.'</th>
		<th class="eb_th2">'.EB_TOURNAMENTS_L16.'</th>
		<th class="eb_th2">'.EB_TOURNAMENTS_L18.'</th>
		</tr>';
		for($i=0; $i < $numTournaments; $i++)
		{
			$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gIcon  = mysql_result($result,$i, TBL_GAMES.".Icon");
			$tournament_id  = mysql_result($result,$i, TBL_TOURNAMENTS.".TournamentID");
			$tournament = new Tournament($tournament_id);

			if($tournament->getField('StartDateTime')!=0)
			{
				$startdatetime_local = $tournament->getField('StartDateTime') + TIMEOFFSET;
				$date_start = date("d M Y", $startdatetime_local);
			}
			else
			{
				$date_start = "-";
			}

			// TODO: get the number of players correct
			/* Nbr players */
			$q_2 = "SELECT COUNT(*) as NbrPlayers"
			." FROM ".TBL_TPLAYERS
			." WHERE (Tournament = '$tournament_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrplayers = $row['NbrPlayers'];

			/* Nbr Teams */
			$q_2 = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TTEAMS
			." WHERE (".TBL_TTEAMS.".Tournament = '$tournament_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrTeams = $row['NbrTeams'];

			switch($tournament->getField('Type'))
			{
				case "1v1":
				$nbrTeamPlayers = $nbrplayers.'/'.$tournament->getField('MaxNumberPlayers');
				break;
				default:
				$nbrTeamPlayers = $nbrTeams.'/'.$tournament->getField('MaxNumberPlayers');
				break;
			}

			if(
			($tournament->getField('StartDateTime')==0)
			||($tournament->getField('StartDateTime')<$time)
			)
			{
				$text .= '<tr>
				<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'">'.$tournament->getField('Name').'</a></td>
				<td class="eb_td"><img '.getGameIconResize($gIcon).'/></td>
				<td class="eb_td">'.$gName.'</td>
				<td class="eb_td">'.(($tournament->getField('MatchType')!='') ? $tournament->getField('MatchType').' - ' : '').tournamentTypeToString($tournament->getField('Type')).'</td>
				<td class="eb_td">'.$date_start.'</td>
				<td class="eb_td">'.$nbrTeamPlayers.'</td>
				</tr>';
			}
		}
		$text .= '</tbody></table><br />';
	}

	$text .= '<p>';
	$text .= '[<a href="'.e_PLUGIN.'ebattles/tournamentspast.php">'.EB_TOURNAMENTS_L21.'</a>]';
	$text .= '</p>';
}
?>


