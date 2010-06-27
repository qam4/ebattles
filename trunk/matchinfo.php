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

$text .= "
<script type='text/javascript' src='./js/tabpane.js'></script>
<script type='text/javascript'>
<!--//
function del_media(v)
{
document.getElementById('del_media').value=v;
document.getElementById('mediaform').submit();
}
//-->
</script>
";
$text .= "
<script type='text/javascript' src='./js/shadowbox/js/lib/prototype.js'></script>
<script type='text/javascript' src='./js/shadowbox/adapter/shadowbox-prototype.js'></script>
<script type='text/javascript' src='./js/shadowbox/shadowbox.js'></script>
<script type='text/javascript'>
Shadowbox.loadSkin('classic', './js/shadowbox/skin'); // use the classic skin
Shadowbox.loadLanguage('en', './js/shadowbox/lang'); // use the English language
Shadowbox.loadPlayer(['img', 'flv', 'wmt', 'swf', 'html', 'iframe'], './js/shadowbox/player'); // use img and qt players

window.onload = Shadowbox.init;

</script>";
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

    // Get event information
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
    $mMap = mysql_result($result,0, TBL_MATCHS.".Map");

    $mapImage = "";
    if ($mMap!=0)
    {
        $q_Maps = "SELECT ".TBL_MAPS.".*"
        ." FROM ".TBL_MAPS
        ." WHERE (".TBL_MAPS.".MapID = '$mMap')";
        $result_Maps = $sql->db_Query($q_Maps);
        $numMaps = mysql_numrows($result_Maps);

        if ($numMaps>0)
        {
            $mImage = mysql_result($result_Maps,$map , TBL_MAPS.".Image");
            $mName = mysql_result($result_Maps,$map , TBL_MAPS.".Name");
            $mDescrition = mysql_result($result_Maps,$map , TBL_MAPS.".Description");

            $mapImage = EB_MATCHR_L44.': '.$mName.' - '.$mDescrition.'<br /><a href="'.getImagePath($mImage, 'games_maps').'" rel="shadowbox"><img '.getMapImageResize($mImage).' title="'.$mName.'" style="vertical-align:middle"/>';
        }


    }

    // Get the scores for this match
    switch($etype)
    {
        case "One Player Ladder":
        case "Team Ladder":
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
        break;
        case "ClanWar":
        $q = "SELECT ".TBL_MATCHS.".*, "
        .TBL_SCORES.".*, "
        .TBL_CLANS.".*, "
        .TBL_TEAMS.".*, "
        .TBL_DIVISIONS.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_CLANS.", "
        .TBL_TEAMS.", "
        .TBL_DIVISIONS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
        ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
        ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
        ." ORDER BY ".TBL_SCORES.".Player_Rank";
        break;
        default:
    }

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
    $q_Mods = "SELECT ".TBL_EVENTMODS.".*"
    ." FROM ".TBL_EVENTMODS
    ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
    ."   AND (".TBL_EVENTMODS.".User = ".USERID.")";
    $result_Mods = $sql->db_Query($q_Mods);
    $numMods = mysql_numrows($result_Mods);

    $reporter_matchteam = 0;
    $q_Reporter = "SELECT DISTINCT ".TBL_SCORES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".User = '$reported_by')";
    $result_Reporter = $sql->db_Query($q_Reporter);
    $numRows = mysql_numrows($result_Reporter);
    if ($numRows>0)
    {
        $reporter_matchteam = mysql_result($result,0, TBL_SCORES.".Player_MatchTeam");
    }

    switch($etype)
    {
        case "One Player Ladder":
        case "Team Ladder":
        // Is the user a player of this match?
        $q_Played = "SELECT DISTINCT ".TBL_SCORES.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
        ." AND (".TBL_PLAYERS.".User = ".USERID.")";
        $result_Played = $sql->db_Query($q_Played);
        $numPlayed = mysql_numrows($result_Played);
        break;
        case "ClanWar":
        // Is the user an opponent of the reporter?
        $q_Played = "SELECT DISTINCT ".TBL_SCORES.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_TEAMS.", "
        .TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
        ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
        ." AND (".TBL_PLAYERS.".User = ".USERID.")";
        $result_Played = $sql->db_Query($q_Played);
        $numPlayed = mysql_numrows($result_Played);
        break;
        default:
    }

    switch($etype)
    {
        case "One Player Ladder":
        case "Team Ladder":
        // Is the user an opponent of the reporter?
        $q_Opps = "SELECT DISTINCT ".TBL_SCORES.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
        ." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
        ." AND (".TBL_PLAYERS.".User = ".USERID.")";
        $result_Opps = $sql->db_Query($q_Opps);
        $numOpps = mysql_numrows($result_Opps);
        break;
        case "ClanWar":
        // Is the user an opponent of the reporter?
        $q_Opps = "SELECT DISTINCT ".TBL_SCORES.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_TEAMS.", "
        .TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
        ." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
        ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
        ." AND (".TBL_PLAYERS.".User = ".USERID.")";
        $result_Opps = $sql->db_Query($q_Opps);
        $numOpps = mysql_numrows($result_Opps);
        break;
        default:
    }

    $can_approve = 0;
    $can_delete = 0;
    $can_submit_media = 0;
    $can_delete_media = 0;
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
        $can_submit_media = 1;
        $can_delete_media = 1;
    }
    if ($numMods>0)
    {
        $userclass |= eb_UC_EB_MODERATOR;
        $can_delete = 1;
        $can_approve = 1;
        $can_submit_media = 1;
        $can_delete_media = 1;
    }
    if (check_class($pref['eb_mod_class']))
    {
        $userclass |= eb_UC_EB_MODERATOR;
        $can_approve = 1;
        $can_submit_media = 1;
        $can_delete_media = 1;
    }
    if ($numOpps>0)
    {
        $userclass |= eb_UC_EVENT_PLAYER;
        $can_approve = 1;
    }
    if ($numPlayed>0)
    {
        $can_submit_media = 1;
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
        $text .= '<input type="hidden" name="eventid" value="'.$event_id.'"/>';
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
        switch($etype)
        {
            case "One Player Ladder":
            case "Team Ladder":
            $pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
            $puid  = mysql_result($result,$i, TBL_USERS.".user_id");
            $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
            $pavatar = mysql_result($result,$i, TBL_USERS.".user_image");
            $pteam  = mysql_result($result,$i, TBL_PLAYERS.".Team");
            list($pclan, $pclantag, $pclanid) = getClanName($pteam);
            break;
            case "ClanWar":
            $pid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
            $pname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $pavatar = mysql_result($result,$i, TBL_CLANS.".Image");
            $pteam  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
            list($pclan, $pclantag, $pclanid) = getClanName($pteam); // Use this function to get other clan info like clan id?
            break;
            default:
        }
        $pscoreid  = mysql_result($result,$i, TBL_SCORES.".ScoreID");
        $prank  = mysql_result($result,$i, TBL_SCORES.".Player_Rank");
        $pMatchTeam  = mysql_result($result,$i, TBL_SCORES.".Player_MatchTeam");
        $pdeltaELO  = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
        $pdeltaTS_mu  = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
        $pdeltaTS_sigma  = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
        $pscore  = mysql_result($result,$i, TBL_SCORES.".Player_Score");
        $pOppScore  = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");
        $ppoints  = mysql_result($result,$i, TBL_SCORES.".Player_Points");
        $pfaction  = mysql_result($result,$i, TBL_SCORES.".Faction");

        $pfactionIcon = "";
        if ($pfaction!=0)
        {
            $q_Factions = "SELECT ".TBL_FACTIONS.".*"
            ." FROM ".TBL_FACTIONS
            ." WHERE (".TBL_FACTIONS.".FactionID = '$pfaction')";
            $result_Factions = $sql->db_Query($q_Factions);
            $numFactions = mysql_numrows($result_Factions);
            if ($numFactions>0)
            {
                $fIcon = mysql_result($result_Factions,0 , TBL_FACTIONS.".Icon");
                $fName = mysql_result($result_Factions,0 , TBL_FACTIONS.".Name");

                $pfactionIcon = ' <img '.getFactionIconResize($fIcon).' title="'.$fName.'" style="vertical-align:middle"/>';
            }
        }

        $image = "";
        if ($pref['eb_avatar_enable_playersstandings'] == 1)
        {
            switch($etype)
            {
                case "One Player Ladder":
                case "Team Ladder":
                if($pavatar)
                {
                    $image = '<img '.getAvatarResize(avatar($pavatar)).' style="vertical-align:middle"/>';
                } else if ($pref['eb_avatar_default_image'] != ''){
                    $image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_image']), 'avatars').' style="vertical-align:middle"/>';
                }
                break;
                case "ClanWar":
                if($pavatar)
                {
                    $image = '<img '.getAvatarResize(getImagePath($pavatar), 'team_avatars').' style="vertical-align:middle"/>';
                } else if ($pref['eb_avatar_default_image'] != ''){
                    $image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_team_image']), 'team_avatars').' style="vertical-align:middle"/>';
                }
                break;
                default:
            }
        }

        //$text .= "Rank #$prank - $pname (team #$pMatchTeam)- score: $pscore (ELO:$pdeltaELO)<br />";
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.$prank.'</b></td>
        <td class="forumheader3">'.$pMatchTeam.$pfactionIcon.'</td>';
        switch($etype)
        {
            case "One Player Ladder":
            case "Team Ladder":
            $text .= '<td class="forumheader3">'.$image.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a></td>';
            break;
            case "ClanWar":
            $text .= '<td class="forumheader3">'.$image.' <a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$pclanid.'">'.$pclan.'</a></td>';
            break;
            default:
        }
        $text .= '
        <td class="forumheader3">'.$pscore.'</td>
        <td class="forumheader3">'.$ppoints.'</td>
        <td class="forumheader3">'.$pdeltaELO.'</td>
        <td class="forumheader3">'.$pdeltaTS_mu.'</td>
        ';

        // Opponent Ratings
        $text .= '<td class="forumheader3">';
        switch($etype)
        {
            case "One Player Ladder":
            case "Team Ladder":
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
                    list($oclan, $oclantag, $oclanid) = getClanName($oteam);

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
            break;
            case "ClanWar":
            break;
            default:
        }
        $text .= '</td>';

        $text .= '</tr>';
    }
    $text .= '</tbody></table><br />';

    // Map Image
    $text .= $mapImage;

    // Media
    $array_types = array(
    'Screenshot' => EB_MATCHD_L19,
    'Replay'     => EB_MATCHD_L20,
    'Video'      => EB_MATCHD_L21
    );

    // List of all media
    $q_UserMedia = "SELECT ".TBL_MEDIA.".*"
    ." FROM ".TBL_MEDIA
    ." WHERE (".TBL_MEDIA.".MatchID = '$match_id')"
    ."   AND (".TBL_MEDIA.".Submitter = ".USERID.")";
    $result_UserMedia = $sql->db_Query($q_UserMedia);
    $numUserMedia = mysql_numrows($result_UserMedia);
    //dbg: echo "numUserMedia $numUserMedia - ".$pref['eb_max_number_media']."<br>";
    if ($numUserMedia >= $pref['eb_max_number_media']) $can_submit_media = 0;

    $q_Media = "SELECT ".TBL_MEDIA.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_MEDIA.", "
    .TBL_USERS
    ." WHERE (".TBL_MEDIA.".MatchID = '$match_id')"
    ."   AND (".TBL_MEDIA.".Submitter = ".TBL_USERS.".user_id)";
    $result_Media = $sql->db_Query($q_Media);
    $numMedia = mysql_numrows($result_Media);

    $text .= '<table class="table_left">';
    $text .= '<form id="mediaform" action="'.e_PLUGIN.'ebattles/matchprocess.php" method="post">';
    $text .= '<input type="hidden" name="eventid" value="'.$event_id.'"/>';
    $text .= '<input type="hidden" name="matchid" value="'.$match_id.'"/>';
    $text .= '<input type="hidden" id="del_media" name="del_media" value=""/>';
    for ($media = 0; $media < $numMedia; $media++)
    {
        $mID = mysql_result($result_Media,$media , TBL_MEDIA.".MediaID");
        $mPath = mysql_result($result_Media,$media , TBL_MEDIA.".Path");
        $mType = mysql_result($result_Media,$media , TBL_MEDIA.".Type");
        $mSubmitterID = mysql_result($result_Media,$media , TBL_MEDIA.".Submitter");
        $mSubmitterName = mysql_result($result_Media,$media , TBL_USERS.".user_name");

        $shadow='';
        if (($mType == "Video")||($mType == "Screenshot"))
        {
            $shadow = 'rel="shadowbox"';
        }

        $text .= '<tr>';
        $text .= '<td><a href="'.$mPath.'" '.$shadow.'>'.$array_types["$mType"].'</a> '.EB_MATCHD_L24.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$mSubmitterID.'">'.$mSubmitterName.'</a><td>';
        $text .= '<td>';
        if (($mSubmitterID == USERID)||($can_delete_media == 1))
        {
            $text .= '<a href="javascript:del_media(\''.$mID.'\');" title="'.EB_MATCHD_L25.'" onclick="return confirm(\''.EB_MATCHD_L26.'\')"><img src="'.e_PLUGIN.'ebattles/images/cross.png" alt="'.EB_MATCHD_L25.'"/></a>';
        }
        $text .= '</td>';
        $text .= '</tr>';
    }
    $text .= '</form>';
    $text .= '</table>';

    /*
    $text .= "<a href='http://img269.imageshack.us/img269/7034/966b.png' rel='shadowbox'>My Image</a><br>";
    $text .= "<a href='http://www.youtube.com/v/iSZoeNuX4gk' rel='shadowbox'>My Video</a>";
    */

    if($can_submit_media != 0)
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/matchprocess.php" method="post">';
        $text .= '<input type="hidden" name="eventid" value="'.$event_id.'"/>';
        $text .= '<input type="hidden" name="matchid" value="'.$match_id.'"/>';
        $text .= '<table class="table_left"><tr>';
        $text .= '<td><select class="tbox" name="mediatype">';
        foreach ($array_types as $key => $value)
        {
            $text .= '<option value="'.$key.'"';
            $text .= '>'.$value.'</option>';
        }
        $text .= '</select></td>';
        $text .= '<td><input class="tbox" type="text" name="mediapath" size="40" value="" maxlength="64" title="'.EB_MATCHD_L22.'"/></td>';
        $text .= '<td><input class="button" type="submit" name="addmedia" value="'.EB_MATCHD_L23.'"/></td>';
        $text .= '</tr></table>';
        $text .= '</form>';
    }
    
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
