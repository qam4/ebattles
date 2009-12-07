<?php
/**
* updateteamstats.php
*
*/

function updateTeamStats($event_id, $time, $serialize = TRUE)
{
    global $sql;

    $file_team = 'cache/sql_cache_event_team_'.$event_id.'.txt';

    /* Event Info */
    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
    $result = $sql->db_Query($q);
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
    $emingames = mysql_result($result,0 , TBL_EVENTS.".nbr_games_to_rank");
    $eminteamgames = mysql_result($result,0 , TBL_EVENTS.".nbr_team_games_to_rank");
    $ehide_ratings_column = mysql_result($result,0 , TBL_EVENTS.".hide_ratings_column");
    
    //Update Teams stats
    $q_Teams = "SELECT ".TBL_TEAMS.".*, "
    .TBL_DIVISIONS.".*, "
    .TBL_CLANS.".*"
    ." FROM ".TBL_TEAMS.", "
    .TBL_DIVISIONS.", "
    .TBL_CLANS
    ." WHERE (".TBL_TEAMS.".Event = '$event_id')"
    ." AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
    ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";

    $result_Teams = $sql->db_Query($q_Teams);
    $numTeams = mysql_numrows($result_Teams);
    $teams_rated = 0;

    $id = array();
    $clan = array();
    $clantag = array();
    $name = array();
    $nbr_players = array();
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

    for($team=0; $team<$numTeams; $team++)
    {
        $tid = mysql_result($result_Teams,$team, TBL_TEAMS.".TeamID");
        $tname = mysql_result($result_Teams,$team, TBL_CLANS.".Name");
        $tclan = mysql_result($result_Teams,$team, TBL_CLANS.".ClanID");
        $tclantag = mysql_result($result_Teams,$team, TBL_CLANS.".Tag");

        // Find all players for that event and that team
        $q_Players = "SELECT * "
        ." FROM ".TBL_PLAYERS." "
        ." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
        ." AND (".TBL_PLAYERS.".Team = '$tid')";
        $result_Players = $sql->db_Query($q_Players);
        $tPlayers = mysql_numrows($result_Players);
        $tnbrplayers_rated = 0;

        $tOverallScore = 0;
        $tELO = 0;
        $tTS_mu = 0;
        $tTS_sigma2 = 0;
        $twin = 0;
        $tloss = 0;
        $tdraw = 0;
        $tgames_played = 0;
        $tscore = 0;
        $toppscore = 0;
        $tpoints = 0;
        $tunique_opponents = 0;
        $topponentsELO = 0;
        $topponents = 0;

        $min_team_games = $eminteamgames;

        if ($tPlayers>0)
        {
            for($player=0; $player<$tPlayers; $player++)
            {
                // For each player
                $pid = mysql_result($result_Players,$player, TBL_PLAYERS.".PlayerID");
                $puid = mysql_result($result_Players,$player, TBL_PLAYERS.".User");
                $pgames_played = mysql_result($result_Players,$player, TBL_PLAYERS.".GamesPlayed");
                $pELO = mysql_result($result_Players,$player, TBL_PLAYERS.".ELORanking");
                $pTS_mu = mysql_result($result_Players,$player, TBL_PLAYERS.".TS_mu");
                $pTS_sigma = mysql_result($result_Players,$player, TBL_PLAYERS.".TS_sigma");
                $pSkill = $pTS_mu - 3*$pTS_sigma;
                $pwin = mysql_result($result_Players,$player, TBL_PLAYERS.".Win");
                $pdraw = mysql_result($result_Players,$player, TBL_PLAYERS.".Draw");
                $ploss = mysql_result($result_Players,$player, TBL_PLAYERS.".Loss");
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

                $tTS_mu += $pTS_mu;
                $tTS_sigma2 += pow($pTS_sigma,2);
                $twin += $pwin;
                $tdraw += $pdraw;
                $tloss += $ploss;
                $tscore += $pscore;
                $toppscore += $poppscore;
                $tpoints += $ppoints;
                $tgames_played += $pgames_played;
                $tunique_opponents += $punique_opponents;
                $topponentsELO += $popponentsELO;
                $topponents += $popponents;
                $twindrawloss = $twin."/".$tdraw."/".$tloss;
                $twinloss = $twin."/".$tloss;
                $tvictory_ratio = ($tloss>0) ? ($twin/$tloss) : $twin; //fm --> draws???
                $tvictory_percent = ($tgames_played>0) ? ((100 * $twin)/($twin+$tdraw+$tloss)) : 0;

                if ($pgames_played>=$emingames)
                {
                    $tnbrplayers_rated++;
                    $tELO += mysql_result($result_Players,$player, TBL_PLAYERS.".ELORanking");
                }
            }

            if ($topponents !=0)
            {
                $topponentsELO /= $topponents;
            }

            if ($tnbrplayers_rated>0)
            {
                $tELO /= $tnbrplayers_rated;
                $tTS_mu /= $tnbrplayers_rated;
                $tTS_sigma = sqrt($tTS_sigma2);

                $tSkill = $tTS_mu - 3*$tTS_sigma;
            }
        }
        // For display
        $id[]  = $tid;
        $name[]  = $tname;
        $clan[]  = $tclan;
        $clantag[]  = $tclantag;
        $nbr_players[]  = $tPlayers;
        $games_played[] = $tgames_played;
        $ELO[] = $tELO;
        $Skill[] = max(0,number_format ($tSkill,0));
        $win[] = $twin;
        $loss[] = $tloss;
        $draw[] = $tdraw;
        $windrawloss[] = $twindrawloss;
        $victory_ratio[] = $twinloss;
        $victory_percent[] = number_format ($tvictory_percent,2)." %";
        $unique_opponents[] = $tunique_opponents;
        $opponentsELO[] = floor($topponentsELO);
        $score[] = ($tgames_played>0) ? number_format($tscore/$tgames_played,2) : 0;
        $oppscore[] = ($tgames_played>0) ? number_format($toppscore/$tgames_played,2) : 0;
        $scorediff[] = ($tgames_played>0) ? number_format(($tscore - $toppscore)/$tgames_played,2) : 0;
        $points[] = $tpoints;

        // Actual score (not for display)
        if ($tgames_played >= $eminteamgames)
        {
            $games_played_score[] = $tgames_played;
            $ELO_score[] = $tELO;
            $Skill_score[] = $tSkill;
            $win_score[] = $twin;
            $loss_score[] = $tloss;
            $draw_score[] = $tdraw;
            $windrawloss_score[] = $twin - $tloss; //fm - ???
            $victory_ratio_score[] = $tvictory_ratio;
            $victory_percent_score[] = $tvictory_percent;
            $unique_opponents_score[] = $tunique_opponents;
            $opponentsELO_score[] = $topponentsELO;
            $score_score[] = ($tgames_played>0) ? $tscore/$tgames_played : 0;
            $oppscore_score[] = ($tgames_played>0) ? -$toppscore/$tgames_played : 0;
            $scorediff_score[] = ($tgames_played>0) ? ($tscore - $toppscore)/$tgames_played : 0;
            $points_score[] = ($tgames_played>0) ? $tpoints/$tgames_played : 0;

            $teams_rated++;
        }

        $q_update = "UPDATE ".TBL_TEAMS
        ." SET ELORanking = $tELO,"
        ."     TS_mu = $tTS_mu,"
        ."     TS_sigma = $tTS_sigma,"
        ."     Loss = $tloss,"
        ."     Win = $twin,"
        ."     Draw = $tdraw,"
        ."     Score = $tscore,"
        ."     ScoreAgainst = $toppscore,"
        ."     Points = $tpoints"
        ." WHERE (TeamID = '$id[$team]')"
        ."   AND (Event = '$event_id')";
        $result_update = $sql->db_Query($q_update);
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
                $display_cat = 0;
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
    "0"=>array("header","<b>Rank</b>","<b>Team</b>","<b>Players</b>")
    );

    if ($ehide_ratings_column == FALSE)
    $stats[0][] = '<b title="Rating">Rating</b><br /><div class="smalltext">['.number_format ($rating_max,2).' max]</div>';

    for ($category=0; $category<$numDisplayedCategories; $category++)
    {
        $stats[0][] = $stat_cat_header[$category];
    }

    $player_index=0;
    $final_score = array();
    for($team=0; $team<$numTeams; $team++)
    {
        $OverallScore[$team]=0;
        if ($games_played[$team] >= $emingames)
        {
            for ($category=0; $category<$numDisplayedCategories; $category++)
            {
                if ($stat_InfoOnly[$category] == FALSE)
                {
                    $final_score[$category][$team] = $stat_a[$category] * $stat_score[$category][$player_index] + $stat_b[$category];
                    $OverallScore[$team]+=$final_score[$category][$team];
                }
            }
            $player_index++;
        }
        else
        {
            for ($category=0; $category<$numDisplayedCategories; $category++)
            {
                $final_score[$category][$team] = 0;
            }
        }

        $q_update = "UPDATE ".TBL_TEAMS
        ." SET OverallScore = $OverallScore[$team]"
        ." WHERE (TeamID = '$id[$team]')"
        ."   AND (Event = '$event_id')";
        $result_update = $sql->db_Query($q_update);
    }
    // Build results table
    //--------------------
    $q_Teams = "SELECT *"
    ." FROM ".TBL_TEAMS
    ." WHERE (Event = '$event_id')"
    ." ORDER BY ".TBL_TEAMS.".OverallScore DESC, ".TBL_TEAMS.".ELORanking DESC";
    $result_Teams = $sql->db_Query($q_Teams);
    $ranknumber = 1;
    for($team=0; $team<$numTeams; $team++)
    {
        $tid = mysql_result($result_Teams,$team, TBL_TEAMS.".TeamID");
        $trank = mysql_result($result_Teams,$team, TBL_TEAMS.".Rank");
        $trankdelta = mysql_result($result_Teams,$team, TBL_TEAMS.".RankDelta");

        // Find index of team
        $index = array_search($tid,$id);

        $trank_side_image = "";
        if($OverallScore[$index]==0)
        {
            $rank = '<span title="Not ranked">-</span>';
            $trankdelta_string = "";
        }
        else
        {
            $rank = $ranknumber;
            $ranknumber++; // increases $ranknumber by 1
            $q_update = "UPDATE ".TBL_TEAMS." SET Rank = $rank WHERE (TeamID = '$tid') AND (Event = '$event_id')";
            $result_update = $sql->db_Query($q_update);

            $new_rankdelta = $trank - $rank;
            if ($new_rankdelta != 0)
            {
                $trankdelta += $new_rankdelta;
                $q_update = "UPDATE ".TBL_TEAMS." SET RankDelta = $trankdelta WHERE (TeamID = '$tid') AND (Event = '$event_id')";
                $result_update = $sql->db_Query($q_update);
            }

            if ($trankdelta>0)
            {
                $trank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/arrow_up.gif" alt="+'.$trankdelta.'" title="+'.$trankdelta.'" style="vertical-align:middle"/>';
            }
            else if (($trankdelta<0)&&($rank+$trankdelta!=0))
            {
                $trank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/arrow_down.gif" alt="'.$trankdelta.'" title="'.$trankdelta.'" style="vertical-align:middle"/>';
            }
            else if ($rank+$trankdelta==0)
            {
                $trank_side_image = '<img src="'.e_PLUGIN.'ebattles/images/arrow_up.gif" alt="Up" title="From unranked" style="vertical-align:middle"/>';
            }
        }

        $q_Players = "SELECT *"
        ." FROM ".TBL_PLAYERS
        ." WHERE (".TBL_PLAYERS.".Team = '$tid')"
        ." AND (".TBL_PLAYERS.".User = ".USERID.")";
        $result_Players = $sql->db_Query($q_Players);
        $num_rows_2 = mysql_numrows($result_Players);
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

        $stats_row[] = "<b>$rank</b> $trank_side_image";
        $stats_row[] = '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$clan[$index].'"><b>'.$name[$index].' ('.$clantag[$index].')</b></a>';
        $stats_row[] = "$nbr_players[$index]";

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
    html_show_table($stats, $numTeams+1, 7);
    echo "<br />";
    */

    if ($serialize)
    {
        // Serialize results array
        $OUTPUT = serialize($stats);
        $fp = fopen($file_team,"w"); // open file with Write permission

        if ($fp == FALSE) {
            // handle error
            $error .= "Could not write to cache directory, please verify cache direcory is writable";
        }

        fputs($fp, $OUTPUT);
        fclose($fp);

        /*
        $stats = unserialize(implode('',file($file_team)));
        foreach ($stats as $id=>$row)
        {
        print $row['category_name']."<br />";
        }
        */
    }
}
?>
