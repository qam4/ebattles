<?php
/**
* updatestats.php
*
*/

$file = 'cache/sql_cache_event_'.$event_id.'.txt';

$q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
." FROM ".TBL_STATSCATEGORIES
." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')";

$result_1 = $sql->db_Query($q_1);
$num_rows = mysql_numrows($result_1);

$ELO_minpoints = 0;
$ELO_maxpoints = 0;
$Skill_minpoints = 0;
$Skill_maxpoints = 0;
$games_played_minpoints = 0;
$games_played_maxpoints = 0;
$streaks_minpoints = 0;
$streaks_maxpoints = 0;
$victory_ratio_minpoints = 0;
$victory_ratio_maxpoints = 0;
$victory_percent_minpoints = 0;
$victory_percent_maxpoints = 0;
$unique_opponents_minpoints = 0;
$unique_opponents_maxpoints = 0;
$opponentsELO_minpoints = 0;
$opponentsELO_maxpoints = 0;
$rating_max= 0;

for($i=0; $i<$num_rows; $i++)
{
    $cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");
    $cat_min = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMinValue");
    $cat_max = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMaxValue");

    if ($cat_max > 0)
    {
        if ($cat_name == "ELO")
        {
            $ELO_minpoints = $cat_min;
            $ELO_maxpoints = $cat_max;
            $rating_max += $ELO_maxpoints;
        }
        if ($cat_name == "Skill")
        {
            $Skill_minpoints = $cat_min;
            $Skill_maxpoints = $cat_max;
            $rating_max += $Skill_maxpoints;
        }
        if ($cat_name == "GamesPlayed")
        {
            $games_played_minpoints = $cat_min;
            $games_played_maxpoints = $cat_max;
            $rating_max += $games_played_maxpoints;
        }
        if ($cat_name == "VictoryRatio")
        {
            $victory_ratio_minpoints = $cat_min;
            $victory_ratio_maxpoints = $cat_max;
            $rating_max += $victory_ratio_maxpoints;
        }
        if ($cat_name == "VictoryPercent")
        {
            $victory_percent_minpoints = $cat_min;
            $victory_percent_maxpoints = $cat_max;
            $rating_max += $victory_percent_maxpoints;
        }
        if ($cat_name == "UniqueOpponents")
        {
            $unique_opponents_minpoints = $cat_min;
            $unique_opponents_maxpoints = $cat_max;
            $rating_max += $unique_opponents_maxpoints;
        }
        if ($cat_name == "OpponentsELO")
        {
            $opponentsELO_minpoints = $cat_min;
            $opponentsELO_maxpoints = $cat_max;
            $rating_max += $opponentsELO_maxpoints;
        }
        if ($cat_name == "Streaks")
        {
            $streaks_minpoints = $cat_min;
            $streaks_maxpoints = $cat_max;
            $rating_max += $streaks_maxpoints;
        }
    }
}

$stats = array
(
"0"=>array
(
"header",
"<b>Rank</b>",
"<b>Player</b>",
)
);

$stats[0][] = "<b title=\"Rating\">Rating</b><br /><div class='smalltext'>[".number_format ($rating_max,2)." max]</div>";

if ($ELO_maxpoints > 0)
{
    $stats[0][] = "<b title=\"ELO\">ELO</b><br /><div class='smalltext'>[".number_format ($ELO_maxpoints,2)." max]</div>";
}
if ($Skill_maxpoints > 0)
{
    $stats[0][] = "<b title=\"TrueSkill(TM)\">Skill</b><br /><div class='smalltext'>[".number_format ($Skill_maxpoints,2)." max]</div>";
}
if ($games_played_maxpoints > 0)
{
    $stats[0][] = "<b title=\"Games\">Games</b><br /><div class='smalltext'>[".number_format ($games_played_maxpoints,2)." max]</div>";
}
if ($victory_ratio_maxpoints > 0)
{
    $stats[0][] = "<b title=\"Win/Loss ratio\">W/L</b><br /><div class='smalltext'><div class='smalltext'>[".number_format ($victory_ratio_maxpoints,2)." max]</div>";
}
if ($victory_percent_maxpoints > 0)
{
    $stats[0][] = "<b title=\"Wins percentage\">W%</b><br /><div class='smalltext'>[".number_format ($victory_percent_maxpoints,2)." max]</div>";
}
if ($unique_opponents_maxpoints > 0)
{
    $stats[0][] = "<b title=\"Unique Opponents\">Opponents</b><br /><div class='smalltext'>[".number_format ($unique_opponents_maxpoints,2)." max]</div>";
}
if ($opponentsELO_maxpoints > 0)
{
    $stats[0][] = "<b title=\"Opponents Average ELO\">Opp. ELO</b><br /><div class='smalltext'>[".number_format ($opponentsELO_maxpoints,2)." max]</div>";
}
if ($streaks_maxpoints > 0)
{
    $stats[0][] = "<b title=\"Current|Best|Worst Streaks\">Streaks</b><br /><div class='smalltext'>[".number_format ($streaks_maxpoints,2)." max]</div>";
}

