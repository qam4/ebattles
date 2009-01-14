<?php
/**
 * quickreport.php
 *
 * This page is for users to report a loss of a 1v1 match
 * the player just needs to input who he conceided to loss to.
 *
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
require_once(HEADERF);

$text = '';

/* Event Name */
$event_id = $_GET['eventid'];

if ( (!isset($_POST['quicklossreport'])) || (!isset($_GET['eventid'])))
{
   $text .= "<br />You are not authorized to report a quick loss.<br />";
   $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]<br />";
}
else
{
   $q = "SELECT ".TBL_PLAYERS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_PLAYERS.", "
                .TBL_USERS
       ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
         ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".Name)"
       ." ORDER BY ".TBL_USERS.".user_name";
   
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);

   $text .= '
   <div class="news">
   <h2>Quick Report</h2>
   <table>
   ';
   
   $text .= "<form action=\"".e_PLUGIN."ebattles/matchprocess.php\" method=\"post\">";
   $text .= '
   <tr>
     <td>
       Player:
       <select name="Player">
   ';
   
   for($i=0; $i<$num_rows; $i++)
   {
     $pid  = mysql_result($result,$i, TBL_USERS.".user_id");
     $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
     $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
     if($pid != USERID)
     { 
     	$text .= "<option value=\"$pid\">#$prank - $pname</option>";
     }
   }
   
   $text .= '
       </select>
     </td>
   </tr>
   <tr>
     <td>
   ';
   
   $reported_by = USERID;
   $text .= "<input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>";
   $text .= "<input type=\"hidden\" name=\"reported_by\" value=\"$reported_by\"></input>";
   
   $text .= '
       <input type="hidden" name="qrsubmitloss" value="1"></input>
       <input class="button" type="submit" value="Submit Loss"></input>
     </td>
   </tr>
   </form>
   </table>
   </div>
   ';   
}

$ns->tablerender('Quick Loss Report', $text);
require_once(FOOTERF);
exit;
?>
