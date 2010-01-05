<?php
/**
* EventInfo.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/show_array.php");
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/clan.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$pages = new Paginator;

$text = '
<script type="text/javascript" src="./js/tabpane.js"></script>
';

if (!isset($_GET['orderby'])) $_GET['orderby'] = 1;
$orderby=$_GET['orderby'];

$sort = "ASC";
if(isset($_GET["sort"]) && !empty($_GET["sort"]))
{
    $sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
    $sort_type = ($_GET["sort"]=="ASC") ? SORT_DESC : SORT_ASC;
}

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    $self = $_SERVER['PHP_SELF'];
    $file = 'cache/sql_cache_event_'.$event_id.'.txt';
    $file_team = 'cache/sql_cache_event_team_'.$event_id.'.txt';

    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
    $result = $sql->db_Query($q);
    $eELOdefault = mysql_result($result, 0, TBL_EVENTS.".ELO_default");
    $eTS_default_mu  = mysql_result($result, 0, TBL_EVENTS.".TS_default_mu");
    $eTS_default_sigma  = mysql_result($result, 0, TBL_EVENTS.".TS_default_sigma");
    $epassword = mysql_result($result, 0, TBL_EVENTS.".Password");

    require_once(e_PLUGIN."ebattles/eventinfo_process.php");

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
    $egame = mysql_result($result,0 , TBL_GAMES.".Name");
    $egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
    $egameicon = mysql_result($result,0 , TBL_GAMES.".Icon");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
    $eallowdraw = mysql_result($result,0 , TBL_EVENTS.".AllowDraw");
    $eallowscore = mysql_result($result,0 , TBL_EVENTS.".AllowScore");
    $eowner = mysql_result($result,0 , TBL_USERS.".user_id");
    $eownername = mysql_result($result,0 , TBL_USERS.".user_name");
    $emingames = mysql_result($result,0 , TBL_EVENTS.".nbr_games_to_rank");
    $eminteamgames = mysql_result($result,0 , TBL_EVENTS.".nbr_team_games_to_rank");
    $erules = mysql_result($result,0 , TBL_EVENTS.".Rules");
    $edescription = mysql_result($result,0 , TBL_EVENTS.".Description");
    $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
    $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
    $enextupdate = mysql_result($result,0 , TBL_EVENTS.".NextUpdate_timestamp");
    $eischanged = mysql_result($result,0 , TBL_EVENTS.".IsChanged");
    $ehide_ratings_column = mysql_result($result,0 , TBL_EVENTS.".hide_ratings_column");
    $ematch_report_userclass = mysql_result($result,0 , TBL_EVENTS.".match_report_userclass");
    $equick_loss_report = mysql_result($result,0 , TBL_EVENTS.".quick_loss_report");

    if ($pref['eb_events_update_delay_enable'] == 1)
    {
        $eneedupdate = 0;
    }
    else
    {
        // Force always update
        $eneedupdate = 1;
    }

    if (  (($time > $enextupdate) && ($eischanged == 1))
        ||(file_exists($file) == FALSE)
        ||((file_exists($file_team) == FALSE) && (($etype == "Team Ladder")))
    )
    {
        $eneedupdate = 1;
    }

    if($estart!=0)
    {
        $estart_local = $estart + TIMEOFFSET;
        $date_start = date("d M Y, h:i A",$estart_local);
    }
    else
    {
        $date_start = "-";
    }
    if($eend!=0)
    {
        $eend_local = $eend + TIMEOFFSET;
        $date_end = date("d M Y, h:i A",$eend_local);
    }
    else
    {
        $date_end = "-";
    }

    $time_comment = '';
    if (  ($estart != 0)
        &&($time <= $estart)
    )
    {
        $time_comment = EB_EVENT_L2.'&nbsp;'.get_formatted_timediff($time, $estart);
    }
    else if (  ($eend != 0)
        &&($time <= $eend)
    )
    {
        $time_comment = EB_EVENT_L3.'&nbsp;'.get_formatted_timediff($time, $eend);
    }
    else if (  ($eend != 0)
        &&($time > $eend)
    )
    {
        $time_comment = EB_EVENT_L4;
    }

    /* Nbr players */
    $q = "SELECT COUNT(*) as NbrPlayers"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrplayers = $row['NbrPlayers'];

    $q = "SELECT COUNT(*) as NbrPlayers"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ."   AND (".TBL_PLAYERS.".Banned != 1)";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrplayersNotBanned = $row['NbrPlayers'];

    /* Update Stats */
    if ($eneedupdate == 1)
    {
        $new_nextupdate = $time + 60*$pref['eb_events_update_delay'];
        $q = "UPDATE ".TBL_EVENTS." SET NextUpdate_timestamp = $new_nextupdate WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);
        $enextupdate = $new_nextupdate;

        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 0 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);
        $eischanged = 0;

        updateStats($event_id, $time, TRUE);
    }

    if ($etype == "Team Ladder")
    {
        $text .= '<div class="tab-pane" id="tab-pane-1-team">';
    }
    else
    {
        $text .= '<div class="tab-pane" id="tab-pane-1">';
    }

    $text .= '<div class="tab-page">';
    $text .= '<div class="tab">'.EB_EVENT_L5.'</div>';
    $text .= $tp->toHTML($edescription, true);
    $text .= '</div>';

    /* Join/Quit Event */
    $text .= '<div class="tab-page">';
    $text .= '<div class="tab">'.EB_EVENT_L6.'</div>';
    $text .= '<table style="width:95%"><tbody>';
    if(check_class(e_UC_MEMBER))
    {
        // If logged in
        if(($eend == 0) || ($time < $eend))
        {
            // If event is not finished
            if ($etype == "Team Ladder")
            {
                // Find if user is captain of a division playing that game
                // if yes, propose to join this event
                $q = "SELECT ".TBL_DIVISIONS.".*, "
                .TBL_CLANS.".*, "
                .TBL_GAMES.".*, "
                .TBL_USERS.".*"
                ." FROM ".TBL_DIVISIONS.", "
                .TBL_CLANS.", "
                .TBL_GAMES.", "
                .TBL_USERS
                ." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
                ." AND (".TBL_GAMES.".GameID = '$egameid')"
                ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
                ." AND (".TBL_USERS.".user_id = ".USERID.")"
                ." AND (".TBL_DIVISIONS.".Captain = ".USERID.")";

                $result = $sql->db_Query($q);
                $num_rows = mysql_numrows($result);
                if($num_rows > 0)
                {
                    for($i=0;$i < $num_rows;$i++)
                    {
                        $div_name  = mysql_result($result,$i, TBL_CLANS.".Name");
                        $div_id    = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");

                        // Is the division signed up
                        $q_2 = "SELECT ".TBL_TEAMS.".*"
                        ." FROM ".TBL_TEAMS
                        ." WHERE (".TBL_TEAMS.".Event = '$event_id')"
                        ." AND (".TBL_TEAMS.".Division = '$div_id')";
                        $result_2 = $sql->db_Query($q_2);
                        $num_rows_2 = mysql_numrows($result_2);

                        $text .= '<tr>';
                        $text .= '<td>'.EB_EVENT_L7.'&nbsp;'.$div_name.'</td>';
                        if( $num_rows_2 == 0)
                        {

                            if ($epassword != "")
                            {
                                $text .= '<td>'.EB_EVENT_L8.'</td>';
                                $text .= '<td>
                                <form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
                                <div>
                                <input class="tbox" type="password" title="'.EB_EVENT_L9.'" name="joinEventPassword"/>
                                <input type="hidden" name="division" value="'.$div_id.'"/>
                                <input class="button" type="submit" name="teamjoinevent" value="'.EB_EVENT_L10.'"/>
                                </div>
                                ';
                                $text .= '</form>';
                                $text .= '</td>';
                            }
                            else
                            {
                                $text .= '<td>'.EB_EVENT_L11.'</td>';
                                $text .= '<td>
                                <form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
                                <div>
                                <input type="hidden" name="joinEventPassword" value=""/>
                                <input type="hidden" name="division" value="'.$div_id.'"/>
                                <input class="button" type="submit" name="teamjoinevent" value="'.EB_EVENT_L12.'"/>
                                </div>
                                ';
                                $text .= '</form>';
                                $text .= '</td>';
                            }
                        }
                        else
                        {
                            // Team signed up.
                            $text .= '<td>'.EB_EVENT_L13.'</td>';
                        }
                        $text .= '</tr>';
                    }
                }
            }

            if ($etype == "Team Ladder")
            {
                // Is user a member of a division for that game?
                $q_2 = "SELECT ".TBL_CLANS.".*, "
                .TBL_MEMBERS.".*, "
                .TBL_DIVISIONS.".*, "
                .TBL_GAMES.".*, "
                .TBL_USERS.".*"
                ." FROM ".TBL_CLANS.", "
                .TBL_MEMBERS.", "
                .TBL_DIVISIONS.", "
                .TBL_GAMES.", "
                .TBL_USERS
                ." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
                ." AND (".TBL_GAMES.".GameID = '$egameid')"
                ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
                ." AND (".TBL_USERS.".user_id = ".USERID.")"
                ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
                ." AND (".TBL_MEMBERS.".User = ".USERID.")";

                $result_2 = $sql->db_Query($q_2);
                $num_rows_2 = mysql_numrows($result_2);
                if(!$result_2 || ( $num_rows_2 == 0))
                {
                    $text .= '<tr><td>'.EB_EVENT_L14.'</td>';
                    $text .= '<td></td></tr>';
                }
                else
                {
                    for($i=0;$i < $num_rows_2;$i++)
                    {
                        $clan_name  = mysql_result($result_2,$i , TBL_CLANS.".Name");
                        $div_id  = mysql_result($result_2,$i , TBL_DIVISIONS.".DivisionID");
                        $q_3 = "SELECT ".TBL_DIVISIONS.".*, "
                        .TBL_USERS.".*"
                        ." FROM ".TBL_DIVISIONS.", "
                        .TBL_USERS
                        ." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
                        ." AND (".TBL_USERS.".user_id = ".TBL_DIVISIONS.".Captain)";
                        $result_3 = $sql->db_Query($q_3);
                        if($result_3)
                        {
                            $captain_name  = mysql_result($result_3,0, TBL_USERS.".user_name");
                            $captain_id  = mysql_result($result_3,0, TBL_USERS.".user_id");
                        }

                        $q_3 = "SELECT ".TBL_CLANS.".*, "
                        .TBL_TEAMS.".*, "
                        .TBL_DIVISIONS.".* "
                        ." FROM ".TBL_CLANS.", "
                        .TBL_TEAMS.", "
                        .TBL_DIVISIONS
                        ." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
                        ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
                        ." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
                        ." AND (".TBL_TEAMS.".Event = '$event_id')";
                        $result_3 = $sql->db_Query($q_3);
                        if(!$result_3 || (mysql_numrows($result_3) == 0))
                        {
                            if ($captain_id != USERID)
                            {
                                $text .= '<tr><td>'.EB_EVENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_EVENT_L16.'</td>';
                                $text .= '<td>'.EB_EVENT_L17.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$captain_id.'">'.$captain_name.'</a>.</td></tr>';
                            }
                        }
                        else
                        {
                            $team_id  = mysql_result($result_3,0 , TBL_TEAMS.".TeamID");
                            $text .= '<tr><td>'.EB_EVENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_EVENT_L18.'</td>';

                            // Is the user already signed up with that team?
                            $q = "SELECT ".TBL_PLAYERS.".*"
                            ." FROM ".TBL_PLAYERS
                            ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
                            ."   AND (".TBL_PLAYERS.".User = ".USERID.")"
                            ."   AND (".TBL_PLAYERS.".Team = '$team_id')";
                            $result = $sql->db_Query($q);
                            if(!$result || (mysql_numrows($result) == 0))
                            {
                                $text .= '<td>
                                <form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
                                <div>
                                <input type="hidden" name="team" value="'.$team_id.'"/>
                                <input class="button" type="submit" name="jointeamevent" value="'.EB_EVENT_L19.'"/>
                                </div>
                                </form></td>
                                ';
                            }
                            else
                            {
                                $user_pid  = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
                                $user_banned  = mysql_result($result,0 , TBL_PLAYERS.".Banned");

                                if ($user_banned)
                                {
                                    $text .= '<td>'.EB_EVENT_L20.'<br />
                                    '.EB_EVENT_L21.'</td>';
                                }
                                else
                                {
                                    // Player signed up
                                    $text .= '<td>'.EB_EVENT_L22.'</td>';

                                    // Player can quit an event if he has not played yet
                                    $q = "SELECT ".TBL_PLAYERS.".*"
                                    ." FROM ".TBL_PLAYERS.", "
                                    .TBL_SCORES
                                    ." WHERE (".TBL_PLAYERS.".PlayerID = '$user_pid')"
                                    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
                                    $result = $sql->db_Query($q);
                                    $nbrscores = mysql_numrows($result);
                                    if (($nbrscores == 0)&&($user_banned!=1))
                                    {
                                        $text .= '<td>
                                        <form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
                                        <div>
                                        <input type="hidden" name="player" value="'.$user_pid.'"/>
                                        <input class="button" type="submit" name="quitevent" value="'.EB_EVENT_L23.'" onclick="return confirm(\''.EB_EVENT_L24.'\');"/>
                                        </div>
                                        </form></td>
                                        ';
                                    }
                                    else
                                    {
                                        $text .= '<td></td>';
                                    }
                                }
                            }
                            $text .= '</tr>';
                        }
                    }
                }
            }
            else
            {
                // Is the user already signed up?
                $q = "SELECT ".TBL_PLAYERS.".*"
                ." FROM ".TBL_PLAYERS
                ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
                ."   AND (".TBL_PLAYERS.".User = ".USERID.")";
                $result = $sql->db_Query($q);
                if(!$result || (mysql_numrows($result) < 1))
                {
                    if ($epassword != "")
                    {
                        $text .= '<tr><td>'.EB_EVENT_L25.'</td>';
                        $text .= '<td>'.EB_EVENT_L26.'</td>';
                        $text .= '<td>';
                        $text .= '
                        <form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
                        <div>
                        <input class="tbox" type="password" title="'.EB_EVENT_L27.'" name="joinEventPassword"/>
                        <input class="button" type="submit" name="joinevent" value="'.EB_EVENT_L19.'"/>
                        </div>
                        </form></td></tr>
                        ';
                    }
                    else
                    {
                        $text .= '<tr><td>'.EB_EVENT_L28.'</td>';
                        $text .= '<td>
                        <form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
                        <div>
                        <input type="hidden" name="joinEventPassword" value=""/>
                        <input class="button" type="submit" name="joinevent" value="'.EB_EVENT_L19.'"/>
                        </div>
                        </form></td></tr>
                        ';
                    }
                }
                else
                {
                    $user_pid  = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
                    $user_banned  = mysql_result($result,0 , TBL_PLAYERS.".Banned");

                    if ($user_banned)
                    {
                        $text .= '<tr><td>'.EB_EVENT_L29.'<br />
                        '.EB_EVENT_L30.'</td><td></td></tr>';
                    }
                    else
                    {
                        $text .= '<tr><td>'.EB_EVENT_L31.'</td>';

                        // Player can quit an event if he has not played yet
                        $q = "SELECT ".TBL_PLAYERS.".*"
                        ." FROM ".TBL_PLAYERS.", "
                        .TBL_SCORES
                        ." WHERE (".TBL_PLAYERS.".PlayerID = '$user_pid')"
                        ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
                        $result = $sql->db_Query($q);
                        $nbrscores = mysql_numrows($result);
                        if ($nbrscores == 0)
                        {
                            $text .= '<td>
                            <form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
                            <div>
                            <input type="hidden" name="player" value="'.$user_pid.'"/>
                            <input class="button" type="submit" name="quitevent" value="'.EB_EVENT_L32.'" onclick="return confirm(\''.EB_EVENT_L33.'\');"/>
                            </div>
                            </form></td></tr>
                            ';
                        }
                        else
                        {
                            $text .= '<td></td></tr>';
                        }
                    }
                }
            }
        }
    }
    else
    {
        $text .= '<tr><td>'.EB_EVENT_L34.'</td>';
        $text .= '<td></td></tr>';
    }
    $text .= '</tbody></table>';
    $text .= '</div>';

    $text .= '<div class="tab-page">';
    $text .= '<div class="tab">'.EB_EVENT_L35.'</div>';

    $text .= '<table class="fborder" style="width:95%"><tbody>';

    $text .= '<tr>';
    $text .= '<td class="forumheader3">'.EB_EVENT_L36.'</td>';
    $text .= '<td class="forumheader3"><b>'.$ename.'</b></td>';
    $text .= '</tr>';

    $text .= '<tr>';
    $text .= '<td class="forumheader3">'.EB_EVENT_L37.'</td>';
    $text .= '<td class="forumheader3">'.eventType($etype).'</td>';
    $text .= '</tr>';

    $text .= '<tr>';
    $text .= '<td class="forumheader3">'.EB_EVENT_L38.'</td>';
    $text .= '<td class="forumheader3"><img '.getGameIconResize($egameicon).'/> '.$egame.'</td>';
    $text .= '</tr>';

    $text .= '<tr>';
    $text .= '<td class="forumheader3">'.EB_EVENT_L39.'</td>';
    $text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$eowner.'">'.$eownername.'</a>';
    $can_manage = 0;
    if (check_class($pref['eb_mod_class'])) $can_manage = 1;
    if (USERID==$eowner) $can_manage = 1;
    if ($can_manage == 1)
    $text .= '<br /><a href="'.e_PLUGIN.'ebattles/eventmanage.php?eventid='.$event_id.'">'.EB_EVENT_L40.'</a>';
    $text .= '</td></tr>';

    $text .= '<tr>';
    $q = "SELECT ".TBL_EVENTMODS.".*, "
    .TBL_USERS.".*"
    ." FROM ".TBL_EVENTMODS.", "
    .TBL_USERS
    ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
    ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTMODS.".User)";
    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    $text .= '<td class="forumheader3">'.EB_EVENT_L41.'</td>';
    $text .= '<td class="forumheader3">';
    if ($num_rows>0)
    {
        $text .= '<ul>';
    for($i=0; $i<$num_rows; $i++){
        $modid  = mysql_result($result,$i, TBL_USERS.".user_id");
        $modname  = mysql_result($result,$i, TBL_USERS.".user_name");
        $text .= '<li><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$modid.'">'.$modname.'</a></li>';
    }
    $text .= '</ul>';
}
$text .= '</td></tr>';

