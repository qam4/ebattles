<?php
/**
* events.php
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

/**
* Display Past Events
*/
$text .= '<div id="tabs">';
$text .= '<ul>';
$text .= '<li><a href="#tabs-1">'.EB_EVENTP_L2.'</a></li>';
$text .= '</ul>';
$text .= '<div id="tabs-1">';
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

	if (!isset($_POST['gameid'])) $_POST['gameid'] = "All";

	// Drop down list to select Games to display
	$q = "SELECT DISTINCT ".TBL_GAMES.".*"
	." FROM ".TBL_GAMES.", "
	. TBL_EVENTS
	." WHERE (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
	." ORDER BY Name";
	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	$text .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">';
	$text .= '<table>';
	$text .= '<tr><td>';
	$text .= '<select class="tbox" name="gameid">';
	$text .= '<option value="All">'.EB_EVENTS_L10.'</option>';
	for($i=0; $i<$num_rows; $i++){
		$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
		$gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
		$text .= '<option value="'.$gid.'">'.htmlspecialchars($gName).'</option>';
	}
	$text .= '</select>';
	$text .= '</td>';
	$text .= '<td>';
	$text .= '<br />';
	$text .= ebImageTextButton('subgameselect', 'magnify.png', EB_EVENTS_L24);
	$text .= '</td>';
	$text .= '</tr>';
	$text .= '</table>';
	$text .= '</form>';
	$text .= '<br />';

	if ($_POST['gameid'] == "All")
	{
		/* set pagination variables */
		$q = "SELECT count(*) "
		." FROM ".TBL_EVENTS
		." WHERE (   (".TBL_EVENTS.".StartDateTime != '')"
		."       AND (".TBL_EVENTS.".StartDateTime < $time)) ";
		$result = $sql->db_Query($q);
		$totalItems = mysql_result($result, 0);
		$pages->items_total = $totalItems;
		$pages->mid_range = eb_PAGINATION_MIDRANGE;
		$pages->paginate();

		$q = "SELECT ".TBL_EVENTS.".*, "
		.TBL_GAMES.".*"
		." FROM ".TBL_EVENTS.", "
		.TBL_GAMES
		." WHERE (   (".TBL_EVENTS.".StartDateTime != '')"
		."       AND (".TBL_EVENTS.".StartDateTime < $time)) "
		."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
		." $pages->limit";
	}
	else
	{
		$q = "SELECT count(*) "
		." FROM ".TBL_EVENTS
		." WHERE (   (".TBL_EVENTS.".StartDateTime != '')"
		."       AND (".TBL_EVENTS.".StartDateTime < $time)) "
		."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")";
		$result = $sql->db_Query($q);
		$totalItems = mysql_result($result, 0);
		$pages->items_total = $totalItems;
		$pages->mid_range = eb_PAGINATION_MIDRANGE;
		$pages->paginate();

		$q = "SELECT ".TBL_EVENTS.".*, "
		.TBL_GAMES.".*"
		." FROM ".TBL_EVENTS.", "
		.TBL_GAMES
		." WHERE (   (".TBL_EVENTS.".StartDateTime != '')"
		."       AND (".TBL_EVENTS.".StartDateTime < $time)) "
		."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
		."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")"
		." $pages->limit";
	}
	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	if(!$result || ($num_rows < 0)){
		/* Error occurred, return given name by default */
		$text .= EB_EVENTS_L11;
	} else if ($num_rows == 0){
		$text .= '<div>'.EB_EVENTS_L12.'</div>';
	}
	else
	{

		// Paginate
		$text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
		$text .= '<span style="float:right">';
		// Go To Page
		$text .= $pages->display_jump_menu();
		$text .= '&nbsp;&nbsp;&nbsp;';
		// Items per page
		$text .= $pages->display_items_per_page();
		$text .= '</span><br /><br />';

		/* Display table contents */
		$text .= '<table class="eb_table" style="width:95%"><tbody>';
		$text .= '<tr>
		<th class="eb_th2">'.EB_EVENTS_L13.'</th>
		<th colspan="2" class="eb_th2">'.EB_EVENTS_L14.'</th>
		<th class="eb_th2">'.EB_EVENTS_L15.'</th>
		<th class="eb_th2">'.EB_EVENTS_L32.'</th>
		<th class="eb_th2">'.EB_EVENTS_L16.'</th>
		<th class="eb_th2">'.EB_EVENTS_L18.'</th>
		</tr>';
		for($i=0; $i<$num_rows; $i++){
			$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gIcon  = mysql_result($result,$i, TBL_GAMES.".Icon");
			$event_id  = mysql_result($result,$i, TBL_EVENTS.".EventID");
			$event = new Event($event_id);

			if($event->getField('StartDateTime')!=0)
			{
				$startdatetime_local = $event->getField('StartDateTime') + TIMEOFFSET;
				$date_start = date("d M Y", $startdatetime_local);
			}
			else
			{
				$date_start = "-";
			}

			// TODO: get the number of players correct
			/* Nbr players */
			$q_2 = "SELECT COUNT(*) as NbrPlayers"
			." FROM ".TBL_PLAYERS
			." WHERE (Event = '$event_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrplayers = $row['NbrPlayers'];

			/* Nbr Teams */
			$q_2 = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Event = '$event_id')";
			$result_2 = $sql->db_Query($q_2);
			$row = mysql_fetch_array($result_2);
			$nbrTeams = $row['NbrTeams'];

			switch($event->getField('Type'))
			{
				case "1v1":
				$nbrTeamPlayers = $nbrplayers.'/'.$event->getField('MaxNumberPlayers');
				break;
				default:
				$nbrTeamPlayers = $nbrTeams.'/'.$event->getField('MaxNumberPlayers');
				break;
			}

			if(
			($event->getField('StartDateTime')==0)
			||($event->getField('StartDateTime')>=$time)
			)
			{
				$text .= '<tr>
				<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/eventinfo.php?EventID='.$event_id.'">'.$event->getField('Name').'</a></td>
				<td class="eb_td"><img '.getGameIconResize($gIcon).'/></td>
				<td class="eb_td">'.$gName.'</td>
				<td class="eb_td">'.eventTypeToString($event->getField('Type')).'</td>
				<td class="eb_td">'.$event->getField('MatchType').'</td>
				<td class="eb_td">'.$date_start.'</td>
				<td class="eb_td">'.$nbrTeamPlayers.'</td>
				</tr>';
			}
		}
		$text .= '</tbody></table>';
	}
	$text .= '<br />'.EB_EVENTP_L3.' [<a href="'.e_PLUGIN.'ebattles/events.php">'.EB_EVENTP_L4.'</a>]<br />';
}

?>
