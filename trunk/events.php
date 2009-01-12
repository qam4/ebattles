<?php
/**
 * events.php
 *
 */

require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");

/**
 * displayEvents - Displays the events database table in
 * a nicely formatted html table.
 */
function displayCurrentEvents(){
   global $sql;
   global $session;

   $time = GMT_time();

   // how many rows to show per page
   $rowsPerPage = 20;
   $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
   $start = $rowsPerPage * $pg - $rowsPerPage;

   if (!isset($_POST['gameid'])) $_POST['gameid'] = "All";
   
   // Drop down list to select Games to display
   $q = "SELECT ".TBL_GAMES.".*"
       ." FROM ".TBL_GAMES
       ." ORDER BY Name";
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   echo "<form name=\"myform\" action=\"".e_PLUGIN."ebattles/".htmlspecialchars($_SERVER['PHP_SELF'])."\" method=\"post\">";
   echo "<table>\n";
   echo "<tr><td>\n";
   echo "Games:<br />\n";
   echo "<select name=\"gameid\">\n";
   echo "<option value=\"All\">All</option>\n";
   for($i=0; $i<$num_rows; $i++){
      $gname  = mysql_result($result,$i, TBL_GAMES.".name");
      $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
      echo "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
   }
   echo "</select>\n";
   echo "</td>\n";
   echo "<td>\n";
   echo "<br />\n";
   echo "<input type=\"hidden\" name=\"subgameselect\" value=\"1\"></input>\n";
   echo "<input type=\"submit\" value=\"Filter\"></input>\n";
   echo "</td>\n";
   echo "</tr>\n";
   echo "</table>\n";
   echo "</form>\n";
   echo "<br />\n";
   echo "<br />\n";
   
   if ($_POST['gameid'] == "All")
   {
     $q = "SELECT count(*) "
         ." FROM ".TBL_EVENTS
         ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
         ."        OR (".TBL_EVENTS.".End_timestamp > $time)) ";
     $result = $sql->db_Query($q);
     $totalPages = mysql_result($result, 0);

     $q = "SELECT ".TBL_EVENTS.".*, "
                   .TBL_GAMES.".*"
         ." FROM ".TBL_EVENTS.", "
                  .TBL_GAMES
         ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
         ."        OR (".TBL_EVENTS.".End_timestamp > $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
         ." LIMIT $start, $rowsPerPage";
   }
   else
   {
     $q = "SELECT count(*) "
         ." FROM ".TBL_EVENTS
         ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
         ."        OR (".TBL_EVENTS.".End_timestamp > $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")";
     $result = $sql->db_Query($q);
     $totalPages = mysql_result($result, 0);

     $q = "SELECT ".TBL_EVENTS.".*, "
                   .TBL_GAMES.".*"
         ." FROM ".TBL_EVENTS.", "
                  .TBL_GAMES
         ." WHERE (   (".TBL_EVENTS.".End_timestamp = '')"
         ."        OR (".TBL_EVENTS.".End_timestamp > $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
         ."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")"
         ." LIMIT $start, $rowsPerPage";
   }
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error displaying info";
      return;
   }
   if($num_rows == 0){
      echo "Database table empty";
      return;
   }
   
   /* Display table contents */
   echo "<table class=\"type1\">\n";
   echo "<tr><td class=\"type1Header\"><b>Event</b></td><td colspan=\"2\" class=\"type1Header\"><b>Game</b></td><td class=\"type1Header\"><b>Type</b></td><td class=\"type1Header\"><b>Start</b></td><td class=\"type1Header\"><b>End</b></td><td class=\"type1Header\"><b>Players</b></td><td class=\"type1Header\"><b>Games</b></td></tr>\n";
   for($i=0; $i<$num_rows; $i++){
      $gname  = mysql_result($result,$i, TBL_GAMES.".name");
      $gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");
      $eid  = mysql_result($result,$i, TBL_EVENTS.".eventid");
      $ename  = mysql_result($result,$i, TBL_EVENTS.".name");
      $etype = mysql_result($result,$i, TBL_EVENTS.".type");
      $estart = mysql_result($result,$i, TBL_EVENTS.".Start_timestamp");
      $eend = mysql_result($result,$i, TBL_EVENTS.".End_timestamp");
      if($estart!=0) 
      {
        $estart_local = $estart + $session->timezone_offset;
        $date_start = date("d M Y",$estart_local);
      }
      else
      {
        $date_start = "-";
      }
      if($eend!=0) 
      {
        $eend_local = $eend + $session->timezone_offset;
        $date_end = date("d M Y",$eend_local);
      }
      else
      {
        $date_end = "-";
      }

      /* Nbr players */
      $q_2 = "SELECT COUNT(*) as NbrPlayers"
          ." FROM ".TBL_PLAYERS
          ." WHERE (Event = '$eid')";
      $result_2 = $sql->db_Query($q_2);
      $row = mysql_fetch_array($result_2);     
      $nbrplayers = $row['NbrPlayers'];     
      /* Nbr matches */
      $q_2 = "SELECT COUNT(*) as NbrMatches"
          ." FROM ".TBL_MATCHS
          ." WHERE (Event = '$eid')";
      $result_2 = $sql->db_Query($q_2);
      $row = mysql_fetch_array($result_2);     
      $nbrmatches = $row['NbrMatches'];     

      if(
           ($eend==0)
         ||($eend>=$time)
        )
      {
        echo "<tr><td class=\"type1Body\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\"><b>$ename</b></a></td><td class=\"type1Body\"><img src=\"".e_PLUGIN."ebattles/images/games_icons/$gicon\" alt=\"$gicon\"></img></td><td class=\"type1Body\">$gname</td><td class=\"type1Body\">$etype</td><td class=\"type1Body\">$date_start</td><td class=\"type1Body\">$date_end</td><td class=\"type1Body\">$nbrplayers</td><td class=\"type1Body\">$nbrmatches</td></tr>\n";
      }
   }
   echo "</table><br />\n";
   // print the navigation link
   paginate($rowsPerPage, $pg, $totalPages);

}

