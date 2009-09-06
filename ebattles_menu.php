<?php
include_once(e_PLUGIN."ebattles/include/main.php");

if (!defined('e107_INIT')) { exit; }

global $PLUGINS_DIRECTORY;
$lan_file = e_PLUGIN."ebattles/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."ebattles/languages/English.php");

$ebattles_title = 'eBattles';
$events_link    = e_PLUGIN.'ebattles/events.php';
$teams_link     = e_PLUGIN.'ebattles/clans.php';
$profile_link   = e_PLUGIN.'ebattles/userinfo.php?user='.USERID;
$games_link   = e_PLUGIN.'ebattles/gamemanage.php';

$text  = '<a href="'.$events_link.'">';
$text .= EBATTLES_MENU_L2;
$text .= '</a><br />';
$text .= '<a href="'.$teams_link.'">';
$text .= EBATTLES_MENU_L3;
$text .= '</a><br />';
if (check_class($pref['eb_mod']))
{
    $text .= '<a href="'.$games_link.'">';
    $text .= EBATTLES_MENU_L5;
    $text .= '</a><br />';
}
if (check_class(e_UC_MEMBER))
{
    $text .= '<a href="'.$profile_link.'">';
    $text .= EBATTLES_MENU_L4;
    $text .= '</a><br />';
}

$ns->tablerender($ebattles_title,$text);
?>
