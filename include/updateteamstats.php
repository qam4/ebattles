<?php
/**
* updateteamstats.php
*
*/

/* include_once(e_PLUGIN."ebattles/include/session.php"); */

$file_team = 'cache/sql_cache_event_team_'.$event_id.'.txt';


$stats = array
(
"0"=>array
(
"header",
"<b>Rank</b>",
"<b>Team</b>",
"<b>Players</b>",
)
);

$stats[0][] = "<b>Rating</b><br />[".number_format ($rating_max,2)." max]";

if ($ELO_maxpoints > 0)
{
    $stats[0][] = "<b>ELO</b><br />[".number_format ($ELO_maxpoints,2)." max]";
}
if ($games_played_maxpoints > 0)
{
    $stats[0][] = "<b>Games</b><br />[".number_format ($games_played_maxpoints,2)." max]";
}
if ($victory_ratio_maxpoints > 0)
{
    $stats[0][] = "<b>W/L</b><br />[".number_format ($victory_ratio_maxpoints,2)." max]";
}
if ($victory_percent_maxpoints > 0)
{
    $stats[0][] = "<b>W%</b><br />[".number_format ($victory_percent_maxpoints,2)." max]";
}
if ($unique_opponents_maxpoints > 0)
{
    $stats[0][] = "<b>Unique Opponents</b><br />[".number_format ($unique_opponents_maxpoints,2)." max]";
}
if ($opponentsELO_maxpoints > 0)
{
    $stats[0][] = "<b>Opponents Avg ELO</b><br />[".number_format ($opponentsELO_maxpoints,2)." max]";
}

$q_1 = "SELECT ".TBL_TEAMS.".*, "
.TBL_DIVISIONS.".*, "
.TBL_CLANS.".*"
." FROM ".TBL_TEAMS.", "
.TBL_DIVISIONS.", "
.TBL_CLANS
." WHERE (".TBL_TEAMS.".Event = '$event_id')"
." AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";

$result_1 = $sql->db_Query($q_1);
$num_rows = mysql_numrows($result_1);
$nbrteams = $num_rows;
$teams_rated = 0;

$id = array();
$clan = array();
$clantag = array();
$name = array();
$nbr_players = array();
$games_played = array();
$ELO = array();
$win = array();
$loss = array();
$winloss = array();
$victory_ratio = array();
$victory_percent = array();
$unique_opponents = array();
$opponentsELO = array();
$games_played_score = array();
$ELO_score = array();
$win_score = array();
$loss_score = array();
$winloss_score = array();
$victory_ratio_score = array();
$victory_percent_score = array();
$unique_opponents_score = array();
$opponentsELO_score = array();

