<?php
/**
* Main.php
*
*/

include(e_PLUGIN."ebattles/include/constants.php");
include(e_PLUGIN."ebattles/include/time.php");

global $pref;
global $sql;

$time = GMT_time();

// If preferences are not set, use default
if(!isset($pref['eb_events_update_delay']))
{
    $pref['eb_events_update_delay'] = 60;    // default 1 hour
}
if(!isset($pref['eb_mod_class']))
{
    $pref['eb_mod_class'] = e_UC_ADMIN;
}
if(!isset($pref['eb_events_create_class']))
{
    $pref['eb_events_create_class'] = e_UC_MEMBER;
}
if(!isset($pref['eb_teams_create_class']))
{
    $pref['eb_teams_create_class'] = e_UC_MEMBER;
}
if(!isset($pref['eb_tab_theme']))
{
    $pref['eb_tab_theme'] = 'default';
}

switch ($pref['eb_tab_theme'])
{
    case 'dark':
    $tab_theme = 'css/tab.dark.css';
    break;
    case 'winclassic':
    $tab_theme = 'css/tab.winclassic.css';
    break;
    case 'webfx':
    $tab_theme = 'css/tab.webfx.css';
    break;
    case 'luna':
    $tab_theme = 'css/luna/tab.css';
    break;
    default:
    $tab_theme = 'css/tab.css';
}

$eplug_css = array(
"js/calendar/calendar-blue.css",
$tab_theme
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

function getGameIcon($icon)
{
    if (preg_match("/\//", $icon))
    {
        // External link
        return $icon;
    }
    else
    {
        // Internal link
        return e_PLUGIN."ebattles/images/games_icons/$icon";
    }
}
?>
