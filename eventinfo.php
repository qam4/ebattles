<?php
/**
 * EventInfo.php
 *
 */
ob_start();
include("include/main.php");
include("include/pagination.php");
include("./include/show_array.php");

define('INT_SECOND', 1);
define('INT_MINUTE', 60);
define('INT_HOUR', 3600);
define('INT_DAY', 86400);
define('INT_WEEK', 604800);

function get_formatted_timediff($then, $now = false)
{
    $now      = (!$now) ? time() : $now;
    $timediff = ($now - $then);
    $weeks    = (int) intval($timediff / INT_WEEK);
    $timediff = (int) intval($timediff - (INT_WEEK * $weeks));
    $days     = (int) intval($timediff / INT_DAY);
    $timediff = (int) intval($timediff - (INT_DAY * $days));
    $hours    = (int) intval($timediff / INT_HOUR);
    $timediff = (int) intval($timediff - (INT_HOUR * $hours));
    $mins     = (int) intval($timediff / INT_MINUTE);
    $timediff = (int) intval($timediff - (INT_MINUTE * $mins));
    $sec      = (int) intval($timediff / INT_SECOND);
    $timediff = (int) intval($timediff - ($sec * INT_SECOND));

    $str = '';
    if ( $weeks )
    {
        $str .= intval($weeks);
        $str .= ($weeks > 1) ? ' weeks' : ' week';
    }

    if ( $days )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($days);
        $str .= ($days > 1) ? ' days' : ' day';
    }

    if ( $hours )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($hours);
        $str .= ($hours > 1) ? ' hours' : ' hour';
    }

    if ( $mins )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($mins);
        $str .= ($mins > 1) ? ' minutes' : ' minute';
    }

    if ( $sec )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($sec);
        $str .= ($sec > 1) ? ' seconds' : ' second';
    }
   
    if ( !$weeks && !$days && !$hours && !$mins && !$sec )
    {
        $str .= '0 seconds';
    }
    else
    {
        $str .= '';
    }
   
    return $str;
}

?>
<div id="main">

<script type="text/javascript" src="./js/tabpane.js"></script>

