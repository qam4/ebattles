<?php
/**
 * EventMatchs.php
 *
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text = '';

global $sql;

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
   $text .= "<br />Error.<br />";
}
else
{
   /* set pagination variables */
   $rowsPerPage = 20;
   $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
   $start = $rowsPerPage * $pg - $rowsPerPage;

   $q = "SELECT count(*) "
       ." FROM ".TBL_MATCHS
       ." WHERE (".TBL_MATCHS.".Event = '$event_id')";
   $result = $sql->db_Query($q);
   $totalPages = mysql_result($result, 0);
      
   $q = "SELECT ".TBL_EVENTS.".*, "
                 .TBL_GAMES.".*"
       ." FROM ".TBL_EVENTS.", "
                .TBL_GAMES
       ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
       ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";       

   $result = $sql->db_Query($q);
   $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
   $egame = mysql_result($result,0 , TBL_GAMES.".Name");
   $text .= "<h1>$ename</h1>";
   $text .= "<h2>$egame</h2>";
   $text .= "<br />";

   $q = "SELECT COUNT(*) as NbrMatchs"
       ." FROM ".TBL_MATCHS
       ." WHERE (Event = '$event_id')";
   $result = $sql->db_Query($q);
   $row = mysql_fetch_array($result);     
   $nbrmatchs = $row['NbrMatchs'];     
   $text .="<div class=\"news\">";
   $text .="<h2>Matches for this Ladder ($nbrmatchs)</h2><br />";
   $text .= "<p>";
   /* Stats/Results */
   $q = "SELECT ".TBL_MATCHS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_MATCHS.", "
                .TBL_USERS
       ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
         ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
       ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
       ." LIMIT $start, $rowsPerPage";
 
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);

   if ($num_rows>0)
   {
      /* Display table contents */
      $text .= "<table class=\"type1Border\">\n";
      $text .= "<tr><td class=\"type1Header\" style=\"width:120px\"><b>Match ID</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Reported By</b></td><td class=\"type1Header\"><b>Players</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Date</b></td></tr>\n";
      for($i=0; $i<$num_rows; $i++){
         $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
         $mReportedBy  = mysql_result($result,$i, TBL_MATCHS.".ReportedBy");
         $mReportedByNickname  = mysql_result($result,$i, TBL_USERS.".user_name");
         $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
         $mTime_local = $mTime + GMT_TIMEOFFSET;
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
               ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".Name)"
             ." ORDER BY ".TBL_SCORES.".Player_Rank";

         $result2 = $sql->db_Query($q2);
         $num_rows2 = mysql_numrows($result2);
         $pname = '';
         $players = '';
         for($j=0; $j<$num_rows2; $j++)
         {
            $pid  = mysql_result($result2,$j, TBL_USERS.".user_id");
            $pname  = mysql_result($result2,$j, TBL_USERS.".user_name");
            if ($j==0)
              $players = "<a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pname</a>";
            else
              $players = $players.", <a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pname</a>";
         }

         $text .= "<tr>\n";
         $text .= "<td class=\"type1Body2\"><b>$mID</b> <a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$event_id&matchid=$mID\">(Show details)</a></td><td class=\"type1Body2\"><a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mReportedBy\">$mReportedByNickname</a></td><td class=\"type1Body2\">$players</td><td class=\"type1Body2\">$date</td></tr>";

      
   }
      $text .= "</table><br />\n"; 
   }
 


   paginate($rowsPerPage, $pg, $totalPages);

   $text .= "<br />";
   $text .= "<p>";
   $text .= "</div>";
}
$ns->tablerender('Event Matches', $text);
require_once(FOOTERF);
exit;
?>
