<?php
// functions for matchs score updates.
//___________________________________________________________________
require_once e_PLUGIN.'ebattles/include/ELO.php';
require_once e_PLUGIN.'ebattles/include/trueskill.php';
include_once(e_PLUGIN."ebattles/include/updatestats.php");
include_once(e_PLUGIN."ebattles/include/updateteamstats.php");

function match_scores_update($match_id, $update_stats)
{
    global $sql;

    // Get event info
    $q = "SELECT ".TBL_EVENTS.".*, "
    .TBL_MATCHS.".*"
    ." FROM ".TBL_EVENTS.", "
    .TBL_MATCHS
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ."   AND (".TBL_EVENTS.".EventID = ".TBL_MATCHS.".Event)";
    $result = $sql->db_Query($q);

    $event_id = mysql_result($result,0 , TBL_EVENTS.".EventID");
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
    $eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
    $eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");
    $eTS_beta = mysql_result($result,0 , TBL_EVENTS.".TS_beta");
    $eTS_epsilon = mysql_result($result,0 , TBL_EVENTS.".TS_epsilon");
    $ePointPerWin = mysql_result($result,0 , TBL_EVENTS.".PointsPerWin");
    $ePointPerDraw = mysql_result($result,0 , TBL_EVENTS.".PointsPerDraw");
    $ePointPerLoss = mysql_result($result,0 , TBL_EVENTS.".PointsPerLoss");
    $eAllowDraw = mysql_result($result,0 , TBL_EVENTS.".AllowDraw");
    $eAllowScore = mysql_result($result,0 , TBL_EVENTS.".AllowScore");

    // Initialize scores ELO/TrueSkill
    $deltaELO = 0;
    $deltaTS_mu = 0;
    $deltaTS_sigma = 1;
    $q = "UPDATE ".TBL_SCORES
    ." SET Player_deltaELO = $deltaELO,"
    ."     Player_deltaTS_mu = $deltaTS_mu,"
    ."     Player_deltaTS_sigma = $deltaTS_sigma"
    ." WHERE (MatchID = '$match_id')";
    $result = $sql->db_Query($q);

    // Calculate number of players and teams for the match
    $q = "SELECT DISTINCT ".TBL_SCORES.".Player_MatchTeam"
    ." FROM ".TBL_SCORES
    ." WHERE (".TBL_SCORES.".MatchID = '$match_id')";
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

                $q = "SELECT ".TBL_MATCHS.".*, "
                .TBL_SCORES.".*, "
                .TBL_PLAYERS.".*, "
                .TBL_USERS.".*"
                ." FROM ".TBL_MATCHS.", "
                .TBL_SCORES.", "
                .TBL_PLAYERS.", "
                .TBL_USERS
                ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
                ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
                ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
                ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
                ." AND (".TBL_SCORES.".Player_MatchTeam = '$i')";
                $resultA = $sql->db_Query($q);
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

                $q = "SELECT ".TBL_MATCHS.".*, "
                .TBL_SCORES.".*, "
                .TBL_PLAYERS.".*, "
                .TBL_USERS.".*"
                ." FROM ".TBL_MATCHS.", "
                .TBL_SCORES.", "
                .TBL_PLAYERS.", "
                .TBL_USERS
                ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
                ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
                ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
                ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
                ." AND (".TBL_SCORES.".Player_MatchTeam = '$j')";
                $resultB = $sql->db_Query($q);
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

                // New ELO ------------------------------------------
                $M=min($NbrPlayersTeamA,$NbrPlayersTeamB)*$eELO_M;      // Span
                $K=$eELO_K;	// Max adjustment per game
                $deltaELO = ELO($M, $K, $teamA_ELO, $teamB_ELO, $teamA_Rank, $teamB_Rank);
                $output .= "deltaELO: $deltaELO<br />";

                // New TrueSkill ------------------------------------------
                $beta=$eTS_beta;          // beta
                $epsilon=$eTS_epsilon;    // draw probability
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
                    $pid = mysql_result($resultA,$k, TBL_PLAYERS.".PlayerID");
                    $scoreELO += $deltaELO/$NbrPlayersTeamA;
                    $scoreTS_mu += $teamA_deltaTS_mu/$NbrPlayersTeamA;
                    $scoreTS_sigma *= $teamA_deltaTS_sigma;
                    $q = "UPDATE ".TBL_SCORES
                    ." SET Player_deltaELO = $scoreELO,"
                    ."     Player_deltaTS_mu = $scoreTS_mu,"
                    ."     Player_deltaTS_sigma = $scoreTS_sigma"
                    ." WHERE (MatchID = '$match_id')"
                    ."   AND (Player = '$pid')";
                    $result = $sql->db_Query($q);
                }
                for ($k=0;$k<$NbrPlayersTeamB;$k++)
                {
                    $scoreELO = mysql_result($resultB,$k, TBL_SCORES.".Player_deltaELO");
                    $scoreTS_mu = mysql_result($resultB,$k, TBL_SCORES.".Player_deltaTS_mu");
                    $scoreTS_sigma = mysql_result($resultB,$k, TBL_SCORES.".Player_deltaTS_sigma");
                    $pid = mysql_result($resultB,$k, TBL_PLAYERS.".PlayerID");
                    $scoreELO -= $deltaELO/$NbrPlayersTeamB;
                    $scoreTS_mu += $teamB_deltaTS_mu/$NbrPlayersTeamB;
                    $scoreTS_sigma *= $teamB_deltaTS_sigma;
                    $q = "UPDATE ".TBL_SCORES
                    ." SET Player_deltaELO = $scoreELO,"
                    ."     Player_deltaTS_mu = $scoreTS_mu,"
                    ."     Player_deltaTS_sigma = $scoreTS_sigma"
                    ." WHERE (MatchID = '$match_id')"
                    ." AND (Player = '$pid')";
                    $result = $sql->db_Query($q);
                }
            }
        }
        $output .= '<br />';

        // Update scores Wins, Draws, Losses, points, score against
        $q =
        "SELECT ".TBL_SCORES.".*, "
        .TBL_PLAYERS.".*"
        ." FROM ".TBL_SCORES.", "
        .TBL_PLAYERS
        ." WHERE (".TBL_SCORES.".MatchID = '$match_id')"
        ."   AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
        $result = $sql->db_Query($q);
        $nbr_players = mysql_numrows($result);
        for($i=0;$i<$nbr_players;$i++)
        {
            $scoreid= mysql_result($result,$i, TBL_SCORES.".ScoreID");
            $pid= mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
            $prank= mysql_result($result,$i, TBL_SCORES.".Player_Rank");
            $pteam= mysql_result($result,$i, TBL_SCORES.".Player_MatchTeam");
            $pwin = 0;
            $ploss = 0;
            $pdraw = 0;
            $pOppScore = 0;
            $pnbrOpps = 0;

            for($j=0;$j<$nbr_players;$j++)
            {
                $oppid= mysql_result($result,$j, TBL_PLAYERS.".PlayerID");
                $opprank= mysql_result($result,$j, TBL_SCORES.".Player_Rank");
                $oppteam= mysql_result($result,$j, TBL_SCORES.".Player_MatchTeam");
                $oppscore= mysql_result($result,$j, TBL_SCORES.".Player_Score");

                if ($pteam != $oppteam)
                {
                    $pOppScore += $oppscore;
                    $pnbrOpps ++;
                    if ($prank<$opprank)
                    {
                        $pwin++;
                    }
                    else if ($prank>$opprank)
                    {
                        $ploss++;
                    }
                    else
                    {
                        $pdraw++;
                    }
                }
            }
            $pOppScore /= $pnbrOpps;
            $q_1 = "UPDATE ".TBL_SCORES
            ." SET Player_Win = $pwin,"
            ."     Player_Draw = $pdraw,"
            ."     Player_Loss = $ploss,"
            ."     Player_Points = $pwin*$ePointPerWin + $pdraw*$ePointPerDraw + $ploss*$ePointPerLoss,"
            ."     Player_ScoreAgainst = $pOppScore"
            ." WHERE (MatchID = '$match_id')"
            ." AND (Player = '$pid')";
            $result_1 = $sql->db_Query($q_1);
        }
        $output .= '<br />';

        // Update Players with scores
        $q = "SELECT ".TBL_MATCHS.".*, "
        .TBL_SCORES.".*, "
        .TBL_PLAYERS.".*, "
        .TBL_USERS.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
        ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
        $result = $sql->db_Query($q);
        $num_players = mysql_numrows($result);
        for($i=0;$i<$num_players;$i++)
        {
            $time_reported = mysql_result($result,$i, TBL_MATCHS.".TimeReported");

            $pdeltaELO = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
            $pdeltaTS_mu = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
            $pdeltaTS_sigma = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
            $pid= mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
            $puid= mysql_result($result,$i, TBL_USERS.".user_id");
            $pName= mysql_result($result,$i, TBL_USERS.".user_name");
            $pteam= mysql_result($result,$i, TBL_PLAYERS.".Team");
            $pELO= mysql_result($result,$i, TBL_PLAYERS.".ELORanking");
            $pTS_mu= mysql_result($result,$i, TBL_PLAYERS.".TS_mu");
            $pTS_sigma= mysql_result($result,$i, TBL_PLAYERS.".TS_sigma");
            $pGamesPlayed= mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
            $pWins= mysql_result($result,$i, TBL_PLAYERS.".Win");
            $pDraws= mysql_result($result,$i, TBL_PLAYERS.".Draw");
            $pLosses= mysql_result($result,$i, TBL_PLAYERS.".Loss");
            $pStreak= mysql_result($result,$i, TBL_PLAYERS.".Streak");
            $pStreak_Best= mysql_result($result,$i, TBL_PLAYERS.".Streak_Best");
            $pStreak_Worst= mysql_result($result,$i, TBL_PLAYERS.".Streak_Worst");
            $pPoints= mysql_result($result,$i, TBL_PLAYERS.".Points");
            $pScore= mysql_result($result,$i, TBL_PLAYERS.".Score");
            $pOppScore= mysql_result($result,$i, TBL_PLAYERS.".ScoreAgainst");

            $pWins += mysql_result($result,$i, TBL_SCORES.".Player_Win");
            $pDraws += mysql_result($result,$i, TBL_SCORES.".Player_Draw");
            $pLosses += mysql_result($result,$i, TBL_SCORES.".Player_Loss");
            $pPoints += mysql_result($result,$i, TBL_SCORES.".Player_Points");
            $pScore += mysql_result($result,$i, TBL_SCORES.".Player_Score");
            $pOppScore += mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");

            $pELO += $pdeltaELO;
            $pTS_mu += $pdeltaTS_mu;
            $pTS_sigma *= $pdeltaTS_sigma;
            $pGamesPlayed += 1;

            $output .= "Player: $pName, new ELO:$pELO<br />";

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
            ." SET ELORanking = $pELO,"
            ."     TS_mu = $pTS_mu,"
            ."     TS_sigma = $pTS_sigma,"
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

            if ($etype == "Team Ladder")
            {
                // Reset rank delta after a match.
                $q_3 = "UPDATE ".TBL_TEAMS." SET RankDelta = 0 WHERE (TeamID = '$pteam')";
                $result_3 = $sql->db_Query($q_3);
            }
        }
        if ($update_stats) updateStats($event_id, $time_reported, FALSE);
        if ($update_stats && ($etype == "Team Ladder")) updateTeamStats($event_id, $time_reported, FALSE);
        //echo $output;
        //exit;
    }
}

?>
