<?php
/**
* quickreport.php
*
* This page is for users to report a loss of a 1v1 match
* the player just needs to input who he conceided to loss to.
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
require_once(HEADERF);

$text = '';

/* Event Name */
$event_id = $_GET['eventid'];

if ( (!isset($_POST['quicklossreport'])) || (!isset($_GET['eventid'])))
{
    $text .= "<br />You are not authorized to report a quick loss.<br />";
    $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]<br />";
}
else
{
    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";

    $result = $sql->db_Query($q);
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");

    $q = "SELECT ".TBL_PLAYERS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
    ." ORDER BY ".TBL_USERS.".user_name";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    $text .= '
    <div class="spacer">
    ';

    $text .= "<form action=\"".e_PLUGIN."ebattles/matchprocess.php\" method=\"post\">";
    $text .= '
    <table>
    <tr>
    <td>
    Player:
    <select class="tbox" name="Player">
    ';

    for($i=0; $i<$num_rows; $i++)
    {
        $pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
        $puid  = mysql_result($result,$i, TBL_USERS.".user_id");
        $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
        $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
        $pteam  = mysql_result($result,$i, TBL_PLAYERS.".Team");
        $pclan = '';
        $pclantag = '';
        if ($etype == "Team Ladder")
        {
            $q_2 = "SELECT ".TBL_CLANS.".*, "
            .TBL_DIVISIONS.".*, "
            .TBL_TEAMS.".* "
            ." FROM ".TBL_CLANS.", "
            .TBL_DIVISIONS.", "
            .TBL_TEAMS
            ." WHERE (".TBL_TEAMS.".TeamID = '$pteam')"
            ."   AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
            ."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
            $result_2 = $sql->db_Query($q_2);
            $num_rows_2 = mysql_numrows($result_2);
            if ($num_rows_2 == 1)
            {
                $pclan  = mysql_result($result_2,0, TBL_CLANS.".Name");
                $pclantag  = mysql_result($result_2,0, TBL_CLANS.".Tag") ."_";
            }
        }

        if($puid != USERID)
        {
            if ($prank==0)
            $prank_txt = "Not ranked";
            else
            $prank_txt = "#$prank";
            $text .= "<option value=\"$pid\">$pclantag$pname ($prank_txt)</option>";
        }
    }

    $text .= '
    </select>
    </td>
    </tr>
    <tr>
    <td>
    ';

    $reported_by = USERID;
    $text .= "<div>";
    $text .= "<input type=\"hidden\" name=\"eventid\" value=\"$event_id\"/>";
    $text .= "<input type=\"hidden\" name=\"reported_by\" value=\"$reported_by\"/>";

    $text .= '
    <input class="button" type="submit" name="qrsubmitloss" value="Submit Loss"/>
    </div>
    </td>
    </tr>
    </table>
    </form>
    </div>
    ';
}

$ns->tablerender('Quick Loss Report', $text);
require_once(FOOTERF);
exit;
?>
