<?php
/**
* updatestats.php
*
*/

$file = 'cache/sql_cache_event_'.$event_id.'.txt';

$id = array();
$uid = array();
$team = array();
$name = array();
$games_played = array();
$ELO = array();
$Skill = array();
$win = array();
$loss = array();
$draw = array();
$windrawloss = array();
$streaks = array();
$victory_ratio = array();
$victory_percent = array();
$unique_opponents = array();
$opponentsELO = array();
$score = array();
$oppscore = array();
$scorediff = array();
$points = array();

$games_played_score = array();
$ELO_score = array();
$Skill_score = array();
$win_score = array();
$loss_score = array();
$draw_score = array();
$windrawloss_score = array();
$victory_ratio_score = array();
$victory_percent_score = array();
$unique_opponents_score = array();
$opponentsELO_score = array();
$streak_score = array();
$score_score = array();
$oppscore_score = array();
$scorediff_score = array();
$points_score = array();


// Update Players stats
$q_1 = "SELECT ".TBL_PLAYERS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_PLAYERS.", "
.TBL_USERS
." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
$result_1 = $sql->db_Query($q_1);
$numPlayers = mysql_numrows($result_1);

$players_rated = 0;
for($i=0; $i<$numPlayers; $i++)
{
    // For each player
    $pid  = mysql_result($result_1,$i, TBL_PLAYERS.".PlayerID");
    $puid  = mysql_result($result_1,$i, TBL_USERS.".user_id");
    $pname  = mysql_result($result_1,$i, TBL_USERS.".user_name");
    $pteam = mysql_result($result_1,$i, TBL_PLAYERS.".Team");
    $pgames_played = mysql_result($result_1,$i, TBL_PLAYERS.".GamesPlayed");
    $pELO = mysql_result($result_1,$i, TBL_PLAYERS.".ELORanking");
    $pTS_mu = mysql_result($result_1,$i, TBL_PLAYERS.".TS_mu");
    $pTS_sigma = mysql_result($result_1,$i, TBL_PLAYERS.".TS_sigma");
    $pSkill = $pTS_mu - 3*$pTS_sigma;
    $pwin = mysql_result($result_1,$i, TBL_PLAYERS.".Win");
    $pdraw = mysql_result($result_1,$i, TBL_PLAYERS.".Draw");
    $ploss = mysql_result($result_1,$i, TBL_PLAYERS.".Loss");
    $pstreak = mysql_result($result_1,$i, TBL_PLAYERS.".Streak");
    $pstreak_worst = mysql_result($result_1,$i, TBL_PLAYERS.".Streak_Worst");
    $pstreak_best = mysql_result($result_1,$i, TBL_PLAYERS.".Streak_Best");
    $pwindrawloss = $pwin."/".$pdraw."/".$ploss;
    $pwinloss = $pwin."/".$ploss;
    $pvictory_ratio = ($ploss>0) ? ($pwin/$ploss) : $pwin; //fm- draw here???
    $pvictory_percent = ($pgames_played>0) ? ((100 * $pwin)/($pwin+$ploss)) : 0;
    $pscore = mysql_result($result_1,$i, TBL_PLAYERS.".Score");
    $poppscore = mysql_result($result_1,$i, TBL_PLAYERS.".ScoreAgainst");
    $ppoints = mysql_result($result_1,$i, TBL_PLAYERS.".Points");

    $popponentsELO = 0;
    $popponents = 0;
    // Unique Opponents
    // Find all matches played by current player
    $q_2 = "SELECT ".TBL_MATCHS.".*, "
    .TBL_SCORES.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".PlayerID = '$pid')";

    $result_2 = $sql->db_Query($q_2);
    $numMatches = mysql_numrows($result_2);

    $players = array();
    if ($numMatches>0)
    {
        for($j=0; $j<$numMatches; $j++)
        {
            // For each match played by current player
            $mID  = mysql_result($result_2,$j, TBL_MATCHS.".MatchID");
            $mplayermatchteam  = mysql_result($result_2,$j, TBL_SCORES.".Player_MatchTeam");

            // Find all scores/players(+users) for that match
            $q_3 = "SELECT ".TBL_MATCHS.".*, "
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

            $result_3 = $sql->db_Query($q_3);
            $numScores = mysql_numrows($result_3);
            for($k=0; $k<$numScores; $k++)
            {
                $ouid  = mysql_result($result_3,$k, TBL_USERS.".user_id");
                $oplayermatchteam  = mysql_result($result_3,$k, TBL_SCORES.".Player_MatchTeam");
                $oELO  = mysql_result($result_3,$k, TBL_PLAYERS.".ELORanking");
                if ($oplayermatchteam != $mplayermatchteam)
                {
                    $players[] = "$ouid";
                    $popponentsELO += $oELO;
                    $popponents += 1;
                }
            }
        }
    }
    $punique_opponents = count(array_unique($players));

    if ($popponents !=0)
    {
        $popponentsELO /= $popponents;
    }

    // For display
    $id[]  = $pid;
    $uid[]  = $puid;
    $name[]  = $pname;
    $team[] = $pteam;
    $games_played[] = $pgames_played;
    $ELO[] = $pELO;
    $Skill[] = max(0,number_format ($pSkill,0));
    $win[] = $pwin;
    $loss[] = $ploss;
    $draw[] = $pdraw;
    $streaks[] = $pstreak."|".$pstreak_best."|".$pstreak_worst;
    $windrawloss[] = $pwindrawloss;
    $victory_ratio[] = $pwinloss;
    $victory_percent[] = number_format ($pvictory_percent,2)." %";
    $unique_opponents[] = $punique_opponents;
    $opponentsELO[] = floor($popponentsELO);
    $score[] = $pscore;
    $oppscore[] = $poppscore;
    $scorediff[] = $pscore - $poppscore;
    $points[] = $ppoints;

    // Actual score (not for display)
    if ($pgames_played >= $emingames)
    {
        $games_played_score[] = $pgames_played;
        $ELO_score[] = $pELO;
        $Skill_score[] = $pSkill;
        $win_score[] = $pwin;
        $loss_score[] = $ploss;
        $draw_score[] = $pdraw;
        $windrawloss_score[] = $pwin - $ploss; //fm - ???
        $victory_ratio_score[] = $pvictory_ratio;
        $victory_percent_score[] = $pvictory_percent;
        $unique_opponents_score[] = $punique_opponents;
        $opponentsELO_score[] = $popponentsELO;
        $streaks_score[] = $pstreak_best; //max(0,$pstreak_best + $pstreak_worst); //fmarc- TBD
        $score_score[] = $pscore;
        $oppscore_score[] = -$poppscore;
        $scorediff_score[] = $pscore - $poppscore;
        $points_score[] = $ppoints;

        $players_rated++;
    }
}

