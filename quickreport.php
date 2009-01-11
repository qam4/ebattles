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
include("include/main.php");

?>
<div id="main">

<?php
   /* Event Name */
   $event_id = $_GET['eventid'];

   if (!isset($_POST['quicklossreport']))
   {
      echo "<br />You are not authorized to report a quick loss.<br />";
      echo "<br />Back to [<a href=\"eventinfo.php?eventid=$event_id\">Event</a>]<br />";
   }
   else
   {
      $q = "SELECT ".TBL_PLAYERS.".*, "
                    .TBL_USERS.".*"
          ." FROM ".TBL_PLAYERS.", "
                   .TBL_USERS
          ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
            ." AND (".TBL_USERS.".username = ".TBL_PLAYERS.".Name)"
          ." ORDER BY ".TBL_USERS.".nickname";
      
      $result = $database->query($q);
      $num_rows = mysql_numrows($result);
?>
<div class="news">
<h2>Quick Report</h2>
<table>
<form action="matchprocess.php" method="post">
<tr>
  <td>
    Player:
    <select name="Player">
<?php   
    for($i=0; $i<$num_rows; $i++){
      $pname  = mysql_result($result,$i, TBL_USERS.".username");
      $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
      $pnickname  = mysql_result($result,$i, TBL_USERS.".nickname");
      if($pname != $session->username)
      { 
      	echo "<option value=\"$pname\">#$prank - $pnickname</option>";
      }
     }
?>
    </select>
  </td>
</tr>
<tr>
  <td>
<?php   
    $reported_by = $session->username;
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
   include("include/footer.php");
?>