for($i=0; $i<$nbrteams; $i++)
{
    $tid = mysql_result($result_1,$i, TBL_TEAMS.".TeamID");
    $tname = mysql_result($result_1,$i, TBL_CLANS.".Name");
    $tclan = mysql_result($result_1,$i, TBL_CLANS.".ClanID");
    $tclantag = mysql_result($result_1,$i, TBL_CLANS.".Tag");

    $q_2 = "SELECT * "
    ." FROM ".TBL_PLAYERS." "
    ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
    ." AND (".TBL_PLAYERS.".Team = '$tid')";

    $result_2 = $sql->db_Query($q_2);
    $num_rows_2 = mysql_numrows($result_2);
    $tPlayers = $num_rows_2;
    $tnbrplayers_rated = 0;

    $tOverallScore = 0;
    $tELO = 0;
    $twin = 0;
    $tloss = 0;
    $tgames_played = 0;
    $tunique_opponents = 0;
    $topponentsELO = 0;
    $topponents = 0;

    $min_team_games = $eminteamgames;

    if ($tPlayers>0)
    {
        for($j=0; $j<$tPlayers; $j++)
        {
            $puid = mysql_result($result_2,$j, TBL_PLAYERS.".User");
            $pgames_played = mysql_result($result_2,$j, TBL_PLAYERS.".GamesPlayed");
            $pELO = mysql_result($result_2,$j, TBL_PLAYERS.".ELORanking");
            $pwin = mysql_result($result_2,$j, TBL_PLAYERS.".Win");
            $ploss = mysql_result($result_2,$j, TBL_PLAYERS.".Loss");

            $popponentsELO = 0;
            $popponents = 0;
            // Unique Opponents
            $q_3 = "SELECT DISTINCT ".TBL_MATCHS.".*, "
            .TBL_SCORES.".*"
            ." FROM ".TBL_MATCHS.", "
            .TBL_SCORES.", "
            .TBL_PLAYERS
            ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
            ." AND (".TBL_MATCHS.".Event = '$event_id')"
            ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
            ." AND (".TBL_PLAYERS.".User = '$puid')";

            $result_3 = $sql->db_Query($q_3);
            $num_rows_3 = mysql_numrows($result_3);

            $players = '';
            if ($num_rows_3>0)
            {
                for($k=0; $k<$num_rows_3; $k++)
                {
                    $mID  = mysql_result($result_3,$k, TBL_MATCHS.".MatchID");
                    $mplayermatchteam  = mysql_result($result_3,$k, TBL_SCORES.".Player_MatchTeam");

                    $q_4 = "SELECT ".TBL_MATCHS.".*, "
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
                    ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";

                    $result_4 = $sql->db_Query($q_4);
                    $num_rows_4 = mysql_numrows($result_4);
                    for($l=0; $l<$num_rows_4; $l++)
                    {
                        $uid  = mysql_result($result_4,$l, TBL_USERS.".user_id");
                        $uplayermatchteam  = mysql_result($result_4,$l, TBL_SCORES.".Player_MatchTeam");
                        $uELO  = mysql_result($result_4,$l, TBL_PLAYERS.".ELORanking");
                        $players[] = "$uid";

                        if ($uplayermatchteam != $mplayermatchteam)
                        {
                            $popponentsELO += $uELO;
                            $popponents += 1;
                        }
                    }
                }
            }

            if (count($players)>1)
            {
                $punique_opponents = count(array_unique($players)) - 1;
                //echo "<br />$puid Unique Opponents: $unique_opponents<br />";
            }
            else
            {
                $punique_opponents = 0;
            }

            $twin += $pwin;
            $tloss += $ploss;
            $tgames_played += $pgames_played;
            $tunique_opponents += $punique_opponents;
            $topponentsELO += $popponentsELO;
            $topponents += $popponents;

            if ($pgames_played>=$emingames)
            {
                $tnbrplayers_rated++;
                $tELO += mysql_result($result_2,$j, TBL_PLAYERS.".ELORanking");
            }
        }

        if ($topponents !=0)
        {
            $topponentsELO /= $topponents;
        }

        if ($tnbrplayers_rated>0)
        {
            $tELO /= $tnbrplayers_rated;
        }
    }

    $twinloss = $twin."/".$tloss;
    $tvictory_ratio = ($tloss>0) ? ($twin/$tloss) : $twin;
    $tvictory_percent = ($tgames_played>0) ? ((100 * $twin)/$tgames_played) : 0;

    $id[]  = $tid;
    $name[]  = $tname;
    $clan[]  = $tclan;
    $clantag[]  = $tclantag;
    $nbr_players[]  = $tPlayers;
    $games_played[] = $tgames_played;
    $ELO[] = $tELO;
    $win[] = $twin;
    $loss[] = $tloss;
    $winloss[] = $twinloss;
    $victory_ratio[] = $tvictory_ratio;
    $victory_percent[] = $tvictory_percent;
    $unique_opponents[] = $tunique_opponents;
    $opponentsELO[] = $topponentsELO;

    if ($tgames_played >= $eminteamgames)
    {
        $games_played_score[] = $tgames_played;
        $ELO_score[] = $tELO;
        $win_score[] = $twin;
        $loss_score[] = $tloss;
        $winloss_score[] = $twinloss;
        $victory_ratio_score[] = $tvictory_ratio;
        $victory_percent_score[] = $tvictory_percent;
        $unique_opponents_score[] = $tunique_opponents;
        $opponentsELO_score[] = $topponentsELO;

        $teams_rated++;
    }
}

