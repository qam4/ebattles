<?php
/**
 *claninfo.php
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

<?php
   /* Clan Name */
   $clan_id = $_GET['clanid'];

   if(isset($_GET['joindivision'])){
   	 $time = GMT_time();
     $div_id = $_GET['division'];
	 $q = " INSERT INTO ".TBL_MEMBERS."(Division,Name,timestamp)
	        VALUES ($div_id,'{USER_ID}',$time)";
         $sql->db_Query($q);
         header("Location: claninfo.php?clanid=$clan_id");
   }
   ob_end_flush();

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

   echo "<h1>$clan_name ($clan_tag)</h1>";
   echo "<p>Owner: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$clan_owner\">$clan_owner_name</a></p><br />";
   
   echo"<p>";
   $can_manage = 0;
   if ($session->isAdmin()) $can_manage = 1;
   if ({USER_ID}==$clan_owner) $can_manage = 1;
   if ($can_manage == 1)
     echo"<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$clan_id\">Manage Team</a><br />";
   echo"</p>";

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

      if($session->logged_in)
      {
         $q_2 = "SELECT ".TBL_MEMBERS.".*"
            ." FROM ".TBL_MEMBERS
            ." WHERE (".TBL_MEMBERS.".Division = '$div_id')"
              ." AND (".TBL_MEMBERS.".Name = '{USER_ID}')";
         $result_2 = $sql->db_Query($q_2);
         if(!$result_2 || (mysql_numrows($result_2) < 1))
         {
            echo "
            <form action=\"".e_PLUGIN."ebattles/claninfo.php\" method=\"get\">
                <input type=\"hidden\" name=\"clanid\" value=\"$clan_id\"></input>
                <input type=\"hidden\" name=\"division\" value=\"$div_id\"></input>
                <input type=\"hidden\" name=\"joindivision\" value=\"1\"></input>
                <input type=\"submit\" value=\"Join Division\"></input>
            </form>";
         }
      }

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
     
          echo "<p>$num_rows_2 member(s)</p>";

          echo "<table class=\"type1\">\n";
          echo "<tr><td class=\"type1Header\"><b>Name</b></td><td class=\"type1Header\"><b>Status</b></td><td class=\"type1Header\"><b>Joined</b></td></tr>\n";
          for($j=0; $j<$num_rows_2; $j++)
          {
             $mname  = mysql_result($result_2,$j, TBL_USERS.".user_id");
             $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
             $mjoined  = mysql_result($result_2,$j, TBL_MEMBERS.".timestamp");
             $mjoined_local = $mjoined + $session->timezone_offset;
             $date = date("d M Y",$mjoined_local);
          
             echo "<tr>\n";
             echo "<td class=\"type1Body\"><b><a class=\"type1\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mname\">$mname</a></b></td><td class=\"type1Body\">Member</td><td class=\"type1Body\">$date</td></tr>";
          
          }
          echo "</table>\n";
      }
      echo"</div>";
      echo "<br />";
   }
   echo "<p>";
   echo "<br />Back to [<a href=\"".e_PLUGIN."ebattles/clans.php\">Teams</a>]<br />";
   echo "</p>";
?>
</div>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
