<?php
/**
* gamemanage.php
*
* This page is for admins to
* - edit games information
* - add custom games
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text = '';

$can_manage = 0;
if (check_class($pref['eb_mod'])) $can_manage = 1;
if ($can_manage == 0)
{
    header("location:".e_HTTP."index.php");
    exit();
}

//dbg form
//print_r($_POST);    // show $_POST
//print_r($_GET);     // show $_GET




$text .= '
<table class="fborder" style="width:95%">
<tbody>
';
//<!-- Game Select -->
// Drop down list to select Games to display
$q = "SELECT ".TBL_GAMES.".*"
." FROM ".TBL_GAMES
." ORDER BY Name";
$result = $sql->db_Query($q);
$numGames = mysql_numrows($result);

if (!isset($_GET['gameid'])) $_GET['gameid'] = mysql_result($result,0 , TBL_GAMES.".GameID");
$game_id = $_GET['gameid'];

$q2 = "SELECT ".TBL_GAMES.".*"
." FROM ".TBL_GAMES
." WHERE (".TBL_GAMES.".GameID = '$game_id')";

$result2 = $sql->db_Query($q2);
$game_name  = mysql_result($result2,0 , TBL_GAMES.".Name");
$game_icon  = mysql_result($result2,0 , TBL_GAMES.".Icon");

$text .= '
<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">
<tr>
<td class="forumheader3"><b>Game</b></td>
<td class="forumheader3">
<select class="tbox" name="gameid" onChange="this.form.submit()">';
for($i=0; $i<$numGames; $i++)
{
    $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
    $gid    = mysql_result($result,$i, TBL_GAMES.".GameID");

    if ($game_id == $gid)
    {
        $text .= '<option value="'.$gid.'" selected="selected">'.htmlspecialchars($gname).'</option>';
    }
    else
    {
        $text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
    }
}
$text .= '</select>';
//$text .= '<input class="button" type="submit" value="Select"></input>';
$text .= '</td>';
$text .= '</tr>';
$text .= '</form>';

$text .= '<form action="'.e_PLUGIN.'ebattles/gameprocess.php?gameid='.$game_id.'" method="post">';
//<!-- Game Name -->
$text .= '
<tr>
<td class="forumheader3"><b>Name</b></td>
<td class="forumheader3">
<input type="text" name="gameName" value="'.$game_name.'"></input>
</td>
</tr>
';

//<!-- Game Icon -->
$text .= '
<tr>
<td class="forumheader3"><b>Icon</b></td>
<td class="forumheader3">
<img src="'.e_PLUGIN.'ebattles/images/games_icons/'.$game_icon.'" alt="'.$game_icon.'"></img>
<input type="text" name="gameIcon" value="'.$game_icon.'"></input>
Icon must be in ebattles/images/games_icons/
</td>
</tr>
';

$text .= '</tbody>';
$text .= '</table>';

//<!-- Save, Add new Game, Delete Game Button -->
$text .= '
<table><tr>
<td>
<input class="button" type="submit" name="gamesettingssave" value="Save Changes"></input>
</td>
<td>
<input class="button" type="submit" name="gamecreate" value="Create new Game"></input>
</td>
<td>
<input class="button" type="submit" name="gamedelete" value="Delete Game" onclick="return confirm(\'Are you sure you want to delete '.$game_name.'?\');"></input>
</td>
</tr></table>
';
$text .= '</form>';

$ns->tablerender('Manage Games', $text);
require_once(FOOTERF);
exit;
?>
