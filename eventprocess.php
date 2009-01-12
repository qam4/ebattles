<?php
/**
 *EventProcess.php
 * 
 */
include("include/session.php");

function resetPlayers($event_id)
{
      global $sql;
      $q2 = "SELECT ".TBL_EVENTS.".*"
          ." FROM ".TBL_EVENTS
          ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";  
      $result2 = $sql->db_Query($q2);
      $eELOdefault = mysql_result($result2,0 , TBL_EVENTS.".ELO_default");
      
      $q2 = "SELECT ".TBL_PLAYERS.".*"
          ." FROM ".TBL_PLAYERS
          ." WHERE (".TBL_PLAYERS.".Event = '$event_id')";  
      $result2 = $sql->db_Query($q2);
      $num_rows_2 = mysql_numrows($result2);
      if ($num_rows_2!=0)
      {
         for($j=0; $j<$num_rows_2; $j++)
         {
            $pID  = mysql_result($result2,$j, TBL_PLAYERS.".PlayerID");
            $q3 = "UPDATE ".TBL_PLAYERS." SET ELORanking = '$eELOdefault' WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET GamesPlayed = 0 WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET Loss = 0 WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET Win = 0 WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET Streak = 0 WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Best = 0 WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Worst = 0 WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
         }
      }
}
function resetTeams($event_id)
{
      global $sql;
      $q2 = "SELECT ".TBL_EVENTS.".*"
          ." FROM ".TBL_EVENTS
          ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";  
      $result2 = $sql->db_Query($q2);
      $eELOdefault = mysql_result($result2,0 , TBL_EVENTS.".ELO_default");
      
      $q2 = "SELECT ".TBL_TEAMS.".*"
          ." FROM ".TBL_TEAMS
          ." WHERE (".TBL_TEAMS.".Event = '$event_id')";  
      $result2 = $sql->db_Query($q2);
      $num_rows_2 = mysql_numrows($result2);
      if ($num_rows_2!=0)
      {
         for($j=0; $j<$num_rows_2; $j++)
         {
            $tID  = mysql_result($result2,$j, TBL_TEAMS.".PlayerID");
            $q3 = "UPDATE ".TBL_TEAMS." SET ELORanking = '$eELOdefault' WHERE (TeamID = '$tID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_TEAMS." SET GamesPlayed = 0 WHERE (TeamID = '$tID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_TEAMS." SET Loss = 0 WHERE (TeamID = '$tID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_TEAMS." SET Win = 0 WHERE (TeamID = '$tID')";
            $result3 = $sql->db_Query($q3);
         }
      }
}
function deleteMatches($event_id)
{
      global $sql;
      $q2 = "SELECT ".TBL_MATCHS.".*"
          ." FROM ".TBL_MATCHS
          ." WHERE (".TBL_MATCHS.".Event = '$event_id')";  
      $result2 = $sql->db_Query($q2);
      $num_rows_2 = mysql_numrows($result2);
      if ($num_rows_2!=0)
      {
         for($j=0; $j<$num_rows_2; $j++)
         {
            $mID  = mysql_result($result2,$j, TBL_MATCHS.".MatchID");
            $q3 = "DELETE FROM ".TBL_SCORES
                ." WHERE (".TBL_SCORES.".MatchID = '$mID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "DELETE FROM ".TBL_MATCHS
                ." WHERE (".TBL_MATCHS.".MatchID = '$mID')";
            $result3 = $sql->db_Query($q3);
         }
      }
}
function deletePlayers($event_id)
{
      global $sql;
      $q3 = "DELETE FROM ".TBL_PLAYERS
          ." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
      $result3 = $sql->db_Query($q3);
}
function deleteTeams($event_id)
{
      global $sql;
      $q3 = "DELETE FROM ".TBL_TEAMS
          ." WHERE (".TBL_TEAMS.".Event = '$event_id')";
      $result3 = $sql->db_Query($q3);
}
function deleteMods($event_id)
{
      global $sql;
      $q3 = "DELETE FROM ".TBL_EVENTMODS
          ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')";
      $result3 = $sql->db_Query($q3);
}
function deleteEvent($event_id)
{
      global $sql;
      deleteMatches($event_id);
      deletePlayers($event_id);
      deleteTeams($event_id);
      deleteMods($event_id);
      $q3 = "DELETE FROM ".TBL_EVENTS
          ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
      $result3 = $sql->db_Query($q3);
}


   $event_id = $_GET['eventid'];
   $q = "SELECT ".TBL_EVENTS.".*"
          ." FROM ".TBL_EVENTS
          ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";  
   $result = $sql->db_Query($q);
   $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
   $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
   
   $can_manage = 0;
   if ($session->isAdmin()) $can_manage = 1;
   if ($session->username==$eowner) $can_manage = 1;
   if ($can_manage == 0)
   {
      header("Location: index.php");
   }
   else{
      
      $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
      $result = $sql->db_Query($q);
      
      if(isset($_POST['eventdeletemod']))
      {      
         $eventmod = $_POST['eventmod'];
         $q2 = "DELETE FROM ".TBL_EVENTMODS
            ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
            ."   AND (".TBL_EVENTMODS.".Name = '$eventmod')";   
         $result2 = $sql->db_Query($q2);
      
         //echo "-- eventdeletemod --<br />";
         header("Location: eventmanage.php?eventid=$event_id");
      }
      if(isset($_POST['eventaddmod']))
      {
         $event_id = $_GET['eventid'];
      
         $eventmod = $_POST['mod'];
      
         $q2 = "SELECT ".TBL_EVENTMODS.".*"
             ." FROM ".TBL_EVENTMODS
             ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
             ."   AND (".TBL_EVENTMODS.".Name = '$eventmod')";   
         $result2 = $sql->db_Query($q2);
         $num_rows_2 = mysql_numrows($result2);
         if ($num_rows_2==0)
         {
            $q2 = "INSERT INTO ".TBL_EVENTMODS."(Event,Name,Level)"
               ." VALUES ('$event_id','$eventmod',1)";   
            $result2 = $sql->db_Query($q2);
         }
         //echo "-- eventaddmod --<br />";
         header("Location: eventmanage.php?eventid=$event_id");
      }
      
      if(isset($_POST['eventsettingssave']))
      {
         $event_id = $_GET['eventid'];
         $q2 = "SELECT ".TBL_EVENTS.".*"
             ." FROM ".TBL_EVENTS
             ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";  
         $result2 = $sql->db_Query($q2);
         $epassword = mysql_result($result2,0 , TBL_EVENTS.".Password");
      
         /* Event Name */
         $new_eventname = htmlspecialchars($_POST['eventname']);
         if ($new_eventname != '')
         {
               $q2 = "UPDATE ".TBL_EVENTS." SET Name = '$new_eventname' WHERE (EventID = '$event_id')";
               $result2 = $sql->db_Query($q2);
         }
         
         /* Event Password */
         $new_eventpassword = htmlspecialchars($_POST['eventpassword']);
         $q2 = "UPDATE ".TBL_EVENTS." SET Password = '$new_eventpassword' WHERE (EventID = '$event_id')";
         $result2 = $sql->db_Query($q2);
      
         /* Event Type */
         // Can change only if no players are signed up
         $q2 = "SELECT ".TBL_PLAYERS.".*"
             ." FROM ".TBL_PLAYERS
             ." WHERE (".TBL_PLAYERS.".Event = '$event_id')";  
         $result2 = $sql->db_Query($q2);
         $num_rows_2 = mysql_numrows($result2);
         if ($num_rows_2==0)
         {
            $new_eventtype = $_POST['eventtype'];
            if ($new_eventtype == 'Individual')
            {
                  $q2 = "UPDATE ".TBL_EVENTS." SET Type = 'One Player Ladder' WHERE (EventID = '$event_id')";
                  $result2 = $sql->db_Query($q2);
            }
            else
            {
                  $q2 = "UPDATE ".TBL_EVENTS." SET Type = 'Team Ladder' WHERE (EventID = '$event_id')";
                  $result2 = $sql->db_Query($q2);
            }
         }
      
         /* Event Game */
         $new_eventgame = $_POST['eventgame'];
               $q2 = "UPDATE ".TBL_EVENTS." SET Game = '$new_eventgame' WHERE (EventID = '$event_id')";
               $result2 = $sql->db_Query($q2);
      
         /* Event Start Date */
         $new_eventstartdate = $_POST['startdate'];
         if ($new_eventstartdate != '')
         {
         	$new_eventstart_local = strtotime($new_eventstartdate);
         	$new_eventstart = $new_eventstart_local - $session->timezone_offset;	// Convert to GMT time
         }
         else
         {
         	$new_eventstart = 0;
         }
               $q2 = "UPDATE ".TBL_EVENTS." SET Start_timestamp = '$new_eventstart' WHERE (EventID = '$event_id')";
               $result2 = $sql->db_Query($q2);
               //echo "$new_eventstart, $new_eventstartdate";
      
         /* Event End Date */
         $new_eventenddate = $_POST['enddate'];
         if ($new_eventenddate != '')
         {
         	$new_eventend_local = strtotime($new_eventenddate);
         	$new_eventend = $new_eventend_local - $session->timezone_offset;	// Convert to GMT time
         }
         else
         {
         	$new_eventend = 0;
         }
         if ($new_eventend < $new_eventstart)
         {
         	$new_eventend = $new_eventstart;
         }
         
               $q2 = "UPDATE ".TBL_EVENTS." SET End_timestamp = '$new_eventend' WHERE (EventID = '$event_id')";
               $result2 = $sql->db_Query($q2);
               //echo "$new_eventend, $new_eventenddate";
      
      
         /* Event Description */
         $allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
         $allowedTags.='<li><ol><ul><span><div><br /><ins><del>';
         $new_eventdescription = strip_tags(stripslashes($_POST['eventdescription']),$allowedTags);
               $q2 = "UPDATE ".TBL_EVENTS." SET Description = '$new_eventdescription' WHERE (EventID = '$event_id')";
               $result2 = $sql->db_Query($q2);
      
         //echo "-- eventsettingssave --<br />";
         header("Location: eventmanage.php?eventid=$event_id");
      }
      if(isset($_POST['eventrulessave']))
      {
         $event_id = $_GET['eventid'];
      
         /* Event Rules */
         $allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
         $allowedTags.='<li><ol><ul><span><div><br /><ins><del>';
         $new_eventrules = strip_tags(stripslashes($_POST['eventrules']),$allowedTags);
               $q2 = "UPDATE ".TBL_EVENTS." SET Rules = '$new_eventrules' WHERE (EventID = '$event_id')";
               $result2 = $sql->db_Query($q2);
      
         //echo "-- eventrulessave --<br />";
         header("Location: eventmanage.php?eventid=$event_id");
      }
      if(isset($_POST['eventresetscores']))
      {
         $event_id = $_GET['eventid'];
         resetPlayers($event_id);
         resetTeams($event_id);
         deleteMatches($event_id);
         
      
         //echo "-- eventresetscores --<br />";
         header("Location: eventmanage.php?eventid=$event_id");
      }      
      if(isset($_POST['eventresetevent']))
      {
         $event_id = $_GET['eventid'];
         deleteMatches($event_id);
         deletePlayers($event_id);
         deleteTeams($event_id);
         
      
         //echo "-- eventresetevent --<br />";
         header("Location: eventmanage.php?eventid=$event_id");
      }   
      if(isset($_POST['eventdelete']))
      {
         $event_id = $_GET['eventid'];
         deleteEvent($event_id);
      
         //echo "-- eventdelete --<br />";
         header("Location: events.php");
      }   
      
         
      if(isset($_POST['eventstatssave']))
      {
         $event_id = $_GET['eventid'];
      
         //echo "-- eventstatssave --<br />";
         
         /* Event Min games to rank */
         $new_eventGamesToRank = htmlspecialchars($_POST['sliderValue0']);      
         if (is_numeric($new_eventGamesToRank))
         {
               $q2 = "UPDATE ".TBL_EVENTS." SET nbr_games_to_rank = '$new_eventGamesToRank' WHERE (EventID = '$event_id')";
               $result2 = $sql->db_Query($q2);
         }
         if ($etype == "Team Ladder")
         {
            /* Event Min Team games to rank */
            $new_eventTeamGamesToRank = htmlspecialchars($_POST['sliderValue1']);      
            if (is_numeric($new_eventTeamGamesToRank))
            {
                  $q2 = "UPDATE ".TBL_EVENTS." SET nbr_team_games_to_rank = '$new_eventTeamGamesToRank' WHERE (EventID = '$event_id')";
                  $result2 = $sql->db_Query($q2);
            }
         }
         /* Event ELO */
         $new_eventELO = htmlspecialchars($_POST['sliderValue2']);      
         if (is_numeric($new_eventELO))
         {
               $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventELO' WHERE (Event = '$event_id') AND (CategoryName = 'ELO')";
               $result2 = $sql->db_Query($q2);
         }
         /* Event GamesPlayed */
         $new_eventGamesPlayed = htmlspecialchars($_POST['sliderValue3']);      
         if (is_numeric($new_eventGamesPlayed))
         {
               $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventGamesPlayed' WHERE (Event = '$event_id') AND (CategoryName = 'GamesPlayed')";
               $result2 = $sql->db_Query($q2);
         }
         /* Event VictoryRatio */
         $new_eventVictoryRatio = htmlspecialchars($_POST['sliderValue4']);      
         if (is_numeric($new_eventVictoryRatio))
         {
               $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventVictoryRatio' WHERE (Event = '$event_id') AND (CategoryName = 'VictoryRatio')";
               $result2 = $sql->db_Query($q2);
         }
         /* Event VictoryPercent */
         $new_eventVictoryPercent = htmlspecialchars($_POST['sliderValue5']);      
         if (is_numeric($new_eventVictoryPercent))
         {
               $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventVictoryPercent' WHERE (Event = '$event_id') AND (CategoryName = 'VictoryPercent')";
               $result2 = $sql->db_Query($q2);
         }
         /* Event UniqueOpponents */
         $new_eventUniqueOpponents = htmlspecialchars($_POST['sliderValue6']);      
         if (is_numeric($new_eventUniqueOpponents))
         {
               $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventUniqueOpponents' WHERE (Event = '$event_id') AND (CategoryName = 'UniqueOpponents')";
               $result2 = $sql->db_Query($q2);
         }
         /* Event OpponentsELO */
         $new_eventOpponentsELO = htmlspecialchars($_POST['sliderValue7']);      
         if (is_numeric($new_eventOpponentsELO))
         {
               $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventOpponentsELO' WHERE (Event = '$event_id') AND (CategoryName = 'OpponentsELO')";
               $result2 = $sql->db_Query($q2);
         }
         /* Event Streaks */
         $new_eventStreaks = htmlspecialchars($_POST['sliderValue8']);      
         if (is_numeric($new_eventStreaks))
         {
               $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventStreaks' WHERE (Event = '$event_id') AND (CategoryName = 'Streaks')";
               $result2 = $sql->db_Query($q2);
         }
          
         $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
         $result = $sql->db_Query($q4);
         
         header("Location: eventmanage.php?eventid=$event_id");
      }
   }   
?>