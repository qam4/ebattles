<?php
/**
* ClanInfo_process.php
*
*/
require_once(e_PLUGIN.'ebattles/include/clan.php');

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

        // User will automatically be signed up to all current ladders this division participates in
        $q_2 = "SELECT ".TBL_TEAMS.".*, "
        .TBL_LADDERS.".*"
        ." FROM ".TBL_TEAMS.", "
        .TBL_LADDERS
        ." WHERE (".TBL_TEAMS.".Division = '$div_id')"
        ." AND (".TBL_TEAMS.".Ladder = ".TBL_LADDERS.".LadderID)"
        ." AND (   (".TBL_LADDERS.".End_timestamp = '')"
        ."        OR (".TBL_LADDERS.".End_timestamp > $time)) ";

        $result_2 = $sql->db_Query($q_2);
        $num_rows_2 = mysql_numrows($result_2);
        if($num_rows_2>0)
        {
            for($j=0; $j<$num_rows_2; $j++)
            {
                $ladder_id  = mysql_result($result_2,$j, TBL_LADDERS.".LadderID");
                $lELO_default  = mysql_result($result_2,$j, TBL_LADDERS.".ELO_default");
                $lTS_default_mu  = mysql_result($result_2,$j, TBL_LADDERS.".TS_default_mu");
                $lTS_default_sigma  = mysql_result($result_2,$j, TBL_LADDERS.".TS_default_sigma");
                $team_id = mysql_result($result_2,$j, TBL_TEAMS.".TeamID");

                // Verify there is no other player for that user/ladder/team
                $q = "SELECT COUNT(*) as NbrPlayers"
                ." FROM ".TBL_PLAYERS
                ." WHERE (Ladder = '$ladder_id')"
                ." AND (Team = '$team_id')"
                ." AND (User = ".USERID.")";
                $result = $sql->db_Query($q);
                $row = mysql_fetch_array($result);
                $nbrplayers = $row['NbrPlayers'];
                if ($nbrplayers == 0)
                {
                    $q = " INSERT INTO ".TBL_PLAYERS."(Ladder,User,Team,ELORanking,TS_mu,TS_sigma)
                    VALUES ($ladder_id,".USERID.",$team_id,$lELO_default,$lTS_default_mu,$lTS_default_sigma)";
                    $sql->db_Query($q);
                    $q4 = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
                    $result = $sql->db_Query($q4);
                }
            }
        }
    }
    // TODO: $clan_id not defined
    header("Location: claninfo.php?clanid=$clan_id");
}
if(isset($_POST['quitdivision']))
{
    $div_id = $_POST['division'];
    $division = new Division($div_id);

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
        $division->deleteMemberPlayers();
        $division->deleteMember();
    }
    // TODO: $clan_id not defined
    header("Location: claninfo.php?clanid=$clan_id");
}
?>
