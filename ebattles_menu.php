<?php
if (!defined('e107_INIT')) { exit; }

global $PLUGINS_DIRECTORY;
$lan_file = e_PLUGIN."ebattles/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."ebattles/languages/English.php");

 $ebattles_title   = 'eBattles';
 $events_link    = e_PLUGIN.'ebattles/events.php';

 $text  = '<a href="'.$events_link.'">';
 $text .= EBATTLES_MENU_L1;
 $text .= '</a>';

 $ns->tablerender($ebattles_title,$text);
?>
