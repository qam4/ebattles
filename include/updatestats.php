<?php
/**
* updatestats.php
*
*/

require_once(e_HANDLER."avatar_handler.php");

$file = 'cache/sql_cache_event_'.$event_id.'.txt';

$id = array();
$uid = array();
$team = array();
$name = array();
$avatar = array();
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
$q_Players = "SELECT ".TBL_PLAYERS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_PLAYERS.", "
.TBL_USERS
." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
$result_Players = $sql->db_Query($q_Players);
$numPlayers = mysql_numrows($result_Players);

$players_rated = 0;
for($player=0; $player<$numPlayers; $player++)
{
    // For each player
    $pid  = mysql_result($result_Players,$player, TBL_PLAYERS.".PlayerID");
    $puid  = mysql_result($result_Players,$player, TBL_PLAYERS.".User");
    $pname  = mysql_result($result_Players,$player, TBL_USERS.".user_name");
    $pavatar = mysql_result($result_Players,$player, TBL_USERS.".user_image");
    $pteam = mysql_result($result_Players,$player, TBL_PLAYERS.".Team");
    $pgames_played = mysql_result($result_Players,$player, TBL_PLAYERS.".GamesPlayed");
    $pELO = mysql_result($result_Players,$player, TBL_PLAYERS.".ELORanking");
    $pTS_mu = mysql_result($result_Players,$player, TBL_PLAYERS.".TS_mu");
    $pTS_sigma = mysql_result($result_Players,$player, TBL_PLAYERS.".TS_sigma");
    $pSkill = $pTS_mu - 3*$pTS_sigma;
    $pwin = mysql_result($result_Players,$player, TBL_PLAYERS.".Win");
    $pdraw = mysql_result($result_Players,$player, TBL_PLAYERS.".Draw");
    $ploss = mysql_result($result_Players,$player, TBL_PLAYERS.".Loss");
    $pstreak = mysql_result($result_Players,$player, TBL_PLAYERS.".Streak");
    $pstreak_worst = mysql_result($result_Players,$player, TBL_PLAYERS.".Streak_Worst");
    $pstreak_best = mysql_result($result_Players,$player, TBL_PLAYERS.".Streak_Best");
    $pwindrawloss = $pwin."/".$pdraw."/".$ploss;
    $pwinloss = $pwin."/".$ploss;
    $pvictory_ratio = ($ploss>0) ? ($pwin/$ploss) : $pwin; //fm- draw here???
    $pvictory_percent = ($pgames_played>0) ? ((100 * $pwin)/($pwin+$ploss)) : 0;
    $pscore = mysql_result($result_Players,$player, TBL_PLAYERS.".Score");
    $poppscore = mysql_result($result_Players,$player, TBL_PLAYERS.".ScoreAgainst");
    $ppoints = mysql_result($result_Players,$player, TBL_PLAYERS.".Points");

    $popponentsELO = 0;
    $popponents = 0;
    // Unique Opponents
    // Find all matches played by current player
    $q_Matches = "SELECT ".TBL_MATCHS.".*, "
            .TBL_SCORES.".*, "
            .TBL_PLAYERS.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES.", "
    .TBL_PLAYERS
    ." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".PlayerID = '$pid')";

    $result_Matches = $sql->db_Query($q_Matches);
    $numMatches = mysql_numrows($result_Matches);

    $players = array();
    if ($numMatches>0)
    {
        for($match=0; $match<$numMatches; $match++)
        {
            // For each match played by current player
            $mID  = mysql_result($result_Matches,$match, TBL_MATCHS.".MatchID");
            $mplayermatchteam  = mysql_result($result_Matches,$match, TBL_SCORES.".Player_MatchTeam");

            // Find all scores/players(+users) for that match
            $q_Scores = "SELECT ".TBL_MATCHS.".*, "
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

            $result_Scores = $sql->db_Query($q_Scores);
            $numScores = mysql_numrows($result_Scores);
            for($scoreIndex=0; $scoreIndex<$numScores; $scoreIndex++)
            {
                $ouid  = mysql_result($result_Scores,$scoreIndex, TBL_USERS.".user_id");
                $oplayermatchteam  = mysql_result($result_Scores,$scoreIndex, TBL_SCORES.".Player_MatchTeam");
                $oELO  = mysql_result($result_Scores,$scoreIndex, TBL_PLAYERS.".ELORanking");
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
    $avatar[] = $pavatar;
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
    $score[] = ($pgames_played>0) ? number_format($pscore/$pgames_played,2) : 0;
    $oppscore[] = ($pgames_played>0) ? number_format($poppscore/$pgames_played,2) : 0;
    $scorediff[] = ($pgames_played>0) ? number_format(($pscore - $poppscore)/$pgames_played,2) : 0;
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
        $score_score[] = ($pgames_played>0) ? $pscore/$pgames_played : 0;
        $oppscore_score[] = ($pgames_played>0) ? -$poppscore/$pgames_played : 0;
        $scorediff_score[] = ($pgames_played>0) ? ($pscore - $poppscore)/$pgames_played : 0;
        $points_score[] = ($pgames_played>0) ? $ppoints/$pgames_played : 0;

        $players_rated++;
    }
}

$rating_max= 0;

$q_Categories = "SELECT ".TBL_STATSCATEGORIES.".*"
." FROM ".TBL_STATSCATEGORIES
." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')"
." ORDER BY ".TBL_STATSCATEGORIES.".CategoryMaxValue DESC";
$result_Categories = $sql->db_Query($q_Categories);
$numCategories = mysql_numrows($result_Categories);

$stat_cat_header = array();
$stat_min = array();
$stat_max = array();
$stat_a = array();
$stat_b = array();
$stat_score = array();
$stat_display = array();
$cat_index = 0;
for($category=0; $category<$numCategories; $category++)
{
    $cat_name = mysql_result($result_Categories,$category, TBL_STATSCATEGORIES.".CategoryName");
    $cat_minpoints = mysql_result($result_Categories,$category, TBL_STATSCATEGORIES.".CategoryMinValue");
    $cat_maxpoints = mysql_result($result_Categories,$category, TBL_STATSCATEGORIES.".CategoryMaxValue");
    $cat_InfoOnly = mysql_result($result_Categories,$category, TBL_STATSCATEGORIES.".InfoOnly");

    if ($cat_maxpoints > 0)
    {
        $display_cat = 1;
        switch ($cat_name)
        {
            case "ELO":
            $cat_header = '<b title="ELO">ELO</b>';
            $min = min($ELO_score);
            $max = max($ELO_score);
            $stat_score[$cat_index] = $ELO_score;
            $stat_display[$cat_index] = $ELO;
            break;
            case "Skill":
            $cat_header = '<b title="TrueSkill(TM)">Skill</b>';
            $min = min($Skill_score);
            $max = max($Skill_score);
            $stat_score[$cat_index] = $Skill_score;
            $stat_display[$cat_index] = $Skill;
            break;
            case "GamesPlayed":
            $cat_header = '<b title="Number of games played">Games</b>';
            $min = 0; //min($games_played_score);
            $max = max($games_played);
            $stat_score[$cat_index] = $games_played_score;
            $stat_display[$cat_index] = $games_played;
            break;
            case "VictoryRatio":
            $cat_header = '<b title="Win/Loss ratio">W/L</b>';
            $min = 0; //min($victory_ratio_score);
            $max = max($victory_ratio_score);
            $stat_score[$cat_index] = $victory_ratio_score;
            $stat_display[$cat_index] = $victory_ratio;
            break;
            case "VictoryPercent":
            $cat_header = '<b title="Wins percentage">W%</b>';
            $min = 0; //min($victory_percent_score);
            $max = max($victory_percent_score);
            $stat_score[$cat_index] = $victory_percent_score;
            $stat_display[$cat_index] = $victory_percent;
            break;
            case "WinDrawLoss":
            $cat_header = '<b title="Win/Draw/Loss">W/D/L</b>';
            $min = min($windrawloss_score);
            $max = max($windrawloss_score);
            $stat_score[$cat_index] = $windrawloss_score;
            $stat_display[$cat_index] = $windrawloss;
            break;
            case "UniqueOpponents":
            $cat_header = '<b title="Unique Opponents">Opponents</b>';
            $min = 0; //min($unique_opponents_score);
            $max = max($unique_opponents_score);
            $stat_score[$cat_index] = $unique_opponents_score;
            $stat_display[$cat_index] = $unique_opponents;
            break;
            case "OpponentsELO":
            $cat_header = '<b title="Opponents Average ELO">Opp. ELO</b>';
            $min = min($opponentsELO_score);
            $max = max($opponentsELO_score);
            $stat_score[$cat_index] = $opponentsELO_score;
            $stat_display[$cat_index] = $opponentsELO;
            break;
            case "Streaks":
            $cat_header = '<b title="Current|Best|Worst Streaks">Streaks</b>';
            $min = min($streaks_score);
            $max = max($streaks_score);
            $stat_score[$cat_index] = $streaks_score;
            $stat_display[$cat_index] = $streaks;
            break;
            case "Score":
            $cat_header = '<b title="Score Average">Score</b>';
            $min = min($score_score);
            $max = max($score_score);
            $stat_score[$cat_index] = $score_score;
            $stat_display[$cat_index] = $score;
            break;
            case "ScoreAgainst":
            $cat_header = '<b title="Opponents Score Average">Opp. Score</b>';
            $min = min($oppscore_score);
            $max = max($oppscore_score);
            $stat_score[$cat_index] = $oppscore_score;
            $stat_display[$cat_index] = $oppscore;
            break;
            case "ScoreDiff":
            $cat_header = '<b title="Score Difference Average">Score Diff.</b>';
            $min = min($scorediff_score);
            $max = max($scorediff_score);
            $stat_score[$cat_index] = $scorediff_score;
            $stat_display[$cat_index] = $scorediff;
            break;
            case "Points":
            $cat_header = '<b title="Points Average">Points</b>';
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
                $cat_header .= '<br /><div class="smalltext">['.number_format ($cat_maxpoints,2).' max]</div>';

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

if ($ehide_ratings_column == FALSE)
$stats[0][] = '<b title="Rating">Rating</b><br /><div class="smalltext">['.number_format ($rating_max,2).' max]</div>';

for ($category=0; $category<$numDisplayedCategories; $category++)
{
    $stats[0][] = $stat_cat_header[$category];
}

$player_index=0;
$final_score = array();
for($player=0; $player<$numPlayers; $player++)
{
    $OverallScore[$player]=0;
    if ($games_played[$player] >= $emingames)
    {
        for ($category=0; $category<$numDisplayedCategories; $category++)
        {
            if ($stat_InfoOnly[$category] == FALSE)
            {
                $final_score[$category][$player] = $stat_a[$category] * $stat_score[$category][$player_index] + $stat_b[$category];
                $OverallScore[$player]+=$final_score[$category][$player];
            }
        }
        $player_index++;
    }
    else
    {
        for ($category=0; $category<$numDisplayedCategories; $category++)
        {
            $final_score[$category][$player] = 0;
        }
    }

    $q_update = "UPDATE ".TBL_PLAYERS." SET OverallScore = $OverallScore[$player] WHERE (PlayerID = '$id[$player]') AND (Event = '$event_id')";
    $result_update = $sql->db_Query($q_update);
}
// Build results table
//--------------------
$q_Players = "SELECT *"
." FROM ".TBL_PLAYERS
." WHERE (Event = '$event_id')"
." ORDER BY ".TBL_PLAYERS.".OverallScore DESC, ".TBL_PLAYERS.".GamesPlayed DESC, ".TBL_PLAYERS.".ELORanking DESC";
$result_Players = $sql->db_Query($q_Players);
$ranknumber = 1;
for($player=0; $player<$numPlayers; $player++)
{
    $pid = mysql_result($result_Players,$player, TBL_PLAYERS.".PlayerID");
    $puid = mysql_result($result_Players,$player, TBL_PLAYERS.".User");
    $prank = mysql_result($result_Players,$player, TBL_PLAYERS.".Rank");
    $prankdelta = mysql_result($result_Players,$player, TBL_PLAYERS.".RankDelta");
    $pstreak = mysql_result($result_Players,$player, TBL_PLAYERS.".Streak");

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
        $q_update = "UPDATE ".TBL_PLAYERS." SET Rank = $rank WHERE (PlayerID = '$pid') AND (Event = '$event_id')";
        $result_update = $sql->db_Query($q_update);

        $new_rankdelta = $prank - $rank;
        if ($new_rankdelta != 0)
        {
            $prankdelta += $new_rankdelta;
            $q_update = "UPDATE ".TBL_PLAYERS." SET RankDelta = $prankdelta WHERE (PlayerID = '$pid') AND (Event = '$event_id')";
            $result_update = $sql->db_Query($q_update);
        }

        if (($new_rankdelta != 0)&&($rank==1))
        {
            // Award: player took 1st place
            $q_Awards = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
            VALUES ($pid,'PlayerTookFirstPlace',$time)";
            $result_Awards = $sql->db_Query($q_Awards);
        }
        if (($new_rankdelta != 0)&&(($prank>10)||($prank==0))&&($rank<=10))
        {
            // Award: player enters top 10
            $q_Awards = "INSERT INTO ".TBL_AWARDS."(Player,Type,timestamp)
            VALUES ($pid,'PlayerInTopTen',$time)";
            $result_Awards = $sql->db_Query($q_Awards);
        }

        $q_Awards = "SELECT ".TBL_AWARDS.".*, "
        .TBL_PLAYERS.".*"
        ." FROM ".TBL_AWARDS.", "
        .TBL_PLAYERS
        ." WHERE (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
        ." AND (".TBL_PLAYERS.".PlayerID = '$pid')"
        ." ORDER BY ".TBL_AWARDS.".timestamp DESC";
        $result_Awards = $sql->db_Query($q_Awards);
        $numAwards = mysql_numrows($result_Awards);
        if ($numAwards > 0)
        {
            $paward  = mysql_result($result_Awards,0, TBL_AWARDS.".AwardID");
            $pawardType  = mysql_result($result_Awards,0, TBL_AWARDS.".Type");
        }

        if ($rank==1)
        {
            $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/awards/award_star_gold_3.png" alt="1st place" title="1st place" style="vertical-align:middle"/>';
        }
        else if (($rank<=10)&&(($rank+$prankdelta>min(10,$nbrplayers))||($rank+$prankdelta==0)))
        {
            $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/awards/award_star_bronze_3.png" alt="top 10" title="top 10" style="vertical-align:middle"/>';
        }
        else if (($numAwards>0)&&($pawardType!='PlayerTookFirstPlace')&&($pawardType!='PlayerInTopTen')&&($pstreak>=5))
        {
            switch ($pawardType)
            {
                case 'PlayerStreak5':
                if ($pstreak>=5)
                {
                    $award = " won 5 games in a row";
                    $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/awards/medal_bronze_3.png" alt="Streak 5" title="5 wins in a row" style="vertical-align:middle"/>';
                }
                break;
                case 'PlayerStreak10':
                if ($pstreak>=10)
                {
                    $award = " won 10 games in a row";
                    $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/awards/medal_silver_3.png" alt="Streak 10" title="10 wins in a row" style="vertical-align:middle"/>';
                }
                break;
                case 'PlayerStreak25':
                if ($pstreak>=25)
                {
                    $award = " won 25 games in a row";
                    $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/awards/medal_gold_3.png" alt="Streak 25" title="25 wins in a row" style="vertical-align:middle"/>';
                }
                break;
            }
        }
        else if ($prankdelta>0)
        {
            $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/arrow_up.gif" alt="+'.$prankdelta.'" title="+'.$prankdelta.'" style="vertical-align:middle"/>';
        }
        else if (($prankdelta<0)&&($rank+$prankdelta!=0))
        {
            $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/arrow_down.gif" alt="'.$prankdelta.'" title="'.$prankdelta.'" style="vertical-align:middle"/>';
        }
        else if ($rank+$prankdelta==0)
        {
            $prank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/arrow_up.gif" alt="Up" title="From unranked" style="vertical-align:middle"/>';
        }
    }

    $pclan = '';
    $pclantag = '';
    if ($etype == "Team Ladder")
    {
        $q_Clans = "SELECT ".TBL_CLANS.".*, "
        .TBL_DIVISIONS.".*, "
        .TBL_TEAMS.".* "
        ." FROM ".TBL_CLANS.", "
        .TBL_DIVISIONS.", "
        .TBL_TEAMS
        ." WHERE (".TBL_TEAMS.".TeamID = '$team[$index]')"
        ." AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
        ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
        $result_Clans  = $sql->db_Query($q_Clans );
        $numClans = mysql_numrows($result_Clans );
        if ($numClans == 1)
        {
            $pclan  = mysql_result($result_Clans ,0, TBL_CLANS.".Name");
            $pclantag  = mysql_result($result_Clans ,0, TBL_CLANS.".Tag")."_";
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


    $image = "";
    if ($pref['eb_avatar_enable_playersstandings'] == 1)
    {
        if($avatar[$index])
        {
            $image = '<img src="'.avatar($avatar[$index]).'" alt="" '.imageResize(avatar($avatar[$index]), $pref['eb_max_avatar_size']).' style="vertical-align:middle"/>';
        } else if ($pref['eb_avatar_default_image'] != ''){
            $image = '<img src="'.getAvatar($pref['eb_avatar_default_image']).'" alt="" style="vertical-align:middle" width="'.$pref['eb_max_avatar_size'].'"/>';
        }
    }

    $stats_row[] = $image.'&nbsp;<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$uid[$index].'"><b>'.$pclantag.$name[$index].'</b></a>';

    if ($ehide_ratings_column == FALSE)
    $stats_row[] = number_format ($OverallScore[$index],2);

    for ($category=0; $category<$numDisplayedCategories; $category++)
    {
        if ($stat_InfoOnly[$category] == TRUE)
        {
            $stats_row[] = $stat_display[$category][$index];
        }
        else
        {
            $stats_row[] = $stat_display[$category][$index].'<br /><div class="smalltext">['.number_format ($final_score[$category][$index],2).']</div>';
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

if ($fp == FALSE) {
    // handle error
    $text .= "Could not write to cache directory, please verify cache direcory is writable";
}

fputs($fp, $OUTPUT);
fclose($fp);

/*
$stats = unserialize(implode('',file($file)));
foreach ($stats as $id=>$row)
{
print $row['category_name']."<br />";
}
*/

?>
