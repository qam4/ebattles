<?php
/**
* Main.php
*
*/

include(e_PLUGIN."ebattles/include/constants.php");
include(e_PLUGIN."ebattles/include/time.php");

global $pref;
global $sql;
global $time;

$time = GMT_time();

switch ($pref['eb_tab_theme'])
{
    case 'ebattles':
    $tab_theme = 'css/tab.ebattles.css';
    break;
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
"css/paginate.css",
"css/ebattles.css",
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

/**
* Searches haystack for needle and
* returns an array of the key path if
* it is found in the (multidimensional)
* array, FALSE otherwise.
*
* @mixed array_searchRecursive ( mixed needle,
* array haystack [, bool strict[, array path]] )
*/
function array_searchRecursive( $needle, $haystack, $strict=false, $path=array() )
{
    if( !is_array($haystack) ) {
        return false;
    }

    foreach( $haystack as $key => $val ) {
        $pos = strpos($val,$needle);
        if( is_array($val) && $subPath = array_searchRecursive($needle, $val, $strict, $path) ) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) || (!$strict && $pos !== false)) {
            $path[] = $key;
            return $path;
        }
    }
    return false;
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

function getAvatar($name)
{
    if (preg_match("/\//", $name))
    {
        // External link
        return $name;
    }
    else
    {
        // Internal link
        return e_PLUGIN."ebattles/images/avatars/$name";
    }
}

function imageResize($image, $target, $force_resize=FALSE) {
    // Resize image so it does not exceeds the max size.
    $image_dims = getimagesize($image);

    if ($image_dims != '')
    {
        $width  = $image_dims[0];
        $height = $image_dims[1];

        if((max($width,$height)>$target)||($force_resize==TRUE))
        {
            //takes the larger size of the width and height and applies the
            //formula accordingly...this is so this script will work
            //dynamically with any size image
            if ($width > $height) {
                $percentage = ($target / $width);
            } else {
                $percentage = ($target / $height);
            }

            //gets the new value and applies the percentage, then rounds the value
            $width = round($width * $percentage);
            $height = round($height * $percentage);

            //returns the new sizes in html image tag format...this is so you
            //can plug this function inside an image tag and just get the
            return 'width="'.$width.'" height="'.$height.'"';
        }
        else
        {
            return '';
        }
    }
    else
    {
        return 'width="'.$target.'"';
    }
}

function getIconResize($icon, $max_size, $enable_max_resize=TRUE, $force_resize=FALSE) {
    global $pref;

    if (($enable_max_resize == TRUE)||($force_resize==TRUE))
    {
        return 'src="'.$icon.'" '.imageResize($icon, $max_size, $force_resize);
    }
    else
    {
        return 'src="'.$icon.'"';
    }
}

function getGameIconResize($gicon) {
    global $pref;
    return getIconResize(getGameIcon($gicon), $pref['eb_max_image_size'], $pref['eb_max_image_size_check']).' alt="'.$gicon.'"';
}

function getActivityIconResize($icon) {
    global $pref;
    return getIconResize($icon, $pref['eb_activity_max_image_size'], $pref['eb_activity_max_image_size_check']);
}

function getActivityGameIconResize($gicon) {
    global $pref;
    return getIconResize(getGameIcon($gicon), $pref['eb_activity_max_image_size'], $pref['eb_activity_max_image_size_check']).' alt="'.$gicon.'"';
}

function getAvatarResize($icon) {
    global $pref;
    return getIconResize($icon, $pref['eb_max_avatar_size']).' alt="'.$icon.'"';
}

function floatToSQL($number)
{
    return number_format($number, 5, ".", "");
}

// ************************************************
// Miscellaneous Helper Functions
// ************************************************

/**
* @return true if current version of e107 is v0.7, otherwise false
*/
function isV07() {
    return true;
}

// ************************************************
// Comment Helper Functions
// ************************************************

/**
* Get number of comments for an item.
* <p>This method returns the number of comments for the supplied plugin/item id.</p>
* @param   string   a unique ID for this plugin, maximum of 10 character
* @param   int      id of the item comments are allowed for
* @return  int      number of comments for the supplied parameters
*/
function getCommentTotal($pluginid, $id) {
    global $pref, $e107cache, $tp;
    $query = "where comment_item_id='$id' AND comment_type='$pluginid'";
    $mysql = new db();
    return $mysql->db_Count("comments", "(*)", $query);
}

/**
* Add comments to a plugins
* <p>This method returns the HTML for a comment form. In addition, it will post comments to the e107v7
* comments database and get any existing comments for the current item.</p>
* @param   string   a unique ID for this plugin, maximum of 10 character
* @param   int      id of the item comments are allowed for
* @return  string   HTML for existing comments for an item and the comments form to allow new comments to be posted
*/
function getComment($pluginid, $id) {
    global $pref, $e107cache, $tp;

    // Include the comment class. Normally, this file is included at a global level, so we need to make the variable
    // it decalares global so it is available inside the comment class
    require_once(e_HANDLER."comment_class.php");
    require(e_FILE."shortcode/batch/comment_shortcodes.php");
    $GLOBALS["comment_shortcodes"] = $comment_shortcodes;

    $pid = 0; // What is this w.r.t. comment table? Parent ID?

    // Define a comment object
    $cobj = new comment();

    // See if we need to post a comment to the database
    if (isset($_POST['commentsubmit'])) {
        $cobj->enter_comment($_POST['author_name'], $_POST['comment'], $pluginid, $id, $pid, $_POST['subject']);
        if ($pref['cachestatus']){
            $e107cache->clear("comment.$pluginid.{$sub_action}");
        }
    }

    // Specific e107 0.617 processing to render existing comments
    if (!isV07()) {
        $query = $pref['nested_comments'] ?
        "comment_item_id='$id' AND comment_type='$pluginid' AND comment_pid='0' ORDER BY comment_datestamp" :
        "comment_item_id='$id' AND comment_type='$pluginid' ORDER BY comment_datestamp";
        unset($text);
        $mysql = new db();
        if ($comment_total = $mysql->db_Select("comments", "*", $query)) {
            $width = 0;
            while ($row = $mysql->db_Fetch()) {
                // ** Need to sort out how to do nested comments here
                if ($pref['nested_comments']) {
                    $text .= $cobj->render_comment($row, $pluginid, "comment", $id, $width, $subject, true);
                } else {
                    $text .= $cobj->render_comment($row, $pluginid, "comment", $id, $width, $subject, true);
                }
            }
            if (ADMIN && getperms("B")) {
                $text .= "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?$pluginid.$id'>".LAN_314."</a></div>";
            }
        }
    }

    // Get comment form - e107 sends this to the output buffer so we must grab it and assign to our return string
    ob_start();
    if (isV07()) {
        // e107 0.7
        $cobj->compose_comment($pluginid, "comment", $id, $width, $subject, false);
    } else {
        // e107 0.617
        if (strlen($text) > 0) {
            $ns = new e107table();
            $ns->tablerender(LAN_5, $text);
        }
        $cobj->form_comment("comment", $pluginid, $id, $subject, $content_type);
    }
    $text = ob_get_contents();
    ob_end_clean();

    return $text;
}


?>
