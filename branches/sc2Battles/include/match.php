<?php
// functions for matchs score updates.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/ELO.php');
require_once(e_PLUGIN.'ebattles/include/trueskill.php');
require_once(e_PLUGIN.'ebattles/include/ladder.php');

class Match extends DatabaseTable
{
	protected $tablename = TBL_MATCHS;
	protected $primary_key = "MatchID";

	/***************************************************************************************
	Functions
	***************************************************************************************/
	function match_scores_update()
	{
		global $sql;

		// Get ladder info
		$q = "SELECT ".TBL_LADDERS.".*, "
		.TBL_MATCHS.".*"
		." FROM ".TBL_LADDERS.", "
		.TBL_MATCHS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		."   AND (".TBL_LADDERS.".LadderID = ".TBL_MATCHS.".Ladder)";
		$result = $sql->db_Query($q);
		$ladder_id = mysql_result($result,0 , TBL_LADDERS.".LadderID");
		$ladder = new Ladder($ladder_id);

		// Initialize scores ELO/TrueSkill
		$deltaELO = 0;
		$deltaTS_mu = 0;
		$deltaTS_sigma = 1;
		$q = "UPDATE ".TBL_SCORES
		." SET Player_deltaELO = '".floatToSQL($deltaELO)."',"
		."     Player_deltaTS_mu = '".floatToSQL($deltaTS_mu)."',"
		."     Player_deltaTS_sigma = '".floatToSQL($deltaTS_sigma)."',"
		."     Player_Win = 0,"
		."     Player_Draw = 0,"
		."     Player_Loss = 0,"
		."     Player_Points = 0"
		." WHERE (MatchID = '".$this->fields['MatchID']."')";
		$result = $sql->db_Query($q);

		// Calculate number of players and teams for the match
		$q = "SELECT DISTINCT ".TBL_SCORES.".Player_MatchTeam"
		." FROM ".TBL_SCORES
		." WHERE (".TBL_SCORES.".MatchID = '".$this->fields['MatchID']."')";
		$result = $sql->db_Query($q);
		$nbr_teams = mysql_numrows($result);

		if ($nbr_teams != 0)
		{
			// Update scores ELO and TS
			for($i=1;$i<=$nbr_teams-1;$i++)
			{
				for($j=($i+1);$j<=$nbr_teams;$j++)
				{
					$output .= "Team $i vs. Team $j<br />";

					switch($ladder->getField('Type'))
					{
						case "One Player Ladder":
						case "Team Ladder":
						$q = "SELECT ".TBL_MATCHS.".*, "
						.TBL_SCORES.".*, "
						.TBL_PLAYERS.".*"
						." FROM ".TBL_MATCHS.", "
						.TBL_SCORES.", "
						.TBL_PLAYERS
						." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
						." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
						." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
						." AND (".TBL_SCORES.".Player_MatchTeam = '$i')";
						$resultA = $sql->db_Query($q);

						$q = "SELECT ".TBL_MATCHS.".*, "
						.TBL_SCORES.".*, "
						.TBL_PLAYERS.".*"
						." FROM ".TBL_MATCHS.", "
						.TBL_SCORES.", "
						.TBL_PLAYERS
						." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
						." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
						." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
						." AND (".TBL_SCORES.".Player_MatchTeam = '$j')";
						$resultB = $sql->db_Query($q);

						$NbrPlayersTeamA = mysql_numrows($resultA);
						$teamA_Rank= mysql_result($resultA,0, TBL_SCORES.".Player_Rank");
						$teamA_ELO=0;
						$teamA_TS_mu=0;
						$teamA_TS_sigma2=0;
						for ($k=0;$k<$NbrPlayersTeamA;$k++)
						{
							$teamA_ELO += mysql_result($resultA,$k, TBL_PLAYERS.".ELORanking");
							$teamA_TS_mu += mysql_result($resultA,$k, TBL_PLAYERS.".TS_mu");
							$teamA_TS_sigma2 += pow(mysql_result($resultA,$k, TBL_PLAYERS.".TS_sigma"),2);
						}
						$teamA_TS_sigma = sqrt($teamA_TS_sigma2);
						$output .= "Team $i ELO: $teamA_ELO, rank: $teamA_Rank<br />";
						$output .= "Team $i TS: mu = $teamA_TS_mu, sigma= $teamA_TS_sigma<br />";


						$NbrPlayersTeamB = mysql_numrows($resultB);
						$teamB_Rank= mysql_result($resultB,0, TBL_SCORES.".Player_Rank");
						$teamB_ELO=0;
						$teamB_TS_mu=0;
						$teamB_TS_sigma2=0;
						for ($k=0;$k<$NbrPlayersTeamB;$k++)
						{
							$teamB_ELO += mysql_result($resultB,$k, TBL_PLAYERS.".ELORanking");
							$teamB_TS_mu += mysql_result($resultB,$k, TBL_PLAYERS.".TS_mu");
							$teamB_TS_sigma2 += pow(mysql_result($resultB,$k, TBL_PLAYERS.".TS_sigma"),2);
						}
						$teamB_TS_sigma = sqrt($teamB_TS_sigma2);
						$output .= "Team $j ELO: $teamB_ELO, rank: $teamB_Rank<br />";
						$output .= "Team $j TS: mu = $teamB_TS_mu, sigma= $teamB_TS_sigma<br />";
						break;
						case "ClanWar":
						$q = "SELECT ".TBL_MATCHS.".*, "
						.TBL_SCORES.".*, "
						.TBL_TEAMS.".*"
						." FROM ".TBL_MATCHS.", "
						.TBL_SCORES.", "
						.TBL_TEAMS
						." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
						." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
						." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
						." AND (".TBL_SCORES.".Player_MatchTeam = '$i')";
						$resultA = $sql->db_Query($q);

						$q = "SELECT ".TBL_MATCHS.".*, "
						.TBL_SCORES.".*, "
						.TBL_TEAMS.".*"
						." FROM ".TBL_MATCHS.", "
						.TBL_SCORES.", "
						.TBL_TEAMS
						." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
						." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
						." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
						." AND (".TBL_SCORES.".Player_MatchTeam = '$j')";
						$resultB = $sql->db_Query($q);

						$NbrPlayersTeamA = mysql_numrows($resultA);
						$teamA_Rank= mysql_result($resultA,0, TBL_SCORES.".Player_Rank");
						$teamA_ELO=0;
						$teamA_TS_mu=0;
						$teamA_TS_sigma2=0;
						for ($k=0;$k<$NbrPlayersTeamA;$k++)
						{
							$teamA_ELO += mysql_result($resultA,$k, TBL_TEAMS.".ELORanking");
							$teamA_TS_mu += mysql_result($resultA,$k, TBL_TEAMS.".TS_mu");
							$teamA_TS_sigma2 += pow(mysql_result($resultA,$k, TBL_TEAMS.".TS_sigma"),2);
						}
						$teamA_TS_sigma = sqrt($teamA_TS_sigma2);
						$output .= "Team $i ELO: $teamA_ELO, rank: $teamA_Rank<br />";
						$output .= "Team $i TS: mu = $teamA_TS_mu, sigma= $teamA_TS_sigma<br />";


						$NbrPlayersTeamB = mysql_numrows($resultB);
						$teamB_Rank= mysql_result($resultB,0, TBL_SCORES.".Player_Rank");
						$teamB_ELO=0;
						$teamB_TS_mu=0;
						$teamB_TS_sigma2=0;
						for ($k=0;$k<$NbrPlayersTeamB;$k++)
						{
							$teamB_ELO += mysql_result($resultB,$k, TBL_TEAMS.".ELORanking");
							$teamB_TS_mu += mysql_result($resultB,$k, TBL_TEAMS.".TS_mu");
							$teamB_TS_sigma2 += pow(mysql_result($resultB,$k, TBL_TEAMS.".TS_sigma"),2);
						}
						$teamB_TS_sigma = sqrt($teamB_TS_sigma2);
						$output .= "Team $j ELO: $teamB_ELO, rank: $teamB_Rank<br />";
						$output .= "Team $j TS: mu = $teamB_TS_mu, sigma= $teamB_TS_sigma<br />";
						break;
						default:
					}

					$teamA_win = 0;
					$teamA_loss = 0;
					$teamA_draw = 0;
					$teamB_win = 0;
					$teamB_loss = 0;
					$teamB_draw = 0;
					// Wins/Losses/Draws
					if($teamA_Rank < $teamB_Rank)
					{
						$teamA_win = 1;
						$teamB_loss = 1;
					}
					else if ($teamA_Rank > $teamB_Rank)
					{
						$teamA_loss = 1;
						$teamB_win = 1;
					}
					else
					{
						$teamA_draw = 1;
						$teamB_draw = 1;
					}
					$teamA_Points = $teamA_win*$ladder->getField('PointsPerWin') + $teamA_draw*$ladder->getField('PointsPerDraw') + $teamA_loss*$ladder->getField('PointsPerLoss');
					$teamB_Points = $teamB_win*$ladder->getField('PointsPerWin') + $teamB_draw*$ladder->getField('PointsPerDraw') + $teamB_loss*$ladder->getField('PointsPerLoss');
					$output .= "Team A: $teamA_Points, $teamA_win, $teamA_draw, $teamA_loss, <br />";
					$output .= "Team B: $teamB_Points, $teamB_win, $teamB_draw, $teamB_loss, <br />";

					// New ELO ------------------------------------------
					$M=min($NbrPlayersTeamA,$NbrPlayersTeamB)*$ladder->getField('ELO_M');      // Span
					$K=$ladder->getField('ELO_K');	// Max adjustment per game
					$deltaELO = ELO($M, $K, $teamA_ELO, $teamB_ELO, $teamA_Rank, $teamB_Rank);
					$output .= "deltaELO: $deltaELO<br />";

					// New TrueSkill ------------------------------------------
					$beta=$ladder->getField('TS_beta');          // beta
					$epsilon=$ladder->getField('TS_epsilon');    // draw probability
					$update = Trueskill_update($epsilon,$beta, $teamA_TS_mu, $teamA_TS_sigma, $teamA_Rank, $teamB_TS_mu, $teamB_TS_sigma, $teamB_Rank);

					$teamA_deltaTS_mu = $update[0];
					$teamA_deltaTS_sigma = $update[1];
					$teamB_deltaTS_mu = $update[2];
					$teamB_deltaTS_sigma = $update[3];
					$output .= "Team $i TS: delta mu = $teamA_deltaTS_mu, delta sigma= $teamA_deltaTS_sigma<br />";
					$output .= "Team $j TS: delta mu = $teamB_deltaTS_mu, delta sigma= $teamB_deltaTS_sigma<br />";

					// Update Scores ------------------------------------------
					for ($k=0;$k<$NbrPlayersTeamA;$k++)
					{
						$scoreELO = mysql_result($resultA,$k, TBL_SCORES.".Player_deltaELO");
						$scoreTS_mu = mysql_result($resultA,$k, TBL_SCORES.".Player_deltaTS_mu");
						$scoreTS_sigma = mysql_result($resultA,$k, TBL_SCORES.".Player_deltaTS_sigma");
						$scoreWin = mysql_result($resultA,$k, TBL_SCORES.".Player_Win");
						$scoreDraw = mysql_result($resultA,$k, TBL_SCORES.".Player_Draw");
						$scoreLoss = mysql_result($resultA,$k, TBL_SCORES.".Player_Loss");
						$scorePoints = mysql_result($resultA,$k, TBL_SCORES.".Player_Points");

						$scoreELO += $deltaELO/$NbrPlayersTeamA;
						$scoreTS_mu += $teamA_deltaTS_mu/$NbrPlayersTeamA;
						$scoreTS_sigma *= $teamA_deltaTS_sigma;
						$scoreWin += $teamA_win;
						$scoreDraw += $teamA_draw;
						$scoreLoss += $teamA_loss;
						$scorePoints += $teamA_Points;

						switch($ladder->getField('Type'))
						{
							case "One Player Ladder":
							case "Team Ladder":
							$pid = mysql_result($resultA,$k, TBL_PLAYERS.".PlayerID");
							$q = "UPDATE ".TBL_SCORES
							." SET Player_deltaELO = '".floatToSQL($scoreELO)."',"
							."     Player_deltaTS_mu = '".floatToSQL($scoreTS_mu)."',"
							."     Player_deltaTS_sigma = '".floatToSQL($scoreTS_sigma)."',"
							."     Player_Win = $scoreWin,"
							."     Player_Draw = $scoreDraw,"
							."     Player_Loss = $scoreLoss,"
							."     Player_Points = $scorePoints"
							." WHERE (MatchID = '".$this->fields['MatchID']."')"
							."   AND (Player = '$pid')";
							break;
							case "ClanWar":
							$pid = mysql_result($resultA,$k, TBL_TEAMS.".TeamID");
							$q = "UPDATE ".TBL_SCORES
							." SET Player_deltaELO = '".floatToSQL($scoreELO)."',"
							."     Player_deltaTS_mu = '".floatToSQL($scoreTS_mu)."',"
							."     Player_deltaTS_sigma = '".floatToSQL($scoreTS_sigma)."',"
							."     Player_Win = $scoreWin,"
							."     Player_Draw = $scoreDraw,"
							."     Player_Loss = $scoreLoss,"
							."     Player_Points = $scorePoints"
							." WHERE (MatchID = '".$this->fields['MatchID']."')"
							."   AND (Team = '$pid')";
							break;
							default:
						}
						$result = $sql->db_Query($q);
						$output .= "team A, Player $pid query: $q<br />";
					}
					for ($k=0;$k<$NbrPlayersTeamB;$k++)
					{
						$scoreELO = mysql_result($resultB,$k, TBL_SCORES.".Player_deltaELO");
						$scoreTS_mu = mysql_result($resultB,$k, TBL_SCORES.".Player_deltaTS_mu");
						$scoreTS_sigma = mysql_result($resultB,$k, TBL_SCORES.".Player_deltaTS_sigma");
						$scoreWin = mysql_result($resultB,$k, TBL_SCORES.".Player_Win");
						$scoreDraw = mysql_result($resultB,$k, TBL_SCORES.".Player_Draw");
						$scoreLoss = mysql_result($resultB,$k, TBL_SCORES.".Player_Loss");
						$scorePoints = mysql_result($resultB,$k, TBL_SCORES.".Player_Points");

						$scoreELO -= $deltaELO/$NbrPlayersTeamB;
						$scoreTS_mu += $teamB_deltaTS_mu/$NbrPlayersTeamB;
						$scoreTS_sigma *= $teamB_deltaTS_sigma;
						$scoreWin += $teamB_win;
						$scoreDraw += $teamB_draw;
						$scoreLoss += $teamB_loss;
						$scorePoints += $teamB_Points;

						switch($ladder->getField('Type'))
						{
							case "One Player Ladder":
							case "Team Ladder":
							$pid = mysql_result($resultB,$k, TBL_PLAYERS.".PlayerID");
							$q = "UPDATE ".TBL_SCORES
							." SET Player_deltaELO = '".floatToSQL($scoreELO)."',"
							."     Player_deltaTS_mu = '".floatToSQL($scoreTS_mu)."',"
							."     Player_deltaTS_sigma = '".floatToSQL($scoreTS_sigma)."',"
							."     Player_Win = $scoreWin,"
							."     Player_Draw = $scoreDraw,"
							."     Player_Loss = $scoreLoss,"
							."     Player_Points = $scorePoints"
							." WHERE (MatchID = '".$this->fields['MatchID']."')"
							."  AND (Player = '$pid')";
							break;
							case "ClanWar":
							$tid = mysql_result($resultB,$k, TBL_TEAMS.".TeamID");
							$q = "UPDATE ".TBL_SCORES
							." SET Player_deltaELO = '".floatToSQL($scoreELO)."',"
							."     Player_deltaTS_mu = '".floatToSQL($scoreTS_mu)."',"
							."     Player_deltaTS_sigma = '".floatToSQL($scoreTS_sigma)."',"
							."     Player_Win = $scoreWin,"
							."     Player_Draw = $scoreDraw,"
							."     Player_Loss = $scoreLoss,"
							."     Player_Points = $scorePoints"
							." WHERE (MatchID = '".$this->fields['MatchID']."')"
							." AND (Team = '$tid')";
							break;
							default:
						}
						$result = $sql->db_Query($q);
					}
				}
			}
			$output .= '<br />';

			// Update scores score against
			switch($ladder->getField('Type'))
			{
				case "One Player Ladder":
				case "Team Ladder":
				$q =
				"SELECT ".TBL_SCORES.".*, "
				.TBL_PLAYERS.".*"
				." FROM ".TBL_SCORES.", "
				.TBL_PLAYERS
				." WHERE (".TBL_SCORES.".MatchID = '".$this->fields['MatchID']."')"
				."   AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
				break;
				case "ClanWar":
				$q =
				"SELECT ".TBL_SCORES.".*, "
				.TBL_TEAMS.".*"
				." FROM ".TBL_SCORES.", "
				.TBL_TEAMS
				." WHERE (".TBL_SCORES.".MatchID = '".$this->fields['MatchID']."')"
				."   AND (".TBL_SCORES.".Team = ".TBL_TEAMS.".TeamID)";
				break;
				default:
			}

			$result = $sql->db_Query($q);
			$nbr_players = mysql_numrows($result);
			for($i=0;$i<$nbr_players;$i++)
			{
				switch($ladder->getField('Type'))
				{
					case "One Player Ladder":
					case "Team Ladder":
					$pid= mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
					break;
					case "ClanWar":
					$pid= mysql_result($result,$i, TBL_TEAMS.".TeamID");
					break;
					default:
				}
				$scoreid= mysql_result($result,$i, TBL_SCORES.".ScoreID");
				$prank= mysql_result($result,$i, TBL_SCORES.".Player_Rank");
				$pteam= mysql_result($result,$i, TBL_SCORES.".Player_MatchTeam");
				$pOppScore = 0;
				$pnbrOpps = 0;

				for($j=0;$j<$nbr_players;$j++)
				{
					$opprank= mysql_result($result,$j, TBL_SCORES.".Player_Rank");
					$oppteam= mysql_result($result,$j, TBL_SCORES.".Player_MatchTeam");
					$oppscore= mysql_result($result,$j, TBL_SCORES.".Player_Score");

					if ($pteam != $oppteam)
					{
						$pOppScore += $oppscore;
						$pnbrOpps ++;
					}
				}
				$pOppScore /= $pnbrOpps;

				switch($ladder->getField('Type'))
				{
					case "One Player Ladder":
					case "Team Ladder":
					$q_1 = "UPDATE ".TBL_SCORES
					." SET Player_ScoreAgainst = $pOppScore"
					." WHERE (MatchID = '".$this->fields['MatchID']."')"
					." AND (Player = '$pid')";
					break;
					case "ClanWar":
					$q_1 = "UPDATE ".TBL_SCORES
					." SET Player_ScoreAgainst = $pOppScore"
					." WHERE (MatchID = '".$this->fields['MatchID']."')"
					." AND (Team = '$pid')";
					break;
					default:
				}

				$result_1 = $sql->db_Query($q_1);
			}
			$output .= '<br />';
			//echo $output;
			//exit;
		}
	}