// Update Overall Score
$q_1 = "SELECT ".TBL_PLAYERS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_PLAYERS.", "
.TBL_USERS
." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";

$result_1 = $sql->db_Query($q_1);
$num_rows = mysql_numrows($result_1);

$players_rated = 0;
for($i=0; $i<$num_rows; $i++)
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
    $ploss = mysql_result($result_1,$i, TBL_PLAYERS.".Loss");
    $pstreak = mysql_result($result_1,$i, TBL_PLAYERS.".Streak");
    $pstreak_worst = mysql_result($result_1,$i, TBL_PLAYERS.".Streak_Worst");
    $pstreak_best = mysql_result($result_1,$i, TBL_PLAYERS.".Streak_Best");
    $pstreak_display = $pstreak." | ".$pstreak_best." | ".$pstreak_worst;
    $pstreak_score = $pstreak_best; //max(0,$pstreak_best + $pstreak_worst); //fmarc- TBD
    $pwinloss = $pwin."/".$ploss;
    $pvictory_ratio = ($ploss>0) ? ($pwin/$ploss) : $pwin;
    $pvictory_percent = ($pgames_played>0) ? ((100 * $pwin)/($pwin+$ploss)) : 0;

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
    ." AND (".TBL_MATCHS.".Event = '$event_id')"
    ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
    ." AND (".TBL_PLAYERS.".PlayerID = '$pid')";

    $result_2 = $sql->db_Query($q_2);
    $num_rows_2 = mysql_numrows($result_2);

    $players = array();
    if ($num_rows_2>0)
    {
        for($j=0; $j<$num_rows_2; $j++)
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
            $num_rows_3 = mysql_numrows($result_3);
            for($k=0; $k<$num_rows_3; $k++)
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
    $Skill[] = $pSkill;
    $win[] = $pwin;
    $loss[] = $ploss;
    $streaks[] = $pstreak_score;
    $streaks_display[] = $pstreak_display;
    $winloss[] = $pwinloss;
    $victory_ratio[] = $pvictory_ratio;
    $victory_percent[] = $pvictory_percent;
    $unique_opponents[] = $punique_opponents;
    $opponentsELO[] = $popponentsELO;

    // Actual score (not for display)
    if ($pgames_played >= $emingames)
    {
        $games_played_score[] = $pgames_played;
        $ELO_score[] = $pELO;
        $Skill_score[] = $pSkill;
        $win_score[] = $pwin;
        $loss_score[] = $ploss;
        $winloss_score[] = $pwin - $ploss;
        $victory_ratio_score[] = $pvictory_ratio;
        $victory_percent_score[] = $pvictory_percent;
        $unique_opponents_score[] = $punique_opponents;
        $opponentsELO_score[] = $popponentsELO;
        $streaks_score[] = $pstreak_best; //max(0,$pstreak_best + $pstreak_worst); //fmarc- TBD

        $players_rated++;
    }
}

