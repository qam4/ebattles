<?php
/**
* EventInfo_process.php
*
*/

if(isset($_POST['joinevent'])){
    if ($_POST['joinEventPassword'] == $epassword)
    {
        // Is the user already signed up?
        $q = "SELECT ".TBL_PLAYERS.".*"
        ." FROM ".TBL_PLAYERS
        ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
        ."   AND (".TBL_PLAYERS.".User = ".USERID.")";
        $result = $sql->db_Query($q);
        if(!$result || (mysql_numrows($result) < 1))
        {
            $q = " INSERT INTO ".TBL_PLAYERS."(Event,User,ELORanking,TS_mu,TS_sigma)
            VALUES ($event_id,".USERID.",$eELOdefault,$eTS_default_mu,$eTS_default_sigma)";
            $sql->db_Query($q);
            $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
            $result = $sql->db_Query($q4);
        }
    }
    header("Location: eventinfo.php?eventid=$event_id");
}
if(isset($_POST['quitevent'])){
    // Player can quit event if he has not played yet
    $q = "SELECT COUNT(*) as NbrScores"
    ." FROM ".TBL_SCORES.", "
    .TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrscores = $row['NbrScores'];
    if ($nbrscores == 0)
    {
        $q = " DELETE FROM ".TBL_PLAYERS
        ." WHERE (Event = '$event_id')"
        ."   AND (User = ".USERID.")";
        $sql->db_Query($q);
        $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q4);
    }
    header("Location: eventinfo.php?eventid=$event_id");
}
if(isset($_POST['quitteamevent'])){
    $team_id = $_POST['team'];

    // Player can quit event if he has not played yet
    $q = "SELECT COUNT(*) as NbrScores"
    ." FROM ".TBL_SCORES.", "
    .TBL_TEAMS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)"
    ." AND (".TBL_PLAYERS.".Team = '$team_id')";
    $result = $sql->db_Query($q);
    $row = mysql_fetch_array($result);
    $nbrscores = $row['NbrScores'];
    if ($nbrscores == 0)
    {
        $q = " DELETE FROM ".TBL_PLAYERS
        ." WHERE (Event = '$event_id')"
        ."   AND (Team = '$team_id')"
        ."   AND (User = ".USERID.")";
        $sql->db_Query($q);
        $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q4);
    }
    header("Location: eventinfo.php?eventid=$event_id");
}
if(isset($_POST['teamjoinevent'])){
    if ($_POST['joinEventPassword'] == $epassword)
    {
        $div_id = $_POST['division'];

        // Is the division signed up
        $q = "SELECT ".TBL_TEAMS.".*"
        ." FROM ".TBL_TEAMS
        ." WHERE (".TBL_TEAMS.".Event = '$event_id')"
        ." AND (".TBL_TEAMS.".Division = '$div_id')";
        $result = $sql->db_Query($q);
        $num_rows = mysql_numrows($result);
        if($num_rows == 0)
        {
            $q = " INSERT INTO ".TBL_TEAMS."(Event,Division)
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

                    // Verify there is no other player for that user/event/team
                    $q = "SELECT COUNT(*) as NbrPlayers"
                    ." FROM ".TBL_PLAYERS
                    ." WHERE (Event = '$event_id')"
                    ." AND (Team = '$team_id')"
                    ." AND (User = '$mid')";
                    $result = $sql->db_Query($q);
                    $row = mysql_fetch_array($result);
                    $nbrplayers = $row['NbrPlayers'];
                    if ($nbrplayers == 0)
                    {
                        $q = " INSERT INTO ".TBL_PLAYERS."(Event,User,Team,ELORanking,TS_mu,TS_sigma)
                        VALUES ($event_id,$mid,$team_id,$eELOdefault,$eTS_default_mu,$eTS_default_sigma)";
                        $sql->db_Query($q);
                    }
                }
                $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
                $result = $sql->db_Query($q4);
            }
        }
        header("Location: eventinfo.php?eventid=$event_id");
    }
}
if(isset($_POST['jointeamevent'])){
    $team_id = $_POST['team'];

    // Is the user already signed up for that team?
    $q = "SELECT ".TBL_PLAYERS.".*"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ."   AND (".TBL_PLAYERS.".Team = '$team_id')"
    ."   AND (".TBL_PLAYERS.".User = ".USERID.")";
    $result = $sql->db_Query($q);
    if(!$result || (mysql_numrows($result) < 1))
    {
        $q = " INSERT INTO ".TBL_PLAYERS."(Event,User,Team,ELORanking,TS_mu,TS_sigma)
        VALUES ($event_id,".USERID.",$team_id,$eELOdefault,$eTS_default_mu,$eTS_default_sigma)";
        $sql->db_Query($q);
        $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q4);
    }
    header("Location: eventinfo.php?eventid=$event_id");
}

?>