	function match_players_update()
	{
		global $sql;

		// Update Teams with scores
		$tdeltaELO         = array();
		$tdeltaTS_mu       = array();
		$tdeltaTS_sigma    = array();
		$tdeltaGamesPlayed = array();
		$tdeltaWins        = array();
		$tdeltaDraws       = array();
		$tdeltaLosses      = array();
		$tdeltaScore       = array();
		$tdeltaOppScore    = array();
		$tdeltaPoints      = array();
		$tnbrPlayers       = array();

		$q = "SELECT DISTINCT ".TBL_PLAYERS.".Team"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." AND (".TBL_PLAYERS.".Team > 0)";
		$result_Teams = $sql->db_Query($q);

		$numTeams = mysql_numrows($result_Teams);
		for($team=0;$team<$numTeams;$team++)
		{
			$tid = mysql_result($result_Teams,$team, TBL_PLAYERS.".Team");

			$tdeltaELO[$tid] = 0;
			$tdeltaTS_mu[$tid] = 0;
			$tdeltaTS_sigma[$tid] = 0;
			$tdeltaGamesPlayed[$tid] = 0;
			$tdeltaWins[$tid] = 0;
			$tdeltaDraws[$tid] = 0;
			$tdeltaLosses[$tid] = 0;
			$tdeltaScore[$tid] = 0;
			$tdeltaOppScore[$tid] = 0;
			$tdeltaPoints[$tid] = 0;
			$tnbrPlayers[$tid] = 0;
		}

		// Update Players with scores
		$q = "SELECT ".TBL_MATCHS.".*, "
		.TBL_SCORES.".*, "
		.TBL_PLAYERS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_USERS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
		$result = $sql->db_Query($q);
		$numPlayers = mysql_numrows($result);
		for($i=0;$i < $numPlayers;$i++)
		{
			$time_reported = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
			$mStatus       = mysql_result($result,$i, TBL_MATCHS.".Status");

			$pid           = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
			$puid          = mysql_result($result,$i, TBL_USERS.".user_id");
			$pName         = mysql_result($result,$i, TBL_USERS.".user_name");
			$pteam         = mysql_result($result,$i, TBL_PLAYERS.".Team");
			$pELO          = mysql_result($result,$i, TBL_PLAYERS.".ELORanking");
			$pTS_mu        = mysql_result($result,$i, TBL_PLAYERS.".TS_mu");
			$pTS_sigma     = mysql_result($result,$i, TBL_PLAYERS.".TS_sigma");
			$pGamesPlayed  = mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
			$pWins         = mysql_result($result,$i, TBL_PLAYERS.".Win");
			$pDraws        = mysql_result($result,$i, TBL_PLAYERS.".Draw");
			$pLosses       = mysql_result($result,$i, TBL_PLAYERS.".Loss");
			$pStreak       = mysql_result($result,$i, TBL_PLAYERS.".Streak");
			$pStreak_Best  = mysql_result($result,$i, TBL_PLAYERS.".Streak_Best");
			$pStreak_Worst = mysql_result($result,$i, TBL_PLAYERS.".Streak_Worst");
			$pScore        = mysql_result($result,$i, TBL_PLAYERS.".Score");
			$pOppScore     = mysql_result($result,$i, TBL_PLAYERS.".ScoreAgainst");
			$pPoints       = mysql_result($result,$i, TBL_PLAYERS.".Points");

			$scoreid           = mysql_result($result,$i, TBL_SCORES.".ScoreID");
			$pdeltaELO         = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
			$pdeltaTS_mu       = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
			$pdeltaTS_sigma    = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
			$pdeltaGamesPlayed = 1;
			$pdeltaWins        = mysql_result($result,$i, TBL_SCORES.".Player_Win");
			$pdeltaDraws       = mysql_result($result,$i, TBL_SCORES.".Player_Draw");
			$pdeltaLosses      = mysql_result($result,$i, TBL_SCORES.".Player_Loss");
			$pdeltaScore       = mysql_result($result,$i, TBL_SCORES.".Player_Score");
			$pdeltaOppScore    = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");
			$pdeltaPoints      = mysql_result($result,$i, TBL_SCORES.".Player_Points");

			$pELO         += $pdeltaELO;
			$pTS_mu       += $pdeltaTS_mu;
			$pTS_sigma    *= $pdeltaTS_sigma;
			$pGamesPlayed += $pdeltaGamesPlayed;
			$pWins        += $pdeltaWins;
			$pDraws       += $pdeltaDraws;
			$pLosses      += $pdeltaLosses;
			$pScore       += $pdeltaScore;
			$pOppScore    += $pdeltaOppScore;
			$pPoints      += $pdeltaPoints;

			if ($pteam != 0)
			{
				$tdeltaELO[$pteam]         += $pdeltaELO;
				$tdeltaTS_mu[$pteam]       += $pdeltaTS_mu;
				$tdeltaTS_sigma[$pteam]    += $pdeltaTS_sigma;
				$tdeltaGamesPlayed[$pteam] += 1;
				$tdeltaWins[$pteam]        += $pdeltaWins;
				$tdeltaDraws[$pteam]       += $pdeltaDraws;
				$tdeltaLosses[$pteam]      += $pdeltaLosses;
				$tdeltaScore[$pteam]       += $pdeltaScore;
				$tdeltaOppScore[$pteam]    += $pdeltaOppScore;
				$tdeltaPoints[$pteam]      += $pdeltaPoints;
				$tnbrPlayers[$pteam]       += 1;
			}

			$output .= "Player: $pName - $pid, new ELO: $pELO<br />";
			$output .= "Games played: $pGamesPlayed<br>";
			$output .= "Match id: ".$this->fields['MatchID']."<br>";

			$gain = mysql_result($result,$i, TBL_SCORES.".Player_Win") - mysql_result($result,$i, TBL_SCORES.".Player_Loss");
			if ($gain * $pStreak > 0)
			{
				// same sign
				$pStreak += $gain;
			}
			else
			{
				// opposite sign
				$pStreak = $gain;
			}

			if ($pStreak > $pStreak_Best) $pStreak_Best = $pStreak;
			if ($pStreak < $pStreak_Worst) $pStreak_Worst = $pStreak;

			if ($pStreak == 5)
			{
				// Award: player wins 5 games in a row
				$q4 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
				VALUES ($pid,'PlayerStreak5',$time_reported)";
				$result4 = $sql->db_Query($q4);
			}
			if ($pStreak == 10)
			{
				// Award: player wins 10 games in a row
				$q4 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
				VALUES ($pid,'PlayerStreak10',$time_reported)";
				$result4 = $sql->db_Query($q4);
			}
			if ($pStreak == 25)
			{
				// Award: player wins 25 games in a row
				$q4 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
				VALUES ($pid,'PlayerStreak25',$time_reported)";
				$result4 = $sql->db_Query($q4);
			}

			// Update database.
			// Reset rank delta after a match.
			$q_3 = "UPDATE ".TBL_PLAYERS
			." SET ELORanking = '".floatToSQL($pELO)."',"
			."     TS_mu = '".floatToSQL($pTS_mu)."',"
			."     TS_sigma = '".floatToSQL($pTS_sigma)."',"
			."     GamesPlayed = $pGamesPlayed,"
			."     Loss = $pLosses,"
			."     Win = $pWins,"
			."     Draw = $pDraws,"
			."     Score = $pScore,"
			."     ScoreAgainst = $pOppScore,"
			."     Points = $pPoints,"
			."     Streak = $pStreak,"
			."     Streak_Best = $pStreak_Best,"
			."     Streak_Worst = $pStreak_Worst,"
			."     RankDelta = 0"
			." WHERE (PlayerID = '$pid')";
			$result_3 = $sql->db_Query($q_3);
		}

		$q = "SELECT DISTINCT ".TBL_PLAYERS.".Team, "
		.TBL_TEAMS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_TEAMS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." AND (".TBL_TEAMS.".TeamID = ".TBL_PLAYERS.".Team)"
		." AND (".TBL_PLAYERS.".Team > 0)";
		$result_Teams = $sql->db_Query($q);

		$numTeams = mysql_numrows($result_Teams);
		for($team=0;$team<$numTeams;$team++)
		{
			$tid = mysql_result($result_Teams,$team, TBL_PLAYERS.".Team");

			$tPoints      = mysql_result($result_Teams,$team, TBL_TEAMS.".Points");
			$tELO         = mysql_result($result_Teams,$team, TBL_TEAMS.".ELORanking");
			$tTS_mu       = mysql_result($result_Teams,$team, TBL_TEAMS.".TS_mu");
			$tTS_sigma    = mysql_result($result_Teams,$team, TBL_TEAMS.".TS_sigma");
			$tGamesPlayed = mysql_result($result_Teams,$team, TBL_TEAMS.".GamesPlayed");
			$tWins        = mysql_result($result_Teams,$team, TBL_TEAMS.".Win");
			$tDraws       = mysql_result($result_Teams,$team, TBL_TEAMS.".Draw");
			$tLosses      = mysql_result($result_Teams,$team, TBL_TEAMS.".Loss");
			$tScore       = mysql_result($result_Teams,$team, TBL_TEAMS.".Score");
			$tOppScore    = mysql_result($result_Teams,$team, TBL_TEAMS.".ScoreAgainst");

			$tdeltaELO[$tid]         /= $tnbrPlayers[$tid];
			$tdeltaTS_mu[$tid]       /= $tnbrPlayers[$tid];
			$tdeltaTS_sigma[$tid]    /= $tnbrPlayers[$tid];
			$tdeltaGamesPlayed[$tid] /= $tnbrPlayers[$tid];
			$tdeltaWins[$tid]        /= $tnbrPlayers[$tid];
			$tdeltaDraws[$tid]       /= $tnbrPlayers[$tid];
			$tdeltaLosses[$tid]      /= $tnbrPlayers[$tid];
			$tdeltaScore[$tid]       /= $tnbrPlayers[$tid];
			$tdeltaOppScore[$tid]    /= $tnbrPlayers[$tid];
			$tdeltaPoints[$tid]      /= $tnbrPlayers[$tid];

			$tELO         += $tdeltaELO[$tid];
			$tTS_mu       += $tdeltaTS_mu[$tid];
			$tTS_sigma    *= $tdeltaTS_sigma[$tid];
			$tGamesPlayed += $tdeltaGamesPlayed[$tid];
			$tWins        += $tdeltaWins[$tid];
			$tDraws       += $tdeltaDraws[$tid];
			$tLosses      += $tdeltaLosses[$tid];
			$tScore       += $tdeltaScore[$tid];
			$tOppScore    += $tdeltaOppScore[$tid];
			$tPoints      += $tdeltaPoints[$tid];

			$output .= "Team: $tid, new ELO: $tdeltaELO[$tid]<br />";
			$output .= "Team: $tid, Games played: $tdeltaGamesPlayed[$tid]<br>";
			$output .= "Match id: ".$this->fields['MatchID']."<br>";

			$q_update = "UPDATE ".TBL_TEAMS
			." SET ELORanking = '".floatToSQL($tELO)."',"
			."     TS_mu = '".floatToSQL($tTS_mu)."',"
			."     TS_sigma = '".floatToSQL($tTS_sigma)."',"
			."     GamesPlayed = $tGamesPlayed,"
			."     Loss = $tLosses,"
			."     Win = $tWins,"
			."     Draw = $tDraws,"
			."     Score = $tScore,"
			."     ScoreAgainst = $tOppScore,"
			."     Points = $tPoints,"
			."     RankDelta = 0"
			." WHERE (TeamID = '$tid')";
			$result_update = $sql->db_Query($q_update);
			$output .= "<br>$q";
		}

		$q = "UPDATE ".TBL_MATCHS." SET Status = 'active' WHERE (MatchID = '".$this->fields['MatchID']."')";
		$result = $sql->db_Query($q);

		//var_dump($output);
		//exit;
	}

