<?php
/**
* matchinfo.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text = '';

global $sql;

/* Event Name */
$event_id = $_GET['eventid'];
$match_id = $_GET['matchid'];

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
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $egame = mysql_result($result,0 , TBL_GAMES.".Name");
    $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
    $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
    $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");

    $text .= "<h1>$ename</h1>";
    $text .= "<h2>$egame</h2>";

    $q = "SELECT ".TBL_MATCHS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)";
    $result = $sql->db_Query($q);
    $reported_by  = mysql_result($result,0, TBL_MATCHS.".ReportedBy");
    $reported_by_name  = mysql_result($result,0, TBL_USERS.".user_name");


    $q = "SELECT ".TBL_MATCHS.".*, "
    .TBL_SCORES.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
    ." ORDER BY ".TBL_SCORES.".Player_Rank";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    $text .="<div class=\"spacer\">";
    $text .= "<h2>Match (#$match_id)</h2><br />";

    if ($num_rows>0)
    {
        $comments  = mysql_result($result,0, TBL_MATCHS.".Comments");
        $time_reported  = mysql_result($result,0, TBL_MATCHS.".TimeReported");
        $time_reported_local = $time_reported + GMT_TIMEOFFSET;
        $date = date("d M Y, h:i:s A",$time_reported_local);

        $text .= "Match reported by <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$reported_by\">$reported_by_name</a> ($date)<br />";
    }
    else
    {
        $date_reported  = '';
        $reported_by  = '';
        $comments  = 'Match deleted';
    }

    // Can I delete the game
    //-----------------------
    $time = GMT_time();

    // Is the user a moderator?
    $q_2 = "SELECT ".TBL_EVENTMODS.".*"
    ." FROM ".TBL_EVENTMODS
    ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
    ."   AND (".TBL_EVENTMODS.".User = ".USERID.")";
    $result_2 = $sql->db_Query($q_2);
    $num_rows_2 = mysql_numrows($result_2);

    $can_delete = 0;
    if (  (USERID==$reported_by)
    &&(  ($eend==0)
    ||(  ($eend>=$time)
    &&($estart<=$time)
    )
    )
    )
    $can_delete = 1;
    if (check_class($pref['eb_mod']))  $can_delete = 1;
    if (USERID==$eowner)  $can_delete = 1;
    if ($num_rows_2>0)  $can_delete = 1;

    if($can_delete != 0)
    {
        $text .= "<form action=\"".e_PLUGIN."ebattles/matchdelete.php?eventid=$event_id\" method=\"post\">";
        $text .= "<div>";
        $text .= "<input type=\"hidden\" name=\"matchid\" value=\"$match_id\"></input>";
        $text .= "<input class=\"button\" type=\"submit\" name=\"deletematch\" value=\"Delete this match\"></input>";
        $text .= "</div>";
        $text .= "</form>";
    }

    $text .= "<br />";

    $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
    $text .= "<tr><td class=\"forumheader\"><b>Rank</b></td><td class=\"forumheader\"><b>Team</b></td><td class=\"forumheader\"><b>Player</b></td><td class=\"forumheader\"><b>Score</b></td><td class=\"forumheader\"><b>ELO</b></td></tr>\n";
    for($i=0; $i<$num_rows; $i++)
    {
        $pid  = mysql_result($result,$i, TBL_USERS.".user_id");
        $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
        $prank  = mysql_result($result,$i, TBL_SCORES.".Player_Rank");
        $pMatchTeam  = mysql_result($result,$i, TBL_SCORES.".Player_MatchTeam");
        $pdeltaELO  = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
        $pscore  = mysql_result($result,$i, TBL_SCORES.".Player_Score");

        $pteam  = mysql_result($result,$i, TBL_PLAYERS.".Team");
        $pclan = '';
        $pclantag = '';
        if ($etype == "Team Ladder")
        {
            $q_2 = "SELECT ".TBL_CLANS.".*, "
            .TBL_DIVISIONS.".*, "
            .TBL_TEAMS.".* "
            ." FROM ".TBL_CLANS.", "
            .TBL_DIVISIONS.", "
            .TBL_TEAMS
            ." WHERE (".TBL_TEAMS.".TeamID = '$pteam')"
            ."   AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
            ."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
            $result_2 = $sql->db_Query($q_2);
            $num_rows_2 = mysql_numrows($result_2);
            if ($num_rows_2 == 1)
            {
                $pclan  = mysql_result($result_2,0, TBL_CLANS.".Name");
                $pclantag  = mysql_result($result_2,0, TBL_CLANS.".Tag") ."_";
            }
        }

        //$text .= "Rank #$prank - $pname (team #$pMatchTeam)- score: $pscore (ELO:$pdeltaELO)<br />";
        $text .= "<tr>\n";
        $text .= "<td class=\"forumheader3\"><b>$prank</b></td><td class=\"forumheader3\">$pMatchTeam</td><td class=\"forumheader3\"><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pclantag$pname</a></td><td class=\"forumheader3\">$pscore</td><td class=\"forumheader3\">$pdeltaELO</td></tr>";

    }
    $text .= "</tbody></table><br />\n";

    $text .= "<p>";
    $text .= "Comments:<br />\n";
    $text .= "$comments<br />\n";
    $text .= "</p>";
    $text .= "</div>";

    $text .= "<p>";
    $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]<br />";
    $text .= "</p>";
}
$ns->tablerender('Event Matches', $text);
require_once(FOOTERF);
exit;
?>
