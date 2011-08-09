<?php
/**
* EventManage.php
*
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/clan.php");

// Specify if we use WYSIWYG for text areas
global $e_wysiwyg;
$e_wysiwyg	= "eventdescription,eventrules";  // set $e_wysiwyg before including HEADERF
require_once(HEADERF);
// Include userclass file
require_once(e_HANDLER."userclass_class.php");

if (e_WYSIWYG)
{
    $insertjs = "rows='25'";
}
else
{
    require_once(e_HANDLER."ren_help.php");
    $insertjs = "rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
}

/*******************************************************************
********************************************************************/
$text = '
<script type="text/javascript" src="./js/slider.js"></script>
<script type="text/javascript" src="./js/tabpane.js"></script>

<!-- main calendar program -->
<script type="text/javascript" src="./js/calendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="./js/calendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="./js/calendar/calendar-setup.js"></script>
<script type="text/javascript">
<!--//
function clearStartDate(frm)
{
frm.startdate.value = ""
}
//-->
</script>
<script type="text/javascript">
<!--//
function clearEndDate(frm)
{
frm.enddate.value = ""
}
//-->
</script>
';
$text .= "
<script type='text/javascript'>
<!--//
function kick_player(v)
{
document.getElementById('kick_player').value=v;
document.getElementById('playersform').submit();
}
function ban_player(v)
{
document.getElementById('ban_player').value=v;
document.getElementById('playersform').submit();
}
function unban_player(v)
{
document.getElementById('unban_player').value=v;
document.getElementById('playersform').submit();
}
function del_player_games(v)
{
document.getElementById('del_player_games').value=v;
document.getElementById('playersform').submit();
}
function del_player_awards(v)
{
document.getElementById('del_player_awards').value=v;
document.getElementById('playersform').submit();
}
//-->
</script>
";


$event_id = $_GET['eventid'];
$self = $_SERVER['PHP_SELF'];

