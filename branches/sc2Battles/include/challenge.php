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

class Challenge extends DatabaseTable
{
	protected $tablename = TBL_CHALLENGES;
	protected $primary_key = "ChallengeID";

	/***************************************************************************************
	Functions
	***************************************************************************************/
	function displayChallengeInfo($type = 0)
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
		." WHERE (".TBL_CHALLENGES.".ChallengeID = '".$this->fields['ChallengeID']."')"
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
				list($challengerpclan, $challengerpclantag, $challengerpclanid) = getClanInfo($challengerpteam);
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
				list($challengedpclan, $challengedpclantag, $challengedpclanid) = getClanInfo($challengedpteam);
				$isUserChallenged = (USERID == $challengedpuid) ? TRUE : FALSE;
				$string .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$challengedpuid.'">'.$challengedpclantag.$challengedpname.'</a>';
				break;
				case "ClanWar":
				// Challenger Info
				$q = "SELECT ".TBL_TEAMS.".*"
				."   AND (".TBL_TEAMS.".TeamID = '$cChallengertID')";
				$result = $sql->db_Query($q);
				$challengertrank  = mysql_result($result, 0, TBL_TEAMS.".Rank");
				list($challengertclan, $challengertclantag, $challengertclanid) = getClanInfo($cChallengertID);

				$isUserChallenger = ($uteam == $cChallengertID) ? TRUE : FALSE;
				$string .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$challengertclanid.'">'.$challengertclan.'</a>';

				$string .= ' vs. ';

				// Challenged Info
				$q = "SELECT ".TBL_TEAMS.".*"
				."   AND (".TBL_TEAMS.".TeamID = '$cChallengedtID')";
				$result = $sql->db_Query($q);
				$challengedtrank  = mysql_result($result, 0, TBL_TEAMS.".Rank");
				list($challengedtclan, $challengedtclantag, $challengedtclanid) = getClanInfo($cChallengedtID);
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

			$q_Mods = "SELECT ".TBL_MODS.".*"
			." FROM ".TBL_MODS
			." WHERE (".TBL_MODS.".Ladder = '$ladder_id')"
			."   AND (".TBL_MODS.".User = ".USERID.")";
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
				$string .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?LadderID='.$ladder_id.'&amp;challengeid='.$this->fields['ChallengeID'].'" method="post">';
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
				$string .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?LadderID='.$ladder_id.'&amp;challengeid='.$this->fields['ChallengeID'].'" method="post">';
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

	function deleteChallenge()
	{
		global $sql;
		$q = "DELETE FROM ".TBL_CHALLENGES
		." WHERE (".TBL_CHALLENGES.".ChallengeID = '".$this->fields['ChallengeID']."')";
		$result = $sql->db_Query($q);
	}

	function ChallengeConfirmForm()
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
		." WHERE (".TBL_CHALLENGES.".ChallengeID = '".$this->fields['ChallengeID']."')"
		." AND (".TBL_USERS.".user_id = ".TBL_CHALLENGES.".ReportedBy)"
		." AND (".TBL_CHALLENGES.".Ladder = ".TBL_LADDERS.".LadderID)"
		." AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)";

		$result = $sql->db_Query($q);
		$numChallenges = mysql_numrows($result);

