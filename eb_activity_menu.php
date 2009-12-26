<?php
/**
* eb_activity_menu.php
*
*/

if (!defined('e107_INIT')) { exit; }

global $PLUGINS_DIRECTORY;
$lan_file = e_PLUGIN."ebattles/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."ebattles/languages/English.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/clan.php");

$ebattles_title = $pref['eb_activity_menuheading'];
$text = displayRecentActivity();

$ns->tablerender($ebattles_title,$text);

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayLatestGames - Displays Latest Games
*/
function displayRecentActivity(){
    global $sql;
    global $time;
    global $pref;

    $events = array();
    $nbr_events = 0;

    // Add recent games
    $rowsPerPage = $pref['eb_activity_number_of_items'];
    /* Stats/Results */
    $q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
    .TBL_USERS.".*, "
    .TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_USERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_MATCHS.".Event = ".TBL_EVENTS.".EventID)"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
    ." AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
    ." LIMIT 0, $rowsPerPage";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if ($num_rows>0)
    {
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
            $mReportedBy  = mysql_result($result,$i, TBL_USERS.".user_id");
            $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $mEventID  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $mEventName  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $mEventgame = mysql_result($result,$i , TBL_GAMES.".Name");
            $mEventgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $mEventType  = mysql_result($result,$i, TBL_EVENTS.".Type");
            $mEventAllowScore = mysql_result($result,$i, TBL_EVENTS.".AllowScore");
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

                $players .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="Match '.$mID.'">';
                $players .= '<img '.getActivityGameIconResize($mEventgameicon).'/>';
                $players .= '</a></td>';

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

                $players .= ' playing '.$mEventgame.' (<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$mEventID.'">'.$mEventName.'</a>)';

                $players .= ' <div class="smalltext">';
                $players .= 'Reported by <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$mReportedBy.'">'.$mReportedByNickName.'</a> ';
                if (($time-$mTime) < INT_MINUTE )
                {
                    $players .= 'a few seconds ago';
                }
                else if (($time-$mTime) < INT_DAY )
                {
                    $players .= get_formatted_timediff($mTime, $time).' ago.';
                }
                else
                {
                    $players .= 'on '.$date.'.';
                }
                $nbr_comments = getCommentTotal("ebmatches", $mID);
                $players .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="Match '.$mID.'">'.$nbr_comments.' comment';
                $players .= ($nbr_comments > 1) ? "s" : "";
                $players .= '</a>';
                $players .= '</div></td></tr>';

                $events[$nbr_events][0] = $mTime;
                $events[$nbr_events][1] = $players;
                $nbr_events ++;
            }
        }
    }

    // Add Awards events
    $q = "SELECT ".TBL_AWARDS.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_USERS.".*, "
    .TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_AWARDS.", "
    .TBL_PLAYERS.", "
    .TBL_USERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
    ." AND (".TBL_PLAYERS.".User = ".TBL_USERS.".user_id)"
    ." AND (".TBL_PLAYERS.".Event = ".TBL_EVENTS.".EventID)"
    ." AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY ".TBL_AWARDS.".timestamp DESC"
    ." LIMIT 0, $rowsPerPage";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $aID  = mysql_result($result,$i, TBL_AWARDS.".AwardID");
            $aUser  = mysql_result($result,$i, TBL_USERS.".user_id");
            $aUserNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $aEventID  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $aEventName  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $aEventgame = mysql_result($result,$i , TBL_GAMES.".Name");
            $aEventgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $aType  = mysql_result($result,$i, TBL_AWARDS.".Type");
            $aTime  = mysql_result($result,$i, TBL_AWARDS.".timestamp");
            $aTime_local = $aTime + TIMEOFFSET;
            $date = date("d M Y, h:i A",$aTime_local);

            switch ($aType) {
                case 'PlayerTookFirstPlace':
                $award = ' took 1st place';
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_gold_3.png").' alt="1st place" title="1st place"/> ';
                break;
                case 'PlayerInTopTen':
                $award = ' entered top 10';
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_bronze_3.png").' alt="top 10" title="top 10"/> ';
                break;
                case 'PlayerStreak5':
                $award = ' won 5 games in a row';
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_bronze_3.png").' alt="1st place" title="5 in a row"/> ';
                break;
                case 'PlayerStreak10':
                $award = ' won 10 games in a row';
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_silver_3.png").' alt="1st place" title="10 in a row"/> ';
                break;
                case 'PlayerStreak25':
                $award = ' won 25 games in a row';
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_gold_3.png").' alt="1st place" title="25 in a row"/> ';
                break;
            }

            $award_string = '<tr><td style="vertical-align:top">'.$icon.'</td>';
            $award_string .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$aUser.'">'.$aUserNickName.'</a>';
            $award_string .= $award;
            $award_string .= " playing $aEventgame (<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$aEventID\">$aEventName</a>)";

            $award_string .= ' <div class="smalltext">';
            if (($time-$aTime) < INT_MINUTE )
            {
                $award_string .= 'a few seconds ago';
            }
            else if (($time-$aTime) < INT_DAY )
            {
                $award_string .= get_formatted_timediff($aTime, $time).' ago.';
            }
            else
            {
                $award_string .= $date;
            }
            $award_string .= '</div></td></tr>';

            $events[$nbr_events][0] = $aTime;
            $events[$nbr_events][1] = $award_string;
            $nbr_events ++;
        }
    }

    $text = '<table style="margin-left: 0px; margin-right: auto;">';
    multi2dSortAsc($events, 0, SORT_DESC);
    for ($index = 0; $index<min($nbr_events, $rowsPerPage); $index++)
    {
        $text .= $events[$index][1];
    }
    $text .= '</table>';

    return $text;
}

?>
