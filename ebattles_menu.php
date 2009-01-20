<?php
if (!defined('e107_INIT')) { exit; }

global $PLUGINS_DIRECTORY;
$lan_file = e_PLUGIN."ebattles/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."ebattles/languages/English.php");

$ebattles_title   = 'eBattles';
$events_link    = e_PLUGIN.'ebattles/events.php';
$clans_link    = e_PLUGIN.'ebattles/clans.php';

$text  = '<a href="'.$events_link.'">';
$text .= EBATTLES_MENU_L1;
$text .= '</a><br>';
$text .= '<a href="'.$clans_link.'">';
$text .= EBATTLES_MENU_L2;
$text .= '</a><br>';
if (check_class(e_UC_MAINADMIN)) $text .= '<a href="'.e_PLUGIN."ebattles/db_admin/insert_data.php".'">Insert Data</a>';

$ns->tablerender($ebattles_title,$text);
?>
