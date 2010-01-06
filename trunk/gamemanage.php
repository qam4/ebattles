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
require_once(e_PLUGIN."ebattles/include/main.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text = '';

$can_manage = 0;
if (check_class($pref['eb_mod_class'])) $can_manage = 1;
if ($can_manage == 0)
{
    header("location:".e_HTTP."index.php");
    exit();
}

$text .= "
<script type='text/javascript'>
<!--//
function changetext(v)
{
document.getElementById('gameIcon').value=v;
}
//-->
</script>
";

$text .= '
<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">
<table>
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
<tr>
<td><b>'.EB_GAME_L3.'</b></td>
<td>
<select class="tbox" name="gameid" onchange="this.form.submit()">';
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
//$text .= '<input class="button" type="submit" value="Select"/>';
$text .= '</td>';
$text .= '</tr>';
$text .= '</tbody>';
$text .= '</table>';
$text .= '</form>';

$text .= '<form id="gameform" action="'.e_PLUGIN.'ebattles/gameprocess.php?gameid='.$game_id.'" method="post">';
$text .= '<table class="fborder" style="width:95%">';
$text .= '<tbody>';
//<!-- Game Name -->
$text .= '
<tr>
<td class="forumheader3"><b>'.EB_GAME_L4.'</b></td>
<td class="forumheader3">
<input class="tbox" type="text" name="gameName" value="'.$game_name.'"/>
</td>
</tr>
';

//<!-- Game Icon -->
$text .= '
<tr>
<td class="forumheader3"><b>'.EB_GAME_L5.'</b></td>
<td class="forumheader3">
<img '.getGameIconResize($game_icon).'/>
<input class="tbox" type="text" id="gameIcon" name="gameIcon" value="'.$game_icon.'"/>
<div class="smalltext">'.EB_GAME_L6.'</div>';

$text .= "<div>";
$avatarlist[0] = "";
$handle = opendir(e_PLUGIN."ebattles/images/games_icons/");
while ($file = readdir($handle))
{
    if ($file != "." && $file != ".." && $file != "index.html" && $file != ".svn" && $file != "Games List.csv")
    {
        $avatarlist[] = $file;
    }
}
closedir($handle);

for($c = 1; $c <= (count($avatarlist)-1); $c++)
{
    $text .= '<a href="javascript:changetext(\''.$avatarlist[$c].'\')"><img src="'.e_PLUGIN.'ebattles/images/games_icons/'.$avatarlist[$c].'" style="border:0" alt="" /></a> ';
}
$text .= "
</div>
";

$text .= '
</td>
</tr>
</tbody>
</table>
';

//<!-- Save, Add new Game, Delete Game Button -->
$text .= '
<table><tr>
<td>
<input class="button" type="submit" name="gamesettingssave" value="'.EB_GAME_L7.'"/>
</td>
<td>
<input class="button" type="submit" name="gamecreate" value="'.EB_GAME_L8.'"/>
</td>
<td>
<input class="button" type="submit" name="gamedelete" value="'.EB_GAME_L9.'" onclick="return confirm(\''.EB_GAME_L10.'\');"/>
</td>
</tr>
</table>
</form>
';

$ns->tablerender(EB_GAME_L2, $text);
require_once(FOOTERF);
exit;
?>
