<?php
/**
 *clanmanage.php
 *
 * This page is for users to edit their account information
 * such as their password, email address, etc. Their
 * usernames can not be edited. When changing their
 * password, they must first confirm their current password.
 *
 */
ob_start();
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

?>
<div id="main">
<script type="text/javascript" src="./js/tabpane.js"></script>

<?php
   /* Clan Name */
   $clan_id = $_GET['clanid'];

   $q = "SELECT ".TBL_CLANS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_CLANS.", "
                .TBL_USERS
       ." WHERE (".TBL_CLANS.".ClanID = '$clan_id')"
         ." AND (".TBL_USERS.".user_id = ".TBL_CLANS.".Owner)";
 
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);

   $clan_name   = mysql_result($result,0, TBL_CLANS.".Name");
   $clan_owner  = mysql_result($result,0, TBL_CLANS.".Owner");
   $clan_owner_name   = mysql_result($result,0, TBL_USERS.".user_name");
   $clan_tag    = mysql_result($result,0, TBL_CLANS.".Tag");

   echo "<h1><a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$clan_id\">$clan_name</a> ($clan_tag)</h1>";   

   $can_manage = 0;
   if ($session->isAdmin()) $can_manage = 1;
   if ({USER_ID}==$clan_owner) $can_manage = 1;
   if ($can_manage == 0)
   {
      header("Location: index.php");
      ob_end_flush();
   }
   else
   {
      ob_end_flush();
?>


<div class="tab-pane" id="tab-pane-4">

<div class="tab-page">
<h2 class="tab">Clan Summary</h2>
<br /><br />

<?php
   echo "<p><b>Owner:</b> <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$clan_owner\">$clan_owner_name</a></p>";

   $q_2 = "SELECT ".TBL_USERS.".*"
      ." FROM ".TBL_USERS;

   $result_2 = $sql->db_Query($q_2);
   $row = mysql_fetch_array($result_2);     
   $num_rows_2 = mysql_numrows($result_2);
   
   echo "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
   echo "<table>";
   echo "<tr>";
   echo "<td><select name=\"clanowner\">\n";
   for($j=0; $j<$num_rows_2; $j++)
   {
      $uid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
      $uname  = mysql_result($result_2,$j, TBL_USERS.".user_name");

      if ($clan_owner == $uid)
      {
         echo "<option value=\"$uid\" selected=\"selected\">$uname ($uid)</option>\n";
      }
      else
      {
         echo "<option value=\"$uid\">$uname ($uid)</option>\n";
      }
   }
   echo "</select>\n";
   echo "</td>\n";
   echo "<td>";
   echo "<input type=\"hidden\" name=\"clanchangeowner\"></input>";
   echo "<input type=\"submit\" value=\"Change Owner\"></input>";
   echo "</td>";
   echo "</tr>";
   echo "</table>";
   echo "</form>";
   echo "<br />";
   echo "<form name=\"clansettingsform\" action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
?>
<table border="0" cellspacing="0" cellpadding="3">
<!-- Clan Name -->
<tr>
  <td><b>Name:</b></td>
  <td>
    <input type="text" size="40" name="clanname" value="<?php echo "$clan_name";?>"></input>
  </td>
</tr>

<!-- Clan Tag -->
<tr>
  <td><b>Tag:</b></td>
  <td>
    <input type="text" size="40" name="clantag" value="<?php echo "$clan_tag";?>"></input>
  </td>
</tr>

</table>
<!-- Save Button -->
<p align="center">
    <input type="hidden" name="clansettingssave" value="1"></input>
    <input type="submit" value="Save"></input>
</p>
</form>

</div>

<div class="tab-page">
<h2 class="tab">Clan Divisions</h2>
<br /><br />
<?php
   echo "<form name=\"clanadddivform\" action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
   $q = "SELECT ".TBL_GAMES.".*"
       ." FROM ".TBL_GAMES
       ." ORDER BY Name";
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   echo "<table>";
   echo "<tr>";
   echo "<td><select name=\"divgame\">\n";
   for($i=0; $i<$num_rows; $i++){
      $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
      $gid  = mysql_result($result,$i, TBL_GAMES.".GameId");
      echo "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
   }
   echo "</select>\n";
   echo "</td>\n";
   echo "<td>";
   echo "<input type=\"hidden\" name=\"clanadddiv\"></input>";
   echo "<input type=\"hidden\" name=\"clanowner\" value=\"$clan_owner\"></input>";
   echo "<input type=\"submit\" value=\"Add Division\"></input>";
   echo "</td>";
   echo "</tr>";
   echo "</table>";
   echo "</form>";
   echo "<br />";


   $q = "SELECT ".TBL_CLANS.".*, "
                 .TBL_DIVISIONS.".*, "
                 .TBL_USERS.".*, "
                 .TBL_GAMES.".*"
       ." FROM ".TBL_CLANS.", "
                .TBL_DIVISIONS.", "
                .TBL_USERS.", "
                .TBL_GAMES
       ." WHERE (".TBL_CLANS.".ClanID = '$clan_id')"
         ." AND (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
         ." AND (".TBL_USERS.".user_id = ".TBL_DIVISIONS.".Captain)"
         ." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";
 
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);
   for($i=0; $i<$num_rows; $i++)
   {
      $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
      $gicon  = mysql_result($result,$i , TBL_GAMES.".Icon");
      $div_id  = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");
      $div_captain  = mysql_result($result,$i, TBL_DIVISIONS.".Captain");
      $div_captain_name  = mysql_result($result,$i, TBL_USERS.".user_name");

      echo"<div class=\"news\">";
      echo "<h2><img src=\"".e_PLUGIN."ebattles/images/games_icons/$gicon\" alt=\"$gicon\"></img> $gname</h2><br />";
      echo "<p>Captain: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$div_captain\">$div_captain_name</a></p>";

      $q_2 = "SELECT ".TBL_CLANS.".*, "
                   .TBL_DIVISIONS.".*, "
                   .TBL_MEMBERS.".*, "
                   .TBL_USERS.".*, "
                   .TBL_GAMES.".*"
         ." FROM ".TBL_CLANS.", "
                  .TBL_DIVISIONS.", "
                  .TBL_USERS.", "
                  .TBL_MEMBERS.", "
                  .TBL_GAMES
         ." WHERE (".TBL_CLANS.".ClanID = '$clan_id')"
           ." AND (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
           ." AND (".TBL_DIVISIONS.".DivisionID = '$div_id')"
           ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
           ." AND (".TBL_USERS.".user_id = ".TBL_MEMBERS.".Name)"
           ." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";

      $result_2 = $sql->db_Query($q_2);
      if(!$result_2 || (mysql_numrows($result_2) < 1))
      {
         echo "<p>No members</p>";
      }
      else
      {
          $row = mysql_fetch_array($result_2);     
          $num_rows_2 = mysql_numrows($result_2);
     
          echo "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
          echo "<table>";
          echo "<tr>";
          echo "<td><select name=\"divcaptain\">\n";
          for($j=0; $j<$num_rows_2; $j++)
          {
             $mid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
             $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");

             if ($div_captain == $mid)
             {
                echo "<option value=\"$mid\" selected=\"selected\">$mname</option>\n";
             }
             else
             {
                echo "<option value=\"$mid\">$mname</option>\n";
             }
          }
          echo "</select>\n";
          echo "</td>\n";
          echo "<td>";
          echo "<input type=\"hidden\" name=\"clandiv\" value=\"$div_id\"></input>";
          echo "<input type=\"hidden\" name=\"clanchangedivcaptain\"></input>";
          echo "<input type=\"submit\" value=\"Change Captain\"></input>";
          echo "</td>";
          echo "</tr>";
          echo "</table>";
          echo "</form>";

          echo "<p>$num_rows_2 member(s)</p>";

          echo "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
          echo "<table class=\"type1\">\n";
          echo "<tr><td class=\"type1Header\"><b>Name</b></td><td class=\"type1Header\"><b>Status</b></td><td class=\"type1Header\"><b>Joined</b></td><td class=\"type1Header\"><b>Kick</b></td></tr>\n";
          for($j=0; $j<$num_rows_2; $j++)
          {
             $mid  = mysql_result($result_2,$j, TBL_MEMBERS.".MemberID");
             $muid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
             $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
             $mjoined  = mysql_result($result_2,$j, TBL_MEMBERS.".timestamp");
             $mjoined_local = $mjoined + $session->timezone_offset;
             $date  = date("d M Y",$mjoined_local);
          
             echo "<tr>\n";
             echo "<td class=\"type1Body\"><b><a class=\"type1\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$muid\">$mname</a></b></td><td class=\"type1Body\">Member</td><td class=\"type1Body\">$date</td>";

             // Checkbox to select which member to kick
             echo "<td class=\"type1Body\"><input type=\"checkbox\" name=\"del[]\" value=\"$mid\" /></td>\n";
             echo "</tr>";
          }
          echo "<tr>";
          echo "<td colspan=\"4\">";
          echo "<input type=\"submit\" name=\"kick\" value=\"Kick Selected\"></input>";
          echo "</td>\n";
          echo "</tr>";
          echo "</table>\n";
          echo "</form>";
      }
      echo"</div>";
      echo "<br />";
   }
   
   
   echo"</div>";     
   echo"</div>";     
   echo "<p>";
   echo "<br />Back to [<a href=\"".e_PLUGIN."ebattles/clans.php\">Teams</a>]<br />";
   echo "</p>";

}
?>

</div>
<script type="text/javascript">
//<![CDATA[

setupAllTabs();

//]]>
</script>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
