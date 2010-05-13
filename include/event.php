<?php
// functions for events.
//___________________________________________________________________
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/updatestats.php");
require_once(e_PLUGIN."ebattles/include/updateteamstats.php");

/***************************************************************************************
Functions
***************************************************************************************/
function resetPlayers($event_id)
{
    global $sql;
    $q2 = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
    $result2 = $sql->db_Query($q2);
    $eELOdefault = mysql_result($result2,0 , TBL_EVENTS.".ELO_default");
    $eTS_default_mu  = mysql_result($result2, 0, TBL_EVENTS.".TS_default_mu");
    $eTS_default_sigma  = mysql_result($result2, 0, TBL_EVENTS.".TS_default_sigma");

    $q2 = "SELECT ".TBL_PLAYERS.".*"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
    $result2 = $sql->db_Query($q2);
    $num_players = mysql_numrows($result2);
    if ($num_players!=0)
    {
        for($j=0; $j<$num_players; $j++)
        {
            $pID  = mysql_result($result2,$j, TBL_PLAYERS.".PlayerID");
            $q3 = "UPDATE ".TBL_PLAYERS
            ." SET ELORanking = '$eELOdefault',"
            ."     TS_mu = '".floatToSQL($eTS_default_mu)."',"
            ."     TS_sigma = '".floatToSQL($eTS_default_sigma)."',"
            ."     GamesPlayed = 0,"
            ."     Loss = 0,"
            ."     Win = 0,"
            ."     Draw = 0,"
            ."     Score = 0,"
            ."     ScoreAgainst = 0,"
            ."     Points = 0,"
            ."     Rank = 0,"
            ."     RankDelta = 0,"
            ."     OverallScore = 0,"
            ."     Streak = 0,"
            ."     Streak_Best = 0,"
            ."     Streak_Worst = 0"
            ." WHERE (PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);

            deleteAwards($pID);
        }
    }
}
function resetTeams($event_id)
{
    global $sql;
    $q2 = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
    $result2 = $sql->db_Query($q2);
    $eELOdefault = mysql_result($result2,0 , TBL_EVENTS.".ELO_default");
    $eTS_default_mu  = mysql_result($result2, 0, TBL_EVENTS.".TS_default_mu");
    $eTS_default_sigma  = mysql_result($result2, 0, TBL_EVENTS.".TS_default_sigma");

    $q2 = "SELECT ".TBL_TEAMS.".*"
    ." FROM ".TBL_TEAMS
    ." WHERE (".TBL_TEAMS.".Event = '$event_id')";
    $result2 = $sql->db_Query($q2);
    $num_teams = mysql_numrows($result2);
    if ($num_teams!=0)
    {
        for($j=0; $j<$num_teams; $j++)
        {
            $tID  = mysql_result($result2,$j, TBL_TEAMS.".TeamID");
            $q3 = "UPDATE ".TBL_TEAMS
            ." SET ELORanking = '$eELOdefault',"
            ."     TS_mu = '".floatToSQL($eTS_default_mu)."',"
            ."     TS_sigma = '".floatToSQL($eTS_default_sigma)."',"
            ."     GamesPlayed = 0,"
            ."     Loss = 0,"
            ."     Win = 0,"
            ."     Draw = 0,"
            ."     Score = 0,"
            ."     ScoreAgainst = 0,"
            ."     Points = 0,"
            ."     Streak = 0,"
            ."     Streak_Best = 0,"
            ."     Streak_Worst = 0"
            ." WHERE (TeamID = '$tID')";
            $result3 = $sql->db_Query($q3);
        }
    }
}
function deleteMatches($event_id)
{
    global $sql;
    $q2 = "SELECT ".TBL_MATCHS.".*"
    ." FROM ".TBL_MATCHS
    ." WHERE (".TBL_MATCHS.".Event = '$event_id')";
    $result2 = $sql->db_Query($q2);
    $num_matches = mysql_numrows($result2);
    if ($num_matches!=0)
    {
        for($j=0; $j<$num_matches; $j++)
        {
            $mID  = mysql_result($result2,$j, TBL_MATCHS.".MatchID");
            $q3 = "DELETE FROM ".TBL_SCORES
            ." WHERE (".TBL_SCORES.".MatchID = '$mID')";
            $result3 = $sql->db_Query($q3);
            $q3 = "DELETE FROM ".TBL_MATCHS
            ." WHERE (".TBL_MATCHS.".MatchID = '$mID')";
            $result3 = $sql->db_Query($q3);
        }
    }
}
function deletePlayerMatches($player_id)
{
    global $sql;

    $q = "SELECT ".TBL_MATCHS.".*, "
    .TBL_SCORES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_SCORES.".Player = '$player_id')";
    $result = $sql->db_Query($q);
    $num_matches = mysql_numrows($result);
    echo "<br>player_id $player_id";
    echo "<br>num_matches $num_matches";
    if ($num_matches!=0)
    {
        for($j=0; $j<$num_matches; $j++)
        {
            set_time_limit(10);
            $mID  = mysql_result($result,$j, TBL_MATCHS.".MatchID");
            deleteMatchScores($mID);
        }
    }
}
function deletePlayers($event_id)
{
    global $sql;
    $q2 = "SELECT ".TBL_PLAYERS.".*"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
    $result2 = $sql->db_Query($q2);
    $num_players = mysql_numrows($result2);
    if ($num_players!=0)
    {
        for($j=0; $j<$num_players; $j++)
        {
            $pID  = mysql_result($result2,$j, TBL_PLAYERS.".PlayerID");
            deleteAwards($pID);
            deletePlayer($pID);
        }
    }
}
function deletePlayer($pID)
{
    global $sql;
    $q = "DELETE FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".PlayerID = '$pID')";
    $result = $sql->db_Query($q);
}

