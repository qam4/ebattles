<?php
/**
 * Main.php
 *
 */

include(e_PLUGIN."ebattles/include/constants.php");

// If preferences are not set, use default
if(!isset($pref['eb_events_update_delay']))
{
    $pref['eb_events_update_delay'] = 60;    // default 1 hour
}
if(!isset($pref['eb_mod']))
{
    $pref['eb_mod'] = e_UC_ADMIN;
}

/*
GMT_TIMEOFFSET = client - gmt
  = (client - server) + (server - gmt)
  = TIMEOFFSET (from e107) + date("z")
*/
$gmt_timezone_offset = TIMEOFFSET + date("Z");
define("GMT_TIMEOFFSET", $gmt_timezone_offset);

function GMT_time() {
$gm_time = time() - date('Z', time());
return $gm_time;
}
?>

<style type="text/css" media="screen">
        @import url("js/tool-man/lists.css");
        @import url("css/tables.css");
        @import url("css/tab.css");
        @import url("js/calendar/calendar-blue.css");
</style>

