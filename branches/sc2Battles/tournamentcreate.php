<?php
/**
 *TournamentCreate.php
 * 
 */
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
require_once(HEADERF);
$text = '';

if ((!isset($_POST['createtournament']))||(!check_class($pref['eb_tournaments_create_class'])))
{
   $text .= '<br />'.EB_TOURNAMENTC_L2.'<br />';
}
else
{
   $userid = $_POST['userid'];
   $username = $_POST['username'];
   
   $q = "INSERT INTO ".TBL_TOURNAMENTS."(Name,Password,Game,Type,Owner, Description)"
       ." VALUES ('".EB_TOURNAMENTC_L3."', '', '1', 'Single Elimination','$userid', '".EB_TOURNAMENTC_L4."')";   
   $result = $sql->db_Query($q);
   $last_id = mysql_insert_id();

   $q = "UPDATE ".TBL_TOURNAMENTS." SET Name = '".EB_TOURNAMENTC_L3." $last_id - $username' WHERE (TournamentID = '$last_id')";
   $result = $sql->db_Query($q);
   
   header("Location: tournamentmanage.php?TournamentID=".$last_id);
   exit;
}

$ns->tablerender(EB_TOURNAMENTC_L1, $text);
require_once(FOOTERF);
exit;
?>