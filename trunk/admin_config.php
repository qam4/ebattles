<?php
// Remember that we must include class2.php
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

// Check current user is an admin, redirect to main site if not
if (!getperms("P")) {
    header("location:".e_HTTP."index.php");
    exit;
}

@include_once e_PLUGIN."ebattles/languages/".e_LANGUAGE."/".e_LANGUAGE."_config.php";
@include_once e_PLUGIN."ebattles/languages/English/English_config.php";

// Include page header stuff for admin pages
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

if (isset($_POST['updatesettings'])) {
    $pref['eb_events_update_delay'] = $_POST['eb_events_update_delay'];
    $pref['eb_events_update_delay_enable'] = $_POST['eb_events_update_delay_enable'];
    $pref['eb_events_create_class'] = $_POST['eb_events_create_class'];
    $pref['eb_teams_create_class'] = $_POST['eb_teams_create_class'];
    $pref['eb_mod_class'] = $_POST['eb_mod_class'];
    $pref['eb_tab_theme'] = $_POST['eb_tab_theme'];
    $pref['eb_max_image_size_check'] = $_POST['eb_max_image_size_check'];
    $pref['eb_max_image_size'] = $_POST['eb_max_image_size'];
    $pref['eb_default_items_per_page'] = $_POST['eb_default_items_per_page'];
    $pref['eb_max_avatar_size'] = $_POST['eb_max_avatar_size'];
    $pref['eb_avatar_enable_playersstandings'] = $_POST['eb_avatar_enable_playersstandings'];
    $pref['eb_avatar_default_image'] = $_POST['eb_avatar_default_image'];
    $pref['eb_avatar_default_image'] = $_POST['eb_avatar_default_image'];
    save_prefs();
    $message = EBATTLES_ADMIN_L1;
}
if (isset($_POST['updatelinks'])) {
    $pref['eb_links_menuheading'] = $_POST['eb_links_menuheading'];
    save_prefs();
    $message = EBATTLES_ADMIN_L1;
}
if (isset($_POST['update_activity'])) {
    $pref['eb_activity_menuheading'] = $_POST['eb_activity_menuheading'];
    $pref['eb_activity_number_of_items'] = $_POST['eb_activity_number_of_items'];
    $pref['eb_activity_max_image_size_check'] = $_POST['eb_activity_max_image_size_check'];
    $pref['eb_activity_max_image_size'] = $_POST['eb_activity_max_image_size'];
    save_prefs();
    $message = EBATTLES_ADMIN_L1;
}
if (e_QUERY)
{
    $qs = explode(".", e_QUERY);
}
if (isset($_POST['eb_events_insert_data']))
{
    @include_once e_PLUGIN."ebattles/db_admin/insert_data.php";
    $message .= EBATTLES_ADMIN_L11;
}

