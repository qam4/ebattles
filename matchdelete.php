<?php
/**
* matchdelete.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);

global $sql;

$text = '';

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    if (!isset($_POST['deletematch']))
    {
        $text .= "<br />You are not authorized to delete this match.<br />";
        $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]<br />";
    }
    else
    {
        $match_id = $_POST['matchid'];
        // Update Players with scores
        $q = "SELECT ".TBL_MATCHS.".*, "
        .TBL_SCORES.".*, "
        .TBL_PLAYERS.".*, "
        .TBL_USERS.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
        ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
        $result = $sql->db_Query($q);
        $num_rows = mysql_numrows($result);
        for($i=0;$i<$num_rows;$i++)
        {
            $pID= mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
            $puid= mysql_result($result,$i, TBL_USERS.".user_id");
            $pname= mysql_result($result,$i, TBL_USERS.".user_name");
            $pELO= mysql_result($result,$i, TBL_PLAYERS.".ELORanking");
            $pTS_mu= mysql_result($result,$i, TBL_PLAYERS.".TS_mu");
            $pTS_sigma= mysql_result($result,$i, TBL_PLAYERS.".TS_sigma");
            $pGamesPlayed= mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
            $pWins= mysql_result($result,$i, TBL_PLAYERS.".Win");
            $pDraws= mysql_result($result,$i, TBL_PLAYERS.".Draw");
            $pLosses= mysql_result($result,$i, TBL_PLAYERS.".Loss");
            $pScore= mysql_result($result,$i, TBL_PLAYERS.".Score");
            $pOppScore= mysql_result($result,$i, TBL_PLAYERS.".ScoreAgainst");
            $pPoints= mysql_result($result,$i, TBL_PLAYERS.".Points");
            $scoreid = mysql_result($result,$i, TBL_SCORES.".ScoreID");
            $pdeltaELO = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
            $pdeltaTS_mu = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_mu");
            $pdeltaTS_sigma = mysql_result($result,$i, TBL_SCORES.".Player_deltaTS_sigma");
            $psWins = mysql_result($result,$i, TBL_SCORES.".Player_Win");
            $psDraws = mysql_result($result,$i, TBL_SCORES.".Player_Draw");
            $psLosses = mysql_result($result,$i, TBL_SCORES.".Player_Loss");
            $psScore = mysql_result($result,$i, TBL_SCORES.".Player_Score");
            $psOppScore = mysql_result($result,$i, TBL_SCORES.".Player_ScoreAgainst");
            $psPoints = mysql_result($result,$i, TBL_SCORES.".Player_Points");

            $pELO -= $pdeltaELO;
            $pTS_mu -= $pdeltaTS_mu;
            $pTS_sigma /= $pdeltaTS_sigma;
            $pWins -= $psWins;
            $pDraws -= $psDraws;
            $pLosses -= $psLosses;
            $pScore -= $psScore;
            $pOppScore -= $psOppScore;
            $pPoints -= $psPoints;
            $pGamesPlayed -= 1;

            $text .= "Player $pname, new ELO:$pELO<br />";

            $q = "UPDATE ".TBL_PLAYERS
            ." SET ELORanking = $pELO,"
            ."     TS_mu = $pTS_mu,"
            ."     TS_sigma = $pTS_sigma,"
            ."     GamesPlayed = $pGamesPlayed,"
            ."     Loss = $pLosses,"
            ."     Win = $pWins,"
            ."     Draw = $pDraws,"
            ."     Score = $pScore,"
            ."     ScoreAgainst = $pOppScore,"
            ."     Points = $pPoints"
            ." WHERE (User = '$puid') AND (Event = '$event_id')";
            $result2 = $sql->db_Query($q);

            // fmarc- Can not reverse "streak" information here :(

            // Delete Score
            $q = "DELETE FROM ".TBL_SCORES." WHERE (ScoreID = '$scoreid')";
            $result2 = $sql->db_Query($q);

        }

        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);

        $text .= "<br />Match deleted<br />";
        $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]<br />";
    }
}
$ns->tablerender('Match Delete', $text);
require_once(FOOTERF);
exit;
?>