	function match_teams_update()
	{
		global $sql;

		// Get ladder info
		$q = "SELECT ".TBL_LADDERS.".*, "
		.TBL_MATCHS.".*"
		." FROM ".TBL_LADDERS.", "
		.TBL_MATCHS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		."   AND (".TBL_LADDERS.".LadderID = ".TBL_MATCHS.".Ladder)";
		$result = $sql->db_Query($q);
		$ladder_id = mysql_result($result,0 , TBL_LADDERS.".LadderID");
		$ladder = new Ladder($ladder_id);

		// Update Teams with scores
		$q = "SELECT ".TBL_MATCHS.".*, "
		.TBL_SCORES.".*, "
		.TBL_CLANS.".*, "
		.TBL_TEAMS.".*, "
		.TBL_DIVISIONS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_CLANS.", "
		.TBL_TEAMS.", "
		.TBL_DIVISIONS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
		." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
		." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)";

		$result = $sql->db_Query($q);
		$numTeams = mysql_numrows($result);
		for($i=0;$i < $numTeams;$i++)
		{
			$time_reported = mysql_result($result,$i, TBL_MATCHS.".TimeReported");

			$tid           = mysql_result($result,$i, TBL_TEAMS.".TeamID");
			$tclanid       = mysql_result($result,$i, TBL_CLANS.".ClanID");
			$tName         = mysql_result($result,$i, TBL_CLANS.".Name");
			$tELO          = mysql_result($result,$i, TBL_TEAMS.".ELORanking");
			$tTS_mu        = mysql_result($result,$i, TBL_TEAMS.".TS_mu");
			$tTS_sigma     = mysql_result($result,$i, TBL_TEAMS.".TS_sigma");
			$tGamesPlayed  = mysql_result($result,$i, TBL_TEAMS.".GamesPlayed");
			$tWins         = mysql_result($result,$i, TBL_TEAMS.".Win");
			$tDraws        = mysql_result($result,$i, TBL_TEAMS.".Draw");
			$tLosses       = mysql_result($result,$i, TBL_TEAMS.".Loss");
			$tScore        = mysql_result($result,$i, TBL_TEAMS.".Score");
			$tOppScore     = mysql_result($result,$i, TBL_TEAMS.".ScoreAgainst");
			$tPoints       = mysql_result($result,$i, TBL_TEAMS.".Points");
			$tStreak       = mysql_result($result,$i, TBL_TEAMS.".Streak");
			$tStreak_Best  = mysql_result($result,$i, TBL_TEAMS.".Streak_Best");
			$tStreak_Worst = mysql_result($result,$i, TBL_TEAMS.".Streak_Worst");

			$tdeltaELO         = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
			$tdeltaTS_mu       = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
			$tdeltaTS_sigma    = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
			$tdeltaGamesPlayed = 1;
			$tdeltaWins        = mysql_result($result,$i, TBL_SCORES.".Player_Win");
			$tdeltaDraws       = mysql_result($result,$i, TBL_SCORES.".Player_Draw");
			$tdeltaLosses      = mysql_result($result,$i, TBL_SCORES.".Player_Loss");
			$tdeltaPoints      = mysql_result($result,$i, TBL_SCORES.".Player_Points");
			$tdeltaScore       = mysql_result($result,$i, TBL_SCORES.".Player_Score");
			$tdeltaOppScore    = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");

			$tELO         += $tdeltaELO;
			$tTS_mu       += $tdeltaTS_mu;
			$tTS_sigma    *= $tdeltaTS_sigma;
			$tGamesPlayed += $tdeltaGamesPlayed;
			$tWins        += $tdeltaWins;
			$tDraws       += $tdeltaDraws;
			$tLosses      += $tdeltaLosses;
			$tScore       += $tdeltaScore;
			$tOppScore    += $tdeltaOppScore;
			$tPoints      += $tdeltaPoints;

			$output .= "Team: $tName - $tid, new ELO: $tELO<br />";
			$output .= "Games played: $tGamesPlayed<br>";
			$output .= "Match id: ".$this->fields['MatchID']."<br>";

			$gain = mysql_result($result,$i, TBL_SCORES.".Player_Win") - mysql_result($result,$i, TBL_SCORES.".Player_Loss");
			if ($gain * $tStreak > 0)
			{
				// same sign
				$tStreak += $gain;
			}
			else
			{
				// opposite sign
				$tStreak = $gain;
			}

			if ($tStreak > $tStreak_Best) $tStreak_Best = $tStreak;
			if ($tStreak < $tStreak_Worst) $tStreak_Worst = $tStreak;

			if ($tStreak == 5)
			{
				// Award: team wins 5 games in a row
				$q4 = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
				VALUES ($tid,'TeamStreak5',$time_reported)";
				$result4 = $sql->db_Query($q4);
			}
			if ($tStreak == 10)
			{
				// Award: team wins 10 games in a row
				$q4 = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
				VALUES ($tid,'TeamStreak10',$time_reported)";
				$result4 = $sql->db_Query($q4);
			}
			if ($tStreak == 25)
			{
				// Award: player wins 25 games in a row
				$q4 = "INSERT INTO ".TBL_AWARDS."(Team,Type,timestamp)
				VALUES ($tid,'TeamStreak25',$time_reported)";
				$result4 = $sql->db_Query($q4);
			}

			// Update database.
			// Reset rank delta after a match.
			$q_3 = "UPDATE ".TBL_TEAMS
			." SET ELORanking = '".floatToSQL($tELO)."',"
			."     TS_mu = '".floatToSQL($tTS_mu)."',"
			."     TS_sigma = '".floatToSQL($tTS_sigma)."',"
			."     GamesPlayed = $tGamesPlayed,"
			."     Loss = $tLosses,"
			."     Win = $tWins,"
			."     Draw = $tDraws,"
			."     Score = $tScore,"
			."     ScoreAgainst = $tOppScore,"
			."     Points = $tPoints,"
			."     Streak = $tStreak,"
			."     Streak_Best = $tStreak_Best,"
			."     Streak_Worst = $tStreak_Worst,"
			."     RankDelta = 0"
			." WHERE (TeamID = '$tid')";
			$result_3 = $sql->db_Query($q_3);
		}

		$q = "UPDATE ".TBL_MATCHS." SET Status = 'active' WHERE (MatchID = '".$this->fields['MatchID']."')";
		$result = $sql->db_Query($q);

		//echo $output;
		//exit;
	}