		if ($numChallenges > 0)
		{
			$ladder_id  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
			$ladder = new Ladder($ladder_id);

			$cReportedBy  = mysql_result($result, 0, TBL_USERS.".user_id");
			$cReportedByNickName  = mysql_result($result, 0, TBL_USERS.".user_name");
			$cLaddergame = mysql_result($result, 0, TBL_GAMES.".Name");
			$cLaddergameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
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

			$output .= '<form action="'.e_PLUGIN.'ebattles/challengeconfirm.php?LadderID='.$ladder_id.'&amp;challengeid='.$this->fields['ChallengeID'].'" method="post">';

			$output .= '<b>'.EB_CHALLENGE_L18.'</b><br />';
			$output .= '<br />';

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
				list($challengerpclan, $challengerpclantag, $challengerpclanid) = getClanInfo($challengerpteam);
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
				list($challengedpclan, $challengedpclantag, $challengedpclanid) = getClanInfo($challengedpteam);
				$isUserChallenged = (USERID == $challengedpuid) ? TRUE : FALSE;
				$string .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$challengedpuid.'">'.$challengedpclantag.$challengedpname.'</a>';
				break;
				case "ClanWar":
				// Challenger Info
				$q = "SELECT ".TBL_TEAMS.".*"
				."   AND (".TBL_TEAMS.".TeamID = '$cChallengertID')";
				$result = $sql->db_Query($q);
				$challengertrank  = mysql_result($result, 0, TBL_TEAMS.".Rank");
				list($challengertclan, $challengertclantag, $challengertclanid) = getClanInfo($cChallengertID);

				$isUserChallenger = ($uteam == $cChallengertID) ? TRUE : FALSE;
				$string .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$challengertclanid.'">'.$challengertclan.'</a>';

				$string .= ' vs. ';

				// Challenged Info
				$q = "SELECT ".TBL_TEAMS.".*"
				."   AND (".TBL_TEAMS.".TeamID = '$cChallengedtID')";
				$result = $sql->db_Query($q);
				$challengedtrank  = mysql_result($result, 0, TBL_TEAMS.".Rank");
				list($challengedtclan, $challengedtclantag, $challengedtclanid) = getClanInfo($cChallengedtID);
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


	function ChallengeAccept()
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
		." WHERE (".TBL_CHALLENGES.".ChallengeID = '".$this->fields['ChallengeID']."')"
		." AND (".TBL_USERS.".user_id = ".TBL_CHALLENGES.".ReportedBy)"
		." AND (".TBL_CHALLENGES.".Ladder = ".TBL_LADDERS.".LadderID)"
		." AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)";

		$result = $sql->db_Query($q);
		$numChallenges = mysql_numrows($result);

		if ($numChallenges > 0)
		{
			$ladder_id  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
			$ladder = new Ladder($ladder_id);

			$cReportedBy  = mysql_result($result, 0, TBL_USERS.".user_id");
			$cReportedByNickName  = mysql_result($result, 0, TBL_USERS.".user_name");
			$cLaddergame = mysql_result($result, 0, TBL_GAMES.".Name");
			$cLaddergameicon = mysql_result($result, 0, TBL_GAMES.".Icon");
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
			VALUES ($ladder_id,".USERID.", $time, '$comments', 'scheduled', $challenge_time)";
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

			$this->deleteChallenge();

			// Send notification to all the players.
			$fromid = 0;
			$subject = SITENAME." ".EB_MATCHR_L52;

			switch($ladder->getField('Type'))
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
				//echo "numPlayers: $numPlayers<br>";

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
				//echo "numPlayers: $numPlayers<br>";

				break;
				default:
			}

			if($numPlayers > 0)
			{
				for($j=0; $j < $numPlayers; $j++)
				{
					$pname = mysql_result($result_Players, $j, TBL_USERS.".user_name");
					$pemail = mysql_result($result_Players, $j, TBL_USERS.".user_email");
					$message = EB_MATCHR_L53.$pname.EB_MATCHR_L54.EB_MATCHR_L55.$ladder->getField('Name').EB_MATCHR_L56;
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

	function ChallengeDecline()
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
		." WHERE (".TBL_CHALLENGES.".ChallengeID = '".$this->fields['ChallengeID']."')"
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
			$ladder_id  = mysql_result($result, 0, TBL_LADDERS.".LadderID");
			$ladder = new Ladder($ladder_id);

			$subject = SITENAME." ".EB_CHALLENGE_L29;
			$message = EB_CHALLENGE_L30.$cReportedByNickName.EB_CHALLENGE_L31.USERNAME.EB_CHALLENGE_L32.$ladder->getField('Name').EB_CHALLENGE_L33;
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

		$this->deleteChallenge();
	}

}



?>