$text .= '<tr><td class="forumheader3">'.EB_EVENT_L42.'</td><td class="forumheader3">'.$date_start.'</td></tr>';
$text .= '<tr><td class="forumheader3">'.EB_EVENT_L43.'</td><td class="forumheader3">'.$date_end.'</td></tr>';
$text .= '<tr><td class="forumheader3"></td><td class="forumheader3">'.$time_comment.'</td></tr>';
$text .= '<tr><td class="forumheader3">'.EB_EVENT_L44.'</td><td class="forumheader3">'.$tp->toHTML($erules, true).'</td></tr>';
$text .= '</tbody></table>';
$text .= '</div>';

$enextupdate_local = $enextupdate + TIMEOFFSET;
$date_nextupdate = date("d M Y, h:i A",$enextupdate_local);

if ($etype == "Team Ladder")
{
    $text .= '<div class="tab-page">';
    $text .= '<div class="tab">'.EB_EVENT_L45.'</div>';

    /* Update Stats */
    if ($eneedupdate == 1)
    {
        updateTeamStats($event_id, $time, TRUE);
    }

    if (($time < $enextupdate) && ($eischanged == 1))
    {
        $text .= EB_EVENT_L46.'&nbsp;'.$date_nextupdate.'<br />';
    }
    /* Nbr Teams */
    $q = "SELECT COUNT(*) as NbrTeams"
    ." FROM ".TBL_TEAMS
    ." WHERE (Event = '$event_id')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrteams = $row['NbrTeams'];
    $text .= '<div class="spacer">';
    $text .= '<p>';
    $text .= $nbrteams.' teams<br />';
    $text .= EB_EVENT_L47.'&nbsp;'.$eminteamgames.'&nbsp;'.EB_EVENT_L48.'<br /><br />';
    $text .= '</p>';

    $stats = unserialize(implode('',file($file_team)));
    // debug print array
    $num_columns = count($stats[0]) - 1;
    $nbr_rows = count($stats);
    $text .= html_show_table($stats, $nbr_rows, $num_columns);

    $text .= '</div>';
    $text .= '</div>';
}
// Players standings stats
$stats = unserialize(implode('',file($file)));
$num_columns = count($stats[0]) - 1;
//print_r($stats);

