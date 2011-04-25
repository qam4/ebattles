<?php
/**
* TournamentManage.php
*
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/clan.php");

// Specify if we use WYSIWYG for text areas
global $e_wysiwyg;
$e_wysiwyg	= "tournamentdescription,tournamentrules";  // set $e_wysiwyg before including HEADERF
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


$tournament_id = $_GET['TournamentID'];
$self = $_SERVER['PHP_SELF'];

if (!$tournament_id)
{
    header("Location: ./tournaments.php");
    exit();
}
else
{
    $q = "SELECT ".TBL_TOURNAMENTS.".*, "
    .TBL_GAMES.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_TOURNAMENTS.", "
    .TBL_GAMES.", "
    .TBL_USERS
    ." WHERE (".TBL_TOURNAMENTS.".TournamentID = '$tournament_id')"
    ."   AND (".TBL_TOURNAMENTS.".Game = ".TBL_GAMES.".GameID)"
    ."   AND (".TBL_USERS.".user_id = ".TBL_TOURNAMENTS.".Owner)";

    $result = $sql->db_Query($q);
    $egame = mysql_result($result,0 , TBL_GAMES.".Name");
    $egameicon  = mysql_result($result,0 , TBL_GAMES.".Icon");
    $egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
    $eowner = mysql_result($result,0 , TBL_USERS.".user_id");
    $eownername = mysql_result($result,0 , TBL_USERS.".user_name");

    $tournament = new Tournament($tournament_id);


    if($tournament->getField('StartDateTime')!=0)
    {
        $StartDateTime_local = $tournament->getField('StartDateTime') + TIMEOFFSET;
        $date_start = date("m/d/Y h:i A", $StartDateTime_local);
    }
    else
    {
        $date_start = "";
    }

    $can_manage = 0;
    if (check_class($pref['eb_mod_class'])) $can_manage = 1;
    if (USERID==$eowner) $can_manage = 1;
    if ($can_manage == 0)
    {
        header("Location: ./tournamentinfo.php?TournamentID=$tournament_id");
        exit();
    }
    else
    {
        //***************************************************************************************
        // tab-page "Tournament Summary"
        $text .= '
        <div class="tab-pane" id="tab-pane-3">

        <div class="tab-page">
        <div class="tab">'.EB_TOURNAMENTM_L2.'</div>
        ';

        $text .= '
        <form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_TOURNAMENTM_L8.'</b></td>';
        $text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'">'.$tournament->getField('Name').'</a></td>';
        $text .= '</tr>';

        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_TOURNAMENTM_L9.'</b><br />';
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
        $text .= '<td><select class="tbox" name="tournamentowner">';
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
        $text .= ebImageTextButton('tournamentchangeowner', 'user_go.ico', EB_TOURNAMENTM_L10);
        $text .= '</td>';
        $text .= '</tr>';
        $text .= '</table>';
        $text .= '</td>';
        $text .= '</tr>';

        $q = "SELECT ".TBL_MODS.".*, "
        .TBL_USERS.".*"
        ." FROM ".TBL_MODS.", "
        .TBL_USERS
        ." WHERE (".TBL_MODS.".Tournament = '$tournament_id')"
        ."   AND (".TBL_USERS.".user_id = ".TBL_MODS.".User)";
        $result = $sql->db_Query($q);
        $numMods = mysql_numrows($result);
        $text .= '
        <tr>
        ';
        $text .= '<td class="forumheader3"><b>'.EB_TOURNAMENTM_L11.'</b></td>';
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
                $text .= '<input type="hidden" name="tournamentmod" value="'.$modid.'"/>';
                $text .= ebImageTextButton('tournamentdeletemod', 'user_delete.ico', EB_TOURNAMENTM_L12, 'negative', EB_TOURNAMENTM_L13);
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
        '.ebImageTextButton('tournamentaddmod', 'user_add.png', EB_TOURNAMENTM_L14).'
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
        ';  // tab-page "Tournament Summary"

        //***************************************************************************************
        // tab-page "Tournament Settings"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_TOURNAMENTM_L3.'</div>
        ';
        $text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        //<!-- Tournament Name -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L15.'</b></td>
        <td class="forumheader3">
        <div><input class="tbox" type="text" size="40" name="tournamentname" value="'.$tournament->getField('Name').'"/></div>
        </td>
        </tr>
        ';

        //<!-- Tournament Password -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L16.'</b></td>
        <td class="forumheader3">
        <div><input class="tbox" type="text" size="40" name="tournamentpassword" value="'.$tournament->getField('Password').'"/></div>
        </td>
        </tr>
        ';
        //<!-- Tournament Game -->

        $q = "SELECT ".TBL_GAMES.".*"
        ." FROM ".TBL_GAMES
        ." ORDER BY Name";
        $result = $sql->db_Query($q);
        /* Error occurred, return given name by default */
        $numGames = mysql_numrows($result);
        $text .= '<tr>';
        $text .= '<td class="forumheader3"><b>'.EB_TOURNAMENTM_L17.'</b></td>';
        $text .= '<td class="forumheader3"><select class="tbox" name="tournamentgame">';
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
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L18.'</b></td>
        <td class="forumheader3"><select class="tbox" name="tournamenttype">';
        $text .= '<option value="Single Elimination" '.($tournament->getField('Type') == "Single Elimination" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L19.'</option>';
        $text .= '</select>
        </td>
        </tr>
        ';

        //<!-- Match Type -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L126.'</b></td>
        <td class="forumheader3"><select class="tbox" name="tournamentmatchtype">';
        $text .= '<option value="1v1" '.($tournament->getField('MatchType') == "1v1" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L127.'</option>';
        $text .= '<option value="2v2" '.($tournament->getField('MatchType') == "2v2" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L128.'</option>';
        $text .= '<option value="FFA" '.($tournament->getField('MatchType') == "FFA" ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L131.'</option>';
        $text .= '</select>
        </td>
        </tr>
        ';

        //<!-- Max Number of Players -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L132.'</b></td>
        <td class="forumheader3"><select class="tbox" name="tournamentmaxnumberplayers">';
        $text .= '<option value="4" '.($tournament->getField('MaxNumberPlayers') == "4" ? 'selected="selected"' : '') .'>4</option>';
        $text .= '<option value="8" '.($tournament->getField('MaxNumberPlayers') == "8" ? 'selected="selected"' : '') .'>8</option>';
        $text .= '<option value="16" '.($tournament->getField('MaxNumberPlayers') == "16" ? 'selected="selected"' : '') .'>16</option>';
        $text .= '</select>
        </td>
        </tr>
        ';
        
        //<!-- Match report userclass -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L21.'</b></td>
        <td class="forumheader3"><select class="tbox" name="tournamentmatchreportuserclass">';
        $text .= '<option value="'.eb_UC_TOURNAMENT_PLAYER.'" '.($tournament->getField('match_report_userclass') == eb_UC_TOURNAMENT_PLAYER ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L22.'</option>';
        $text .= '<option value="'.eb_UC_TOURNAMENT_MODERATOR.'" '.($tournament->getField('match_report_userclass') == eb_UC_TOURNAMENT_MODERATOR ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L23.'</option>';
        $text .= '<option value="'.eb_UC_TOURNAMENT_OWNER.'" '.($tournament->getField('match_report_userclass') == eb_UC_TOURNAMENT_OWNER ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L24.'</option>';
        $text .= '</select>
        </td>
        </tr>
        ';

        //<!-- Match Approval -->
        $q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES
        ." WHERE (".TBL_MATCHS.".Tournament = '$tournament_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_MATCHS.".Status = 'pending')";
        $result = $sql->db_Query($q);
        $row = mysql_fetch_array($result);
        $nbrMatchesPending = $row['NbrMatches'];


        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L108.'</b><div class="smalltext">'.EB_TOURNAMENTM_L109.'</div></td>
        <td class="forumheader3">
        <div>';
        $text .= '<select class="tbox" name="tournamentmatchapprovaluserclass">';
        $text .= '<option value="'.eb_UC_NONE.'" '.(($tournament->getField('MatchesApproval') == eb_UC_NONE) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L113.'</option>';
        $text .= '<option value="'.eb_UC_TOURNAMENT_PLAYER.'" '.((($tournament->getField('MatchesApproval') & eb_UC_TOURNAMENT_PLAYER)!=0) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L112.'</option>';
        $text .= '<option value="'.eb_UC_TOURNAMENT_MODERATOR.'" '.((($tournament->getField('MatchesApproval') & eb_UC_TOURNAMENT_MODERATOR)!=0) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L111.'</option>';
        $text .= '<option value="'.eb_UC_TOURNAMENT_OWNER.'" '.((($tournament->getField('MatchesApproval') & eb_UC_TOURNAMENT_OWNER)!=0) ? 'selected="selected"' : '') .'>'.EB_TOURNAMENTM_L110.'</option>';
        $text .= '</select>';
        $text .= ($nbrMatchesPending>0) ? '<div><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/>&nbsp;<b>'.$nbrMatchesPending.'&nbsp;'.EB_TOURNAMENT_L64.'</b></div>' : '';
        $text .= '
        </div>
        </td>
        </tr>
        ';

        //<!-- Start Date -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L32.'</b></td>
        <td class="forumheader3">
        <table>
        <tr>
        <td>
        <div><input class="tbox" type="text" name="startdate" id="f_date_start"  value="'.$date_start.'" readonly="readonly" /></div>
        </td>
        <td>
        <img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_start" style="cursor: pointer; border: 1px solid red;" title="'.EB_TOURNAMENTM_L33.'"
        ';
        $text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
        $text .= '
        </td>
        <td>
        <div><input class="button" type="button" value="'.EB_TOURNAMENTM_L34.'" onclick="clearStartDate(this.form);"/></div>
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

        //<!-- Description -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L36.'</b></td>
        <td class="forumheader3">
        ';
        $text .= '<textarea class="tbox" id="tournamentdescription" name="tournamentdescription" cols="70" '.$insertjs.'>'.$tournament->getField('Description').'</textarea>';
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
        '.ebImageTextButton('tournamentsettingssave', 'disk.png', EB_TOURNAMENTM_L37).'
        </div>
        </td></tr></table>

        </form>
        </div>
        ';  // tab-page "Tournament Settings"
        
        //***************************************************************************************
		// tab-page "Tournament Rules"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_TOURNAMENTM_L4.'</div>
        ';
        $text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';

        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
        //<!-- Rules -->
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L38.'</b></td>
        <td class="forumheader3">
        ';
        $text .= '<textarea class="tbox" id="tournamentrules" name="tournamentrules" cols="70" '.$insertjs.'>'.$tournament->getField('Rules').'</textarea>';
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
        '.ebImageTextButton('tournamentrulessave', 'disk.png', EB_TOURNAMENTM_L39).'
        </div>
        </td></tr></table>

        </form>
        </div>
        ';  // tab-page "Tournament Rules"

        //***************************************************************************************
        // tab-page "Tournament Players/Teams"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_TOURNAMENTM_L5.'</div>
        ';

        $pages = new Paginator;

        $array = array(
        'name'   => array(EB_TOURNAMENTM_L55, TBL_USERS.'.user_name'),
        'rank'   => array(EB_TOURNAMENTM_L56, TBL_PLAYERS.'.OverallScore'),
        'games'  => array(EB_TOURNAMENTM_L57, TBL_PLAYERS.'.GamesPlayed'),
        'awards' => array(EB_TOURNAMENTM_L58, '')
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
        ." WHERE (".TBL_PLAYERS.".Tournament = '$tournament_id')"
        ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
        $result = $sql->db_Query($q);
        $row = mysql_fetch_array($result);
        $numPlayers = $row['NbrPlayers'];

        $totalItems = $numPlayers;
        $pages->items_total = $totalItems;
        $pages->mid_range = eb_PAGINATION_MIDRANGE;
        $pages->paginate();

        /* Number of teams */
        switch($tournament->getField('Type'))
        {
            case "Team Tournament":
            case "ClanWar":
            $q = "SELECT COUNT(*) as NbrTeams"
            ." FROM ".TBL_TEAMS
            ." WHERE (".TBL_TEAMS.".Tournament = '$tournament_id')";
            $result = $sql->db_Query($q);
            $row = mysql_fetch_array($result);
            $numTeams = $row['NbrTeams'];

            $text .= '<div class="spacer">';
            $text .= '<p>';
            $text .= $numTeams.' '.EB_TOURNAMENTM_L114.'<br />';
            $text .= '</p>';
            $text .= '</div>';
            break;
            default:
        }

        /* Number of players */
        switch($tournament->getField('Type'))
        {
            case "One Player Tournament":
            case "Team Tournament":
            $text .= '<div class="spacer">';
            $text .= '<p>';
            $text .= $numPlayers.' '.EB_TOURNAMENTM_L40.'<br />';
            $text .= '</p>';
            $text .= '</div>';
            break;
            default:
        }

        /* Add Team/Player */
        switch($tournament->getField('MatchType'))
        {
            case "2v2":
            // Form to add a team's division to the tournament
            $q = "SELECT ".TBL_DIVISIONS.".*, "
            .TBL_CLANS.".*"
            ." FROM ".TBL_DIVISIONS.", "
            .TBL_CLANS
            ." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
            ."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
            $result = $sql->db_Query($q);
            /* Error occurred, return given name by default */
            $numDivisions = mysql_numrows($result);

            $text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
            $text .= '
            <table class="fborder" style="width:95%">
            <tbody>
            <tr>
            <td class="forumheader3">
            <b>'.EB_TOURNAMENTM_L41.'</b>
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
            '.ebImageTextButton('tournamentaddteam', 'user_add.png', EB_TOURNAMENTM_L42).'
            <input class="tbox" type="checkbox" name="tournamentaddteamnotify"/>'.EB_TOURNAMENTM_L43.'
            </td>
            </tr>
            </tbody>
            </table>
            </form>
            ';
            break;
            case "1v1":
            // Form to add a player to the tournament
            $q = "SELECT ".TBL_USERS.".*"
            ." FROM ".TBL_USERS;
            $result = $sql->db_Query($q);
            /* Error occurred, return given name by default */
            $numUsers = mysql_numrows($result);
            $text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
            $text .= '
            <table class="fborder" style="width:95%">
            <tbody>
            <tr>
            <td class="forumheader3">
            <b>'.EB_TOURNAMENTM_L44.'</b>
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
            <td>'.ebImageTextButton('tournamentaddplayer', 'user_add.png', EB_TOURNAMENTM_L45).'</td>
            <td><div><input class="tbox" type="checkbox" name="tournamentaddplayernotify"/>'.EB_TOURNAMENTM_L46.'</div></td>
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
        $text .= '<tr><td style="vertical-align:top">'.EB_TOURNAMENTM_L47.':</td>';
        $text .= '<td>'.EB_TOURNAMENTM_L48.'</td></tr>';
        $text .= '<tr><td style="vertical-align:top">'.EB_TOURNAMENTM_L49.':</td>';
        $text .= '<td>'.EB_TOURNAMENTM_L50.'</td></tr>';
        $text .= '</table>';

        switch($tournament->getField('Type'))
        {
            case "Team Tournament":
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
            ." AND (".TBL_TEAMS.".Tournament = '$tournament_id')";
            $result = $sql->db_Query($q_Teams);
            $num_rows = mysql_numrows($result);
            if(!$result || ($num_rows < 0)){
                $text .= EB_TOURNAMENTM_L51.'<br />';
            }
            if($num_rows == 0){
                $text .= EB_TOURNAMENTM_L115.'<br />';
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

        switch($tournament->getField('Type'))
        {
            case "One Player Tournament":
            case "Team Tournament":
            $orderby_array = $array["$orderby"];
            $q_Players = "SELECT ".TBL_PLAYERS.".*, "
            .TBL_USERS.".*"
            ." FROM ".TBL_PLAYERS.", "
            .TBL_USERS
            ." WHERE (".TBL_PLAYERS.".Tournament = '$tournament_id')"
            ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
            ." ORDER BY $orderby_array[1] $sort"
            ." $pages->limit";
            $result = $sql->db_Query($q_Players);
            $num_rows = mysql_numrows($result);
            if(!$result || ($num_rows < 0)){
                $text .= EB_TOURNAMENTM_L51.'<br />';
            }
            if($num_rows == 0){
                $text .= EB_TOURNAMENTM_L52.'<br />';
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
                $text .= '<form id="playersform" action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
                $text .= '<table class="fborder" style="width:95%"><tbody>';
                $text .= '<tr>';
                foreach($array as $opt=>$opt_array)
                {
                    $text .= '<td class="forumheader"><a href="'.e_PLUGIN.'ebattles/tournamentmanage.php?TournamentID='.$tournament_id.'&amp;orderby='.$opt.'&amp;sort='.$sort.'">'.$opt_array[0].'</a></td>';
                }
                $text .= '<td class="forumheader">'.EB_TOURNAMENTM_L59;
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
                    list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);

                    $q_awards = "SELECT COUNT(*) as NbrAwards"
                    ." FROM ".TBL_AWARDS
                    ." WHERE (".TBL_AWARDS.".Player = '$pid')";
                    $result_awards = $sql->db_Query($q_awards);
                    $row = mysql_fetch_array($result_awards);
                    $pawards = $row['NbrAwards'];

                    if ($prank == 0) $prank = EB_TOURNAMENTM_L53;

                    $text .= '<tr>';
                    $text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a></td>';
                    $text .= '<td class="forumheader3">'.(($pbanned) ? EB_TOURNAMENTM_L54 : $prank).'</td>';
                    $text .= '<td class="forumheader3">'.$pgames.'</td>';
                    $text .= '<td class="forumheader3">'.$pawards.'</td>';
                    $text .= '<td class="forumheader3">';
                    if ($pbanned)
                    {
                        $text .= ' <a href="javascript:unban_player(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L60.'" onclick="return confirm(\''.EB_TOURNAMENTM_L61.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_go.ico" alt="'.EB_TOURNAMENTM_L60.'"/></a>';
                    }
                    else
                    {
                        $text .= ' <a href="javascript:ban_player(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L62.'" onclick="return confirm(\''.EB_TOURNAMENTM_L63.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_delete.ico" alt="'.EB_TOURNAMENTM_L62.'"/></a>';
                    }
                    if (($pgames == 0)&&($pawards == 0))
                    {
                        $text .= ' <a href="javascript:kick_player(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L64.'" onclick="return confirm(\''.EB_TOURNAMENTM_L65.'\')"><img src="'.e_PLUGIN.'ebattles/images/cross.png" alt="'.EB_TOURNAMENTM_L64.'"/></a>';
                    }
                    if ($pgames != 0)
                    {
                        $text .= ' <a href="javascript:del_player_games(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L66.'" onclick="return confirm(\''.EB_TOURNAMENTM_L67.'\')"><img src="'.e_PLUGIN.'ebattles/images/controller_delete.ico" alt="'.EB_TOURNAMENTM_L66.'"/></a>';
                    }
                    if ($pawards != 0)
                    {
                        $text .= ' <a href="javascript:del_player_awards(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L68.'" onclick="return confirm(\''.EB_TOURNAMENTM_L69.'\')"><img src="'.e_PLUGIN.'ebattles/images/award_star_delete.ico" alt="'.EB_TOURNAMENTM_L68.'"/></a>';
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
        ';  // tab-page "Tournament Players/Teams"

        //***************************************************************************************
        // tab-page "Tournament Reset"
        $text .= '
        <div class="tab-page">
        <div class="tab">'.EB_TOURNAMENTM_L6.'</div>
        ';
        $text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
        $text .= '
        <table class="fborder" style="width:95%">
        <tbody>
        ';
 
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L74.'</b><br />'.EB_TOURNAMENTM_L75.'</td>
        <td class="forumheader3">
        ';
        $text .= ebImageTextButton('tournamentresettournament', 'bin_closed.png', EB_TOURNAMENTM_L76, '', EB_TOURNAMENTM_L77);
        $text .= '
        </td>
        </tr>
        ';
        $text .= '
        <tr>
        <td class="forumheader3"><b>'.EB_TOURNAMENTM_L78.'</b><br />'.EB_TOURNAMENTM_L79.'</td>
        <td class="forumheader3">
        ';
        $text .= ebImageTextButton('tournamentdelete', 'delete.png', EB_TOURNAMENTM_L80, 'negative', EB_TOURNAMENTM_L81);
        $text .= '
        </td>
        </tr>
        ';
        $text .= '
        </tbody>
        </table>
        </form>
        </div>
        ';  // tab-page "Tournament Reset"
        
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

$ns->tablerender($tournament->getField('Name')." ($egame - ".tournamentTypeToString($tournament->getField('Type')).") - ".EB_TOURNAMENTM_L1, $text);
require_once(FOOTERF);
exit;
?>