function deleteTeams($event_id)
{
    global $sql;
    $q3 = "DELETE FROM ".TBL_TEAMS
    ." WHERE (".TBL_TEAMS.".Event = '$event_id')";
    $result3 = $sql->db_Query($q3);
}
function deleteMods($event_id)
{
    global $sql;
    $q3 = "DELETE FROM ".TBL_EVENTMODS
    ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')";
    $result3 = $sql->db_Query($q3);
}
function deleteStatsCats($event_id)
{
    global $sql;
    $q3 = "DELETE FROM ".TBL_STATSCATEGORIES
    ." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')";
    $result3 = $sql->db_Query($q3);
}
function deleteAwards($player_id)
{
    global $sql;
    $q3 = "DELETE FROM ".TBL_AWARDS
    ." WHERE (".TBL_AWARDS.".Player = '$player_id')";
    $result3 = $sql->db_Query($q3);
}
function deleteEvent($event_id)
{
    global $sql;
    deleteMatches($event_id);
    deletePlayers($event_id);
    deleteTeams($event_id);
    deleteMods($event_id);
    deleteStatsCats($event_id);
    $q3 = "DELETE FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
    $result3 = $sql->db_Query($q3);
}
/**
* eventScoresUpdate - Re-calculate the scores and players of an event
*/
function eventScoresUpdate($event_id, $current_match)
{
    global $sql;
    global $time;

    //echo "dbg: current_match $current_match<br>";

    $numMatchsPerUpdate = 10;

    $q = "SELECT ".TBL_MATCHS.".*"
    ." FROM ".TBL_MATCHS
    ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
    ." AND (".TBL_MATCHS.".Status = 'active')"
    ." ORDER BY TimeReported";
    $result = $sql->db_Query($q);
    $num_matches = mysql_numrows($result);
    //echo "dbg: num_matches $num_matches<br>";
    
    if ($current_match > $num_matches)
    {
        updateStats($event_id, $time, TRUE);
        echo "Done.";
        echo '<META HTTP-EQUIV="Refresh" Content="0; URL=eventmanage.php?eventid='.$event_id.'">';
    }
    else
    {
        $next_match = 1;
        if ($current_match == 0)
        {
            /* Event Info */
            $q = "SELECT ".TBL_EVENTS.".*"
            ." FROM ".TBL_EVENTS
            ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
            $result = $sql->db_Query($q);
            $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");

            // Reset players stats
            resetPlayers($event_id);
            updateStats($event_id, $estart, FALSE);
            if ($etype == "Team Ladder") updateTeamStats($event_id, $estart, FALSE);
        }
        else
        {
            if (ob_get_level() == 0) {
                ob_start();
            }
            // Output a 'waiting message'
            echo str_pad('Please wait while this task completes... ',4096)."<br />\n";

            // Update matchs scores
            for($j=$current_match - 1; $j < min($current_match + $numMatchsPerUpdate - 1, $num_matches); $j++)
            {
                set_time_limit(10);

                $next_match = $j + 2;
                $mID  = mysql_result($result,$j, TBL_MATCHS.".MatchID");
                $time_reported  = mysql_result($result,$j, TBL_MATCHS.".TimeReported");

                match_scores_update($mID);
                match_players_update($mID);
                updateStats($event_id, $time_reported, FALSE);
                if ($etype == "Team Ladder") updateTeamStats($event_id, $time_reported, FALSE);


                //echo 'match '.$j.': '.$mID.'<br>';
                //echo '<div class="percents">match '.$j.': '.$mID.'</div>';
                echo '<div class="percents">' . number_format(100*($j+1)/$num_matches, 0, '.', '') . '%&nbsp;complete</div>';
                echo str_pad('',4096)."\n";
                ob_flush();
                flush();
            }
        }

        echo '<form name="updateform" action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
        echo '<input type="hidden" name="match" value="'.$next_match.'"/>';
        echo '<input type="hidden" name="eventupdatescores" value="1"/>';
        echo '</form>';
        echo '<script language="javascript">document.updateform.submit()</script>';

        ob_end_flush();
    }
    exit;
}
/**
* eventAddPlayer - add a user to an event
*/
function eventAddPlayer($event_id, $user, $team = 0, $notify)
{
    global $sql;

    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
    $result = $sql->db_Query($q);
    $eELOdefault = mysql_result($result, 0, TBL_EVENTS.".ELO_default");
    $eTS_default_mu = mysql_result($result, 0, TBL_EVENTS.".TS_default_mu");
    $eTS_default_sigma = mysql_result($result, 0, TBL_EVENTS.".TS_default_sigma");
    $ename = mysql_result($result, 0, TBL_EVENTS.".Name");

    $q = "SELECT ".TBL_USERS.".*"
    ." FROM ".TBL_USERS
    ." WHERE (".TBL_USERS.".user_id = '$user')";
    $result = $sql->db_Query($q);
    $username = mysql_result($result, 0, TBL_USERS.".user_name");

    // Is the user already signed up for the team?
    $q = "SELECT ".TBL_PLAYERS.".*"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ."   AND (".TBL_PLAYERS.".Team = '$team')"
    ."   AND (".TBL_PLAYERS.".User = '$user')";
    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if ($num_rows==0)
    {
        $q = " INSERT INTO ".TBL_PLAYERS."(Event,User,Team,ELORanking,TS_mu,TS_sigma)
        VALUES ($event_id,$user,$team,$eELOdefault,$eTS_default_mu,$eTS_default_sigma)";
        $sql->db_Query($q);
        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $sql->db_Query($q);

        if ($notify)
        {
            $sendto = $user;
            $subject = "$ename";
            $message = 
            "Hello $username,
            
            You are invited to participate in the \"$ename\" ladder.
            Please click the following link to view the ladder details:
            <a href='".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id'>$ename</a>.
            
            Cordially,
            ".USERNAME." (ladder admin)";
            sendNotification($sendto, $subject, $message, $fromid=0);
        }
    }
}


