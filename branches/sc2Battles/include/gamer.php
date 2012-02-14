<?php
// functions for events.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');

class Gamer extends DatabaseTable
{
	protected $tablename = TBL_GAMERS;
	protected $primary_key = "GamerID";
}

function updateGamer($user, $game, $Name, $UniqueGameID){
	global $tp;
	global $sql;

	$q = "SELECT ".TBL_GAMERS.".*"
	." FROM ".TBL_GAMERS
	." WHERE (".TBL_GAMERS.".Game = '".$game."')"
	."   AND (".TBL_GAMERS.".User = ".$user.")";
	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	if ($num_rows==0)
	{
		$q = " INSERT INTO ".TBL_GAMERS."(User,Game,Name,UniqueGameID)
		VALUES ($user, $game, '".$Name."', '".$UniqueGameID."')";
		$sql->db_Query($q);
	}
	else
	{
		$gamerID =  mysql_result($result, 0, TBL_GAMERS.".GamerID");
		$q = "UPDATE ".TBL_GAMERS." SET UniqueGameID = '".$UniqueGameID."' WHERE (GamerID = '".$gamerID."')";
		$sql->db_Query($q);
		$q = "UPDATE ".TBL_GAMERS." SET Name = '".$Name."' WHERE (GamerID = '".$gamerID."')";
		$sql->db_Query($q);
	}
}


?>
