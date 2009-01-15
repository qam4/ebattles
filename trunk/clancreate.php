<?php
/**
 *TeamCreate.php
 * 
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
require_once(HEADERF);
$text = '';

if (!isset($_POST['createteam']))
{
   $text .= "<br />You are not authorized to create a team.<br />";
}
else
{
   $userid = $_POST['userid'];
   $username = $_POST['username'];

   $q2 = "INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)"
       ." VALUES ('Team', '$username', '$userid')";   
   $result2 = $sql->db_Query($q2);
   $last_id = mysql_insert_id();

   $q2 = "UPDATE ".TBL_CLANS." SET Name = 'Team $last_id - $username' WHERE (ClanID = '$last_id')";
   $result2 = $sql->db_Query($q2);
   header("Location: clanmanage.php?clanid=".$last_id);
}
$ns->tablerender('Events', $text);
require_once(FOOTERF);
exit;
?>
