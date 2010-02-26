<?php
/**
* events.php
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");

require_once(HEADERF);

$text = "
<script type='text/javascript' src='./js/tabpane.js'></script>
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

$text .= '
<div class="tab-pane" id="tab-pane-2">
';
/**
* Display Current Events
*/
$text .= '
<div class="tab-page">
<div class="tab">'.EB_EVENTS_L2.'</div>
';
displayCurrentEvents();
$text .= '</div>';

/**
* Display Recent Events
*/
$text .= '
<div class="tab-page">
<div class="tab">'.EB_EVENTS_L3.'</div>
';
displayRecentEvents();
$text .= '
</div>
</div>
';

$text .= disclaimer();

$text .= '
<script type="text/javascript">
//<![CDATA[
setupAllTabs();
//]]>
</script>
';

$ns->tablerender(EB_EVENTS_L1, $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayEvents - Displays the events database table in
* a nicely formatted html table.
*/
function displayCurrentEvents(){
    global $pref;
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

    if (!isset($_GET['orderby'])) $_GET['orderby'] = 'game';
    $orderby=$_GET['orderby'];

    $sort = "ASC";
    if(isset($_GET["sort"]) && !empty($_GET["sort"]))
    {
        $sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
    }

    // Drop down list to select Games to display
    $q = "SELECT DISTINCT ".TBL_GAMES.".*"
    ." FROM ".TBL_GAMES.", "
    . TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY Name";
    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    $text .= '<form id="submitform" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">';
    $text .= '<div>';
    $text .= '<table>';
    $text .= '<tr><td>';
    $text .= EB_EVENTS_L9.'<br />';
    $text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
    if ($gameid == "All")
    {
        $text .= '<option value="All" selected="selected">'.EB_EVENTS_L10.'</option>';
    }
    else
    {
        $text .= '<option value="All">'.EB_EVENTS_L10.'</option>';
    }
    for($i=0; $i<$num_rows; $i++)
    {
        $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
        $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
        if ($gameid == $gid)
        {
            $text .= '<option value="'.$gid.'" selected="selected">'.htmlspecialchars($gname).'</option>';
        }
        else
        {
            $text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
        }
    }
    $text .= '</select>';
    $text .= '</td>';
    $text .= '</tr>';
    $text .= '</table>';
    $text .= '<br />';

    if ($gameid == "All")
    {
        $q = "SELECT count(*) "
        ." FROM ".TBL_EVENTS
        ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
        ."        OR (".TBL_EVENTS.".End_timestamp > $time)) ";
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
        ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
        ."        OR (".TBL_EVENTS.".End_timestamp > $time)) "
        ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
        ." ORDER BY $orderby_array[1] $sort"
        ." $pages->limit";
    }
    else
    {
        $q = "SELECT count(*) "
        ." FROM ".TBL_EVENTS
        ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
        ."        OR (".TBL_EVENTS.".End_timestamp > $time)) "
        ."   AND (".TBL_EVENTS.".Game = '$gameid')";
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
        ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
        ."        OR (".TBL_EVENTS.".End_timestamp > $time)) "
        ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
        ."   AND (".TBL_EVENTS.".Game = '$gameid')"
        ." ORDER BY $orderby_array[1] $sort"
        ." $pages->limit";
    }

    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    if(!$result || ($num_rows < 0))
    {
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
        for($i=0; $i<$num_rows; $i++)
        {
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
                ($eend==0)
                ||($eend>=$time)
            )
            {
                $text .= '<tr>
                <td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$eid.'">'.$ename.'</a></td>
                <td class="forumheader3"><img '.getGameIconResize($gicon).'/></td>
                <td class="forumheader3">'.$gname.'</td>
                <td class="forumheader3">'.eventType($etype).'</td>
                <td class="forumheader3">'.$date_start.'</td>
                <td class="forumheader3">'.$date_end.'</td>
                <td class="forumheader3">'.$nbrTeamPlayers.'</td>
                <td class="forumheader3">'.$nbrmatches.'</td>
                </tr>';
            }
        }
        $text .= '</tbody></table><br />';
    }

    if(check_class($pref['eb_events_create_class']))
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/eventcreate.php" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="userid" value="'.USERID.'"/>';
        $text .= '<input type="hidden" name="username" value="'.USERNAME.'"/>';
        $text .= '<input class="button" type="submit" name="createevent" value="'.EB_EVENTS_L20.'"/>';
        $text .= '</div>';
        $text .= '</form>';
    }
    else
    {
        //$text .= '<div>'.EB_EVENTC_L2.'</div>';
    }
}

