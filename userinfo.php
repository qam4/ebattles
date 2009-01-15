<?php
/**
 * UserInfo.php
 *
 * This page is for users to view their account information
 * with a link added for them to edit the information.
 *
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");
?>
<div id="main">
<script type="text/javascript" src="./js/tabpane.js"></script>

<?php
/* Requested Username error checking */
$req_user = trim($_GET['user']);
if(!$req_user || strlen($req_user) == 0 ||
   !eregi("^([0-9a-z])+$", $req_user) ||
   !$sql->usernameTaken($req_user)){
   die("Username not registered");
}

/* Logged in user viewing own account */
if(strcmp(USERID,$req_user) == 0){
   echo "<h1>My Account</h1>";
}
/* Visitor not viewing own account */
else{
   echo "<h1>User Info</h1>";

   if(check_class(e_UC_MEMBER))
   {
     echo "<p><a href=\"".e_PLUGIN."ebattles/pm.php?action=send&amp;to=$req_user\">Send a Message</a></p>\n";
   }
}

?>
<div class="tab-pane" id="tab-pane-5">

<div class="tab-page">
<h2 class="tab">Profile</h2>
<?php
echo "<p>";
/* Display requested user information */
$req_user_info = $sql->getUserInfo($req_user);

/* Username */
echo "<b>Username: ".$req_user_info['username']."</b><br />";
/* Username */
echo "<b>Nickname: ".$req_user_info['name']."</b><br />";

/**
 * Note: when you add your own fields to the users table
 * to hold more information, like homepage, location, etc.
 * they can be easily accessed by the user info array.
 *
 * $session->user_info['location']; (for logged in users)
 *
 * ..and for this page,
 *
 * $req_user_info['location']; (for any user)
 */
/* If logged in user viewing own account, give link to edit */
if(strcmp(USERID,$req_user) == 0){
   echo "<br /><a href=\"".e_PLUGIN."ebattles/useredit.php\">Edit Account Information</a><br />";
}
echo "</p>";
echo "</div>";

/* Display list of events */
?>
<div class="tab-page">
<h2 class="tab">Events</h2>
<?php
if(strcmp(USERID,$req_user) == 0){
   echo "<form action=\"".e_PLUGIN."ebattles/eventcreate.php\" method=\"post\">";
   echo "<input type=\"hidden\" name=\"userid\" value=\"$req_user\"></input>";
   echo "<input class=\"button\" type=\"submit\" name=\"createevent\" value=\"Create new event\"></input>";
   echo "</form>";
}
echo "<h2>Player</h2>";
$q = " SELECT *"
    ." FROM ".TBL_PLAYERS.", "
             .TBL_EVENTS.", "
             .TBL_GAMES
    ." WHERE (".TBL_PLAYERS.".Name = '$req_user')"
    ."   AND (".TBL_PLAYERS.".Event = ".TBL_EVENTS.".EventID)"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";
    
$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
 
if ($num_rows>0)
{
   /* Display table contents */
   echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
       echo "<tr>";
       echo "<td>";
       echo "Name";
       echo "</td>";
       echo "<td>";
       echo "Rank";
       echo "</td>";
       echo "<td>";
       echo "W/L";
       echo "</td>";
       echo "<td>";
       echo "Status";
       echo "</td>";
       echo "</tr>";
   
   for($i=0; $i<$num_rows; $i++)
   {
       $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
       $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
       $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
       $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
       $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
       $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
       $pwinloss  = mysql_result($result,$i, TBL_PLAYERS.".Win")."/".mysql_result($result,$i, TBL_PLAYERS.".Loss");
       echo "<tr>";
       echo "<td>";
       echo "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
       echo "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame";
       echo "</td>";
       echo "<td>";
       echo "$prank";
       echo "</td>";
       echo "<td>";
       echo "$pwinloss";
       echo "</td>";
       echo "<td>";
       if($eowner == $req_user)
       {
         echo "Owner";
         if ($eowner == USERID)
         {
         	 echo " (<a href=\"".e_PLUGIN."ebattles/eventmanage.php?eventid=$eid\">Manage</a>)";
         }
       }
       else
       {
         echo "Member";
       }
   
       echo "</td>";    echo "</tr>";
   }
echo "</table>";
}

echo "<h2>Owner</h2>";
$q = " SELECT *"
    ." FROM ".TBL_EVENTS.", "
             .TBL_GAMES
    ." WHERE (".TBL_EVENTS.".Owner = '$req_user')"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";
    
$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
 
