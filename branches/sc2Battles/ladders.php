<?php
/**
* ladders.php
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/ladder.php");
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
* Display Current Ladders
*/
$text .= '
<div class="tab-page">
<div class="tab">'.EB_LADDERS_L2.'</div>
';
displayCurrentLadders();
$text .= '</div>';

/**
* Display Recent Ladders
*/
$text .= '
<div class="tab-page">
<div class="tab">'.EB_LADDERS_L3.'</div>
';
displayRecentLadders();
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
/*
        $text .= '<span class="buttons"><a href="'.e_PLUGIN.'ebattles/matchdelete.php?action=createladder&amp;username='.$USERNAME.'&amp;userid='.$USERID.'" title="'.EB_LADDERS_L20.'" style="text-decoration:none"><img src="'.e_PLUGIN.'ebattles/images/add.png" alt="'.EB_LADDERS_L20.'"/>'.EB_LADDERS_L20.'</a></span>';
        $text .= '<div><img src="'.e_PLUGIN.'ebattles/images/add.png" alt="'.EB_LADDERS_L20.'" style="vertical-align:middle"/>'.EB_LADDERS_L20.'</div>';
        $text .= '<div><button type="submit" name="createladder"><img src="'.e_PLUGIN.'ebattles/images/add.png" alt="'.EB_LADDERS_L20.'" style="vertical-align:middle"/>'.EB_LADDERS_L20.'</button></div>';
*/
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
    . TBL_LADDERS
    ." WHERE (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY Name";
    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    $text .= '<form id="submitform" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">';
    $text .= '<div>';
    $text .= '<table>';
    $text .= '<tr><td>';
    $text .= EB_LADDERS_L9.'<br />';
    $text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
    if ($gameid == "All")
    {
        $text .= '<option value="All" selected="selected">'.EB_LADDERS_L10.'</option>';
    }
    else
    {
        $text .= '<option value="All">'.EB_LADDERS_L10.'</option>';
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
        ." FROM ".TBL_LADDERS
        ." WHERE (   (".TBL_LADDERS.".End_timestamp = '')"
        ."        OR (".TBL_LADDERS.".End_timestamp > $time)) ";
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
        ."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
        ." ORDER BY $orderby_array[1] $sort, LadderID DESC"
        ." $pages->limit";
    }
    else
    {
        $q = "SELECT count(*) "
        ." FROM ".TBL_LADDERS
        ." WHERE (   (".TBL_LADDERS.".End_timestamp = '')"
        ."        OR (".TBL_LADDERS.".End_timestamp > $time)) "
        ."   AND (".TBL_LADDERS.".Game = '$gameid')";
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
        ."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
        ."   AND (".TBL_LADDERS.".Game = '$gameid')"
        ." ORDER BY $orderby_array[1] $sort, LadderID DESC"
        ." $pages->limit";
    }

    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    if(!$result || ($num_rows < 0))
    {
        $text .= EB_LADDERS_L11;
        return;
    }
    if($num_rows == 0)
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
        $text .= '<table class="fborder" style="width:95%"><tbody>';
        $text .= '<tr>
        <td class="forumheader"><b>'.EB_LADDERS_L13.'</b></td>
        <td colspan="2" class="forumheader"><b>'.EB_LADDERS_L14.'</b></td>
        <td class="forumheader"><b>'.EB_LADDERS_L15.'</b></td>
        <td class="forumheader"><b>'.EB_LADDERS_L16.'</b></td>
        <td class="forumheader"><b>'.EB_LADDERS_L17.'</b></td>
        <td class="forumheader"><b>'.EB_LADDERS_L18.'</b></td>
        <td class="forumheader"><b>'.EB_LADDERS_L19.'</b></td>
        </tr>';
        for($i=0; $i<$num_rows; $i++)
        {
            $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
            $gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");
            $eid  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
            $ename  = mysql_result($result,$i, TBL_LADDERS.".Name");
            $etype = mysql_result($result,$i, TBL_LADDERS.".Type");
            $estart = mysql_result($result,$i, TBL_LADDERS.".Start_timestamp");
            $eend = mysql_result($result,$i, TBL_LADDERS.".End_timestamp");
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
            ." WHERE (Ladder = '$eid')";
            $result_2 = $sql->db_Query($q_2);
            $row = mysql_fetch_array($result_2);
            $nbrplayers = $row['NbrPlayers'];

            /* Nbr Teams */
            $q_2 = "SELECT COUNT(*) as NbrTeams"
            ." FROM ".TBL_TEAMS
            ." WHERE (".TBL_TEAMS.".Ladder = '$eid')";
            $result_2 = $sql->db_Query($q_2);
            $row = mysql_fetch_array($result_2);
            $nbrTeams = $row['NbrTeams'];

            /* Nbr matches */
            $q_2 = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES
            ." WHERE (Ladder = '$eid')"
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
                <td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$eid.'">'.$ename.'</a></td>
                <td class="forumheader3"><img '.getGameIconResize($gicon).'/></td>
                <td class="forumheader3">'.$gname.'</td>
                <td class="forumheader3">'.ladderTypeToString($etype).'</td>
                <td class="forumheader3">'.$date_start.'</td>
                <td class="forumheader3">'.$date_end.'</td>
                <td class="forumheader3">'.$nbrTeamPlayers.'</td>
                <td class="forumheader3">'.$nbrmatches.'</td>
                </tr>';
            }
        }
        $text .= '</tbody></table><br />';
    }
}

