<?php
// functions for tournaments.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');

class Tournament extends DatabaseTable
{
	protected $tablename = TBL_TOURNAMENTS;
	protected $primary_key = "TournamentID";

	/***************************************************************************************
	Functions
	***************************************************************************************/
}

function tournamentTypeToString($type)
{
	switch($type)
	{
		case "Single Elimination":
		return EB_TOURNAMENTS_L22;
		break;
		default:
		return $type;
	}
}
?>