if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    $q = "SELECT ".TBL_EVENTS.".*, "
    .TBL_GAMES.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_EVENTS.", "
    .TBL_GAMES.", "
    .TBL_USERS
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTS.".Owner)";

    $result = $sql->db_Query($q);
    $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
    $epassword = mysql_result($result,0 , TBL_EVENTS.".Password");
    $egame = mysql_result($result,0 , TBL_GAMES.".Name");
    $egameicon  = mysql_result($result,0 , TBL_GAMES.".Icon");
    $egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
    $eowner = mysql_result($result,0 , TBL_USERS.".user_id");
    $eownername = mysql_result($result,0 , TBL_USERS.".user_name");
    $emingames = mysql_result($result,0 , TBL_EVENTS.".nbr_games_to_rank");
    $eminteamgames = mysql_result($result,0 , TBL_EVENTS.".nbr_team_games_to_rank");
    $erules = mysql_result($result,0 , TBL_EVENTS.".Rules");
    $edescription = mysql_result($result,0 , TBL_EVENTS.".Description");
    $eAllowDraw = mysql_result($result,0 , TBL_EVENTS.".AllowDraw");
    $eAllowScore = mysql_result($result,0 , TBL_EVENTS.".AllowScore");
    $eMatchesApproval = mysql_result($result,0 , TBL_EVENTS.".MatchesApproval");
    $eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
    $eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");
    $eTS_beta = mysql_result($result,0 , TBL_EVENTS.".TS_beta");
    $eTS_epsilon = mysql_result($result,0 , TBL_EVENTS.".TS_epsilon");
    $ePointPerWin = mysql_result($result,0 , TBL_EVENTS.".PointsPerWin");
    $ePointPerDraw = mysql_result($result,0 , TBL_EVENTS.".PointsPerDraw");
    $ePointPerLoss = mysql_result($result,0 , TBL_EVENTS.".PointsPerLoss");
    $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
    $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
    $ehide_ratings_column = mysql_result($result,0 , TBL_EVENTS.".hide_ratings_column");
    $ematch_report_userclass = mysql_result($result,0 , TBL_EVENTS.".match_report_userclass");
    $equick_loss_report = mysql_result($result,0 , TBL_EVENTS.".quick_loss_report");
    $eranking_type = mysql_result($result,0 , TBL_EVENTS.".RankingType");
    $echallengesenabled = mysql_result($result,0 , TBL_EVENTS.".ChallengesEnable");
    $eMaxDatesPerChallenge = mysql_result($result,0 , TBL_EVENTS.".MaxDatesPerChallenge");
    $eMaxMapsPerMatch = mysql_result($result,0 , TBL_EVENTS.".MaxMapsPerMatch");
    $eMaxPlayers = mysql_result($result,0 , TBL_EVENTS.".MaxPlayers");

    if($estart!=0)
    {
        $estart_local = $estart + TIMEOFFSET;
        $date_start = date("m/d/Y h:i A",$estart_local);
    }
    else
    {
        $date_start = "";
    }
    if($eend!=0)
    {
        $eend_local = $eend + TIMEOFFSET;
        $date_end = date("m/d/Y h:i A",$eend_local);
    }
    else
    {
        $date_end = "";
    }

    $can_manage = 0;
    if (check_class($pref['eb_mod_class'])) $can_manage = 1;
    if (USERID==$eowner) $can_manage = 1;
    if ($can_manage == 0)
    {
        header("Location: ./eventinfo.php?eventid=$event_id");
        exit();
    }
    else
    {
        //***************************************************************************************
        // tab-page "Event Summary"
        $text .= '
        <div class="tab-pane" id="tab-pane-3">

        <div class="tab-page">
        <div class="tab">'.EB_EVENTM_L2.'</div>
        ';

        $text .= '
        <form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_EVENTM_L8.'</b></td>';
        $text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.$ename.'</a></td>';
        $text .= '</tr>';

        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_EVENTM_L9.'</b><br />';
        $text .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$eowner.'">'.$eownername.'</a>';
        $text .= '</td>';

        $q_2 = "SELECT ".TBL_USERS.".*"
        ." FROM ".TBL_USERS;
        $result_2 = $sql->db_Query($q_2);
        $row = mysql_fetch_array($result_2);
        $num_rows_2 = mysql_numrows($result_2);

        $text .= '<td class="forumheader3">';
        $text .= '<table>';
        $text .= '<tr>';
        $text .= '<td><select class="tbox" name="eventowner">';
        for($j=0; $j<$num_rows_2; $j++)
        {
            $uid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
            $uname  = mysql_result($result_2,$j, TBL_USERS.".user_name");

            if ($eowner == $uid)
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
        $text .= ebImageTextButton('eventchangeowner', 'user_go.ico', EB_EVENTM_L10);
        $text .= '</td>';
        $text .= '</tr>';
        $text .= '</table>';
        $text .= '</td>';
        $text .= '</tr>';

        $q = "SELECT ".TBL_EVENTMODS.".*, "
        .TBL_USERS.".*"
        ." FROM ".TBL_EVENTMODS.", "
        .TBL_USERS
        ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
        ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTMODS.".User)";
        $result = $sql->db_Query($q);
        $numMods = mysql_numrows($result);
        $text .= '
        <tr>
        ';
        $text .= '<td class="forumheader3"><b>'.EB_EVENTM_L11.'</b></td>';
        $text .= '<td class="forumheader3">';
        if ($numMods>0)
        {
            $text .= '<table>';
            for($i=0; $i<$numMods; $i++){
                $modid  = mysql_result($result,$i, TBL_USERS.".user_id");
                $modname  = mysql_result($result,$i, TBL_USERS.".user_name");
                $text .= '<tr>';
                $text .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$modid.'">'.$modname.'</a></td>';
                $text .= '<td>';
                $text .= '<div>';
                $text .= '<input type="hidden" name="eventmod" value="'.$modid.'"/>';
                $text .= ebImageTextButton('eventdeletemod', 'user_delete.ico', EB_EVENTM_L12, 'negative', EB_EVENTM_L13);
                $text .= '</div>';
                $text .= '</td>';
                $text .= '</tr>';
            }
            $text .= '</table>';
        }
        $q = "SELECT ".TBL_USERS.".*"
        ." FROM ".TBL_USERS;
        $result = $sql->db_Query($q);
        /* Error occurred, return given name by default */
        $numUsers = mysql_numrows($result);
        $text .= '
        <table>
        <tr>
        <td>
        <select class="tbox" name="mod">
        ';
        for($i=0; $i<$numUsers; $i++)
        {
            $uid  = mysql_result($result,$i, TBL_USERS.".user_id");
            $uname  = mysql_result($result,$i, TBL_USERS.".user_name");
            $text .= '<option value="'.$uid.'">'.$uname.'</option>';
        }
        $text .= '
        </select>
        </td>
        <td>
        <div>
        '.ebImageTextButton('eventaddmod', 'user_add.png', EB_EVENTM_L14).'
        </div>
        </td>
        </tr>
        </table>
        ';
        $text .= '
        </td>
        </tr>
        </tbody>
        </table>
        </form>
        </div>
        ';  // tab-page "Event Summary"

        //***************************************************************************************
        // tab-page "Event Settings"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_EVENTM_L3.'</div>
        ';
        $text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        //<!-- Event Name -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L15.'</b></td>
        <td class="forumheader3">
        <div><input class="tbox" type="text" size="40" name="eventname" value="'.$ename.'"/></div>
        </td>
        </tr>
        ';

        //<!-- Event Password -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L16.'</b></td>
        <td class="forumheader3">
        <div><input class="tbox" type="text" size="40" name="eventpassword" value="'.$epassword.'"/></div>
        </td>
        </tr>
        ';
        //<!-- Event Game -->

        $q = "SELECT ".TBL_GAMES.".*"
        ." FROM ".TBL_GAMES
        ." ORDER BY Name";
        $result = $sql->db_Query($q);
        /* Error occurred, return given name by default */
        $numGames = mysql_numrows($result);
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_EVENTM_L17.'</b></td>';
        $text .= '<td class="forumheader3"><select class="tbox" name="eventgame">';
        for($i=0; $i<$numGames; $i++){
            $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
            $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
            if ($egame == $gname)
            {
                $text .= '<option value="'.$gid.'" selected="selected">'.htmlspecialchars($gname).'</option>';
            }
            else
            {
                $text .= '<option value="'.$gid.'">'.htmlspecialchars($gname).'</option>';
            }
        }
        $text .= '</select>';
        $text .= '</td></tr>';

        //<!-- Type -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L18.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="radio" size="40" name="eventtype" '.($etype == "One Player Ladder" ? 'checked="checked"' : '').' value="Individual" />'.EB_EVENTM_L19;
        $text .= '<input class="tbox" type="radio" size="40" name="eventtype" '.($etype == "Team Ladder" ? 'checked="checked"' : '').' value="Team" />'.EB_EVENTM_L20;
        $text .= '<input class="tbox" type="radio" size="40" name="eventtype" '.($etype == "ClanWar" ? 'checked="checked"' : '').' value="ClanWar" />'.EB_EVENTM_L116;

        $text .= '
        </div>
        </td>
        </tr>
        ';

        //<!-- Rating Type -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L117.'</b><div class="smalltext">'.EB_EVENTM_L118.'</div></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="radio" size="40" name="eventrankingtype" '.($eranking_type == "Classic" ? 'checked="checked"' : '').' value="Classic" />'.EB_EVENTM_L119;
        $text .= '<input class="tbox" type="radio" size="40" name="eventrankingtype" '.($eranking_type == "CombinedStats" ? 'checked="checked"' : '').' value="CombinedStats" />'.EB_EVENTM_L120;

        $text .= '
        </div>
        </td>
        </tr>
        ';

        //<!-- Match report userclass -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L21.'</b></td>
        <td class="forumheader3"><select class="tbox" name="eventmatchreportuserclass">';
        $text .= '<option value="'.eb_UC_EVENT_PLAYER.'" '.($ematch_report_userclass == eb_UC_EVENT_PLAYER ? 'selected="selected"' : '') .'>'.EB_EVENTM_L22.'</option>';
        $text .= '<option value="'.eb_UC_EVENT_MODERATOR.'" '.($ematch_report_userclass == eb_UC_EVENT_MODERATOR ? 'selected="selected"' : '') .'>'.EB_EVENTM_L23.'</option>';
        $text .= '<option value="'.eb_UC_EVENT_OWNER.'" '.($ematch_report_userclass == eb_UC_EVENT_OWNER ? 'selected="selected"' : '') .'>'.EB_EVENTM_L24.'</option>';
        $text .= '</select>
        </td>
        </tr>
        ';

        //<!-- Allow Quick Loss Report -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L25.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="checkbox" name="eventallowquickloss"';
        if ($equick_loss_report == TRUE)
        {
            $text .= ' checked="checked"/>';
        }
        else
        {
            $text .= '/>';
        }
        $text .= '
        </div>
        </td>
        </tr>
        ';

        //<!-- Allow Score -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L26.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="checkbox" name="eventallowscore"';
        if ($eAllowScore == TRUE)
        {
            $text .= ' checked="checked"/>';
        }
        else
        {
            $text .= '/>';
        }
        $text .= '
        </div>
        </td>
        </tr>
        ';

        //<!-- Match Approval -->
        $q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES
        ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_MATCHS.".Status = 'pending')";
        $result = $sql->db_Query($q);
        $row = mysql_fetch_array($result);
        $nbrMatchesPending = $row['NbrMatches'];


        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L108.'</b><div class="smalltext">'.EB_EVENTM_L109.'</div></td>
        <td class="forumheader3">
        <div>';
        $text .= '<select class="tbox" name="eventmatchapprovaluserclass">';
        $text .= '<option value="'.eb_UC_NONE.'" '.(($eMatchesApproval == eb_UC_NONE) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L113.'</option>';
        $text .= '<option value="'.eb_UC_EVENT_PLAYER.'" '.((($eMatchesApproval & eb_UC_EVENT_PLAYER)!=0) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L112.'</option>';
        $text .= '<option value="'.eb_UC_EVENT_MODERATOR.'" '.((($eMatchesApproval & eb_UC_EVENT_MODERATOR)!=0) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L111.'</option>';
        $text .= '<option value="'.eb_UC_EVENT_OWNER.'" '.((($eMatchesApproval & eb_UC_EVENT_OWNER)!=0) ? 'selected="selected"' : '') .'>'.EB_EVENTM_L110.'</option>';
        $text .= '</select>';
        $text .= ($nbrMatchesPending>0) ? '<div><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/>&nbsp;<b>'.$nbrMatchesPending.'&nbsp;'.EB_EVENT_L64.'</b></div>' : '';
        $text .= '
        </div>
        </td>
        </tr>
        ';

        //<!-- Allow Draws -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L27.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="checkbox" name="eventallowdraw"';
        if ($eAllowDraw == TRUE)
        {
            $text .= ' checked="checked"/>';
        }
        else
        {
            $text .= '/>';
        }
        $text .= '
        </div>
        </td>
        </tr>
        ';

        //<!-- Points -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L28.'</b></td>
        <td class="forumheader3">
        <table>
        <tr>
        <td>'.EB_EVENTM_L29.'</td>
        <td>'.EB_EVENTM_L30.'</td>
        <td>'.EB_EVENTM_L31.'</td>
        </tr>
        <tr>
        <td>
        <div><input class="tbox" type="text" name="eventpointsperwin" value="'.$ePointPerWin.'"/></div>
        </td>
        <td>
        <div><input class="tbox" type="text" name="eventpointsperdraw" value="'.$ePointPerDraw.'"/></div>
        </td>
        <td>
        <div><input class="tbox" type="text" name="eventpointsperloss" value="'.$ePointPerLoss.'"/></div>
        </td>
        </tr>
        </table>
        ';
        $text .= '
        </td>
        </tr>
        ';

        //<!-- Maps -->
         $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L125.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="text" name="eventmaxmapspermatch" size="2" value="'.$eMaxMapsPerMatch.'"';
        $text .= '
        </div>
        </td>
        </tr>
        '; 

        //<!-- Players -->
         $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L126.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="text" name="eventmaxplayers" size="2" value="'.$eMaxPlayers.'"';
        $text .= '
        </div>
        </td>
        </tr>
        '; 
        
        //<!-- Start Date -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L32.'</b></td>
        <td class="forumheader3">
        <table>
        <tr>
        <td>
        <div><input class="tbox" type="text" name="startdate" id="f_date_start"  value="'.$date_start.'" readonly="readonly" /></div>
        </td>
        <td>
        <img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_start" style="cursor: pointer; border: 1px solid red;" title="'.EB_EVENTM_L33.'"
        ';
        $text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
        $text .= '
        </td>
        <td>
        <div><input class="button" type="button" value="'.EB_EVENTM_L34.'" onclick="clearStartDate(this.form);"/></div>
        </td>
        </tr>
        </table>
        ';
        $text .= '
        <script type="text/javascript">
        Calendar.setup({
        inputField     :    "f_date_start",      // id of the input field
        ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_start",   // trigger for the calendar (button ID)
        singleClick    :    true,           // single-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
        });
        </script>
        </td>
        </tr>
        ';

        //<!-- End Date -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L35.'</b></td>
        <td class="forumheader3">
        <table>
        <tr>
        <td>
        <div><input class="tbox" type="text" name="enddate" id="f_date_end"  value="'.$date_end.'" readonly="readonly" /></div>
        </td>
        <td>
        <img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_end" style="cursor: pointer; border: 1px solid red;" title="'.EB_EVENTM_L33.'"
        ';
        $text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
        $text .= '
        </td>
        <td>
        <div><input class="button" type="button" value="'.EB_EVENTM_L34.'" onclick="clearEndDate(this.form);"/></div>
        </td>
        </tr>
        </table>
        ';
        $text .= '
        <script type="text/javascript">
        Calendar.setup({
        inputField     :    "f_date_end",      // id of the input field
        ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_end",   // trigger for the calendar (button ID)
        singleClick    :    true,           // single-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
        });
        </script>
        </td>
        </tr>
        ';

        //<!-- Description -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L36.'</b></td>
        <td class="forumheader3">
        ';
        $text .= '<textarea class="tbox" id="eventdescription" name="eventdescription" cols="70" '.$insertjs.'>'.$edescription.'</textarea>';
        if (!e_WYSIWYG)
        {
            $text .= '<br />'.display_help("helpb",1);
        }
        $text .= '
        </td>
        </tr>
        </tbody>
        </table>
        ';

        //<!-- Save Button -->
        $text .= '
        <table><tr><td>
        <div>
        '.ebImageTextButton('eventsettingssave', 'disk.png', EB_EVENTM_L37).'
        </div>
        </td></tr></table>

        </form>
        </div>
        ';  // tab-page "Event Settings"
        
        //***************************************************************************************
		// tab-page "Event Rules"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_EVENTM_L4.'</div>
        ';
        $text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';

        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        //<!-- Rules -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L38.'</b></td>
        <td class="forumheader3">
        ';
        $text .= '<textarea class="tbox" id="eventrules" name="eventrules" cols="70" '.$insertjs.'>'.$erules.'</textarea>';
        if (!e_WYSIWYG)
        {
            $text .= '<br />'.display_help("helpb",1);
        }
        $text .= '
        </td>
        </tr>
        </tbody>
        </table>
        ';
        //<!-- Save Button -->
        $text .= '
        <table><tr><td>
        <div>
        '.ebImageTextButton('eventrulessave', 'disk.png', EB_EVENTM_L39).'
        </div>
        </td></tr></table>

        </form>
        </div>
        ';  // tab-page "Event Rules"

        //***************************************************************************************
        // tab-page "Event Players/Teams"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_EVENTM_L5.'</div>
        ';

        $pages = new Paginator;

        $array = array(
        'name'   => array(EB_EVENTM_L55, TBL_USERS.'.user_name'),
        'rank'   => array(EB_EVENTM_L56, TBL_PLAYERS.'.OverallScore'),
        'games'  => array(EB_EVENTM_L57, TBL_PLAYERS.'.GamesPlayed'),
        'awards' => array(EB_EVENTM_L58, '')
        );

        if (!isset($_GET['orderby'])) $_GET['orderby'] = 'rank';
        $orderby=$_GET['orderby'];

        $sort = "DESC";
        if(isset($_GET["sort"]) && !empty($_GET["sort"]))
        {
            $sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
        }

        $q = "SELECT COUNT(*) as NbrPlayers"
        ." FROM ".TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
        ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
        $result = $sql->db_Query($q);
        $row = mysql_fetch_array($result);
        $numPlayers = $row['NbrPlayers'];

        $totalItems = $numPlayers;
        $pages->items_total = $totalItems;
        $pages->mid_range = eb_PAGINATION_MIDRANGE;
        $pages->paginate();

        /* Number of teams */
        switch($etype)
        {
            case "Team Ladder":
            case "ClanWar":
            $q = "SELECT COUNT(*) as NbrTeams"
            ." FROM ".TBL_TEAMS
            ." WHERE (".TBL_TEAMS.".Event = '$event_id')";
            $result = $sql->db_Query($q);
            $row = mysql_fetch_array($result);
            $numTeams = $row['NbrTeams'];

            $text .= '<div class="spacer">';
            $text .= '<p>';
            $text .= $numTeams.' '.EB_EVENTM_L114.'<br />';
            $text .= '</p>';
            $text .= '</div>';
            break;
            default:
        }

        /* Number of players */
        switch($etype)
        {
            case "One Player Ladder":
            case "Team Ladder":
            $text .= '<div class="spacer">';
            $text .= '<p>';
            $text .= $numPlayers.' '.EB_EVENTM_L40.'<br />';
            $text .= '</p>';
            $text .= '</div>';
            break;
            default:
        }

        /* Add Team/Player */
        switch($etype)
        {
            case "Team Ladder":
            case "ClanWar":
            // Form to add a team's division to the event
            $q = "SELECT ".TBL_DIVISIONS.".*, "
            .TBL_CLANS.".*"
            ." FROM ".TBL_DIVISIONS.", "
            .TBL_CLANS
            ." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
            ."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
            $result = $sql->db_Query($q);
            /* Error occurred, return given name by default */
            $numDivisions = mysql_numrows($result);

            $text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
            $text .= '
            <table class="fborder" style="width:95%">
            <tbody>
            <tr>
            <td class="forumheader3">
            <b>'.EB_EVENTM_L41.'</b>
            </td>
            <td class="forumheader3">
            <select class="tbox" name="division">
            ';
            for($i=0; $i<$numDivisions; $i++)
            {
                $did  = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");
                $dname  = mysql_result($result,$i, TBL_CLANS.".Name");
                $text .= '<option value="'.$did.'">'.$dname.'</option>';
            }
            $text .= '
            </select>
            '.ebImageTextButton('eventaddteam', 'user_add.png', EB_EVENTM_L42).'
            <input class="tbox" type="checkbox" name="eventaddteamnotify"/>'.EB_EVENTM_L43.'
            </td>
            </tr>
            </tbody>
            </table>
            </form>
            ';
            break;
            case "One Player Ladder":
            // Form to add a player to the event
            $q = "SELECT ".TBL_USERS.".*"
            ." FROM ".TBL_USERS;
            $result = $sql->db_Query($q);
            /* Error occurred, return given name by default */
            $numUsers = mysql_numrows($result);
            $text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
            $text .= '
            <table class="fborder" style="width:95%">
            <tbody>
            <tr>
            <td class="forumheader3">
            <b>'.EB_EVENTM_L44.'</b>
            </td>
            <td class="forumheader3">
            <table>
            <tr>
            <td><div><select class="tbox" name="player">
            ';
            for($i=0; $i<$numUsers; $i++)
            {
                $uid  = mysql_result($result,$i, TBL_USERS.".user_id");
                $uname  = mysql_result($result,$i, TBL_USERS.".user_name");
                $text .= '<option value="'.$uid.'">'.$uname.'</option>';
            }
            $text .= '
            </select></div></td>
            <td>'.ebImageTextButton('eventaddplayer', 'user_add.png', EB_EVENTM_L45).'</td>
            <td><div><input class="tbox" type="checkbox" name="eventaddplayernotify"/>'.EB_EVENTM_L46.'</div></td>
            </tr>
            </table>
            </td>
            </tr>
            </tbody>
            </table>
            </form>
            ';
            break;
            default:
        }

        $text .= '<br /><table>';
        $text .= '<tr><td style="vertical-align:top">'.EB_EVENTM_L47.':</td>';
        $text .= '<td>'.EB_EVENTM_L48.'</td></tr>';
        $text .= '<tr><td style="vertical-align:top">'.EB_EVENTM_L49.':</td>';
        $text .= '<td>'.EB_EVENTM_L50.'</td></tr>';
        $text .= '</table>';

        switch($etype)
        {
            case "Team Ladder":
            case "ClanWar":
            // Show list of teams here
            $q_Teams = "SELECT ".TBL_CLANS.".*, "
            .TBL_TEAMS.".*, "
            .TBL_DIVISIONS.".* "
            ." FROM ".TBL_CLANS.", "
            .TBL_TEAMS.", "
            .TBL_DIVISIONS
            ." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
            ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
            ." AND (".TBL_TEAMS.".Event = '$event_id')";
            $result = $sql->db_Query($q_Teams);
            $num_rows = mysql_numrows($result);
            if(!$result || ($num_rows < 0)){
                $text .= EB_EVENTM_L51.'<br />';
            }
            if($num_rows == 0){
                $text .= EB_EVENTM_L115.'<br />';
            }
            else
            {
                $text .= '<table class="fborder" style="width:95%"><tbody>';
                $text .= '<tr><td class="forumheader"><b>'.EB_CLANS_L5.'</b></td>
                <td class="forumheader"><b>'.EB_CLANS_L6.'</b></td></tr>';
                for($i=0; $i < $num_rows; $i++){
                    $clanid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
                    $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
                    $ctag  = mysql_result($result,$i, TBL_CLANS.".Tag");
                    $cavatar  = mysql_result($result,$i, TBL_CLANS.".Image");
                    $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");

                    $image = "";
                    if ($pref['eb_avatar_enable_teamslist'] == 1)
                    {
                        if($cavatar)
                        {
                            $image = '<img '.getAvatarResize(getImagePath($cavatar, 'team_avatars')).' style="vertical-align:middle"/>';
                        } else if ($pref['eb_avatar_default_team_image'] != ''){
                            $image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_team_image'], 'team_avatars')).' style="vertical-align:middle"/>';
                        }
                    }

                    $text .= '<tr>
                    <td class="forumheader3">'.$image.'&nbsp;<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$clanid.'">'.$cname.'</a></td>
                    <td class="forumheader3">'.$ctag.'</td></tr>';
                }
                $text .= '</tbody></table>';
            }
            break;
            default:
        }

        switch($etype)
        {
            case "One Player Ladder":
            case "Team Ladder":
            $orderby_array = $array["$orderby"];
            $q_Players = "SELECT ".TBL_PLAYERS.".*, "
            .TBL_USERS.".*"
            ." FROM ".TBL_PLAYERS.", "
            .TBL_USERS
            ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
            ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
            ." ORDER BY $orderby_array[1] $sort"
            ." $pages->limit";
            $result = $sql->db_Query($q_Players);
            $num_rows = mysql_numrows($result);
            if(!$result || ($num_rows < 0)){
                $text .= EB_EVENTM_L51.'<br />';
            }
            if($num_rows == 0){
                $text .= EB_EVENTM_L52.'<br />';
            }
            else
            {
                // Paginate
                $text .= '<br />';
                $text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
                $text .= '<span style="float:right">';
                // Go To Page
                $text .= $pages->display_jump_menu();
                $text .= '&nbsp;&nbsp;&nbsp;';
                // Items per page
                $text .= $pages->display_items_per_page();
                $text .= '</span><br /><br />';
                /* Display table contents */
                $text .= '<form id="playersform" action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
                $text .= '<table class="fborder" style="width:95%"><tbody>';
                $text .= '<tr>';
                foreach($array as $opt=>$opt_array)
                {
                    $text .= '<td class="forumheader"><a href="'.e_PLUGIN.'ebattles/eventmanage.php?eventid='.$event_id.'&amp;orderby='.$opt.'&amp;sort='.$sort.'">'.$opt_array[0].'</a></td>';
                }
                $text .= '<td class="forumheader">'.EB_EVENTM_L59;
                $text .= '<input type="hidden" id="ban_player" name="ban_player" value=""/>';
                $text .= '<input type="hidden" id="unban_player" name="unban_player" value=""/>';
                $text .= '<input type="hidden" id="kick_player" name="kick_player" value=""/>';
                $text .= '<input type="hidden" id="del_player_games" name="del_player_games" value=""/>';
                $text .= '<input type="hidden" id="del_player_awards" name="del_player_awards" value=""/>';
                $text .= '</td></tr>';
                for($i=0; $i<$num_rows; $i++)
                {
                    $pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
                    $puid = mysql_result($result,$i, TBL_USERS.".user_id");
                    $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
                    $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
                    $pbanned = mysql_result($result,$i, TBL_PLAYERS.".Banned");
                    $pgames = mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
                    $pteam = mysql_result($result,$i, TBL_PLAYERS.".Team");
                    list($pclan, $pclantag, $pclanid) = getClanName($pteam);

                    $q_awards = "SELECT COUNT(*) as NbrAwards"
                    ." FROM ".TBL_AWARDS
                    ." WHERE (".TBL_AWARDS.".Player = '$pid')";
                    $result_awards = $sql->db_Query($q_awards);
                    $row = mysql_fetch_array($result_awards);
                    $pawards = $row['NbrAwards'];

                    if ($prank == 0) $prank = EB_EVENTM_L53;

                    $text .= '<tr>';
                    $text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a></td>';
                    $text .= '<td class="forumheader3">'.(($pbanned) ? EB_EVENTM_L54 : $prank).'</td>';
                    $text .= '<td class="forumheader3">'.$pgames.'</td>';
                    $text .= '<td class="forumheader3">'.$pawards.'</td>';
                    $text .= '<td class="forumheader3">';
                    if ($pbanned)
                    {
                        $text .= ' <a href="javascript:unban_player(\''.$pid.'\');" title="'.EB_EVENTM_L60.'" onclick="return confirm(\''.EB_EVENTM_L61.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_go.ico" alt="'.EB_EVENTM_L60.'"/></a>';
                    }
                    else
                    {
                        $text .= ' <a href="javascript:ban_player(\''.$pid.'\');" title="'.EB_EVENTM_L62.'" onclick="return confirm(\''.EB_EVENTM_L63.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_delete.ico" alt="'.EB_EVENTM_L62.'"/></a>';
                    }
                    if (($pgames == 0)&&($pawards == 0))
                    {
                        $text .= ' <a href="javascript:kick_player(\''.$pid.'\');" title="'.EB_EVENTM_L64.'" onclick="return confirm(\''.EB_EVENTM_L65.'\')"><img src="'.e_PLUGIN.'ebattles/images/cross.png" alt="'.EB_EVENTM_L64.'"/></a>';
                    }
                    if ($pgames != 0)
                    {
                        $text .= ' <a href="javascript:del_player_games(\''.$pid.'\');" title="'.EB_EVENTM_L66.'" onclick="return confirm(\''.EB_EVENTM_L67.'\')"><img src="'.e_PLUGIN.'ebattles/images/controller_delete.ico" alt="'.EB_EVENTM_L66.'"/></a>';
                    }
                    if ($pawards != 0)
                    {
                        $text .= ' <a href="javascript:del_player_awards(\''.$pid.'\');" title="'.EB_EVENTM_L68.'" onclick="return confirm(\''.EB_EVENTM_L69.'\')"><img src="'.e_PLUGIN.'ebattles/images/award_star_delete.ico" alt="'.EB_EVENTM_L68.'"/></a>';
                    }
                    $text .= '</td>';
                    $text .= '</tr>';
                }
                $text .= '</tbody></table>';
                $text .= '</form>';
            }
            break;
            default:
        }

        $text .= '
        </div>
        ';  // tab-page "Event Players/Teams"

        //***************************************************************************************
        // tab-page "Event Reset"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_EVENTM_L6.'</div>
        ';
        $text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L70.'</b><br />'.EB_EVENTM_L71.'</td>
        <td class="forumheader3">
        ';
        $text .= ebImageTextButton('eventresetscores', 'bin_closed.png', EB_EVENTM_L72, '', EB_EVENTM_L73);
        $text .= '
        </td>
        </tr>
        ';
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L74.'</b><br />'.EB_EVENTM_L75.'</td>
        <td class="forumheader3">
        ';
        $text .= ebImageTextButton('eventresetevent', 'bin_closed.png', EB_EVENTM_L76, '', EB_EVENTM_L77);
        $text .= '
        </td>
        </tr>
        ';
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L78.'</b><br />'.EB_EVENTM_L79.'</td>
        <td class="forumheader3">
        ';
        $text .= ebImageTextButton('eventdelete', 'delete.png', EB_EVENTM_L80, 'negative', EB_EVENTM_L81);
        $text .= '
        </td>
        </tr>
        ';
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L82.'</b><br />'.EB_EVENTM_L83.'</td>
        <td class="forumheader3">
        ';
        $text .= ebImageTextButton('eventupdatescores', 'chart_curve.png', EB_EVENTM_L84, '', EB_EVENTM_L85);
        $text .= '
        </td>
        </tr>
        </tbody>
        </table>
        </form>
        </div>
        ';  // tab-page "Event Reset"
        
        //***************************************************************************************
        // tab-page "Event Stats"
        $cat_index = 0;
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_EVENTM_L7.'</div>
        ';
        $text .= EB_EVENTM_L86;
        $text .= "
        <script type='text/javascript'>
        var A_TPL = {
        'b_vertical' : false,
        'b_watch': true,
        'n_controlWidth': 100,
        'n_controlHeight': 16,
        'n_sliderWidth': 17,
        'n_sliderHeight': 16,
        'n_pathLeft' : 0,
        'n_pathTop' : 0,
        'n_pathLength' : 83,
        's_imgControl': 'images/slider/sldr3h_bg.gif',
        's_imgSlider': 'images/slider/sldr3h_sl.gif',
        'n_zIndex': 1
        }
        </script>
        ";

        $text .= '<form id="eventstatsform" action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
        $text .= '
        <table class="fborder" style="width:95%"><tbody>';

        $text .= '
        <tr>
        <td class="forumheader">'.EB_EVENTM_L87.'</td>
        <td class="forumheader" colspan="2">'.EB_EVENTM_L88.'</td>
        <td class="forumheader">'.EB_EVENTM_L89.'</td>
        </tr>';
        if ($etype != "ClanWar")
        {
            $text .= '
            <tr>
            <td class="forumheader3">'.EB_EVENTM_L90.'</td>
            <td class="forumheader3">
            <input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
            </td>
            <td class="forumheader3">
            ';
            $text .= "
            <script type='text/javascript'>
            var A_INIT = {
            's_form' : 'eventstatsform',
            's_name': 'sliderValue".$cat_index."',
            'n_minValue' : 0,
            'n_maxValue' : 10,
            'n_value' : ".$emingames.",
            'n_step' : 1
            }

            new slider(A_INIT, A_TPL);
            </script>
            ";
            $text .= '
            </td>
            <td class="forumheader3"></td>
            </tr>
            ';
            $cat_index ++;
        }

        if (($etype == "Team Ladder")||($etype == "ClanWar"))
        {
            $text .= '
            <tr>
            <td class="forumheader3">'.EB_EVENTM_L91.'</td>
            <td class="forumheader3">
            <input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
            </td>
            <td class="forumheader3">
            ';
            $text .= "
            <script type='text/javascript'>
            var A_INIT = {
            's_form' : 'eventstatsform',
            's_name': 'sliderValue".$cat_index."',
            'n_minValue' : 0,
            'n_maxValue' : 10,
            'n_value' : ".$eminteamgames.",
            'n_step' : 1
            }

            new slider(A_INIT, A_TPL);
            </script>
            ";
            $text .= '
            </td>
            <td class="forumheader3"></td>
            </tr>
            ';
            $cat_index ++;
        }

        $q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
        ." FROM ".TBL_STATSCATEGORIES
        ." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')";

        $result_1 = $sql->db_Query($q_1);
        $numCategories = mysql_numrows($result_1);

        $rating_max=0;
        for($i=0; $i<$numCategories; $i++)
        {
            $cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");
            $cat_min = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMinValue");
            $cat_max = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMaxValue");
            $cat_InfoOnly = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".InfoOnly");

            switch ($cat_name)
            {

                case "ELO":
                $cat_name_display = EB_EVENTM_L92;
                break;
                case "GamesPlayed":
                $cat_name_display = EB_EVENTM_L93;
                break;
                case "VictoryRatio":
                $cat_name_display = EB_EVENTM_L94;
                break;
                case "VictoryPercent":
                $cat_name_display = EB_EVENTM_L95;
                break;
                case "WinDrawLoss":
                $cat_name_display = EB_EVENTM_L96;
                break;
                case "UniqueOpponents":
                $cat_name_display = EB_EVENTM_L97;
                break;
                case "OpponentsELO":
                $cat_name_display = EB_EVENTM_L98;
                break;
                case "Streaks":
                $cat_name_display = EB_EVENTM_L99;
                break;
                case "Skill":
                $cat_name_display = EB_EVENTM_L100;
                break;
                case "Score":
                $cat_name_display = EB_EVENTM_L101;
                break;
                case "ScoreAgainst":
                $cat_name_display = EB_EVENTM_L102;
                break;
                case "ScoreDiff":
                $cat_name_display = EB_EVENTM_L103;
                break;
                case "Points":
                $cat_name_display = EB_EVENTM_L104;
                break;
                default:
            }

            //---------------------------------------------------
            $text .= '
            <tr>
            <td class="forumheader3">'.$cat_name_display.'</td>
            <td class="forumheader3">
            <input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
            </td>
            <td class="forumheader3">
            ';
            $text .= "
            <script type='text/javascript'>
            var A_INIT = {
            's_form' : 'eventstatsform',
            's_name': 'sliderValue".$cat_index."',
            'n_minValue' : 0,
            'n_maxValue' : 100,
            'n_value' : ".$cat_max.",
            'n_step' : 1
            }

            new slider(A_INIT, A_TPL);
            </script>
            ";
            $text .= '</td>';

            $text .= '
            <td class="forumheader3">
            <input class="tbox" type="checkbox" name="infoonly'.$i.'" value="1"
            ';
            if ($cat_InfoOnly == TRUE)
            {
                $text .= ' checked="checked"';
            }
            else
            {
                $rating_max+=$cat_max;

            }
            $text .= '/></td>';

            $text .= '</tr>';
            //----------------------------------------

            $cat_index++;
        }

        $text .= '
        <tr>
        <td class="forumheader3">'.EB_EVENTM_L105.'</td>
        <td class="forumheader3">'.$rating_max.'</td>
        <td class="forumheader3" colspan="2">
        <input class="tbox" type="checkbox" name="hideratings" value="1"
        ';
        if ($ehide_ratings_column == TRUE)
        {
            $text .= ' checked="checked"';
        }
        $text .= '/>&nbsp;'.EB_EVENTM_L106.'</td>';

        $text .= '
        </tr></tbody></table>

        <!-- Save Button -->
        <table><tr><td>
        <div>
        '.ebImageTextButton('eventstatssave', 'disk.png', EB_EVENTM_L107).'
        </div>
        </td></tr></table>
        </form>
        </div>';   // tab-page "Event Stats"
        
        //***************************************************************************************
        // tab-page "Event Settings"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_EVENTM_L121.'</div>
        ';
        $text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        //<!-- Enable/Disable Challenges -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L122.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="checkbox" name="eventchallengesenable"';
        if ($echallengesenabled == TRUE)
        {
            $text .= ' checked="checked"/>';
        }
        else
        {
            $text .= '/>';
        }
        $text .= '
        </div>
        </td>
        </tr>
        ';
        
        //<!-- Max number of Dates per Challenge -->
         $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_EVENTM_L124.'</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="text" name="eventdatesperchallenge" size="2" value="'.$eMaxDatesPerChallenge.'"';
        $text .= '
        </div>
        </td>
        </tr>
        '; 

        // ------------------------------
         $text .= '
        </tbody>
        </table>
        ';

        //<!-- Save Button -->
        $text .= '
        <table><tr><td>
        <div>
        '.ebImageTextButton('eventchallengessave', 'disk.png', EB_EVENTM_L123).'
        </div>
        </td></tr></table>

        </form>
        </div>
        ';  // tab-page "Event Challenges"        

        $text .= '
        </div>
        <script type="text/javascript">
        //<![CDATA[

        setupAllTabs();

        //]]>
        </script>
        ';
    }
}

$ns->tablerender("$ename ($egame - ".eventType($etype).") - ".EB_EVENTM_L1, $text);
require_once(FOOTERF);
exit;
?>
