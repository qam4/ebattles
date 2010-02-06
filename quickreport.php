<?php
/**
* quickreport.php
*
* This page is for users to report a loss of a 1v1 match
* the player just needs to input who he conceided to loss to.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
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
    $text .= 'Select the player';

    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
    $result = $sql->db_Query($q);
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");

    $q = "SELECT ".TBL_PLAYERS.".*"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ."   AND (".TBL_PLAYERS.".User = '".USERID."')";
    $result = $sql->db_Query($q);
    $uteam = mysql_result($result,0 , TBL_PLAYERS.".Team");
    
    $q = "SELECT ".TBL_PLAYERS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_PLAYERS.", "
    .TBL_USERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ."   AND (".TBL_PLAYERS.".Banned != 1)"
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
        list($pclan, $pclantag) = getClanName($pteam);

        if(($puid != USERID)&&(($uteam == 0)||($uteam != $pteam)))
        {
            if ($prank==0)
            $prank_txt = EB_EVENT_L54;
            else
            $prank_txt = "#$prank";
            $text .= '<option value="'.$pid.'">'.$pclantag.$pname.' ('.$prank_txt.')</option>';
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

$ns->tablerender("$ename ($egame - ".eventType($etype).") - Quick Loss Report", $text);
require_once(FOOTERF);
exit;
?>
