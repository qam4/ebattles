<?php
/**
* EventMatchs.php
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);

$pages = new Paginator;

$text .='
<script type="text/javascript" src="./js/tabpane.js"></script>
';

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    $q = "SELECT ".TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

    $result = $sql->db_Query($q);
    $mEventID  = mysql_result($result,0, TBL_EVENTS.".EventID");
    $mEventName  = mysql_result($result,0, TBL_EVENTS.".Name");
    $mEventgame = mysql_result($result,0 , TBL_GAMES.".Name");
    $mEventgameicon = mysql_result($result,0 , TBL_GAMES.".Icon");
    $mEventType  = mysql_result($result,0 , TBL_EVENTS.".Type");
    $mEventAllowScore = mysql_result($result,0 , TBL_EVENTS.".AllowScore");

    $text .="<div class=\"tab-pane\" id=\"tab-pane-11\">";
    $text .="<div class=\"tab-page\">";
    $text .="<div class=\"tab\">All Matches</div>";
    $q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES
    ." WHERE (Event = '$event_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrmatches = $row['NbrMatches'];
    $text .="<p>";
    $text .="$nbrmatches matches played";
    $text .="</p>";
    $text .="<br />";

    /* set pagination variables */
    $totalItems = $nbrmatches;
    $pages->items_total = $totalItems;
    $pages->mid_range = eb_PAGINATION_MIDRANGE;
    $pages->paginate();

    // Paginate
    $text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
    $text .= '<span style="float:right">';
    // Go To Page
    $text .= $pages->display_jump_menu();
    $text .= '&nbsp;&nbsp;&nbsp;';
    // Items per page
    $text .= $pages->display_items_per_page();
    $text .= '</span><br /><br />';

    $text .= "<div class=\"spacer\">";
    /* Stats/Results */
    $q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
    ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
    ." $pages->limit";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if ($num_rows>0)
    {
        for($i=0; $i<$num_rows; $i++)
        {
            $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
            $mReportedBy  = mysql_result($result,$i, TBL_USERS.".user_id");
            $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
            $mTime_local = $mTime + GMT_TIMEOFFSET;
            $date = date("d M Y, h:i A",$mTime_local);
            $q2 = "SELECT DISTINCT ".TBL_MATCHS.".*, "
            .TBL_SCORES.".Player_Rank"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES
            ." WHERE (".TBL_MATCHS.".MatchID = '$mID')"
            ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
            $result2 = $sql->db_Query($q2);
            $numRanks = mysql_numrows($result2);
            if ($numRanks > 0)
            {
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
                ." ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";

                $result2 = $sql->db_Query($q2);
                $numPlayers = mysql_numrows($result2);
                $pname = '';
                $players = '';
                $scores = '';

                //$players .= "<a href=\"".e_PLUGIN."ebattles/matchinfo.php?matchid=$mID\"><img ".getGameIconResize($mEventgameicon)."/></a> ";

                $rank = 1;
                for ($index = 0; $index < $numPlayers; $index++)
                {
                    $pid  = mysql_result($result2,$index , TBL_USERS.".user_id");
                    $pname  = mysql_result($result2,$index , TBL_USERS.".user_name");
                    $prank  = mysql_result($result2,$index , TBL_SCORES.".Player_Rank");
                    $pteam  = mysql_result($result2,$index , TBL_SCORES.".Player_MatchTeam");
                    $pscore = mysql_result($result2,$index , TBL_SCORES.".Player_Score");
                    $pclan = '';
                    $pclantag = '';
                    if ($mEventType == "Team Ladder")
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

                    if($index>0)
                    {
                        if ($pteam == $team)
                        {
                            $players .= " & ";
                        }
                        else
                        {
                            if ($prank == $rank)
                            {
                                $str = " tied ";
                            }
                            else
                            {
                                $str = " defeated ";
                            }
                            $scores .= "-".$pscore;
                            $players .= $str;
                            $team++;
                        }
                    }
                    else
                    {
                        $team = $pteam;
                        $scores .= $pscore;
                    }

                    $players .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pclantag$pname</a>";
                }

                //score here
                if ($mEventAllowScore == TRUE)
                {
                    $players .= " (".$scores.") ";
                }
                $players .= " (<a href=\"".e_PLUGIN."ebattles/matchinfo.php?matchid=$mID\" title=\"Match $mID\">View details</a>)";

                $players .= " <div class='smalltext'>";
                $players .= "Reported by <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mReportedBy\">$mReportedByNickName</a> ";
                if (($time-$mTime) < INT_MINUTE )
                {
                    $players .= "a few seconds ago";
                }
                else if (($time-$mTime) < INT_DAY )
                {
                    $players .= get_formatted_timediff($mTime, $time)." ago.";
                }
                else
                {
                    $players .= "on ".$date.".";
                }
                $nbr_comments = getCommentTotal("ebmatches", $mID);
                $players .= " <a href=\"".e_PLUGIN."ebattles/matchinfo.php?matchid=$mID\" title=\"Match $mID\">".$nbr_comments." comment";
                $players .= ($nbr_comments > 1) ? "s" : "";
                $players .= "</a>";
                $players .= "</div>";
            }
            $text .= "$players<br />";
        }
    }
    $text .= "<br />";

    $text .= '
    <p>
    <br />Back to [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">Event</a>]<br />
    </p>
    ';

    $text .= "</div>";
    $text .= "</div>";
    $text .= "</div>";
}
$ns->tablerender("$mEventName ($mEventgame - $mEventType)", $text);
require_once(FOOTERF);
exit;
?>
