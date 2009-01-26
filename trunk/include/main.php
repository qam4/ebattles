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
"js/tool-man/lists.css",
"js/calendar/calendar-blue.css",
"css/tab.css"
);

?>
