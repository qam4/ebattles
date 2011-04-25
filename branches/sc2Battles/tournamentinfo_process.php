<?php
/**
* TournamentInfo_process.php
*
*/
require_once(e_PLUGIN.'ebattles/include/tournament.php');

$tournament_id = $_GET['TournamentID'];
$tournament = new Tournament($tournament_id);

if(isset($_POST['quittournament'])){
    $pid = $_POST['player'];

    // Player can quit an tournament if he has not played yet
    $q = "SELECT ".TBL_PLAYERS.".*"
    ." FROM ".TBL_PLAYERS.", "
    .TBL_SCORES
    ." WHERE (".TBL_PLAYERS.".PlayerID = '$pid')"
    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
    $result = $sql->db_Query($q);
    $nbrscores = mysql_numrows($result);
    if ($nbrscores == 0)
    {
        deletePlayer($pid);
        $q = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '$tournament_id')";
        $result = $sql->db_Query($q);
    }
    header("Location: tournamentinfo.php?TournamentID=$tournament_id");
}
if(isset($_POST['jointournament'])){
    if ($_POST['joinTournamentPassword'] == $tournament->getField('Password'))
    {
        $tournament->tournamentAddPlayer(USERID, 0, FALSE);
    }
    header("Location: tournamentinfo.php?TournamentID=$tournament_id");
}
if(isset($_POST['teamjointournament'])){
    if ($_POST['joinTournamentPassword'] == $tournament->getField('Password'))
    {
        $div_id = $_POST['division'];
        $tournament->tournamentAddDivision($div_id, FALSE);
    }
    header("Location: tournamentinfo.php?TournamentID=$tournament_id");
}
if(isset($_POST['jointeamtournament'])){
    $team_id = $_POST['team'];
    $tournament->tournamentAddPlayer (USERID, $team_id, FALSE);
    header("Location: tournamentinfo.php?TournamentID=$tournament_id");
}

?>
