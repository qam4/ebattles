<?php
/**
* Challenge.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/event.php");
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
	.TBL_EVENTS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_CHALLENGES.", "
	.TBL_USERS.", "
	.TBL_EVENTS.", "
	.TBL_GAMES
	." WHERE (".TBL_CHALLENGES.".ChallengeID = '$challenge_id')"
	." AND (".TBL_USERS.".user_id = ".TBL_CHALLENGES.".ReportedBy)"
	." AND (".TBL_CHALLENGES.".Event = ".TBL_EVENTS.".EventID)"
	." AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)";

	$result = $sql->db_Query($q);
	$numChallenges = mysql_numrows($result);
	if ($numChallenges > 0)
	{
		$cReportedBy  = mysql_result($result, 0, TBL_USERS.".user_id");
		$cReportedByNickName  = mysql_result($result, 0, TBL_USERS.".user_name");
		$cEventID  = mysql_result($result, 0, TBL_EVENTS.".EventID");
		$cEventName  = mysql_result($result, 0, TBL_EVENTS.".Name");
		$cEventOwner  = mysql_result($result, 0, TBL_EVENTS.".Owner");
		$cEventgame = mysql_result($result, 0, TBL_GAMES.".Name");
		$cEventgameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
		$cEventType  = mysql_result($result, 0, TBL_EVENTS.".Type");
		$cStatus  = mysql_result($result,0, TBL_CHALLENGES.".Status");
		$cTime  = mysql_result($result, 0, TBL_CHALLENGES.".TimeReported");
		$cTime_local = $cTime + TIMEOFFSET;
		$date = date("d M Y, h:i A",$cTime_local);

		$cChallengerpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerPlayer");
		$cChallengedpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedPlayer");

		$string .= '<tr>';
		// Game icon
		if (($type & eb_MATCH_NOEVENTINFO) == 0)
		{
			$string .= '<td style="vertical-align:top"><a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$cEventID.'" title="'.$cEventgame.'">';
			$string .= '<img '.getActivityGameIconResize($cEventgameicon).'/>';
			$string .= '</a></td>';
		}

		$string .= '<td>';
		// Challenger Info
		$q = "SELECT ".TBL_PLAYERS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_PLAYERS.", "
		.TBL_USERS
		." WHERE (".TBL_PLAYERS.".Event = '$cEventID')"
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
		." WHERE (".TBL_PLAYERS.".Event = '$cEventID')"
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

		// Event
		if (($type & eb_MATCH_NOEVENTINFO) == 0)
		{
			$string .= ' '.EB_MATCH_L12.' <a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$cEventID.'">'.$cEventName.'</a>';
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
		$string .= '<td>';
		// If user is challenger, show the "Withraw" button
		if ($isUserChallenger == TRUE)
		{
			$string .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?eventid='.$cEventID.'&amp;challengeid='.$challenge_id.'" method="post">';
			$string .= '<div>';
			$string .= ebImageTextButton('challenge_withdraw', 'delete.png', '', 'negative', EB_CHALLENGE_L16, EB_CHALLENGE_L15);
			$string .= '</div>';
			$string .= '</form>';
		}
		// If user is challenged, show the "Confirm"  button
		// If user is admin/moderator, show the "Edit" button
		if ($isUserChallenged == TRUE)
		{
			$string .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?eventid='.$cEventID.'&amp;challengeid='.$challenge_id.'" method="post">';
			$string .= '<div>';
			$string .= ebImageTextButton('challenge_confirm', 'page_white_edit.png', '', '', '', EB_CHALLENGE_L17);
			$string .= '</div>';
			$string .= '</form>';
		}
		$string .= '</td>';
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