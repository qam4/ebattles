<?php
/**
* UserInfo.php
*
* This page is for users to view their account information
* with a link added for them to edit the information.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_HANDLER."rate_class.php");
require_once(e_PLUGIN."ebattles/include/clan.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$pages = new Paginator;
$rater = new rater();

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

    $text .= '<div class="tab-pane" id="tab-pane-5">';

    /*
    --------------------- 
    Player Profile
    ---------------------
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">'.EB_USER_L2.'</div>
    ';

    $text .= '<p>';
    $text .= EB_USER_L7.': <a href="'.e_BASE.'user.php?id.'.$req_user.'">'.$uname.'</a>';
    $text .= '</p>';

    $text .= '</div>';

    /*
    --------------------- 
    Events 
    ---------------------
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">'.EB_USER_L3.'</div>
    ';
    if((strcmp(USERID,$req_user) == 0)&&(check_class($pref['eb_events_create_class'])))
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/eventcreate.php" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="userid" value="'.$req_user.'"/>';
        $text .= '<input type="hidden" name="username" value="'.$uname.'"/>';
        $text .= '<input class="button" type="submit" name="createevent" value="'.EB_EVENTS_L20.'"/>';
        $text .= '</div>';
        $text .= '</form><br />';
    }
    /* Display list of events where the user is a player */
    $text .= '<div class="spacer"><b>'.EB_USER_L8.'</b></div>';
    $text .= '<div>'.$uname.'&nbsp;'.EB_USER_L9.'</div>';
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
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L10;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L11;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L12;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L13;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L14;
        $text .= '</td>';
        $text .= '</tr>';

        for($i=0; $i<$num_rows; $i++)
        {
            $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $eid = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $eowner = mysql_result($result,$i, TBL_EVENTS.".Owner");
            $pid =  mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
            $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
            $pwinloss  = mysql_result($result,$i, TBL_PLAYERS.".Win")."/".mysql_result($result,$i, TBL_PLAYERS.".Draw")."/".mysql_result($result,$i, TBL_PLAYERS.".Loss");

            $q_Scores = "SELECT ".TBL_SCORES.".*, "
            .TBL_PLAYERS.".*"
            ." FROM ".TBL_SCORES.", "
            .TBL_PLAYERS
            ." WHERE (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
            ." AND (".TBL_PLAYERS.".PlayerID = '$pid')";

            $result_Scores = $sql->db_Query($q_Scores);
            $numScores = mysql_numrows($result_Scores);
            $prating = 0;
            $prating_votes = 0;
            for($scoreIndex=0; $scoreIndex<$numScores; $scoreIndex++)
            {
                $sid  = mysql_result($result_Scores,$scoreIndex, TBL_SCORES.".ScoreID");

                // Get user rating.
                $rate = $rater->getrating("ebscores", $sid);

                $prating += $rate[0]*($rate[1] + $rate[2]/10);
                $prating_votes += $rate[0];
            }
        if ($prating_votes !=0)
        {
            $prating /= $prating_votes;
        }
                    $rating = displayRating($prating, $prating_votes);

            $text .= '<tr>';
            $text .= '<td class="forumheader3">';
            $text .= '<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$eid.'">'.$ename.'</a><br />';
            $text .= '<img '.getGameIconResize($egameicon).'/> '.$egame;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            $text .= $prank;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            $text .= $pwinloss;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            $text .= $rating;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            if($eowner == $req_user)
            {
                $text .= EB_USER_L15;
                if ($eowner == USERID)
                {
                    $text .= ' (<a href="'.e_PLUGIN.'ebattles/eventmanage.php?eventid='.$eid.'">'.EB_USER_L16.'</a>)';
                }
            }
            else
            {
                $text .= EB_USER_L17;
            }

            $text .= '</td>';
            $text .= '</tr>';
        }
        $text .= '</table>';
    }

    /* Display list of events where the user is the owner */
    $text .= '<br /><div class="spacer"><b>'.EB_USER_L18.'</b></div>';
    $text .= '<div>'.$uname.'&nbsp;'.EB_USER_L19.'</div>';
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
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L10;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L14;
        $text .= '</td>';
        $text .= '</tr>';

        for($i=0; $i<$num_rows; $i++)
        {
            $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
            $text .= '<tr>';
            $text .= '<td class="forumheader3">';
            $text .= '<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$eid.'">'.$ename.'</a><br />';
            $text .= '<img '.getGameIconResize($egameicon).'/> '.$egame;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            if($eowner == $req_user)
            {
                $text .= EB_USER_L15;
                if ($eowner == USERID)
                {
                    $text .= ' (<a href="'.e_PLUGIN.'ebattles/eventmanage.php?eventid='.$eid.'">'.EB_USER_L16.'</a>)';
                }
            }
            else
            {
                $text .= EB_USER_L17;
            }
            $text .= '</td>';
            $text .= '</tr>';
        }
        $text .= '</table>';
    }

    /* Display list of events where the user is a moderator */
    $text .= '<br /><div class="spacer"><b>'.EB_USER_L20.'</b></div>';
    $text .= '<div>'.$uname.'&nbsp;'.EB_USER_L21.'</div>';
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
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L10;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L14;
        $text .= '</td>';
        $text .= '</tr>';

        for($i=0; $i<$num_rows; $i++)
        {
            $ename  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $egame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $egameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $eid  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $eowner  = mysql_result($result,$i, TBL_EVENTS.".Owner");
            $text .= '<tr>';
            $text .= '<td class="forumheader3">';
            $text .= '<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$eid.'">'.$ename.'</a><br />';
            $text .= '<img '.getGameIconResize($egameicon).'/> '.$egame;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            if($eowner == $req_user)
            {
                $text .= EB_USER_L15;
                if ($eowner == USERID)
                {
                    $text .= ' (<a href="'.e_PLUGIN.'ebattles/eventmanage.php?eventid='.$eid.'">'.EB_USER_L16.'</a>)';
                }
            }
            else
            {
                $text .= EB_USER_L17;
            }
            $text .= '</td>';
            $text .= '</tr>';
        }
        $text .= '</table>';
    }
    $text .= '</div>';

    /*
    --------------------- 
    Divisions
    ---------------------
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">'.EB_USER_L4.'</div>
    ';
    if((strcmp(USERID,$req_user) == 0)&&(check_class($pref['eb_teams_create_class'])))
    {
        $text .= '<form action="'.e_PLUGIN.'ebattles/clancreate.php" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="userid" value="'.$req_user.'"/>';
        $text .= '<input type="hidden" name="username" value="'.$uname.'"/>';
        $text .= '<input class="button" type="submit" name="createteam" value="'.EB_CLANS_L7.'"/>';
        $text .= '</div>';
        $text .= '</form><br />';
    }

    /* Display list of divisions where the user is a member */
    $text .= '<div class="spacer"><b>'.EB_USER_L22.'</b></div>';
    $text .= '<div>'.$uname.'&nbsp;'.EB_USER_L23.'</div>';
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
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L24;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L25;
        $text .= '</td>';
        $text .= '</tr>';
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
            $text .= '<tr>';
            $text .= '<td class="forumheader3">';
            $text .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$cid.'">'.$cname.'</a><br />';
            $text .= '<img '.getGameIconResize($dgameicon).'/> '.$dgame;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            if($cowner == $req_user)
            {
                $text .= EB_USER_L15;
                if ($cowner == USERID)
                {
                    $text .= ' (<a href="'.e_PLUGIN.'ebattles/clanmanage.php?clanid='.$cid.'">'.EB_USER_L16.'</a>)';
                }
            }
            else
            {
                $text .= EB_USER_L17;
            }
            $text .= '</td>';
            $text .= '</tr>';

        }
        $text .= '</table>';
    }

    /* Display list of teams where the user is te owner */
    $text .= '<br /><div class="spacer"><b>'.EB_USER_L26.'</b></div>';
    $text .= '<div>'.$uname.'&nbsp;'.EB_USER_L27.'</div>';
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
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L28;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L14;
        $text .= '</td>';
        $text .= '</tr>';
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
            $text .= '<tr>';
            $text .= '<td class="forumheader3">';
            $text .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$cid.'">'.$cname.'</a><br />';
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            if($cowner == $req_user)
            {
                $text .= EB_USER_L15;
                if ($cowner == USERID)
                {
                    $text .= ' (<a href="'.e_PLUGIN.'ebattles/clanmanage.php?clanid='.$cid.'">'.EB_USER_L14.'</a>)';
                }
            }
            else
            {
                $text .= EB_USER_L17;
            }
            $text .= '</td>';
            $text .= '</tr>';

        }
        $text .= '</table>';
    }

    /* Display list of divisions where the user is the captain */
    $text .= '<br /><div class="spacer"><b>'.EB_USER_L29.'</b></div>';
    $text .= '<div>'.$uname.'&nbsp;'.EB_USER_L30.'</div>';
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
        $text .= '<table class="fborder" style="width:95%">';
        $text .= '<tr>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L24;
        $text .= '</td>';
        $text .= '<td class="forumheader3">';
        $text .= EB_USER_L14;
        $text .= '</td>';
        $text .= '</tr>';
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $cname  = mysql_result($result,$i, TBL_CLANS.".Name");
            $cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
            $dcaptain  = mysql_result($result,$i, TBL_DIVISIONS.".Captain");
            $dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
            $dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $text .= '<tr>';
            $text .= '<td class="forumheader3">';
            $text .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$cid.'">'.$cname.'</a><br />';
            $text .= '<img '.getGameIconResize($dgameicon).'/> '.$dgame;
            $text .= '</td>';
            $text .= '<td class="forumheader3">';
            if($cowner == $req_user)
            {
                $text .= EB_USER_L15;
                if ($cowner == USERID)
                {
                    $text .= ' (<a href="'.e_PLUGIN.'ebattles/clanmanage.php?clanid='.$cid.'">'.EB_USER_L16.'</a>)';
                }
            }
            else
            {
                $text .= EB_USER_L17;
            }
            $text .= '</td>';
            $text .= '</tr>';

        }
        $text .= '</table>';
    }
    $text .= '</div>';

    /*
    --------------------- 
    Matches
    ---------------------
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">'.EB_USER_L5.'</div>
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
        $text .= '<table class="table_left">';
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
            $mTime_local = $mTime + TIMEOFFSET;
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
                $players = '<tr>';
                $scores = '';

                $players .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="Match '.$mID.'">';
                $players .= '<img '.getActivityGameIconResize($mEventgameicon).'/>';
                $players .= '</a></td>';

                $players .= '<td>';
                $rank = 1;
                $matchteam = 0;
                for ($index = 0; $index < $numPlayers; $index++)
                {
                    $puid  = mysql_result($result2,$index , TBL_USERS.".user_id");
                    $pname  = mysql_result($result2,$index , TBL_USERS.".user_name");
                    $prank  = mysql_result($result2,$index , TBL_SCORES.".Player_Rank");
                    $pteam  = mysql_result($result2,$index , TBL_PLAYERS.".Team");
                    $pmatchteam  = mysql_result($result2,$index , TBL_SCORES.".Player_MatchTeam");
                    $pscore = mysql_result($result2,$index , TBL_SCORES.".Player_Score");
                    list($pclan, $pclantag) = getClanName($pteam);

                    if($index>0)
                    {
                        if ($pmatchteam == $matchteam)
                        {
                        $players .= ' &amp; ';
                        }
                        else
                        {
                            if ($prank == $rank)
                            {
                            $str = '&nbsp;'.EB_MATCH_L2.'&nbsp;';
                            }
                            else
                            {
                            $str = '&nbsp;'.EB_MATCH_L3.'&nbsp;';
                            }
                            $scores .= "-".$pscore;
                            $players .= $str;
                            $matchteam++;
                        }
                    }
                    else
                    {
                        $matchteam = $pmatchteam;
                        $scores .= $pscore;
                    }

                    $players .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a>';
                }

                //score here
                if ($mEventAllowScore == TRUE)
                {
                    $players .= '&nbsp;('.$scores.')&nbsp;';
                }

                $players .= '&nbsp;'.EB_MATCH_L12.'&nbsp;'.$mEventgame.' (<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$mEventID.'">'.$mEventName.'</a>)';

                $players .= ' <div class="smalltext">';
                $players .= EB_MATCH_L6.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$mReportedBy.'">'.$mReportedByNickName.'</a> ';
                if (($time-$mTime) < INT_MINUTE )
                {
                $players .= EB_MATCH_L7;
                }
                else if (($time-$mTime) < INT_DAY )
                {
                $players .= get_formatted_timediff($mTime, $time).'&nbsp;'.EB_MATCH_L8;
                }
                else
                {
                $players .= EB_MATCH_L9.'&nbsp;'.$date.'.';
                }
                $nbr_comments = getCommentTotal("ebmatches", $mID);
            $players .= ' <a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="'.EB_MATCH_L4.'&nbsp;'.$mID.'">'.$nbr_comments.'&nbsp;';
            $players .= ($nbr_comments > 1) ? EB_MATCH_L10 : EB_MATCH_L11;
                $players .= '</a>';
                $players .= '</div><br /></td></tr>';

                $text .= $players;
            }
        }
        $text .= '</table>';
    }
    $text .= '</div>';

    /*
    --------------------- 
    Awards
    ---------------------
    */
    $text .= '
    <div class="tab-page">
    <div class="tab">'.EB_USER_L6.'</div>
    ';

    /* Stats/Results */
    $q = "SELECT ".TBL_AWARDS.".*, "
    .TBL_EVENTS.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_GAMES.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_AWARDS.", "
    .TBL_PLAYERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES.", "
    .TBL_USERS
    ." WHERE (".TBL_USERS.".user_id = $req_user)"
    ." AND (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
    ." AND (".TBL_PLAYERS.".User = ".TBL_USERS.".user_id)"
    ." AND (".TBL_PLAYERS.".Event = ".TBL_EVENTS.".EventID)"
    ." AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY ".TBL_AWARDS.".timestamp DESC";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    $text .= '<br />';
    if ($num_rows>0)
    {
        $text .= '<table class="table_left">';
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $aID  = mysql_result($result,$i, TBL_AWARDS.".AwardID");
            $aUser  = mysql_result($result,$i, TBL_USERS.".user_id");
            $aUserNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $aEventID  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $aEventName  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $aEventgame = mysql_result($result,$i , TBL_GAMES.".Name");
            $aEventgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $aType  = mysql_result($result,$i, TBL_AWARDS.".Type");
            $aTime  = mysql_result($result,$i, TBL_AWARDS.".timestamp");
            $aTime_local = $aTime + TIMEOFFSET;
            $date = date("d M Y, h:i A",$aTime_local);

            switch ($aType) {
                case 'PlayerTookFirstPlace':
                $award = EB_AWARD_L2;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_gold_3.png").' alt="'.EB_AWARD_L3.'" title="'.EB_AWARD_L3.'"/> ';
                break;
                case 'PlayerInTopTen':
                $award = EB_AWARD_L4;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_bronze_3.png").' alt="'.EB_AWARD_L5.'" title="'.EB_AWARD_L5.'"/> ';
                break;
                case 'PlayerStreak5':
                $award = EB_AWARD_L6;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_bronze_3.png").' alt="'.EB_AWARD_L7.'" title="'.EB_AWARD_L7.'"/> ';
                break;
                case 'PlayerStreak10':
                $award = EB_AWARD_L8;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_silver_3.png").' alt="'.EB_AWARD_L9.'" title="'.EB_AWARD_L9.'"/> ';
                break;
                case 'PlayerStreak25':
                $award = EB_AWARD_L10;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_gold_3.png").' alt="'.EB_AWARD_L11.'" title="'.EB_AWARD_L11.'"/> ';
                break;
            }

            $award_string = '<tr><td style="vertical-align:top">'.$icon.'</td>';
            $award_string .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$aUser.'">'.$aUserNickName.'</a>';
            $award_string .= '&nbsp;'.$award;
            $award_string .= '&nbsp;'.EB_MATCH_L12.'&nbsp;'.$aEventgame.' (<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$aEventID.'">'.$aEventName.'</a>)';

            $award_string .= ' <div class="smalltext">';
            if (($time-$aTime) < INT_MINUTE )
            {
                $award_string .= EB_MATCH_L7;
            }
            else if (($time-$aTime) < INT_DAY )
            {
                $award_string .= get_formatted_timediff($aTime, $time).'&nbsp;'.EB_MATCH_L8;
            }
            else
            {
                $award_string .= $date;
            }
            $award_string .= '</div><br /></td></tr>';

            $text .= $award_string;
        }
        $text .= '</table><br />';
    }
    $text .= "<br />";
    $text .="</div>";

    $text .= '
    </div>

    <script type="text/javascript">
    //<![CDATA[
    setupAllTabs();
    //]]>
    </script>
    ';
}
$ns->tablerender(EB_USER_L1, $text);
require_once(FOOTERF);
exit;
?>