// Sorting the stats table
$header = $stats[0];

$new_header = array();
$column = 0;
foreach ($header as $header_cell)
{
    //fm echo "column $column: $header_cell<br>";
    $pieces = explode("<br />", $header_cell);

    $new_header[] = '<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'&amp;orderby='.$column.'&amp;sort='.$sort.'">'.$pieces[0].'</a>'.$pieces[1];
    $column++;
}
$header = array($new_header);
$header[0][0] = "header";

array_splice($stats,0,1);
multi2dSortAsc($stats, $orderby, $sort_type);
$stats = array_merge($header, $stats);

$text .= '<div class="tab-page">';
$text .= '<div class="tab">'.EB_EVENT_L49.'</div>';

if (($time < $enextupdate) && ($eischanged == 1))
{
    $text .= EB_EVENT_L50.'&nbsp;'.$date_nextupdate.'<br />';
}

/* set pagination variables */
$totalItems = $nbrplayers;
$pages->items_total = $totalItems;
$pages->mid_range = eb_PAGINATION_MIDRANGE;
$pages->paginate();

$text .= '<p>';
$text .= $nbrplayers.'&nbsp;'.EB_EVENT_L51.'<br />';
$text .= EB_EVENT_L52.'&nbsp;'.$emingames.'&nbsp;'.EB_EVENT_L53.'<br />';
$text .= '</p>';

