<?php
/**
* ChallengeRequest.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/event.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/challenge.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

/*
//dbg form
echo "<br>_POST: ";
print_r($_POST);    // show $_POST
echo "<br>_GET: ";
print_r($_GET);     // show $_GET
*/

/* Event Name */
$event_id = $_GET['eventid'];
$challenge_id = $_GET['challengeid'];
$q = "SELECT ".TBL_EVENTS.".*"
." FROM ".TBL_EVENTS
." WHERE (".TBL_EVENTS.".eventid = '$event_id')";
$result = $sql->db_Query($q);

$eMatchesApproval = mysql_result($result,0 , TBL_EVENTS.".MatchesApproval");
$etype = mysql_result($result,0 , TBL_EVENTS.".Type");
$ename = mysql_result($result,0 , TBL_EVENTS.".Name");
$text = '';

if(isset($_POST['challenge_withdraw']))
{
	deleteChallenge($challenge_id);
	$text .= EB_EVENT_L69;
	$text .= '<br />';
	$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
}
if(isset($_POST['challenge_confirm']))
{
	$text .= PlayerChallengeConfirmForm($challenge_id);
}
if(isset($_POST['challenge_accept']))
{
	// Verify form
	$error_str = '';

	if (!empty($error_str)) {
		// show form again
		$text .= PlayerChallengeConfirmForm($challenge_id);

		// errors have occured, halt execution and show form again.
		$text .= '<p style="color:red">'.EB_MATCHR_L14;
		$text .= '<ul style="color:red">'.$error_str.'</ul></p>';
	}
	else
	{
		PlayerChallengeAccept($challenge_id);
		$text .= EB_CHALLENGE_L22;
		$text .= '<br />';
		$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
	}
}
if(isset($_POST['challenge_decline']))
{
	deleteChallenge($challenge_id);
	$text .= EB_EVENT_L69;

	$text .= '<br />';
	$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
}

$ns->tablerender("$ename (".eventType($etype).") - ".EB_CHALLENGE_L1, $text);
require_once(FOOTERF);
exit;

//=================================================================================
// Functions
//=================================================================================
function PlayerChallengeConfirmForm($challenge_id)
{
	global $sql;
	global $tp;
	global $time;

	$output ='';

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
		$cEventNumDates  = 3; // Hardcoded for now
		$cStatus  = mysql_result($result,0, TBL_CHALLENGES.".Status");
		$cTime  = mysql_result($result, 0, TBL_CHALLENGES.".TimeReported");
		$cTime_local = $cTime + TIMEOFFSET;
		$date = date("d M Y, h:i A",$cTime_local);
		$cMatchDates  = mysql_result($result, 0, TBL_CHALLENGES.".MatchDates");

		$cChallengerpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerPlayer");
		$cChallengedpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedPlayer");

		$output .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?eventid='.$cEventID.'&amp;challengeid='.$challenge_id.'" method="post">';

		$output .= '<b>'.EB_CHALLENGE_L18.'</b><br />';
		$output .= '<br />';

		// Challenger Info
		$output .= '<b>'.EB_CHALLENGE_L5.'</b>'; // Challenger
		$q = "SELECT ".TBL_PLAYERS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_PLAYERS.", "
		.TBL_USERS
		." WHERE (".TBL_PLAYERS.".Event = '$cEventID')"
		."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
		."   AND (".TBL_PLAYERS.".PlayerID = '$cChallengerpID')";
		$result = $sql->db_Query($q);

		$pid    = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
		$puid   = mysql_result($result,0 , TBL_USERS.".user_id");
		$prank  = mysql_result($result,0 , TBL_PLAYERS.".Rank");
		$pname  = mysql_result($result,0 , TBL_USERS.".user_name");
		$pteam  = mysql_result($result,0 , TBL_PLAYERS.".Team");
		list($pclan, $pclantag, $pclanid) = getClanName($pteam);

		if ($prank==0)
		$prank_txt = EB_EVENT_L54;
		else
		$prank_txt = "#$prank";
		$str = $pclantag.$pname.' ('.$prank_txt.')';
		$output .= ' '.$str.'<br />';

		// Challenged Info
		$output .= '<b>'.EB_CHALLENGE_L6.'</b>'; // Challenged
		$q = "SELECT ".TBL_PLAYERS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_PLAYERS.", "
		.TBL_USERS
		." WHERE (".TBL_PLAYERS.".Event = '$cEventID')"
		."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
		."   AND (".TBL_PLAYERS.".PlayerID = '$cChallengedpID')";
		$result = $sql->db_Query($q);

		$pid    = mysql_result($result, 0, TBL_PLAYERS.".PlayerID");
		$puid   = mysql_result($result, 0, TBL_USERS.".user_id");
		$prank  = mysql_result($result, 0, TBL_PLAYERS.".Rank");
		$pname  = mysql_result($result, 0, TBL_USERS.".user_name");
		$pteam  = mysql_result($result, 0, TBL_PLAYERS.".Team");
		list($pclan, $pclantag, $pclanid) = getClanName($pteam);

		if ($prank==0)
		$prank_txt = EB_EVENT_L54;
		else
		$prank_txt = "#$prank";
		$str = $pclantag.$pname.' ('.$prank_txt.')';
		$output .= ' '.$str.'<br />';

		$output .= '<br />';

		// Select Date
		$matchDates = explode(",", $cMatchDates);

		$output .= '<b>'.EB_CHALLENGE_L19.'</b><br />'; // Select Dates
		$output .= '<div>
		<select class="tbox" name="challengedate">
		';
		foreach($matchDates as $matchDate)
		{
			$matchDate_local = $matchDate + TIMEOFFSET;
			$date = date("d M Y, h:i A",$matchDate_local);
			$output .= '<option value="'.$matchDate.'"';
			$output .= '>'.$date.'</option>';
		}
		$output .= '
		</select>
		</div>
		';

		$output .= '
		<br />
		<table class="table_left"><tr>
		<td>'.ebImageTextButton('challenge_accept', 'thumb_up.png', EB_CHALLENGE_L20, 'positive').'</td>
		<td>'.ebImageTextButton('challenge_decline', 'thumb_down.png', EB_CHALLENGE_L21, 'negative').'</td>
		</tr></table>
		</form>
		';
	}
	return $output;
}


function PlayerChallengeAccept($challenge_id)
{
	global $sql;
	global $tp;
	global $time;

	$challenge_time = $_POST['challengedate'];

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
		$cEventNumDates  = 3; // Hardcoded for now
		$cStatus  = mysql_result($result,0, TBL_CHALLENGES.".Status");
		$cTime  = mysql_result($result, 0, TBL_CHALLENGES.".TimeReported");
		$cChallengerpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerPlayer");
		$cChallengedpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedPlayer");


		// Create Match ------------------------------------------
		$comments = '';
		$q =
		"INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported, Comments, Status, TimeScheduled)
		VALUES ($cEventID,".USERID.", $time, '$comments', 'scheduled', $challenge_time)";
		$result = $sql->db_Query($q);

		$last_id = mysql_insert_id();
		$match_id = $last_id;

		// Create Scores ------------------------------------------
		$q =
		"INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_Rank)
		VALUES ($match_id,$cChallengerpID,1,1)
		";
		$result = $sql->db_Query($q);

		$q =
		"INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_Rank)
		VALUES ($match_id,$cChallengedpID,2,2)
		";
		$result = $sql->db_Query($q);
		
		deleteChallenge($challenge_id);
	}
}

?>



