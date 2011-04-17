<?php
/**
* Challenge.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/ladder.php");
require_once(e_PLUGIN."ebattles/include/clan.php");

function displayChallengeInfo($challenge_id, $type = 0)
{
	global $time;
	global $sql;
	global $pref;

	$string ='';
	// Get info about the challenge
	$q = "SELECT DISTINCT ".TBL_CHALLENGES.".*, "
	.TBL_USERS.".*, "
	.TBL_LADDERS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_CHALLENGES.", "
	.TBL_USERS.", "
	.TBL_LADDERS.", "
	.TBL_GAMES
	." WHERE (".TBL_CHALLENGES.".ChallengeID = '$challenge_id')"
	." AND (".TBL_USERS.".user_id = ".TBL_CHALLENGES.".ReportedBy)"
	." AND (".TBL_CHALLENGES.".Ladder = ".TBL_LADDERS.".LadderID)"
	." AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)";

	$result = $sql->db_Query($q);
	$numChallenges = mysql_numrows($result);
	if ($numChallenges > 0)
	{
		$cReportedBy  = mysql_result($result, 0, TBL_USERS.".user_id");
		$cReportedByNickName  = mysql_result($result, 0, TBL_USERS.".user_name");
		$cLaddergame = mysql_result($result, 0, TBL_GAMES.".Name");
		$cLaddergameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
		$cStatus  = mysql_result($result,0, TBL_CHALLENGES.".Status");
		$cTime  = mysql_result($result, 0, TBL_CHALLENGES.".TimeReported");
		$cTime_local = $cTime + TIMEOFFSET;
		$date = date("d M Y, h:i A",$cTime_local);
		$ladder_id  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
		$ladder = new Ladder($ladder_id);

		$cChallengerpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerPlayer");
		$cChallengedpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedPlayer");
		$cChallengertID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerTeam");
		$cChallengedtID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedTeam");

		$string .= '<tr>';
		// Game icon
		if (($type & eb_MATCH_NOLADDERINFO) == 0)
		{
			$string .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder->getField('LadderID').'" title="'.$cLaddergame.'">';
			$string .= '<img '.getActivityGameIconResize($cLaddergameicon).'/>';
			$string .= '</a></td>';
		}

		$string .= '<td>';

		// User info
		$q = "SELECT ".TBL_PLAYERS.".*, "
		.TBL_USERS.".*, "
		.TBL_TEAMS.".*"
		." FROM ".TBL_PLAYERS.", "
		.TBL_USERS.", "
		.TBL_TEAMS
		." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
		."   AND (".TBL_TEAMS.".TeamID = ".TBL_PLAYERS.".Team)"
		."   AND (".TBL_PLAYERS.".User = '".USERID."')";
		$result = $sql->db_Query($q);
		$uteam  = mysql_result($result,0 , TBL_PLAYERS.".Team");

		switch($ladder->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			// Challenger Info
			$q = "SELECT ".TBL_PLAYERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
			."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
			."   AND (".TBL_PLAYERS.".PlayerID = '$cChallengerpID')";
			$result = $sql->db_Query($q);
			$challengerpid   = mysql_result($result, 0,TBL_PLAYERS.".PlayerID");
			$challengerpuid   = mysql_result($result, 0, TBL_USERS.".user_id");
			$challengerpname  = mysql_result($result, 0, TBL_USERS.".user_name");
			$challengerpavatar = mysql_result($result,$index, TBL_USERS.".user_image");
			$challengerpteam  = mysql_result($result,$index , TBL_PLAYERS.".Team");
			list($challengerpclan, $challengerpclantag, $challengerpclanid) = getClanName($challengerpteam);
			$isUserChallenger = (USERID == $challengerpuid) ? TRUE : FALSE;
			$string .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$challengerpuid.'">'.$challengerpclantag.$challengerpname.'</a>';

			$string .= ' vs. ';

			// Challenged Info
			$q = "SELECT ".TBL_PLAYERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
			."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
			."   AND (".TBL_PLAYERS.".PlayerID = '$cChallengedpID')";
			$result = $sql->db_Query($q);
			$challengedpid   = mysql_result($result, 0,TBL_PLAYERS.".PlayerID");
			$challengedpuid   = mysql_result($result, 0, TBL_USERS.".user_id");
			$challengedpname  = mysql_result($result, 0, TBL_USERS.".user_name");
			$challengedpavatar = mysql_result($result,$index, TBL_USERS.".user_image");
			$challengedpteam  = mysql_result($result,$index , TBL_PLAYERS.".Team");
			list($challengedpclan, $challengedpclantag, $challengedpclanid) = getClanName($challengedpteam);
			$isUserChallenged = (USERID == $challengedpuid) ? TRUE : FALSE;
			$string .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$challengedpuid.'">'.$challengedpclantag.$challengedpname.'</a>';
			break;
			case "ClanWar":
			// Challenger Info
			$q = "SELECT ".TBL_TEAMS.".*"
			."   AND (".TBL_TEAMS.".TeamID = '$cChallengertID')";
			$result = $sql->db_Query($q);
			$challengertrank  = mysql_result($result, 0, TBL_TEAMS.".Rank");
			list($challengertclan, $challengertclantag, $challengertclanid) = getClanName($cChallengertID);

			$isUserChallenger = ($uteam == $cChallengertID) ? TRUE : FALSE;
			$string .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$challengertclanid.'">'.$challengertclan.'</a>';

			$string .= ' vs. ';

			// Challenged Info
			$q = "SELECT ".TBL_TEAMS.".*"
			."   AND (".TBL_TEAMS.".TeamID = '$cChallengedtID')";
			$result = $sql->db_Query($q);
			$challengedtrank  = mysql_result($result, 0, TBL_TEAMS.".Rank");
			list($challengedtclan, $challengedtclantag, $challengedtclanid) = getClanName($cChallengedtID);
			$isUserChallenged = ($uteam == $cChallengedtID) ? TRUE : FALSE;
			$string .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$challengedtclanid.'">'.$challengedtclan.'</a>';
			break;

			default:
		}

		// Ladder
		if (($type & eb_MATCH_NOLADDERINFO) == 0)
		{
			$string .= ' '.EB_MATCH_L12.' <a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.$ladder->getField('Name').'</a>';
		}

		// Submitted by
		$string .= ' <div class="smalltext">';
		$string .= EB_MATCH_L15.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$cReportedBy.'">'.$cReportedByNickName.'</a> ';

		if (($time-$cTime) < INT_MINUTE )
		{
			$string .= EB_MATCH_L7;
		}
		else if (($time-$cTime) < INT_DAY )
		{
			$string .= get_formatted_timediff($cTime, $time).'&nbsp;'.EB_MATCH_L8;
		}
		else
		{
			$string .= EB_MATCH_L9.'&nbsp;'.$date.'.';
		}

		$string .= '</div></td>';

		// Action form
		// Is the user a moderator?
		$can_delete = 0;

		$q_Mods = "SELECT ".TBL_LADDERMODS.".*"
		." FROM ".TBL_LADDERMODS
		." WHERE (".TBL_LADDERMODS.".Ladder = '$ladder_id')"
		."   AND (".TBL_LADDERMODS.".User = ".USERID.")";
		$result_Mods = $sql->db_Query($q_Mods);
		$numMods = mysql_numrows($result_Mods);

		if (check_class($pref['eb_mod_class']))  $can_delete = 1;
		if (USERID==$eowner)
		{
			$userclass |= eb_UC_LADDER_OWNER;
			$can_delete = 1;
		}
		if ($numMods>0)
		{
			$userclass |= eb_UC_EB_MODERATOR;
			$can_delete = 1;
		}
		// If the user is challenger, show the "Withdraw" button
		/*
		if ($isUserChallenger == TRUE)
		{
		$can_delete = 1;
		}
		*/

		if($can_delete != 0)
		{
		$string .= '<td>';
			$string .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?LadderID='.$ladder_id.'&amp;challengeid='.$challenge_id.'" method="post">';
			$string .= '<div>';
			$string .= ebImageTextButton('challenge_withdraw', 'delete.png', '', 'simple', EB_CHALLENGE_L16, EB_CHALLENGE_L15);
			$string .= '</div>';
			$string .= '</form>';
		$string .= '</td>';
		}
		// If the user is challenged, show the "Confirm"  button
		if ($isUserChallenged == TRUE)
		{
		$string .= '<td>';
			$string .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?LadderID='.$ladder_id.'&amp;challengeid='.$challenge_id.'" method="post">';
			$string .= '<div>';
			$string .= ebImageTextButton('challenge_confirm', 'page_white_edit.png', '', 'simple', '', EB_CHALLENGE_L17);
			$string .= '</div>';
			$string .= '</form>';
		$string .= '</td>';
		}
		// If the user is admin/moderator, show the "Edit" button
		// fm: TBD ???

		$string .= '</tr>';
	}

	return $string;
}

function deleteChallenge($challenge_id)
{
	global $sql;
	$q = "DELETE FROM ".TBL_CHALLENGES
	." WHERE (".TBL_CHALLENGES.".ChallengeID = '$challenge_id')";
	$result = $sql->db_Query($q);
}

?>