/* My Position */
$q = "SELECT *"
." FROM ".TBL_PLAYERS
." WHERE (Event = '$event_id')"
."   AND (User = ".USERID.")";
$result = $sql->db_Query($q);
$can_report = 0;
$can_report_quickloss = 0;
$userclass = 0;
$pbanned=0;
if(mysql_numrows($result) == 1)
{
    $userclass |= eb_UC_EVENT_PLAYER;

    // Show link to my position
    $row = mysql_fetch_array($result);
    $prank = $row['Rank'];
    $pbanned = $row['Banned'];

    if ($prank==0)
    $prank_txt = EB_EVENT_L54;
    else
    $prank_txt = "#$prank";

    $search_user = array_searchRecursive( 'user='.USERID.'"', $stats, false);

    ($search_user) ? $link_page = ceil($search_user[0]/$pages->items_per_page) : $link_page = 1;

    $text .= '<p>';
    $text .= "<a href=\"$self?page=$link_page&amp;ipp=$pages->items_per_page$pages->querystring\">".EB_EVENT_L55." $prank_txt</a><br />";
    $text .= '</p>';
    // Is the event started, and not ended
    if (  ($eend == 0)
        ||(  ($eend >= $time)
            &&($estart <= $time)
        )
    )
    {
        $can_report = 1;
        $can_report_quickloss = 1;
    }
}

