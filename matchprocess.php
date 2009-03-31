<?php
/**
* MatchProcess.php
* Quick match report process
*
*/
require_once("../../class2.php");
require_once e_PLUGIN.'ebattles/include/main.php';
require_once e_PLUGIN.'ebattles/include/ELO.php';
require_once e_PLUGIN.'ebattles/include/trueskill.php';
global $sql;

if(isset($_POST['qrsubmitloss']))
{
    $event_id = $_POST['eventid'];
    $reported_by = $_POST['reported_by'];

    $time = GMT_time();

    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";

    $result = $sql->db_Query($q);
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
    $eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
    $eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");
    $eTS_beta = mysql_result($result,0 , TBL_EVENTS.".TS_beta");
    $eTS_epsilon = mysql_result($result,0 , TBL_EVENTS.".TS_epsilon");
    $ePointsPerWin = mysql_result($result,0 , TBL_EVENTS.".PointsPerWin");
    $ePointsPerLoss = mysql_result($result,0 , TBL_EVENTS.".PointsPerLoss");

    // Attention here, we use user_id, so there has to be 1 user for 1 player
    $plooserUser = $reported_by;
    $q = "SELECT *"
    ." FROM ".TBL_PLAYERS
    ." WHERE (Event = '$event_id')"
    ."   AND (User = '$plooserUser')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $plooserID = $row['PlayerID'];
    $plooserELO = $row['ELORanking'];
    $plooserTS_mu = $row['TS_mu'];
    $plooserTS_sigma = $row['TS_sigma'];
    $plooserGames = $row['GamesPlayed'];
    $plooserLosses = $row['Loss'];
    $plooserPoints = $row['Points'];
    $plooserScore = $row['Score'];
    $plooserStreak = $row['Streak'];
    $plooserStreak_Best = $row['Streak_Best'];
    $plooserStreak_Worst = $row['Streak_Worst'];
    $plooserTeam = $row['Team'];

    $pwinnerID = $_POST['Player'];
    $q = "SELECT *"
    ." FROM ".TBL_PLAYERS
    ." WHERE (Event = '$event_id')"
    ."   AND (PlayerID = '$pwinnerID')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $pwinnerUser = $row['User'];
    $pwinnerELO = $row['ELORanking'];
    $pwinnerTS_mu = $row['TS_mu'];
    $pwinnerTS_sigma = $row['TS_sigma'];
    $pwinnerGames = $row['GamesPlayed'];
    $pwinnerWins = $row['Win'];
    $pwinnerPoints = $row['Points'];
    $pwinnerScore = $row['Score'];
    $pwinnerStreak = $row['Streak'];
    $pwinnerStreak_Best = $row['Streak_Best'];
    $pwinnerStreak_Worst = $row['Streak_Worst'];
    $pwinnerTeam = $row['Team'];

    // New ELO ------------------------------------------
    $M=$eELO_M;       // Span
    $K=$eELO_K;	// Max adjustment per game
    $deltaELO = ELO($M, $K, $pwinnerELO, $plooserELO, 1, 2);
    $plooserELO -= $deltaELO;
    $pwinnerELO += $deltaELO;

    // New TrueSkill ------------------------------------------
    $beta=$eTS_beta;          // beta
    $epsilon=$eTS_epsilon;    // draw probability
    $update = Trueskill_update($epsilon,$beta, $pwinnerTS_mu, $pwinnerTS_sigma, 1, $plooserTS_mu, $plooserTS_sigma, 2);

    $winner_deltaTS_mu = $update[0];
    $winner_deltaTS_sigma = $update[1];
    $looser_deltaTS_mu = $update[2];
    $looser_deltaTS_sigma = $update[3];

    $pwinnerTS_mu += $winner_deltaTS_mu;
    $plooserTS_mu += $looser_deltaTS_mu;
    $pwinnerTS_sigma *= $winner_deltaTS_sigma;
    $plooserTS_sigma *= $looser_deltaTS_sigma;

    // Update players data ------------------------------------------
    // Looser
    //--------
    $plooserGames += 1;
    $pwinnerGames += 1;
    $plooserLosses += 1;
    $plooserPoints += $ePointsPerLoss;
    $plooserScore += 0; //fm- TBD
    $pwinnerWins += 1;
    $pwinnerPoints += $ePointsPerWin;
    $pwinnerScore += 0; //fm- TBD
    if ($plooserStreak > 0)
    {
        $plooserStreak = -1;
    }
    else
    {
        $plooserStreak -= 1;
    }
    if ($plooserStreak < $plooserStreak_Worst) $plooserStreak_Worst = $plooserStreak;

    // Update database
    // Reset rank delta after a match.
    $q = "UPDATE ".TBL_PLAYERS
    ." SET ELORanking = $plooserELO,"
    ."     GamesPlayed = $plooserGames,"
    ."     TS_mu = $plooserTS_mu,"
    ."     TS_sigma = $plooserTS_sigma,"
    ."     Loss = $plooserLosses,"
    ."     Score = $plooserScore,"
    ."     Points = $plooserPoints,"
    ."     Streak = $plooserStreak,"
    ."     Streak_Worst = $plooserStreak_Worst,"
    ."     RankDelta = 0"
    ." WHERE (PlayerID = '$plooserID')";
    $result = $sql->db_Query($q);

    // Winner
    //--------

    $result = $sql->db_Query($q);
    if ($pwinnerStreak < 0)
    {
        $pwinnerStreak = 1;
    }
    else
    {
        $pwinnerStreak += 1;
    }
    if ($pwinnerStreak > $pwinnerStreak_Best) $pwinnerStreak_Best = $pwinnerStreak;

    if ($pwinnerStreak == 5)
    {
        // Award: player wins 5 games in a row
        $q4 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
        VALUES ($pwinnerID,'PlayerStreak5',$time)";
        $result4 = $sql->db_Query($q4);
    }
    if ($pwinnerStreak == 10)
    {
        // Award: player wins 10 games in a row
        $q4 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
        VALUES ($pwinnerID,'PlayerStreak10',$time)";
        $result4 = $sql->db_Query($q4);
    }
    if ($pwinnerStreak == 25)
    {
        // Award: player wins 25 games in a row
        $q4 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
        VALUES ($pwinnerID,'PlayerStreak25',$time)";
        $result4 = $sql->db_Query($q4);
    }

    // Update database
    // Reset rank delta after a match.
    $q = "UPDATE ".TBL_PLAYERS
    ." SET ELORanking = $pwinnerELO,"
    ."     GamesPlayed = $pwinnerGames,"
    ."     TS_mu = $pwinnerTS_mu,"
    ."     TS_sigma = $pwinnerTS_sigma,"
    ."     Win = $pwinnerWins,"
    ."     Score = $pwinnerScore,"
    ."     Points = $pwinnerPoints,"
    ."     Streak = $pwinnerStreak,"
    ."     Streak_Best = $pwinnerStreak_Best,"
    ."     RankDelta = 0"
    ." WHERE (PlayerID = '$pwinnerID')";
    $result = $sql->db_Query($q);

    // Update Teams data ------------------------------------------
    if ($etype == "Team Ladder")
    {
        // Reset rank delta after a match.
        $q_3 = "UPDATE ".TBL_TEAMS." SET RankDelta = 0 WHERE (TeamID = '$plooserTeam')";
        $result_3 = $sql->db_Query($q_3);

        $q_3 = "UPDATE ".TBL_TEAMS." SET RankDelta = 0 WHERE (TeamID = '$pwinnerTeam')";
        $result_3 = $sql->db_Query($q_3);
    }

    // Create Match ------------------------------------------
    $comments = '';
    $q =
    "INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported, Comments)
    VALUES ($event_id,'$reported_by',$time, '$comments')";
    $result = $sql->db_Query($q);

    $last_id = mysql_insert_id();
    $match_id = $last_id;

    // Create Scores ------------------------------------------
    $q =
    "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_deltaELO,Player_deltaTS_mu,Player_deltaTS_sigma,Player_Score,Player_ScoreAgainst,Player_Rank,Player_Win,Player_Points)
    VALUES ($match_id,$pwinnerID,1,$deltaELO,$winner_deltaTS_mu,$winner_deltaTS_sigma,0,0,1,1,$ePointsPerWin)
    ";
    $result = $sql->db_Query($q);

    $q =
    "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_deltaELO,Player_deltaTS_mu,Player_deltaTS_sigma,Player_Score,Player_ScoreAgainst,Player_Rank,Player_Loss,Player_Points)
    VALUES ($match_id,$plooserID,2,-$deltaELO,$looser_deltaTS_mu,$looser_deltaTS_sigma,0,0,2,1,$ePointsPerLoss)
    ";
    $result = $sql->db_Query($q);

    $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
    $result = $sql->db_Query($q);

    header("Location: matchinfo.php?eventid=$event_id&matchid=$match_id");
}
else
{
    // should not be here -> redirect
    header("Location: events.php");
}

?>