function displayRecentLadders(){
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
    . TBL_LADDERS
    ." WHERE (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY Name";
    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    $text .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">';
    $text .= '<table>';
    $text .= '<tr><td>';
    $text .= EB_LADDERS_L9.'<br />';
    $text .= '<select class="tbox" name="gameid" onchange="this.form.submit()">';
    if ($gameid == "All")
    {
        $text .= '<option value="All" selected="selected">'.EB_LADDERS_L10.'</option>';
    }
    else
    {
        $text .= '<option value="All">'.EB_LADDERS_L10.'</option>';
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
        $q = "SELECT ".TBL_LADDERS.".*, "
        .TBL_GAMES.".*"
        ." FROM ".TBL_LADDERS.", "
        .TBL_GAMES
        ." WHERE (   (".TBL_LADDERS.".End_timestamp != '')"
        ."       AND (".TBL_LADDERS.".End_timestamp < $time)) "
        ."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
        ." LIMIT 0, $rowsPerPage";
    }
    else
    {
        $q = "SELECT ".TBL_LADDERS.".*, "
        .TBL_GAMES.".*"
        ." FROM ".TBL_LADDERS.", "
        .TBL_GAMES
        ." WHERE (   (".TBL_LADDERS.".End_timestamp != '')"
        ."       AND (".TBL_LADDERS.".End_timestamp < $time)) "
        ."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
        ."   AND (".TBL_LADDERS.".Game = '$gameid')"
        ." LIMIT 0, $rowsPerPage";
    }

    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    if(!$result || ($num_rows < 0))
    {
        $text .= EB_LADDERS_L11;
        return;
    }
    if($num_rows == 0)
    {
        $text .= '<div>'.EB_LADDERS_L12.'</div>';
        return;
    }
    /* Display table contents */
    $text .= '<table class="fborder" style="width:95%"><tbody>';
    $text .= '<tr>
    <td class="forumheader"><b>'.EB_LADDERS_L13.'</b></td>
    <td colspan="2" class="forumheader"><b>'.EB_LADDERS_L14.'</b></td>
    <td class="forumheader"><b>'.EB_LADDERS_L15.'</b></td>
    <td class="forumheader"><b>'.EB_LADDERS_L16.'</b></td>
    <td class="forumheader"><b>'.EB_LADDERS_L17.'</b></td>
    <td class="forumheader"><b>'.EB_LADDERS_L18.'</b></td>
    <td class="forumheader"><b>'.EB_LADDERS_L19.'</b></td>
    </tr>';
    for($i=0; $i<$num_rows; $i++)
    {
        $gname  = mysql_result($result,$i, TBL_GAMES.".name");
        $gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");
        $eid  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
        $ename  = mysql_result($result,$i, TBL_LADDERS.".name");
        $etype = mysql_result($result,$i, TBL_LADDERS.".type");
        $estart = mysql_result($result,$i, TBL_LADDERS.".Start_timestamp");
        $eend = mysql_result($result,$i, TBL_LADDERS.".End_timestamp");
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
        ." WHERE (Ladder = '$eid')";
        $result_2 = $sql->db_Query($q_2);
        $row = mysql_fetch_array($result_2);
        $nbrplayers = $row['NbrPlayers'];

        /* Nbr Teams */
        $q_2 = "SELECT COUNT(*) as NbrTeams"
        ." FROM ".TBL_TEAMS
        ." WHERE (".TBL_TEAMS.".Ladder = '$eid')";
        $result_2 = $sql->db_Query($q_2);
        $row = mysql_fetch_array($result_2);
        $nbrTeams = $row['NbrTeams'];
            
        /* Nbr matches */
        $q_2 = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES
        ." WHERE (Ladder = '$eid')"
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
            <td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$eid.'">'.$ename.'</a></td>
            <td class="forumheader3"><img '.getGameIconResize($gicon).'/></td>
            <td class="forumheader3">'.$gname.'</td>
            <td class="forumheader3">'.ladderTypeToString($etype).'</td>
            <td class="forumheader3">'.$date_start.'</td>
            <td class="forumheader3">'.$date_end.'</td>
            <td class="forumheader3">'.$nbrTeamPlayers.'</td>
            <td class="forumheader3">'.$nbrmatches.'</td>
            </tr>';
        }
    }
    $text .= '</tbody></table><br />';

    $text .= '<p>';
    $text .= '[<a href="'.e_PLUGIN.'ebattles/ladderspast.php">'.EB_LADDERS_L21.'</a>]';
    $text .= '</p>';
}
?>


