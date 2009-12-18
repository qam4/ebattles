<?php
/**
* EventInfo_process.php
*
*/
require_once(e_PLUGIN.'ebattles/include/event.php');

if(isset($_POST['quitevent'])){
    $pid = $_POST['player'];
    // Player can quit an event if he has not played yet
    $q = "SELECT ".TBL_PLAYERS.".*"
    ." FROM ".TBL_PLAYERS.", "
    .TBL_SCORES
    ." WHERE (".TBL_PLAYERS.".PlayerID = '$pid')"
    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
    $result = $sql->db_Query($q);
    $nbrscores = mysql_numrows($result);
    if ($nbrscores == 0)
    {
        $pid = mysql_result($result, 0, TBL_PLAYERS.".PlayerID");
        deletePlayer($pid);
        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);
    }
    header("Location: eventinfo.php?eventid=$event_id");
}
if(isset($_POST['joinevent'])){
    if ($_POST['joinEventPassword'] == $epassword)
    {
        eventAddPlayer ($event_id, USERID);
    }
    header("Location: eventinfo.php?eventid=$event_id");
}
if(isset($_POST['teamjoinevent'])){
    if ($_POST['joinEventPassword'] == $epassword)
    {
        $div_id = $_POST['division'];
        eventAddDivision($event_id, $div_id);
    }
    header("Location: eventinfo.php?eventid=$event_id");
}
if(isset($_POST['jointeamevent'])){
    $team_id = $_POST['team'];
    eventAddPlayer ($event_id, USERID, $team_id);
    header("Location: eventinfo.php?eventid=$event_id");
}

?>
