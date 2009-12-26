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
include_once(e_PLUGIN."ebattles/include/clan.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text .='
<script type="text/javascript" src="./js/tabpane.js"></script>
';

global $sql;

$match_id = $_GET['matchid'];

if (!$match_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    $text .= '<div class="tab-pane" id="tab-pane-12">';
    $text .= '<div class="tab-page">';
    $text .= '<div class="tab">Match details</div>';

    // Did the user play in that match
    $q = "SELECT DISTINCT ".TBL_SCORES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".User = ".USERID.")";
    $result = $sql->db_Query($q);
    $numPlayers = mysql_numrows($result);

    if ($numPlayers>0)
    {
        $uteam = mysql_result($result,0 , TBL_SCORES.".Player_MatchTeam");
    }

    $q = "SELECT ".TBL_EVENTS.".*, "
    .TBL_GAMES.".*, "
    .TBL_MATCHS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_EVENTS.", "
    .TBL_GAMES.", "
    .TBL_MATCHS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ."   AND (".TBL_EVENTS.".EventID = ".TBL_MATCHS.".Event)"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ."   AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)";

    $result = $sql->db_Query($q);
    $event_id = mysql_result($result,0 , TBL_EVENTS.".EventID");
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $egame = mysql_result($result,0 , TBL_GAMES.".Name");
    $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
    $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
    $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
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
    $numScores = mysql_numrows($result);
    $text .= '<div class="spacer">';

    if ($numScores>0)
    {
        $comments  = mysql_result($result,0, TBL_MATCHS.".Comments");
        $time_reported  = mysql_result($result,0, TBL_MATCHS.".TimeReported");
        $time_reported_local = $time_reported + TIMEOFFSET;
        $date = date("d M Y, h:i A",$time_reported_local);

        $text .= 'Match reported by <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$reported_by.'">'.$reported_by_name.'</a> ('.$date.')<br />';
    }
    else
    {
        $date_reported  = '';
        $reported_by  = '';
        $comments  = 'Match deleted';
    }

    // Can I delete the game
    //-----------------------
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
    &&($estart<=$time))))
    $can_delete = 1;
    if (check_class($pref['eb_mod_class']))  $can_delete = 1;
    if (USERID==$eowner)  $can_delete = 1;
    if ($num_rows_2>0)  $can_delete = 1;

    if($can_delete != 0)
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/matchdelete.php?eventid='.$event_id.'" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="matchid" value="'.$match_id.'"/>';
        $text .= '<input class="button" type="submit" name="deletematch" value="Delete this match" onclick="return confirm(\'Are you sure you want to delete this match?\');"/>';
        $text .= '</div>';
        $text .= '</form>';
    }

    $text .= '<br />';

    $text .= '<table class="fborder" style="width:95%"><tbody>';
    $text .= '<tr>
    <td class="forumheader"><b>Rank</b></td>
    <td class="forumheader"><b>Team</b></td>
    <td class="forumheader"><b>Player</b></td>
    <td class="forumheader"><b>Score</b></td>
    <td class="forumheader"><b>Points</b></td>
    <td class="forumheader"><b>ELO</b></td>
    <td class="forumheader"><b>Skill</b></td>
    <td class="forumheader"><b>Opponent Rating</b></td>
    </tr>';
    for($i=0; $i < $numScores; $i++)
    {
        $pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
        $puid  = mysql_result($result,$i, TBL_USERS.".user_id");
        $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
        $pscoreid  = mysql_result($result,$i, TBL_SCORES.".ScoreID");
        $prank  = mysql_result($result,$i, TBL_SCORES.".Player_Rank");
        $pMatchTeam  = mysql_result($result,$i, TBL_SCORES.".Player_MatchTeam");
        $pdeltaELO  = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
        $pdeltaTS_mu  = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
        $pdeltaTS_sigma  = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
        $pscore  = mysql_result($result,$i, TBL_SCORES.".Player_Score");
        $pOppScore  = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");
        $ppoints  = mysql_result($result,$i, TBL_SCORES.".Player_Points");

        $pteam  = mysql_result($result,$i, TBL_PLAYERS.".Team");
        list($pclan, $pclantag) = getClanName($pteam);

        //$text .= "Rank #$prank - $pname (team #$pMatchTeam)- score: $pscore (ELO:$pdeltaELO)<br />";
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.$prank.'</b></td>
        <td class="forumheader3">'.$pMatchTeam.'</td>
        <td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a></td>
        <td class="forumheader3">'.$pscore.'</td>
        <td class="forumheader3">'.$ppoints.'</td>
        <td class="forumheader3">'.$pdeltaELO.'</td>
        <td class="forumheader3">'.$pdeltaTS_mu.'</td>
        ';

        // Find all opponents ratings
        $text .= '<td class="forumheader3"><table style="margin-left: 0px; margin-right: auto;">';
        for($opponentIndex=0; $opponentIndex < $numScores; $opponentIndex++)
        {
            $can_rate = FALSE;
            $opid = mysql_result($result,$opponentIndex, TBL_PLAYERS.".PlayerID");
            $oMatchTeam = mysql_result($result,$opponentIndex, TBL_SCORES.".Player_MatchTeam");
            $ouid = mysql_result($result,$opponentIndex, TBL_USERS.".user_id");
            $ouname = mysql_result($result,$opponentIndex, TBL_USERS.".user_name");
            $oteam  = mysql_result($result,$opponentIndex, TBL_PLAYERS.".Team");
            list($oclan, $oclantag) = getClanName($oteam);

            if (($numPlayers>0)&&($ouid == USERID)&&($uteam!=$pMatchTeam)) $can_rate = TRUE;
            if ($oMatchTeam != $pMatchTeam)
            {
                $rating = getRating("ebscores", $pscoreid, $can_rate, true, $ouid);
                if (preg_match("/".EBATTLES_RATELAN_2."/", $rating))
                {
                    $text .= '<tr><td>'.$rating.'</td></tr>';
                }
                else if ($rating != EBATTLES_RATELAN_4)
                {
                    $text .= '<tr><td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$ouid.'">'.$oclantag.$ouname.'&nbsp</a></td><td>'.$rating.'</td></tr>';
                }
            }
        }
        $text .= '</table></td>';
        $text .= '</tr>';
    }
    $text .= '</tbody></table><br />';

    if ($comments)
    {
        $text .= '<p>';
        $text .= 'Reporter comments:<br />';
        $text .= $tp->toHTML($comments, true).'<br />';
        $text .= '</p>';
    }

    $text .= '</div>';

    $text .= '</div>';
    $text .= '</div>';

    $text .= '<p>';
    $text .= '<br />Back to [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">Event</a>]<br />';
    $text .= '</p>';

    $ns->tablerender("$ename ($egame - $etype)", $text);

    unset($text);

    $text .= getComment("ebmatches", $match_id);
    echo $text;

}
require_once(FOOTERF);
exit;
?>
