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
include_once(e_PLUGIN."ebattles/include/main.php");

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
    $text .='<script type="text/javascript" src="./js/tabpane.js"></script>';

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
        <div class="tab">Team Summary</div>
        ';

        $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tbody>';
        $text .= '<!-- Clan -->';
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>Team</b></td>';
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
            $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
            $text .= '<br /><input class="button" type="submit" name="clandelete" value="Delete Team" onclick="return confirm(\'Are you sure you want to delete this team?\');"/>';
            $text .= "</form>";
        }
        $text .= '</td>';

        $text .= '<!-- Clan Owner -->';
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>Owner</b><br />';
        $text .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$clan_owner\">$clan_owner_name</a>";
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
                $text .= "<option value=\"$uid\" selected=\"selected\">$uname</option>\n";
            }
            else
            {
                $text .= "<option value=\"$uid\">$uname</option>\n";
            }
        }
        $text .= '</select>';
        $text .= '</td>';
        $text .= '<td>';
        $text .= '<input class="button" type="submit" name="clanchangeowner" value="Change Owner"/>';
        $text .= '</td>';
        $text .= '</tr>';
        $text .= '</table>';
        $text .= '</td>';
        $text .= '</tr>';

        $text .= '<!-- Clan Name -->';
        $text .= '<tr>';
        $text .= '
        <td class="forumheader3"><b>Name</b></td>
        <td class="forumheader3">
        <input class="tbox" type="text" size="40" name="clanname" value="'.$clan_name.'"/>
        </td>
        </tr>

        <!-- Clan Tag -->
        <tr>
        <td class="forumheader3"><b>Tag</b></td>
        <td class="forumheader3">
        <input class="tbox" type="text" size="40" name="clantag" value="'.$clan_tag.'"/>
        </td>
        </tr>

        <!-- Clan Password -->
        <tr>
        <td class="forumheader3"><b>Password</b></td>
        <td class="forumheader3">
        <input class="tbox" type="text" size="40" name="clanpassword" value="'.$clan_password.'"/>
        </td>
        </tr>

        </tbody>
        </table>
        <!-- Save Button -->
        <table><tbody><tr><td>
        <input class="button" type="submit" name="clansettingssave" value="Save"/>
        </td></tr></tbody></table>
        </form>

        </div>
        ';

        $text .= '
        <div class="tab-page">
        <div class="tab">Team Divisions</div>
        ';

        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tbody>';

        $q = "SELECT ".TBL_GAMES.".*"
        ." FROM ".TBL_GAMES
        ." ORDER BY Name";
        $result = $sql->db_Query($q);
        /* Error occurred, return given name by default */
        $num_rows = mysql_numrows($result);
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= 'Create a division for each game your team plays in';
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
        $text .= "<div>";
        $text .= '<select class="tbox" name="divgame">';
        for($i=0; $i<$num_rows; $i++){
            $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
            $gid  = mysql_result($result,$i, TBL_GAMES.".GameId");
            $text .= "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
        }
        $text .= '</select>';
        $text .= '<input type="hidden" name="clanowner" value="'.$clan_owner.'"/>';
        $text .= '<input class="button" type="submit" name="clanadddiv" value="Add Division"/>';
        $text .= "</div>";
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
            $text .= '<b><img src="'.getGameIcon($gicon).'" alt="'.$gicon.'"/> '.$gname.'</b><br />';
            $text .= "Captain: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$div_captain\">$div_captain_name</a>";

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
            //dbg: echo "division $div_id scores: $numDivScores<br>";
            //dbg: include_once(e_PLUGIN."ebattles/include/show_db_results.php");
            //dbg: show_db_results($result_DivScores);
            if ($numDivScores == 0)
            {
                $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
                $text .= '<input type="hidden" name="clandiv" value="'.$div_id.'"/>';
                $text .= '<input class="button" type="submit" name="clandeletediv" value="Delete Division" onclick="return confirm(\'Are you sure you want to delete this division?\');"/>';
                $text .= "</form>";
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
                $text .= '<td class="forumheader3">No members</td></tr>';
            }
            else
            {
                $row = mysql_fetch_array($result_2);
                $num_rows_2 = mysql_numrows($result_2);

                $text .= '<td class="forumheader3">';
                $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
                $text .= '<table>';
                $text .= '<tr>';
                $text .= '<td><select class="tbox" name="divcaptain">';
                for($j=0; $j<$num_rows_2; $j++)
                {
                    $mid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                    $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");

                    if ($div_captain == $mid)
                    {
                        $text .= "<option value=\"$mid\" selected=\"selected\">$mname</option>\n";
                    }
                    else
                    {
                        $text .= "<option value=\"$mid\">$mname</option>\n";
                    }
                }
                $text .= '</select>';
                $text .= '</td>';
                $text .= '<td>';
                $text .= '<input type="hidden" name="clandiv" value="'.$div_id.'"/>';
                $text .= '<input class="button" type="submit" name="clanchangedivcaptain" value="Change Captain"/>';
                $text .= '</td>';
                $text .= '</tr>';
                $text .= '</table>';
                $text .= '</form>';
                $text .= '</td>';
                $text .= '</tr>';

                $text .= '<tr>';
                $text .= '<td class="forumheader3">'.$num_rows_2.' member(s)</td>';
                $text .= '<td class="forumheader3">';
                $text .= "<form action=\"".e_PLUGIN."ebattles/clanprocess.php?clanid=$clan_id\" method=\"post\">";
                $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
                $text .= "<tr><td class=\"forumheader\"><b>Name</b></td><td class=\"forumheader\"><b>Status</b></td><td class=\"forumheader\"><b>Joined</b></td><td class=\"forumheader\"><b>Kick</b></td></tr>\n";
                for($j=0; $j<$num_rows_2; $j++)
                {
                    $mid  = mysql_result($result_2,$j, TBL_MEMBERS.".MemberID");
                    $muid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                    $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
                    $mjoined  = mysql_result($result_2,$j, TBL_MEMBERS.".timestamp");
                    $mjoined_local = $mjoined + GMT_TIMEOFFSET;
                    $date  = date("d M Y",$mjoined_local);

                    $text .= "<tr>\n";
                    $text .= "<td class=\"forumheader3\"><b><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$muid\">$mname</a></b></td><td class=\"forumheader3\">Member</td><td class=\"forumheader3\">$date</td>";

                    // Checkbox to select which member to kick
                    $text .= "<td class=\"forumheader3\"><input type=\"checkbox\" name=\"del[]\" value=\"$mid\" /></td>\n";
                    $text .= "</tr>";
                }
                $text .= "<tr>";
                $text .= "<td colspan=\"4\">";
                $text .= "<input class=\"button\" type=\"submit\" name=\"kick\" value=\"Kick Selected\"/>";
                $text .= "</td>\n";
                $text .= "</tr>";
                $text .= "</tbody></table>\n";
                $text .= "</form>";
                $text .= '</td>';
                $text .= '</tr>';
            }
        }
        $text .= '</tbody>';
        $text .= '</table>';

        $text .= '</div>';
        $text .= '</div>';
        $text .= '<p>';
        $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/clans.php\">Teams</a>]<br />";
        $text .= '</p>';
    }

    $text .= '
    <script type="text/javascript">
    //<![CDATA[

    setupAllTabs();

    //]]>
    </script>
    ';

    $ns->tablerender("$clan_name - Manage", $text);
}
require_once(FOOTERF);
exit;
?>