	function deleteMatchScores($ladder_id)
	{
		global $sql;

		/* Ladder Info */
		$ladder = new Ladder($ladder_id);

		switch($ladder->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			$this->deletePlayersMatchScores();
			break;
			case "ClanWar":
			$this->deleteTeamsMatchScores();
			break;
			default:
		}

		$q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
		$result = $sql->db_Query($q);
	}

	function deletePlayersMatchScores()
	{
		global $sql;


		// Update Teams with scores
		$tdeltaELO         = array();
		$tdeltaTS_mu       = array();
		$tdeltaTS_sigma    = array();
		$tdeltaGamesPlayed = array();
		$tdeltaWins        = array();
		$tdeltaDraws       = array();
		$tdeltaLosses      = array();
		$tdeltaScore       = array();
		$tdeltaOppScore    = array();
		$tdeltaPoints      = array();
		$tnbrPlayers       = array();

		$q = "SELECT DISTINCT ".TBL_PLAYERS.".Team"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." AND (".TBL_PLAYERS.".Team > 0)";
		$result_Teams = $sql->db_Query($q);

		$numTeams = mysql_numrows($result_Teams);
		for($team=0;$team<$numTeams;$team++)
		{
			$tid = mysql_result($result_Teams,$team, TBL_PLAYERS.".Team");

			$tdeltaELO[$tid]         = 0;
			$tdeltaTS_mu[$tid]       = 0;
			$tdeltaTS_sigma[$tid]    = 0;
			$tdeltaGamesPlayed[$tid] = 0;
			$tdeltaWins[$tid]        = 0;
			$tdeltaDraws[$tid]       = 0;
			$tdeltaLosses[$tid]      = 0;
			$tdeltaScore[$tid]       = 0;
			$tdeltaOppScore[$tid]    = 0;
			$tdeltaPoints[$tid]      = 0;
			$tnbrPlayers[$tid]       = 0;
		}

		// Update Players with scores
		$q = "SELECT ".TBL_MATCHS.".*, "
		.TBL_SCORES.".*, "
		.TBL_PLAYERS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_USERS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
		$result = $sql->db_Query($q);
		$numPlayers = mysql_numrows($result);
		for($i=0;$i < $numPlayers;$i++)
		{
			$mStatus = mysql_result($result,$i, TBL_MATCHS.".Status");

			$pid           = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
			$puid          = mysql_result($result,$i, TBL_USERS.".user_id");
			$pname         = mysql_result($result,$i, TBL_USERS.".user_name");
			$pteam         = mysql_result($result,$i, TBL_PLAYERS.".Team");
			$pELO          = mysql_result($result,$i, TBL_PLAYERS.".ELORanking");
			$pTS_mu        = mysql_result($result,$i, TBL_PLAYERS.".TS_mu");
			$pTS_sigma     = mysql_result($result,$i, TBL_PLAYERS.".TS_sigma");
			$pGamesPlayed  = mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
			$pWins         = mysql_result($result,$i, TBL_PLAYERS.".Win");
			$pDraws        = mysql_result($result,$i, TBL_PLAYERS.".Draw");
			$pLosses       = mysql_result($result,$i, TBL_PLAYERS.".Loss");
			$pStreak       = mysql_result($result,$i, TBL_PLAYERS.".Streak");
			$pStreak_Best  = mysql_result($result,$i, TBL_PLAYERS.".Streak_Best");
			$pStreak_Worst = mysql_result($result,$i, TBL_PLAYERS.".Streak_Worst");
			$pScore        = mysql_result($result,$i, TBL_PLAYERS.".Score");
			$pOppScore     = mysql_result($result,$i, TBL_PLAYERS.".ScoreAgainst");
			$pPoints       = mysql_result($result,$i, TBL_PLAYERS.".Points");

			$scoreid           = mysql_result($result,$i, TBL_SCORES.".ScoreID");
			$pdeltaELO         = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
			$pdeltaTS_mu       = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
			$pdeltaTS_sigma    = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
			$pdeltaGamesPlayed = 1;
			$pdeltaWins        = mysql_result($result,$i, TBL_SCORES.".Player_Win");
			$pdeltaDraws       = mysql_result($result,$i, TBL_SCORES.".Player_Draw");
			$pdeltaLosses      = mysql_result($result,$i, TBL_SCORES.".Player_Loss");
			$pdeltaScore       = mysql_result($result,$i, TBL_SCORES.".Player_Score");
			$pdeltaOppScore    = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");
			$pdeltaPoints      = mysql_result($result,$i, TBL_SCORES.".Player_Points");

			$pELO         -= $pdeltaELO;
			$pTS_mu       -= $pdeltaTS_mu;
			$pTS_sigma    /= $pdeltaTS_sigma;
			$pGamesPlayed -= $pdeltaGamesPlayed;
			$pWins        -= $pdeltaWins;
			$pDraws       -= $pdeltaDraws;
			$pLosses      -= $pdeltaLosses;
			$pScore       -= $pdeltaScore;
			$pOppScore    -= $pdeltaOppScore;
			$pPoints      -= $pdeltaPoints;

			$output .= "<br>pid:$pid, pname $pname, pscore: $pdeltaScore, pelo: $pELO, pteam: $pteam<br />";

			if ($pteam != 0)
			{
				$tdeltaELO[$pteam]         += $pdeltaELO;
				$tdeltaTS_mu[$pteam]       += $pdeltaTS_mu;
				$tdeltaTS_sigma[$pteam]    += $pdeltaTS_sigma;
				$tdeltaGamesPlayed[$pteam] += 1;
				$tdeltaWins[$pteam]        += $pdeltaWins;
				$tdeltaDraws[$pteam]       += $pdeltaDraws;
				$tdeltaLosses[$pteam]      += $pdeltaLosses;
				$tdeltaScore[$pteam]       += $pdeltaScore;
				$tdeltaOppScore[$pteam]    += $pdeltaOppScore;
				$tdeltaPoints[$pteam]      += $pdeltaPoints;
				$tnbrPlayers[$pteam]       += 1;
			}

			if ($mStatus == 'active')
			{
				$q = "UPDATE ".TBL_PLAYERS
				." SET ELORanking = '".floatToSQL($pELO)."',"
				."     TS_mu = '".floatToSQL($pTS_mu)."',"
				."     TS_sigma = '".floatToSQL($pTS_sigma)."',"
				."     GamesPlayed = $pGamesPlayed,"
				."     Loss = $pLosses,"
				."     Win = $pWins,"
				."     Draw = $pDraws,"
				."     Score = $pScore,"
				."     ScoreAgainst = $pOppScore,"
				."     Points = $pPoints"
				." WHERE (PlayerID = '$pid')";
				$result2 = $sql->db_Query($q);
				$output .= "$q<br>";
			}

			// fmarc- Can not reverse "streak" information here :(

			// Delete Score
			$q = "DELETE FROM ".TBL_SCORES." WHERE (ScoreID = '$scoreid')";
			$result2 = $sql->db_Query($q);
			$output .= "$q<br>";

		}
		$q = "SELECT DISTINCT ".TBL_PLAYERS.".Team, "
		.TBL_TEAMS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_PLAYERS.", "
		.TBL_TEAMS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
		." AND (".TBL_TEAMS.".TeamID = ".TBL_PLAYERS.".Team)"
		." AND (".TBL_PLAYERS.".Team > 0)";
		$result_Teams = $sql->db_Query($q);

		$numTeams = mysql_numrows($result_Teams);
		for($team=0;$team<$numTeams;$team++)
		{
			$tid = mysql_result($result_Teams,$team, TBL_PLAYERS.".Team");

			$tPoints      = mysql_result($result_Teams,$team, TBL_TEAMS.".Points");
			$tELO         = mysql_result($result_Teams,$team, TBL_TEAMS.".ELORanking");
			$tTS_mu       = mysql_result($result_Teams,$team, TBL_TEAMS.".TS_mu");
			$tTS_sigma    = mysql_result($result_Teams,$team, TBL_TEAMS.".TS_sigma");
			$tGamesPlayed = mysql_result($result_Teams,$team, TBL_TEAMS.".GamesPlayed");
			$tWins        = mysql_result($result_Teams,$team, TBL_TEAMS.".Win");
			$tDraws       = mysql_result($result_Teams,$team, TBL_TEAMS.".Draw");
			$tLosses      = mysql_result($result_Teams,$team, TBL_TEAMS.".Loss");
			$tScore       = mysql_result($result_Teams,$team, TBL_TEAMS.".Score");
			$tOppScore    = mysql_result($result_Teams,$team, TBL_TEAMS.".ScoreAgainst");

			$tdeltaELO[$tid]         /= $tnbrPlayers[$tid];
			$tdeltaTS_mu[$tid]       /= $tnbrPlayers[$tid];
			$tdeltaTS_sigma[$tid]    /= $tnbrPlayers[$tid];
			$tdeltaGamesPlayed[$tid] /= $tnbrPlayers[$tid];
			$tdeltaWins[$tid]        /= $tnbrPlayers[$tid];
			$tdeltaDraws[$tid]       /= $tnbrPlayers[$tid];
			$tdeltaLosses[$tid]      /= $tnbrPlayers[$tid];
			$tdeltaScore[$tid]       /= $tnbrPlayers[$tid];
			$tdeltaOppScore[$tid]    /= $tnbrPlayers[$tid];
			$tdeltaPoints[$tid]      /= $tnbrPlayers[$tid];

			$tPoints      -= $tdeltaPoints[$tid];
			$tELO         -= $tdeltaELO[$tid];
			$tTS_mu       -= $tdeltaTS_mu[$tid];
			$tTS_sigma    /= $tdeltaTS_sigma[$tid];
			$tGamesPlayed -= $tdeltaGamesPlayed[$tid];
			$tWins        -= $tdeltaWins[$tid];
			$tDraws       -= $tdeltaDraws[$tid];
			$tLosses      -= $tdeltaLosses[$tid];
			$tScore       -= $tdeltaScore[$tid];
			$tOppScore    -= $tdeltaOppScore[$tid];

			$output .= "Team: $tid, new ELO: $tdeltaELO[$tid]<br />";
			$output .= "Team: $tid, Games played: $tdeltaGamesPlayed[$tid]<br>";
			$output .= "Match id: ".$this->fields['MatchID']."<br>";

			if ($mStatus == 'active')
			{
				$q_update = "UPDATE ".TBL_TEAMS
				." SET ELORanking = '".floatToSQL($tELO)."',"
				."     TS_mu = '".floatToSQL($tTS_mu)."',"
				."     TS_sigma = '".floatToSQL($tTS_sigma)."',"
				."     GamesPlayed = $tGamesPlayed,"
				."     Loss = $tLosses,"
				."     Win = $tWins,"
				."     Draw = $tDraws,"
				."     Score = $tScore,"
				."     ScoreAgainst = $tOppScore,"
				."     Points = $tPoints,"
				."     RankDelta = 0"
				." WHERE (TeamID = '$tid')";
				$result_update = $sql->db_Query($q_update);
				$output .= "<br>$q";
			}
		}

		// The match itself is kept in database, only the scores are deleted.
		$q = "UPDATE ".TBL_MATCHS." SET Status = 'deleted' WHERE (MatchID = '".$this->fields['MatchID']."')";
		$result = $sql->db_Query($q);

		//echo $output;
		//exit;
	}

