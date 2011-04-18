<?php
/**
* eb_activity_menu.php
*
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/match.php");

$ladder_id = $_GET['LadderID'];

$ebattles_title = $pref['eb_activity_menuheading'];
$text = displayRecentActivity($ladder_id);

$ns->tablerender($ebattles_title,$text);

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayRecentActivity - Displays Recent Activity
*/
function displayRecentActivity($ladder_id){
	global $sql;
	global $time;
	global $pref;

	$ladders = array();
	$nbr_ladders = 0;

	if ($ladder_id != '')
	{
		$LadderID_match = " AND (".TBL_MATCHS.".Ladder = '$ladder_id')";
		$LadderID_award = " AND (".TBL_LADDERS.".LadderID = '$ladder_id')";
	}

	// Add recent games
	$rowsPerPage = $pref['eb_activity_number_of_items'];
	/* Stats/Results */
	$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
	." FROM ".TBL_MATCHS.", "
	.TBL_SCORES
	." WHERE (".TBL_MATCHS.".Status = 'active')"
	." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
	.$LadderID_match
	." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
	." LIMIT 0, $rowsPerPage";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	if ($num_rows>0)
	{
		/* Display table contents */
		for($i=0; $i<$num_rows; $i++)
		{
			$match_id  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
			$match = new Match($match_id);
			$ladders[$nbr_ladders][0] = $match->getField('TimeReported');
			$ladders[$nbr_ladders][1] = $match->displayMatchInfo();
			$nbr_ladders ++;
		}
	}

	// Add Awards ladders
	$q = "SELECT ".TBL_AWARDS.".*, "
	.TBL_PLAYERS.".*, "
	.TBL_USERS.".*, "
	.TBL_LADDERS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_AWARDS.", "
	.TBL_PLAYERS.", "
	.TBL_USERS.", "
	.TBL_LADDERS.", "
	.TBL_GAMES
    ." WHERE (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
	." AND     (".TBL_PLAYERS.".User = ".TBL_USERS.".user_id)"
    ." AND (".TBL_PLAYERS.".Ladder = ".TBL_LADDERS.".LadderID)"
	." AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	.$LadderID_award
	." ORDER BY ".TBL_AWARDS.".timestamp DESC"
	." LIMIT 0, $rowsPerPage";

	$result = $sql->db_Query($q);
	$numAwards = mysql_numrows($result);
	
	if ($numAwards>0)
	{
		/* Display table contents */
		for($i=0; $i < $numAwards; $i++)
		{
			$aID  = mysql_result($result,$i, TBL_AWARDS.".AwardID");
			$aUser  = mysql_result($result,$i, TBL_USERS.".user_id");
			$aUserNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
			$aLaddergame = mysql_result($result,$i , TBL_GAMES.".Name");
			$aLaddergameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
			$aType  = mysql_result($result,$i, TBL_AWARDS.".Type");
			$aTime  = mysql_result($result,$i, TBL_AWARDS.".timestamp");
			$aTime_local = $aTime + TIMEOFFSET;
			$date = date("d M Y, h:i A",$aTime_local);
			$aLadderID  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$aLadderName  = mysql_result($result,$i, TBL_LADDERS.".Name");

			switch ($aType) {
				case 'PlayerTookFirstPlace':
				$award = EB_AWARD_L2;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_gold_3.png").' alt="'.EB_AWARD_L3.'" title="'.EB_AWARD_L3.'"/> ';
				break;
				case 'PlayerInTopTen':
				$award = EB_AWARD_L4;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_bronze_3.png").' alt="'.EB_AWARD_L5.'" title="'.EB_AWARD_L5.'"/> ';
				break;
				case 'PlayerStreak5':
				$award = EB_AWARD_L6;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_bronze_3.png").' alt="'.EB_AWARD_L7.'" title="'.EB_AWARD_L7.'"/> ';
				break;
				case 'PlayerStreak10':
				$award = EB_AWARD_L8;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_silver_3.png").' alt="'.EB_AWARD_L9.'" title="'.EB_AWARD_L9.'"/> ';
				break;
				case 'PlayerStreak25':
				$award = EB_AWARD_L10;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_gold_3.png").' alt="'.EB_AWARD_L11.'" title="'.EB_AWARD_L11.'"/> ';
				break;
			}

			$award_string = '<tr><td style="vertical-align:top">'.$icon.'</td>';
			$award_string .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$aUser.'">'.$aUserNickName.'</a>';
			$award_string .= ' '.$award;
			$award_string .= ' '.EB_MATCH_L12.' <a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$aLadderID.'">'.$aLadderName.'</a> ('.$aLaddergame.')';

			$award_string .= ' <div class="smalltext">';
			if (($time-$aTime) < INT_MINUTE )
			{
				$award_string .= EB_MATCH_L7;
			}
			else if (($time-$aTime) < INT_DAY )
			{
				$award_string .= get_formatted_timediff($aTime, $time).'&nbsp;'.EB_MATCH_L8;
			}
			else
			{
				$award_string .= $date;
			}
			$award_string .= '</div></td></tr>';

			$ladders[$nbr_ladders][0] = $aTime;
			$ladders[$nbr_ladders][1] = $award_string;
			$nbr_ladders ++;
		}
	}

	$q = "SELECT ".TBL_AWARDS.".*, "
	.TBL_TEAMS.".*, "
	.TBL_LADDERS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_AWARDS.", "
	.TBL_TEAMS.", "
	.TBL_LADDERS.", "
	.TBL_GAMES
    ." WHERE (".TBL_AWARDS.".Team = ".TBL_TEAMS.".TeamID)"
    ." AND (".TBL_TEAMS.".Ladder = ".TBL_LADDERS.".LadderID)"
	." AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	.$LadderID_award
	." ORDER BY ".TBL_AWARDS.".timestamp DESC"
	." LIMIT 0, $rowsPerPage";

	$result = $sql->db_Query($q);
	$numAwards = mysql_numrows($result);
	
	if ($numAwards>0)
	{
		/* Display table contents */
		for($i=0; $i < $numAwards; $i++)
		{
			$aID  = mysql_result($result,$i, TBL_AWARDS.".AwardID");
			$aLaddergame = mysql_result($result,$i , TBL_GAMES.".Name");
			$aLaddergameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
			$aType  = mysql_result($result,$i, TBL_AWARDS.".Type");
			$aTime  = mysql_result($result,$i, TBL_AWARDS.".timestamp");
			$aTime_local = $aTime + TIMEOFFSET;
			$date = date("d M Y, h:i A",$aTime_local);
			$aLadderID  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$aLadderName  = mysql_result($result,$i, TBL_LADDERS.".Name");

			$aClanTeam  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
			list($tclan, $tclantag, $tclanid) = getClanInfo($aClanTeam);

			switch ($aType) {
				case 'TeamTookFirstPlace':
				$award = EB_AWARD_L2;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_gold_3.png").' alt="'.EB_AWARD_L3.'" title="'.EB_AWARD_L3.'"/> ';
				break;
				case 'TeamInTopTen':
				$award = EB_AWARD_L4;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_bronze_3.png").' alt="'.EB_AWARD_L5.'" title="'.EB_AWARD_L5.'"/> ';
				break;
				case 'TeamStreak5':
				$award = EB_AWARD_L6;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_bronze_3.png").' alt="'.EB_AWARD_L7.'" title="'.EB_AWARD_L7.'"/> ';
				break;
				case 'TeamStreak10':
				$award = EB_AWARD_L8;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_silver_3.png").' alt="'.EB_AWARD_L9.'" title="'.EB_AWARD_L9.'"/> ';
				break;
				case 'TeamStreak25':
				$award = EB_AWARD_L10;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_gold_3.png").' alt="'.EB_AWARD_L11.'" title="'.EB_AWARD_L11.'"/> ';
				break;
			}

			$award_string = '<tr><td style="vertical-align:top">'.$icon.'</td>';
			$award_string .= '<td><a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$tclanid.'">'.$tclan.'</a>';
			$award_string .= ' '.$award;
			$award_string .= ' '.EB_MATCH_L12.' <a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$aLadderID.'">'.$aLadderName.'</a> ('.$aLaddergame.')';

			$award_string .= ' <div class="smalltext">';
			if (($time-$aTime) < INT_MINUTE )
			{
				$award_string .= EB_MATCH_L7;
			}
			else if (($time-$aTime) < INT_DAY )
			{
				$award_string .= get_formatted_timediff($aTime, $time).'&nbsp;'.EB_MATCH_L8;
			}
			else
			{
				$award_string .= $date;
			}
			$award_string .= '</div></td></tr>';

			$ladders[$nbr_ladders][0] = $aTime;
			$ladders[$nbr_ladders][1] = $award_string;
			$nbr_ladders ++;
		}
	}
	
	$text .= '<table style="margin-left: 0px; margin-right: auto;">';
	multi2dSortAsc($ladders, 0, SORT_DESC);
	for ($index = 0; $index<min($nbr_ladders, $rowsPerPage); $index++)
	{
		$text .= $ladders[$index][1];
	}
	if($index==0)
	$text .= "<tr><td>No activity</td></tr>";

	$text .= '</table>';

	return $text;
}

?>