/**
* eventAddDivision - add a division to an event
*/
function eventAddDivision($event_id, $div_id, $notify)
{
    global $sql;

    // Is the division signed up
    $q = "SELECT ".TBL_TEAMS.".*"
    ." FROM ".TBL_TEAMS
    ." WHERE (".TBL_TEAMS.".Event = '$event_id')"
    ." AND (".TBL_TEAMS.".Division = '$div_id')";
    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if($num_rows == 0)
    {
        $q = "INSERT INTO ".TBL_TEAMS."(Event,Division)
        VALUES ($event_id,$div_id)";
        $sql->db_Query($q);
        $team_id =  mysql_insert_id();

        // All members of this division will automatically be signed up to this event
        $q_2 = "SELECT ".TBL_DIVISIONS.".*, "
        .TBL_MEMBERS.".*, "
        .TBL_USERS.".*"
        ." FROM ".TBL_DIVISIONS.", "
        .TBL_USERS.", "
        .TBL_MEMBERS
        ." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
        ." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
        ." AND (".TBL_USERS.".user_id = ".TBL_MEMBERS.".User)";
        $result_2 = $sql->db_Query($q_2);
        $num_rows_2 = mysql_numrows($result_2);
        if($num_rows_2 > 0)
        {
            for($j=0; $j<$num_rows_2; $j++)
            {
                $mid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
                eventAddPlayer ($event_id, $mid, $team_id, $notify);
            }
            $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
            $result = $sql->db_Query($q4);
        }
    }
}

function eventType($type)
{
	switch($type)
	{
		case "One Player Ladder":
		return EB_EVENTS_L22;
		break;
		case "Team Ladder":
		return EB_EVENTS_L23;
		break;
		default:
		return $type;
	}
}
?>
