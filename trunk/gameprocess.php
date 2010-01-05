<?php
/**
* GameProcess.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");

echo '
<html>
<head>
<style type="text/css">
<!--
.percents {
background: #FFF;
position:absolute;
text-align: center;
}
-->
</style>
</head>
<body>
';


$can_manage = 0;
if (check_class($pref['eb_mod_class'])) $can_manage = 1;
if ($can_manage == 0)
{
    header("location:".e_HTTP."index.php");
    exit();
}

// GameManage Process
if(isset($_POST['gamesettingssave']))
{
    $game_id = $_GET['gameid'];

    /* Game Name */
    $new_gamename = htmlspecialchars($_POST['gameName']);
    if ($new_gamename != '')
    {
        $q = "UPDATE ".TBL_GAMES." SET Name = '$new_gamename' WHERE (GameID = '$game_id')";
        $result = $sql->db_Query($q);
    }
    /* Game Icon */
    $new_gameicon = htmlspecialchars($_POST['gameIcon']);
    if ($new_gameicon != '')
    {
        $q = "UPDATE ".TBL_GAMES." SET Icon = '$new_gameicon' WHERE (GameID = '$game_id')";
        $result = $sql->db_Query($q);
    }
    header("Location: gamemanage.php?gameid=$game_id");
}
if(isset($_POST['gamedelete']))
{
    $game_id = $_GET['gameid'];
    deleteGame($game_id);

    header("Location: gamesmanage.php");
}
if (isset($_POST['gamecreate']))
{
    $q = "INSERT INTO ".TBL_GAMES."(Name,Icon)"
    ." VALUES ('Game Name', 'unknown.gif')";
    $result = $sql->db_Query($q);
    $last_id = mysql_insert_id();

    $q = "UPDATE ".TBL_GAMES." SET Name = '$last_id - Game' WHERE (GameID = '$last_id')";
    $result = $sql->db_Query($q);

    header("Location: gamemanage.php?gameid=".$last_id);
    exit;
}
// GamesManage Process
if(isset($_POST['delete_game']) && $_POST['delete_game']!="")
{
    $game_id = $_POST['delete_game'];
    deleteGame($game_id);
    header("Location: {$_SERVER['HTTP_REFERER']}");
}

if(isset($_POST['delete_selected_games']))
{
    if (count($_POST['game_sel']) > 0)
    {
        $del_ids=$_POST['game_sel'];
        foreach($del_ids as $game_id)
        {
            deleteGame($game_id);
        }
    }
    header("Location: {$_SERVER['HTTP_REFERER']}");
}

if(isset($_POST['delete_all_games']))
{
    $q = "SELECT ".TBL_GAMES.".*"
    ." FROM ".TBL_GAMES;
    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    for($i=0; $i<$num_rows; $i++)
    {
        $game_id  = mysql_result($result,$i, TBL_GAMES.".GameID");
        deleteGame($game_id);
    }
    header("Location: {$_SERVER['HTTP_REFERER']}");
}

if(isset($_POST['update_selected_games']))
{
    if (count($_POST['game_sel']) > 0)
    {
        $del_ids=$_POST['game_sel'];
        foreach($del_ids as $game_id)
        {
            updateGame($game_id);
        }
    }
    header("Location: {$_SERVER['HTTP_REFERER']}");
}

if(isset($_POST['update_all_games']))
{
    updateAllGames();
    //header("Location: {$_SERVER['HTTP_REFERER']}");
    echo '<META HTTP-EQUIV="Refresh" Content="0; URL=gamesmanage.php">';
}

if(isset($_POST['add_games']))
{
    insertGames();
    header("Location: {$_SERVER['HTTP_REFERER']}");
}

exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* deleteGame - Delete a game from the database
*/
function deleteGame($game_id)
{
    global $sql;

    //fm: Should check if the game is used in a team or event?
    // Do not delete game 1 (unknown game)
    if ($game_id != 1)
    {
        $q = "DELETE FROM ".TBL_GAMES
        ." WHERE (".TBL_GAMES.".GameID = '$game_id')";
        $result = $sql->db_Query($q);
    }
}
/**
* updateGame - Update a game from the database, using info from Games List.csv
*/
function updateGame($game_id)
{
    global $sql;

    // Get info from database the game is already in database
    $query = "SELECT ".TBL_GAMES.".*"
    ." FROM ".TBL_GAMES
    ." WHERE (".TBL_GAMES.".GameID = '$game_id')";
    $result = $sql->db_Query($query);

    $gname  = mysql_result($result,0 , TBL_GAMES.".Name");

    if($file_handle = fopen(e_PLUGIN."ebattles/images/games_icons/Games List.csv", "r"))
    {
        $line_of_text = fgetcsv($file_handle, 1024); // header
        while (!feof($file_handle) ) {
            $line_of_text = fgetcsv($file_handle, 1024);

            $shortname = addslashes($line_of_text[0]);
            $longname  = addslashes($line_of_text[1]);
            $icon  = addslashes($line_of_text[2]);

            if ($gname==$longname)
            {
                $query =
                "UPDATE ".TBL_GAMES
                ." SET Icon='$icon'"
                ." WHERE (".TBL_GAMES.".GameID = '$game_id')";
                $result = $sql->db_Query($query);
                break;
            }
        }
        fclose($file_handle);
    }
}
/**
* updateAllGames - Update all games in the database, using info from Games List.csv
*/
function updateAllGames()
{
    global $sql;

    // Output a 'waiting message'
    if (ob_get_level() == 0) {
        ob_start();
    }
    echo str_pad('Please wait while this task completes... ',4096)."<br />\n";

    $games_info = array();
    $index = 0;
    if($file_handle = fopen(e_PLUGIN."ebattles/images/games_icons/Games List.csv", "r"))
    {
        $line_of_text = fgetcsv($file_handle, 1024); // header
        while (!feof($file_handle) ) {
            $line_of_text = fgetcsv($file_handle, 1024);

            $games_info[$index] =array (
            'shortname' => addslashes($line_of_text[0]),
            'longname'  => addslashes($line_of_text[1]),
            'icon'      => addslashes($line_of_text[2])
            );
            $index ++;
        }
        fclose($file_handle);
    }

    // Get info from database the game is already in database
    $query = "SELECT ".TBL_GAMES.".*"
    ." FROM ".TBL_GAMES;
    $result = $sql->db_Query($query);
    $num_rows = mysql_numrows($result);
    for ($i = 0; $i<$num_rows; $i++)
    {
        set_time_limit(10);
        $gname  = mysql_result($result,$i , TBL_GAMES.".Name");
        $gid  = mysql_result($result,$i , TBL_GAMES.".GameID");

        $search_game = array_searchRecursive( $gname, $games_info, false);

        if ($search_game)
        {
            $q_2 =
            "UPDATE ".TBL_GAMES
            ." SET Icon='".$games_info[$search_game[0]]['icon']."'"
            ." WHERE (".TBL_GAMES.".GameID = '$gid')";
            $result_2 = $sql->db_Query($q_2);
            //usleep(100);
        }
        echo '<div class="percents">' . number_format(100*$i/$num_rows, 0, '.', '') . '%&nbsp;complete</div>';
        echo str_pad('',4096)."\n";
        ob_flush();
    }
    echo "<br>Done.";
    ob_end_flush();
}

/**
* insertGames - Insert games in database, using info from Games List.csv
*/
function insertGames()
{
    global $sql;
    // Insert Games in database
    if($file_handle = fopen(e_PLUGIN."ebattles/images/games_icons/Games List.csv", "r"))
    {
        $line_of_text = fgetcsv($file_handle, 1024); // header
        while (!feof($file_handle) ) {
            $line_of_text = fgetcsv($file_handle, 1024);

            $shortname = addslashes($line_of_text[0]);
            $longname  = addslashes($line_of_text[1]);
            $icon  = addslashes($line_of_text[2]);

            // Check if the game is already in database
            $query = "SELECT ".TBL_GAMES.".*"
            ." FROM ".TBL_GAMES
            ." WHERE (".TBL_GAMES.".Name = '$longname')";
            $result = $sql->db_Query($query);
            $num_rows = mysql_numrows($result);
            if ($num_rows==0)
            {
                $query =
                "INSERT INTO ".TBL_GAMES."(Name, Icon)
                VALUES ('$longname', '$icon')";
                $result = $sql->db_Query($query);
            }
        }
        fclose($file_handle);
    }
}

?>
