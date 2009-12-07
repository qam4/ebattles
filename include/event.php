<?php
// functions for events.
//___________________________________________________________________
require_once e_PLUGIN.'ebattles/include/match.php';

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
            ."     TS_mu = '$eTS_default_mu',"
            ."     TS_sigma = '$eTS_default_sigma',"
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
            $tID  = mysql_result($result2,$j, TBL_TEAMS.".PlayerID");
            $q3 = "UPDATE ".TBL_TEAMS
            ." SET ELORanking = '$eELOdefault',"
            ."     TS_mu = '$eTS_default_mu',"
            ."     TS_sigma = '$eTS_default_sigma',"
            ."     GamesPlayed = 0,"
            ."     Loss = 0,"
            ."     Win = 0,"
            ."     Draws = 0,"
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
            $q3 = "DELETE FROM ".TBL_PLAYERS
            ." WHERE (".TBL_PLAYERS.".PlayerID = '$pID')";
            $result3 = $sql->db_Query($q3);
        }
    }
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
function eventScoresUpdate($event_id)
{
    global $sql;

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

    // Update each match scores
    $q = "SELECT ".TBL_MATCHS.".*"
    ." FROM ".TBL_MATCHS
    ." WHERE (".TBL_MATCHS.".Event = '$event_id')"
    ." ORDER BY TimeReported";
    $result = $sql->db_Query($q);
    $num_matches = mysql_numrows($result);
    if ($num_matches!=0)
    {
        for($j=0; $j<$num_matches; $j++)
        {
            set_time_limit(10);
            $mID  = mysql_result($result,$j, TBL_MATCHS.".MatchID");
            
            match_scores_update($mID, TRUE);
            //fm echo '<div class="percents">match ' . $j . '</div>';
            echo '<div class="percents">' . number_format(100*($j+1)/$num_matches, 0, '.', '') . '%&nbsp;complete</div>';
            echo str_pad('',4096)."\n";
            ob_flush();
        }
    }
    echo "<br>Done.";
    ob_end_flush();
}
?>
