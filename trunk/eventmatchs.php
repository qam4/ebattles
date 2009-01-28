<?php
/**
* EventMatchs.php
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text = '';

global $sql;

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    /* set pagination variables */
    $rowsPerPage = 20;
    $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
    $start = $rowsPerPage * $pg - $rowsPerPage;

    $q = "SELECT count(*) "
    ." FROM ".TBL_MATCHS
    ." WHERE (".TBL_MATCHS.".Event = '$event_id')";
    $result = $sql->db_Query($q);
    $totalPages = mysql_result($result, 0);

    $q = "SELECT ".TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

    $result = $sql->db_Query($q);
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $egame = mysql_result($result,0 , TBL_GAMES.".Name");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
    $text .= "<h1>$ename</h1>";
    $text .= "<h2>$egame</h2>";
    $text .= "<br />";

    $q = "SELECT COUNT(*) as NbrMatchs"
    ." FROM ".TBL_MATCHS
    ." WHERE (Event = '$event_id')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrmatchs = $row['NbrMatchs'];
    $text .="<h2>Matches for this Ladder ($nbrmatchs)</h2><br />";
    $text .= "<div class=\"spacer\">";
    $text .= "<div style=\"text-align:center\">";
    /* Stats/Results */
    $q = "SELECT ".TBL_MATCHS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
    ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
    ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
    ." LIMIT $start, $rowsPerPage";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        /* Display table contents */
        $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
        $text .= "<tr><td class=\"forumheader\" style=\"width:120px\"><b>Match ID</b></td><td class=\"forumheader\" style=\"width:90px\"><b>Reported By</b></td><td class=\"forumheader\"><b>Players</b></td><td class=\"forumheader\" style=\"width:90px\"><b>Date</b></td></tr>\n";
        for($i=0; $i<$num_rows; $i++){
            $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
            $mReportedBy  = mysql_result($result,$i, TBL_MATCHS.".ReportedBy");
            $mReportedByNickname  = mysql_result($result,$i, TBL_USERS.".user_name");
            $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
            $mTime_local = $mTime + GMT_TIMEOFFSET;
            //$date = date("d M Y, h:i:s A",$mTime);
            $date = date("d M Y",$mTime_local);

            $q2 = "SELECT ".TBL_MATCHS.".*, "
            .TBL_SCORES.".*, "
            .TBL_PLAYERS.".*, "
            .TBL_USERS.".*"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES.", "
            .TBL_PLAYERS.", "
            .TBL_USERS
            ." WHERE (".TBL_MATCHS.".MatchID = '$mID')"
            ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
            ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
            ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
            ." ORDER BY ".TBL_SCORES.".Player_Rank";

            $result2 = $sql->db_Query($q2);
            $num_rows2 = mysql_numrows($result2);
            $pname = '';
            $players = '';
            for($j=0; $j<$num_rows2; $j++)
            {
                $pid  = mysql_result($result2,$j, TBL_USERS.".user_id");
                $pname  = mysql_result($result2,$j, TBL_USERS.".user_name");
                $pteam  = mysql_result($result2,$j, TBL_PLAYERS.".Team");
                $pclan = '';
                $pclantag = '';
                if ($etype == "Team Ladder")
                {
                    $q_3 = "SELECT ".TBL_CLANS.".*, "
                    .TBL_DIVISIONS.".*, "
                    .TBL_TEAMS.".* "
                    ." FROM ".TBL_CLANS.", "
                    .TBL_DIVISIONS.", "
                    .TBL_TEAMS
                    ." WHERE (".TBL_TEAMS.".TeamID = '$pteam')"
                    ."   AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
                    ."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
                    $result_3 = $sql->db_Query($q_3);
                    $num_rows_3 = mysql_numrows($result_3);
                    if ($num_rows_3 == 1)
                    {
                        $pclan  = mysql_result($result_3,0, TBL_CLANS.".Name");
                        $pclantag  = mysql_result($result_3,0, TBL_CLANS.".Tag") ."_";
                    }
                }

                if ($j==0)
                $players = "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pclantag$pname</a>";
                else
                $players = $players.", <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pclantag$pname</a>";
            }

            $text .= "<tr>\n";
            $text .= "<td class=\"forumheader3\"><b>$mID</b> <a href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$event_id&amp;matchid=$mID\">(Show details)</a></td><td class=\"forumheader3\"><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mReportedBy\">$mReportedByNickname</a></td><td class=\"forumheader3\">$players</td><td class=\"forumheader3\">$date</td></tr>";


        }
        $text .= "</tbody></table><br />\n";
    }



    $text .= paginate($rowsPerPage, $pg, $totalPages);

    $text .= "<br />";
    $text .= "</div>";
    $text .= "</div>";
}
$ns->tablerender('Event Matches', $text);
require_once(FOOTERF);
exit;
?>
