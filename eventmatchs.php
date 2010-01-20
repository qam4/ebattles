<?php
/**
* EventMatchs.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/event.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$pages = new Paginator;

$text ='
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

    $text .= '<div class="tab-pane" id="tab-pane-11">';
    $text .= '<div class="tab-page">';
    $text .= '<div class="tab">'.EB_MATCHS_L1.'</div>';
    $q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES
    ." WHERE (Event = '$event_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrmatches = $row['NbrMatches'];
    $text .= '<p>';
    $text .= $nbrmatches.' '.EB_MATCHS_L2;
    $text .= '</p>';
    $text .= '<br />';

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
        /* Display table contents */
        $text .= '<table class="table_left">';
        for($i=0; $i<$num_rows; $i++)
        {
            $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
            $mReportedBy  = mysql_result($result,$i, TBL_USERS.".user_id");
            $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
            $mTime_local = $mTime + TIMEOFFSET;
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
                $players = '<tr>';
                $scores = '';

                /*
                $players .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="Match '.$mID.'">';
                $players .= '<img '.getActivityGameIconResize($mEventgameicon).'/>';
                $players .= '</a></td>';
                */

                $players .= '<td>';
                $rank = 1;
                $matchteam = 0;
                for ($index = 0; $index < $numPlayers; $index++)
                {
                    $puid  = mysql_result($result2,$index , TBL_USERS.".user_id");
                    $pname  = mysql_result($result2,$index , TBL_USERS.".user_name");
                    $prank  = mysql_result($result2,$index , TBL_SCORES.".Player_Rank");
                    $pteam  = mysql_result($result2,$index , TBL_PLAYERS.".Team");
                    $pmatchteam  = mysql_result($result2,$index , TBL_SCORES.".Player_MatchTeam");
                    $pscore = mysql_result($result2,$index , TBL_SCORES.".Player_Score");
                    list($pclan, $pclantag) = getClanName($pteam);

                    if($index>0)
                    {
                        if ($pmatchteam == $matchteam)
                        {
                            $players .= ' &amp; ';
                        }
                        else
                        {
                            if ($prank == $rank)
                            {
                                $str = '&nbsp;'.EB_MATCH_L2.'&nbsp;';
                            }
                            else
                            {
                                $str = '&nbsp;'.EB_MATCH_L3.'&nbsp;';
                            }
                            $scores .= "-".$pscore;
                            $players .= $str;
                            $matchteam++;
                        }
                    }
                    else
                    {
                        $matchteam = $pmatchteam;
                        $scores .= $pscore;
                    }

                    $players .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a>';
                }

                //score here
                if ($mEventAllowScore == TRUE)
                {
                    $players .= ' ('.$scores.') ';
                }

                $players .= ' (<a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="'.EB_MATCH_L4.'&nbsp;'.$mID.'">'.EB_MATCH_L5.'</a>)';

                $players .= ' <div class="smalltext">';
                $players .= EB_MATCH_L6.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$mReportedBy.'">'.$mReportedByNickName.'</a> ';
                if (($time-$mTime) < INT_MINUTE )
                {
                    $players .= EB_MATCH_L7;
                }
                else if (($time-$mTime) < INT_DAY )
                {
                    $players .= get_formatted_timediff($mTime, $time).'&nbsp;'.EB_MATCH_L8;
                }
                else
                {
                    $players .= EB_MATCH_L9.'&nbsp;'.$date.'.';
                }
                $nbr_comments = getCommentTotal("ebmatches", $mID);
                $players .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="'.EB_MATCH_L4.'&nbsp;'.$mID.'">'.$nbr_comments.'&nbsp;';
                $players .= ($nbr_comments > 1) ? EB_MATCH_L10 : EB_MATCH_L11;
                $players .= '</a>';
                $players .= '</div><br /></td></tr>';

                $text .= $players;
            }
        }
        $text .= '</table>';
    }
    $text .= '<br />';

    $text .= '<p>';
    $text .= EB_MATCHS_L3.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.EB_MATCHS_L4.'</a>]<br />';
    $text .= '</p>';

    $text .= '</div>';
    $text .= '</div>';
}
$ns->tablerender("$mEventName ($mEventgame - ".eventType($mEventType).")", $text);
require_once(FOOTERF);
exit;
?>
