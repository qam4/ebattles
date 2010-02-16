<?php
/**
* matchreport.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/clan.php");

/*******************************************************************
********************************************************************/
// Specify if we use WYSIWYG for text areas
global $e_wysiwyg;
$e_wysiwyg = "match_comment";  // set $e_wysiwyg before including HEADERF
require_once(HEADERF);

$text = '';

$text .= '
<script type="text/javascript">
';
$text .= "
<!--
function SwitchSelected(id)
{
var select = document.getElementById('rank'+id);
nbr_ranks = select.length
new_rank_txt = select.options[select.selectedIndex].text

for (k = 1; k <= nbr_ranks; k++)
{
old_rank_found=0
for (j = 1; j <= nbr_ranks; j++)
{
var select = document.getElementById('rank'+j);
rank_txt = select.options[select.selectedIndex].text
if (rank_txt == 'Team #'+k) {old_rank_found=1}
}
if (old_rank_found==0) {old_rank = k}
}

for (j = 1; j <= nbr_ranks; j++)
{
if (j!=id)
{
var select = document.getElementById('rank'+j);
rank_txt = select.options[select.selectedIndex].text
if (rank_txt == new_rank_txt) {select.selectedIndex=old_rank-1}
}
}
}
//-->
";
$text .= '
</script>
';

/* Event Name */
$event_id = $_GET['eventid'];

$q = "SELECT ".TBL_EVENTS.".*"
." FROM ".TBL_EVENTS
." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
$result = $sql->db_Query($q);

$ename = mysql_result($result,0 , TBL_EVENTS.".Name");
$etype = mysql_result($result,0 , TBL_EVENTS.".Type");
$eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
$eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");
$eTS_beta = mysql_result($result,0 , TBL_EVENTS.".TS_beta");
$eTS_epsilon = mysql_result($result,0 , TBL_EVENTS.".TS_epsilon");
$ePointPerWin = mysql_result($result,0 , TBL_EVENTS.".PointsPerWin");
$ePointPerDraw = mysql_result($result,0 , TBL_EVENTS.".PointsPerDraw");
$ePointPerLoss = mysql_result($result,0 , TBL_EVENTS.".PointsPerLoss");
$eAllowDraw = mysql_result($result,0 , TBL_EVENTS.".AllowDraw");
$eAllowScore = mysql_result($result,0 , TBL_EVENTS.".AllowScore");
$eAllowScore = mysql_result($result,0 , TBL_EVENTS.".AllowScore");
$eMatchesApproval = mysql_result($result,0 , TBL_EVENTS.".MatchesApproval");

switch($etype)
{
    case "One Player Ladder":
    case "Team Ladder":
    $q = "SELECT ".TBL_PLAYERS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ." AND (".TBL_PLAYERS.".Banned != 1)"
    ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
    ." ORDER BY ".TBL_USERS.".user_name";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    $players_id[0] = EB_MATCHR_L1;
    $players_uid[0] = EB_MATCHR_L1;
    $players_name[0] = EB_MATCHR_L1;
    for($i=0; $i<$num_rows; $i++){
        $pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
        $puid  = mysql_result($result,$i, TBL_USERS.".user_id");
        $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
        $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
        $pteam  = mysql_result($result,$i, TBL_PLAYERS.".Team");
        list($pclan, $pclantag, $pclanid) = getClanName($pteam);
        if ($prank==0)
        $prank_txt = EB_EVENT_L54;
        else
        $prank_txt = "#$prank";

        $players_id[$i+1] = $pid;
        $players_uid[$i+1] = $puid;
        $players_name[$i+1] = $pclantag.$pname." ($prank_txt)";
    }
    break;
    case "ClanWar":
    $q = "SELECT ".TBL_CLANS.".*, "
    .TBL_TEAMS.".*, "
    .TBL_DIVISIONS.".* "
    ." FROM ".TBL_CLANS.", "
    .TBL_TEAMS.", "
    .TBL_DIVISIONS
    ." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
    ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
    ." AND (".TBL_TEAMS.".Event = '$event_id')"
    ." ORDER BY ".TBL_CLANS.".Name";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    $players_id[0] = EB_MATCHR_L1;
    $players_uid[0] = EB_MATCHR_L1;
    $players_name[0] = EB_MATCHR_L1;
    for($i=0; $i<$num_rows; $i++){
        $pid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
        $puid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
        $prank  = mysql_result($result,$i, TBL_TEAMS.".Rank");
        $pname  = mysql_result($result,$i, TBL_CLANS.".Name");
        if ($prank==0)
        $prank_txt = EB_EVENT_L54;
        else
        $prank_txt = "#$prank";

        $players_id[$i+1] = $pid;
        $players_uid[$i+1] = $puid;
        $players_name[$i+1] = $pname." ($prank_txt)";
    }
    break;
    default:
}

