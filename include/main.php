<?php
/**
 * Main.php
 *
 */

include(e_PLUGIN."ebattles/include/constants.php");
include(e_PLUGIN."ebattles/include/time.php");

// If preferences are not set, use default
if(!isset($pref['eb_events_update_delay']))
{
    $pref['eb_events_update_delay'] = 60;    // default 1 hour
}
if(!isset($pref['eb_mod']))
{
    $pref['eb_mod'] = e_UC_ADMIN;
}

$eplug_css = array(
"js/calendar/calendar-blue.css",
"css/tab.css"
);

function multi2dSortAsc(&$arr, $key, $sort)
{
    $sort_col = array();
    foreach ($arr as $sub)
    {
        $string = $sub[$key];
        // remove html tags
        $string = preg_replace("/<[^>]*>/e","", $string);
        $string = preg_split("/\/\s|\||(<br)/", $string);

        //echo "$string[0]<br>";
        $sort_col[] = $string[0];
    }
    array_multisort($sort_col, $sort, SORT_NUMERIC, $arr);
}
?>
