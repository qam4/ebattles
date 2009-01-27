<?php
/**
* UserInfo.php
*
* This page is for users to view their account information
* with a link added for them to edit the information.
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text .= '';

/* User */
$req_user = $_GET['user'];

if (!$req_user)
{
    header("Location: ./events.php"); // should be users.php which does not exist yet
    exit();
}
else
{
    $text .= '<script type="text/javascript" src="./js/tabpane.js"></script>';

    /* Logged in user viewing own account */
    if(strcmp(USERID,$req_user) == 0){
    }
    /* Visitor not viewing own account */
    else{
    }

    $q2 = "SELECT ".TBL_USERS.".*"
    ." FROM ".TBL_USERS
    ." WHERE (".TBL_USERS.".user_id = $req_user)";
    $result2 = $sql->db_Query($q2);
    $uid  = mysql_result($result2,0, TBL_USERS.".user_id");
    $uname  = mysql_result($result2,0, TBL_USERS.".user_name");

    $text .= '
    <div class="tab-pane" id="tab-pane-5">

    <div class="tab-page">
    <div class="tab">Profile</div>
    ';

    $text .= "<p>";
    $text .= "User Profile: <a href='".e_BASE."user.php?id.$req_user'>$uname</a>";
    $text .= "</p>";

    /* Display requested user information */
    //$req_user_info = $sql->getUserInfo($req_user);

    /* Username */
    //$text .= "<b>Username: ".$req_user_info['username']."</b><br />";
    /* Username */
    //$text .= "<b>Nickname: ".$req_user_info['name']."</b><br />";
    $text .= "</div>";

    /* Display list of events */
    $text .= '
    <div class="tab-page">
    <div class="tab">Events</div>
    ';
    if(strcmp(USERID,$req_user) == 0){
        $text .= "<form action=\"".e_PLUGIN."ebattles/eventcreate.php\" method=\"post\">";
        $text .= "<input type=\"hidden\" name=\"userid\" value=\"$req_user\"></input>";
        $text .= "<input class=\"button\" type=\"submit\" name=\"createevent\" value=\"Create new event\"></input>";
        $text .= "</form>";
    }
    $text .= "<h2>Player</h2>";
    $text .= "Events in which this user plays";
    $q = " SELECT *"
    ." FROM ".TBL_PLAYERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_PLAYERS.".User = '$req_user')"
    ."   AND (".TBL_PLAYERS.".Event = ".TBL_EVENTS.".EventID)"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        /* Display table contents */
        $text .= "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td>";
        $text .= "Name";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "Rank";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "W/L";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "Status";
        $text .= "</td>";
        $text .= "</tr>";

        for($i=0; $i<$num_rows; $i++)
        {
            $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
            $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
            $pwinloss  = mysql_result($result,$i, TBL_PLAYERS.".Win")."/".mysql_result($result,$i, TBL_PLAYERS.".Loss");
            $text .= "<tr>";
            $text .= "<td>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
            $text .= "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame";
            $text .= "</td>";
            $text .= "<td>";
            $text .= "$prank";
            $text .= "</td>";
            $text .= "<td>";
            $text .= "$pwinloss";
            $text .= "</td>";
            $text .= "<td>";
            if($eowner == $req_user)
            {
                $text .= "Owner";
                if ($eowner == USERID)
                {
                    $text .= " (<a href=\"".e_PLUGIN."ebattles/eventmanage.php?eventid=$eid\">Manage</a>)";
                }
            }
            else
            {
                $text .= "Member";
            }

            $text .= "</td>";    $text .= "</tr>";
        }
        $text .= "</table>";
    }

    $text .= "<h2>Owner</h2>";
    $text .= "Events this user owns";
    $q = " SELECT *"
    ." FROM ".TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_EVENTS.".Owner = '$req_user')"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        /* Display table contents */
        $text .= "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td>";
        $text .= "Name";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "Status";
        $text .= "</td>";
        $text .= "</tr>";

        for($i=0; $i<$num_rows; $i++)
        {
            $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
            $text .= "<tr>";
            $text .= "<td>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
            $text .= "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame";
            $text .= "</td>";
            $text .= "<td>";
            if($eowner == $req_user)
            {
                $text .= "Owner";
                if ($eowner == USERID)
                {
                    $text .= " (<a href=\"".e_PLUGIN."ebattles/eventmanage.php?eventid=$eid\">Manage</a>)";
                }
            }
            else
            {
                $text .= "Member";
            }
            $text .= "</td>";
            $text .= "</tr>";
        }
        $text .= "</table>";
    }

    $text .= "<h2>Moderator</h2>";
    $text .= "Events this user moderates";
    $q = " SELECT *"
    ." FROM ".TBL_EVENTMODS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_EVENTMODS.".User = '$req_user')"
    ."   AND (".TBL_EVENTMODS.".Event = ".TBL_EVENTS.".EventID)"
    ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        /* Display table contents */
        $text .= "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td>";
        $text .= "Name";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "Status";
        $text .= "</td>";
        $text .= "</tr>";

        for($i=0; $i<$num_rows; $i++)
        {
            $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
            $text .= "<tr>";
            $text .= "<td>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
            $text .= "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame";
            $text .= "</td>";
            $text .= "<td>";
            if($eowner == $req_user)
            {
                $text .= "Owner";
                if ($eowner == USERID)
                {
                    $text .= " (<a href=\"".e_PLUGIN."ebattles/eventmanage.php?eventid=$eid\">Manage</a>)";
                }
            }
            else
            {
                $text .= "Member";
            }
            $text .= "</td>";
            $text .= "</tr>";
        }
        $text .= "</table>";
    }
    $text .= "</div>";

    /* Display list of divisions */
    $text .= '
    <div class="tab-page">
    <div class="tab">Teams membership</div>
    ';
    if(strcmp(USERID,$req_user) == 0){
        $text .= "<form action=\"".e_PLUGIN."ebattles/clancreate.php\" method=\"post\">";
        $text .= "<input type=\"hidden\" name=\"userid\" value=\"$req_user\"></input>";
        $text .= "<input type=\"hidden\" name=\"username\" value=\"".USERNAME."\"></input>";
        $text .= "<input class=\"button\" type=\"submit\" name=\"createteam\" value=\"Create new team\"></input>";
        $text .= "</form>";
    }

    $text .= "<h2>Member</h2>";
    $text .= "$uname is member of the following divisions";
    $q = "SELECT ".TBL_CLANS.".*, "
    .TBL_DIVISIONS.".*, "
    .TBL_MEMBERS.".*, "
    .TBL_USERS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_CLANS.", "
    .TBL_DIVISIONS.", "
    .TBL_USERS.", "
    .TBL_MEMBERS.", "
    .TBL_GAMES
    ." WHERE (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
    ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
    ." AND (".TBL_MEMBERS.".User = ".TBL_USERS.".user_id)"
    ." AND (".TBL_USERS.".user_id = '$req_user')"
    ." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        $text .= "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td>";
        $text .= "Division";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "Status";
        $text .= "</td>";
        $text .= "</tr>";
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
            $text .= "<tr>";
            $text .= "<td>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
            $text .= "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$dgameicon\" alt=\"$egameicon\"></img> $dgame";
            $text .= "</td>";
            $text .= "<td>";
            if($cowner == $req_user)
            {
                $text .= "Owner";
                if ($cowner == USERID)
                {
                    $text .= " (<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$cid\">Manage</a>)";
                }
            }
            else
            {
                $text .= "Member";
            }
            $text .= "</td>";
            $text .= "</tr>";

        }
        $text .= "</table>";
    }

    $text .= "<h2>Owner</h2>";
    $text .= "$uname is owner of the following teams";
    $q = "SELECT ".TBL_CLANS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_CLANS.", "
    .TBL_USERS
    ." WHERE (".TBL_CLANS.".Owner = ".TBL_USERS.".user_id)"
    ." AND (".TBL_USERS.".user_id = '$req_user')";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        $text .= "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td>";
        $text .= "Team";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "Status";
        $text .= "</td>";
        $text .= "</tr>";
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
            $text .= "<tr>";
            $text .= "<td>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
            $text .= "</td>";
            $text .= "<td>";
            if($cowner == $req_user)
            {
                $text .= "Owner";
                if ($cowner == USERID)
                {
                    $text .= " (<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$cid\">Manage</a>)";
                }
            }
            else
            {
                $text .= "Member";
            }
            $text .= "</td>";
            $text .= "</tr>";

        }
        $text .= "</table>";
    }

    $text .= "<h2>Captain</h2>";
    $text .= "$uname is captain of the following divisions";
    $q = "SELECT ".TBL_CLANS.".*, "
    .TBL_DIVISIONS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_CLANS.", "
    .TBL_DIVISIONS.", "
    .TBL_GAMES
    ." WHERE (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
    ." AND (".TBL_GAMES.".GameId = ".TBL_DIVISIONS.".Game)"
    ." AND (".TBL_DIVISIONS.".Captain = '$req_user')";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        $text .= "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td>";
        $text .= "Division";
        $text .= "</td>";
        $text .= "<td>";
        $text .= "Status";
        $text .= "</td>";
        $text .= "</tr>";
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $dcaptain  = mysql_result($result,$i, TBL_DIVISIONS.".Captain");
            $dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");


            $text .= "<tr>";
            $text .= "<td>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
            $text .= "<img src=\"".e_PLUGIN."ebattles/images/games_icons/$dgameicon\" alt=\"$egameicon\"></img> $dgame";
            $text .= "</td>";
            $text .= "<td>";
            if($cowner == $req_user)
            {
                $text .= "Owner";
                if ($cowner == USERID)
                {
                    $text .= " (<a href=\"".e_PLUGIN."ebattles/clanmanage.php?clanid=$cid\">Manage</a>)";
                }
            }
            else
            {
                $text .= "Member";
            }
            $text .= "</td>";
            $text .= "</tr>";

        }
        $text .= "</table>";
    }
    $text .= "</div>";

    $text .= '
    <div class="tab-page">
    <div class="tab">Matches</div>
    ';
    /* set pagination variables */
    $rowsPerPage = 5;
    $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
    $start = $rowsPerPage * $pg - $rowsPerPage;

    /* Stats/Results */
    $q = "SELECT count(*) "
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".User = '$req_user')";
    $result = $sql->db_Query($q);
    $totalPages = mysql_result($result, 0);

    $q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_USERS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".User = '$req_user')"
    ." AND (".TBL_MATCHS.".ReportedBy = ".TBL_USERS.".user_id)"
    ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
    ." LIMIT $start, $rowsPerPage";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        /* Display table contents */
        $text .= "<table class=\"fborder\" style=\"width:95%\"><tbody>";
        $text .= "<tr><td class=\"forumheader\" style=\"width:120px\"><b>Match ID</b></td><td class=\"forumheader\"><b>Event</b></td><td class=\"forumheader\" style=\"width:90px\"><b>Reported By</b></td><td class=\"forumheader\"><b>Players</b></td><td class=\"forumheader\" style=\"width:90px\"><b>Date</b></td></tr>\n";
        for($i=0; $i<$num_rows; $i++){
            $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
            $mReportedBy  = mysql_result($result,$i, TBL_MATCHS.".ReportedBy");
            $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $mEvent  = mysql_result($result,$i, TBL_MATCHS.".Event");
            $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
            //$date = date("d M Y, h:i:s A",$mTime);
            $mTime_local = $mTime + GMT_TIMEOFFSET;
            $date = date("d M Y",$mTime_local);

            $q2 = "SELECT ".TBL_EVENTS.".*, "
            .TBL_GAMES.".*"
            ." FROM ".TBL_EVENTS.", "
            .TBL_GAMES
            ." WHERE (".TBL_EVENTS.".eventid = '$mEvent')"
            ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

            $result2 = $sql->db_Query($q2);
            $ename = mysql_result($result2,0 , TBL_EVENTS.".Name");
            $egame = mysql_result($result2,0 , TBL_GAMES.".Name");

            $q2 = "SELECT ".TBL_MATCHS.".*, "
            .TBL_SCORES.".*, "
            .TBL_PLAYERS.".*, "
            .TBL_USERS.".*"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES.", "
            .TBL_PLAYERS.", "
            .TBL_USERS
            ." WHERE (".TBL_MATCHS.".MatchID = '$mID')"
            ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
            ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
            ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
            ." ORDER BY ".TBL_SCORES.".Player_Rank";

            $result2 = $sql->db_Query($q2);
            $num_rows2 = mysql_numrows($result2);
            $pname = '';
            $players = '';
            for($j=0; $j<$num_rows2; $j++)
            {
                $pid  = mysql_result($result2,$j, TBL_USERS.".user_id");
                $pname  = mysql_result($result2,$j, TBL_USERS.".user_name");
                if ($j==0)
                $players = "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pname</a>";
                else
                $players = $players.", <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pname</a>";
            }

            $text .= "<tr>\n";
            $text .= "<td class=\"forumheader3\"><b>$mID</b> <a href=\"".e_PLUGIN."ebattles/matchinfo.php?eventid=$mEvent&amp;matchid=$mID\">(Show details)</a></td><td class=\"forumheader3\"><a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$mEvent\">$ename</a></td><td class=\"forumheader3\"><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mReportedBy\">$mReportedByNickName</a></td><td class=\"forumheader3\">$players</td><td class=\"forumheader3\">$date</td></tr>";


        }
        $text .= "</tbody></table><br />\n";
    }

    $text .= paginate($rowsPerPage, $pg, $totalPages);

    $text .= "</div>";
    $text .= "</div>";

    $text .= '
    <script type="text/javascript">
    //<![CDATA[

    setupAllTabs();

    //]]>
    </script>
    ';
}
$ns->tablerender('Player Information', $text);
require_once(FOOTERF);
exit;
?>
