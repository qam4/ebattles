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
    $pref['eb_mod'] = $_POST['eb_mod'];
    save_prefs();
    $message = EBATTLES_ADMIN_L1;
}
if (isset($_POST['eb_events_insert_data']))
{
    @include_once e_PLUGIN."ebattles/db_admin/insert_data.php";
    $message .= EBATTLES_ADMIN_L11;
}


if (isset($message)) {
    $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' id='ebform'>
<table style='".ADMIN_WIDTH."' class='fborder' style='width:95%'>
<tbody>
";

$text .= "</select>
</td>
</tr>

<tr><td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L2.": </td>
<td class='forumheader3' style='width:60%'>". r_userclass("eb_mod", $pref['eb_mod'], 'off', "admin, classes")."
</td>
</tr>
";

$text .= "<tr>
<td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L3.":  <div class='smalltext'>".EBATTLES_ADMIN_L4."</div></td>
<td class='forumheader3' style='width:60%'>
<input class='tbox' type='text' name='eb_events_update_delay' size='8' value='".$pref['eb_events_update_delay']."' maxlength='3' /> ".EBATTLES_ADMIN_L5."<br />

<input type='checkbox' name='eb_events_update_delay_enable' value='1' ".($pref['eb_events_update_delay_enable'] == 1 ? "checked='checked'" :"")."/>".EBATTLES_ADMIN_L6."
</td>
</tr>
";


$text .= "<tr>
<td class='forumheader3' style='width:40%'>".EBATTLES_ADMIN_L7.": </td>
<td class='forumheader3' style='width:60%'>
<input class='button' type='submit' name='eb_events_insert_data' value='".EBATTLES_ADMIN_L8."'>
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

require_once(e_ADMIN."footer.php");
?>
