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

$text .= '
<script type="text/javascript" src="./js/tabpane.js"></script>
';

/**
* Display Users Table
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

    if (!isset($_POST['gameid'])) $_POST['gameid'] = "All";

    // Drop down list to select Games to display
    $q = "SELECT DISTINCT ".TBL_GAMES.".*"
    ." FROM ".TBL_GAMES.", "
    . TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY Name";
    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    $text .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">';
    $text .= '<table>';
    $text .= '<tr><td>';
    $text .= EB_EVENTS_L9.'<br />';
    $text .= '<select class="tbox" name="gameid">';
    $text .= '<option value="All">'.EB_EVENTS_L10.'</option>';
    for($i=0; $i<$num_rows; $i++){
        $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
        $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
        $text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
    }
    $text .= '</select>';
    $text .= '</td>';
    $text .= '<td>';
    $text .= '<br />';
    $text .= '<input class="button" type="submit" name="subgameselect" value="'.EB_EVENTS_L24.'"/>';
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
        ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
        ."       AND (".TBL_EVENTS.".End_timestamp < $time)) ";
        $result = $sql->db_Query($q);
        $totalItems = mysql_result($result, 0);
        $pages->items_total = $totalItems;
        $pages->mid_range = eb_PAGINATION_MIDRANGE;
        $pages->paginate();

        $q = "SELECT ".TBL_EVENTS.".*, "
        .TBL_GAMES.".*"
        ." FROM ".TBL_EVENTS.", "
        .TBL_GAMES
        ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
        ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
        ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
        ." $pages->limit";
    }
    else
    {
        $q = "SELECT count(*) "
        ." FROM ".TBL_EVENTS
        ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
        ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
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
        ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
        ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
        ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
        ."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")"
        ." $pages->limit";
    }
    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    if(!$result || ($num_rows < 0)){
        $text .= EB_EVENTS_L11;
        return;
    }
    if($num_rows == 0){
        $text .= '<div>'.EB_EVENTS_L12.'</div>';
        return;
    }

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
    for($i=0; $i<$num_rows; $i++){
        $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
        $gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");
        $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
        $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
        $etype = mysql_result($result,$i, TBL_EVENTS.".Type");
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

        if(
        ($eend!=0)
        ||($eend<=$time)
        )
        {
            $text .= '<tr>
            <td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$eid.'">'.$ename.'</a></td>
            <td class="forumheader3"><img '.getGameIconResize($gicon).'/></td>
            <td class="forumheader3">'.$gname.'</td>
            <td class="forumheader3">'.eventType($etype).'</td>
            <td class="forumheader3">'.$date_start.'</td>
            <td class="forumheader3">'.$date_end.'</td>
            <td class="forumheader3">'.$nbrplayers.'</td>
            <td class="forumheader3">'.$nbrmatches.'</td>
            </tr>';
        }
    }
    $text .= '</tbody></table>';

    $text .= '<br />'.EB_EVENTP_L3.' [<a href="'.e_PLUGIN.'ebattles/events.php">'.EB_EVENTP_L4.'</a>]<br />';
}

?>
