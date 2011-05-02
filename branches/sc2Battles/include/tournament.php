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
	/**
	* tournamentAddPlayer - add a user to a tournament
	*/
	function tournamentAddPlayer($user, $team = 0, $notify)
	{
		global $sql;
		global $time;

		$q = "SELECT ".TBL_USERS.".*"
		." FROM ".TBL_USERS
		." WHERE (".TBL_USERS.".user_id = '$user')";
		$result = $sql->db_Query($q);
		$username = mysql_result($result, 0, TBL_USERS.".user_name");
		$useremail = mysql_result($result, 0, TBL_USERS.".user_email");

		// Find gamer for that user
		$q = "SELECT ".TBL_GAMERS.".*"
		." FROM ".TBL_GAMERS
		." WHERE (".TBL_GAMERS.".Game = '".$this->fields['Game']."')"
		."   AND (".TBL_GAMERS.".User = '$user')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		if ($num_rows==0)
		{
			echo "Error: no gamer";
			return;
		}
		else
		{
			$gamerID = mysql_result($result, 0, TBL_GAMERS.".GamerID");
		}

		// Is the user already signed up for the team?
		$q = "SELECT ".TBL_TPLAYERS.".*"
		." FROM ".TBL_TPLAYERS.", "
		.TBL_GAMERS
		." WHERE (".TBL_TPLAYERS.".Tournament = '".$this->fields['TournamentID']."')"
		."   AND (".TBL_TPLAYERS.".Team = '$team')"
		."   AND (".TBL_TPLAYERS.".Gamer = '$gamerID')";
		$result = $sql->db_Query($q);
		$num_rows = mysql_numrows($result);
		echo "num_rows: $num_rows<br>";
		if ($num_rows==0)
		{
			$q = " INSERT INTO ".TBL_TPLAYERS."(Tournament,Gamer,Team,Joined)
			VALUES (".$this->fields['TournamentID'].",$gamerID,$team,$time)";
			$sql->db_Query($q);
			echo "player created, query: $q<br>";
			$q = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '".$this->fields['TournamentID']."')";
			$sql->db_Query($q);

			if ($notify)
			{
				$sendto = $user;
				$subject = SITENAME.$this->fields['Name'];
				$message = EB_TOURNAMENTS_L26.$username.EB_TOURNAMENTS_L27.$this->fields['Name'].EB_TOURNAMENTS_L29.EB_TOURNAMENTS_L31.USERNAME;
				sendNotification($sendto, $subject, $message, $fromid=0);

				// Send email
				//$message = EB_TOURNAMENTS_L26.$username.EB_TOURNAMENTS_L27.$this->fields['Name'].EB_TOURNAMENTS_L30."<a href='".SITEURLBASE.e_PLUGIN_ABS."ebattles/tournamentinfo.php?TournamentID=$this->fields['TournamentID']'>$this->fields['Name']</a>.".EB_TOURNAMENTS_L31.USERNAME.EB_TOURNAMENTS_L32;
				$message = EB_TOURNAMENTS_L26.$username.EB_TOURNAMENTS_L27.$this->fields['Name'].EB_TOURNAMENTS_L30.SITEURLBASE.e_PLUGIN_ABS."ebattles/tournamentinfo.php?TournamentID=$this->fields['TournamentID']".EB_TOURNAMENTS_L31.USERNAME;
				require_once(e_HANDLER."mail.php");
				sendemail($useremail, $subject, $message);
			}
		}
	}


	/**
	* tournamentAddDivision - add a division to a tournament
	*/
	function tournamentAddDivision($div_id, $notify)
	{
		global $sql;
		global $time;

		//$add_players = ( $this->fields['Type'] == "ClanWar" ? FALSE : TRUE);
		$add_players = TRUE;

		// Is the division signed up
		$q = "SELECT ".TBL_TTEAMS.".*"
		." FROM ".TBL_TTEAMS
		." WHERE (".TBL_TTEAMS.".Tournament = '".$this->fields['TournamentID']."')"
		." AND (".TBL_TTEAMS.".Division = '$div_id')";
		$result = $sql->db_Query($q);
		$numTeams = mysql_numrows($result);
		if($numTeams == 0)
		{
			$q = "INSERT INTO ".TBL_TTEAMS."(Tournament,Division,Joined)
			VALUES (".$this->fields['TournamentID'].",$div_id,$time)";
			$sql->db_Query($q);
			$team_id =  mysql_insert_id();

			if ($add_players == TRUE)
			{
				// All members of this division will automatically be signed up to this tournament
				$q_2 = "SELECT ".TBL_DIVISIONS.".*, "
				.TBL_MEMBERS.".*, "
				.TBL_USERS.".*"
				." FROM ".TBL_DIVISIONS.", "
				.TBL_USERS.", "
				.TBL_MEMBERS
				." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
				." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
				." AND (".TBL_USERS.".user_id = ".TBL_MEMBERS.".User)";
				$result_2 = $sql->db_Query($q_2);
				$num_rows_2 = mysql_numrows($result_2);
				if($num_rows_2 > 0)
				{
					for($j=0; $j<$num_rows_2; $j++)
					{
						$user_id  = mysql_result($result_2,$j, TBL_USERS.".user_id");
						$this->tournamentAddPlayer($user_id, $team_id, $notify);
					}
					$q4 = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 1 WHERE (TournamentID = '".$this->fields['TournamentID']."')";
					$result = $sql->db_Query($q4);
				}
			}
		}
	}
	
	function updateResults($results) {
		global $sql;
		
		$new_results = serialize($results);
		$q = "UPDATE ".TBL_TOURNAMENTS." SET Results = '".$new_results."' WHERE (TournamentID = '".$this->fields['TournamentID']."')";
		$result = $sql->db_Query($q);		
	}

	function updateRounds($rounds) {
		global $sql;
		
		$new_rounds = serialize($rounds);
		$q = "UPDATE ".TBL_TOURNAMENTS." SET Rounds = '".$new_rounds."' WHERE (TournamentID = '".$this->fields['TournamentID']."')";
		$result = $sql->db_Query($q);		
	}

	function updateMapPool($mapPool) {
		global $sql;
		
		$mapString = '';
		for ($map = 0; $map < count($mapPool); $map++)
		{
			if ($map > 0) $mapString .= ',';
			$mapString .= $mapPool[$map];
		}
		
		$q = "UPDATE ".TBL_TOURNAMENTS." SET MapPool = '".$mapString."' WHERE (TournamentID = '".$this->fields['TournamentID']."')";
		var_dump($q);
		exit;
		$result = $sql->db_Query($q);		
	}
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

function deleteTPlayer($player_id)
{
	global $sql;
	$q = "DELETE FROM ".TBL_TPLAYERS
	." WHERE (".TBL_TPLAYERS.".TPlayerID = '$player_id')";
	$result = $sql->db_Query($q);
}
?>
