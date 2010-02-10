<?php
// functions for matchs score updates.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/ELO.php');
require_once(e_PLUGIN.'ebattles/include/trueskill.php');

function match_scores_update($match_id)
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
    $eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
    $eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");
    $eTS_beta = mysql_result($result,0 , TBL_EVENTS.".TS_beta");
    $eTS_epsilon = mysql_result($result,0 , TBL_EVENTS.".TS_epsilon");
    $ePointPerWin = mysql_result($result,0 , TBL_EVENTS.".PointsPerWin");
    $ePointPerDraw = mysql_result($result,0 , TBL_EVENTS.".PointsPerDraw");
    $ePointPerLoss = mysql_result($result,0 , TBL_EVENTS.".PointsPerLoss");

    // Initialize scores ELO/TrueSkill
    $deltaELO = 0;
    $deltaTS_mu = 0;
    $deltaTS_sigma = 1;
    $q = "UPDATE ".TBL_SCORES
    ." SET Player_deltaELO = '$deltaELO',"
    ."     Player_deltaTS_mu = '".floatToSQL($deltaTS_mu)."',"
    ."     Player_deltaTS_sigma = '".floatToSQL($deltaTS_sigma)."'"
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
                    ."     Player_deltaTS_mu = '".floatToSQL($scoreTS_mu)."',"
                    ."     Player_deltaTS_sigma = '".floatToSQL($scoreTS_sigma)."'"
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
                    ."     Player_deltaTS_mu = '".floatToSQL($scoreTS_mu)."',"
                    ."     Player_deltaTS_sigma = '".floatToSQL($scoreTS_sigma)."'"
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
    }
}

function match_players_update($match_id)
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
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");

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

        $output .= "Player: $pName - $pid, new ELO:$pELO<br />";
        $output .= "Games played: $pGamesPlayed<br>";
        $output .= "Match id: $match_id<br>";

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
        
        if ($etype == "Team Ladder")
        {
            // Reset rank delta after a match.
            $q_3 = "UPDATE ".TBL_TEAMS." SET RankDelta = 0 WHERE (TeamID = '$pteam')";
            $result_3 = $sql->db_Query($q_3);
        }
    }

    $q = "UPDATE ".TBL_MATCHS." SET Status = 'active' WHERE (MatchID = '$match_id')";
    $result = $sql->db_Query($q);

    //echo $output;
    //exit;
}

function deleteMatchScores($match_id)
{
    global $sql;

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
    $numPlayers = mysql_numrows($result);
    for($i=0;$i < $numPlayers;$i++)
    {
        $pID= mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
        $puid= mysql_result($result,$i, TBL_USERS.".user_id");
        $pname= mysql_result($result,$i, TBL_USERS.".user_name");
        $pELO= mysql_result($result,$i, TBL_PLAYERS.".ELORanking");
        $pTS_mu= mysql_result($result,$i, TBL_PLAYERS.".TS_mu");
        $pTS_sigma= mysql_result($result,$i, TBL_PLAYERS.".TS_sigma");
        $pGamesPlayed= mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
        $pWins= mysql_result($result,$i, TBL_PLAYERS.".Win");
        $pDraws= mysql_result($result,$i, TBL_PLAYERS.".Draw");
        $pLosses= mysql_result($result,$i, TBL_PLAYERS.".Loss");
        $pScore= mysql_result($result,$i, TBL_PLAYERS.".Score");
        $pOppScore= mysql_result($result,$i, TBL_PLAYERS.".ScoreAgainst");
        $pPoints= mysql_result($result,$i, TBL_PLAYERS.".Points");
        $scoreid = mysql_result($result,$i, TBL_SCORES.".ScoreID");
        $pdeltaELO = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
        $pdeltaTS_mu = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
        $pdeltaTS_sigma = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
        $psWins = mysql_result($result,$i, TBL_SCORES.".Player_Win");
        $psDraws = mysql_result($result,$i, TBL_SCORES.".Player_Draw");
        $psLosses = mysql_result($result,$i, TBL_SCORES.".Player_Loss");
        $psScore = mysql_result($result,$i, TBL_SCORES.".Player_Score");
        $psOppScore = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");
        $psPoints = mysql_result($result,$i, TBL_SCORES.".Player_Points");
        $mStatus = mysql_result($result,$i, TBL_MATCHS.".Status");

        $pELO -= $pdeltaELO;
        $pTS_mu -= $pdeltaTS_mu;
        $pTS_sigma /= $pdeltaTS_sigma;
        $pWins -= $psWins;
        $pDraws -= $psDraws;
        $pLosses -= $psLosses;
        $pScore -= $psScore;
        $pOppScore -= $psOppScore;
        $pPoints -= $psPoints;
        $pGamesPlayed -= 1;

        $output .= "<br>pid:$pid, pname $pname, pscore: $psScore, pelo: $pELO<br />";

        if ($mStatus == 'active')
        {
            $q = "UPDATE ".TBL_PLAYERS
            ." SET ELORanking = $pELO,"
            ."     TS_mu = '".floatToSQL($pTS_mu)."',"
            ."     TS_sigma = '".floatToSQL($pTS_sigma)."',"
            ."     GamesPlayed = $pGamesPlayed,"
            ."     Loss = $pLosses,"
            ."     Win = $pWins,"
            ."     Draw = $pDraws,"
            ."     Score = $pScore,"
            ."     ScoreAgainst = $pOppScore,"
            ."     Points = $pPoints"
            ." WHERE (PlayerID = '$pID')";
            $result2 = $sql->db_Query($q);
            $output .= "<br>$q";
        }

        // fmarc- Can not reverse "streak" information here :(

        // Delete Score
        $q = "DELETE FROM ".TBL_SCORES." WHERE (ScoreID = '$scoreid')";
        $result2 = $sql->db_Query($q);
        $output .= "<br>$q";

    }
    // The match itself is kept in database, only the scores are deleted.
    $q = "UPDATE ".TBL_MATCHS." SET Status = 'deleted' WHERE (MatchID = '$match_id')";
    $result = $sql->db_Query($q);

    //echo $output;
    //exit;
}