// Check if user can report
// Is the user admin?
if (check_class($pref['eb_mod_class']))
{
    $userclass |= eb_UC_EB_MODERATOR;
    $can_report = 1;
}
// Is the user event owner?
if (USERID==$eowner)
{
    $userclass |= eb_UC_EVENT_OWNER;
    $can_report = 1;
}
// Is the user a moderator?
$q_2 = "SELECT ".TBL_EVENTMODS.".*"
." FROM ".TBL_EVENTMODS
." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
."   AND (".TBL_EVENTMODS.".User = ".USERID.")";
$result_2 = $sql->db_Query($q_2);
$num_rows_2 = mysql_numrows($result_2);
if ($num_rows_2>0)
{
    $userclass |= eb_UC_EVENT_MODERATOR;
    $can_report = 1;
}

if (($nbrplayersNotBanned < 2)||($pbanned))
{
    $can_report = 0;
    $can_report_quickloss = 0;
}

// check if only 1 player with this userid
$q = "SELECT DISTINCT ".TBL_PLAYERS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_PLAYERS.", "
.TBL_USERS
." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
."   AND (".TBL_USERS.".user_id = ".USERID.")";
$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
if ($num_rows>1)
$can_report_quickloss = 0;

// Check if AllowScore is set
if ($eallowscore==TRUE)
$can_report_quickloss = 0;

