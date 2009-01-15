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
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text = '';

/* Clan Name */
$clan_id = $_GET['clanid'];

if (!$clan_id)
{
   $text .= "<br />Error.<br />";
}
else
{
   $text .='<script type="text/javascript" src="./js/tabpane.js"></script>';

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

   $text .= "<h1><a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$clan_id\">$clan_name</a> ($clan_tag)</h1>";   

   $can_manage = 0;
   if (check_class(e_UC_MAINADMIN)) $can_manage = 1;
   if (USERID==$clan_owner) $can_manage = 1;
   if ($can_manage == 0)
   {
      $text .= "<br />Error.<br />";
   }
   else
   { 	
      $text .= '
         <div class="tab-pane" id="tab-pane-4">
         
         <div class="tab-page">
         <div class="tab">Clan Summary</div>
      ';
      
      $text .= "<p><b>Owner:</b> <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$clan_owner\">$clan_owner_name</a></p>";
      
      $q_2 = "SELECT ".TBL_USERS.".*"
         ." FROM ".TBL_USERS;
      
      $result_2 = $sql->db_Query($q_2);
      $row = mysql_fetch_array($result_2);     
      $num_rows_2 = mysql_numrows($result_2);
      
      $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
      $text .= "<table>";
      $text .= "<tr>";
      $text .= "<td><select name=\"clanowner\">\n";
      for($j=0; $j<$num_rows_2; $j++)
      {
         $uid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
         $uname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
      
         if ($clan_owner == $uid)
         {
            $text .= "<option value=\"$uid\" selected=\"selected\">$uname ($uid)</option>\n";
         }
         else
         {
            $text .= "<option value=\"$uid\">$uname ($uid)</option>\n";
         }
      }
      $text .= "</select>\n";
      $text .= "</td>\n";
      $text .= "<td>";
      $text .= "<input type=\"hidden\" name=\"clanchangeowner\"></input>";
      $text .= "<input class=\"button\" type=\"submit\" value=\"Change Owner\"></input>";
      $text .= "</td>";
      $text .= "</tr>";
      $text .= "</table>";
      $text .= "</form>";
      $text .= "<br />";
      $text .= "<form name=\"clansettingsform\" action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";

      $text .= '
         <table border="0" cellspacing="0" cellpadding="3">
         <!-- Clan Name -->
         <tr>
           <td><b>Name:</b></td>
           <td>
             <input type="text" size="40" name="clanname" value="'.$clan_name.'"></input>
           </td>
         </tr>
         
         <!-- Clan Tag -->
         <tr>
           <td><b>Tag:</b></td>
           <td>
             <input type="text" size="40" name="clantag" value="'.$clan_tag.'"></input>
           </td>
         </tr>
         
         </table>
         <!-- Save Button -->
         <p align="center">
             <input type="hidden" name="clansettingssave" value="1"></input>
             <input class="button" type="submit" value="Save"></input>
         </p>
         </form>
         
         </div>
      ';

      $text .= '
         <div class="tab-page">
         <div class="tab">Clan Divisions</div>
      ';

      $text .= "<form name=\"clanadddivform\" action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
      $q = "SELECT ".TBL_GAMES.".*"
          ." FROM ".TBL_GAMES
          ." ORDER BY Name";
      $result = $sql->db_Query($q);
      /* Error occurred, return given name by default */
      $num_rows = mysql_numrows($result);
      $text .= "<table>";
      $text .= "<tr>";
      $text .= "<td><select name=\"divgame\">\n";
      for($i=0; $i<$num_rows; $i++){
         $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
         $gid  = mysql_result($result,$i, TBL_GAMES.".GameId");
         $text .= "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
      }
      $text .= "</select>\n";
      $text .= "</td>\n";
      $text .= "<td>";
      $text .= "<input type=\"hidden\" name=\"clanadddiv\"></input>";
      $text .= "<input type=\"hidden\" name=\"clanowner\" value=\"$clan_owner\"></input>";
      $text .= "<input class=\"button\" type=\"submit\" value=\"Add Division\"></input>";
      $text .= "</td>";
      $text .= "</tr>";
      $text .= "</table>";
      $text .= "</form>";
      $text .= "<br />";
      
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
      
         $text .="<div class=\"news\">";
         $text .= "<h2><img src=\"".e_PLUGIN."ebattles/images/games_icons/$gicon\" alt=\"$gicon\"></img> $gname</h2>";
         $text .= "<p>Captain: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$div_captain\">$div_captain_name</a></p>";
      
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
            $text .= "<p>No members</p>";
         }
         else
         {
             $row = mysql_fetch_array($result_2);     
             $num_rows_2 = mysql_numrows($result_2);
        
             $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
             $text .= "<table>";
             $text .= "<tr>";
             $text .= "<td><select name=\"divcaptain\">\n";
             for($j=0; $j<$num_rows_2; $j++)
             {
                $mid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
      
                if ($div_captain == $mid)
                {
                   $text .= "<option value=\"$mid\" selected=\"selected\">$mname</option>\n";
                }
                else
                {
                   $text .= "<option value=\"$mid\">$mname</option>\n";
                }
             }
             $text .= "</select>\n";
             $text .= "</td>\n";
             $text .= "<td>";
             $text .= "<input type=\"hidden\" name=\"clandiv\" value=\"$div_id\"></input>";
             $text .= "<input type=\"hidden\" name=\"clanchangedivcaptain\"></input>";
             $text .= "<input class=\"button\" type=\"submit\" value=\"Change Captain\"></input>";
             $text .= "</td>";
             $text .= "</tr>";
             $text .= "</table>";
             $text .= "</form>";
      
             $text .= "<p>$num_rows_2 member(s)</p>";
      
             $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
             $text .= "<table class=\"type1Border\">\n";
             $text .= "<tr><td class=\"type1Header\"><b>Name</b></td><td class=\"type1Header\"><b>Status</b></td><td class=\"type1Header\"><b>Joined</b></td><td class=\"type1Header\"><b>Kick</b></td></tr>\n";
             for($j=0; $j<$num_rows_2; $j++)
             {
                $mid  = mysql_result($result_2,$j, TBL_MEMBERS.".MemberID");
                $muid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
                $mjoined  = mysql_result($result_2,$j, TBL_MEMBERS.".timestamp");
                $mjoined_local = $mjoined + GMT_TIMEOFFSET;
                $date  = date("d M Y",$mjoined_local);
             
                $text .= "<tr>\n";
                $text .= "<td class=\"type1Body2\"><b><a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$muid\">$mname</a></b></td><td class=\"type1Body2\">Member</td><td class=\"type1Body2\">$date</td>";
      
                // Checkbox to select which member to kick
                $text .= "<td class=\"type1Body2\"><input type=\"checkbox\" name=\"del[]\" value=\"$mid\" /></td>\n";
                $text .= "</tr>";
             }
             $text .= "<tr>";
             $text .= "<td colspan=\"4\">";
             $text .= "<input class=\"button\" type=\"submit\" name=\"kick\" value=\"Kick Selected\"></input>";
             $text .= "</td>\n";
             $text .= "</tr>";
             $text .= "</table>\n";
             $text .= "</form>";
         }
         $text .="</div>";
         $text .= "<br />";
      }
      
      
      $text .="</div>";     
      $text .="</div>";     
      $text .= "<p>";
      $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/clans.php\">Teams</a>]<br />";
      $text .= "</p>";
   }

   $text .= '
      <script type="text/javascript">
      //<![CDATA[
      
      setupAllTabs();
      
      //]]>
      </script>
   ';

}
$ns->tablerender('Manage Team', $text);
require_once(FOOTERF);
exit;
?>