if ($teams_rated>0)
{
    $games_played_min = 0; //min($games_played_score);
    $ELO_min = min($ELO_score);
    $victory_ratio_min = 0; //min($victory_ratio_score);
    $victory_percent_min = 0; //min($victory_percent_score);
    $unique_opponents_min = 0; //min($unique_opponents_score);
    $opponentsELO_min = min($opponentsELO_score);

    $games_played_max = max($games_played);
    $ELO_max = max($ELO_score);
    $victory_ratio_max = max($victory_ratio_score);
    $victory_percent_max = max($victory_percent_score);
    $unique_opponents_max = max($unique_opponents_score);
    $opponentsELO_max = max($opponentsELO_score);

    // a = (ymax-ymin)/(xmax-xmin)
    // b = ymin - a.xmin
    if ($ELO_max==$ELO_min)
    {
        $ELO_a = 0;
        $ELO_b = $ELO_maxpoints;
    }
    else
    {
        $ELO_a = ($ELO_maxpoints-$ELO_minpoints) / ($ELO_max-$ELO_min);
        $ELO_b = $ELO_minpoints - $ELO_a * $ELO_min;
    }
    if ($games_played_max==$games_played_min)
    {
        $games_played_a = 00;
        $games_played_b = $games_played_maxpoints;
    }
    else
    {
        $games_played_a = ($games_played_maxpoints-$games_played_minpoints) / ($games_played_max-$games_played_min);
        $games_played_b = $games_played_minpoints - $games_played_a * $games_played_min;
    }
    if ($victory_ratio_max==$victory_ratio_min)
    {
        $victory_ratio_a = 0;
        $victory_ratio_b = $victory_ratio_maxpoints;
    }
    else
    {
        $victory_ratio_a = ($victory_ratio_maxpoints-$victory_ratio_minpoints) / ($victory_ratio_max-$victory_ratio_min);
        $victory_ratio_b = $victory_ratio_minpoints - $victory_ratio_a * $victory_ratio_min;
    }
    if ($victory_percent_max==$victory_percent_min)
    {
        $victory_percent_a = 0;
        $victory_percent_b = $victory_percent_maxpoints;
    }
    else
    {
        $victory_percent_a = ($victory_percent_maxpoints-$victory_percent_minpoints) / ($victory_percent_max-$victory_percent_min);
        $victory_percent_b = $victory_percent_minpoints - $victory_percent_a * $victory_percent_min;
    }
    if ($unique_opponents_max==$unique_opponents_min)
    {
        $unique_opponents_a = 0;
        $unique_opponents_b = $unique_opponents_maxpoints;
    }
    else
    {
        $unique_opponents_a = ($unique_opponents_maxpoints-$unique_opponents_minpoints) / ($unique_opponents_max-$unique_opponents_min);
        $unique_opponents_b = $unique_opponents_minpoints - $unique_opponents_a * $unique_opponents_min;
    }
    if ($opponentsELO_max==$opponentsELO_min)
    {
        $opponentsELO_a = 0;
        $opponentsELO_b = $opponentsELO_maxpoints;
    }
    else
    {
        $opponentsELO_a = ($opponentsELO_maxpoints-$opponentsELO_minpoints) / ($opponentsELO_max-$opponentsELO_min);
        $opponentsELO_b = $opponentsELO_minpoints - $opponentsELO_a * $opponentsELO_min;
    }
}

for($i=0; $i<$nbrteams; $i++)
{
    if ($games_played[$i] >= $emingames)
    {
        $ELO_final_score[$i] = $ELO_a * $ELO[$i] + $ELO_b;
        $games_played_final_score[$i] = $games_played_a * $games_played[$i] + $games_played_b;
        $victory_ratio_final_score[$i] = $victory_ratio_a * $victory_ratio[$i] + $victory_ratio_b;
        $victory_percent_final_score[$i] = $victory_percent_a * $victory_percent[$i] + $victory_percent_b;
        $unique_opponents_final_score[$i] = $unique_opponents_a * $unique_opponents[$i] + $unique_opponents_b;
        $opponentsELO_final_score[$i] = $opponentsELO_a * $opponentsELO[$i] + $opponentsELO_b;
    }
    else
    {
        $ELO_final_score[$i] = 0;
        $games_played_final_score[$i] = 0;
        $victory_ratio_final_score[$i] = 0;
        $victory_percent_final_score[$i] = 0;
        $unique_opponents_final_score[$i] = 0;
        $opponentsELO_final_score[$i] = 0;
    }

    $OverallScore[$i] = $ELO_final_score[$i] + $games_played_final_score[$i] + $victory_ratio_final_score[$i] + $victory_percent_final_score[$i] + $unique_opponents_final_score[$i] + $opponentsELO_final_score[$i];

    $q_3 = "UPDATE ".TBL_TEAMS." SET ELORanking = $tELO WHERE (TeamID = '$id[$i]') AND (Event = '$event_id')";
    $result_3 = $sql->db_Query($q_3);
    $q_3 = "UPDATE ".TBL_TEAMS." SET Win = $twin WHERE (TeamID = '$id[$i]') AND (Event = '$event_id')";
    $result_3 = $sql->db_Query($q_3);
    $q_3 = "UPDATE ".TBL_TEAMS." SET Loss = $tloss WHERE (TeamID = '$id[$i]') AND (Event = '$event_id')";
    $result_3 = $sql->db_Query($q_3);
    $q_3 = "UPDATE ".TBL_TEAMS." SET OverallScore = $OverallScore[$i] WHERE (TeamID = '$id[$i]') AND (Event = '$event_id')";
    $result_3 = $sql->db_Query($q_3);
}

