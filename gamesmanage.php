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
require_once(e_PLUGIN."ebattles/include/paginator.class.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text .="
<script type='text/javascript' src='./js/tabpane.js'></script>
<script type='text/javascript'>
<!--//
function selectAll(x) {
for(var i=0,l=x.form.length; i<l; i++)
if(x.form[i].type == 'checkbox' && x.form[i].name != 'sAll')
x.form[i].checked=x.form[i].checked?false:true
}
//-->
<!--//
function buttonval(v)
{
document.getElementById('delete_game').value=v;
document.getElementById('gamesform').submit();
}
//-->
</script>
";

$can_manage = 0;
if (check_class($pref['eb_mod_class'])) $can_manage = 1;
if ($can_manage == 0)
{
    header("location:".e_HTTP."index.php");
    exit();
}

$text .= '
<div class="tab-pane" id="tab-pane-13">
';
/**
* Display Games List
*/
$text .= '
<div class="tab-page">
<div class="tab">Games</div>
';
displayGames();
$text .= '</div>
</div>

<script type="text/javascript">
//<![CDATA[
setupAllTabs();
//]]>
</script>
';

$ns->tablerender('Games', $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayGames - Displays the games database table
*/
function displayGames(){
    global $pref;
    global $sql;
    global $text;
    global $session;
    $pages = new Paginator;

    $array = array(
    'id'   => array('ID', TBL_GAMES.'.GameID'),
    'icon'   => array('Icon', TBL_GAMES.'.Icon'),
    'game'   => array('Game', TBL_GAMES.'.Name')
    );

    if (!isset($_GET['orderby'])) $_GET['orderby'] = 'game';
    $orderby=$_GET['orderby'];

    $sort = "ASC";
    if(isset($_GET["sort"]) && !empty($_GET["sort"]))
    {
        $sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
    }

    $q = "SELECT count(*) "
    ." FROM ".TBL_GAMES;
    $result = $sql->db_Query($q);

    /*
    require_once(e_PLUGIN."ebattles/include/show_db_results.php");
    show_db_results($result);
    */
    $numGames = mysql_result($result, 0);
    $totalItems = $numGames;
    $pages->items_total = $totalItems;
    $pages->mid_range = eb_PAGINATION_MIDRANGE;
    $pages->paginate();

    $text .="<div class=\"spacer\">";
    $text .="<p>";
    $text .="$numGames games<br />";
    $text .="</p>";
    $text .="</div>";

    $orderby_array = $array["$orderby"];
    $q = "SELECT ".TBL_GAMES.".*"
    ." FROM ".TBL_GAMES
    ." ORDER BY $orderby_array[1] $sort"
    ." $pages->limit";
    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if(!$result || ($num_rows < 0)){
        $text .= "Error displaying info";
        return;
    }
    if($num_rows == 0){
        $text .= "No Games";
    }
    else
    {
        // Paginate
        $text .= "<br />";
        $text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
        $text .= '<span style="float:right">';
        // Go To Page
        $text .= $pages->display_jump_menu();
        $text .= '&nbsp;&nbsp;&nbsp;';
        // Items per page
        $text .= $pages->display_items_per_page();
        $text .= '</span><br /><br />';

        /* Display table contents */
        $text .= '<form id="gamesform" action="'.e_PLUGIN.'ebattles/gameprocess.php" method="post">';
        $text .= '<table class="fborder" style="width:95%"><tbody>';
        $text .= '<tr>';
        $text .= '<td class="forumheader"><input class="tbox" type="checkbox" name="sAll" onclick="selectAll(this)" /> (Select all)</td>';
        foreach($array as $opt=>$opt_array)
        $text .= '<td class="forumheader"><a href="'.e_PLUGIN.'ebattles/gamesmanage.php?orderby='.$opt.'&amp;sort='.$sort.'">'.$opt_array[0].'</a></td>';
        $text .= '<td class="forumheader">Options';
        $text .= '<input type="hidden" id="delete_game" name="delete_game" value=""/></td></tr>';
        for($i=0; $i<$num_rows; $i++){
            $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
            $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
            $gicon  = mysql_result($result,$i, TBL_GAMES.".Icon");

            $text .= '<tr>';
            $text .= '<td class="forumheader3"><input class="tbox" type="checkbox" name="game_sel[]" value="'.$gid.'" /></td>';
            $text .= '<td class="forumheader3">'.$gid.'</td>';
            $text .= '<td class="forumheader3"><img '.getGameIconResize($gicon).' title="'.$gicon.'"/></td>';
            $text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/gamemanage.php?gameid='.$gid.'">'.$gname.'</a></td>';
            $text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/gamemanage.php?gameid='.$gid.'"><img src="'.e_PLUGIN.'ebattles/images/pencil.png" alt="Edit Game" title="Edit Game"/></a>';
            $text .= '<a href="javascript:buttonval(\''.$gid.'\');" title="Delete Game" onclick="return confirm(\'Are you sure you want to delete this game?\')"><img src="'.e_PLUGIN.'ebattles/images/cross.png" alt="Delete Game"/></a>';
            $text .= '</td>';
            $text .= '</tr>';
        }
        $text .= '</tbody></table>';

        $text .= '<table><tr>
        <td>
        <input class="button" type="submit" name="delete_selected_games" value="Delete selected" onclick="return confirm(\'Are you sure you want to delete these games?\')"/>
        </td>
        <td>
        <input class="button" type="submit" name="delete_all_games" value="Delete all Games" onclick="return confirm(\'Are you sure you want to delete all the games?\')"/>
        </td>
        <td>
        <input class="button" type="submit" name="update_selected_games" value="Update selected"/>
        </td>
        <td>
        <input class="button" type="submit" name="update_all_games" value="Update all Games"/>
        </td>
        <td>
        <input class="button" type="submit" name="add_games" value="Add Games"/>
        </td>
        </tr>
        </table>
        ';

        $text .= '</form>';
    }
}

?>