if (isset($message)) {
    $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

// ========================================================
//				LINKS OPTIONS MENU
// ========================================================
if((isset($qs[0]) && $qs[0] == "eb_links"))
{
    $text .= "<div style='text-align:center'>
    <form id='adminform' method='post' action='".e_SELF."'>
    <table style='".ADMIN_WIDTH."' class='fborder'>
    <tbody>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L27.":</td>
    <td class='forumheader3' style='width:60%'>
    <input class='tbox' type='text' name='eb_links_menuheading' size='20' value='".$pref['eb_links_menuheading']."'/>
    </td>
    </tr>
    ";
    
    $text .= "<tr>
    <td  class='forumheader' colspan='3' style='text-align:center'>
    <input class='button' type='submit' name='updatelinks' value='".EBATTLES_ADMIN_L28."' />
    </td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>";

    // The usual, tell e107 what to include on the page
    $ns->tablerender(EBATTLES_ADMIN_L10, $text);
}

// ========================================================
//				RECENT ACTIVITY OPTIONS MENU
// ========================================================
if((isset($qs[0]) && $qs[0] == "eb_activity"))
{
    $text .= "<div style='text-align:center'>
    <form id='adminform' method='post' action='".e_SELF."'>
    <table style='".ADMIN_WIDTH."' class='fborder'>
    <tbody>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L27.":</td>
    <td class='forumheader3' style='width:60%'>
    <input class='tbox' type='text' name='eb_activity_menuheading' size='20' value='".$pref['eb_activity_menuheading']."'/>
    </td>
    </tr>
    ";
    
    $items = '';
    $ipp_array = array(5,10,25,50,100);
    foreach($ipp_array as $ipp_opt)
    $items .= ($ipp_opt == $pref['eb_activity_number_of_items']) ? "<option selected=\"selected\" value=\"$ipp_opt\">$ipp_opt</option>\n":"<option value=\"$ipp_opt\">$ipp_opt</option>\n";
    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L30.":</td>
    <td class='forumheader3' style='width:60%'>
    <select class='tbox' name='eb_activity_number_of_items'>".$items."</select>
    </td>
    </tr>
    ";
    
    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L15.":  <div class='smalltext'>".EBATTLES_ADMIN_L16."</div></td>
    <td class='forumheader3' style='width:60%'>
    <input class='tbox' type='text' name='eb_activity_max_image_size' size='8' value='".$pref['eb_activity_max_image_size']."' maxlength='3' /> px<br />
    <input class='tbox' type='checkbox' name='eb_activity_max_image_size_check' value='1' ".($pref['eb_activity_max_image_size_check'] == 1 ? "checked='checked'" :"")."/>".EBATTLES_ADMIN_L17."
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td  class='forumheader' colspan='3' style='text-align:center'>
    <input class='button' type='submit' name='update_activity' value='".EBATTLES_ADMIN_L28."' />
    </td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>";

    // The usual, tell e107 what to include on the page
    $ns->tablerender(EBATTLES_ADMIN_L10, $text);
}


// ========================================================
//				MAIN OPTIONS MENU
// ========================================================
if(!isset($qs[0]) || (isset($qs[0]) && $qs[0] == "config")){
    $text = "
    <script type='text/javascript'>
    <!--//
    function changetext(v)
    {
    document.getElementById('eb_avatar_default_image').value=v;
    }
    //-->
    </script>
    ";

    $text .= "<div style='text-align:center'>
    <form id='adminform' method='post' action='".e_SELF."'>
    <table style='".ADMIN_WIDTH."' class='fborder'>
    <tbody>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L2.": </td>
    <td class='forumheader3' style='width:60%'>". r_userclass("eb_mod_class", $pref['eb_mod_class'], 'off', "admin, classes")."
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L12.": </td>
    <td class='forumheader3' style='width:60%'>". r_userclass("eb_events_create_class", $pref['eb_events_create_class'], 'off', "public, member, admin, classes")."
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L13.": </td>
    <td class='forumheader3' style='width:60%'>". r_userclass("eb_teams_create_class", $pref['eb_teams_create_class'], 'off', "public, member, admin, classes")."
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L3.":  <div class='smalltext'>".EBATTLES_ADMIN_L4."</div></td>
    <td class='forumheader3' style='width:60%'>
    <input class='tbox' type='text' name='eb_events_update_delay' size='8' value='".$pref['eb_events_update_delay']."' maxlength='3' /> ".EBATTLES_ADMIN_L5."<br />

    <input class='tbox' type='checkbox' name='eb_events_update_delay_enable' value='1' ".($pref['eb_events_update_delay_enable'] == 1 ? "checked='checked'" :"")."/>".EBATTLES_ADMIN_L6."
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L14.": </td>
    <td class='forumheader3' style='width:60%'>
    <input type='radio' size='40' name='eb_tab_theme' ".($pref['eb_tab_theme'] == 'default' ? "checked='checked'" :"")." value='default' />Default
    <input type='radio' size='40' name='eb_tab_theme' ".($pref['eb_tab_theme'] == 'ebattles' ? "checked='checked'" :"")." value='ebattles' />eBattles
    <input type='radio' size='40' name='eb_tab_theme' ".($pref['eb_tab_theme'] == 'dark' ? "checked='checked'" :"")." value='dark' />Dark
    <input type='radio' size='40' name='eb_tab_theme' ".($pref['eb_tab_theme'] == 'winclassic' ? "checked='checked'" :"")." value='winclassic' />Windows Classic
    <input type='radio' size='40' name='eb_tab_theme' ".($pref['eb_tab_theme'] == 'webfx' ? "checked='checked'" :"")." value='webfx' />Web FX
    <input type='radio' size='40' name='eb_tab_theme' ".($pref['eb_tab_theme'] == 'luna' ? "checked='checked'" :"")." value='luna' />Luna
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L15.":  <div class='smalltext'>".EBATTLES_ADMIN_L16."</div></td>
    <td class='forumheader3' style='width:60%'>
    <input class='tbox' type='text' name='eb_max_image_size' size='8' value='".$pref['eb_max_image_size']."' maxlength='3' /> px<br />
    <input class='tbox' type='checkbox' name='eb_max_image_size_check' value='1' ".($pref['eb_max_image_size_check'] == 1 ? "checked='checked'" :"")."/>".EBATTLES_ADMIN_L17."
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L19.":  <div class='smalltext'>".EBATTLES_ADMIN_L20."</div></td>
    <td class='forumheader3' style='width:60%'>
    <input class='tbox' type='text' name='eb_max_avatar_size' size='8' value='".$pref['eb_max_avatar_size']."' maxlength='3' /> px<br />
    <input class='tbox' type='checkbox' name='eb_avatar_enable_playersstandings' value='1' ".($pref['eb_avatar_enable_playersstandings'] == 1 ? "checked='checked'" :"")."/>".EBATTLES_ADMIN_L21."
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L22.":  <div class='smalltext'>".EBATTLES_ADMIN_L23."</div></td>
    <td class='forumheader3' style='width:60%'>
    ";
    if ($pref['eb_avatar_default_image'] != '')
    {
        $text .= '<img src="'.getAvatar($pref['eb_avatar_default_image']).'" alt="Default Avatar" style="vertical-align:middle"/>&nbsp;';
    }
    $text .= "<input class='tbox' type='text' id='eb_avatar_default_image' name='eb_avatar_default_image' size='20' value='".$pref['eb_avatar_default_image']."'/>";

    $text .= "<div>";
    $avatarlist[0] = "";
    $handle = opendir(e_PLUGIN."ebattles/images/avatars/");
    while ($file = readdir($handle))
    {
        if ($file != "." && $file != ".." && $file != "index.html" && $file != ".svn")
        {
            $avatarlist[] = $file;
        }
    }
    closedir($handle);

    for($c = 1; $c <= (count($avatarlist)-1); $c++)
    {
        $text .= '<a href="javascript:changetext(\''.$avatarlist[$c].'\')"><img src="'.e_PLUGIN.'ebattles/images/avatars/'.$avatarlist[$c].'" alt="'.$avatarlist[$c].'" style="border:0"/></a> ';
    }
    $text .= "
    </div>
    ";

    $text .= "</td>
    </tr>
    ";

    $items = '';
    $ipp_array = array(5,10,25,50,100,'All');
    foreach($ipp_array as $ipp_opt)
    $items .= ($ipp_opt == $pref['eb_default_items_per_page']) ? "<option selected=\"selected\" value=\"$ipp_opt\">$ipp_opt</option>\n":"<option value=\"$ipp_opt\">$ipp_opt</option>\n";
    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L18.":</td>
    <td class='forumheader3' style='width:60%'>
    <select class='tbox' name='eb_default_items_per_page'>".$items."</select>
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L7.": </td>
    <td class='forumheader3' style='width:60%'>
    <input class='button' type='submit' name='eb_events_insert_data' value='".EBATTLES_ADMIN_L8."'/>
    </td>
    </tr>
    ";

    $text .= "<tr>
    <td  class='forumheader' colspan='3' style='text-align:center'>
    <input class='button' type='submit' name='updatesettings' value='".EBATTLES_ADMIN_L9."' />
    </td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>";

    // The usual, tell e107 what to include on the page
    $ns->tablerender(EBATTLES_ADMIN_L10, $text);
}

require_once(e_ADMIN."footer.php");

function admin_config_adminmenu()
{
    if (e_QUERY) {
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
    }
    if (!isset($action) || ($action == ""))
    {
        $action = "config";
    }
    $var['config']['text'] = EBATTLES_ADMIN_L24;
    $var['config']['link'] = "admin_config.php";

    $var['eb_links']['text'] = EBATTLES_ADMIN_L25;
    $var['eb_links']['link'] ="admin_config.php?eb_links";

    $var['eb_activity']['text'] = EBATTLES_ADMIN_L26;
    $var['eb_activity']['link'] ="admin_config.php?eb_activity";

    $var['eb_activity']['text'] = EBATTLES_ADMIN_L32;
    $var['eb_activity']['link'] ="admin_vupdate.php";

    show_admin_menu(EBATTLES_L1, $action, $var);
}
?>
