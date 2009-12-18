<?php
/**
*ClanProcess.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/clan.php');

if(isset($_POST['clandelete']))
{
    $clan_id = $_GET['clanid'];

    $q_ClanScores = "SELECT ".TBL_DIVISIONS.".*, "
    .TBL_TEAMS.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_SCORES.".*"
    ." FROM ".TBL_DIVISIONS.", "
    .TBL_TEAMS.", "
    .TBL_PLAYERS.", "
    .TBL_SCORES
    ." WHERE (".TBL_DIVISIONS.".Clan = '$clan_id')"
    ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
    ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
    $result_ClanScores = $sql->db_Query($q_ClanScores);
    $numClanScores = mysql_numrows($result_ClanScores);
    if ($numClanScores == 0)
    {
        // Delete players, teams, members, divisions and clan
        $q_ClanDivs = "SELECT ".TBL_DIVISIONS.".*"
        ." FROM ".TBL_DIVISIONS
        ." WHERE (".TBL_DIVISIONS.".Clan = '$clan_id')";
        $result_ClanDivs = $sql->db_Query($q_ClanDivs);
        $numClanDivs = mysql_numrows($result_ClanDivs);
        for ($i = 0; $i < $numClanDivs; $i ++)
        {
            $div_id = mysql_result($result_ClanDivs, $i, TBL_DIVISIONS.".DivisionID");
            deleteDivPlayers($div_id);
            deleteDivTeams($div_id);
            deleteDivMembers($div_id);
            deleteDiv($div_id);
        }

        deleteClan($clan_id);
    }
    //echo "-- clandelete --<br />";
    header("Location: clans.php");
}
if(isset($_POST['clandeletediv']))
{
    $clan_id = $_GET['clanid'];
    $div_id = $_POST['clandiv'];

    $q_DivScores = "SELECT ".TBL_DIVISIONS.".*, "
    .TBL_TEAMS.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_SCORES.".*"
    ." FROM ".TBL_DIVISIONS.", "
    .TBL_TEAMS.", "
    .TBL_PLAYERS.", "
    .TBL_SCORES
    ." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
    ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
    ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
    $result_DivScores = $sql->db_Query($q_DivScores);
    $numDivScores = mysql_numrows($result_DivScores);
    if ($numDivScores == 0)
    {
        // Delete players, teams, members and divison
        deleteDivPlayers($div_id);
        deleteDivTeams($div_id);
        deleteDivMembers($div_id);
        deleteDiv($div_id);
    }
    echo "-- clandeletediv --<br />";
    header("Location: clanmanage.php?clanid=$clan_id");
}
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
    $q2 = "UPDATE ".TBL_CLANS." SET password = '$new_clanpassword' WHERE (ClanID = '$clan_id')";
    $result2 = $sql->db_Query($q2);

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
//fm: Not good
// We can not delete members w/o deleting the corresponding players.
// And we can delete players only if they have not scored yet.
// Therefore, we can only delete members if they have not played in a match yet.
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
