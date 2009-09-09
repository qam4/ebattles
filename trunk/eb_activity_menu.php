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

$ebattles_title = "Recent activity";
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

    $time = GMT_time();
    $events = array();
    $nbr_events = 0;


    // Add recent games
    $rowsPerPage = 5;
    /* Stats/Results */
    $q = "SELECT ".TBL_MATCHS.".*, "
    .TBL_USERS.".*, "
    .TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_USERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_MATCHS.".Event = ".TBL_EVENTS.".EventID)"
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
            $mTime_local = $mTime + GMT_TIMEOFFSET;
            $date = date("d M Y, h:i:s A",$mTime_local);
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
                if ($numRanks == 1)
                {
                    $str = " tied ";
                }
                else if ($numRanks == 2)
                {
                    $str = " defeated ";
                }
                else
                {
                    $str = " vs ";
                }

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

                $players .= "<a href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$mEventID&amp;matchid=$mID\"><img src=\"".getGameIcon($mEventgameicon)."\" alt=\"$mEventgameicon\"/></a> ";

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

                $players .= " playing $mEventgame (<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$mEventID\">$mEventName</a>)";
                if (($time-$mTime) < INT_DAY )
                {
                    $players .= " <div class='smalltext'>".get_formatted_timediff($mTime, $time)." ago.</div>";
                }
                else
                {
                    $players .= " <div class='smalltext'>".$date.".</div>";
                }

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
            $aTime_local = $aTime + GMT_TIMEOFFSET;
            $date = date("d M Y, h:i:s A",$aTime_local);

            switch ($aType) {
                case 'PlayerTookFirstPlace':
                $award = " took 1st place";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/awards/award_star_gold_3.png\" alt=\"1st place\" title=\"1st place\"/> ";
                break;
                case 'PlayerInTopTen':
                $award = " entered top 10";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/awards/award_star_bronze_3.png\" alt=\"top 10\" title=\"top 10\"/> ";
                break;
                case 'PlayerStreak5':
                $award = " won 5 games in a row";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/awards/medal_bronze_3.png\" alt=\"1st place\" title=\"5 in a row\"/> ";
                break;
                case 'PlayerStreak10':
                $award = " won 10 games in a row";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/awards/medal_silver_3.png\" alt=\"1st place\" title=\"10 in a row\"/> ";
                break;
                case 'PlayerStreak25':
                $award = " won 25 games in a row";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/awards/medal_gold_3.png\" alt=\"1st place\" title=\"25 in a row\"/> ";
                break;
            }

            $award_string = $icon;
            $award_string .= " <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$aUser\">$aUserNickName</a>";
            $award_string .= $award;
            $award_string .= " playing $aEventgame (<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$aEventID\">$aEventName</a>)";

            if (($time-$aTime) < INT_DAY )
            {
                $award_string .= " <div class='smalltext'>".get_formatted_timediff($aTime, $time)." ago.</div>";
            }
            else
            {
                $award_string .= " <div class='smalltext'>".$date.".</div>";
            }

            $events[$nbr_events][0] = $aTime;
            $events[$nbr_events][1] = $award_string;
            $nbr_events ++;
        }
    }

    multi2dSortAsc($events, 0, SORT_DESC);
    for ($index = 0; $index<$nbr_events; $index++)
    {
        $text .= $events[$index][1];
    }

    return $text;
}

?>
