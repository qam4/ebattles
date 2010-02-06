<?php
/**
* ClanCreate.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(HEADERF);
$text = '';

if ((!isset($_POST['createteam']))||(!check_class($pref['eb_teams_create_class'])))
{
    $text .= '<br />'.EB_CLANS_L8.'<br />';
}
else
{
    $userid = $_POST['userid'];
    $username = $_POST['username'];

    $q = "INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)"
    ." VALUES ('Team', '$username', '$userid')";
    $result = $sql->db_Query($q);
    $last_id = mysql_insert_id();

    $q = "UPDATE ".TBL_CLANS." SET Name = '".EB_CLAN_L1." $last_id - $username' WHERE (ClanID = '$last_id')";
    $result = $sql->db_Query($q);
    header("Location: clanmanage.php?clanid=".$last_id);
}
$ns->tablerender(EB_CLANC_L2, $text);
require_once(FOOTERF);
exit;
?>
