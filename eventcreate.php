<?php
/**
 *EventProcess.php
 * 
 */
ob_start();
include("include/main.php");

if (!isset($_POST['createevent']))
{
     echo "<br />You are not authorized to create an event.<br />";
     echo "<br />Back to [<a href=\"index.php\">Main</a>]<br />";
}
else
{
   $username = $_POST['userid'];

   $q2 = "INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Description)"
       ." VALUES ('$username event', '', '1', 'One Player Ladder','$username', 'Put a description for your event here')";   
   $result2 = $sql->db_Query($q2);
   $last_id = mysql_insert_id();
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'ELO')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'GamesPlayed')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'VictoryRatio')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'VictoryPercent')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'UniqueOpponents')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'OpponentsELO')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'Streaks')";
   $result2 = $sql->db_Query($q2);

   header("Location: eventmanage.php?eventid=".$last_id);
   // could use: printf("<script>location.href='eventmanage.php?eventid=$last_id'</script>");
}
ob_end_flush();
