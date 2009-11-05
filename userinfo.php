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
require_once(e_PLUGIN."ebattles/include/paginator.class.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$pages = new Paginator;

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
    if((strcmp(USERID,$req_user) == 0)&&(check_class($pref['eb_events_create_class'])))
    {
        $text .= "<form action=\"".e_PLUGIN."ebattles/eventcreate.php\" method=\"post\">";
        $text .= "<div>";
        $text .= "<input type=\"hidden\" name=\"userid\" value=\"$req_user\"/>";
        $text .= "<input class=\"button\" type=\"submit\" name=\"createevent\" value=\"Create new event\"/>";
        $text .= "</div>";
        $text .= "</form>";
    }
    $text .= "<b>Player</b><br />";
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
        $text .= "<table class='fborder' cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td class='forumheader3'>";
        $text .= "Name";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
        $text .= "Rank";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
        $text .= "W/L";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
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
            $pwinloss  = mysql_result($result,$i, TBL_PLAYERS.".Win")."/".mysql_result($result,$i, TBL_PLAYERS.".Draw")."/".mysql_result($result,$i, TBL_PLAYERS.".Loss");
            $text .= "<tr>";
            $text .= "<td class='forumheader3'>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
            $text .= "<img ".getGameIconResize($egameicon)."/> $egame";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
            $text .= "$prank";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
            $text .= "$pwinloss";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
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

    $text .= "<br /><b>Owner</b><br />";
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
        $text .= "<table class='fborder' cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td class='forumheader3'>";
        $text .= "Name";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
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
            $text .= "<td class='forumheader3'>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
            $text .= "<img ".getGameIconResize($egameicon)."/> $egame";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
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

    $text .= "<br /><b>Moderator</b><br />";
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
        $text .= "<table class='fborder' cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td class='forumheader3'>";
        $text .= "Name";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
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
            $text .= "<td class='forumheader3'>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$eid\">$ename</a><br />";
            $text .= "<img ".getGameIconResize($egameicon)."/> $egame";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
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
    if((strcmp(USERID,$req_user) == 0)&&(check_class($pref['eb_teams_create_class'])))
    {
        $text .= "<form action=\"".e_PLUGIN."ebattles/clancreate.php\" method=\"post\">";
        $text .= "<div>";
        $text .= "<input type=\"hidden\" name=\"userid\" value=\"$req_user\"/>";
        $text .= "<input type=\"hidden\" name=\"username\" value=\"".USERNAME."\"/>";
        $text .= "<input class=\"button\" type=\"submit\" name=\"createteam\" value=\"Create new team\"/>";
        $text .= "</div>";
        $text .= "</form>";
    }

    $text .= "<b>Member</b><br />";
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
        $text .= "<table class='fborder' cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td class='forumheader3'>";
        $text .= "Division";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
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
            $text .= "<td class='forumheader3'>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
            $text .= "<img ".getGameIconResize($dgameicon)."/> $dgame";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
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

    $text .= "<br /><b>Owner</b><br />";
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
        $text .= "<table class='fborder' cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td class='forumheader3'>";
        $text .= "Team";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
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
            $text .= "<td class='forumheader3'>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
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

    $text .= "<br /><b>Captain</b><br />";
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
        $text .= "<table class='fborder' cellspacing=\"0\" cellpadding=\"3\">\n";
        $text .= "<tr>";
        $text .= "<td class='forumheader3'>";
        $text .= "Division";
        $text .= "</td>";
        $text .= "<td class='forumheader3'>";
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
            $text .= "<td class='forumheader3'>";
            $text .= "<a href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$cid\">$cname</a><br />";
            $text .= "<img ".getGameIconResize($dgameicon)."/> $dgame";
            $text .= "</td>";
            $text .= "<td class='forumheader3'>";
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

    /* Stats/Results */
    /* set pagination variables */
    $q = "SELECT count(*) "
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".User = '$req_user')";
    $result = $sql->db_Query($q);
    $totalItems = mysql_result($result, 0);
    $pages->items_total = $totalItems;
    $pages->mid_range = eb_PAGINATION_MIDRANGE;
    $pages->paginate();

    $q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
    .TBL_SCORES.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_USERS.".*, "
    .TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS.", "
    .TBL_USERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_MATCHS.".Event = ".TBL_EVENTS.".EventID)"
    ." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
    ." AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".User = '$req_user')"
    ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
    ." $pages->limit";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if ($num_rows>0)
    {
        // Paginate
        $text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
        $text .= '<span style="float:right">';
        // Go To Page
        $text .= $pages->display_jump_menu();
        $text .= '&nbsp;&nbsp;&nbsp;';
        // Items per page
        $text .= $pages->display_items_per_page();
        $text .= '</span><br /><br />';

        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
            $mReportedBy  = mysql_result($result,$i, TBL_USERS.".user_id");
            $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $mEventID  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $mEventName  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $mEventgame = mysql_result($result,$i , TBL_GAMES.".Name");
            $mEventgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $mEventType  = mysql_result($result,$i, TBL_EVENTS.".Type");
            $mEventAllowScore = mysql_result($result,$i, TBL_EVENTS.".AllowScore");
            $mTime  = mysql_result($result,$i, TBL_MATCHS.".TimeReported");
            $mTime_local = $mTime + GMT_TIMEOFFSET;
            $date = date("d M Y, h:i A",$mTime_local);
            $q2 = "SELECT DISTINCT ".TBL_MATCHS.".*, "
            .TBL_SCORES.".Player_Rank"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES
            ." WHERE (".TBL_MATCHS.".MatchID = '$mID')"
            ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
            $result2 = $sql->db_Query($q2);
            $numRanks = mysql_numrows($result2);
            if ($numRanks > 0)
            {
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
                ." ORDER BY ".TBL_SCORES.".Player_Rank, ".TBL_SCORES.".Player_MatchTeam";

                $result2 = $sql->db_Query($q2);
                $numPlayers = mysql_numrows($result2);
                $pname = '';
                $players = '';
                $scores = '';

                $players .= "<a href=\"".e_PLUGIN."ebattles/matchinfo.php?matchid=$mID\"><img ".getGameIconResize($mEventgameicon)."/></a> ";

                $rank = 1;
                for ($index = 0; $index < $numPlayers; $index++)
                {
                    $pid  = mysql_result($result2,$index , TBL_USERS.".user_id");
                    $pname  = mysql_result($result2,$index , TBL_USERS.".user_name");
                    $prank  = mysql_result($result2,$index , TBL_SCORES.".Player_Rank");
                    $pteam  = mysql_result($result2,$index , TBL_SCORES.".Player_MatchTeam");
                    $pscore = mysql_result($result2,$index , TBL_SCORES.".Player_Score");
                    $pclan = '';
                    $pclantag = '';
                    if ($mEventType == "Team Ladder")
                    {
                        $q_3 = "SELECT ".TBL_CLANS.".*, "
                        .TBL_DIVISIONS.".*, "
                        .TBL_TEAMS.".* "
                        ." FROM ".TBL_CLANS.", "
                        .TBL_DIVISIONS.", "
                        .TBL_TEAMS
                        ." WHERE (".TBL_TEAMS.".TeamID = '$pteam')"
                        ."   AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
                        ."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
                        $result_3 = $sql->db_Query($q_3);
                        $num_rows_3 = mysql_numrows($result_3);
                        if ($num_rows_3 == 1)
                        {
                            $pclan  = mysql_result($result_3,0, TBL_CLANS.".Name");
                            $pclantag  = mysql_result($result_3,0, TBL_CLANS.".Tag") ."_";
                        }
                    }

                    if($index>0)
                    {
                        if ($pteam == $team)
                        {
                            $players .= " & ";
                        }
                        else
                        {
                            if ($prank == $rank)
                            {
                                $str = " tied ";
                            }
                            else
                            {
                                $str = " defeated ";
                            }
                            $scores .= "-".$pscore;
                            $players .= $str;
                            $team++;
                        }
                    }
                    else
                    {
                        $team = $pteam;
                        $scores .= $pscore;
                    }

                    $players .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$pid\">$pclantag$pname</a>";
                }

                //score here
                if ($mEventAllowScore == TRUE)
                {
                    $players .= " (".$scores.") ";
                }

                $players .= " playing $mEventgame (<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$mEventID\">$mEventName</a>)";

                $players .= " <div class='smalltext'>";
                $players .= "Reported by <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$mReportedBy\">$mReportedByNickName</a> ";
                if (($time-$mTime) < INT_MINUTE )
                {
                    $players .= "a few seconds ago";
                }
                else if (($time-$mTime) < INT_DAY )
                {
                    $players .= get_formatted_timediff($mTime, $time)." ago.";
                }
                else
                {
                    $players .= "on ".$date.".";
                }
                $nbr_comments = getCommentTotal("ebmatches", $mID);
                $players .= " <a href=\"".e_PLUGIN."ebattles/matchinfo.php?matchid=$mID\" title=\"Match $mID\">".$nbr_comments." comment";
                $players .= ($nbr_comments > 1) ? "s" : "";
                $players .= "</a>";
                $players .= "</div>";
            }
            $text .= "$players";
        }
    }

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
