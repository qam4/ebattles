<?php
/**
*ClanProcess.php
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

/*
function deleteDivisions($clan_id)
{
global $sql;
$q3 = "DELETE FROM ".TBL_DIVISIONS
." WHERE (".TBL_EVENTMODS.".Clan = '$clan_id')";
$result3 = $sql->db_Query($q3);
}
function deleteClan($clan_id)
{
global $sql;
$q3 = "DELETE FROM ".TBL_CLANS
." WHERE (".TBL_CLANS.".ClanID = '$clan_id')";
$result3 = $sql->db_Query($q3);
}
*/
/*
if(isset($_POST['clandeletediv']))
{
$clan_id = $_GET['clanid'];
$div_game = $_POST['divgame'];

$q2 = "DELETE FROM ".TBL_DIVISIONS
." WHERE (".TBL_DIVISIONS.".Clan = '$clan_id')"
."   AND (".TBL_DIVISIONS.".Game = '$div_game')";
$result2 = $sql->db_Query($q2);

echo "-- clandeletemod --<br />";
header("Location: clanmanage.php?clanid=$clan_id");
}
*/
if(isset($_POST['clanadddiv']))
{
    $clan_id = $_GET['clanid'];
    $clan_owner = $_POST['clanowner'];
    $div_game = $_POST['divgame'];

    $q2 = "SELECT ".TBL_DIVISIONS.".*"
    ." FROM ".TBL_DIVISIONS
    ." WHERE (".TBL_DIVISIONS.".Clan = '$clan_id')"
    ."   AND (".TBL_DIVISIONS.".Game  = '$div_game')";
    $result2 = $sql->db_Query($q2);
    $num_rows_2 = mysql_numrows($result2);
    if ($num_rows_2==0)
    {
        $q2 = "INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)"
        ." VALUES ('$clan_id','$div_game','$clan_owner')";
        $result2 = $sql->db_Query($q2);
    }
    //echo "-- clanadddiv --<br />";
    header("Location: clanmanage.php?clanid=$clan_id");
}

if(isset($_POST['clansettingssave']))
{
    $clan_id = $_GET['clanid'];

    /* Clan Name */
    $new_clanname = htmlspecialchars($_POST['clanname']);
    if ($new_clanname != '')
    {
        $q2 = "UPDATE ".TBL_CLANS." SET Name = '$new_clanname' WHERE (ClanID = '$clan_id')";
        $result2 = $sql->db_Query($q2);
    }
    /* Clan Tag */
    $new_clantag = htmlspecialchars($_POST['clantag']);
    if ($new_clantag != '')
    {
        $q2 = "UPDATE ".TBL_CLANS." SET Tag = '$new_clantag' WHERE (ClanID = '$clan_id')";
        $result2 = $sql->db_Query($q2);
    }
    /* Clan Password */
    $new_clanpassword = htmlspecialchars($_POST['clanpassword']);
    if ($new_clanpassword != '')
    {
        $q2 = "UPDATE ".TBL_CLANS." SET password = '$new_clanpassword' WHERE (ClanID = '$clan_id')";
        $result2 = $sql->db_Query($q2);
    }
    //echo "-- clansettingssave --<br />";
    header("Location: clanmanage.php?clanid=$clan_id");
}
if(isset($_POST['clanchangeowner']))
{
    $clan_id = $_GET['clanid'];
    $clan_owner = $_POST['clanowner'];

    /* Clan Owner */
    $q2 = "UPDATE ".TBL_CLANS." SET Owner = '$clan_owner' WHERE (ClanID = '$clan_id')";
    $result2 = $sql->db_Query($q2);

    //echo "-- clanchangeowner --<br />";
    header("Location: clanmanage.php?clanid=$clan_id");
}
if(isset($_POST['clanchangedivcaptain']))
{
    $clan_id = $_GET['clanid'];
    $clan_div = $_POST['clandiv'];
    $div_captain = $_POST['divcaptain'];

    /* Division Captain */
    $q2 = "UPDATE ".TBL_DIVISIONS." SET Captain = '$div_captain' WHERE (DivisionID = '$clan_div')";
    $result2 = $sql->db_Query($q2);

    //echo "-- clanchangedivcaptain --<br />";
    header("Location: clanmanage.php?clanid=$clan_id");
}
/*
if(isset($_POST['clandelete']))
{
    $clan_id = $_GET['clanid'];
    deleteClan($clan_id);

    //echo "-- clandelete --<br />";
    header("Location: clans.php");
}
*/
if (isset($_POST['kick']))
{
    $clan_id = $_GET['clanid'];
    if (count($_POST['del']) > 0)
    {
        $del_ids=$_POST['del'];

        for($i=0;$i<count($del_ids);$i++)
        {
            $q2 = "DELETE FROM ".TBL_MEMBERS
            ." WHERE (".TBL_MEMBERS.".MemberID = '$del_ids[$i]')";
            $result2 = $sql->db_Query($q2);
        }
    }
    //echo "-- kick --<br />";
    header("Location: clanmanage.php?clanid=$clan_id");
}

//echo "-- test clan process --<br />";
exit;
?>