function displayRecentEvents(){
   global $sql;
   global $session;

   $time = GMT_time();

   // how many rows to show per page
   $rowsPerPage = 20;
   if (!isset($_POST['gameid'])) $_POST['gameid'] = "All";
   
   $q = "SELECT ".TBL_GAMES.".*"
       ." FROM ".TBL_GAMES
       ." ORDER BY Name";
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   echo "<form name=\"myform\" action=\"".e_PLUGIN."ebattles/".htmlspecialchars($_SERVER['PHP_SELF'])."\" method=\"post\">";
   echo "<table>\n";
   echo "<tr><td>\n";
   echo "Games:<br />\n";
   echo "<select name=\"gameid\">\n";
   echo "<option value=\"All\">All</option>\n";
   for($i=0; $i<$num_rows; $i++){
      $gname  = mysql_result($result,$i, TBL_GAMES.".name");
      $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
      echo "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
   }
   echo "</select>\n";
   echo "</td>\n";
   echo "<td>\n";
   echo "<br />\n";
   echo "<input type=\"hidden\" name=\"subgameselect\" value=\"1\"></input>\n";
   echo "<input type=\"submit\" value=\"Filter\"></input>\n";
   echo "</td>\n";
   echo "</tr>\n";
   echo "</table>\n";
   echo "</form>\n";
   echo "<br />\n";
   echo "<br />\n";
   
   if ($_POST['gameid'] == "All")
   {
     $q = "SELECT ".TBL_EVENTS.".*, "
                   .TBL_GAMES.".*"
         ." FROM ".TBL_EVENTS.", "
                  .TBL_GAMES
         ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
         ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
         ." LIMIT 0, $rowsPerPage";
   }
   else
   {   
     $q = "SELECT ".TBL_EVENTS.".*, "
                   .TBL_GAMES.".*"
         ." FROM ".TBL_EVENTS.", "
                  .TBL_GAMES
         ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
         ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
         ."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")"
         ." LIMIT 0, $rowsPerPage";
   }
       
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error displaying info";
      return;
   }
   if($num_rows == 0){
      echo "Database table empty";
      return;
   }
   /* Display table contents */
   echo "<table class=\"type1\">\n";
   echo "<tr><td class=\"type1Header\"><b>Event</b></td><td colspan=\"2\" class=\"type1Header\"><b>Game</b></td><td class=\"type1Header\"><b>Type</b></td><td class=\"type1Header\"><b>Start</b></td><td class=\"type1Header\"><b>End</b></td><td class=\"type1Header\"><b>Players</b></td><td class=\"type1Header\"><b>Games</b></td></tr>\n";
   for($i=0; $i<$num_rows; $i++){
      $gname  = mysql_result($result,$i, TBL_GAMES.".name");
      $gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");
      $eid  = mysql_result($result,$i, TBL_EVENTS.".eventid");
      $ename  = mysql_result($result,$i, TBL_EVENTS.".name");
      $etype = mysql_result($result,$i, TBL_EVENTS.".type");
      $estart = mysql_result($result,$i, TBL_EVENTS.".Start_timestamp");
      $eend = mysql_result($result,$i, TBL_EVENTS.".End_timestamp");
      if($estart!=0) 
      {
        $estart_local = $estart + $session->timezone_offset;
        $date_start = date("d M Y",$estart_local);
      }
      else
      {
        $date_start = "-";
      }
      if($eend!=0) 
      {
        $eend_local = $eend + $session->timezone_offset;
        $date_end = date("d M Y",$eend_local);
      }
      else
      {
        $date_end = "-";
      }

      /* Nbr players */
      $q_2 = "SELECT COUNT(*) as NbrPlayers"
          ." FROM ".TBL_PLAYERS
          ." WHERE (Event = '$eid')";
      $result_2 = $sql->db_Query($q_2);
      $row = mysql_fetch_array($result_2);     
      $nbrplayers = $row['NbrPlayers'];     
      /* Nbr matches */
      $q_2 = "SELECT COUNT(*) as NbrMatches"
          ." FROM ".TBL_MATCHS
          ." WHERE (Event = '$eid')";
      $result_2 = $sql->db_Query($q_2);
      $row = mysql_fetch_array($result_2);     
      $nbrmatches = $row['NbrMatches'];     

      if(
           ($eend!=0)
         &&($eend<$time)
         )
      {
        echo "<tr><td class=\"type1Body\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\"><b>$ename</b></a></td><td class=\"type1Body\"><img src=\"".e_PLUGIN."ebattles/images/games_icons/$gicon\" alt=\"$gicon\"></img></td><td class=\"type1Body\">$gname</td><td class=\"type1Body\">$etype</td><td class=\"type1Body\">$date_start</td><td class=\"type1Body\">$date_end</td><td class=\"type1Body\">$nbrplayers</td><td class=\"type1Body\">$nbrmatches</td></tr>\n";
      }
   }
   echo "</table><br />\n";

   echo "<p>";
   echo "[<a href=\"".e_PLUGIN."ebattles/eventspast.php\">Show all past events</a>]";
   echo "</p>";

}?>



<div id="main">
<script type="text/javascript" src="./js/tabpane.js"></script>

<h1>Events</h1>

<div class="tab-pane" id="tab-pane-2">
<div class="tab-page">
<h2 class="tab">Current Events</h2>

<?php
if($session->logged_in)
{
     echo "<form action=\"".e_PLUGIN."ebattles/eventcreate.php\" method=\"post\">";
     echo "<input type=\"hidden\" name=\"userid\" value=\"{USER_ID}\"></input>";
     echo "<input type=\"submit\" name=\"createevent\" value=\"Create new Event\"></input>";
     echo "</form>";
}
?>

<?php
/**
 * Display Current Events
 */
?>
<?php
displayCurrentEvents();
?>
</div>

<div class="tab-page">
<h2 class="tab">Recent Events</h2>

<?php
/**
 * Display Recent Events
 */
?>
<?php
displayRecentEvents();
?>
</div>

</div>

<p>
Back to [<a href="./index.php">Main Page</a>]
</p>

</div>
<script type="text/javascript">
//<![CDATA[

setupAllTabs();

//]]>
</script>

<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
