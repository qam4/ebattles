<?php
/**
* GameProcess.php
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

$can_manage = 0;
if (check_class($pref['eb_mod'])) $can_manage = 1;
if ($can_manage == 0)
{
    header("location:".e_HTTP."index.php");
    exit();
}

if(isset($_POST['gamesettingssave']))
{
    $game_id = $_GET['gameid'];

    /* Game Name */
    $new_gamename = htmlspecialchars($_POST['gameName']);
    if ($new_gamename != '')
    {
        $q2 = "UPDATE ".TBL_GAMES." SET Name = '$new_gamename' WHERE (GameID = '$game_id')";
        $result2 = $sql->db_Query($q2);
    }
    /* Game Icon */
    $new_gameicon = htmlspecialchars($_POST['gameIcon']);
    if ($new_gameicon != '')
    {
        $q2 = "UPDATE ".TBL_GAMES." SET Icon = '$new_gameicon' WHERE (GameID = '$game_id')";
        $result2 = $sql->db_Query($q2);
    }
    header("Location: gamemanage.php?gameid=$game_id");
}
if(isset($_POST['gamedelete']))
{
    $game_id = $_GET['gameid'];
    deleteGame($game_id);

    header("Location: gamemanage.php");
}
if (isset($_POST['gamecreate']))
{
   $q2 = "INSERT INTO ".TBL_GAMES."(Name,Icon)"
       ." VALUES ('Game Name', 'unknown.gif')";   
   $result2 = $sql->db_Query($q2);
   $last_id = mysql_insert_id();

   $q2 = "UPDATE ".TBL_GAMES." SET Name = '$last_id - Game' WHERE (GameID = '$last_id')";
   $result2 = $sql->db_Query($q2);

   header("Location: gamemanage.php?gameid=".$last_id);
   exit;
}
exit;

/***************************************************************************************
Functions
***************************************************************************************/
function deleteGame($game_id)
{
    global $sql;
    $q3 = "DELETE FROM ".TBL_GAMES
    ." WHERE (".TBL_GAMES.".GameID = '$game_id')";
    $result3 = $sql->db_Query($q3);
}

?>
