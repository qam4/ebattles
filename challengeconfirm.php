<?php
/**
* ChallengeRequest.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN.'ebattles/include/main.php');
require_once(e_PLUGIN.'ebattles/include/match.php');
require_once(e_PLUGIN."ebattles/include/ladder.php");
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

/* Ladder Name */
$ladder_id = $_GET['LadderID'];
$challenge_id = $_GET['challengeid'];

$ladder = new Ladder($ladder_id);

$text = '';

if(isset($_POST['challenge_withdraw']))
{
	deleteChallenge($challenge_id);
	$text .= EB_LADDER_L69;
	$text .= '<br />';
	$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
}
if(isset($_POST['challenge_confirm']))
{
	$text .= ChallengeConfirmForm($challenge_id);
}
if(isset($_POST['challenge_accept']))
{
	// Verify form
	$error_str = '';

	if (!empty($error_str)) {
		// show form again
		$text .= ChallengeConfirmForm($challenge_id);

		// errors have occured, halt execution and show form again.
		$text .= '<p style="color:red">'.EB_MATCHR_L14;
		$text .= '<ul style="color:red">'.$error_str.'</ul></p>';
	}
	else
	{
		ChallengeAccept($challenge_id);
		$text .= EB_CHALLENGE_L22;
		$text .= '<br />';
		$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
	}
}
if(isset($_POST['challenge_decline']))
{
	Challengedecline($challenge_id);
	$text .= EB_LADDER_L69;

	$text .= '<br />';
	$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
}

$ns->tablerender($ladder->getField('Name')." (".ladderTypeToString($ladder->getField('Type')).") - ".EB_CHALLENGE_L1, $text);
require_once(FOOTERF);
exit;

