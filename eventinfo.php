<?php
/**
 * EventInfo.php
 *
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");
include_once(e_PLUGIN."ebattles/include/show_array.php");

define('INT_SECOND', 1);
define('INT_MINUTE', 60);
define('INT_HOUR', 3600);
define('INT_DAY', 86400);
define('INT_WEEK', 604800);

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text = '
<script type="text/javascript" src="./js/tabpane.js"></script>
';

global $sql;

$time = GMT_time();

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
	   header("Location: ./events.php");
	   exit();
}
else
{
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
    $result = $sql->db_Query($q);
    $eELOdefault = mysql_result($result, 0, TBL_EVENTS.".ELO_default");
    $epassword = mysql_result($result, 0, TBL_EVENTS.".Password");

   if(isset($_GET['joinevent'])){
      if ($_GET['joinEventPassword'] == $epassword)
      {
      
	    $q = " INSERT INTO ".TBL_PLAYERS."(Event,Name,ELORanking)
	           VALUES ($event_id,".USERID.",$eELOdefault)";
            $sql->db_Query($q);
            $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
            $result = $sql->db_Query($q4);
            header("Location: eventinfo.php?eventid=$event_id");
      }
   }
   if(isset($_GET['quitevent'])){
         $q = " DELETE FROM ".TBL_PLAYERS
             ." WHERE (Event = '$event_id')"
             ."   AND (Name = ".USERID.")";
         $sql->db_Query($q);
         $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
         $result = $sql->db_Query($q4);
         header("Location: eventinfo.php?eventid=$event_id");
   }
   if(isset($_GET['teamjoinevent'])){
         $div_id = $_GET['division'];
	 $q = " INSERT INTO ".TBL_TEAMS."(Event,Division)
	        VALUES ($event_id,$div_id)";
         $sql->db_Query($q);
         $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
         $result = $sql->db_Query($q4);
         header("Location: eventinfo.php?eventid=$event_id");
   }
   if(isset($_GET['jointeamevent'])){
         $team_id = $_GET['team'];
	 $q = " INSERT INTO ".TBL_PLAYERS."(Event,Name,Team,ELORanking)
	        VALUES ($event_id,".USERID.",$team_id,$eELOdefault)";
         $sql->db_Query($q);
         header("Location: eventinfo.php?eventid=$event_id");
   }
   
   $q = "SELECT ".TBL_EVENTS.".*, "
                 .TBL_GAMES.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_EVENTS.", "
                .TBL_GAMES.", "
                .TBL_USERS
       ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
       ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"      
       ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTS.".Owner)";   

   $result = $sql->db_Query($q);
   $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
   $egame = mysql_result($result,0 , TBL_GAMES.".Name");
   $egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
   $egameicon = mysql_result($result,0 , TBL_GAMES.".Icon");
   $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
   $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
   $eownername = mysql_result($result,0 , TBL_USERS.".user_name");
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
     $estart_local = $estart + GMT_TIMEOFFSET;
     $date_start = date("d M Y, h:i A",$estart_local);
   }
   else
   {
     $date_start = "-";
   }
   if($eend!=0) 
   {
     $eend_local = $eend + GMT_TIMEOFFSET;
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

   $text .= "<h1>$ename ($etype)</h1>";
   $text .= "<h2><img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame</h2>";
 

   /* Update Stats */
   if ($eneedupdate == 1)
   {
   	$new_nextupdate = $time + EVENTS_UDATE_DELAY;
   	$q = "UPDATE ".TBL_EVENTS." SET NextUpdate_timestamp = $new_nextupdate WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);
   	$enextupdate = $new_nextupdate;

   	$q = "UPDATE ".TBL_EVENTS." SET IsChanged = 0 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);
    	$eischanged = 0;
  	
        include_once(e_PLUGIN."ebattles/include/updatestats.php");  
   }

   $text .="<div class=\"tab-pane\" id=\"tab-pane-1\">";

   $text .="<div class=\"tab-page\">";
   $text .="<div class=\"tab\">Event Info</div>";

   $text .= "<p>";
   if(check_class(e_UC_MEMBER))
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
               ." AND (".TBL_USERS.".user_id = ".USERID.")"
               ." AND (".TBL_DIVISIONS.".Captain = ".USERID.")";

         $result = $sql->db_Query($q);
         $num_rows = mysql_numrows($result);
         if($num_rows > 0)
         {
           $text .= "<table>";
           for($i=0;$i < $num_rows;$i++)
           {
              $div_name  = mysql_result($result,$i, TBL_CLANS.".Name");
              $div_id    = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");

              // Is the division signed up
              $q_2 = "SELECT ".TBL_TEAMS.".*"
                  ." FROM ".TBL_TEAMS
                  ." WHERE (".TBL_TEAMS.".Event = '$event_id')"
                    ." AND (".TBL_TEAMS.".Division = '$div_id')";
                   $result_2 = $sql->db_Query($q_2);
                   $num_rows_2 = mysql_numrows($result_2);
              
              $text .= "<tr>";
              if( $num_rows_2 == 0)
              {
                 $text .= "<td>Your are the captain of $div_name.</td>";
                 $text .= "<td>
                 <form action=\"".e_PLUGIN."ebattles/eventinfo.php\" method=\"get\">
                     <input type=\"hidden\" name=\"division\" value=\"$div_id\"></input>
                     <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                     <input type=\"hidden\" name=\"teamjoinevent\" value=\"1\"></input>
                     <input class=\"button\" type=\"submit\" value=\"Team Join Event\"></input>
                 ";
                 $text .= '</form>';
                 $text .= "</td>";
              }
              else
              {
                 $text .= "<td>Your team $div_name is signed up for this event.</td>";
              }
              $text .= "</tr>";
           }
           $text .= "</table>";
         }
      }

      // Is the user already signed up for this event?
      $q = "SELECT *"
          ." FROM ".TBL_PLAYERS
          ." WHERE (Event = '$event_id')"
          ."   AND (Name = ".USERID.")";
      
      $result = $sql->db_Query($q);
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
                  ." AND (".TBL_USERS.".user_id = ".USERID.")"
                  ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
                  ." AND (".TBL_MEMBERS.".Name = ".USERID.")";
         
 
            $result_2 = $sql->db_Query($q_2);
            $num_rows_2 = mysql_numrows($result_2);
            if(!$result_2 || ( $num_rows_2 < 1))
            {
            	  $text .= "You are not a member of any team for this game.<br />";
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
                  $result_3 = $sql->db_Query($q_3);
                  if(!$result_3 || (mysql_numrows($result_3) < 1))
                  {
                      $text .= "Your team $clan_name has not signed up to this event.<br />";
                      $text .= "Please contact your captain.<br /><br />";               	  
                  }
                  else
                  {
                      $team_id  = mysql_result($result_3,0 , TBL_TEAMS.".TeamID");
                      $text .= "Your team $clan_name has signed up to this event.<br />";
                      $text .= "
                      <form style=\"float:left\" action=\"".e_PLUGIN."ebattles/eventinfo.php\" method=\"get\">
                          <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                          <input type=\"hidden\" name=\"team\" value=\"$team_id\"></input>
                          <input type=\"hidden\" name=\"jointeamevent\" value=\"1\"></input>
                          <input class=\"button\" type=\"submit\" value=\"Join Event\"></input>
                      </form>
                      ";
                  }
               }
            }
         }
         else
         {
            if ($epassword != "")
            {
               $text .= "Event Password:";
               $text .= "
               <form action=\"".e_PLUGIN."ebattles/eventinfo.php\" method=\"get\">
                   <input type=\"password\" title=\"Enter the password\" name=\"joinEventPassword\"></input>
                   <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                   <input type=\"hidden\" name=\"joinevent\" value=\"1\"></input>
                   <input class=\"button\" type=\"submit\" value=\"Join Event\"></input>
               </form>
               ";
            }
            else
            {
               $text .= "
               <form action=\"".e_PLUGIN."ebattles/eventinfo.php\" method=\"get\">
                   <input type=\"hidden\" name=\"joinEventPassword\" value=\"\"></input>
                   <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                   <input type=\"hidden\" name=\"joinevent\" value=\"1\"></input>
                   <input class=\"button\" type=\"submit\" value=\"Join Event\"></input>
               </form>
               ";
            }            
         }
      }
      else
      {
         $text .= "You are signed up for this event.<br />";
         /* 
         // Removed "quit button", because this erases the player from database.
             $text .= "
             <form action=\"".e_PLUGIN."ebattles/eventinfo.php\" method=\"get\">
                 <input type=\"hidden\" name=\"eventid\" value=\"$event_id\"></input>
                 <input type=\"hidden\" name=\"quitevent\" value=\"1\"></input>
                 <input class=\"button\" type=\"submit\" value=\"Quit Event\"></input>
             </form>
             ";     
         */
      }   
   }
   else
   {   	
      $text .= "Please log in to participate to this event.<br />";
   }
   $text .= "</p>";      
      
   $text .= "<p>";
   $text .="Owner: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$eowner\">$eownername</a><br />";
   $can_manage = 0;
   if (check_class(e_UC_MAINADMIN)) $can_manage = 1;
   if (USERID==$eowner) $can_manage = 1;
   if ($can_manage == 1)
     $text .="<a href=\"".e_PLUGIN."ebattles/eventmanage.php?eventid=$event_id\">Click here to Manage event</a><br />";
   $text .="</p>";

   $text .= "<p>";
   $q = "SELECT ".TBL_EVENTMODS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_EVENTMODS.", "
                .TBL_USERS
       ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
       ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTMODS.".Name)";   
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);
   $text .= "Moderators:<br />";
   for($i=0; $i<$num_rows; $i++){
      $modname  = mysql_result($result,$i, TBL_EVENTMODS.".Name");
      $modname  = mysql_result($result,$i, TBL_USERS.".user_name");
      $text .= "- <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$modname\">$modname</a><br />";
   }
   $text .="</p>";
   
   $text .= "<p>Starts: $date_start<br />Ends: $date_end</p>";
   $text .= "<p>$time_comment</p>";
   $text .= "<p>Description: $edescription</p>";
   $text .= "<p>Rules: $erules</p>";

   $text .="</div>";

   $text .="<div class=\"tab-page\">";
   $text .="<div class=\"tab\">Standings for this Ladder</div>";

   $enextupdate_local = $enextupdate + GMT_TIMEOFFSET;
   $date_nextupdate = date("d M Y, h:i A",$enextupdate_local);
   if (($time < $enextupdate) && ($eischanged == 1))
   {
     $text .="Next Update: $date_nextupdate<br />";
   }

   if ($etype == "Team Ladder")
   {
      /* Update Stats */
      if ($eneedupdate == 1)
      {
        include_once(e_PLUGIN."ebattles/include/updateteamstats.php");  
      }
       
      /* Nbr Teams */
      $q = "SELECT COUNT(*) as NbrTeams"
          ." FROM ".TBL_TEAMS
          ." WHERE (Event = '$event_id')";
      $result = $sql->db_Query($q);
      $row = mysql_fetch_array($result);     
      $nbrteams = $row['NbrTeams'];     
      $text .="<div class=\"news\">";
      $text .="<h2>Teams Standings</h2>";
      $text .="<p>";
      $text .="$nbrteams teams<br />";
      $text .="Minimum $eminteamgames team matches to rank.";
      $text .="</p>";

      $stats = unserialize(implode('',file($file_team))); 
      // debug print array
      $num_columns = count($stats[0]) - 1;
      $nbr_rows = count($stats);
      $text .= html_show_table($stats, $nbr_rows, $num_columns);

      $text .= "</div>";
   }

   /* Nbr players */
   $q = "SELECT COUNT(*) as NbrPlayers"
       ." FROM ".TBL_PLAYERS
       ." WHERE (Event = '$event_id')";
   $result = $sql->db_Query($q);
   $row = mysql_fetch_array($result);     
   $nbrplayers = $row['NbrPlayers'];     
   $totalPages = $nbrplayers;

   $text .="<div class=\"news\">";
   $text .="<h2>Players Standings</h2>";

   $text .="<p>";
   $text .="$nbrplayers players<br />";
   $text .="Minimum $emingames matches to rank.<br />";
   $text .="</p>";

   /* My Position */
   $q = "SELECT *"
       ." FROM ".TBL_PLAYERS
       ." WHERE (Event = '$event_id')"
       ."   AND (Name = ".USERID.")";
 
   $result = $sql->db_Query($q);
   $can_report = 0;
   $can_report_quickloss = 0;
   if(!$result || (mysql_numrows($result) < 1))
   {}
   else
   {
      $row = mysql_fetch_array($result);     
      $prank = $row['Rank'];
      
      $link_page = ceil($prank/$rowsPerPage);
      $text .= "<p>";
      $text .= "<a href=\"$self?eventid=$event_id&amp;pg=$link_page\">Show My Position #$prank</a><br />";    
      $text .= "</p>";
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
   if (check_class(e_UC_MAINADMIN)) $can_report = 1;
   // Is the user event owner?
   if (USERID==$eowner) $can_report = 1;
   // Is the user a moderator?
   $q_2 = "SELECT ".TBL_EVENTMODS.".*"
       ." FROM ".TBL_EVENTMODS
       ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
       ."   AND (".TBL_EVENTMODS.".Name = ".USERID.")";   
   $result_2 = $sql->db_Query($q_2);
   $num_rows_2 = mysql_numrows($result_2);
   if ($num_rows_2>0) $can_report = 1;
   
   if ($nbrplayers < 2)
   {
        $can_report = 0;
        $can_report_quickloss = 0;
   }

   if(($can_report_quickloss != 0)||($can_report != 0))
   {
      $text .= "<table>";
      $text .= "<tr>";
      if($can_report_quickloss != 0)
      {
         $text .= "<td>";
         $text .= "<form action=\"".e_PLUGIN."ebattles/quickreport.php?eventid=$event_id\" method=\"post\">";
         $text .= "<input class=\"button\" type=\"submit\" name=\"quicklossreport\" value=\"Quick Loss Report\"></input>";
         $text .= "</form>";
         $text .= "</td>";
      }
      if($can_report != 0)
      {
         $text .= "<td>";
         $text .= "<form action=\"".e_PLUGIN."ebattles/matchreport.php?eventid=$event_id\" method=\"post\">";
         $text .= "<input class=\"button\" type=\"submit\" name=\"matchreport\" value=\"Match Report\"></input>";
         $text .= "</form>";
         $text .= "</td>";
      }
      $text .= "</tr>";
      $text .= "</table>";
   }
   $text .= "<br />";

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
   $text .= html_show_table($stats_paginate, $nbr_rows, $num_columns);
   
   $text .= "<br />";

   // print the navigation link
   $text .= paginate($rowsPerPage, $pg, $totalPages);
   $text .= "<br />";
   $text .="</div>";
   $text .="</div>";

   $text .="<div class=\"tab-page\">";
   $text .="<div class=\"tab\">Latest Matches</div>";

   $q = "SELECT COUNT(*) as NbrMatches"
       ." FROM ".TBL_MATCHS
       ." WHERE (Event = '$event_id')";
   $result = $sql->db_Query($q);
   $row = mysql_fetch_array($result);     
   $nbrmatches = $row['NbrMatches'];
   $text .="<p>";
   $text .="$nbrmatches matches played<br />";
   $text .="</p>";

   $rowsPerPage = 5;
   /* Stats/Results */
   $q = "SELECT ".TBL_MATCHS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_MATCHS.", "
                .TBL_USERS
       ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
         ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
       ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
       ." LIMIT 0, $rowsPerPage";
 
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);

   if ($num_rows>0)
   {
      /* Display table contents */
      $text .= "<table class=\"type1Border\">\n";
      $text .= "<tr><td class=\"type1Header\" style=\"width:120px\"><b>Match ID</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Reported By</b></td><td class=\"type1Header\"><b>Players</b></td><td class=\"type1Header\" style=\"width:90px\"><b>Date</b></td></tr>\n";
      for($i=0; $i<$num_rows; $i++)
      {
         $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
         $mReportedBy  = mysql_result($result,$i, TBL_MATCHS.".ReportedBy");
         $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
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
         $text .= "<td class=\"type1Body2\"><b>$mID</b> <a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$event_id&amp;matchid=$mID\">(Show details)</a></td><td class=\"type1Body2\"><a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mReportedBy\">$mReportedByNickName</a></td><td class=\"type1Body2\">$players</td><td class=\"type1Body2\">$date</td></tr>";
      }
      $text .= "</table><br />\n"; 
   }
   $text .= "[<a href=\"".e_PLUGIN."ebattles/eventmatchs.php?eventid=$event_id\">Show all Matches</a>]";
 
   $text .= "<br />";
   $text .="</div>";
   $text .="</div>";

   $text .= '
   </div>
   
   <script type="$text/javascript">
   //<![CDATA[
   setupAllTabs();
   //]]>
   </script>
   ';
}

$ns->tablerender('Event Information', $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
 Functions
***************************************************************************************/
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