// Calculate Rank
//----------------
$q_1 = "SELECT *"
." FROM ".TBL_TEAMS
." WHERE (Event = '$event_id')"
." ORDER BY ".TBL_TEAMS.".OverallScore DESC, ".TBL_TEAMS.".ELORanking DESC";

$result_1 = $sql->db_Query($q_1);
$num_rows = mysql_numrows($result_1);

$ranknumber = 1;
for($i=0; $i<$num_rows; $i++)
{
    $tid = mysql_result($result_1,$i, TBL_TEAMS.".TeamID");

    $q_2 = "UPDATE ".TBL_TEAMS." SET Rank = $ranknumber WHERE (TeamID = '$tid') AND (Event = '$event_id')";
    $result_2 = $sql->db_Query($q_2);

    $index = array_search($tid,$id);

    $q_2 = "SELECT *"
    ." FROM ".TBL_PLAYERS
    ." WHERE (".TBL_PLAYERS.".Team = '$tid')"
    ." AND (".TBL_PLAYERS.".User = ".USERID.")";
    $result_2 = $sql->db_Query($q_2);
    $num_rows_2 = mysql_numrows($result_2);
    if($num_rows_2 > 0)
    {
        $stats_row = array
        (
        "row_highlight"
        );
    }
    else
    {
        $stats_row = array
        (
        "row"
        );
    }

    $stats_row[] = "<b>$ranknumber</b>";
    $stats_row[] = "<a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$clan[$index]\"><b>$name[$index] ($clantag[$index])</b></a>";
    $stats_row[] = "$nbr_players[$index]";
    $stats_row[] = number_format ($OverallScore[$index],2);
    if ($ELO_maxpoints > 0)
    {
        $stats_row[] = "$ELO[$index]<br />[".number_format ($ELO_final_score[$index],2)."]";
    }
    if ($games_played_maxpoints > 0)
    {
        $stats_row[] = "$games_played[$index]<br />[".number_format ($games_played_final_score[$index],2)."]";
    }
    if ($victory_ratio_maxpoints > 0)
    {
        $stats_row[] = "$winloss[$index]<br />[".number_format ($victory_ratio_final_score[$index],2)."]";
    }
    if ($victory_percent_maxpoints > 0)
    {
        $stats_row[] = number_format ($victory_percent[$index],2)." %<br />[".number_format ($victory_percent_final_score[$index],2)."]";
    }
    if ($unique_opponents_maxpoints > 0)
    {
        $stats_row[] = "$unique_opponents[$index]<br />[".number_format ($unique_opponents_final_score[$index],2)."]";
    }
    if ($opponentsELO_maxpoints > 0)
    {
        $stats_row[] = floor($opponentsELO[$index])."<br />[".number_format ($opponentsELO_final_score[$index],2)."]";
    }

    $stats[] = $stats_row;
    $ranknumber++; // increases $ranknumber by 1
}




/*
// debug print array
include_once(e_PLUGIN."ebattles/include/show_array.php");
echo "<br />";
html_show_table($stats, $num_rows+1, 7);
echo "<br />";
*/

// Serialize results array
$OUTPUT = serialize($stats);
$fp = fopen($file_team,"w"); // open file with Write permission
fputs($fp, $OUTPUT);
fclose($fp);

/*
$stats = unserialize(implode('',file($file_team)));
foreach ($stats as $id=>$row)
{
print $row['category_name']."<br />";
}
*/

