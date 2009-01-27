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
* Display ...
*/
$text .= '
<div class="tab-page">
<div class="tab">dummy</div>
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
            if ($numRanks == 2)
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
            ." ORDER BY ".TBL_SCORES.".Player_Rank";

            $result2 = $sql->db_Query($q2);
            $numPlayers = mysql_numrows($result2);
            $pname = '';

            $players = "<a href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$mEventID&amp;matchid=$mID\"><img src=\"".e_PLUGIN."ebattles/images/games_icons/$mEventgameicon\" alt=\"$mEventgameicon\"></img></a> ";

            $pid  = mysql_result($result2,0, TBL_USERS.".user_id");
            $pname  = mysql_result($result2,0 , TBL_USERS.".user_name");
            $players .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pname</a>";

            $rank = 1;
            for ($index = 1; $index < $numPlayers; $index++)
            {
                $pid  = mysql_result($result2,$index , TBL_USERS.".user_id");
                $pname  = mysql_result($result2,$index , TBL_USERS.".user_name");
                $pteam  = mysql_result($result2,$index , TBL_SCORES.".Player_Rank");
                if ($pteam == $rank)
                {
                    $players .= " & ";
                }
                else
                {
                    $players .= $str;
                    $rank++;
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

            ;

            $text .= "$players<br />";
        }
    }
}

?>