if($userclass < $ematch_report_userclass) $can_report = 0;
if($equick_loss_report==FALSE) $can_report_quickloss = 0;

if(($can_report_quickloss != 0)||($can_report != 0))
{
    $text .= '<table>';
    $text .= '<tr>';
    if($can_report_quickloss != 0)
    {
        $text .= '<td>';
        $text .= '<form action="'.e_PLUGIN.'ebattles/quickreport.php?eventid='.$event_id.'" method="post">';
        $text .= '<div><input class="button" type="submit" name="quicklossreport" value="'.EB_EVENT_L56.'"/></div>';
        $text .= '</form>';
        $text .= '</td>';
    }
    if($can_report != 0)
    {
        $text .= '<td>';
        $text .= '<form action="'.e_PLUGIN.'ebattles/matchreport.php?eventid='.$event_id.'" method="post">';
        $text .= '<div>';
        $text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
        $text .= '<input class="button" type="submit" name="matchreport" value="'.EB_EVENT_L57.'"/>';
        $text .= '</div>';
        $text .= '</form>';
        $text .= '</td>';
    }
    $text .= '</tr>';
    $text .= '</table>';
}
$text .= '<br />';

// Paginate
$text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
$text .= '<span style="float:right">';
// Go To Page
$text .= $pages->display_jump_menu();
$text .= '&nbsp;&nbsp;&nbsp;';
// Items per page
$text .= $pages->display_items_per_page();
$text .= '</span><br /><br />';

