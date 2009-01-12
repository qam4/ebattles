<?php
/**
 *TeamCreate.php
 * 
 */
ob_start();
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

if (!isset($_POST['createteam']))
{
     echo "<br />You are not authorized to create an team.<br />";
     echo "<br />Back to [<a href=\"".e_PLUGIN."ebattles/index.php\">Main</a>]<br />";
}
else
{
   $userid = $_POST['userid'];

   $q2 = "INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)"
       ." VALUES ('$userid team', '$userid', '$userid')";   
   $result2 = $sql->db_Query($q2);
   $last_id = mysql_insert_id();

   header("Location: clanmanage.php?clanid=".$last_id);
}
ob_end_flush();
