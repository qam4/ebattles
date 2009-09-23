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

?>
