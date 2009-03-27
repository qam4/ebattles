<?php
/**
* eBattles.php
*
*/

// always include the class2.php file - this is the main e107 file
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

// this generates all the HTML up to the start of the main section
require_once(HEADERF);

$text = '
<script type="text/javascript" src="./js/tabpane.js"></script>
';

$text .= '
<div class="tab-pane" id="tab-pane-7">
';
/**
* Display Latest Games
*/
$text .= '
<div class="tab-page">
<div class="tab">Latest Games</div>
';
displayLatestGames();
$text .= '</div>';

/**
* Display Latest Awards
*/
$text .= '
<div class="tab-page">
<div class="tab">Latest Awards</div>
';
displayLatestAwards();
$text .= '</div>';

/**
* Display ...
*/
$text .= '
<div class="tab-page">
<div class="tab"><br/></div>
';
$text .= '
</div>
</div>

<script type="text/javascript">
//<![CDATA[
setupAllTabs();
//]]>
</script>
';

$ns->tablerender('eBattles', $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayLatestGames - Displays Latest Games
*/
function displayLatestGames(){
    global $sql;
    global $text;

    $time = GMT_time();

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
    $text .= "<br />";
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

            $players = "<a href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$mEventID&amp;matchid=$mID\"><img src=\"".e_PLUGIN."ebattles/images/games_icons/$mEventgameicon\" alt=\"$mEventgameicon\"></img></a> ";

            $pid  = mysql_result($result2,0, TBL_USERS.".user_id");
            $pname  = mysql_result($result2,0 , TBL_USERS.".user_name");
            $players .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pname</a>";

            $rank = 1;
            $team = 1;
            for ($index = 1; $index < $numPlayers; $index++)
            {
                $pid  = mysql_result($result2,$index , TBL_USERS.".user_id");
                $pname  = mysql_result($result2,$index , TBL_USERS.".user_name");
                $prank  = mysql_result($result2,$index , TBL_SCORES.".Player_Rank");
                $pteam  = mysql_result($result2,$index , TBL_SCORES.".Player_MatchTeam");
                if ($pteam == team)
                {
                    $players .= " & ";
                }
                else
                {
                    $players .= $str;
                    $team++;
                }
                $players .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pname</a>";
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

            $text .= "$players<br />";
        }
    }
}

/**
* displayLatestAwards - Displays Latest Awards
*/
function displayLatestAwards(){
    global $sql;
    global $text;

    $time = GMT_time();

    $rowsPerPage = 5;
    /* Stats/Results */
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

    $text .= "<br />";
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
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/award_star_gold_3.png\" alt=\"1st place\" title=\"1st place\"></img> ";
                break;
                case 'PlayerInTopTen':
                $award = " entered top 10";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/award_star_bronze_3.png\" alt=\"top 10\" title=\"top 10\"></img> ";
                break;
                case 'PlayerStreak5':
                $award = " won 5 games in a row";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/medal_bronze_3.png\" alt=\"1st place\" title=\"5 in a row\"></img> ";
                break;
                case 'PlayerStreak10':
                $award = " won 10 games in a row";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/medal_silver_3.png\" alt=\"1st place\" title=\"10 in a row\"></img> ";
                break;
                case 'PlayerStreak25':
                $award = " won 25 games in a row";
                $icon = "<img src=\"".e_PLUGIN."ebattles/images/medal_gold_3.png\" alt=\"1st place\" title=\"25 in a row\"></img> ";
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

            $text .= "$award_string<br />";
        }
    }
}
?>