	function deleteTeamsMatchScores()
	{
		global $sql;

		// Update Players with scores
		$q = "SELECT ".TBL_MATCHS.".*, "
		.TBL_SCORES.".*, "
		.TBL_TEAMS.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_TEAMS
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)";
		$result = $sql->db_Query($q);
		$numTeams = mysql_numrows($result);

		for($i=0;$i < $numTeams;$i++)
		{
			$mStatus = mysql_result($result,$i, TBL_MATCHS.".Status");

			$tid          = mysql_result($result,$i, TBL_TEAMS.".TeamID");
			$tELO         = mysql_result($result,$i, TBL_TEAMS.".ELORanking");
			$tTS_mu       = mysql_result($result,$i, TBL_TEAMS.".TS_mu");
			$tTS_sigma    = mysql_result($result,$i, TBL_TEAMS.".TS_sigma");
			$tGamesPlayed = mysql_result($result,$i, TBL_TEAMS.".GamesPlayed");
			$tWins        = mysql_result($result,$i, TBL_TEAMS.".Win");
			$tDraws       = mysql_result($result,$i, TBL_TEAMS.".Draw");
			$tLosses      = mysql_result($result,$i, TBL_TEAMS.".Loss");
			$tScore       = mysql_result($result,$i, TBL_TEAMS.".Score");
			$tOppScore    = mysql_result($result,$i, TBL_TEAMS.".ScoreAgainst");
			$tPoints      = mysql_result($result,$i, TBL_TEAMS.".Points");

			$scoreid           = mysql_result($result,$i, TBL_SCORES.".ScoreID");
			$tdeltaELO         = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
			$tdeltaTS_mu       = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
			$tdeltaTS_sigma    = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
			$tdeltaGamesPlayed = 1;
			$tdeltaWins        = mysql_result($result,$i, TBL_SCORES.".Player_Win");
			$tdeltaDraws       = mysql_result($result,$i, TBL_SCORES.".Player_Draw");
			$tdeltaLosses      = mysql_result($result,$i, TBL_SCORES.".Player_Loss");
			$tdeltaScore       = mysql_result($result,$i, TBL_SCORES.".Player_Score");
			$tdeltaOppScore    = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");
			$tdeltaPoints      = mysql_result($result,$i, TBL_SCORES.".Player_Points");

			$tELO           -= $tdeltaELO;
			$tTS_mu         -= $tdeltaTS_mu;
			$tTS_sigma      /= $tdeltaTS_sigma;
			$tGamesPlayed   -= $tdeltaGamesPlayed;
			$tWins          -= $tdeltaWins;
			$tDraws         -= $tdeltaDraws;
			$tLosses        -= $tdeltaLosses;
			$tScore         -= $tdeltaScore;
			$tOppScore      -= $tdeltaOppScore;
			$tPoints        -= $tdeltaPoints;

			$output .= "<br>tid: $tid, tscore: $tsScore, telo: $tELO<br />";

			if ($mStatus == 'active')
			{
				$q_update = "UPDATE ".TBL_TEAMS
				." SET ELORanking = '".floatToSQL($tELO)."',"
				."     TS_mu = '".floatToSQL($tTS_mu)."',"
				."     TS_sigma = '".floatToSQL($tTS_sigma)."',"
				."     GamesPlayed = $tGamesPlayed,"
				."     Loss = $tLosses,"
				."     Win = $tWins,"
				."     Draw = $tDraws,"
				."     Score = $tScore,"
				."     ScoreAgainst = $tOppScore,"
				."     Points = $tPoints,"
				."     RankDelta = 0"
				." WHERE (TeamID = '$tid')";
				$result_update = $sql->db_Query($q_update);
				$output .= "<br>$q";
			}

			// fmarc- Can not reverse "streak" information here :(

			// Delete Score
			$q = "DELETE FROM ".TBL_SCORES." WHERE (ScoreID = '$scoreid')";
			$result2 = $sql->db_Query($q);
			$output .= "$q<br>";
		}

