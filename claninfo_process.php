<?php
/**
* ClanInfo_process.php
*
*/
require_once(e_PLUGIN.'ebattles/include/clan.php');
require_once(e_PLUGIN.'ebattles/include/event.php');

if(isset($_POST['joindivision']))
{
	$div_id = $_POST['division'];

	$q = "SELECT ".TBL_CLANS.".*, "
	.TBL_DIVISIONS.".*"
	." FROM ".TBL_CLANS.", "
	.TBL_DIVISIONS
	." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
	." AND (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)";

	$result = $sql->db_Query($q);
	$clan_password  = mysql_result($result, 0, TBL_CLANS.".password");
	$gid  = mysql_result($result, 0, TBL_DIVISIONS.".Game");
	
	if ($_POST['joindivisionPassword'] == $clan_password)
	{
		$Name = $tp->toDB($_POST["gamername"]);
		$UniqueGameID = $tp->toDB($_POST["gameruniquegameid"]);
		updateGamer(USERID, $gid, $Name, $UniqueGameID);

		$q = " INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
		VALUES ($div_id,".USERID.",$time)";
		$sql->db_Query($q);

		// User will automatically be signed up to all current events this division participates in
		$q_2 = "SELECT ".TBL_TEAMS.".*, "
		.TBL_EVENTS.".*"
		." FROM ".TBL_TEAMS.", "
		.TBL_EVENTS
		." WHERE (".TBL_TEAMS.".Division = '$div_id')"
		." AND (".TBL_TEAMS.".Event = ".TBL_EVENTS.".EventID)"
		." AND (".TBL_EVENTS.".Status != 'finished')";

		$result_2 = $sql->db_Query($q_2);
		$num_rows_2 = mysql_numrows($result_2);
		if($num_rows_2>0)
		{
			for($j=0; $j<$num_rows_2; $j++)
			{
				$event_id  = mysql_result($result_2,$j, TBL_EVENTS.".EventID");
				$event = new Event($event_id);

				$team_id = mysql_result($result_2,$j, TBL_TEAMS.".TeamID");

				// Find gamer for that user
				$q = "SELECT ".TBL_GAMERS.".*"
				." FROM ".TBL_GAMERS
				." WHERE (".TBL_GAMERS.".Game = '".$event->getField('Game')."')"
				."   AND (".TBL_GAMERS.".User = '".USERID."')";
				$result = $sql->db_Query($q);
				$num_rows = mysql_numrows($result);
				if ($num_rows==0)
				{
					$q = " INSERT INTO ".TBL_GAMERS."(User,Game,UniqueGameID)
					VALUES ($user,".$event->getField('Game').",'".USERNAME."')";
					$sql->db_Query($q);
					$last_id = mysql_insert_id();
					$gamerID = $last_id;
				}
				else
				{
					$gamerID = mysql_result($result, 0, TBL_GAMERS.".GamerID");
				}

				// Verify there is no other player for that user/event/team
				$q = "SELECT COUNT(*) as NbrPlayers"
				." FROM ".TBL_PLAYERS
				." WHERE (Event = '$event_id')"
				." AND (Team = '$team_id')"
				." AND (User = ".USERID.")";
				$result = $sql->db_Query($q);
				$row = mysql_fetch_array($result);
				$nbrplayers = $row['NbrPlayers'];
				if ($nbrplayers == 0)
				{
					$q = " INSERT INTO ".TBL_PLAYERS."(Event,Gamer,Team,ELORanking,TS_mu,TS_sigma)
					VALUES ($event_id,$gamerID,$team_id,$event->getField('ELO_default'),$event->getField('TS_default_mu'),$event->getField('TS_default_sigma'))";
					$sql->db_Query($q);
					$event->setFieldDB('IsChanged', 1);
				}
			}
		}
	}
	// TODO: $clan_id not defined
	header("Location: claninfo.php?clanid=$clan_id");
}
if(isset($_POST['quitdivision']))
{
	$div_id = $_POST['division'];
	$division = new Division($div_id);

	// Check that the member has made no games with this division
	$q_MemberScores = "SELECT ".TBL_MEMBERS.".*, "
	.TBL_TEAMS.".*, "
	.TBL_PLAYERS.".*, "
	.TBL_SCORES.".*"
	." FROM ".TBL_MEMBERS.", "
	.TBL_TEAMS.", "
	.TBL_PLAYERS.", "
	.TBL_SCORES
	." WHERE (".TBL_MEMBERS.".User = ".USERID.")"
	." AND (".TBL_MEMBERS.".Division = '$div_id')"
	." AND (".TBL_TEAMS.".Division = '$div_id')"
	." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
	." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
	$result_MemberScores = $sql->db_Query($q_MemberScores);
	$numMemberScores = mysql_numrows($result_MemberScores);
	if ($numMemberScores == 0)
	{
		$division->deleteMemberPlayers();
		$division->deleteMember();
	}
	// TODO: $clan_id not defined
	header("Location: claninfo.php?clanid=$clan_id");
}
?>
