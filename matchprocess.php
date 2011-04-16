<?php
/**
* MatchProcess.php
* Quick match report process
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');

if(isset($_POST['qrsubmitloss']))
{
    $ladder_id = $_POST['LadderID'];
    $reported_by = $_POST['reported_by'];
    $pwinnerID = $_POST['Player'];

    $q = "SELECT ".TBL_LADDERS.".*"
    ." FROM ".TBL_LADDERS
    ." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')";
    $result = $sql->db_Query($q);

    $eMatchesApproval = mysql_result($result,0 , TBL_LADDERS.".MatchesApproval");
    $etype = mysql_result($result,0 , TBL_LADDERS.".Type");

    // Attention here, we use user_id, so there has to be 1 user for 1 player
    $plooserUser = $reported_by;
    $q = "SELECT *"
    ." FROM ".TBL_PLAYERS
    ." WHERE (Ladder = '$ladder_id')"
    ."   AND (User = '$plooserUser')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $plooserID = $row['PlayerID'];

    // Create Match ------------------------------------------
    $comments = '';
    $q =
    "INSERT INTO ".TBL_MATCHS."(Ladder,ReportedBy,TimeReported, Comments, Status)
    VALUES ($ladder_id,'$reported_by',$time, '$comments', 'pending')";
    $result = $sql->db_Query($q);

    $last_id = mysql_insert_id();
    $match_id = $last_id;

    // Create Scores ------------------------------------------
    $q =
    "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_Rank)
    VALUES ($match_id,$pwinnerID,1,1)
    ";
    $result = $sql->db_Query($q);

    $q =
    "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_Rank)
    VALUES ($match_id,$plooserID,2,2)
    ";
    $result = $sql->db_Query($q);

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
        $q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
        $result = $sql->db_Query($q);
    }

    $q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
    $result = $sql->db_Query($q);

    header("Location: matchinfo.php?matchid=$match_id");
    exit;
}
if (isset($_POST['approvematch']))
{
    $ladder_id = $_POST['LadderID'];
    $match_id = $_POST['matchid'];

    $q = "SELECT ".TBL_LADDERS.".*"
    ." FROM ".TBL_LADDERS
    ." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')";
    $result = $sql->db_Query($q);

    $etype = mysql_result($result,0 , TBL_LADDERS.".Type");
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

    $q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
    $result = $sql->db_Query($q);

    header("Location: matchinfo.php?matchid=$match_id");
    exit;
}
if (isset($_POST['addmedia']))
{
    $ladder_id = $_POST['LadderID'];
    $match_id = $_POST['matchid'];
    $media_type = $_POST['mediatype'];
    $media_path = $tp->toDB($_POST['mediapath']);
    $submitter = USERID;

    if (preg_match("/http:\/\//", $media_path))
    {
        add_media($match_id, $submitter, $media_path, $media_type);
    }

    header("Location: matchinfo.php?matchid=$match_id");
    exit;
}
if (isset($_POST['del_media']) && $_POST['del_media']!="")
{
    $match_id = $_POST['matchid'];
    $media = $_POST['del_media'];

    delete_media($media);

    header("Location: matchinfo.php?matchid=$match_id");
    exit;
}

// should not be here -> redirect
header("Location: ladders.php");

?>
