<?php
/**
 * EventMatchs.php
 *
 */
include("include/main.php");
include("include/pagination.php");
?>
<div id="main">

<?php
   global $database;

   /* Event Name */
   $event_id = $_GET['eventid'];

   /* set pagination variables */
   $rowsPerPage = 20;
   $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
   $start = $rowsPerPage * $pg - $rowsPerPage;

   $q = "SELECT count(*) "
       ." FROM ".TBL_MATCHS
       ." WHERE (".TBL_MATCHS.".Event = '$event_id')";
   $result = $database->query($q);
   $totalPages = mysql_result($result, 0);
      
   $q = "SELECT ".TBL_EVENTS.".*, "
                 .TBL_GAMES.".*"
       ." FROM ".TBL_EVENTS.", "
                .TBL_GAMES
       ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
       ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";       

   $result = $database->query($q);
   $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
   $egame = mysql_result($result,0 , TBL_GAMES.".Name");
   echo "<h1>$ename</h1>";
   echo "<h2>$egame</h2>";
   echo "<br />";

   $q = "SELECT COUNT(*) as NbrMatchs"
       ." FROM ".TBL_MATCHS
       ." WHERE (Event = '$event_id')";
   $result = $database->query($q);
   $row = mysql_fetch_array($result);     
   $nbrmatchs = $row['NbrMatchs'];     
   echo"<div class=\"news\">";
   echo"<h2>Matches for this Ladder ($nbrmatchs)</h2><br />";
   echo "<p>";
   /* Stats/Results */
   $q = "SELECT ".TBL_MATCHS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_MATCHS.", "
                .TBL_USERS
       ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
         ." AND (".TBL_USERS.".username = ".TBL_MATCHS.".ReportedBy)"
       ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
       ." LIMIT $start, $rowsPerPage";
 
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);

   if ($num_rows>0)
   {
      /* Display table contents */
      echo "<table class=\"type1\">\n";
      echo "<tr><td class=\"type1Header\" style=\"width:120px\"><b>Match ID</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Reported By</b></td><td class=\"type1Header\"><b>Players</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Date</b></td></tr>\n";
      for($i=0; $i<$num_rows; $i++){
         $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
         $mReportedBy  = mysql_result($result,$i, TBL_MATCHS.".ReportedBy");
         $mReportedByNickname  = mysql_result($result,$i, TBL_USERS.".nickname");
         $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
         $mTime_local = $mTime + $session->timezone_offset;
         //$date = date("d M Y, h:i:s A",$mTime);
         $date = date("d M Y",$mTime_local);

         $q2 = "SELECT ".TBL_MATCHS.".*, "
                       .TBL_SCORES.".*, "
                       .TBL_PLAYERS.".*, "
                       .TBL_USERS.".*"
             ." FROM ".TBL_MATCHS.", "
                      .TBL_SCORES.", "
                      .TBL_PLAYERS.", "
                      .TBL_USERS
             ." WHERE (".TBL_MATCHS.".MatchID = '$mID')"
               ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
               ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
               ." AND (".TBL_USERS.".username = ".TBL_PLAYERS.".Name)"
             ." ORDER BY ".TBL_SCORES.".Player_Rank";

         $result2 = $database->query($q2);
         $num_rows2 = mysql_numrows($result2);
         $pnickname = '';
         $players = '';
         for($j=0; $j<$num_rows2; $j++)
         {
            $pnickname  = mysql_result($result2,$j, TBL_USERS.".nickname");
            $pname  = mysql_result($result2,$j, TBL_USERS.".username");
            if ($j==0)
              $players = "<a class=\"type1\" href=\"userinfo.php?user=$pname\">$pnickname</a>";
            else
              $players = $players.", <a class=\"type1\" href=\"userinfo.php?user=$pname\">$pnickname</a>";
         }

         echo "<tr>\n";
         echo "<td class=\"type1Body\"><b>$mID</b> <a class=\"type1\" href=\"matchinfo.php?eventid=$event_id&matchid=$mID\">(Show details)</a></td><td class=\"type1Body\"><a class=\"type1\" href=\"userinfo.php?user=$mReportedBy\">$mReportedByNickname</a></td><td class=\"type1Body\">$players</td><td class=\"type1Body\">$date</td></tr>";

      
   }
      echo "</table><br />\n"; 
   }
 


   paginate($rowsPerPage, $pg, $totalPages);

   echo "<br />";
   echo "<p>";
   echo "</div>";
/* Link back to main */
echo "<p>";
echo "<br />Back to [<a href=\"eventinfo.php?eventid=$event_id\">Event</a>]<br />";
echo "</p>";

?>
</div>
<?php
include("include/footer.php");
?>
