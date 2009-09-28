<?php
/**
* ClanInfo_process.php
*
*/
if(isset($_POST['joindivision']))
{
    $div_id = $_POST['division'];

    $q = "SELECT ".TBL_CLANS.".*, "
    .TBL_DIVISIONS.".*"
    ." FROM ".TBL_CLANS.", "
    .TBL_DIVISIONS
    ." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
    ." AND (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)";

    $result = $sql->db_Query($q);
    $clan_password  = mysql_result($result, 0, TBL_CLANS.".password");
    if ($_POST['joindivisionPassword'] == $clan_password)
    {
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
    }
    header("Location: claninfo.php?clanid=$clan_id");
}
if(isset($_POST['quitdivision']))
{
    $div_id = $_POST['division'];

    // Check that the member has made no games with this division
    $q_MemberScores = "SELECT ".TBL_MEMBERS.".*, "
    .TBL_TEAMS.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_SCORES.".*"
    ." FROM ".TBL_MEMBERS.", "
    .TBL_TEAMS.", "
    .TBL_PLAYERS.", "
    .TBL_SCORES
    ." WHERE (".TBL_MEMBERS.".User = ".USERID.")"
    ." AND (".TBL_MEMBERS.".Division = '$div_id')"
    ." AND (".TBL_TEAMS.".Division = '$div_id')"
    ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
    ." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
    $result_MemberScores = $sql->db_Query($q_MemberScores);
    $numMemberScores = mysql_numrows($result_MemberScores);
    if ($numMemberScores == 0)
    {
        deleteMemberPlayers($div_id);
        deleteMember($div_id);
    }
    header("Location: claninfo.php?clanid=$clan_id");
}

//----------------------------------------------------------
function deleteMemberPlayers($div_id)
{
    global $sql;

    $q_MemberPlayers = "SELECT ".TBL_MEMBERS.".*, "
    .TBL_TEAMS.".*, "
    .TBL_PLAYERS.".*"
    ." FROM ".TBL_MEMBERS.", "
    .TBL_TEAMS.", "
    .TBL_PLAYERS
    ." WHERE (".TBL_MEMBERS.".User = ".USERID.")"
    ." AND (".TBL_MEMBERS.".Division = '$div_id')"
    ." AND (".TBL_TEAMS.".Division = '$div_id')"
    ." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)";
    $result_MemberPlayers = $sql->db_Query($q_MemberPlayers);
    $numMemberPlayers = mysql_numrows($result_MemberPlayers);
    if ($numMemberPlayers != 0)
    {
        for($j=0; $j<$numMemberPlayers; $j++)
        {
            $pID  = mysql_result($result_MemberPlayers,$j, TBL_PLAYERS.".PlayerID");
            $q = "DELETE FROM ".TBL_PLAYERS
            ." WHERE (".TBL_PLAYERS.".PlayerID = '$pID')";
            $result = $sql->db_Query($q);
        }
    }
}
function deleteMember($div_id)
{
    global $sql;

    $q = " DELETE FROM ".TBL_MEMBERS
    ." WHERE (Division = '$div_id')"
    ."   AND (User = ".USERID.")";
    $sql->db_Query($q);
}





?>