if ($players_rated>0)
{
    $games_played_min = 0; //min($games_played_score);
    $ELO_min = min($ELO_score);
    $Skill_min = min($Skill_score);
    $victory_ratio_min = 0; //min($victory_ratio_score);
    $victory_percent_min = 0; //min($victory_percent_score);
    $unique_opponents_min = 0; //min($unique_opponents_score);
    $opponentsELO_min = min($opponentsELO_score);
    $streaks_min = min($streaks_score);

    $games_played_max = max($games_played);
    $ELO_max = max($ELO_score);
    $Skill_max = max($Skill_score);
    $victory_ratio_max = max($victory_ratio_score);
    $victory_percent_max = max($victory_percent_score);
    $unique_opponents_max = max($unique_opponents_score);
    $opponentsELO_max = max($opponentsELO_score);
    $streaks_max = max($streaks_score);

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
    if ($Skill_max==$Skill_min)
    {
        $Skill_a = 0;
        $Skill_b = $Skill_maxpoints;
    }
    else
    {
        $Skill_a = ($Skill_maxpoints-$Skill_minpoints) / ($Skill_max-$Skill_min);
        $Skill_b = $Skill_minpoints - $Skill_a * $Skill_min;
    }
    if ($games_played_max==$games_played_min)
    {
        $games_played_a = 0;
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
    if ($streaks_max==$streaks_min)
    {
        $streaks_a = 0;
        $streaks_b = $streaks_maxpoints;
    }
    else
    {
        $streaks_a = ($streaks_maxpoints-$streaks_minpoints) / ($streaks_max-$streaks_min);
        $streaks_b = $streaks_minpoints - $streaks_a * $streaks_min;
    }
}

for($i=0; $i<$num_rows; $i++)
{
    if ($games_played[$i] >= $emingames)
    {
        $ELO_final_score[$i] = $ELO_a * $ELO[$i] + $ELO_b;
        $Skill_final_score[$i] = $Skill_a * $Skill[$i] + $Skill_b;
        $games_played_final_score[$i] = $games_played_a * $games_played[$i] + $games_played_b;
        $victory_ratio_final_score[$i] = $victory_ratio_a * $victory_ratio[$i] + $victory_ratio_b;
        $victory_percent_final_score[$i] = $victory_percent_a * $victory_percent[$i] + $victory_percent_b;
        $unique_opponents_final_score[$i] = $unique_opponents_a * $unique_opponents[$i] + $unique_opponents_b;
        $opponentsELO_final_score[$i] = $opponentsELO_a * $opponentsELO[$i] + $opponentsELO_b;
        $streaks_final_score[$i] = $streaks_a * $streaks_score[$i] + $streaks_b;
    }
    else
    {
        $ELO_final_score[$i] = 0;
        $Skill_final_score[$i] = 0;
        $games_played_final_score[$i] = 0;
        $victory_ratio_final_score[$i] = 0;
        $victory_percent_final_score[$i] = 0;
        $unique_opponents_final_score[$i] = 0;
        $opponentsELO_final_score[$i] = 0;
        $streaks_final_score[$i] = 0;
    }

    $OverallScore[$i] = $ELO_final_score[$i] + $Skill_final_score[$i] + $games_played_final_score[$i] + $victory_ratio_final_score[$i] + $victory_percent_final_score[$i] + $unique_opponents_final_score[$i] + $opponentsELO_final_score[$i] + $streaks_final_score[$i];

    $q_3 = "UPDATE ".TBL_PLAYERS." SET OverallScore = $OverallScore[$i] WHERE (PlayerID = '$id[$i]') AND (Event = '$event_id')";
    $result_3 = $sql->db_Query($q_3);
}

// Calculate Rank
//----------------
$q_1 = "SELECT *"
." FROM ".TBL_PLAYERS
." WHERE (Event = '$event_id')"
." ORDER BY ".TBL_PLAYERS.".OverallScore DESC, ".TBL_PLAYERS.".GamesPlayed DESC, ".TBL_PLAYERS.".ELORanking DESC";

$result_1 = $sql->db_Query($q_1);
$num_rows = mysql_numrows($result_1);

$ranknumber = 1;
for($i=0; $i<$num_rows; $i++)
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
        $q_2 = "UPDATE ".TBL_PLAYERS." SET Rank = $ranknumber WHERE (PlayerID = '$pid') AND (Event = '$event_id')";
        $result_2 = $sql->db_Query($q_2);

        $new_rankdelta = $prank - $ranknumber;
        if (($new_rankdelta != 0)&&($prank!=0))
        {
            $q_2 = "UPDATE ".TBL_PLAYERS." SET RankDelta = $new_rankdelta WHERE (PlayerID = '$pid') AND (Event = '$event_id')";
            $result_2 = $sql->db_Query($q_2);
            $prankdelta = $new_rankdelta;
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

        //echo "dbg: $name[$index]: $rank, $prankdelta, $numAwards: $pawardType<br>";
        if ($rank==1)
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/award_star_gold_3.png\" alt=\"1st place\" title=\"1st place\"></img>";
        }
        else if (($rank<=10)&&(($rank+$prankdelta>min(10,$nbrplayers))||($prankdelta==0)))
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/award_star_bronze_3.png\" alt=\"top 10\" title=\"top 10\"></img>";
        }
        else if (($numAwards>0)&&($pawardType!='PlayerTookFirstPlace')&&($pawardType!='PlayerInTopTen')&&($pstreak>=5))
        {
            //echo "dbg: award: $pawardType";
            switch ($pawardType) {
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
        else if ($prankdelta<0)
        {
            $prank_side_image = "<img src=\"".e_PLUGIN."ebattles/images/arrow_down.gif\" alt=\"$prankdelta\" title=\"$prankdelta\"></img>";
        }
        else if ($prankdelta==0)
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
        $num_rows_2 = mysql_numrows($result_2);
        if ($num_rows_2 == 1)
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
    if ($ELO_maxpoints > 0)
    {
        $stats_row[] = "$ELO[$index]<br />[".number_format ($ELO_final_score[$index],2)."]";
    }
    if ($Skill_maxpoints > 0)
    {
        $stats_row[] = number_format ($Skill[$index],2)."<br />[".number_format ($Skill_final_score[$index],2)."]";
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
    if ($streaks_maxpoints > 0)
    {
        $stats_row[] = $streaks_display[$index]."<br />[".number_format ($streaks_final_score[$index],2)."]";
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
