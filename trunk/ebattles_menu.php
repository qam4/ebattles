<?php
if (!defined('e107_INIT')) { exit; }

global $PLUGINS_DIRECTORY;
$lan_file = e_PLUGIN."ebattles/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."ebattles/languages/English.php");

$ebattles_title = 'eBattles';
$ebattles_link  = e_PLUGIN.'ebattles/ebattles.php';
$events_link    = e_PLUGIN.'ebattles/events.php';
$teams_link     = e_PLUGIN.'ebattles/clans.php';

$text  = '<a href="'.$ebattles_link.'">';
$text .= EBATTLES_MENU_L1;
$text .= '</a><br>';
$text .= '<a href="'.$events_link.'">';
$text .= EBATTLES_MENU_L2;
$text .= '</a><br>';
$text .= '<a href="'.$teams_link.'">';
$text .= EBATTLES_MENU_L3;
$text .= '</a><br>';

$ns->tablerender($ebattles_title,$text);
?>
