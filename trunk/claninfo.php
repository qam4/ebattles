<?php
/**
*claninfo.php
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

/* Clan Name */
$clan_id = $_GET['clanid'];
$time = GMT_time();

if (!$clan_id)
{
    header("Location: ./clans.php");
    exit();
}
else
{
    if(isset($_POST['joindivision']))
    {
        $div_id = $_POST['division'];
        $q = " INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
        VALUES ($div_id,".USERID.",$time)";
        $sql->db_Query($q);

        // User will automatically be signed up to all current events this division participates in
        $q_2 = "SELECT ".TBL_TEAMS.".*, "
        .TBL_EVENTS.".*"
        ." FROM ".TBL_TEAMS.", "
        .TBL_EVENTS
        ." WHERE (".TBL_TEAMS.".Division = '$div_id')"
        ." AND (".TBL_TEAMS.".Event = ".TBL_EVENTS.".EventID)"
        ." AND (   (".TBL_EVENTS.".End_timestamp = '')"
        ."        OR (".TBL_EVENTS.".End_timestamp > $time)) ";

        $result_2 = $sql->db_Query($q_2);
        $num_rows_2 = mysql_numrows($result_2);
        if($num_rows_2>0)
        {
            for($j=0; $j<$num_rows_2; $j++)
            {
                $eid  = mysql_result($result_2,$j, TBL_EVENTS.".EventID");
                $eELOdefault  = mysql_result($result_2,$j, TBL_EVENTS.".ELO_default");
                $eTS_default_mu  = mysql_result($result_2,$j, TBL_EVENTS.".TS_default_mu");
                $eTS_default_sigma  = mysql_result($result_2,$j, TBL_EVENTS.".TS_default_sigma");
                $team_id = mysql_result($result_2,$j, TBL_TEAMS.".TeamID");
                $q = " INSERT INTO ".TBL_PLAYERS."(Event,User,Team,ELORanking,TS_mu,TS_sigma)
                VALUES ($eid,".USERID.",$team_id,$eELOdefault,$eTS_default_mu,$eTS_default_sigma)";
                $sql->db_Query($q);
                $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$eid')";
                $result = $sql->db_Query($q4);
            }
        }
        header("Location: claninfo.php?clanid=$clan_id");
    }

    $text ='<script type="text/javascript" src="./js/tabpane.js"></script>';

    $text .= '
    <div class="tab-pane" id="tab-pane-6">
    ';
    /**
    * Display Latest Games
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">Team Summary</div>
    ';
    displayTeamSummary($clan_id);
    $text .= '</div>';

    /**
    * Display Divisions
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">Divisions</div>
    ';
    displayTeamDivisions($clan_id);
    $text .= '</div>';

    /**
    * Display Events
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">Events</div>
    ';
    displayTeamEvents($clan_id);
    $text .= '</div>';

    $text .= '
    </div>

    <p>
    <br />Back to [<a href="'.e_PLUGIN.'ebattles/clans.php">Teams</a>]<br />
    </p>

    <script type="text/javascript">
    //<![CDATA[
    setupAllTabs();
    //]]>
    </script>
    ';
}
$ns->tablerender('eBattles', $text);
require_once(FOOTERF);
exit;

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayTeamSummary - Displays ...
*/
function displayTeamSummary($clan_id){
    global $sql;
    global $text;

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

    $text .= "<h1>$clan_name ($clan_tag)</h1>";

    $text .= "<p>Owner: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$clan_owner\">$clan_owner_name</a><br />";
    $can_manage = 0;
    if (check_class($pref['eb_mod'])) $can_manage = 1;
    if (USERID==$clan_owner) $can_manage = 1;
    if ($can_manage == 1)
    $text .="<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$clan_id\">Click here to Manage Team</a><br />";
    $text .="</p>";
}

/**
* displayTeamDivisions - Displays ...
*/
function displayTeamDivisions($clan_id){
    global $sql;
    global $text;

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

        $text .="<div class=\"spacer\">";
        $text .= "<h2><img src=\"".e_PLUGIN."ebattles/images/games_icons/$gicon\" alt=\"$gicon\"/> $gname</h2>";
        $text .= "<p>Captain: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$div_captain\">$div_captain_name</a></p>";

        if(check_class(e_UC_MEMBER))
        {
            $q_2 = "SELECT ".TBL_MEMBERS.".*"
            ." FROM ".TBL_MEMBERS
            ." WHERE (".TBL_MEMBERS.".Division = '$div_id')"
            ." AND (".TBL_MEMBERS.".User = ".USERID.")";
            $result_2 = $sql->db_Query($q_2);
            if(!$result_2 || (mysql_numrows($result_2) < 1))
            {
                $text .= "
                <form action=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$clan_id\" method=\"post\">
                <div>
                <input type=\"hidden\" name=\"division\" value=\"$div_id\"/>
                <input class=\"button\" type=\"submit\" name=\"joindivision\" value=\"Join Division\"/>
                </div>
                </form>";
            }
        }

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
            $text .= "<p>No members</p>";
        }
        else
        {
            $row = mysql_fetch_array($result_2);
            $num_rows_2 = mysql_numrows($result_2);

            $text .= "<p>$num_rows_2 member(s)</p>";

            $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
            $text .= "<tr><td class=\"forumheader\"><b>Name</b></td><td class=\"forumheader\"><b>Status</b></td><td class=\"forumheader\"><b>Joined</b></td></tr>\n";
            for($j=0; $j<$num_rows_2; $j++)
            {
                $mid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                $mname  = mysql_result($result_2,$j, TBL_USERS.".user_name");
                $mjoined  = mysql_result($result_2,$j, TBL_MEMBERS.".timestamp");
                $mjoined_local = $mjoined + GMT_TIMEOFFSET;
                $date = date("d M Y",$mjoined_local);

                $text .= "<tr>\n";
                $text .= "<td class=\"forumheader3\"><b><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mid\">$mname</a></b></td><td class=\"forumheader3\">Member</td><td class=\"forumheader3\">$date</td></tr>";

            }
            $text .= "</tbody></table>\n";
        }
        $text .="</div>";
    }
}

