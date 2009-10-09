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

function imageResize($image, $target) {
    // Resize image so it does not exceeds the max size.
    $image_dims = getimagesize($image);

    $width  = $image_dims[0];
    $height = $image_dims[1];

    if(max($width,$height)>$target)
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

function getGameIconResize($gicon) {
    global $pref;

    if ($pref['eb_max_image_size_check'] == 1)
    {
        return 'src="'.getGameIcon($gicon).'" alt="'.$gicon.'" '.imageResize(getGameIcon($gicon), $pref['eb_max_image_size']);
    }
    else
    {
        return 'src="'.getGameIcon($gicon).'" alt="'.$gicon.'"';;
    }
}

?>
