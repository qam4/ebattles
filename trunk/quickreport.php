<?php
/**
 * quickreport.php
 *
 * This page is for users to edit their account information
 * such as their password, email address, etc. Their
 * usernames can not be edited. When changing their
 * password, they must first confirm their current password.
 *
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

?>
<div id="main">

<?php
   /* Event Name */
   $event_id = $_GET['eventid'];

   if (!isset($_POST['quicklossreport']))
   {
      echo "<br />You are not authorized to report a quick loss.<br />";
      echo "<br />Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]<br />";
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
?>
<div class="news">
<h2>Quick Report</h2>
<table>
<?php   
echo "<form action=\"".e_PLUGIN."ebattles/matchprocess.php\" method=\"post\">";
?>
<tr>
  <td>
    Player:
    <select name="Player">
<?php   
    for($i=0; $i<$num_rows; $i++){
      $pid  = mysql_result($result,$i, TBL_USERS.".user_id");
      $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
      $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
      if($pid != {USER_ID})
      { 
      	echo "<option value=\"$pname\">#$prank - $pname</option>";
      }
     }
?>
    </select>
  </td>
</tr>
<tr>
  <td>
<?php   
    $reported_by = {USER_ID};
    echo "<input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>";
    echo "<input type=\"hidden\" name=\"reported_by\" value=\"$reported_by\"></input>";
?>
    <input type="hidden" name="qrsubmitloss" value="1"></input>
    <input type="submit" value="Submit Loss"></input>
  </td>
</tr>
</form>
</table>
</div>
</div>
<?php
   }
   include_once(e_PLUGIN."ebattles/include/footer.php");
?>