<?php
/**
 *LadderProcess.php
 * 
 */
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(HEADERF);
$text = '';

if ((!isset($_POST['createladder']))||(!check_class($pref['eb_ladders_create_class'])))
{
   $text .= '<br />'.EB_LADDERC_L2.'<br />';
}
else
{
   $userid = $_POST['userid'];
   $username = $_POST['username'];

   $q = "INSERT INTO ".TBL_LADDERS."(Name,Password,Game,Type,Owner, Description, RankingType)"
       ." VALUES ('".EB_LADDERC_L3."', '', '1', 'One Player Ladder','$userid', '".EB_LADDERC_L4."', 'Classic')";   
   $result = $sql->db_Query($q);
   $last_id = mysql_insert_id();
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'ELO')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue)
    VALUES ('$last_id', 'Skill', 4)";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue, InfoOnly)
    VALUES ('$last_id', 'GamesPlayed', 1, 1)";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue)
    VALUES ('$last_id', 'VictoryRatio', 3)";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'WinDrawLoss')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'VictoryPercent')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'UniqueOpponents')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'OpponentsELO')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName, CategoryMaxValue, InfoOnly)
    VALUES ('$last_id', 'Streaks', 2, 1)";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'Score')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'ScoreAgainst')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'ScoreDiff')";
   $result = $sql->db_Query($q);
   $q = 
   "INSERT INTO ".TBL_STATSCATEGORIES."(Ladder, CategoryName)
    VALUES ('$last_id', 'Points')";
   $result = $sql->db_Query($q);

   $q = "UPDATE ".TBL_LADDERS." SET Name = '".EB_LADDERC_L3." $last_id - $username' WHERE (LadderID = '$last_id')";
   $result = $sql->db_Query($q);

   header("Location: laddermanage.php?LadderID=".$last_id);
   exit;
}

$ns->tablerender(EB_LADDERC_L1, $text);
require_once(FOOTERF);
exit;
?>