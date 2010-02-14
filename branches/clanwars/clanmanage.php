<?php
/**
*clanmanage.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text = '';

/* Clan Name */
$clan_id = $_GET['clanid'];

if (!$clan_id)
{
    header("Location: ./clans.php");
    exit();
}
else
{
    $text .= "
    <script type='text/javascript' src='./js/tabpane.js'></script>
    <script type='text/javascript'>
    <!--//
    function changeteamtext(v)
    {
    document.getElementById('clanavatar').value=v;
    }    //-->
    </script>
    ";

    $q = "SELECT ".TBL_CLANS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_CLANS.", "
    .TBL_USERS
    ." WHERE (".TBL_CLANS.".ClanID = '$clan_id')"
    ." AND (".TBL_USERS.".user_id = ".TBL_CLANS.".Owner)";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    $clan_name   = mysql_result($result,0, TBL_CLANS.".Name");
    $clan_owner  = mysql_result($result,0, TBL_USERS.".user_id");
    $clan_owner_name   = mysql_result($result,0, TBL_USERS.".user_name");
    $clan_tag    = mysql_result($result,0, TBL_CLANS.".Tag");
    $clan_password    = mysql_result($result,0, TBL_CLANS.".password");
    $clan_avatar    = mysql_result($result,0, TBL_CLANS.".Image");
    if ($clan_avatar == '' && $pref['eb_avatar_default_team_image'] != '') $clan_avatar = $pref['eb_avatar_default_team_image'];

    $can_manage = 0;
    if (check_class($pref['eb_mod_class'])) $can_manage = 1;
    if (USERID==$clan_owner) $can_manage = 1;
    if ($can_manage == 0)
    {
        header("Location: ./claninfo.php?clanid=$clan_id");
        exit();
    }
    else
    {
        $text .= '
        <div class="tab-pane" id="tab-pane-4">

        <div class="tab-page">
        <div class="tab">'.EB_CLANM_L2.'</div>
        ';

        $text .= '<form action="'.e_PLUGIN.'ebattles/clanprocess.php?clanid='.$clan_id.'" method="post">';
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tbody>';
        $text .= '<!-- Clan -->';
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_CLANM_L4.'</b></td>';
        $text .= '<td class="forumheader3"><b><a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$clan_id.'">'.$clan_name.'</a> ('.$clan_tag.')</b>';

        // Delete team
        $q_ClanScores = "SELECT ".TBL_DIVISIONS.".*, "
        .TBL_TEAMS.".*, "
        .TBL_PLAYERS.".*, "
        .TBL_SCORES.".*"
        ." FROM ".TBL_DIVISIONS.", "
        .TBL_TEAMS.", "
        .TBL_PLAYERS.", "
        .TBL_SCORES
        ." WHERE (".TBL_DIVISIONS.".Clan = '$clan_id')"
        ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
        ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
        ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
        $result_ClanScores = $sql->db_Query($q_ClanScores);
        $numClanScores = mysql_numrows($result_ClanScores);
        if ($numClanScores == 0)
        {
            $text .= '<br />';
            $text .= '<input class="button" type="submit" name="clandelete" value="'.EB_CLANM_L5.'" onclick="return confirm(\''.EB_CLANM_L6.'\');"/>';
        }
        $text .= '</td></tr>';

        $text .= '<!-- Clan Owner -->';
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_CLANM_L7.'</b><br />';
        $text .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$clan_owner.'">'.$clan_owner_name.'</a>';
        $text .= '</td>';

        $q_2 = "SELECT ".TBL_USERS.".*"
        ." FROM ".TBL_USERS;

        $result_2 = $sql->db_Query($q_2);
        $row = mysql_fetch_array($result_2);
        $num_rows_2 = mysql_numrows($result_2);

        $text .= '<td class="forumheader3">';
        $text .= '<table>';
        $text .= '<tr>';
        $text .= '<td><select class="tbox" name="clanowner">';
        for($j=0; $j<$num_rows_2; $j++)
        {
            $uid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
            $uname  = mysql_result($result_2,$j, TBL_USERS.".user_name");

            if ($clan_owner == $uid)
            {
                $text .= '<option value="'.$uid.'" selected="selected">'.$uname.'</option>';
            }
            else
            {
                $text .= '<option value="'.$uid.'">'.$uname.'</option>';
            }
        }
        $text .= '</select>';
        $text .= '</td>';
        $text .= '<td>';
        $text .= '<input class="button" type="submit" name="clanchangeowner" value="'.EB_CLANM_L8.'"/>';
        $text .= '</td>';
        $text .= '</tr>';
        $text .= '</table>';
        $text .= '</td>';
        $text .= '</tr>';

        $text .= '<!-- Clan Name -->';
        $text .= '<tr>';
        $text .= '
        <td class="forumheader3"><b>'.EB_CLANM_L9.'</b></td>
        <td class="forumheader3">
        <input class="tbox" type="text" size="40" name="clanname" value="'.$clan_name.'"/>
        </td>
        </tr>

        <!-- Clan Avatar -->
        <tr>
        <td class="forumheader3"><b>'.EB_CLANM_L29.'</b><div class="smalltext">'.EB_CLANM_L30.'</div></td>
        <td class="forumheader3">';
        if ($clan_avatar != '')
        {
            $text .= '<img '.getAvatarResize(getTeamAvatar($clan_avatar)).' style="vertical-align:middle"/>&nbsp;';
        }
        $text .= '<input class="tbox" type="text" id="clanavatar" name="clanavatar" size="20" value="'.$clan_avatar.'"/>';

        $text .= '<div><br />';
        $avatarlist = array();
        $avatarlist[0] = "";
        $handle = opendir(e_PLUGIN."ebattles/images/team_avatars/");
        while ($file = readdir($handle))
        {
            if ($file != "." && $file != ".." && $file != "index.html" && $file != ".svn" && $file != "Thumbs.db")
            {
                $avatarlist[] = $file;
            }
        }
        closedir($handle);

        for($c = 1; $c <= (count($avatarlist)-1); $c++)
        {
            $text .= '<a href="javascript:changeteamtext(\''.$avatarlist[$c].'\')"><img src="'.e_PLUGIN.'ebattles/images/team_avatars/'.$avatarlist[$c].'" alt="'.$avatarlist[$c].'" style="border:0"/></a> ';
        }
        $text .= '
        </div>
        ';

        $text .= '
        </td>
        </tr>

        <!-- Clan Tag -->
        <tr>
        <td class="forumheader3"><b>'.EB_CLANM_L10.'</b></td>
        <td class="forumheader3">
        <input class="tbox" type="text" size="40" name="clantag" value="'.$clan_tag.'"/>
        </td>
        </tr>

        <!-- Clan Password -->
        <tr>
        <td class="forumheader3"><b>'.EB_CLANM_L11.'</b></td>
        <td class="forumheader3">
        <input class="tbox" type="text" size="40" name="clanpassword" value="'.$clan_password.'"/>
        </td>
        </tr>

        </tbody>
        </table>
        <!-- Save Button -->
        <table><tbody><tr><td>
        <input class="button" type="submit" name="clansettingssave" value="'.EB_CLANM_L12.'"/>
        </td></tr></tbody></table>
        </form>

        </div>
        ';

        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_CLANM_L3.'</div>
        ';

        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tbody>';

        $q = "SELECT DISTINCT ".TBL_GAMES.".*"
        ." FROM ".TBL_GAMES.", "
        . TBL_EVENTS
        ." WHERE (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
        ." ORDER BY Name";
        $result = $sql->db_Query($q);
        /* Error occurred, return given name by default */
        $num_rows = mysql_numrows($result);
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= EB_CLANM_L13;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= '<form action="'.e_PLUGIN.'ebattles/clanprocess.php?clanid='.$clan_id.'" method="post">';
        $text .= '<div>';
        $text .= '<select class="tbox" name="divgame">';
        for($i=0; $i<$num_rows; $i++){
            $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
            $gid  = mysql_result($result,$i, TBL_GAMES.".GameId");
            $text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
        }
        $text .= '</select>';
        $text .= '<input type="hidden" name="clanowner" value="'.$clan_owner.'"/>';
        $text .= '<input class="button" type="submit" name="clanadddiv" value="'.EB_CLANM_L14.'"/>';
        $text .= '</div>';
        $text .= '</form>';
        $text .= '</td>';
        $text .= '</tr>';

        $q = "SELECT ".TBL_CLANS.".*, "
        .TBL_DIVISIONS.".*, "
        .TBL_USERS.".*, "
        .TBL_GAMES.".*"
        ." FROM ".TBL_CLANS.", "
        .TBL_DIVISIONS.", "
        .TBL_USERS.", "
        .TBL_GAMES
        ." WHERE (".TBL_CLANS.".ClanID = '$clan_id')"
        ." AND (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
        ." AND (".TBL_USERS.".user_id = ".TBL_DIVISIONS.".Captain)"
        ." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";

        $result = $sql->db_Query($q);
        $num_rows = mysql_numrows($result);
        for($i=0; $i<$num_rows; $i++)
        {
            $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
            $gicon  = mysql_result($result,$i , TBL_GAMES.".Icon");
            $div_id  = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");
            $div_captain  = mysql_result($result,$i, TBL_USERS.".user_id");
            $div_captain_name  = mysql_result($result,$i, TBL_USERS.".user_name");

            $text .= '<tr>';
            $text .= '<td class="forumheader3">';
            $text .= '<b><img '.getGameIconResize($gicon).'/> '.$gname.'</b><br />';
            $text .= EB_CLANM_L15.': <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$div_captain.'">'.$div_captain_name.'</a>';

            // Delete division
            $q_DivScores = "SELECT ".TBL_DIVISIONS.".*, "
            .TBL_TEAMS.".*, "
            .TBL_PLAYERS.".*, "
            .TBL_SCORES.".*"
            ." FROM ".TBL_DIVISIONS.", "
            .TBL_TEAMS.", "
            .TBL_PLAYERS.", "
            .TBL_SCORES
            ." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
            ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
            ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
            ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
            $result_DivScores = $sql->db_Query($q_DivScores);
            $numDivScores = mysql_numrows($result_DivScores);
            if ($numDivScores == 0)
            {
                $text .= '<br /><form action="'.e_PLUGIN.'ebattles/clanprocess.php?clanid='.$clan_id.'" method="post">';
                $text .= '<div>';
                $text .= '<input type="hidden" name="clandiv" value="'.$div_id.'"/>';
                $text .= '<input class="button" type="submit" name="clandeletediv" value="'.EB_CLANM_L16.'" onclick="return confirm(\''.EB_CLANM_L17.'\');"/>';
                $text .= '</div></form>';
            }

            $text .= '</td>';
            $q_2 = "SELECT ".TBL_CLANS.".*, "
            .TBL_DIVISIONS.".*, "
            .TBL_MEMBERS.".*, "
            .TBL_USERS.".*, "
            .TBL_GAMES.".*"
            ." FROM ".TBL_CLANS.", "
            .TBL_DIVISIONS.", "
            .TBL_USERS.", "
            .TBL_MEMBERS.", "
            .TBL_GAMES
            ." WHERE (".TBL_CLANS.".ClanID = '$clan_id')"
            ." AND (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
            ." AND (".TBL_DIVISIONS.".DivisionID = '$div_id')"
            ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
            ." AND (".TBL_USERS.".user_id = ".TBL_MEMBERS.".User)"
            ." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";

            $result_2 = $sql->db_Query($q_2);
            if(!$result_2 || (mysql_numrows($result_2) < 1))
            {
                $text .= '<td class="forumheader3">'.EB_CLANM_L18.'</td></tr>';
            }
            else
            {
                $row = mysql_fetch_array($result_2);
                $num_rows_2 = mysql_numrows($result_2);

                $text .= '<td class="forumheader3">';
                $text .= '<form action="'.e_PLUGIN.'ebattles/clanprocess.php?clanid='.$clan_id.'" method="post">';
                $text .= '<table>';
                $text .= '<tr>';
                $text .= '<td><select class="tbox" name="divcaptain">';
                for($j=0; $j<$num_rows_2; $j++)
                {
                    $mid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                    $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");

                    if ($div_captain == $mid)
                    {
                        $text .= '<option value="'.$mid.'" selected="selected">'.$mname.'</option>';
                    }
                    else
                    {
                        $text .= '<option value="'.$mid.'">'.$mname.'</option>';
                    }
                }
                $text .= '</select>';
                $text .= '</td>';
                $text .= '<td>';
                $text .= '<input type="hidden" name="clandiv" value="'.$div_id.'"/>';
                $text .= '<input class="button" type="submit" name="clanchangedivcaptain" value="'.EB_CLANM_L19.'"/>';
                $text .= '</td>';
                $text .= '</tr>';
                $text .= '</table>';
                $text .= '</form>';
                $text .= '</td>';
                $text .= '</tr>';

                $text .= '<tr>';
                $text .= '<td class="forumheader3">'.$num_rows_2.'&nbsp;'.EB_CLANM_L20.'</td>';
                $text .= '<td class="forumheader3">';
                $text .= '<form action="'.e_PLUGIN.'ebattles/clanprocess.php?clanid='.$clan_id.'" method="post">';
                $text .= '<table class="fborder" style="width:95%"><tbody>';
                $text .= '<tr>
                <td class="forumheader"><b>'.EB_CLANM_L21.'</b></td>
                <td class="forumheader"><b>'.EB_CLANM_L22.'</b></td>
                <td class="forumheader"><b>'.EB_CLANM_L23.'</b></td>
                <td class="forumheader"><b>'.EB_CLANM_L24.'</b></td>
                </tr>';
                for($j=0; $j<$num_rows_2; $j++)
                {
                    $mid  = mysql_result($result_2,$j, TBL_MEMBERS.".MemberID");
                    $muid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                    $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
                    $mjoined  = mysql_result($result_2,$j, TBL_MEMBERS.".timestamp");
                    $mjoined_local = $mjoined + TIMEOFFSET;
                    $date  = date("d M Y",$mjoined_local);

                    $text .= '<tr>';
                    $text .= '<td class="forumheader3"><b><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$muid.'">'.$mname.'</a></b></td>
                    <td class="forumheader3">'.EB_CLANM_L25.'</td>
                    <td class="forumheader3">'.$date.'</td>';

                    // Checkbox to select which member to kick
                    $text .= '<td class="forumheader3"><input type="checkbox" name="del[]" value="'.$mid.'" /></td>';
                    $text .= '</tr>';
                }
                $text .= '<tr>';
                $text .= '<td colspan="4">';
                $text .= '<input class="button" type="submit" name="kick" value="'.EB_CLANM_L26.'"/>';
                $text .= '</td>';
                $text .= '</tr>';
                $text .= '</tbody></table>';
                $text .= '</form>';
                $text .= '</td>';
                $text .= '</tr>';
            }
        }
        $text .= '</tbody>';
        $text .= '</table>';

        $text .= '</div>';
        $text .= '</div>';
        $text .= '<p>';
        $text .= '<br />'.EB_CLANM_L27.' [<a href="'.e_PLUGIN.'ebattles/clans.php">'.EB_CLANM_L28.'</a>]<br />';
        $text .= '</p>';
    }

    $text .= '
    <script type="text/javascript">
    //<![CDATA[

    setupAllTabs();

    //]]>
    </script>
    ';

    $ns->tablerender("$clan_name - ".EB_CLANM_L1, $text);
}
require_once(FOOTERF);
exit;
?>