function displayMatchInfo($match_id, $type = 0)
{
    global $time;
    global $sql;
    global $pref;

    $string ='';
    $q = "SELECT ".TBL_MATCHS.".*, "
    .TBL_USERS.".*, "
    .TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_USERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
    ." AND (".TBL_MATCHS.".Event = ".TBL_EVENTS.".EventID)"
    ." AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if ($num_rows>0)
    {
        $mReportedBy  = mysql_result($result, 0, TBL_USERS.".user_id");
        $mReportedByNickName  = mysql_result($result, 0, TBL_USERS.".user_name");
        $mEventID  = mysql_result($result, 0, TBL_EVENTS.".EventID");
        $mEventName  = mysql_result($result, 0, TBL_EVENTS.".Name");
        $mEventOwner  = mysql_result($result, 0, TBL_EVENTS.".Owner");
        $mEventgame = mysql_result($result, 0, TBL_GAMES.".Name");
        $mEventgameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
        $mEventType  = mysql_result($result, 0, TBL_EVENTS.".Type");
        $mEventAllowScore = mysql_result($result, 0, TBL_EVENTS.".AllowScore");
        $mEventMatchesApproval = mysql_result($result,0 , TBL_EVENTS.".MatchesApproval");
        $mStatus  = mysql_result($result,0, TBL_MATCHS.".Status");
        $mTime  = mysql_result($result, 0, TBL_MATCHS.".TimeReported");
        $mTime_local = $mTime + TIMEOFFSET;
        $date = date("d M Y, h:i A",$mTime_local);

        $q2 = "SELECT DISTINCT ".TBL_MATCHS.".*, "
        .TBL_SCORES.".Player_Rank"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
        $result2 = $sql->db_Query($q2);
        $numRanks = mysql_numrows($result2);
        if ($numRanks > 0)
        {
            $can_approve = 0;
            $userclass = 0;

            $reporter_matchteam = 0;
            $q_2 = "SELECT DISTINCT ".TBL_SCORES.".*"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES.", "
            .TBL_PLAYERS.", "
            .TBL_USERS
            ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
            ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
            ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
            ." AND (".TBL_PLAYERS.".User = '$mReportedBy')";
            $result_2 = $sql->db_Query($q_2);
            $numRows = mysql_numrows($result_2);
            if ($numRows>0)
            {
                $reporter_matchteam = mysql_result($result_2,0, TBL_SCORES.".Player_MatchTeam");
            }

            // Is the user an opponent of the reporter?
            $q_2 = "SELECT DISTINCT ".TBL_SCORES.".*"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES.", "
            .TBL_PLAYERS.", "
            .TBL_USERS
            ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
            ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
            ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
            ." AND (".TBL_SCORES.".Player_MatchTeam != '$reporter_matchteam')"
            ." AND (".TBL_PLAYERS.".User = ".USERID.")";
            $result_2 = $sql->db_Query($q_2);
            $numOpps = mysql_numrows($result_2);

            $can_approve = 0;
            if (USERID==$mEventOwner)
            {
                $userclass |= eb_UC_EVENT_OWNER;
                $can_approve = 1;
            }
            if ($numMods>0)
            {
                $userclass |= eb_UC_EB_MODERATOR;
                $can_approve = 1;
            }
            if (check_class($pref['eb_mod_class']))
            {
                $userclass |= eb_UC_EB_MODERATOR;
                $can_approve = 1;
            }
            if ($numOpps>0)
            {
                $userclass |= eb_UC_EVENT_PLAYER;
                $can_approve = 1;
            }
            if($userclass < $mEventMatchesApproval) $can_approve = 0;
            if($mEventMatchesApproval == eb_UC_NONE) $can_approve = 0;
            if ($mStatus == 'active') $can_approve = 0;

            $q2 = "SELECT ".TBL_MATCHS.".*, "
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
            ." ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";

            $result2 = $sql->db_Query($q2);
            $numPlayers = mysql_numrows($result2);
            $pname = '';
            $string = '<tr>';
            $scores = '';

            if ($type == 0)
            {
                $string .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$match_id.'" title="'.$mEventgame.'">';
                $string .= '<img '.getActivityGameIconResize($mEventgameicon).'/>';
                $string .= '</a></td>';
            }

            $string .= '<td>';
            $rank = 1;
            $matchteam = 0;
            for ($index = 0; $index < $numPlayers; $index++)
            {
                $puid  = mysql_result($result2,$index , TBL_USERS.".user_id");
                $pname  = mysql_result($result2,$index , TBL_USERS.".user_name");
                $prank  = mysql_result($result2,$index , TBL_SCORES.".Player_Rank");
                $pteam  = mysql_result($result2,$index , TBL_PLAYERS.".Team");
                $pmatchteam  = mysql_result($result2,$index , TBL_SCORES.".Player_MatchTeam");
                $pscore = mysql_result($result2,$index , TBL_SCORES.".Player_Score");
                list($pclan, $pclantag) = getClanName($pteam);

                if($index>0)
                {
                    if ($pmatchteam == $matchteam)
                    {
                        $string .= ' &amp; ';
                    }
                    else
                    {
                        if ($prank == $rank)
                        {
                            $str = ' '.EB_MATCH_L2.' ';
                        }
                        else
                        {
                            $str = ' '.EB_MATCH_L3.' ';
                        }
                        $scores .= "-".$pscore;
                        $string .= $str;
                        $matchteam++;
                    }
                }
                else
                {
                    $matchteam = $pmatchteam;
                    $scores .= $pscore;
                }

                $string .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a>';
            }

            //score here
            if ($mEventAllowScore == TRUE)
            {
                $string .= ' ('.$scores.') ';
            }

            if ($type == 0)
            {
                $string .= ' '.EB_MATCH_L12.' <a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$mEventID.'">'.$mEventName.'</a>';
            }
            if ($can_approve == 1)
            {
                $string .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$match_id.'"><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/></a>';
            }
            else
            {
                $string .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$match_id.'"><img src="'.e_PLUGIN.'ebattles/images/magnify.png" alt="'.EB_MATCH_L5.'" title="'.EB_MATCH_L5.'" style="vertical-align:text-top;"/></a>';
            }
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
            $nbr_comments = getCommentTotal("ebmatches", $match_id);
            $string .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$match_id.'" title="'.EB_MATCH_L4.'&nbsp;'.$match_id.'">'.$nbr_comments.'&nbsp;';
            $string .= ($nbr_comments > 1) ? EB_MATCH_L10 : EB_MATCH_L11;
            $string .= '</a>';
            $string .= '</div></td></tr>';
        }
    }
    return $string;
}
?>