// Paginate the statistics array
$max_row = count($stats);
$stats_paginate = array($stats[0]);
$nbr_rows = 1;

for ($i = $pages->low + 1; $i <= $pages->high + 1; $i++)
{
    if ($i < $max_row)
    {
        $stats_paginate[] = $stats[$i];
        $nbr_rows ++;
    }
}
$text .= html_show_table($stats_paginate, $nbr_rows, $num_columns);

$text .= '</div>';

$text .= '
<div class="tab-page">
<div class="tab">'.EB_EVENT_L58.'</div>
';

$mEventType  = $etype;
$mEventAllowScore = $eallowscore;
$q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
." FROM ".TBL_MATCHS.", "
.TBL_SCORES
." WHERE (Event = '$event_id')"
." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
$result = $sql->db_Query($q);

$row = mysql_fetch_array($result);
$nbrmatches = $row['NbrMatches'];
$text .= '<p>';
$text .= $nbrmatches.'&nbsp;'.EB_EVENT_L59;
$text .= ' [<a href="'.e_PLUGIN.'ebattles/eventmatchs.php?eventid='.$event_id.'">'.EB_EVENT_L60.'</a>]';
$text .= '</p>';
$text .= '<br />';

$rowsPerPage = $pref['eb_default_items_per_page'];
/* Stats/Results */
$q = "SELECT DISTINCT ".TBL_MATCHS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_MATCHS.", "
.TBL_SCORES.", "
.TBL_USERS
." WHERE (".TBL_MATCHS.".Event = '$event_id')"
." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
." AND (".TBL_USERS.".user_id = ".TBL_MATCHS.".ReportedBy)"
." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
." LIMIT 0, $rowsPerPage";

$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);
if ($num_rows>0)
{
    /* Display table contents */
    $text .= '<table class="table_left">';
    for($i=0; $i<$num_rows; $i++)
    {
        $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
        $mReportedBy  = mysql_result($result,$i, TBL_USERS.".user_id");
        $mReportedByNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
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

            /*
            $players .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="Match '.$mID.'">';
            $players .= '<img '.getActivityGameIconResize($mEventgameicon).'/>';
            $players .= '</a></td>';
            */

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
                        $players .= ' & ';
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
                $players .= ' ('.$scores.') ';
            }

            $players .= ' (<a href="'.e_PLUGIN.'ebattles/matchinfo.php?matchid='.$mID.'" title="'.EB_MATCH_L4.'" '.$mID.'">'.EB_MATCH_L5.'</a>)';

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

$text .= '<div class="tab-page">';
$text .= '<div class="tab">'.EB_EVENT_L63.'</div>';

$rowsPerPage = $pref['eb_default_items_per_page'];
/* Stats/Results */
$q = "SELECT ".TBL_AWARDS.".*, "
.TBL_PLAYERS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_AWARDS.", "
.TBL_PLAYERS.", "
.TBL_USERS
." WHERE (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
." AND (".TBL_PLAYERS.".User = ".TBL_USERS.".user_id)"
." AND (".TBL_PLAYERS.".Event = '$event_id')"
." ORDER BY ".TBL_AWARDS.".timestamp DESC"
." LIMIT 0, $rowsPerPage";

$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);

if ($num_rows>0)
{
    $text .= '<table class="table_left">';
    /* Display table contents */
    for($i=0; $i<$num_rows; $i++)
    {
        $aID  = mysql_result($result,$i, TBL_AWARDS.".AwardID");
        $aUser  = mysql_result($result,$i, TBL_USERS.".user_id");
        $aUserNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
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
$text .= '<br />';
$text .= '</div>';

$text .= '
</div>

<script type="text/javascript">
//<![CDATA[
setupAllTabs();
//]]>
</script>
';
}

$ns->tablerender("$ename ($egame - ".eventType($etype).")", $text);
require_once(FOOTERF);
exit;

?>

