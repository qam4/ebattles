<?php
/**
 * matchinfo.php
 *
 * This page is for users to edit their account information
 * such as their password, email address, etc. Their
 * usernames can not be edited. When changing their
 * password, they must first confirm their current password.
 *
 */
include("include/main.php");

?>
<div id="main">

<?php
   /* Event Name */
   $event_id = $_GET['eventid'];
   $match_id = $_GET['matchid'];

   $q = "SELECT ".TBL_EVENTS.".*, "
                 .TBL_GAMES.".*"
       ." FROM ".TBL_EVENTS.", "
                .TBL_GAMES
       ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
       ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";       

   $result = $database->query($q);
   $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
   $egame = mysql_result($result,0 , TBL_GAMES.".Name");
   $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
   $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
   $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
   
   echo "<h1>$ename</h1>";
   echo "<h2>$egame</h2>";
   

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
         ." AND (".TBL_USERS.".username = ".TBL_PLAYERS.".Name)"
       ." ORDER BY ".TBL_SCORES.".Player_Rank";
 
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);
   echo"<div class=\"news\">";
   echo "<h2>Match (#$match_id)</h2><br />";

   if ($num_rows>0)
   {
      $reported_by  = mysql_result($result,0, TBL_MATCHS.".ReportedBy");
      $reported_by_nickname  = mysql_result($result,0, TBL_USERS.".nickname");
      $comments  = mysql_result($result,0, TBL_MATCHS.".Comments");
      $time_reported  = mysql_result($result,0, TBL_MATCHS.".TimeReported");
      $time_reported_local = $time_reported + $session->timezone_offset;
      $date = date("d M Y, h:i:s A",$time_reported_local);
      
      echo "Match reported by <a href=\"userinfo.php?user=$reported_by\">$reported_by_nickname</a> ($date)<br />";
   }
   else
   {
      $date_reported  = '';
      $reported_by  = '';
      $comments  = 'Match deleted';
   }
   
   // Can I delete the game
   //-----------------------
   $time = GMT_time();

   // Is the user a moderator?
   $q_2 = "SELECT ".TBL_EVENTMODS.".*"
       ." FROM ".TBL_EVENTMODS
       ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
       ."   AND (".TBL_EVENTMODS.".Name = '$session->username')";   
   $result_2 = $database->query($q_2);
   $num_rows_2 = mysql_numrows($result_2);
   
   $can_delete = 0;
   if (  ($session->username==$reported_by)
       &&(  ($eend==0)
          ||(  ($eend>=$time)
             &&($estart<=$time)
            )
         )
      )
     $can_delete = 1;
   if ($session->isAdmin())  $can_delete = 1;
   if ($session->username==$eowner)  $can_delete = 1;
   if ($num_rows_2>0)  $can_delete = 1;
   
   if($can_delete != 0)
   {
      echo "<form action=\"matchdelete.php?eventid=$event_id\" method=\"post\">";
      echo "<input type=\"hidden\" name=\"matchid\" value=\"$match_id\"></input>";
      echo "<input type=\"submit\" name=\"deletematch\" value=\"Delete this match\"></input>";
      echo "</form>";
   }
   
   echo "<br />";
   
   echo "<table class=\"type1\">\n";
   echo "<tr><td class=\"type1Header\"><b>Rank</b></td><td class=\"type1Header\"><b>Team</b></td><td class=\"type1Header\"><b>Player</b></td><td class=\"type1Header\"><b>Score</b></td><td class=\"type1Header\"><b>ELO</b></td></tr>\n";
   for($i=0; $i<$num_rows; $i++)
   {
      $pname  = mysql_result($result,$i, TBL_USERS.".username");
      $pnickname  = mysql_result($result,$i, TBL_USERS.".nickname");
      $prank  = mysql_result($result,$i, TBL_SCORES.".Player_Rank");
      $pMatchTeam  = mysql_result($result,$i, TBL_SCORES.".Player_MatchTeam");
      $pdeltaELO  = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
      $pscore  = mysql_result($result,$i, TBL_SCORES.".Player_Score");

      //echo "Rank #$prank - $pnickname (team #$pMatchTeam)- score: $pscore (ELO:$pdeltaELO)<br />";
      echo "<tr>\n";
      echo "<td class=\"type1Body\"><b>$prank</b></td><td class=\"type1Body\">$pMatchTeam</td><td class=\"type1Body\"><a class=\"type1\" href=\"userinfo.php?user=$pname\">$pnickname</a></td><td class=\"type1Body\">$pscore</td><td class=\"type1Body\">$pdeltaELO</td></tr>";

   }
   echo "</table><br />\n";
   
   echo "<p>";
   echo "Comments:<br />\n";
   echo "$comments<br />\n";
   echo "</p>";
   echo "</div>";

   echo "<p>";
   echo "<br />Back to [<a href=\"eventinfo.php?eventid=$event_id\">Event</a>]<br />";
   echo "</p>";
?>
</div>
<?php
include("include/footer.php");
?>