<?php
   global $database;

   $time = GMT_time();
   
   /* Event Name */
   $event_id = $_GET['eventid'];
   $self = $_SERVER['PHP_SELF'];
   $file = 'cache/sql_cache_event_'.$event_id.'.txt'; 
   $file_team = 'cache/sql_cache_event_team_'.$event_id.'.txt'; 

   // how many rows to show per page
   $rowsPerPage = 20;
   $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
   $start = $rowsPerPage * $pg - $rowsPerPage;

   $q = "SELECT ".TBL_EVENTS.".*"
        ." FROM ".TBL_EVENTS
        ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";  
    $result = $database->query($q);
    $eELOdefault = mysql_result($result, 0, TBL_EVENTS.".ELO_default");
    $epassword = mysql_result($result, 0, TBL_EVENTS.".Password");

   if(isset($_GET['joinevent'])){
      if ($_GET['joinEventPassword'] == $epassword)
      {
      
	    $q = " INSERT INTO ".TBL_PLAYERS."(Event,Name,ELORanking)
	           VALUES ($event_id,'$session->username',$eELOdefault)";
            $database->query($q);
            $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
            $result = $database->query($q4);
            header("Location: eventinfo.php?eventid=$event_id");
      }
   }
   if(isset($_GET['quitevent'])){
         $q = " DELETE FROM ".TBL_PLAYERS
             ." WHERE (Event = '$event_id')"
             ."   AND (Name = '$session->username')";
         $database->query($q);
         $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
         $result = $database->query($q4);
         header("Location: eventinfo.php?eventid=$event_id");
   }
   if(isset($_GET['teamjoinevent'])){
         $div_id = $_GET['division'];
	 $q = " INSERT INTO ".TBL_TEAMS."(Event,Division)
	        VALUES ($event_id,$div_id)";
         $database->query($q);
         $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
         $result = $database->query($q4);
         header("Location: eventinfo.php?eventid=$event_id");
   }
   if(isset($_GET['jointeamevent'])){
         $team_id = $_GET['team'];
	 $q = " INSERT INTO ".TBL_PLAYERS."(Event,Name,Team,ELORanking)
	        VALUES ($event_id,'$session->username',$team_id,$eELOdefault)";
         $database->query($q);
         header("Location: eventinfo.php?eventid=$event_id");
   }
   ob_end_flush();
   
   $q = "SELECT ".TBL_EVENTS.".*, "
                 .TBL_GAMES.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_EVENTS.", "
                .TBL_GAMES.", "
                .TBL_USERS
       ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
       ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"      
       ."   AND (".TBL_USERS.".username = ".TBL_EVENTS.".Owner)";   

   $result = $database->query($q);
   $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
   $egame = mysql_result($result,0 , TBL_GAMES.".Name");
   $egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
   $egameicon = mysql_result($result,0 , TBL_GAMES.".Icon");
   $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
   $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
   $eownernickname = mysql_result($result,0 , TBL_USERS.".nickname");
   $emingames = mysql_result($result,0 , TBL_EVENTS.".nbr_games_to_rank");
   $eminteamgames = mysql_result($result,0 , TBL_EVENTS.".nbr_team_games_to_rank");
   $erules = mysql_result($result,0 , TBL_EVENTS.".Rules");
   $edescription = mysql_result($result,0 , TBL_EVENTS.".Description");
   $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
   $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
   $enextupdate = mysql_result($result,0 , TBL_EVENTS.".NextUpdate_timestamp");
   $eischanged = mysql_result($result,0 , TBL_EVENTS.".IsChanged");
   
   $eneedupdate = 0;
   if (  (($time > $enextupdate) && ($eischanged == 1))
       ||(file_exists($file) == FALSE)
       ||((file_exists($file_team) == FALSE) && (($etype == "Team Ladder")))
      )
   {
      $eneedupdate = 1;
   }
   
   if($estart!=0) 
   {
     $estart_local = $estart + $session->timezone_offset;
     $date_start = date("d M Y, h:i A",$estart_local);
   }
   else
   {
     $date_start = "-";
   }
   if($eend!=0) 
   {
     $eend_local = $eend + $session->timezone_offset;
     $date_end = date("d M Y, h:i A",$eend_local);
   }
   else
   {
     $date_end = "-";
   }
   
   $time_comment = '';
   if (  ($estart != 0)
       &&($time <= $estart)
      )
   {
      $time_comment = 'Event starts in '.get_formatted_timediff($time, $estart);
   }
   else if (  ($eend != 0)
            &&($time <= $eend)
           )
   {
      $time_comment = 'Event ends in '.get_formatted_timediff($time, $eend);
   }

   echo "<h1>$ename ($etype)</h1>";
   echo "<h2><img src=\"images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame</h2>";
   //echo "<br />";
 

   /* Update Stats */
   if ($eneedupdate == 1)
   {
   	$new_nextupdate = $time + EVENTS_UDATE_DELAY;
   	$q = "UPDATE ".TBL_EVENTS." SET NextUpdate_timestamp = $new_nextupdate WHERE (EventID = '$event_id')";
        $result = $database->query($q);
   	$enextupdate = $new_nextupdate;

   	$q = "UPDATE ".TBL_EVENTS." SET IsChanged = 0 WHERE (EventID = '$event_id')";
        $result = $database->query($q);
    	$eischanged = 0;
  	
        include("include/updatestats.php");  
   }

   echo"<div class=\"tab-pane\" id=\"tab-pane-1\">";

   echo"<div class=\"tab-page\">";
   echo"<h2 class=\"tab\">Event Info</h2>";
   echo"<br /><br />";

   if($session->logged_in)
   {
      /* Join/Quit Event */
      if ($etype == "Team Ladder")
      {
         $q = "SELECT ".TBL_DIVISIONS.".*, "
                       .TBL_CLANS.".*, "
                       .TBL_GAMES.".*, "
                       .TBL_USERS.".*"
             ." FROM ".TBL_DIVISIONS.", "
                      .TBL_CLANS.", "
                      .TBL_GAMES.", "
                      .TBL_USERS
             ." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
               ." AND (".TBL_GAMES.".GameID = '$egameid')"
               ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
               ." AND (".TBL_USERS.".username = '$session->username')"
               ." AND (".TBL_DIVISIONS.".Captain = '$session->username')";

         $result = $database->query($q);
         $num_rows = mysql_numrows($result);
         if($num_rows > 0)
         {
           for($i=0;$i < $num_rows;$i++)
           {
              $div_name  = mysql_result($result,$i, TBL_CLANS.".Name");
              $div_id    = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");

              // Is the division signed up
              $q_2 = "SELECT ".TBL_TEAMS.".*"
                  ." FROM ".TBL_TEAMS
                  ." WHERE (".TBL_TEAMS.".Event = '$event_id')"
                    ." AND (".TBL_TEAMS.".Division = '$div_id')";
                   $result_2 = $database->query($q_2);
                   $num_rows_2 = mysql_numrows($result_2);
              
              if( $num_rows_2 == 0)
              {
                 echo "Your are the captain of $div_name.";
                 echo "<br />";
                 echo "
                 <form style=\"float:left\" action=\"eventinfo.php\" method=\"get\">
                     <input type=\"hidden\" name=\"division\" value=\"$div_id\"></input>
                     <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                     <input type=\"hidden\" name=\"teamjoinevent\" value=\"1\"></input>
                     <input type=\"submit\" value=\"Team Join Event\"></input>
                 ";
                 echo '</form>';
                 echo "<br /><br /><br />";
              }
              else
              {
              }
           }
         }
      }

         // Is the user already signed up for this event?
         $q = "SELECT *"
             ." FROM ".TBL_PLAYERS
             ." WHERE (Event = '$event_id')"
             ."   AND (Name = '$session->username')";
       
         $result = $database->query($q);
         if(!$result || (mysql_numrows($result) < 1))
         {
            if ($etype == "Team Ladder")
            {
               // Is user a member of a division for that game?
               $q_2 = "SELECT ".TBL_CLANS.".*, "
                             .TBL_MEMBERS.".*, "
                             .TBL_DIVISIONS.".*, "
                             .TBL_GAMES.".*, "
                             .TBL_USERS.".*"
                   ." FROM ".TBL_CLANS.", "
                            .TBL_MEMBERS.", "
                            .TBL_DIVISIONS.", "
                            .TBL_GAMES.", "
                            .TBL_USERS
                   ." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
                     ." AND (".TBL_GAMES.".GameID = '$egameid')"
                     ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
                     ." AND (".TBL_USERS.".username = '$session->username')"
                     ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
                     ." AND (".TBL_MEMBERS.".Name = '$session->username')";
            
 
               $result_2 = $database->query($q_2);
               $num_rows_2 = mysql_numrows($result_2);
               if(!$result_2 || ( $num_rows_2 < 1))
               {
               	  echo "You are not a member of any team for this game.<br />";
               }
               else
               {
                  for($i=0;$i < $num_rows_2;$i++)
                  {
                     $clan_name  = mysql_result($result_2,$i , TBL_CLANS.".Name");
                     $div_id  = mysql_result($result_2,$i , TBL_DIVISIONS.".DivisionID");
                     
                     $q_3 = "SELECT ".TBL_CLANS.".*, "
                                   .TBL_TEAMS.".*, "
                                   .TBL_DIVISIONS.".*"
                         ." FROM ".TBL_CLANS.", "
                                  .TBL_TEAMS.", "
                                  .TBL_DIVISIONS
                         ." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
                           ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
                           ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
                           ." AND (".TBL_TEAMS.".Event = '$event_id')";                  
                     $result_3 = $database->query($q_3);
                     if(!$result_3 || (mysql_numrows($result_3) < 1))
                     {
                         echo "Your team $clan_name has not signed up to this event.<br />";
                         echo "Please contact your captain.<br /><br />";               	  
                     }
                     else
                     {
                         $team_id  = mysql_result($result_3,0 , TBL_TEAMS.".TeamID");
                         echo "Your team $clan_name has signed up to this event.";
                         echo "<br />";
                         echo "
                         <form style=\"float:left\" action=\"eventinfo.php\" method=\"get\">
                             <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                             <input type=\"hidden\" name=\"team\" value=\"$team_id\"></input>
                             <input type=\"hidden\" name=\"jointeamevent\" value=\"1\"></input>
                             <input type=\"submit\" value=\"Join Event\"></input>
                         </form>
                         ";
                         echo "<br /><br /><br />";
                     }
                  }
               }
            }
            else
            {
               if ($epassword != "")
               {
                  echo "Event Password:";
                  echo "
                  <form action=\"eventinfo.php\" method=\"get\">
                      <input type=\"password\" title=\"Enter the password\" name=\"joinEventPassword\"></input>
                      <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                      <input type=\"hidden\" name=\"joinevent\" value=\"1\"></input>
                      <input type=\"submit\" value=\"Join Event\"></input>
                  </form>
                  ";
               }
               else
               {
                  echo "
                  <form action=\"eventinfo.php\" method=\"get\">
                      <input type=\"hidden\" name=\"joinEventPassword\" value=\"\"></input>
                      <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                      <input type=\"hidden\" name=\"joinevent\" value=\"1\"></input>
                      <input type=\"submit\" value=\"Join Event\"></input>
                  </form>
                  ";
               }            }
         }
         else
         {
            echo "You are signed up for this event.<br />";
            /* 
            // Removed "quit button", because this erases the player from database.
                echo "
                <form action=\"eventinfo.php\" method=\"get\">
                    <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                    <input type=\"hidden\" name=\"quitevent\" value=\"1\"></input>
                    <input type=\"submit\" value=\"Quit Event\"></input>
                </form>
                ";     
            */
         }   
      
   }
   else
   {   	
      echo "Please log in to participate to this event.<br />";
   }
      
   echo "<hr />";
   echo "<p>";
   echo"Owner: <a href=\"userinfo.php?user=$eowner\">$eownernickname</a><br />";

   $can_manage = 0;
   if ($session->isAdmin()) $can_manage = 1;
   if ($session->username==$eowner) $can_manage = 1;
   if ($can_manage == 1)
     echo"<a href=\"eventmanage.php?eventid=$event_id\">Manage event</a><br />";
   echo"</p>";

   echo "<p>";
   $q = "SELECT ".TBL_EVENTMODS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_EVENTMODS.", "
                .TBL_USERS
       ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
       ."   AND (".TBL_USERS.".username = ".TBL_EVENTMODS.".Name)";   
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);
   echo "Moderators:<br />";
   for($i=0; $i<$num_rows; $i++){
      $modname  = mysql_result($result,$i, TBL_EVENTMODS.".Name");
      $modnickname  = mysql_result($result,$i, TBL_USERS.".nickname");
      echo "- <a href=\"userinfo.php?user=$modname\">$modnickname</a><br />";
   }
   echo"</p>";
   echo "<p>Starts: $date_start<br />Ends: $date_end</p>";
   echo "<p>$time_comment</p>";
   echo "<p>Description: $edescription</p>";
   echo "<p>Rules: $erules</p>";

   echo"</div>";

   echo"<div class=\"tab-page\">";
   echo"<h2 class=\"tab\">Standings for this Ladder</h2>";
   echo"<br /><br />";

   $enextupdate_local = $enextupdate + $session->timezone_offset;
   $date_nextupdate = date("d M Y, h:i A",$enextupdate_local);
   if (($time < $enextupdate) && ($eischanged == 1))
   {
     echo"Next Update: $date_nextupdate<br />";
   }
   echo"<br />";

   if ($etype == "Team Ladder")
   {
      /* Update Stats */
      if ($eneedupdate == 1)
      {
        include("include/updateteamstats.php");  
      }
       
      /* Nbr Teams */
      $q = "SELECT COUNT(*) as NbrTeams"
          ." FROM ".TBL_TEAMS
          ." WHERE (Event = '$event_id')";
      $result = $database->query($q);
      $row = mysql_fetch_array($result);     
      $nbrteams = $row['NbrTeams'];     
      echo"<div class=\"news\">";
      echo"<h2>Teams Standings</h2>";
      echo"<p>";
      echo"$nbrteams teams<br />";
      echo"Minimum $eminteamgames team matches to rank.";
      echo"</p>";

      $stats = unserialize(implode('',file($file_team))); 
      // debug print array
      $num_columns = count($stats[0]) - 1;
      $nbr_rows = count($stats);
      html_show_table($stats, $nbr_rows, $num_columns);

      echo "</div>";
   }
   echo "<br />";


   /* Nbr players */
   $q = "SELECT COUNT(*) as NbrPlayers"
       ." FROM ".TBL_PLAYERS
       ." WHERE (Event = '$event_id')";
   $result = $database->query($q);
   $row = mysql_fetch_array($result);     
   $nbrplayers = $row['NbrPlayers'];     
   $totalPages = $nbrplayers;

   echo"<div class=\"news\">";
   echo"<h2>Players Standings</h2>";

   echo"<p>";
   echo"$nbrplayers players<br />";
   echo"Minimum $emingames matches to rank.<br />";
   echo"</p>";

   /* My Position */
   $q = "SELECT *"
       ." FROM ".TBL_PLAYERS
       ." WHERE (Event = '$event_id')"
       ."   AND (Name = '$session->username')";
 
   $result = $database->query($q);
   $can_report = 0;
   $can_report_quickloss = 0;
   if(!$result || (mysql_numrows($result) < 1))
   {}
   else
   {
      $row = mysql_fetch_array($result);     
      $prank = $row['Rank'];
      
      $link_page = ceil($prank/$rowsPerPage);
      echo "<p>";
      echo "<a href=\"$self?eventid=$event_id&amp;pg=$link_page\">Show My Position #$prank</a><br />";    
      echo "</p>";
      $time = GMT_time();
      // Is the event started, and not ended
      if (  ($eend == 0)
          ||(  ($eend >= $time)
             &&($estart <= $time)
            )
         )
      {
        $can_report = 1;
        $can_report_quickloss = 1;
      }

   }
      
   // Is the user admin?
   if ($session->isAdmin()) $can_report = 1;
   // Is the user event owner?
   if ($session->username==$eowner) $can_report = 1;
   // Is the user a moderator?
   $q_2 = "SELECT ".TBL_EVENTMODS.".*"
       ." FROM ".TBL_EVENTMODS
       ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
       ."   AND (".TBL_EVENTMODS.".Name = '$session->username')";   
   $result_2 = $database->query($q_2);
   $num_rows_2 = mysql_numrows($result_2);
   if ($num_rows_2>0) $can_report = 1;
   
   if ($nbrplayers < 2)
   {
        $can_report = 0;
        $can_report_quickloss = 0;
   }

   if(($can_report_quickloss != 0)||($can_report != 0))
   {
      echo "<table>";
      echo "<tr>";
      if($can_report_quickloss != 0)
      {
         echo "<td>";
         echo "<form action=\"quickreport.php?eventid=$event_id\" method=\"post\">";
         echo "<input type=\"submit\" name=\"quicklossreport\" value=\"Quick Loss Report\"></input>";
         echo "</form>";
         echo "</td>";
      }
      if($can_report != 0)
      {
         echo "<td>";
         echo "<form action=\"matchreport.php?eventid=$event_id\" method=\"post\">";
         echo "<input type=\"submit\" name=\"matchreport\" value=\"Match Report\"></input>";
         echo "</form>";
         echo "</td>";
      }
      echo "</tr>";
      echo "</table>";
   }
   echo "<br />";

   $stats = unserialize(implode('',file($file))); 
   $num_columns = count($stats[0]) - 1;
   
   // Paginate the statistics array
   $max_row = count($stats);
   $stats_paginate = array($stats[0]);
   $nbr_rows = 1;
   for ($i = $start+1; $i < $start + $rowsPerPage + 1; $i++)
   {
     if ($i < $max_row)
     {
       $stats_paginate[] = $stats[$i];
       $nbr_rows ++;
     }
   }
   html_show_table($stats_paginate, $nbr_rows, $num_columns);
   
   echo "<br />";

   // print the navigation link
   paginate($rowsPerPage, $pg, $totalPages);
   echo "<br />";
   echo"</div>";
   echo"</div>";
   echo "<br />";


   echo"<div class=\"tab-page\">";
   echo"<h2 class=\"tab\">Latest Matches</h2>";
   echo"<br /><br />";

   $q = "SELECT COUNT(*) as NbrMatches"
       ." FROM ".TBL_MATCHS
       ." WHERE (Event = '$event_id')";
   $result = $database->query($q);
   $row = mysql_fetch_array($result);     
   $nbrmatches = $row['NbrMatches'];
   echo"<p>";
   echo"$nbrmatches matches played<br />";
   echo"</p>";

   $rowsPerPage = 5;
   /* Stats/Results */
   $q = "SELECT ".TBL_MATCHS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_MATCHS.", "
                .TBL_USERS
       ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
         ." AND (".TBL_USERS.".username = ".TBL_MATCHS.".ReportedBy)"
       ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
       ." LIMIT 0, $rowsPerPage";
 
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);

   if ($num_rows>0)
   {
      /* Display table contents */
      echo "<table class=\"type1\">\n";
      echo "<tr><td class=\"type1Header\" style=\"width:120px\"><b>Match ID</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Reported By</b></td><td class=\"type1Header\"><b>Players</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Date</b></td></tr>\n";
      for($i=0; $i<$num_rows; $i++)
      {
         $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
         $mReportedBy  = mysql_result($result,$i, TBL_MATCHS.".ReportedBy");
         $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".nickname");
         $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
         $mTime_local = $mTime + $session->timezone_offset;
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
               ." AND (".TBL_USERS.".username = ".TBL_PLAYERS.".Name)"
             ." ORDER BY ".TBL_SCORES.".Player_Rank";

         $result2 = $database->query($q2);
         $num_rows2 = mysql_numrows($result2);
         $pnickname = '';
         $players = '';
         for($j=0; $j<$num_rows2; $j++)
         {
            $pnickname  = mysql_result($result2,$j, TBL_USERS.".nickname");
            $pname  = mysql_result($result2,$j, TBL_USERS.".username");
            if ($j==0)
              $players = "<a class=\"type1\" href=\"userinfo.php?user=$pname\">$pnickname</a>";
            else
              $players = $players.", <a class=\"type1\" href=\"userinfo.php?user=$pname\">$pnickname</a>";
         }

         echo "<tr>\n";
         echo "<td class=\"type1Body\"><b>$mID</b> <a class=\"type1\" href=\"matchinfo.php?eventid=$event_id&amp;matchid=$mID\">(Show details)</a></td><td class=\"type1Body\"><a class=\"type1\" href=\"userinfo.php?user=$mReportedBy\">$mReportedByNickName</a></td><td class=\"type1Body\">$players</td><td class=\"type1Body\">$date</td></tr>";
      }
      echo "</table><br />\n"; 
   }
   echo "[<a href=\"eventmatchs.php?eventid=$event_id\">Show all Matches</a>]";
 
   echo "<br />";
   echo"</div>";
   echo"</div>";

/* Link back to main */
echo "<p>";
echo "<br />Back to [<a href=\"index.php\">Main</a>]<br />";
echo "</p>";

?>
</div>

<script type="text/javascript">
//<![CDATA[

setupAllTabs();

//]]>
</script>

<?php
include("include/footer.php");
?>
