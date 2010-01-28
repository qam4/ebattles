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
    $event_id = $_POST['eventid'];
    $reported_by = $_POST['reported_by'];
    $pwinnerID = $_POST['Player'];

    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
    $result = $sql->db_Query($q);

    $eMatchesApproval = mysql_result($result,0 , TBL_EVENTS.".MatchesApproval");

    // Attention here, we use user_id, so there has to be 1 user for 1 player
    $plooserUser = $reported_by;
    $q = "SELECT *"
    ." FROM ".TBL_PLAYERS
    ." WHERE (Event = '$event_id')"
    ."   AND (User = '$plooserUser')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $plooserID = $row['PlayerID'];

    // Create Match ------------------------------------------
    $comments = '';
    $q =
    "INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported, Comments, Status)
    VALUES ($event_id,'$reported_by',$time, '$comments', 'pending')";
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
    if ($eMatchesApproval == eb_MA_DISABLE)
    {
        match_players_update($match_id);

        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);
    }

    $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
    $result = $sql->db_Query($q);

    header("Location: matchinfo.php?matchid=$match_id");
    exit;
}
if (isset($_POST['approvematch']))
{
    $event_id = $_POST['eventid'];
    $match_id = $_POST['matchid'];

    match_players_update($match_id);

    $q = "UPDATE ".TBL_MATCHS." SET Status = 'active' WHERE (MatchID = '$match_id')";
    $result = $sql->db_Query($q);

    $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
    $result = $sql->db_Query($q);

    header("Location: matchinfo.php?matchid=$match_id");
    exit;
}

// should not be here -> redirect
header("Location: events.php");

?>
