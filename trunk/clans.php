<?php
/**
* clans.php
*
*/

require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

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
<div class="tab">Teams</div>
';
displayClans();
$text .= '
</div>
';

$text .= '
</div>
';

$ns->tablerender('Teams', $text);
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
        $text .= "Error displaying info";
        return;
    }
    if($num_rows == 0){
        $text .= "<div>No Teams.</div>";
    }
    else
    {
        // Paginate
        $text .= $pages->display_pages();
        $text .= '<span style="float:right">';
        // Go To Page
        $text .= $pages->display_jump_menu();
        $text .= '&nbsp;&nbsp;&nbsp;';
        // Items per page
        $text .= $pages->display_items_per_page();
        $text .= '</span><br /><br />';

        /* Display table contents */
        $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
        $text .= "<tr><td class=\"forumheader\"><b>Team</b></td><td class=\"forumheader\"><b>Tag</b></td></tr>\n";
        for($i=0; $i<$num_rows; $i++){
            $clanid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $ctag  = mysql_result($result,$i, TBL_CLANS.".Tag");
            $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");

            $text .= "<tr><td class=\"forumheader3\"><a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$clanid\">$cname</a></td><td class=\"forumheader3\">$ctag</td></tr>\n";
        }
        $text .= "</tbody></table><br />\n";
    }

    if(check_class($pref['eb_teams_create_class']))
    {
        $text .= "<form action=\"".e_PLUGIN."ebattles/clancreate.php\" method=\"post\">";
        $text .= "<div>";
        $text .= "<input type=\"hidden\" name=\"userid\" value=\"".USERID."\"/>";
        $text .= "<input type=\"hidden\" name=\"username\" value=\"".USERNAME."\"/>";
        $text .= "<input class=\"button\" type=\"submit\" name=\"createteam\" value=\"Create new team\"/>";
        $text .= "</div>";
        $text .= "</form>";
    }
    else
    {
        //$text .= "<div>You are not authorized to create a team.</div>";
    }


}
?>

