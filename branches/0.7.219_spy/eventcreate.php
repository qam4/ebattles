<?php
/**
 *EventProcess.php
 * 
 */
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(HEADERF);
$text = '';

if ((!isset($_POST['createevent']))||(!check_class($pref['eb_events_create_class'])))
{
   $text .= '<br />'.EB_EVENTC_L2.'<br />';
}
else
{
   $userid = $_POST['userid'];
   $username = $_POST['username'];

   $q2 = "INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Description)"
       ." VALUES ('".EB_EVENTC_L3."', '', '1', 'One Player Ladder','$userid', '".EB_EVENTC_L4."')";   
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

   $q2 = "UPDATE ".TBL_EVENTS." SET Name = '".EB_EVENTC_L3." $last_id - $username' WHERE (EventID = '$last_id')";
   $result2 = $sql->db_Query($q2);

   header("Location: eventmanage.php?eventid=".$last_id);
   exit;
}

$ns->tablerender(EB_EVENTC_L1, $text);
require_once(FOOTERF);
exit;
?>