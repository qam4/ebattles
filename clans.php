<?php
/**
* clans.php
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");

require_once(e_PLUGIN."ebattles/include/paginator.class.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text = '
<script type="text/javascript" src="./js/tabpane.js"></script>
';

/**
* Display Clans Table
*/
$text .= '
<div class="tab-pane" id="tab-pane-8">
<div class="tab-page">
<div class="tab">'.EB_CLANS_L2.'</div>
';
displayClans();
$text .= '
</div>
';

$text .= '
</div>
';

$ns->tablerender(EB_CLANS_L1, $text);
require_once(FOOTERF);
exit;


/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayClans - Displays the Clans database table in
* a nicely formatted html table.
*/
function displayClans(){
    global $pref;
    global $sql;
    global $text;

    $pages = new Paginator;

    /* set pagination variables */
    $q = "SELECT count(*) "
    ." FROM ".TBL_CLANS;
    $result = $sql->db_Query($q);
    $totalItems = mysql_result($result, 0);
    $pages->items_total = $totalItems;
    $pages->mid_range = eb_PAGINATION_MIDRANGE;
    $pages->paginate();

    $q = "SELECT ".TBL_CLANS.".*"
    ." FROM ".TBL_CLANS
    ." ORDER BY Name"
    ." $pages->limit";

    $result = $sql->db_Query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    if(!$result || ($num_rows < 0)){
        $text .= EB_CLANS_L3;
        return;
    }
    if($num_rows == 0){
        $text .= '<div>'.EB_CLANS_L4.'</div>';
    }
    else
    {
        // Paginate
        $text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
        $text .= '<span style="float:right">';
        // Go To Page
        $text .= $pages->display_jump_menu();
        $text .= '&nbsp;&nbsp;&nbsp;';
        // Items per page
        $text .= $pages->display_items_per_page();
        $text .= '</span><br /><br />';

        /* Display table contents */
        $text .= '<table class="fborder" style="width:95%"><tbody>';
        $text .= '<tr><td class="forumheader"><b>'.EB_CLANS_L5.'</b></td>
        <td class="forumheader"><b>'.EB_CLANS_L6.'</b></td></tr>';
        for($i=0; $i<$num_rows; $i++){
            $clanid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $ctag  = mysql_result($result,$i, TBL_CLANS.".Tag");
            $cavatar  = mysql_result($result,$i, TBL_CLANS.".Image");
            $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");

            $image = "";
            if ($pref['eb_avatar_enable_teamslist'] == 1)
            {            if($cavatar)
                {
                    $image = '<img '.getAvatarResize(getImagePath($cavatar), 'team_avatars').' style="vertical-align:middle"/>';
                } else if ($pref['eb_avatar_default_team_image'] != ''){
                    $image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_team_image']), 'team_avatars').' style="vertical-align:middle"/>';
                }
            }

            $text .= '<tr>
            <td class="forumheader3">'.$image.'&nbsp;<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$clanid.'">'.$cname.'</a></td>
            <td class="forumheader3">'.$ctag.'</td></tr>';
        }
        $text .= '</tbody></table><br />';
    }

    if(check_class($pref['eb_teams_create_class']))
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/clancreate.php" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="userid" value="'.USERID.'"/>';
        $text .= '<input type="hidden" name="username" value="'.USERNAME.'"/>';
        $text .= '<input class="button" type="submit" name="createteam" value="'.EB_CLANS_L7.'"/>';
        $text .= '</div>';
        $text .= '</form>';
    }
    else
    {
        //$text .= '<div>'..'</div>';
    }


}
?>

