<?php
/**
* EventManage.php
*
*
*/

require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

//these have to be set for the tinymce wysiwyg
global $pref, $e_wysiwyg;

// Enable WYSIWYG
if ($pref['wysiwyg'])
{
// Specify if we use WYSIWYG for text areas
$e_wysiwyg	= "eventdescription,eventrules";
define(e_WYSIWYG, TRUE);
$WYSIWYG = TRUE;
}

/*******************************************************************
********************************************************************/
require_once(HEADERF);
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
    $eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
    $eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");
    $eTS_beta = mysql_result($result,0 , TBL_EVENTS.".TS_beta");
    $eTS_epsilon = mysql_result($result,0 , TBL_EVENTS.".TS_epsilon");
    $ePointPerWin = mysql_result($result,0 , TBL_EVENTS.".PointsPerWin");
    $ePointPerDraw = mysql_result($result,0 , TBL_EVENTS.".PointsPerDraw");
    $ePointPerLoss = mysql_result($result,0 , TBL_EVENTS.".PointsPerLoss");
    $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
    $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
    if($estart!=0)
    {
        $estart_local = $estart + GMT_TIMEOFFSET;
        $date_start = date("m/d/Y h:i A",$estart_local);
    }
    else
    {
        $date_start = "";
    }
    if($eend!=0)
    {
        $eend_local = $eend + GMT_TIMEOFFSET;
        $date_end = date("m/d/Y h:i A",$eend_local);
    }
    else
    {
        $date_end = "";
    }

    $text .= "<h1><a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">$ename</a> ($etype)</h1>";
    $text .= "<h2><img src=\"".getGameIcon($egameicon)."\" alt=\"$egameicon\"/> $egame</h2>";

    $can_manage = 0;
    if (check_class($pref['eb_mod'])) $can_manage = 1;
    if (USERID==$eowner) $can_manage = 1;
    if ($can_manage == 0)
    {
        header("Location: ./eventinfo.php?eventid=$event_id");
        exit();
    }
    else
    {
        //***************************************************************************************
        $text .='
        <div class="tab-pane" id="tab-pane-3">

        <div class="tab-page">
        <div class="tab">Event Summary</div>
        ';

        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        <tr>
        ';
        $text .= '<td class="forumheader3">Owner</td>';
        $text .= '<td class="forumheader3">';
        $text .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$eowner\">$eownername</a></td>";
        $text .= '
        </tr>
        ';

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
        $text .= '<td class="forumheader3">Moderators</td>';
        $text .= '<td class="forumheader3">';
        if ($numMods>0)
        {
            $text .= "<form action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
            $text .= "<table>";
            for($i=0; $i<$numMods; $i++){
                $modid  = mysql_result($result,$i, TBL_USERS.".user_id");
                $modname  = mysql_result($result,$i, TBL_USERS.".user_name");
                $text .="<tr>";
                $text .= "<td><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$modid\">$modname</a></td>";
                $text .= "<td>";
                $text .= "<div>";
                $text .= "<input type=\"hidden\" name=\"eventmod\" value=\"$modid\"/>";
                $text .= "<input class=\"button\" type=\"submit\" name=\"eventdeletemod\" value=\"Remove Moderator\" onclick=\"return confirm('Are you sure you want to remove this moderator?');\"/>";
                $text .= "</div>";
                $text .= "</td>";
                $text .= "</tr>";
            }
            $text .= "</table>";
            $text .= "</form>";
        }
        $text .= "<form action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
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
        for($i=0; $i<$numUsers; $i++){
            $uid  = mysql_result($result,$i, TBL_USERS.".user_id");
            $uname  = mysql_result($result,$i, TBL_USERS.".user_name");
            $text .= "<option value=\"$uid\">$uname</option>\n";
        }
        $text .= '
        </select>
        </td>
        <td>
        <div>
        <input type="hidden" name="eventaddmod"/>
        <input class="button" type="submit" value="Add Moderator"/>
        </div>
        </td>
        </tr>
        </table>
        </form>
        ';
        $text .= '
        </td>
        </tr>
        </tbody>
        </table>
        </div>
        ';

        //***************************************************************************************
        $text .= '
        <div class="tab-page">
        <div class="tab">Event Settings</div>
        ';
        $text .= "<form action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        //<!-- Event Name -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Name</b></td>
        <td class="forumheader3">
        <div><input class="tbox" type="text" size="40" name="eventname" value="'.$ename.'"/></div>
        </td>
        </tr>
        ';

        //<!-- Event Password -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Join Event Password</b></td>
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
        $text .= '<td class="forumheader3"><b>Game</b></td>';
        $text .= '<td class="forumheader3"><select class="tbox" name="eventgame">';
        for($i=0; $i<$numGames; $i++){
            $gname  = mysql_result($result,$i, TBL_GAMES.".name");
            $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
            if ($egame == $gname)
            {
                $text .= "<option value=\"$gid\" selected=\"selected\">".htmlspecialchars($gname)."</option>\n";
            }
            else
            {
                $text .= "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
            }
        }
        $text .= '</select>';
        $text .= '</td></tr>';

        //<!-- Type -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Type</b></td>
        <td class="forumheader3">
        <div>
        ';
        if ($etype == "Team Ladder")
        {
            $text .= '<input class="tbox" type="radio" size="40" name="eventtype" value="Individual" />Individual';
            $text .= '<input class="tbox" type="radio" size="40" name="eventtype" checked="checked" value="Team" />Team';
        }
        else
        {
            $text .= '<input class="tbox" type="radio" size="40" name="eventtype" checked="checked" value="Individual" />Individual';
            $text .= '<input class="tbox" type="radio" size="40" name="eventtype" value="Team" />Team';
        }
        $text .='
        </div>
        </td>
        </tr>
        ';

        //<!-- Allow Score -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Allow Scoring</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="checkbox" name="eventallowscore"';
        if ($eAllowScore == TRUE)
        {
            $text .= 'checked>';
        }
        else
        {
            $text .= '>';
        }
        $text .='
        </div>
        </td>
        </tr>
        ';

        //<!-- Allow Draws -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Allow Draws</b></td>
        <td class="forumheader3">
        <div>
        ';
        $text .= '<input class="tbox" type="checkbox" name="eventallowdraw"';
        if ($eAllowDraw == TRUE)
        {
            $text .= 'checked>';
        }
        else
        {
            $text .= '>';
        }
        $text .='
        </div>
        </td>
        </tr>
        ';

        //<!-- Points -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Points</b></td>
        <td class="forumheader3">
        <table>
        <tr>
        <td>per win</td><td>per draw</td><td>per loss</td>
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
        
        //<!-- Start Date -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Start Date</b></td>
        <td class="forumheader3">
        <table>
        <tr>
        <td>
        <div><input class="tbox" type="text" name="startdate" id="f_date_start"  value="'.$date_start.'" readonly="readonly" /></div>
        </td>
        <td>
        <img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_start" style="cursor: pointer; border: 1px solid red;" title="Date selector"
        ';
        $text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
        $text .= '
        </td>
        <td>
        <div><input class="button" type="button" value="Reset" onclick="clearStartDate(this.form);"/></div>
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
        <td class="forumheader3"><b>End Date</b></td>
        <td class="forumheader3">
        <table>
        <tr>
        <td>
        <div><input class="tbox" type="text" name="enddate" id="f_date_end"  value="'.$date_end.'" readonly="readonly" /></div>
        </td>
        <td>
        <img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_end" style="cursor: pointer; border: 1px solid red;" title="Date selector"
        ';
        $text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
        $text .= '
        </td>
        <td>
        <div><input class="button" type="button" value="Reset" onclick="clearEndDate(this.form);"/></div>
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
        <td class="forumheader3"><b>Description</b></td>
        <td class="forumheader3">
        ';
        $text .= '<textarea class="tbox" id="eventdescription" name="eventdescription" cols="70" rows="20">'.htmlspecialchars($edescription).'</textarea>';
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
        <input class="button" type="submit" name="eventsettingssave" value="Save"/>
        </div>
        </td></tr></table>

        </form>
        </div>
        ';
        //***************************************************************************************
        $text .= '
        <div class="tab-page">
        <div class="tab">Event Rules</div>
        ';
        $text .= "<form action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";

        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        //<!-- Rules -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>Rules</b></td>
        <td class="forumheader3">
        ';
        $text .= '<textarea class="tbox" id="eventrules" name="eventrules" cols="70" rows="20">'.$erules.'</textarea>';
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
        <input class="button" type="submit" name="eventrulessave" value="Save"/>
        </div>
        </td></tr></table>

        </form>
        </div>
        ';

        //***************************************************************************************
        $text .='
        <div class="tab-page">
        <div class="tab">Event Reset</div>
        ';
        $text .= "<form action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
        $text .='
        <table class="fborder" style="width:95%">
        <tbody>
        <tr>
        ';
        $text .= '
        <td class="forumheader3"><b>Reset Players/Teams.</b><br />
        - Reset Players and Teams Statistics (Rank, Score, ELO, Skill, Games Played, Wins, Losses)<br />
        - Delete all Matches
        </td>
        <td class="forumheader3">
        ';
        $text .= "<input class=\"button\" type=\"submit\" name=\"eventresetscores\" value=\"Reset Scores\" onclick=\"return confirm('Are you sure you want to delete this event scores?');\"/>";
        $text .= '
        </td>
        </tr>
        <tr>
        ';
        $text .= '
        <td class="forumheader3"><b>Reset Event.</b><br />
        - Delete all Players and Teams.<br />
        - Delete all Matches.
        </td>
        <td class="forumheader3">
        ';
        $text .= "<input class=\"button\" type=\"submit\" name=\"eventresetevent\" value=\"Reset Event\" onclick=\"return confirm('Are you sure you want to reset this event?');\"/>";
        $text .= '
        </td>
        </tr>
        <tr>
        ';
        $text .= '
        <td class="forumheader3"><b>Delete Event.</b><br />
        - Delete Event.<br />
        </td>
        <td class="forumheader3">
        ';
        $text .= "<input class=\"button\" type=\"submit\" name=\"eventdelete\" value=\"Delete Event\" onclick=\"return confirm('Are you sure you want to delete this event?');\"/>";
        $text .= '
        </td>
        </tr>
        </tbody>
        </table>
        </form>
        </div>
        ';
        //***************************************************************************************
        $cat_index = 0;
        $text .= '
        <div class="tab-page">
        <div class="tab">Event Stats</div>
        ';
        $text .= 'Enter stats maximum ratings for each category.<br />';
        $text .= 'Categories will be displayed in order of max rating.<br />';
        $text .= 'Categories with max=0 will not be displayed.<br />';
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

        $text .= "<form id=\"eventstatsform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
        $text .= '
        <table class="fborder" style="width:95%"><tbody>
        <tr>
        <td class="forumheader">Stat</td>
        <td class="forumheader" colspan=2>Max</td>
        <td class="forumheader">Info Only</td>
        </tr>
        <tr>
        <td class="forumheader3">
        Number of Matches to Rank
        </td>
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

        if ($etype == "Team Ladder")
        {
            $text .= '
            <tr>
            <td class="forumheader3">Number of Team Matches to Rank</td>
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
                $cat_name_display = "ELO";
                break;
                case "GamesPlayed":
                $cat_name_display = "Games Played";
                break;
                case "VictoryRatio":
                $cat_name_display = "Win/Loss";
                break;
                case "VictoryPercent":
                $cat_name_display = "Victory Percent";
                break;
                case "WinDrawLoss":
                $cat_name_display = "Win/Draw/Loss";
                break;
                case "UniqueOpponents":
                $cat_name_display = "Unique Opponents";
                break;
                case "OpponentsELO":
                $cat_name_display = "Opponents Avg ELO";
                break;
                case "Streaks":
                $cat_name_display = "Streaks";
                break;
                case "Skill":
                $cat_name_display = "Skill";
                break;
                case "Score":
                $cat_name_display = "Score";
                break;
                case "ScoreAgainst":
                $cat_name_display = "Opponents Score";
                break;
                case "ScoreDiff":
                $cat_name_display = "Score Difference";
                break;
                case "Points":
                $cat_name_display = "Points";
                break;
                default:
            }

            //---------------------------------------------------
            $text .= '
            <tr>
            <td class="forumheader3">'.$cat_name_display.'</td>
            <td class="forumheader3">
            <input name="sliderValue'.$cat_index.'" id="sliderValue" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
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
                $text .= ' checked';
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
        <td class="forumheader3">Rating Max</td>
        <td class="forumheader3" colspan="3">'.$rating_max.'</td>
        </tr>
        </tbody></table>

        <!-- Save Button -->
        <table><tr><td>
        <div>
        <input class="button" type="submit" name="eventstatssave" value="Save"/>
        </div>
        </td></tr></table>
        </form>
        </div>
        </div>
        <script type="text/javascript">
        //<![CDATA[

        setupAllTabs();

        //]]>
        </script>
        ';
    }
}

$ns->tablerender('Manage Event', $text);
require_once(FOOTERF);
exit;
?>
