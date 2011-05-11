<?php
/**
* TournamentInfo_process.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/tournament.php');
require_once(e_PLUGIN.'ebattles/include/gamer.php');

$tournament_id = $_GET['TournamentID'];
$tournament = new Tournament($tournament_id);

if(isset($_POST['quittournament'])){
	$pid = $_POST['player'];

	// Player can quit an tournament if he has not played yet
	// TODO - can quit if tournament not started.
	$q = "SELECT ".TBL_TPLAYERS.".*"
	." FROM ".TBL_TPLAYERS.", "
	.TBL_SCORES
	." WHERE (".TBL_TPLAYERS.".TPlayerID = '$pid')"
	." AND (".TBL_SCORES.".Player = ".TBL_TPLAYERS.".TPlayerID)";
	$result = $sql->db_Query($q);
	$nbrscores = mysql_numrows($result);

	$nbrscores = 0;
	if ($nbrscores == 0)
	{
		deleteTPlayer($pid);
		$q = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '$tournament_id')";
		$result = $sql->db_Query($q);
	}
	header("Location: tournamentinfo.php?TournamentID=$tournament_id");
}
if(isset($_POST['jointournament'])){
	
	if ($_POST['joinTournamentPassword'] == $tournament->getField('password'))
	{
		$UniqueGameID = $tp->toDB($_POST["charactername"].'#'.$_POST["code"]);
		updateGamer(USERID, $tournament->getField('Game'), $UniqueGameID);
		$tournament->tournamentAddPlayer(USERID, 0, FALSE);
	}

	header("Location: tournamentinfo.php?TournamentID=$tournament_id");
}
if(isset($_POST['teamjointournament'])){
	if ($_POST['joinTournamentPassword'] == $tournament->getField('password'))
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
