<?php
// functions for clan.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/event.php');

class Clan extends DatabaseTable
{
	protected $tablename = TBL_CLANS;
	protected $primary_key = "ClanID";

	function deleteClan()
	{
		global $sql;
		$q = "DELETE FROM ".TBL_CLANS
		." WHERE (".TBL_CLANS.".ClanID = '".$this->fields['ClanID']."')";
		$result = $sql->db_Query($q);
	}
}

class Division extends DatabaseTable
{
	protected $tablename = TBL_DIVISIONS;
	protected $primary_key = "DivisionID";

	//----------------------------------------------------------
	function deleteMemberPlayers()
	{
		global $sql;

		$q_MemberPlayers = "SELECT ".TBL_MEMBERS.".*, "
		.TBL_TEAMS.".*, "
		.TBL_PLAYERS.".*"
		." FROM ".TBL_MEMBERS.", "
		.TBL_TEAMS.", "
		.TBL_PLAYERS
		." WHERE (".TBL_MEMBERS.".User = ".USERID.")"
		." AND (".TBL_MEMBERS.".Division = '".$this->fields['DivisionID']."')"
		." AND (".TBL_TEAMS.".Division = '".$this->fields['DivisionID']."')"
		." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)";
		$result_MemberPlayers = $sql->db_Query($q_MemberPlayers);
		$numMemberPlayers = mysql_numrows($result_MemberPlayers);
		if ($numMemberPlayers != 0)
		{
			for($j=0; $j<$numMemberPlayers; $j++)
			{
				$pID  = mysql_result($result_MemberPlayers,$j, TBL_PLAYERS.".PlayerID");
				deletePlayer($pID);
			}
		}
	}
	function deleteMember()
	{
		global $sql;

		$q = " DELETE FROM ".TBL_MEMBERS
		." WHERE (Division = '".$this->fields['DivisionID']."')"
		."   AND (User = ".USERID.")";
		$sql->db_Query($q);
	}
	//----------------------------------------------------------
	function deleteDivPlayers()
	{
		global $sql;
		$q_DivPlayers = "SELECT ".TBL_TEAMS.".*, "
		.TBL_PLAYERS.".*"
		." FROM ".TBL_TEAMS.", "
		.TBL_PLAYERS
		." WHERE (".TBL_TEAMS.".Division = '".$this->fields['DivisionID']."')"
		." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)";
		$result_DivPlayers = $sql->db_Query($q_DivPlayers);
		$numDivPlayers = mysql_numrows($result_DivPlayers);
		if ($numDivPlayers!=0)
		{
			for($j=0; $j<$numDivPlayers; $j++)
			{
				$pID  = mysql_result($result_DivPlayers,$j, TBL_PLAYERS.".PlayerID");
				deletePlayer($pID);
			}
		}
	}
	function deleteDivTeams()
	{
		// Attention, need to make sure teams have no players/scores first
		global $sql;
		$q = "DELETE FROM ".TBL_TEAMS
		." WHERE (".TBL_TEAMS.".Division = '".$this->fields['DivisionID']."')";
		$result = $sql->db_Query($q);
	}
	function deleteDivMembers()
	{
		global $sql;
		$q = "DELETE FROM ".TBL_MEMBERS
		." WHERE (".TBL_MEMBERS.".Division = '".$this->fields['DivisionID']."')";
		$result = $sql->db_Query($q);
	}
	function deleteDiv()
	{
		global $sql;
		$q = "DELETE FROM ".TBL_DIVISIONS
		." WHERE (".TBL_DIVISIONS.".DivisionID = '".$this->fields['DivisionID']."')";
		$result = $sql->db_Query($q);
	}
}

function getClanInfo($teamID)
{
	global $sql;
	$clan = '';
	$clantag = '';
	$q = "SELECT ".TBL_CLANS.".*, "
	.TBL_DIVISIONS.".*, "
	.TBL_TEAMS.".* "
	." FROM ".TBL_CLANS.", "
	.TBL_DIVISIONS.", "
	.TBL_TEAMS
	." WHERE (".TBL_TEAMS.".TeamID = '$teamID')"
	."   AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
	."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	if ($num_rows == 1)
	{
		$clanid  = mysql_result($result,0, TBL_CLANS.".ClanID");
		$clanname  = mysql_result($result,0, TBL_CLANS.".Name");
		$clantag  = mysql_result($result,0, TBL_CLANS.".Tag") ."&nbsp;";
	}
	return array($clanname, $clantag, $clanid);
}

?>