function displayRecentEvents(){
    global $sql;
    global $session;
    global $text;
    global $time;
    global $pref;

    $pages = new Paginator;

    // how many rows to show per page
    $rowsPerPage = $pref['eb_default_items_per_page'];

    if (!isset($_GET['gameid'])) $_GET['gameid'] = "All";
    $gameid = $_GET['gameid'];

    $q = "SELECT DISTINCT ".TBL_GAMES.".*"
    ." FROM ".TBL_GAMES.", "
    . TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY Name";
    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    $text .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">';
    $text .= '<table>';
    $text .= '<tr><td>';
    $text .= EB_EVENTS_L9.'<br />';
    $text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
    if ($gameid == "All")
    {
        $text .= '<option value="All" selected="selected">'.EB_EVENTS_L10.'</option>';
    }
    else
    {
        $text .= '<option value="All">'.EB_EVENTS_L10.'</option>';
    }
    for($i=0; $i<$num_rows; $i++)
    {
        $gname  = mysql_result($result,$i, TBL_GAMES.".name");
        $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
        if ($gameid == $gid)
        {
            $text .= '<option value="'.$gid.'" selected="selected">'.htmlspecialchars($gname).'</option>';
        }
        else
        {
            $text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
        }
    }
    $text .= '</select>';
    $text .= '</td>';
    $text .= '</tr>';
    $text .= '</table>';
    $text .= '</form>';
    $text .= '<br />';

    if ($gameid == "All")
    {
        $q = "SELECT ".TBL_EVENTS.".*, "
        .TBL_GAMES.".*"
        ." FROM ".TBL_EVENTS.", "
        .TBL_GAMES
        ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
        ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
        ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
        ." LIMIT 0, $rowsPerPage";
    }
    else
    {
        $q = "SELECT ".TBL_EVENTS.".*, "
        .TBL_GAMES.".*"
        ." FROM ".TBL_EVENTS.", "
        .TBL_GAMES
        ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
        ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
        ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
        ."   AND (".TBL_EVENTS.".Game = '$gameid')"
        ." LIMIT 0, $rowsPerPage";
    }

    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    if(!$result || ($num_rows < 0))
    {
        $text .= EB_EVENTS_L11;
        return;
    }
    if($num_rows == 0)
    {
        $text .= '<div>'.EB_EVENTS_L12.'</div>';
        return;
    }
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
    for($i=0; $i<$num_rows; $i++)
    {
        $gname  = mysql_result($result,$i, TBL_GAMES.".name");
        $gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");
        $eid  = mysql_result($result,$i, TBL_EVENTS.".eventid");
        $ename  = mysql_result($result,$i, TBL_EVENTS.".name");
        $etype = mysql_result($result,$i, TBL_EVENTS.".type");
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
            &&($eend<$time)
        )
        {
            $text .= '<tr>
            <td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$eid.'">'.$ename.'</a></td>
            <td class="forumheader3"><img '.getGameIconResize($gicon).'/></td>
            <td class="forumheader3">'.$gname.'</td>
            <td class="forumheader3">'.eventType($etype).'</td>
            <td class="forumheader3">'.$date_start.'</td>
            <td class="forumheader3">'.$date_end.'</td>
            <td class="forumheader3">'.$nbrTeamPlayers.'</td>
            <td class="forumheader3">'.$nbrmatches.'</td>
            </tr>';
        }
    }
    $text .= '</tbody></table><br />';

    $text .= '<p>';
    $text .= '[<a href="'.e_PLUGIN.'ebattles/eventspast.php">'.EB_EVENTS_L21.'</a>]';
    $text .= '</p>';
}
?>


