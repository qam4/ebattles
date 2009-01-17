<?php
   // always include the class2.php file - this is the main e107 file
   require_once("../../class2.php");
   include_once(e_PLUGIN."ebattles/include/main.php");

   // this generates all the HTML up to the start of the main section
   require_once(HEADERF);

   $text = '';

   $rowsPerPage = 5;
   /* Stats/Results */
   $q = "SELECT ".TBL_MATCHS.".*, "
                 .TBL_USERS.".*, "
                 .TBL_EVENTS.".*"
       ." FROM ".TBL_MATCHS.", "
                .TBL_USERS.", "
                .TBL_EVENTS
       ." WHERE (".TBL_MATCHS.".Event = ".TBL_EVENTS.".EventID)"
         ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
       ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
       ." LIMIT 0, $rowsPerPage";
 
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);

   if ($num_rows>0)
   {
      /* Display table contents */
      for($i=0; $i<$num_rows; $i++)
      {
         $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
         $mReportedBy  = mysql_result($result,$i, TBL_USERS.".user_id");
         $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
         $mEventID  = mysql_result($result,$i, TBL_EVENTS.".EventID");
         $mEventName  = mysql_result($result,$i, TBL_EVENTS.".Name");
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

         $text .= "$players<br>";
      }
   }


   // Ensure the pages HTML is rendered using the theme layout.
   $ns->tablerender('eBattles', $text);

   // this generates all the HTML (menus etc.) after the end of the main section
   e107_require_once(FOOTERF);
?>