$rating_max= 0;

$q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
." FROM ".TBL_STATSCATEGORIES
." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')"
." ORDER BY ".TBL_STATSCATEGORIES.".CategoryMaxValue DESC";
$result_1 = $sql->db_Query($q_1);
$numCategories = mysql_numrows($result_1);

$stat_cat_header = array();
$stat_min = array();
$stat_max = array();
$stat_a = array();
$stat_b = array();
$stat_score = array();
$stat_display = array();
$cat_index = 0;
for($i=0; $i<$numCategories; $i++)
{
    $cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");
    $cat_minpoints = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMinValue");
    $cat_maxpoints = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMaxValue");
    $cat_InfoOnly = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".InfoOnly");

    if ($cat_maxpoints > 0)
    {
        $display_cat = 1;
        switch ($cat_name)
        {
            case "ELO":
            $cat_header = "<b title=\"ELO\">ELO</b>";
            $min = min($ELO_score);
            $max = max($ELO_score);
            $stat_score[$cat_index] = $ELO_score;
            $stat_display[$cat_index] = $ELO;
            break;
            case "Skill":
            $cat_header = "<b title=\"TrueSkill(TM)\">Skill</b>";
            $min = min($Skill_score);
            $max = max($Skill_score);
            $stat_score[$cat_index] = $Skill_score;
            $stat_display[$cat_index] = $Skill;
            break;
            case "GamesPlayed":
            $cat_header = "<b title=\"Number of games played\">Games</b>";
            $min = 0; //min($games_played_score);
            $max = max($games_played);
            $stat_score[$cat_index] = $games_played_score;
            $stat_display[$cat_index] = $games_played;
            break;
            case "VictoryRatio":
            $cat_header = "<b title=\"Win/Loss ratio\">W/L</b>";
            $min = 0; //min($victory_ratio_score);
            $max = max($victory_ratio_score);
            $stat_score[$cat_index] = $victory_ratio_score;
            $stat_display[$cat_index] = $victory_ratio;
            break;
            case "VictoryPercent":
            $cat_header = "<b title=\"Wins percentage\">W%</b>";
            $min = 0; //min($victory_percent_score);
            $max = max($victory_percent_score);
            $stat_score[$cat_index] = $victory_percent_score;
            $stat_display[$cat_index] = $victory_percent;
            break;
            case "WinDrawLoss":
            $cat_header = "<b title=\"Win/Draw/Loss\">W/D/L</b>";
            $min = min($windrawloss_score);
            $max = max($windrawloss_score);
            $stat_score[$cat_index] = $windrawloss_score;
            $stat_display[$cat_index] = $windrawloss;
            break;
            case "UniqueOpponents":
            $cat_header = "<b title=\"Unique Opponents\">Opponents</b>";
            $min = 0; //min($unique_opponents_score);
            $max = max($unique_opponents_score);
            $stat_score[$cat_index] = $unique_opponents_score;
            $stat_display[$cat_index] = $unique_opponents;
            break;
            case "OpponentsELO":
            $cat_header = "<b title=\"Opponents Average ELO\">Opp. ELO</b>";
            $min = min($opponentsELO_score);
            $max = max($opponentsELO_score);
            $stat_score[$cat_index] = $opponentsELO_score;
            $stat_display[$cat_index] = $opponentsELO;
            break;
            case "Streaks":
            $cat_header = "<b title=\"Current|Best|Worst Streaks\">Streaks</b>";
            $min = min($streaks_score);
            $max = max($streaks_score);
            $stat_score[$cat_index] = $streaks_score;
            $stat_display[$cat_index] = $streaks;
            break;
            case "Score":
            $cat_header = "<b title=\"Score\">Score</b>";
            $min = min($score_score);
            $max = max($score_score);
            $stat_score[$cat_index] = $score_score;
            $stat_display[$cat_index] = $score;
            break;
            case "ScoreAgainst":
            $cat_header = "<b title=\"Opponents Score\">Opp. Score</b>";
            $min = min($oppscore_score);
            $max = max($oppscore_score);
            $stat_score[$cat_index] = $oppscore_score;
            $stat_display[$cat_index] = $oppscore;
            break;
            case "ScoreDiff":
            $cat_header = "<b title=\"Score Difference\">Score Diff.</b>";
            $min = min($scorediff_score);
            $max = max($scorediff_score);
            $stat_score[$cat_index] = $scorediff_score;
            $stat_display[$cat_index] = $scorediff;
            break;
            case "Points":
            $cat_header = "<b title=\"Points\">Points</b>";
            $min = min($points_score);
            $max = max($points_score);
            $stat_score[$cat_index] = $points_score;
            $stat_display[$cat_index] = $points;
            break;
            default:
            $display_cat = 0;
        }

        if ($display_cat==1)
        {
            $stat_InfoOnly[$cat_index] = $cat_InfoOnly;
            if ($cat_InfoOnly == TRUE)
            {
                $cat_header .= "";
            }
            else
            {
                $cat_header .= "<br /><div class='smalltext'>[".number_format ($cat_maxpoints,2)." max]</div>";

                // a = (ymax-ymin)/(xmax-xmin)
                // b = ymin - a.xmin
                if ($max==$min)
                {
                    $a = 0;
                    $b = $cat_maxpoints;
                }
                else
                {
                    $a = ($cat_maxpoints-$cat_minpoints) / ($max-$min);
                    $b = $cat_minpoints - $a * $min;
                }

                $stat_min[$cat_index] = $min;
                $stat_max[$cat_index] = $max;
                $stat_a[$cat_index] = $a;
                $stat_b[$cat_index] = $b;

                $rating_max += $cat_maxpoints;
            }
            $stat_cat_header[$cat_index] = $cat_header;
            $cat_index++;
        }
    }
}
$numDisplayedCategories = $cat_index;