		// The match itself is kept in database, only the scores are deleted.
		$q = "UPDATE ".TBL_MATCHS." SET Status = 'deleted' WHERE (MatchID = '".$this->fields['MatchID']."')";
		$result = $sql->db_Query($q);

		//echo $output;
		//exit;
	}

	function displayMatchInfo($type = 0)
	{
		global $time;
		global $sql;
		global $pref;

		$string ='';
		// Get info about the match
		$q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
		.TBL_USERS.".*, "
		.TBL_LADDERS.".*, "
		.TBL_GAMES.".*"
		." FROM ".TBL_MATCHS.", "
		.TBL_SCORES.", "
		.TBL_USERS.", "
		.TBL_LADDERS.", "
		.TBL_GAMES
		." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
		." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
		." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
		." AND (".TBL_MATCHS.".Ladder = ".TBL_LADDERS.".LadderID)"
		." AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)";

		$result = $sql->db_Query($q);
		$numMatchs = mysql_numrows($result);
		//dbg: var_dump($q);
		if ($numMatchs > 0)
		{
			$mReportedBy  = mysql_result($result, 0, TBL_USERS.".user_id");
			$mReportedByNickName  = mysql_result($result, 0, TBL_USERS.".user_name");
			$mLaddergame = mysql_result($result, 0, TBL_GAMES.".Name");
			$mLaddergameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
			$mStatus  = mysql_result($result,0, TBL_MATCHS.".Status");
			$mTime  = mysql_result($result, 0, TBL_MATCHS.".TimeReported");
			$mTime_local = $mTime + TIMEOFFSET;
			$date = date("d M Y, h:i A",$mTime_local);
			$mTimeScheduled  = mysql_result($result, 0, TBL_MATCHS.".TimeScheduled");
			$mTimeScheduled_local = $mTimeScheduled + TIMEOFFSET;
			$dateScheduled = date("d M Y, h:i A",$mTimeScheduled_local);
			$ladder_id  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
			$ladder = new Ladder($ladder_id);

			// Calculate number of players and teams for the match
			$q = "SELECT DISTINCT ".TBL_SCORES.".Player_MatchTeam"
			." FROM ".TBL_SCORES
			." WHERE (".TBL_SCORES.".MatchID = '".$this->fields['MatchID']."')";
			$result = $sql->db_Query($q);
			$nbr_teams = mysql_numrows($result);

			// Check if the match has several ranks
			$q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
			.TBL_SCORES.".Player_Rank"
			." FROM ".TBL_MATCHS.", "
			.TBL_SCORES
			." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
			." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
			$result = $sql->db_Query($q);
			$numRanks = mysql_numrows($result);
			if ($numRanks > 0)
			{
				$can_approve = 0;
				$can_report = 0;
				$can_schedule = 0;
				$userclass = 0;

				switch($ladder->getField('Type'))
				{
					case "One Player Ladder":
					case "Team Ladder":
					// Get the match reporter's match team
					$reporter_matchteam = 0;
					$q_Reporter = "SELECT DISTINCT ".TBL_SCORES.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_PLAYERS.", "
					.TBL_USERS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
					." AND (".TBL_PLAYERS.".User = '$mReportedBy')";
					$result_Reporter = $sql->db_Query($q_Reporter);
					$numRows = mysql_numrows($result_Reporter);
					if ($numRows>0)
					{
						$reporter_matchteam = mysql_result($result_Reporter,0, TBL_SCORES.".Player_MatchTeam");
					}

					// Is the user an opponent of the reporter?
					$q_Opps = "SELECT DISTINCT ".TBL_SCORES.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_PLAYERS.", "
					.TBL_USERS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
					." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
					." AND (".TBL_PLAYERS.".User = ".USERID.")";
					$result_Opps = $sql->db_Query($q_Opps);
					$numOpps = mysql_numrows($result_Opps);
					break;
					case "ClanWar":
					// Get the match reporter's match team
					$reporter_matchteam = 0;
					$q_Reporter = "SELECT DISTINCT ".TBL_SCORES.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_TEAMS.", "
					.TBL_PLAYERS.", "
					.TBL_USERS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
					." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
					." AND (".TBL_PLAYERS.".User = '$mReportedBy')";
					$result_Reporter = $sql->db_Query($q_Reporter);
					$numRows = mysql_numrows($result_Reporter);
					if ($numRows>0)
					{
						$reporter_matchteam = mysql_result($result_Reporter,0, TBL_SCORES.".Player_MatchTeam");
					}

					// Is the user an opponent of the reporter?
					$q_Opps = "SELECT DISTINCT ".TBL_SCORES.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_TEAMS.", "
					.TBL_PLAYERS.", "
					.TBL_USERS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
					." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
					." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
					." AND (".TBL_PLAYERS.".User = ".USERID.")";
					$result_Opps = $sql->db_Query($q_Opps);
					$numOpps = mysql_numrows($result_Opps);
					//dbg: echo "numOpps: $numOpps, mt: $reporter_matchteam<br>";
					break;
					default:
				}

				// Is the user a player in the match?
				switch($ladder->getField('Type'))
				{
					case "One Player Ladder":
					case "Team Ladder":
					$q_UserPlayers = "SELECT DISTINCT ".TBL_SCORES.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_PLAYERS.", "
					.TBL_USERS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
					." AND (".TBL_PLAYERS.".User = ".USERID.")";
					$result_UserPlayers = $sql->db_Query($q_UserPlayers);
					$numUserPlayers = mysql_numrows($result_UserPlayers);

					break;
					case "ClanWar":
					$q_UserPlayers = "SELECT DISTINCT ".TBL_SCORES.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_TEAMS.", "
					.TBL_PLAYERS.", "
					.TBL_USERS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
					." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
					." AND (".TBL_PLAYERS.".User = ".USERID.")";
					$result_UserPlayers = $sql->db_Query($q_UserPlayers);
					$numUserPlayers = mysql_numrows($result_UserPlayers);
					//dbg: echo "numUserPlayers: $numUserPlayers<br>";

					break;
					default:
				}

				$can_approve = 0;
				if (USERID==$ladder->getField('Owner'))
				{
					$userclass |= eb_UC_LADDER_OWNER;
					$can_approve = 1;
					$can_report = 1;
					$can_schedule = 1;
				}
				if ($numMods>0)
				{
					$userclass |= eb_UC_EB_MODERATOR;
					$can_approve = 1;
					$can_report = 1;
					$can_schedule = 1;
				}
				if (check_class($pref['eb_mod_class']))
				{
					$userclass |= eb_UC_EB_MODERATOR;
					$can_approve = 1;
					$can_report = 1;
					$can_schedule = 1;
				}
				if ($numOpps>0)
				{
					$userclass |= eb_UC_LADDER_PLAYER;
					$can_approve = 1;
				}
				if ($numUserPlayers > 0)
				{
					$can_report = 1;
				}
				if ($userclass < $ladder->getField('MatchesApproval')) $can_approve = 0;
				if ($ladder->getField('MatchesApproval') == eb_UC_NONE) $can_approve = 0;
				if ($mStatus != 'pending') $can_approve = 0;
				if ($mStatus != 'scheduled') $can_report = 0;

				$orderby_str = " ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";
				if($nbr_teams==2) $orderby_str = " ORDER BY ".TBL_SCORES.".Player_MatchTeam";

				switch($ladder->getField('Type'))
				{
					case "One Player Ladder":
					case "Team Ladder":
					$q = "SELECT ".TBL_MATCHS.".*, "
					.TBL_SCORES.".*, "
					.TBL_PLAYERS.".*, "
					.TBL_USERS.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_PLAYERS.", "
					.TBL_USERS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
					." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
					.$orderby_str;
					break;
					case "ClanWar":
					$q = "SELECT ".TBL_MATCHS.".*, "
					.TBL_SCORES.".*, "
					.TBL_CLANS.".*, "
					.TBL_TEAMS.".*, "
					.TBL_DIVISIONS.".*"
					." FROM ".TBL_MATCHS.", "
					.TBL_SCORES.", "
					.TBL_CLANS.", "
					.TBL_TEAMS.", "
					.TBL_DIVISIONS
					." WHERE (".TBL_MATCHS.".MatchID = '".$this->fields['MatchID']."')"
					." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
					." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
					." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
					." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
					.$orderby_str;
					break;
					default:
				}

				$result = $sql->db_Query($q);
				$numPlayers = mysql_numrows($result);
				$pname = '';
				$string .= '<tr>';
				$scores = '';

				if (($type & eb_MATCH_NOLADDERINFO) == 0)
				{
					$string .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$this->fields['MatchID'].'" title="'.$mLaddergame.'">';
					$string .= '<img '.getActivityGameIconResize($mLaddergameicon).'/>';
					$string .= '</a></td>';
				}

				$string .= '<td>';
				$matchteam = 0;
				for ($index = 0; $index < $numPlayers; $index++)
				{
					switch($ladder->getField('Type'))
					{
						case "One Player Ladder":
						case "Team Ladder":
						$puid  = mysql_result($result,$index , TBL_USERS.".user_id");
						$pname  = mysql_result($result,$index , TBL_USERS.".user_name");
						$pavatar = mysql_result($result,$index, TBL_USERS.".user_image");
						$pteam  = mysql_result($result,$index , TBL_PLAYERS.".Team");
						break;
						case "ClanWar":
						$pname  = mysql_result($result,$index, TBL_CLANS.".Name");
						$pavatar = mysql_result($result,$index, TBL_CLANS.".Image");
						$pteam  = mysql_result($result,$index, TBL_TEAMS.".TeamID");
						break;
						default:
					}
					list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);
					$prank  = mysql_result($result,$index , TBL_SCORES.".Player_Rank");
					$pmatchteam  = mysql_result($result,$index , TBL_SCORES.".Player_MatchTeam");
					$pscore = mysql_result($result,$index , TBL_SCORES.".Player_Score");
					$pfaction  = mysql_result($result,$index, TBL_SCORES.".Faction");

					$pfactionIcon = "";
					//if (($pfaction!=0)&&($type!=0))
					if ($pfaction!=0)
					{
						$q_Factions = "SELECT ".TBL_FACTIONS.".*"
						." FROM ".TBL_FACTIONS
						." WHERE (".TBL_FACTIONS.".FactionID = '$pfaction')";
						$result_Factions = $sql->db_Query($q_Factions);
						$numFactions = mysql_numrows($result_Factions);
						if ($numFactions>0)
						{
							$fIcon = mysql_result($result_Factions,0 , TBL_FACTIONS.".Icon");
							$fName = mysql_result($result_Factions,0 , TBL_FACTIONS.".Name");

							$pfactionIcon = ' <img '.getFactionIconResize($fIcon).' title="'.$fName.'" style="vertical-align:middle"/>';
						}
					}

					/* takes too long
					$image = '';
					if ($pref['eb_avatar_enable_playersstandings'] == 1)
					{
					switch($ladder->getField('Type'))
					{
					case "One Player Ladder":
					case "Team Ladder":
					if($pavatar)
					{
					$image = '<img '.getAvatarResize(avatar($pavatar)).' style="vertical-align:middle"/>';
					} else if ($pref['eb_avatar_default_image'] != ''){
					$image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_image'], 'avatars')).' style="vertical-align:middle"/>';
					}
					break;
					case "ClanWar":
					if($pavatar)
					{
					$image = '<img '.getAvatarResize(getImagePath($pavatar, 'team_avatars')).' style="vertical-align:middle"/>';
					} else if ($pref['eb_avatar_default_image'] != ''){
					$image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_team_image'], 'team_avatars')).' style="vertical-align:middle"/>';
					}
					break;
					default:
					}
					}
					*/

					if($index>0)
					{
						$scores .= "-".$pscore;
						if ($pmatchteam == $matchteam)
						{
							$string .= ' &amp; ';
						}
						else
						{
							if (($type & eb_MATCH_SCHEDULED) != 0)
							{
								$str = ' vs. ';

							}
							else if ($prank == $rank)
							{
								$str = ' '.EB_MATCH_L2.' ';
							}
							else if ($prank > $rank)
							{
								$str = ' '.EB_MATCH_L3.' ';
							}
							else
							{
								$str = ' '.EB_MATCH_L14.' ';
							}

							$string .= $str;
							$matchteam = $pmatchteam;
							$rank = $prank;
						}
					}
					else
					{
						$rank = $prank;
						$matchteam = $pmatchteam;
						$scores .= $pscore;
					}
					/*
					echo "rank: $rank, prank: $prank<br>";
					echo "mt: $matchteam, pmt $pmatchteam<br>";
					*/

					$string .= $pfactionIcon.' ';

					switch($ladder->getField('Type'))
					{
						case "One Player Ladder":
						case "Team Ladder":
						$string .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a>';
						break;
						case "ClanWar":
						$string .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$pclanid.'">'.$pclan.'</a>';
						break;
						default:
					}

				}

				//score here
				if (($ladder->getField('AllowScore') == TRUE)
				&&(($type & eb_MATCH_SCHEDULED) == 0))
				{
					$string .= ' ('.$scores.') ';
				}

				if (($type & eb_MATCH_NOLADDERINFO) == 0)
				{
					$string .= ' '.EB_MATCH_L12.' <a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.$ladder->getField('Name').'</a>';
				}
				if ($can_approve == 1)
				{
					$string .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$this->fields['MatchID'].'"><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/></a>';
				}
				else
				{
					if((($type & eb_MATCH_SCHEDULED) == 0)||($can_schedule == 1))
					{
						$string .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$this->fields['MatchID'].'"><img src="'.e_PLUGIN.'ebattles/images/magnify.png" alt="'.EB_MATCH_L5.'" title="'.EB_MATCH_L5.'" style="vertical-align:text-top;"/></a>';
					}
				}

				if (($type & eb_MATCH_SCHEDULED) == 0)
				{
					$string .= ' <div class="smalltext">';
					$string .= EB_MATCH_L6.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$mReportedBy.'">'.$mReportedByNickName.'</a> ';

					if (($time-$mTime) < INT_MINUTE )
					{
						$string .= EB_MATCH_L7;
					}
					else if (($time-$mTime) < INT_DAY )
					{
						$string .= get_formatted_timediff($mTime, $time).'&nbsp;'.EB_MATCH_L8;
					}
					else
					{
						$string .= EB_MATCH_L9.'&nbsp;'.$date.'.';
					}
					$nbr_comments = getCommentTotal("ebmatches", $this->fields['MatchID']);
					$string .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$this->fields['MatchID'].'" title="'.EB_MATCH_L4.'&nbsp;'.$this->fields['MatchID'].'">'.$nbr_comments.'&nbsp;';
					$string .= ($nbr_comments > 1) ? EB_MATCH_L10 : EB_MATCH_L11;
					$string .= '</a>';
					$string .= '</div></td>';
				}
				else
				{
					$string .= ' <div class="smalltext">';
					$string .= EB_MATCH_L16.'&nbsp;';
					$string .= EB_MATCH_L17.'&nbsp;'.$dateScheduled.'.';

					$string .= '</div></td>';
				}

				if ($can_report == 1)
				{
					$string .= '<td>';
					$string .= '<form action="'.e_PLUGIN.'ebattles/matchreport.php?LadderID='.$ladder_id.'&amp;matchid='.$this->fields['MatchID'].'" method="post">';
					$text .= '<div>';
					$text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
					$text .= '</div>';
					$string .= '<div>';
					$string .= ebImageTextButton('matchscheduledreport', 'page_white_edit.png', '', 'simple', '', EB_LADDER_L57);
					$string .= '</div>';
					$string .= '</form>';
					$string .= '</td>';
				}

				$string .= '</tr>';
			}
		}
		return $string;
	}

	function add_media($submitter, $media_path, $media_type)
	{
		global $sql;

		$q = "INSERT INTO ".TBL_MEDIA."(MatchID,Submitter,Path,Type)
		VALUES ('".$this->fields['MatchID']."','$submitter','$media_path','$media_type')";
		$sql->db_Query($q);

		//dbg: echo "$this->fields['MatchID'], $submitter, $media_path, $media_type";
	}
}

function delete_media($media)
{
	global $sql;

	$q = "DELETE FROM ".TBL_MEDIA." WHERE (MediaID = '$media')";
	$result = $sql->db_Query($q);
}
?>