/**
* displayTeamEvents - Displays ...
*/
function displayTeamEvents($clan_id){
    global $sql;
    global $text;

    $q = "SELECT ".TBL_CLANS.".*, "
    .TBL_DIVISIONS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_CLANS.", "
    .TBL_DIVISIONS.", "
    .TBL_GAMES
    ." WHERE (".TBL_CLANS.".ClanID = '$clan_id')"
    ." AND (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
    ." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    for($i=0; $i<$num_rows; $i++)
    {
        $gname  = mysql_result($result,$i, TBL_GAMES.".Name");
        $gicon  = mysql_result($result,$i , TBL_GAMES.".Icon");
        $div_id  = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");

        $text .="<div class=\"spacer\">";
        $text .= "<h2><img src=\"".e_PLUGIN."ebattles/images/games_icons/$gicon\" alt=\"$gicon\"/> $gname</h2>";

        $q_2 = "SELECT ".TBL_TEAMS.".*, "
        .TBL_EVENTS.".*"
        ." FROM ".TBL_TEAMS.", "
        .TBL_EVENTS
        ." WHERE (".TBL_TEAMS.".Division = '$div_id')"
        ." AND (".TBL_TEAMS.".Event = ".TBL_EVENTS.".EventID)"
        ." AND (   (".TBL_EVENTS.".End_timestamp = '')"
        ."        OR (".TBL_EVENTS.".End_timestamp > $time)) ";

        $result_2 = $sql->db_Query($q_2);
        if(!$result_2 || (mysql_numrows($result_2) < 1))
        {
            $text .= "<p>No current events</p>";
        }
        else
        {
            $row = mysql_fetch_array($result_2);
            $num_rows_2 = mysql_numrows($result_2);

            $text .= "<p>$num_rows_2 current event(s)</p>";

            $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
            $text .= "<tr><td class=\"forumheader\"><b>Event</b></td><td class=\"forumheader\"><b>Rank</b></td></tr>\n";
            for($j=0; $j<$num_rows_2; $j++)
            {
                $eid  = mysql_result($result_2,$j, TBL_EVENTS.".EventID");
                $ename  = mysql_result($result_2,$j, TBL_EVENTS.".Name");
                $erank  = mysql_result($result_2,$j, TBL_TEAMS.".Rank");

                $text .= "<tr>\n";
                $text .= "<td class=\"forumheader3\"><b><a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a></b></td><td class=\"forumheader3\">$erank</td></tr>";
            }
            $text .= "</tbody></table>\n";
        }

        $q_2 = "SELECT ".TBL_TEAMS.".*, "
        .TBL_EVENTS.".*"
        ." FROM ".TBL_TEAMS.", "
        .TBL_EVENTS
        ." WHERE (".TBL_TEAMS.".Division = '$div_id')"
        ." AND (".TBL_TEAMS.".Event = ".TBL_EVENTS.".EventID)"
        ." AND (    (".TBL_EVENTS.".End_timestamp != '')"
        ."      AND (".TBL_EVENTS.".End_timestamp < $time)) ";

        $result_2 = $sql->db_Query($q_2);
        if(!$result_2 || (mysql_numrows($result_2) < 1))
        {
            $text .= "<p>No old events</p>";
        }
        else
        {
            $row = mysql_fetch_array($result_2);
            $num_rows_2 = mysql_numrows($result_2);

            $text .= "<p>$num_rows_2 old event(s)</p>";

            $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
            $text .= "<tr><td class=\"forumheader\"><b>Event</b></td><td class=\"forumheader\"><b>Rank</b></td></tr>\n";
            for($j=0; $j<$num_rows_2; $j++)
            {
                $eid  = mysql_result($result_2,$j, TBL_EVENTS.".EventID");
                $ename  = mysql_result($result_2,$j, TBL_EVENTS.".Name");
                $erank  = mysql_result($result_2,$j, TBL_TEAMS.".Rank");

                $text .= "<tr>\n";
                $text .= "<td class=\"forumheader3\"><b><a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a></b></td><td class=\"forumheader3\">$erank</td></tr>";
            }
            $text .= "</tbody></table>\n";
        }
        $text .="</div>";
    }
}

?>
