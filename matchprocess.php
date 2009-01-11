<?php
/**
 *MatchProcess.php
 * 
 */
include("./include/session.php");
require_once './include/ELO.php';
global $database;

   if(isset($_POST['qrsubmitloss']))
   {
      $event_id = $_POST['eventid'];
      $reported_by = $_POST['reported_by'];
      
      $q = "SELECT ".TBL_EVENTS.".*"
          ." FROM ".TBL_EVENTS
          ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
      
      $result = $database->query($q);
      $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
      $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
      $eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
      $eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");

      $plooserName = $reported_by;
      $q = "SELECT *"
          ." FROM ".TBL_PLAYERS
          ." WHERE (Event = '$event_id')"
          ."   AND (Name = '$plooserName')";
      $result = $database->query($q);
      $row = mysql_fetch_array($result);     
      $plooserID = $row['PlayerID'];
      $plooserELO = $row['ELORanking'];     
      $plooserGames = $row['GamesPlayed'];
      $plooserLosses = $row['Loss'];     
      $plooserStreak = $row['Streak'];     
      $plooserStreak_Best = $row['Streak_Best'];     
      $plooserStreak_Worst = $row['Streak_Worst'];     

      $pwinnerName = $_POST['Player'];
      $q = "SELECT *"
          ." FROM ".TBL_PLAYERS
          ." WHERE (Event = '$event_id')"
          ."   AND (Name = '$pwinnerName')";
      $result = $database->query($q);
      $row = mysql_fetch_array($result);     
      $pwinnerID = $row['PlayerID'];
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
      $q = "UPDATE ".TBL_PLAYERS." SET ELORanking = $plooserELO WHERE (Name = '$plooserName') AND (Event = '$event_id')";
      $result = $database->query($q);
      $q = "UPDATE ".TBL_PLAYERS." SET ELORanking = $pwinnerELO WHERE (Name = '$pwinnerName') AND (Event = '$event_id')";
      $result = $database->query($q);

      $plooserGames += 1;
      $pwinnerGames += 1;
      $plooserLosses += 1;
      $pwinnerWins += 1;
      $q = "UPDATE ".TBL_PLAYERS." SET GamesPlayed = $plooserGames WHERE (Name = '$plooserName') AND (Event = '$event_id')";
      $result = $database->query($q);
      $q = "UPDATE ".TBL_PLAYERS." SET Loss = $plooserLosses WHERE (Name = '$plooserName') AND (Event = '$event_id')";
      $result = $database->query($q);
      if ($plooserStreak > 0)
      {
      	$plooserStreak = -1;
      }
      else
      {
      	$plooserStreak -= 1;
      }
      if ($plooserStreak < $plooserStreak_Worst) $plooserStreak_Worst = $plooserStreak; 
      $q3 = "UPDATE ".TBL_PLAYERS." SET Streak = $plooserStreak WHERE (Name = '$plooserName') AND (Event = '$event_id')";
      $result3 = $database->query($q3);
      $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Worst = $plooserStreak_Worst WHERE (Name = '$plooserName') AND (Event = '$event_id')";
      $result3 = $database->query($q3);      
      
      $q = "UPDATE ".TBL_PLAYERS." SET GamesPlayed = $pwinnerGames WHERE (Name = '$pwinnerName') AND (Event = '$event_id')";
      $result = $database->query($q);
      $q = "UPDATE ".TBL_PLAYERS." SET Win = $pwinnerWins WHERE (Name = '$pwinnerName') AND (Event = '$event_id')";
      $result = $database->query($q);
      if ($pwinnerStreak < 0)
      {
      	$pwinnerStreak = 1;
      }
      else
      {
      	$pwinnerStreak += 1;
      }
      if ($pwinnerStreak > $pwinnerStreak_Best) $pwinnerStreak_Best = $pwinnerStreak; 
      $q3 = "UPDATE ".TBL_PLAYERS." SET Streak = $pwinnerStreak WHERE (Name = '$pwinnerName') AND (Event = '$event_id')";
      $result3 = $database->query($q3);
      $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Best = $pwinnerStreak_Best WHERE (Name = '$pwinnerName') AND (Event = '$event_id')";
      $result3 = $database->query($q3);      

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
      $result = $database->query($q);
      
      $last_id = mysql_insert_id();

      // Create Scores ------------------------------------------        
      $q = 
      "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_deltaELO,Player_Score,Player_Rank)
       VALUES ($last_id,$pwinnerID,1,$deltaELO,1,1)
       ";
      $result = $database->query($q);
	
      $q = 
      "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_deltaELO,Player_Score,Player_Rank)
       VALUES ($last_id,$plooserID,2,-$deltaELO,0,2)
       ";
      $result = $database->query($q);

      $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
      $result = $database->query($q);

      header("Location: eventinfo.php?eventid=$event_id");
   }
   else
   {
      // should not be here -> redirect
      header("Location: index.php");
   }

?>