$stats = array
(
"0"=>array("header","<b>Rank</b>","<b>Player</b>")
);

$stats[0][] = "<b title=\"Rating\">Rating</b><br /><div class='smalltext'>[".number_format ($rating_max,2)." max]</div>";
for ($j=0; $j<$numDisplayedCategories; $j++)
{
    $stats[0][] = $stat_cat_header[$j];
}

$player_index=0;
$final_score = array();
for($i=0; $i<$numPlayers; $i++)
{
    $OverallScore[$i]=0;
    if ($games_played[$i] >= $emingames)
    {
        for ($j=0; $j<$numDisplayedCategories; $j++)
        {
            if ($stat_InfoOnly[$j] == FALSE)
            {
                $final_score[$j][$i] = $stat_a[$j] * $stat_score[$j][$player_index] + $stat_b[$j];
                $OverallScore[$i]+=$final_score[$j][$i];
            }
        }
        $player_index++;
    }
    else
    {
        for ($j=0; $j<$numDisplayedCategories; $j++)
        {
            $final_score[$j][$i] = 0;
        }
    }

    $q_3 = "UPDATE ".TBL_PLAYERS." SET OverallScore = $OverallScore[$i] WHERE (PlayerID = '$id[$i]') AND (Event = '$event_id')";
    $result_3 = $sql->db_Query($q_3);
}
// Build results table
//--------------------
$q_1 = "SELECT *"
." FROM ".TBL_PLAYERS
." WHERE (Event = '$event_id')"
." ORDER BY ".TBL_PLAYERS.".OverallScore DESC, ".TBL_PLAYERS.".GamesPlayed DESC, ".TBL_PLAYERS.".ELORanking DESC";
$result_1 = $sql->db_Query($q_1);
$ranknumber = 1;
for($i=0; $i<$numPlayers; $i++)
{
    $pid = mysql_result($result_1,$i, TBL_PLAYERS.".PlayerID");
    $puid = mysql_result($result_1,$i, TBL_PLAYERS.".User");
    $prank = mysql_result($result_1,$i, TBL_PLAYERS.".Rank");
    $prankdelta = mysql_result($result_1,$i, TBL_PLAYERS.".RankDelta");
    $pstreak = mysql_result($result_1,$i, TBL_PLAYERS.".Streak");

    // Find index of player
    $index = array_search($pid,$id);

    $prank_side_image = "";
    if($OverallScore[$index]==0)
    {
        $rank = '<span title="Not ranked">-</span>';
        $prankdelta_string = "";
    }
    else
    {
        $rank = $ranknumber;
        $ranknumber++; // increases $ranknumber by 1
        $q_2 = "UPDATE ".TBL_PLAYERS." SET Rank = $rank WHERE (PlayerID = '$pid') AND (Event = '$event_id')";
        $result_2 = $sql->db_Query($q_2);

        $new_rankdelta = $prank - $rank;
        if ($new_rankdelta != 0)
        {
            $prankdelta += $new_rankdelta;
            $q_2 = "UPDATE ".TBL_PLAYERS." SET RankDelta = $prankdelta WHERE (PlayerID = '$pid') AND (Event = '$event_id')";
            $result_2 = $sql->db_Query($q_2);
        }

        if (($new_rankdelta != 0)&&($rank==1))
        {
            // Award: player took 1st place
            $q_2 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
            VALUES ($pid,'PlayerTookFirstPlace',$time)";
            $result_2 = $sql->db_Query($q_2);
        }
        if (($new_rankdelta != 0)&&(($prank>10)||($prank==0))&&($rank<=10))
        {
            // Award: player enters top 10
            $q_2 = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
            VALUES ($pid,'PlayerInTopTen',$time)";
            $result_2 = $sql->db_Query($q_2);
        }

        $q_2 = "SELECT ".TBL_AWARDS.".*, "
        .TBL_PLAYERS.".*"
        ." FROM ".TBL_AWARDS.", "
        .TBL_PLAYERS
        ." WHERE (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
        ." AND (".TBL_PLAYERS.".PlayerID = '$pid')"
        ." ORDER BY ".TBL_AWARDS.".timestamp DESC";
        $result_2 = $sql->db_Query($q_2);
        $numAwards = mysql_numrows($result_2);
        if ($numAwards > 0)
        {
            $paward  = mysql_result($result_2,0, TBL_AWARDS.".AwardID");
            $pawardType  = mysql_result($result_2,0, TBL_AWARDS.".Type");
        }

        if ($rank==1)
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/award_star_gold_3.png\" alt=\"1st place\" title=\"1st place\"></img>";
        }
        else if (($rank<=10)&&(($rank+$prankdelta>min(10,$nbrplayers))||($rank+$prankdelta==0)))
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/award_star_bronze_3.png\" alt=\"top 10\" title=\"top 10\"></img>";
        }
        else if (($numAwards>0)&&($pawardType!='PlayerTookFirstPlace')&&($pawardType!='PlayerInTopTen')&&($pstreak>=5))
        {
            switch ($pawardType)
            {
                case 'PlayerStreak5':
                if ($pstreak>=5)
                {
                    $award = " won 5 games in a row";
                    $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/medal_bronze_3.png\" alt=\"Streak 5\" title=\"5 wins in a row\"></img>";
                }
                break;
                case 'PlayerStreak10':
                if ($pstreak>=10)
                {
                    $award = " won 10 games in a row";
                    $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/medal_silver_3.png\" alt=\"Streak 10\" title=\"10 wins in a row\"></img>";
                }
                break;
                case 'PlayerStreak25':
                if ($pstreak>=25)
                {
                    $award = " won 25 games in a row";
                    $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/medal_gold_3.png\" alt=\"Streak 25\" title=\"25 wins in a row\"></img>";
                }
                break;
            }
        }
        else if ($prankdelta>0)
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/arrow_up.gif\" alt=\"+$prankdelta\" title=\"+$prankdelta\"></img>";
        }
        else if (($prankdelta<0)&&($rank+$prankdelta!=0))
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/arrow_down.gif\" alt=\"$prankdelta\" title=\"$prankdelta\"></img>";
        }
        else if ($rank+$prankdelta==0)
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/arrow_up.gif\" alt=\"Up\" title=\"From unranked\"></img>";
        }
    }

    $pclan = '';
    $pclantag = '';
    if ($etype == "Team Ladder")
    {
        $q_2 = "SELECT ".TBL_CLANS.".*, "
        .TBL_DIVISIONS.".*, "
        .TBL_TEAMS.".* "
        ." FROM ".TBL_CLANS.", "
        .TBL_DIVISIONS.", "
        .TBL_TEAMS
        ." WHERE (".TBL_TEAMS.".TeamID = '$team[$index]')"
        ." AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
        ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
        $result_2 = $sql->db_Query($q_2);
        $numClans = mysql_numrows($result_2);
        if ($numClans == 1)
        {
            $pclan  = mysql_result($result_2,0, TBL_CLANS.".Name");
            $pclantag  = mysql_result($result_2,0, TBL_CLANS.".Tag")."_";
        }
    }

    if(strcmp(USERID,$puid) == 0)
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

    $stats_row[] = "<b>$rank</b> $prank_side_image";
    $stats_row[] = "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$uid[$index]\"><b>$pclantag$name[$index]</b></a>";
    $stats_row[] = number_format ($OverallScore[$index],2);
    for ($j=0; $j<$numDisplayedCategories; $j++)
    {
        if ($stat_InfoOnly[$j] == TRUE)
        {
            $stats_row[] = $stat_display[$j][$index];
        }
        else
        {
            $stats_row[] = $stat_display[$j][$index]."<br />[".number_format ($final_score[$j][$index],2)."]";
        }
    }
    $stats[] = $stats_row;
}

/*
// debug print array
include_once(e_PLUGIN."ebattles/include/show_array.php");
echo "<br />";
html_show_table($stats, $numPlayers+1, 7);
echo "<br />";
*/

// Serialize results array
$OUTPUT = serialize($stats);
$fp = fopen($file,"w"); // open file with Write permission
fputs($fp, $OUTPUT);
fclose($fp);

/*
$stats = unserialize(implode('',file($file)));
foreach ($stats as $uid=>$row)
{
print $row['category_name']."<br />";
}
*/

?>
