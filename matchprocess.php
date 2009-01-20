<?php
/**
* MatchProcess.php
* Quick match report process
*
*/
require_once("../../class2.php");
require_once e_PLUGIN.'ebattles/include/main.php';
require_once e_PLUGIN.'ebattles/include/ELO.php';
global $sql;

if(isset($_POST['qrsubmitloss']))
{
    $event_id = $_POST['eventid'];
    $reported_by = $_POST['reported_by'];

    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";

    $result = $sql->db_Query($q);
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
    $eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
    $eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");

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
    $plooserGames = $row['GamesPlayed'];
    $plooserLosses = $row['Loss'];
    $plooserStreak = $row['Streak'];
    $plooserStreak_Best = $row['Streak_Best'];
    $plooserStreak_Worst = $row['Streak_Worst'];

    $pwinnerID = $_POST['Player'];
    $q = "SELECT *"
    ." FROM ".TBL_PLAYERS
    ." WHERE (Event = '$event_id')"
    ."   AND (PlayerID = '$pwinnerID')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $pwinnerUser = $row['User'];
    $pwinnerELO = $row['ELORanking'];
    $pwinnerGames = $row['GamesPlayed'];
    $pwinnerWins = $row['Win'];
    $pwinnerStreak = $row['Streak'];
    $pwinnerStreak_Best = $row['Streak_Best'];
    $pwinnerStreak_Worst = $row['Streak_Worst'];

    // New ELO ------------------------------------------
    $M=$eELO_M;       // Span
    $K=$eELO_K;	// Max adjustment per game
    $deltaELO = ELO($M, $K, $pwinnerELO, $plooserELO, 1, 0);
    $plooserELO = $plooserELO - $deltaELO;
    $pwinnerELO = $pwinnerELO + $deltaELO;

    // Update players data ------------------------------------------
    $q = "UPDATE ".TBL_PLAYERS." SET ELORanking = $plooserELO WHERE (PlayerID = '$plooserID')";
    $result = $sql->db_Query($q);
    $q = "UPDATE ".TBL_PLAYERS." SET ELORanking = $pwinnerELO WHERE (PlayerID = '$pwinnerID')";
    $result = $sql->db_Query($q);

    $plooserGames += 1;
    $pwinnerGames += 1;
    $plooserLosses += 1;
    $pwinnerWins += 1;
    $q = "UPDATE ".TBL_PLAYERS." SET GamesPlayed = $plooserGames WHERE (PlayerID = '$plooserID')";
    $result = $sql->db_Query($q);
    $q = "UPDATE ".TBL_PLAYERS." SET Loss = $plooserLosses WHERE (PlayerID = '$plooserID')";
    $result = $sql->db_Query($q);
    if ($plooserStreak > 0)
    {
        $plooserStreak = -1;
    }
    else
    {
        $plooserStreak -= 1;
    }
    if ($plooserStreak < $plooserStreak_Worst) $plooserStreak_Worst = $plooserStreak;
    $q3 = "UPDATE ".TBL_PLAYERS." SET Streak = $plooserStreak WHERE (PlayerID = '$plooserID')";
    $result3 = $sql->db_Query($q3);
    $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Worst = $plooserStreak_Worst WHERE (PlayerID = '$plooserID')";
    $result3 = $sql->db_Query($q3);

    $q = "UPDATE ".TBL_PLAYERS." SET GamesPlayed = $pwinnerGames WHERE (PlayerID = '$pwinnerID')";
    $result = $sql->db_Query($q);
    $q = "UPDATE ".TBL_PLAYERS." SET Win = $pwinnerWins WHERE (PlayerID = '$pwinnerID') AND (Event = '$event_id')";
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
    $q3 = "UPDATE ".TBL_PLAYERS." SET Streak = $pwinnerStreak WHERE (PlayerID = '$pwinnerID')";
    $result3 = $sql->db_Query($q3);
    $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Best = $pwinnerStreak_Best WHERE (PlayerID = '$pwinnerID')";
    $result3 = $sql->db_Query($q3);

    // Update Teams data ------------------------------------------
    if ($etype == "Team Ladder")
    {



    }

    // Create Match ------------------------------------------
    $time = GMT_time();
    $comments = '';
    $q =
    "INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported, Comments)
    VALUES ($event_id,'$reported_by',$time, '$comments')";
    $result = $sql->db_Query($q);

    $last_id = mysql_insert_id();

    // Create Scores ------------------------------------------
    $q =
    "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_deltaELO,Player_Score,Player_Rank)
    VALUES ($last_id,$pwinnerID,1,$deltaELO,1,1)
    ";
    $result = $sql->db_Query($q);

    $q =
    "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_deltaELO,Player_Score,Player_Rank)
    VALUES ($last_id,$plooserID,2,-$deltaELO,0,2)
    ";
    $result = $sql->db_Query($q);

    $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
    $result = $sql->db_Query($q);

    header("Location: eventinfo.php?eventid=$event_id");
}
else
{
    // should not be here -> redirect
    header("Location: events.php");
}

?>