if ($num_rows>0)
{
   /* Display table contents */
   echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
       echo "<tr>";
       echo "<td>";
       echo "Name";
       echo "</td>";
       echo "<td>";
       echo "Status";
       echo "</td>";
       echo "</tr>";
   
   for($i=0; $i<$num_rows; $i++)
   {
       $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
       $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
       $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
       $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
       $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
       echo "<tr>";
       echo "<td>";
       echo "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
       echo "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame";
       echo "</td>";
       echo "<td>";
       if($eowner == $req_user)
       {
         echo "Owner";
         if ($eowner == USERID)
         {
         	 echo " (<a href=\"".e_PLUGIN."ebattles/eventmanage.php?eventid=$eid\">Manage</a>)";
         }
       }
       else
       {
         echo "Member";
       }
       echo "</td>";
       echo "</tr>";
   }
   echo "</table>";
}

echo "<h2>Moderator</h2>";
$q = " SELECT *"
    ." FROM ".TBL_EVENTMODS.", "
             .TBL_EVENTS.", "
             .TBL_GAMES
    ." WHERE (".TBL_EVENTMODS.".Name = '$req_user')"
    ."   AND (".TBL_EVENTMODS.".Event = ".TBL_EVENTS.".EventID)"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";
    
$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
 
if ($num_rows>0)
{
   /* Display table contents */
   echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
       echo "<tr>";
       echo "<td>";
       echo "Name";
       echo "</td>";
       echo "<td>";
       echo "Status";
       echo "</td>";
       echo "</tr>";
   
   for($i=0; $i<$num_rows; $i++)
   {
       $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
       $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
       $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
       $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
       $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
       echo "<tr>";
       echo "<td>";
       echo "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
       echo "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame";
       echo "</td>";
       echo "<td>";
       if($eowner == $req_user)
       {
         echo "Owner";
         if ($eowner == USERID)
         {
         	 echo " (<a href=\"".e_PLUGIN."ebattles/eventmanage.php?eventid=$eid\">Manage</a>)";
         }
       }
       else
       {
         echo "Member";
       }
       echo "</td>";
       echo "</tr>";
   }
   echo "</table>";
}
echo "</div>";

/* Display list of divisions */
echo "<br /><br />";

?>
<div class="tab-page">
<h2 class="tab">Teams membership</h2>
<?php

if(strcmp(USERID,$req_user) == 0){
   echo "<form action=\"".e_PLUGIN."ebattles/clancreate.php\" method=\"post\">";
   echo "<input type=\"hidden\" name=\"userid\" value=\"$req_user\"></input>";
   $text .= "<input type=\"hidden\" name=\"username\" value=\"".USERNAME."\"></input>";
   echo "<input class=\"button\" type=\"submit\" name=\"createteam\" value=\"Create new team\"></input>";
   echo "</form>";
}

echo "<h2>Member</h2>";
$q = "SELECT ".TBL_CLANS.".*, "
             .TBL_DIVISIONS.".*, "
             .TBL_MEMBERS.".*, "
             .TBL_USERS.".*, "
             .TBL_GAMES.".*"
   ." FROM ".TBL_CLANS.", "
            .TBL_DIVISIONS.", "
            .TBL_USERS.", "
            .TBL_MEMBERS.", "
            .TBL_GAMES
   ." WHERE (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
     ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
     ." AND (".TBL_MEMBERS.".Name = ".TBL_USERS.".user_id)"
     ." AND (".TBL_USERS.".user_id = '$req_user')"
     ." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";
    
$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
 
if ($num_rows>0)
{
   echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
       echo "<tr>";
       echo "<td>";
       echo "Name";
       echo "</td>";
       echo "<td>";
       echo "Status";
       echo "</td>";
       echo "</tr>";
   /* Display table contents */
   for($i=0; $i<$num_rows; $i++)
   {
       $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
       $dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
       $dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
       $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
       $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
       echo "<tr>";
       echo "<td>";
       echo "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
       echo "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$dgameicon\" alt=\"$egameicon\"></img> $dgame";
       echo "</td>";
       echo "<td>";
       if($cowner == $req_user)
       {
         echo "Owner";
         if ($cowner == USERID)
         {
         	 echo " (<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$cid\">Manage</a>)";
         }
       }
       else
       {
         echo "Member";
       }
       echo "</td>";
       echo "</tr>";
   
   }
   echo "</table>";
}


echo "<h2>Owner</h2>";
$q = "SELECT ".TBL_CLANS.".*, "
             .TBL_USERS.".*"
   ." FROM ".TBL_CLANS.", "
            .TBL_USERS
   ." WHERE (".TBL_CLANS.".Owner = ".TBL_USERS.".user_id)"
     ." AND (".TBL_USERS.".user_id = '$req_user')";
    
$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
 
if ($num_rows>0)
{
   echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
       echo "<tr>";
       echo "<td>";
       echo "Name";
       echo "</td>";
       echo "<td>";
       echo "Status";
       echo "</td>";
       echo "</tr>";
   /* Display table contents */
   for($i=0; $i<$num_rows; $i++)
   {
       $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
       $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
       $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
       echo "<tr>";
       echo "<td>";
       echo "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
       echo "</td>";
       echo "<td>";
       if($cowner == $req_user)
       {
         echo "Owner";
         if ($cowner == USERID)
         {
         	 echo " (<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$cid\">Manage</a>)";
         }
       }
       else
       {
         echo "Member";
       }
       echo "</td>";
       echo "</tr>";
   
   }
   echo "</table>";
}

