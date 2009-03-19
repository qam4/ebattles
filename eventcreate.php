<?php
/**
 *EventProcess.php
 * 
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
require_once(HEADERF);
$text = '';

if (!isset($_POST['createevent']))
{
   $text .= "<br />You are not authorized to create an event.<br />";
}
else
{
   $userid = $_POST['userid'];
   $username = $_POST['username'];

   $q2 = "INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Description)"
       ." VALUES ('Event', '', '1', 'One Player Ladder','$userid', 'Put a description for your event here')";   
   $result2 = $sql->db_Query($q2);
   $last_id = mysql_insert_id();
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'ELO')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'Skill')";
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
    VALUES ('$last_id', 'WinDrawLoss')";
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
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'Score')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'ScoreAgainst')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'ScoreDiff')";
   $result2 = $sql->db_Query($q2);
   $q2 = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName)
    VALUES ('$last_id', 'Points')";
   $result2 = $sql->db_Query($q2);

   $q2 = "UPDATE ".TBL_EVENTS." SET Name = 'Event $last_id - $username' WHERE (EventID = '$last_id')";
   $result2 = $sql->db_Query($q2);

   header("Location: eventmanage.php?eventid=".$last_id);
   exit;
}

$ns->tablerender('Create Event', $text);
require_once(FOOTERF);
exit;
?>