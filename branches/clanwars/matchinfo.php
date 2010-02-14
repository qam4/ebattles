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
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/event.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text .= '
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
    $text .= '<div class="tab">'.EB_MATCHD_L1.'</div>';

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
    $eMatchesApproval = mysql_result($result,0 , TBL_EVENTS.".MatchesApproval");
    $mStatus  = mysql_result($result,0, TBL_MATCHS.".Status");
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

        $text .= EB_MATCHD_L2.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$reported_by.'">'.$reported_by_name.'</a> ('.$date.')<br />';
    }
    else
    {
        $date_reported  = '';
        $reported_by  = '';
        $comments  = EB_MATCHD_L3;
    }

    // Can I delete the game
    //-----------------------
    // Is the user a moderator?
    $q_2 = "SELECT ".TBL_EVENTMODS.".*"
    ." FROM ".TBL_EVENTMODS
    ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
    ."   AND (".TBL_EVENTMODS.".User = ".USERID.")";
    $result_2 = $sql->db_Query($q_2);
    $numMods = mysql_numrows($result_2);

    $reporter_matchteam = 0;
    $q_2 = "SELECT DISTINCT ".TBL_SCORES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".User = '$reported_by')";
    $result_2 = $sql->db_Query($q_2);
    $numRows = mysql_numrows($result_2);
    if ($numRows>0)
    {
      $reporter_matchteam = mysql_result($result_2,0, TBL_SCORES.".Player_MatchTeam");
    }

    // Is the user an opponent of the reporter?
    $q_2 = "SELECT DISTINCT ".TBL_SCORES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
    ." AND (".TBL_PLAYERS.".User = ".USERID.")";
    $result_2 = $sql->db_Query($q_2);
    $numOpps = mysql_numrows($result_2);

    $can_approve = 0;
    $can_delete = 0;
    if (  (USERID==$reported_by)
    &&(  ($eend==0)
    ||(  ($eend>=$time)
    &&($estart<=$time))))
    $can_delete = 1;
    if (check_class($pref['eb_mod_class']))  $can_delete = 1;
    if (USERID==$eowner)
    {
        $userclass |= eb_UC_EVENT_OWNER;
        $can_delete = 1;
        $can_approve = 1;
    }
    if ($numMods>0)
    {
        $userclass |= eb_UC_EB_MODERATOR;
        $can_delete = 1;
        $can_approve = 1;
    }
    if (check_class($pref['eb_mod_class']))
    {
        $userclass |= eb_UC_EB_MODERATOR;
        $can_approve = 1;
    }
    if ($numOpps>0)
    {
        $userclass |= eb_UC_EVENT_PLAYER;
        $can_approve = 1;
    }
    if($userclass < $eMatchesApproval) $can_approve = 0;
    if($eMatchesApproval == eb_UC_NONE) $can_approve = 0;
    if ($mStatus == 'active') $can_approve = 0;  

    if ($mStatus == 'pending')
        $text .= '<div>'.EB_MATCHD_L18.'</div>';


    if($can_delete != 0)
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/matchdelete.php?eventid='.$event_id.'" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="matchid" value="'.$match_id.'"/>';
        $text .= '<input class="button" type="submit" name="deletematch" value="'.EB_MATCHD_L4.'" onclick="return confirm(\''.EB_MATCHD_L5.'\');"/>';
        $text .= '</div>';
        $text .= '</form>';
    }
    if($can_approve != 0)
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/matchprocess.php" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="eventid" value="'.$event_id.'"/>';
        $text .= '<input type="hidden" name="matchid" value="'.$match_id.'"/>';
        $text .= '<input class="button" type="submit" name="approvematch" value="'.EB_MATCHD_L17.'"/>';
        $text .= '</div>';
        $text .= '</form>';
    }
    $text .= '<br />';

    $text .= '<table class="fborder" style="width:95%"><tbody>';
    $text .= '<tr>
    <td class="forumheader"><b>'.EB_MATCHD_L6.'</b></td>
    <td class="forumheader"><b>'.EB_MATCHD_L7.'</b></td>
    <td class="forumheader"><b>'.EB_MATCHD_L8.'</b></td>
    <td class="forumheader"><b>'.EB_MATCHD_L9.'</b></td>
    <td class="forumheader"><b>'.EB_MATCHD_L10.'</b></td>
    <td class="forumheader"><b>'.EB_MATCHD_L11.'</b></td>
    <td class="forumheader"><b>'.EB_MATCHD_L12.'</b></td>
    <td class="forumheader"><b>'.EB_MATCHD_L13.'</b></td>
    </tr>';
    for($i=0; $i < $numScores; $i++)
    {
        $pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
        $puid  = mysql_result($result,$i, TBL_USERS.".user_id");
        $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
        $pavatar = mysql_result($result,$i, TBL_USERS.".user_image");
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
        
        $image = "";
        if ($pref['eb_avatar_enable_playersstandings'] == 1)
        {
            if($pavatar)
            {
                $image = '<img '.getAvatarResize(avatar($pavatar)).' style="vertical-align:middle"/>';
            } else if ($pref['eb_avatar_default_image'] != ''){
                $image = '<img '.getAvatarResize(getAvatar($pref['eb_avatar_default_image'])).' style="vertical-align:middle"/>';
            }
        }

        //$text .= "Rank #$prank - $pname (team #$pMatchTeam)- score: $pscore (ELO:$pdeltaELO)<br />";
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.$prank.'</b></td>
        <td class="forumheader3">'.$pMatchTeam.'</td>
        <td class="forumheader3">'.$image.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a></td>
        <td class="forumheader3">'.$pscore.'</td>
        <td class="forumheader3">'.$ppoints.'</td>
        <td class="forumheader3">'.$pdeltaELO.'</td>
        <td class="forumheader3">'.$pdeltaTS_mu.'</td>
        ';

        $text .= '<td class="forumheader3">';
        if ($numScores>0)
        {
            // Find all opponents ratings
            $text .= '<table style="margin-left: 0px; margin-right: auto;">';
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
                    $text .= '<tr>';
                    $rating = getRating("ebscores", $pscoreid, $can_rate, true, $ouid);
                    if (preg_match("/".EB_RATELAN_2."/", $rating))
                    {
                        $text .= '<td>'.$rating.'</td><td></td>';
                    }
                    else if ($rating != EB_RATELAN_4)
                    {
                        $text .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$ouid.'">'.$oclantag.$ouname.'&nbsp;</a></td><td>'.$rating.'</td>';
                    }
                    else
                    {
                        $text .= '<td></td><td></td>';
                    }
                    $text .= '</tr>';
                }
            }
            $text .= '</table>';
        }
        $text .= '</td>';
        $text .= '</tr>';
    }
    $text .= '</tbody></table><br />';

    if ($comments)
    {
        $text .= '<p>';
        $text .= EB_MATCHD_L14.':<br />';
        $text .= $tp->toHTML($comments, true).'<br />';
        $text .= '</p>';
    }

    $text .= '</div>';

    $text .= '</div>';
    $text .= '</div>';

    $text .= '<p>';
    $text .= '<br />'.EB_MATCHD_L15.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.EB_MATCHD_L16.'</a>]<br />';
    $text .= '</p>';

    $ns->tablerender("$ename ($egame - ".eventType($etype).")", $text);

    unset($text);

    $text .= getComment("ebmatches", $match_id);
    echo $text;

}
require_once(FOOTERF);
exit;
?>
