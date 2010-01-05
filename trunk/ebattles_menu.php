<?php
if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN."ebattles/include/main.php");

$ebattles_title = $pref['eb_links_menuheading'];
$events_link    = e_PLUGIN.'ebattles/events.php';
$teams_link     = e_PLUGIN.'ebattles/clans.php';
$profile_link   = e_PLUGIN.'ebattles/userinfo.php?user='.USERID;
$games_link   = e_PLUGIN.'ebattles/gamesmanage.php';

$text  = '<a href="'.$events_link.'">';
$text .= EB_MENU_L2;
$text .= '</a><br />';
$text .= '<a href="'.$teams_link.'">';
$text .= EB_MENU_L3;
$text .= '</a><br />';
if (check_class($pref['eb_mod_class']))
{
    $text .= '<a href="'.$games_link.'">';
    $text .= EB_MENU_L5;
    $text .= '</a><br />';
}
if (check_class(e_UC_MEMBER))
{
    $text .= '<a href="'.$profile_link.'">';
    $text .= EB_MENU_L4;
    $text .= '</a><br />';
}

$ns->tablerender($ebattles_title,$text);
?>