$text .= '
<div class="spacer">
';

// assuming we saved the above function in "functions.php", let's make sure it's available
require_once(e_PLUGIN.'ebattles/matchreport_functions.php');

// has the form been submitted?
if (isset($_POST['submit']))
{
    // the form has been submitted
    // perform data checks.
    $error_str = ''; // initialise $error_str as empty

    $reported_by = $_POST['reported_by'];
    $userclass = $_POST['userclass'];
    //$text .= "reported by: $reported_by<br />";

    $comments = $tp->toDB($_POST['match_comment']);

    $nbr_players = $_POST['nbr_players'];
    $nbr_teams = $_POST['nbr_teams'];
    $userIsPlaying = 0;
    for($i=1;$i<=$nbr_players;$i++)
    {
        $pid = $_POST['player'.$i];

        switch($etype)
        {
            case "One Player Ladder":
            case "Team Ladder":
            $q =
            "SELECT ".TBL_USERS.".*, "
            .TBL_PLAYERS.".*"
            ." FROM ".TBL_USERS.", "
            .TBL_PLAYERS
            ." WHERE (".TBL_PLAYERS.".PlayerID = '$pid')"
            ."   AND (".TBL_PLAYERS.".User     = ".TBL_USERS.".user_id)";
            $result = $sql->db_Query($q);
            $row = mysql_fetch_array($result);
            $puid = $row['user_id'];
            $pTeam = $row['Team'];
            $pMatchTeam = $_POST['team'.$i];

            if ($puid == $reported_by)
            $userIsPlaying = 1;

            // Check if a player is not selected
            if ($pid == $players_name[0])
            $error_str .= '<li>'.EB_MATCHR_L2.$i.'&nbsp;'.EB_MATCHR_L3.'</li>';

            // Check if 2 players are the same user
            // Check if 2 players of same team are playing against each other
            for($j=$i+1;$j<=$nbr_players;$j++)
            {
                //if ($_POST['player'.$i] == $_POST['player'.$j])
                $pjid = $_POST['player'.$j];
                $q =
                "SELECT ".TBL_USERS.".*, "
                .TBL_PLAYERS.".*"
                ." FROM ".TBL_USERS.", "
                .TBL_PLAYERS
                ." WHERE (".TBL_PLAYERS.".PlayerID = '$pjid')"
                ."   AND (".TBL_PLAYERS.".User   = ".TBL_USERS.".user_id)";
                $result = $sql->db_Query($q);
                $row = mysql_fetch_array($result);
                $pjuid = $row['user_id'];
                $pjTeam = $row['Team'];
                $pjMatchTeam = $_POST['team'.$j];

                if ($puid == $pjuid)
                $error_str .= '<li>'.EB_MATCHR_L4.$i.'&nbsp;'.EB_MATCHR_L5.$j.'</li>';
                if (($pTeam == $pjTeam)&&($pMatchTeam != $pjMatchTeam)&&($pTeam != 0))
                $error_str .= '<li>'.EB_MATCHR_L6.$i.'&nbsp;'.EB_MATCHR_L7.$j.' '.EB_MATCHR_L8.'</li>';
            }
            break;
            case "ClanWar":

            //fm- Need to do some check here

            break;
            default:
        }
    }
    if (($userclass == eb_UC_EVENT_PLAYER) && ($userIsPlaying == 0))
    $error_str .= '<li>'.EB_MATCHR_L9.'</li>';

    // Check if a team has no player
    // Check if a score is not a number
    for($i=1;$i<=$nbr_teams;$i++)
    {
        if (!isset($_POST['score'.$i])) $_POST['score'.$i] = 0;
        $team_players = 0;
        for($j=1;$j<=$nbr_players;$j++)
        {
            if ($_POST['team'.$j] == 'Team #'.$i)
            $team_players ++;
        }
        if ($team_players == 0)
        $error_str .= '<li>'.EB_MATCHR_L10.$i.'&nbsp;'.EB_MATCHR_L11.'</li>';
        if(!preg_match("/^\d+$/", $_POST['score'.$i]))
        $error_str .= '<li>'.EB_MATCHR_L12.$i.'&nbsp;'.EB_MATCHR_L13.'&nbsp;'.$_POST['score'.$i].'</li>';
    }

    // we could do more data checks, but you get the idea.
    // we could also strip any HTML from the variables, convert it to entities, have a maximum character limit on the values, etc etc, but this is just an example.
    // now, have any of these errors happened? We can find out by checking if $error_str is empty

    //$error_str = 'test';

    if (!empty($error_str)) {
        // show form again
        user_form($players_id, $players_name, $event_id, $eAllowDraw, $eAllowScore,$userclass);
        // errors have occured, halt execution and show form again.
        $text .= '<p style="color:red">'.EB_MATCHR_L14;
        $text .= '<ul style="color:red">'.$error_str.'</ul></p>';
    }
    else
    {
        //$text .= "OK<br />";
        $nbr_players = $_POST['nbr_players'];

        $actual_rank[1] = 1;
        for($i=1;$i<=$nbr_teams;$i++)
        {
            $text .= 'Rank #'.$i.': '.$_POST['rank'.$i];
            $text .= '<br />';
            // Calculate actual rank based on draws checkboxes
            if ($_POST['draw'.$i] != "")
            $actual_rank[$i] = $actual_rank[$i-1];
            else
            $actual_rank[$i] = $i;
        }

        $text .= '--------------------<br />';

        $text .= 'Comments: '.$tp->toHTML($comments).'<br />';

        // Create Match ------------------------------------------
        $q =
        "INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported,Comments, Status)
        VALUES ($event_id,'$reported_by',$time, '$comments', 'pending')";
        $result = $sql->db_Query($q);

        $last_id = mysql_insert_id();
        $match_id = $last_id;

        // Create Scores ------------------------------------------
        for($i=1;$i<=$nbr_players;$i++)
        {
            $pid = $_POST['player'.$i];
            $pteam = str_replace("Team #","",$_POST['team'.$i]);

            for($j=1;$j<=$nbr_teams;$j++)
            {
                if( $_POST['rank'.$j] == "Team #".$pteam)
                $prank = $actual_rank[$j];
            }

            for($j=1;$j<=$nbr_teams;$j++)
            {
                if( $_POST['rank'.$j] == "Team #".$pteam)
                $pscore = $_POST['score'.$j];
            }

            switch($etype)
            {
                case "One Player Ladder":
                case "Team Ladder":
                $q =
                "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_Score,Player_Rank)
                VALUES ($match_id,$pid,$pteam,$pscore,$prank)
                ";
                break;
                case "ClanWar":
                $q =
                "INSERT INTO ".TBL_SCORES."(MatchID,Team,Player_MatchTeam,Player_Score,Player_Rank)
                VALUES ($match_id,$pid,$pteam,$pscore,$prank)
                ";
                break;
                default:
            }
            $result = $sql->db_Query($q);
        }
        $text .= '--------------------<br />';

        // Update scores stats
        match_scores_update($match_id);

        // Automatically Update Players stats only if Match Approval is Disabled
        if ($eMatchesApproval == eb_UC_NONE)
        {
            switch($etype)
            {
                case "One Player Ladder":
                case "Team Ladder":
                match_players_update($match_id);
                break;
                case "ClanWar":
                match_teams_update($match_id);
                break;
                default:
            }

            $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
            $result = $sql->db_Query($q);
        }

        header("Location: matchinfo.php?matchid=$match_id");
        exit();
    }
    // if we get here, all data checks were okay, process information as you wish.
} else {

    if (!isset($_POST['matchreport']))
    {
        $text .= '<p>'.EB_MATCHR_L33.'</p>';
        $text .= '<p>'.EB_MATCHR_L34.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">Event</a>]</p>';
    }
    else if (!check_class(e_UC_MEMBER))
    {
        $text .= '<p>'.EB_MATCHR_L36.'</p>';
        $text .= '<p>'.EB_MATCHR_L34.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">Event</a>]</p>';
    }
    else
    {
        $userclass = $_POST['userclass'];
        // the form has not been submitted, let's show it
        user_form($players_id, $players_name, $event_id, $eAllowDraw, $eAllowScore,$userclass);
    }
}

$text .= '
</div>
';

$ns->tablerender("$ename (".eventType($etype).") - ".EB_MATCHR_L32, $text);
require_once(FOOTERF);
exit;
?>
