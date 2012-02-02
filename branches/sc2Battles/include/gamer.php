<?php
// functions for events.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');

class Gamer extends DatabaseTable
{
	protected $tablename = TBL_GAMERS;
	protected $primary_key = "GamerID";
}

class SC2Gamer extends Gamer
{
	/***************************************************************************************
	Functions
	***************************************************************************************/
	function getGamerName()
	{
		$tmp = explode('#', $this->fields['UniqueGameID']);
		return $tmp[0];
	}

	function getGamerCode()
	{
		$tmp = explode('#', $this->fields['UniqueGameID']);
		return $tmp[1];
	}
}

function updateGamer($user, $game, $UniqueGameID){
	global $tp;
	global $sql;

	$tmp = explode('#', $UniqueGameID);
	$q = "SELECT ".TBL_GAMERS.".*"
	." FROM ".TBL_GAMERS
	." WHERE (".TBL_GAMERS.".Game = '".$game."')"
	."   AND (".TBL_GAMERS.".User = ".$user.")";
	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	if ($num_rows==0)
	{
		$q = " INSERT INTO ".TBL_GAMERS."(User,Game,Name,UniqueGameID)
		VALUES ($user, $game, '".$tmp[0]."', '".$UniqueGameID."')";
		$sql->db_Query($q);
	}
	else
	{
		$gamerID =  mysql_result($result, 0, TBL_GAMERS.".GamerID");
		$q = "UPDATE ".TBL_GAMERS." SET UniqueGameID = '".$UniqueGameID."' WHERE (GamerID = '".$gamerID."')";
		$sql->db_Query($q);
		$q = "UPDATE ".TBL_GAMERS." SET Name = '".$tmp[0]."' WHERE (GamerID = '".$gamerID."')";
		$sql->db_Query($q);
	}
}


?>