echo "<h2>Captain</h2>";
$q = "SELECT ".TBL_CLANS.".*, "
             .TBL_DIVISIONS.".*, "
             .TBL_GAMES.".*"
   ." FROM ".TBL_CLANS.", "
            .TBL_DIVISIONS.", "
            .TBL_GAMES
   ." WHERE (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
     ." AND (".TBL_GAMES.".GameId = ".TBL_DIVISIONS.".Game)"
     ." AND (".TBL_DIVISIONS.".Captain = '$req_user')";
    
$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
 
if ($num_rows>0)
{
   echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
       echo "<tr>";
       echo "<td>";
       echo "Name";
       echo "</td>";
       echo "<td>";
       echo "Status";
       echo "</td>";
       echo "</tr>";
   /* Display table contents */
   for($i=0; $i<$num_rows; $i++)
   {
       $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
       $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
       $dcaptain  = mysql_result($result,$i, TBL_DIVISIONS.".Captain");
       $dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
       $dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");


       echo "<tr>";
       echo "<td>";
       echo "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
       echo "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$dgameicon\" alt=\"$egameicon\"></img> $dgame";
       echo "</td>";
       echo "<td>";
       if($cowner == $req_user)
       {
         echo "Owner";
         if ($cowner == USERID)
         {
         	 echo " (<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$cid\">Manage</a>)";
         }
       }
       else
       {
         echo "Member";
       }
       echo "</td>";
       echo "</tr>";
   
   }
   echo "</table>";
}
echo "</div>";


?>
<div class="tab-page">
<h2 class="tab">Matches</h2>
<?php

   /* set pagination variables */
   $rowsPerPage = 5;
   $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
   $start = $rowsPerPage * $pg - $rowsPerPage;
         
   /* Stats/Results */
   $q = "SELECT count(*) "
    ." FROM ".TBL_MATCHS.", "
             .TBL_SCORES.", "
             .TBL_PLAYERS
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
      ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
      ." AND (".TBL_PLAYERS.".Name = '$req_user')";
   $result = $sql->db_Query($q);
   $totalPages = mysql_result($result, 0);

   $q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
                          .TBL_USERS.".*"
    ." FROM ".TBL_MATCHS.", "
             .TBL_USERS.", "
             .TBL_SCORES.", "
             .TBL_PLAYERS
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
      ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
      ." AND (".TBL_PLAYERS.".Name = '$req_user')"
      ." AND (".TBL_MATCHS.".ReportedBy = ".TBL_USERS.".user_id)"
    ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
    ." LIMIT $start, $rowsPerPage";
 
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);

   if ($num_rows>0)
   {
      /* Display table contents */
      echo "<table class=\"type1Border\">\n";
      echo "<tr><td class=\"type1Header\" style=\"width:120px\"><b>Match ID</b></td><td class=\"type1Header\"><b>Event</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Reported By</b></td><td class=\"type1Header\"><b>Players</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Date</b></td></tr>\n";
      for($i=0; $i<$num_rows; $i++){
         $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
         $mReportedBy  = mysql_result($result,$i, TBL_MATCHS.".ReportedBy");
         $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
         $mEvent  = mysql_result($result,$i, TBL_MATCHS.".Event");
         $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
         //$date = date("d M Y, h:i:s A",$mTime);
         $mTime_local = $mTime + GMT_TIMEOFFSET;
         $date = date("d M Y",$mTime_local);

         $q2 = "SELECT ".TBL_EVENTS.".*, "
                       .TBL_GAMES.".*"
             ." FROM ".TBL_EVENTS.", "
                      .TBL_GAMES
             ." WHERE (".TBL_EVENTS.".eventid = '$mEvent')"
             ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";       
         
         $result2 = $sql->db_Query($q2);
         $ename = mysql_result($result2,0 , TBL_EVENTS.".Name");
         $egame = mysql_result($result2,0 , TBL_GAMES.".Name");

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

         echo "<tr>\n";
         echo "<td class=\"type1Body2\"><b>$mID</b> <a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$mEvent&amp;matchid=$mID\">(Show details)</a></td><td class=\"type1Body2\"><a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$mEvent\">$ename</a></td><td class=\"type1Body2\"><a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mReportedBy\">$mReportedByNickName</a></td><td class=\"type1Body2\">$players</td><td class=\"type1Body2\">$date</td></tr>";

      
   }
      echo "</table><br />\n"; 
   }
 
   $text .= paginate($rowsPerPage, $pg, $totalPages);

echo "</div>";

echo "</div>";
/* Link back to main */
echo "<p>";
echo "<br />Back to [<a href=\"".e_PLUGIN."ebattles/index.php\">Main</a>]<br />";
echo "</p>";

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