//=================================================================================
// Functions
//=================================================================================
function ChallengeConfirmForm($challenge_id)
{
	global $sql;
	global $tp;
	global $time;

	$output ='';

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
		$cLadderID  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
		$cLadderName  = mysql_result($result, 0, TBL_LADDERS.".Name");
		$cLadderOwner  = mysql_result($result, 0, TBL_LADDERS.".Owner");
		$cLaddergame = mysql_result($result, 0, TBL_GAMES.".Name");
		$cLaddergameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
		$cLadderType  = mysql_result($result, 0, TBL_LADDERS.".Type");
		$cLadderNumDates  = mysql_result($result,0 , TBL_LADDERS.".MaxDatesPerChallenge");
		$cComments  = mysql_result($result,0, TBL_CHALLENGES.".Comments");
		$cStatus  = mysql_result($result,0, TBL_CHALLENGES.".Status");
		$cTime  = mysql_result($result, 0, TBL_CHALLENGES.".TimeReported");
		$cTime_local = $cTime + TIMEOFFSET;
		$date = date("d M Y, h:i A",$cTime_local);
		$cMatchDates  = mysql_result($result, 0, TBL_CHALLENGES.".MatchDates");

		$cChallengerpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerPlayer");
		$cChallengedpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedPlayer");
		$cChallengertID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerTeam");
		$cChallengedtID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedTeam");

		$output .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?LadderID='.$cLadderID.'&amp;challengeid='.$challenge_id.'" method="post">';

		$output .= '<b>'.EB_CHALLENGE_L18.'</b><br />';
		$output .= '<br />';

		switch($cLadderType)
		{
			case "One Player Ladder":
			case "Team Ladder":
			// Challenger Info
			$q = "SELECT ".TBL_PLAYERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_PLAYERS.".Ladder = '$cLadderID')"
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
			." WHERE (".TBL_PLAYERS.".Ladder = '$cLadderID')"
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
		$output .= ' '.$string.'<br />';

		$output .= '<br />';

		// Comments
		if ($cComments)
		{
			$output .= '<b>'.EB_CHALLENGE_L28.'</b><br />'; // Comments
			$output .= '<p>';
			$output .= $tp->toHTML($cComments, true).'<br />';
			$output .= '</p>';
			$output .= '<br />';
		}


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


function ChallengeAccept($challenge_id)
{
	global $sql;
	global $tp;
	global $time;
	global $pref;

	$challenge_time = $_POST['challengedate'];

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
		$cLadderID  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
		$cLadderName  = mysql_result($result, 0, TBL_LADDERS.".Name");
		$cLadderOwner  = mysql_result($result, 0, TBL_LADDERS.".Owner");
		$cLaddergame = mysql_result($result, 0, TBL_GAMES.".Name");
		$cLaddergameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
		$cLadderType  = mysql_result($result, 0, TBL_LADDERS.".Type");
		$cLadderNumDates  = mysql_result($result,0 , TBL_LADDERS.".MaxDatesPerChallenge");
		$cStatus  = mysql_result($result,0, TBL_CHALLENGES.".Status");
		$cTime  = mysql_result($result, 0, TBL_CHALLENGES.".TimeReported");
		$cChallengerpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerPlayer");
		$cChallengedpID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedPlayer");
		$cChallengertID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengerTeam");
		$cChallengedtID  = mysql_result($result, 0, TBL_CHALLENGES.".ChallengedTeam");

		// Create Match ------------------------------------------
		$comments = '';
		$q =
		"INSERT INTO ".TBL_MATCHS."(Ladder,ReportedBy,TimeReported, Comments, Status, TimeScheduled)
		VALUES ($cLadderID,".USERID.", $time, '$comments', 'scheduled', $challenge_time)";
		$result = $sql->db_Query($q);

		$last_id = mysql_insert_id();
		$match_id = $last_id;

		$q =
		"INSERT INTO ".TBL_SCORES."(MatchID,Player,Team,Player_MatchTeam,Player_Rank)
		VALUES ($match_id,$cChallengerpID,$cChallengertID,1,1)
		";
		$result = $sql->db_Query($q);

		$q =
		"INSERT INTO ".TBL_SCORES."(MatchID,Player,Team,Player_MatchTeam,Player_Rank)
		VALUES ($match_id,$cChallengedpID,$cChallengedtID,2,2)
		";
		$result = $sql->db_Query($q);

		deleteChallenge($challenge_id);

		// Send notification to all the players.
		$fromid = 0;
		$subject = SITENAME." ".EB_MATCHR_L52;

		switch($cLadderType)
		{
			case "One Player Ladder":
			case "Team Ladder":
			$q_Players = "SELECT DISTINCT ".TBL_USERS.".*"
			." FROM ".TBL_MATCHS.", "
			.TBL_SCORES.", "
			.TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
			." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
			." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
			." AND (".TBL_PLAYERS.".User = ".TBL_USERS.".user_id)";
			$result_Players = $sql->db_Query($q_Players);
			$numPlayers = mysql_numrows($result_Players);
			echo "numPlayers: $numPlayers<br>";

			break;
			case "ClanWar":
			$q_Players = "SELECT DISTINCT ".TBL_USERS.".*"
			." FROM ".TBL_MATCHS.", "
			.TBL_SCORES.", "
			.TBL_TEAMS.", "
			.TBL_PLAYERS.", "
			.TBL_USERS
			." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
			." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
			." AND (".TBL_TEAMS.".TeamID = ".TBL_SCORES.".Team)"
			." AND (".TBL_PLAYERS.".Team = ".TBL_TEAMS.".TeamID)"
			." AND (".TBL_PLAYERS.".User = ".TBL_USERS.".user_id)";
			$result_Players = $sql->db_Query($q_Players);
			$numPlayers = mysql_numrows($result_Players);
			echo "numPlayers: $numPlayers<br>";

			break;
			default:
		}

		if($numPlayers > 0)
		{
			for($j=0; $j < $numPlayers; $j++)
			{
				$pname = mysql_result($result_Players, $j, TBL_USERS.".user_name");
				$pemail = mysql_result($result_Players, $j, TBL_USERS.".user_email");
				$message = EB_MATCHR_L53.$pname.EB_MATCHR_L54.EB_MATCHR_L55.$cLadderName.EB_MATCHR_L56;
				if (check_class($pref['eb_pm_notifications_class']))
				{
					$sendto = mysql_result($result_Players, $j, TBL_USERS.".user_id");
					sendNotification($sendto, $subject, $message, $fromid);
				}
				if (check_class($pref['eb_email_notifications_class']))
				{
					// Send email
					require_once(e_HANDLER."mail.php");
					sendemail($pemail, $subject, $message);
				}
			}
		}
	}
}

function ChallengeDecline($challenge_id)
{
	global $sql;
	global $tp;
	global $time;
	global $pref;

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
		$cReportedByEmail  = mysql_result($result, 0, TBL_USERS.".user_email");
		$cLadderID  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
		$cLadderName  = mysql_result($result, 0, TBL_LADDERS.".Name");

		$subject = SITENAME." ".EB_CHALLENGE_L29;
		$message = EB_CHALLENGE_L30.$cReportedByNickName.EB_CHALLENGE_L31.USERNAME.EB_CHALLENGE_L32.$cLadderName.EB_CHALLENGE_L33;
		$fromid = 0;
		$sendto = $cReportedBy;
		$sendtoemail = $cReportedByEmail;
		if (check_class($pref['eb_pm_notifications_class']))
		{
			// Send PM
			sendNotification($sendto, $subject, $message, $fromid);
		}

		if (check_class($pref['eb_email_notifications_class']))
		{
			// Send email
			require_once(e_HANDLER."mail.php");
			sendemail($sendtoemail, $subject, $message);
		}
	}

	deleteChallenge($challenge_id);
}
?>



