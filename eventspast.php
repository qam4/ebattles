<?php
/**
 * events.php
 *
 */

require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text .='
<script type="text/javascript" src="./js/tabpane.js"></script>
';

/**
 * Display Users Table
 */
$text .= '
<div class="tab-pane" id="tab-pane-9">
<div class="tab-page">
<div class="tab">Past Events</div>
';
displayPastEvents();
$text .='
</div>
</div>
';

$ns->tablerender('Past Events', $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
 Functions
***************************************************************************************/
/**
 * displayEvents - Displays the events database table in
 * a nicely formatted html table.
 */
function displayPastEvents(){
   global $sql;
   global $text;
   global $time;

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
   $text .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">';
   $text .= '<table>';
   $text .= '<tr><td>';
   $text .= 'Games:<br />';
   $text .= '<select class="tbox" name="gameid">';
   $text .= '<option value="All">All</option>';
   for($i=0; $i<$num_rows; $i++){
      $gname  = mysql_result($result,$i, TBL_GAMES.".name");
      $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
      $text .= "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
   }
   $text .= "</select>\n";
   $text .= "</td>\n";
   $text .= "<td>\n";
   $text .= "<br />\n";
   $text .= "<input class=\"button\" type=\"submit\" name=\"subgameselect\" value=\"Filter\"/>\n";
   $text .= "</td>\n";
   $text .= "</tr>\n";
   $text .= "</table>\n";
   $text .= "</form>\n";
   $text .= "<br />\n";
   
   if ($_POST['gameid'] == "All")
   {
     $q = "SELECT count(*) "
         ." FROM ".TBL_EVENTS
         ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
         ."       AND (".TBL_EVENTS.".End_timestamp < $time)) ";
     $result = $sql->db_Query($q);
     $totalPages = mysql_result($result, 0);

     $q = "SELECT ".TBL_EVENTS.".*, "
                   .TBL_GAMES.".*"
         ." FROM ".TBL_EVENTS.", "
                  .TBL_GAMES
         ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
         ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
         ." LIMIT $start, $rowsPerPage";
   }
   else
   {
     $q = "SELECT count(*) "
         ." FROM ".TBL_EVENTS
         ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
         ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")";
     $result = $sql->db_Query($q);
     $totalPages = mysql_result($result, 0);

     $q = "SELECT ".TBL_EVENTS.".*, "
                   .TBL_GAMES.".*"
         ." FROM ".TBL_EVENTS.", "
                  .TBL_GAMES
         ." WHERE (   (".TBL_EVENTS.".End_timestamp != '')"
         ."       AND (".TBL_EVENTS.".End_timestamp < $time)) "
         ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
         ."   AND (".TBL_EVENTS.".Game = ".$_POST['gameid'].")"
         ." LIMIT $start, $rowsPerPage";
   }
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      $text .= "Error displaying info";
      return;
   }
   if($num_rows == 0){
      $text .= "<div>No old events.</div>";
      return;
   }
   
   /* Display table contents */
   $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
   $text .= "<tr><td class=\"forumheader\"><b>Event</b></td><td colspan=\"2\" class=\"forumheader\"><b>Game</b></td><td class=\"forumheader\"><b>Type</b></td><td class=\"forumheader\"><b>Start</b></td><td class=\"forumheader\"><b>End</b></td><td class=\"forumheader\"><b>Players</b></td><td class=\"forumheader\"><b>Games</b></td></tr>\n";
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
        $estart_local = $estart + GMT_TIMEOFFSET;
        $date_start = date("d M Y",$estart_local);
      }
      else
      {
        $date_start = "-";
      }
      if($eend!=0) 
      {
        $eend_local = $eend + GMT_TIMEOFFSET;
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
         ||($eend<=$time)
        )
      {
        $text .= "<tr><td class=\"forumheader3\"><a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a></td><td class=\"forumheader3\"><img src=\"".getGameIcon($gicon)."\" alt=\"$gicon\"/></td><td class=\"forumheader3\">$gname</td><td class=\"forumheader3\">$etype</td><td class=\"forumheader3\">$date_start</td><td class=\"forumheader3\">$date_end</td><td class=\"forumheader3\">$nbrplayers</td><td class=\"forumheader3\">$nbrmatches</td></tr>\n";
      }
   }
   $text .= "</tbody></table><br />\n";
   // print the navigation link
   $text .= paginate($rowsPerPage, $pg, $totalPages);

   $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/events.php\">Events</a>]<br />";
}

?>
