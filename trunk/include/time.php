<?php
/**
 * Time.php
 *
 */

define('INT_SECOND', 1);
define('INT_MINUTE', 60);
define('INT_HOUR', 3600);
define('INT_DAY', 86400);
define('INT_WEEK', 604800);

/*
GMT_TIMEOFFSET = client - gmt
  = (client - server) + (server - gmt)
  = TIMEOFFSET (from e107) + date("z")
*/
$gmt_timezone_offset = TIMEOFFSET + date("Z");
define("GMT_TIMEOFFSET", $gmt_timezone_offset);


/***************************************************************************************
Time Functions
***************************************************************************************/
function GMT_time() {
$gm_time = time() - date('Z', time());
return $gm_time;
}

function get_formatted_timediff($then, $now = false)
{
    $now      = (!$now) ? time() : $now;
    $timediff = ($now - $then);
    $weeks    = (int) intval($timediff / INT_WEEK);
    $timediff = (int) intval($timediff - (INT_WEEK * $weeks));
    $days     = (int) intval($timediff / INT_DAY);
    $timediff = (int) intval($timediff - (INT_DAY * $days));
    $hours    = (int) intval($timediff / INT_HOUR);
    $timediff = (int) intval($timediff - (INT_HOUR * $hours));
    $mins     = (int) intval($timediff / INT_MINUTE);
    $timediff = (int) intval($timediff - (INT_MINUTE * $mins));
    $sec      = (int) intval($timediff / INT_SECOND);
    $timediff = (int) intval($timediff - ($sec * INT_SECOND));

    $str = '';
    if ( $weeks )
    {
        $str .= intval($weeks);
        $str .= ($weeks > 1) ? ' weeks' : ' week';
    }

    if ( $days )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($days);
        $str .= ($days > 1) ? ' days' : ' day';
    }

    if ( $hours )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($hours);
        $str .= ($hours > 1) ? ' hours' : ' hour';
    }

    if ( $mins )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($mins);
        $str .= ($mins > 1) ? ' minutes' : ' minute';
    }

    if ( $sec )
    {
        $str .= ($str) ? ', ' : '';
        $str .= intval($sec);
        $str .= ($sec > 1) ? ' seconds' : ' second';
    }

    if ( !$weeks && !$days && !$hours && !$mins && !$sec )
    {
        $str .= '0 seconds';
    }
    else
    {
        $str .= '';
    }

    return $str;
}
?>