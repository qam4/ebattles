<?php
/**
* tournamentinfo.php
*
*/

/* Update */
if ($eventIsChanged == 1)
{
	$event->setFieldDB('IsChanged', 0);
	$eventIsChanged = 0;
}

$can_signup = 0;
$eMaxNumberPlayers = $event->getField('MaxNumberPlayers');
switch($event->getField('Type'))
{
	case "One Player Tournament":
	if(($eMaxNumberPlayers == 0)||($nbrplayers < $eMaxNumberPlayers)) $can_signup = 1;
	$tab_title = EB_EVENT_L77;
	break;
	case "Clan Tournament":
	if(($eMaxNumberPlayers == 0)||($nbrteams < $eMaxNumberPlayers))	$can_signup = 1;
	$tab_title = EB_EVENT_L84;
	break;
	default:
}

$text .= '<div id="tabs">';
$text .= '<ul>';
$text .= '<li><a href="#tabs-1">'.EB_EVENT_L35.'</a></li>';
$text .= '<li><a href="#tabs-3">'.EB_EVENT_L76.'</a></li>';
$text .= '<li><a href="#tabs-4">'.EB_EVENT_L58.'</a></li>';
$text .= '<li><a href="#tabs-5">'.$tab_title.'</a></li>';
$text .= '</ul>';

/*----------------------------------------------------------------------------------------
Display Info
----------------------------------------------------------------------------------------*/
$text .= '<div id="tabs-1">';
$can_manage = 0;
if (check_class($pref['eb_mod_class'])) $can_manage = 1;
if (USERID==$eowner) $can_manage = 1;
if ($can_manage == 1)
{
	$text .= '
	<form action="'.e_PLUGIN.'ebattles/eventmanage.php?eventid='.$event_id.'" method="post"><div>
	'.ebImageTextButton('submit', 'page_white_edit.png', EB_EVENT_L40).'
	</div></form>';
}


/* Signup, Join/Quit Event */
if ($can_signup==1)
{
	$text .= '<table style="width:95%"><tbody>';
	$userIsDivisionCaptain = FALSE;
	if(check_class(e_UC_MEMBER))
	{
		// If logged in
		if($event->getField('Status') == 'signup')
		{
			// If event signups
			if ($event->getField('Type') == "Clan Tournament")
			{
				// Find if user is captain of a division playing that game
				// if yes, propose to join this event
				$q = "SELECT ".TBL_DIVISIONS.".*, "
				.TBL_CLANS.".*, "
				.TBL_GAMES.".*, "
				.TBL_USERS.".*"
				." FROM ".TBL_DIVISIONS.", "
				.TBL_CLANS.", "
				.TBL_GAMES.", "
				.TBL_USERS
				." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
				." AND (".TBL_GAMES.".GameID = '$egameid')"
				." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
				." AND (".TBL_USERS.".user_id = ".USERID.")"
				." AND (".TBL_DIVISIONS.".Captain = ".USERID.")";

				$result = $sql->db_Query($q);
				$numDivs = mysql_numrows($result);
				if($numDivs > 0)
				{
					$userIsDivisionCaptain = TRUE;
					for($i=0;$i < $numDivs;$i++)
					{
						$div_name  = mysql_result($result,$i, TBL_CLANS.".Name");
						$div_id    = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");

						// Is the division signed up
						$q_2 = "SELECT ".TBL_TEAMS.".*"
						." FROM ".TBL_TEAMS
						." WHERE (".TBL_TEAMS.".Event = '$event_id')"
						." AND (".TBL_TEAMS.".Division = '$div_id')";
						$result_2 = $sql->db_Query($q_2);
						$numTeams = mysql_numrows($result_2);

						$text .= '<tr>';
						$text .= '<td>'.EB_EVENT_L7.'&nbsp;'.$div_name.'</td>';
						if( $numTeams == 0)
						{
							if ($event->getField('password') != "")
							{
								$text .= '<td>'.EB_EVENT_L8.'<span class="required">*</span></td>';
								$text .= '<td>
								<form action="'.e_PLUGIN.'ebattles/eventinfo_process.php?eventid='.$event_id.'" method="post">
								<div>
								<input class="tbox required" type="password" title="'.EB_EVENT_L9.'" name="joinEventPassword"/>
								<input type="hidden" name="division" value="'.$div_id.'"/>
								'.ebImageTextButton('teamjoinevent', 'user_add.png', EB_EVENT_L10).'
								</div>
								';
								$text .= '</form>';
								$text .= '</td>';
							}
							else
							{
								$text .= '<td>'.EB_EVENT_L11.'</td>';
								$text .= '<td>
								<form action="'.e_PLUGIN.'ebattles/eventinfo_process.php?eventid='.$event_id.'" method="post">
								<div>
								<input type="hidden" name="joinEventPassword" value=""/>
								<input type="hidden" name="division" value="'.$div_id.'"/>
								'.ebImageTextButton('teamjoinevent', 'user_add.png', EB_EVENT_L12).'
								</div>
								';
								$text .= '</form>';
								$text .= '</td>';
							}
						}
						else
						{
							// Team signed up.
							$text .= '<td>'.EB_EVENT_L13.'</td>';
						}
						$text .= '</tr>';
					}
				}
			}

			switch($event->getField('Type'))
			{
				case "Clan Tournament":
				// Is the user a member of a division for that game?
				$q_2 = "SELECT ".TBL_CLANS.".*, "
				.TBL_MEMBERS.".*, "
				.TBL_DIVISIONS.".*, "
				.TBL_GAMES.".*, "
				.TBL_USERS.".*"
				." FROM ".TBL_CLANS.", "
				.TBL_MEMBERS.", "
				.TBL_DIVISIONS.", "
				.TBL_GAMES.", "
				.TBL_USERS
				." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
				." AND (".TBL_GAMES.".GameID = '$egameid')"
				." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
				." AND (".TBL_USERS.".user_id = ".USERID.")"
				." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
				." AND (".TBL_MEMBERS.".User = ".USERID.")";

				$result_2 = $sql->db_Query($q_2);
				$numMembers = mysql_numrows($result_2);
				if(!$result_2 || ( $numMembers == 0))
				{
					$text .= '<tr><td>'.EB_EVENT_L14.'</td>';
					$text .= '<td></td></tr>';
				}
				else
				{
					for($i=0;$i < $numMembers;$i++)
					{
						$clan_name  = mysql_result($result_2,$i , TBL_CLANS.".Name");
						$div_id  = mysql_result($result_2,$i , TBL_DIVISIONS.".DivisionID");
						$q_3 = "SELECT ".TBL_DIVISIONS.".*, "
						.TBL_USERS.".*"
						." FROM ".TBL_DIVISIONS.", "
						.TBL_USERS
						." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
						." AND (".TBL_USERS.".user_id = ".TBL_DIVISIONS.".Captain)";
						$result_3 = $sql->db_Query($q_3);
						if($result_3)
						{
							$captain_name  = mysql_result($result_3,0, TBL_USERS.".user_name");
							$captain_id  = mysql_result($result_3,0, TBL_USERS.".user_id");
						}

						$q_3 = "SELECT ".TBL_CLANS.".*, "
						.TBL_TEAMS.".*, "
						.TBL_DIVISIONS.".* "
						." FROM ".TBL_CLANS.", "
						.TBL_TEAMS.", "
						.TBL_DIVISIONS
						." WHERE (".TBL_DIVISIONS.".DivisionID = '$div_id')"
						." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
						." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
						." AND (".TBL_TEAMS.".Event = '$event_id')";
						$result_3 = $sql->db_Query($q_3);
						if(!$result_3 || (mysql_numrows($result_3) == 0))
						{
							if ($captain_id != USERID)
							{
								$text .= '<tr><td>'.EB_EVENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_EVENT_L16.'</td>';
								$text .= '<td>'.EB_EVENT_L17.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$captain_id.'">'.$captain_name.'</a>.</td></tr>';
							}
						}
						else
						{
							$team_id  = mysql_result($result_3,0 , TBL_TEAMS.".TeamID");
							$text .= '<tr><td>'.EB_EVENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_EVENT_L18.'</td>';

							// Is the user already signed up with that team?
							$q = "SELECT ".TBL_PLAYERS.".*"
							." FROM ".TBL_PLAYERS.", "
							.TBL_GAMERS
							." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
							."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
							."   AND (".TBL_GAMERS.".User = ".USERID.")"
							."   AND (".TBL_PLAYERS.".Team = '$team_id')";
							$result = $sql->db_Query($q);
							if(!$result || (mysql_numrows($result) == 0))
							{
								$text .= '<td>
								<form action="'.e_PLUGIN.'ebattles/eventinfo_process.php?eventid='.$event_id.'" method="post">
								<div>
								<input type="hidden" name="team" value="'.$team_id.'"/>
								'.ebImageTextButton('jointeamevent', 'user_add.png', EB_EVENT_L19).'
								</div>
								</form></td>
								';
							}
							else
							{
								$user_pid  = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
								$user_banned  = mysql_result($result,0 , TBL_PLAYERS.".Banned");

								if ($user_banned)
								{
									$text .= '<td>'.EB_EVENT_L20.'<br />
									'.EB_EVENT_L21.'</td>';
								}
								else
								{
									// Player signed up
									$text .= '<td>'.EB_EVENT_L22.'</td>';

									// Player can quit an event if he has not played yet
									$q = "SELECT ".TBL_PLAYERS.".*"
									." FROM ".TBL_PLAYERS.", "
									.TBL_SCORES
									." WHERE (".TBL_PLAYERS.".PlayerID = '$user_pid')"
									." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
									$result = $sql->db_Query($q);
									$nbrscores = mysql_numrows($result);

									// TODO: change conditions to quit
									if (($nbrscores == 0)&&($user_banned!=1)&&($event->getField('Type')!="Clan Ladder"))
									{
										$text .= '<td>
										<form action="'.e_PLUGIN.'ebattles/eventinfo_process.php?eventid='.$event_id.'" method="post">
										<div>
										<input type="hidden" name="player" value="'.$user_pid.'"/>
										'.ebImageTextButton('quitevent', 'user_delete.ico', EB_EVENT_L23, 'negative jq-button', EB_EVENT_L24).'
										</div>
										</form></td>
										';
									}
									else
									{
										$text .= '<td></td>';
									}
								}
							}
							$text .= '</tr>';
						}
					}
				}
				break;
				case "One Player Tournament":
				// Find gamer for that user
				$q = "SELECT ".TBL_GAMERS.".*"
				." FROM ".TBL_GAMERS
				." WHERE (".TBL_GAMERS.".Game = '".$event->getField('Game')."')"
				."   AND (".TBL_GAMERS.".User = ".USERID.")";
				$result = $sql->db_Query($q);
				$num_rows = mysql_numrows($result);
				if ($num_rows!=0)
				{
					$gamerID = mysql_result($result,0 , TBL_GAMERS.".GamerID");
					$gamer = new Gamer($gamerID);
					$gamerName = $gamer->getField('Name');
					$gamerUniqueGameID = $gamer->getField('UniqueGameID');
				}
				else
				{
					$gamerID = 0;
					$gamerName = '';
					$gamerUniqueGameID = '';
				}

				// Is the user already signed up?
				$q = "SELECT ".TBL_PLAYERS.".*"
				." FROM ".TBL_PLAYERS.", "
				.TBL_GAMERS
				." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
				."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				."   AND (".TBL_GAMERS.".User = ".USERID.")";
				$result = $sql->db_Query($q);
				if(!$result || (mysql_numrows($result) < 1))
				{
					$hide_password = ($event->getField('password') == "") ?  'hide ignore' : '';

					$text .= '<tr><td style="text-align:right">
					<div>
					'.ebImageTextButton('joinevent', 'user_add.png', EB_EVENT_L19, '', '', EB_EVENT_L28).'
					</div>
					';

					$text .= gamerEventSignupModalForm($event_id, $gamerID, $gamerName, $gamerUniqueGameID, $hide_password);
					$text .= '</td></tr>';
				}
				else
				{
					$user_pid  = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
					$user_banned  = mysql_result($result,0 , TBL_PLAYERS.".Banned");

					if ($user_banned)
					{
						$text .= '<tr><td>'.EB_EVENT_L29.'<br />
						'.EB_EVENT_L30.'</td><td></td></tr>';
					}
					else
					{
						$text .= '<tr><td>'.EB_EVENT_L31.'</td>';

						// Player can quit an event if he has not played yet
						$q = "SELECT ".TBL_PLAYERS.".*"
						." FROM ".TBL_PLAYERS.", "
						.TBL_SCORES
						." WHERE (".TBL_PLAYERS.".PlayerID = '$user_pid')"
						." AND (".TBL_SCORES.".Player = ".TBL_PLAYERS.".PlayerID)";
						$result = $sql->db_Query($q);
						$nbrscores = mysql_numrows($result);
						// TODO: change conditions to quit
						if ($nbrscores == 0)
						{
							$text .= '<td style="text-align:right">
							<form action="'.e_PLUGIN.'ebattles/eventinfo_process.php?eventid='.$event_id.'" method="post">
							<div>
							<input type="hidden" name="player" value="'.$user_pid.'"/>
							'.ebImageTextButton('quitevent', 'user_delete.ico', EB_EVENT_L32, 'negative jq-button', EB_EVENT_L33).'
							</div>
							</form></td></tr>
							';
						}
						else
						{
							$text .= '<td></td></tr>';
						}
					}
				}
				break;
				default:
			}
		}
		else
		{
			$text .=  '<tr><td>'.EB_EVENT_L75.'</td></tr>';
		}
	}
	else
	{
		$text .= '<tr><td>'.EB_EVENT_L34.'</td>';
		$text .= '<td></td></tr>';
	}
	$text .= '</tbody></table>';
}
else
{
	$text .= EB_EVENT_L75;
}

/* Info */
$text .= '<table class="eb_table" style="width:95%"><tbody>';

$text .= '<tr>';
$text .= '<td class="eb_td eb_tdc1">'.EB_EVENT_L36.'</td>';
$text .= '<td class="eb_td" style="font-variant:small-caps"><b>'.$event->getField('Name').'</b></td>';
$text .= '</tr>';

$text .= '<tr>';
$text .= '<td class="eb_td eb_tdc1">'.EB_EVENT_L37.'</td>';
$text .= '<td class="eb_td">'.(($event->getField('MatchType')!='') ? $event->getField('MatchType').' - ' : '').$event->eventTypeToString().'</td>';
$text .= '</tr>';

$text .= '<tr>';
$text .= '<td class="eb_td eb_tdc1">'.EB_EVENT_L38.'</td>';
$text .= '<td class="eb_td"><img '.getGameIconResize($egameicon).'/> '.$egame.'</td>';
$text .= '</tr>';

$text .= '<tr>';
$text .= '<td class="eb_td eb_tdc1">'.EB_EVENT_L39.'</td>';
$text .= '<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$eowner.'">'.$eownername.'</a>';
$text .= '</td></tr>';

$text .= '<tr>';
$q = "SELECT ".TBL_EVENTMODS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_EVENTMODS.", "
.TBL_USERS
." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
."   AND (".TBL_USERS.".user_id = ".TBL_EVENTMODS.".User)";
$result = $sql->db_Query($q);
$numMods = mysql_numrows($result);
$text .= '<td class="eb_td eb_tdc1">'.EB_EVENT_L41.'</td>';
$text .= '<td class="eb_td">';
if ($numMods>0)
{
	$text .= '<ul>';
	for($i=0; $i< $numMods; $i++){
		$modid  = mysql_result($result,$i, TBL_USERS.".user_id");
		$modname  = mysql_result($result,$i, TBL_USERS.".user_name");
		$text .= '<li><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$modid.'">'.$modname.'</a></li>';
	}
	$text .= '</ul>';
}
$text .= '</td></tr>';

$text .= '<tr><td class="eb_td eb_tdc1">'.EB_EVENT_L82.'</td><td class="eb_td">'.$event->eventStatusToString().'</td></tr>';
$time_comment = $event->eventStatusToTimeComment();
$text .= '<tr><td class="eb_td eb_tdc1">'.EB_EVENT_L42.'</td><td class="eb_td">'.$date_start.'</td></tr>';
$text .= '<tr><td class="eb_td eb_tdc1"></td><td class="eb_td">'.$time_comment.'</td></tr>';
$text .= '<tr><td class="eb_td eb_tdc1">'.EB_EVENTM_L36.'</td><td class="eb_td">'.$tp->toHTML($event->getField('Description'), true).'</td></tr>';
$text .= '<tr><td class="eb_td eb_tdc1">'.EB_EVENT_L44.'</td><td class="eb_td">'.$tp->toHTML($event->getField('Rules'), true).'</td></tr>';
$text .= '</tbody></table>';
$text .= '</div>';    // tabs-1 "Info"

/* Teams Standings */
$can_approve = 0;
$can_report = 0;
$can_schedule = 0;
$can_report_quickloss = 0;
$can_submit_replay = 0;
$can_challenge = 0;
$userclass = 0;
// Check if user can report
// Is the user admin?
if (check_class($pref['eb_mod_class']))
{
	$userclass |= eb_UC_EB_MODERATOR;
	$can_report = 1;
	$can_submit_replay = 1;
	$can_schedule = 1;
	$can_approve = 1;
}

// Is the user event owner?
if (USERID==$eowner)
{
	$userclass |= eb_UC_EVENT_OWNER;
	$can_report = 1;
	$can_submit_replay = 1;
	$can_schedule = 1;
	$can_approve = 1;
}
// Is the user a moderator?
$q_2 = "SELECT ".TBL_EVENTMODS.".*"
." FROM ".TBL_EVENTMODS
." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
."   AND (".TBL_EVENTMODS.".User = ".USERID.")";
$result_2 = $sql->db_Query($q_2);
$numMods = mysql_numrows($result_2);
if ($numMods>0)
{
	$userclass |= eb_UC_EVENT_MODERATOR;
	$can_report = 1;
	$can_submit_replay = 1;
	$can_schedule = 1;
	$can_approve = 1;
}
/*
if ($userIsDivisionCaptain == TRUE)
{
$userclass |= eb_UC_EVENT_PLAYER;
$can_report = 1;
}
*/

// Is the user a player?
$q = "SELECT ".TBL_PLAYERS.".*"
." FROM ".TBL_PLAYERS.", "
.TBL_GAMERS
." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
."   AND (".TBL_GAMERS.".User = ".USERID.")";
$result = $sql->db_Query($q);

$pbanned=0;
if(mysql_numrows($result) == 1)
{
	$userclass |= eb_UC_EVENT_PLAYER;

	// Is the event started, and not ended
	if ($event->getField('Status') == 'active')
	{
		$can_report = 1;
		$can_report_quickloss = 0;
		$can_submit_replay = 1;
		$can_challenge = 0;
	}
}

switch($event->getField('Type'))
{
	case "One Player Tournament":
	if (($nbrplayersNotBanned < 2)||($pbanned))
	{
	}
	$can_report = 0;
	$can_report_quickloss = 0;
	$can_schedule = 0;
	$can_challenge = 0;
	//sc2:
	$can_submit_replay = 0;
	break;
	case "Clan Tournament":
	if ($nbrteams < 2)
	{
	}
	$can_report = 0;
	$can_report_quickloss = 0;
	$can_schedule = 0;
	$can_challenge = 0;
	//sc2:
	$can_submit_replay = 0;
	break;
	default:
}

// check if only 1 player with this userid
$q = "SELECT DISTINCT ".TBL_PLAYERS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_PLAYERS.", "
.TBL_GAMERS.", "
.TBL_USERS
." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
."   AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
."   AND (".TBL_USERS.".user_id = ".USERID.")";
$result = $sql->db_Query($q);
$numPlayers = mysql_numrows($result);
if ($numPlayers>1)
$can_report_quickloss = 0;

// Check if AllowScore is set
if ($event->getField('AllowScore')==TRUE)
$can_report_quickloss = 0;

if($event->getField('Type') == "Clan Ladder") $can_report_quickloss = 0;  // Disable quick loss report for clan wars for now
if($event->getField('quick_loss_report')==FALSE) $can_report_quickloss = 0;
if($userclass < $event->getField('match_report_userclass')) $can_report = 0;
if($userclass < $event->getField('match_replay_report_userclass')) $can_submit_replay = 0;

if($userclass < $event->getField('MatchesApproval')) $can_approve = 0;
if($event->getField('MatchesApproval') == eb_UC_NONE) $can_approve = 0;

if($event->getField('ChallengesEnable')==FALSE) $can_challenge= 0;

//fm: Need userclass for match scheduling

$nextupdate_timestamp_local_local = $nextupdate_timestamp_local + TIMEOFFSET;
$date_nextupdate = date("d M Y, h:i A",$nextupdate_timestamp_local_local);

if (($event->getField('Type') == "Team Ladder")||($event->getField('Type') == "Clan Ladder"))
{
	$text .= '<div id="tabs-2">';

	if (($time < $nextupdate_timestamp_local) && ($eventIsChanged == 1))
	{
		$text .= EB_EVENT_L46.'&nbsp;'.$date_nextupdate.'<br />';
	}


	$text .= '</div>';    // tabs-2 "Teams Standings"
}

/* Players Standings */
$text .= '<div id="tabs-3">';

if (($time < $nextupdate_timestamp_local) && ($eventIsChanged == 1))
{
	$text .= EB_EVENT_L50.'&nbsp;'.$date_nextupdate.'<br />';
}

list($bracket_html) = $event->brackets();
$text .= $bracket_html;

$text .= '</div>';    // tabs-3 "Brackets"

/* Matches */
$text .= '<div id="tabs-4">';
$q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
." FROM ".TBL_MATCHS.", "
.TBL_SCORES
." WHERE (".TBL_MATCHS.".Event = '$event_id')"
." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
." AND (".TBL_MATCHS.".Status = 'pending')";
$result = $sql->db_Query($q);
$row = mysql_fetch_array($result);
$nbrMatchesPending = $row['NbrMatches'];
if ($nbrMatchesPending == 0) $can_approve = 0;

/* Display Match Report buttons */
if(($can_report_quickloss != 0)||($can_report != 0)||($can_submit_replay != 0)||($can_schedule != 0))
{
	$text .= '<table>';
	$text .= '<tr>';
	if($can_submit_replay != 0)
	{
		$text .= '<td>';
		$text .= '<form action="'.e_PLUGIN.'ebattles/submitreplay.php?eventid='.$event_id.'" method="post"><div>';
		$text .= ebImageTextButton('submitreplay', 'flag_red.png', EB_EVENT_L81);
		$text .= '</div></form>';
		$text .= '</td>';
	}
	if($can_report_quickloss != 0)
	{
		$text .= '<td>';
		$text .= '<form action="'.e_PLUGIN.'ebattles/quickreport.php?eventid='.$event_id.'" method="post"><div>';
		$text .= ebImageTextButton('quicklossreport', 'flag_red.png', EB_EVENT_L56);
		$text .= '</div></form>';
		$text .= '</td>';
	}
	if($can_report != 0)
	{
		$text .= '<td>';
		$text .= '<form action="'.e_PLUGIN.'ebattles/matchreport.php?eventid='.$event_id.'" method="post">';
		$text .= '<div>';
		$text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
		$text .= ebImageTextButton('matchreport', 'page_white_edit.png', EB_EVENT_L57);
		$text .= '</div>';
		$text .= '</form>';
		$text .= '</td>';
	}
	if($can_schedule != 0)
	{
		$text .= '<td>';
		$text .= '<form action="'.e_PLUGIN.'ebattles/matchreport.php?eventid='.$event_id.'" method="post">';
		$text .= '<div>';
		$text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
		$text .= ebImageTextButton('matchschedule', 'add.png', EB_EVENT_L72);
		$text .= '</div>';
		$text .= '</form>';
		$text .= '</td>';
	}
	$text .= '</tr>';
	$text .= '</table>';
}
$text .= '<br />';

/* Display Active Matches */
$rowsPerPage = $pref['eb_default_items_per_page'];

$q = "SELECT COUNT(DISTINCT ".TBL_MATCHS.".MatchID) as NbrMatches"
." FROM ".TBL_MATCHS.", "
.TBL_SCORES
." WHERE (Event = '$event_id')"
." AND (".TBL_MATCHS.".Status = 'active')"
." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)";
$result = $sql->db_Query($q);

$row = mysql_fetch_array($result);
$numMatches = $row['NbrMatches'];

$text .= '<p><b>';
$text .= $numMatches.'&nbsp;'.EB_EVENT_L59;
if ($numMatches>$rowsPerPage)
{
	$text .= ' [<a href="'.e_PLUGIN.'ebattles/eventmatchs.php?eventid='.$event_id.'">'.EB_EVENT_L60.'</a>]';
}
$text .= '</b></p>';
$text .= '<br />';

$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
." FROM ".TBL_MATCHS.", "
.TBL_SCORES.", "
.TBL_USERS
." WHERE (".TBL_MATCHS.".Event = '$event_id')"
." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
." AND (".TBL_MATCHS.".Status = 'active')"
." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
." LIMIT 0, $rowsPerPage";
$result = $sql->db_Query($q);
$numMatches = mysql_numrows($result);

if ($numMatches>0)
{
	/* Display table contents */
	$text .= '<table class="table_left">';
	for($i=0; $i < $numMatches; $i++)
	{
		$match_id  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
		$match = new Match($match_id);
		$text .= $match->displayMatchInfo(eb_MATCH_NOEVENTINFO);
	}
	$text .= '</table>';
}

$text .= '<br />';

/* Display Pending Matches */
$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
." FROM ".TBL_MATCHS.", "
.TBL_SCORES.", "
.TBL_USERS
." WHERE (".TBL_MATCHS.".Event = '$event_id')"
." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
." AND (".TBL_MATCHS.".Status = 'pending')"
." ORDER BY ".TBL_MATCHS.".TimeReported DESC";
$result = $sql->db_Query($q);
$numMatches = mysql_numrows($result);

if ($numMatches>0)
{
	$text .= '<p><b>';
	$text .= $numMatches.'&nbsp;'.EB_EVENT_L64;
	$text .= '</b></p>';
	$text .= '<br />';

	/* Display table contents */
	$text .= '<table class="table_left">';
	for($i=0; $i < $numMatches; $i++)
	{
		$match_id  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
		$match = new Match($match_id);
		$text .= $match->displayMatchInfo(eb_MATCH_NOEVENTINFO);
	}
	$text .= '</table>';
}

/* Display Scheduled Matches */
$text .= '<br />';

$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
." FROM ".TBL_MATCHS.", "
.TBL_SCORES
." WHERE (".TBL_MATCHS.".Event = '$event_id')"
." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
." AND (".TBL_MATCHS.".Status = 'scheduled')"
." ORDER BY ".TBL_MATCHS.".TimeReported DESC";
$result = $sql->db_Query($q);
$numMatches = mysql_numrows($result);
if ($numMatches>0)
{
	$text .= '<p><b>';
	$text .= $numMatches.'&nbsp;'.EB_EVENT_L70;
	$text .= '</b></p>';
	$text .= '<br />';

	/* Display table contents */
	$text .= '<table class="table_left">';
	for($i=0; $i < $numMatches; $i++)
	{
		$match_id  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
		$match = new Match($match_id);
		$text .= $match->displayMatchInfo(eb_MATCH_NOEVENTINFO|eb_MATCH_SCHEDULED);
	}
	$text .= '</table>';
}

$text .= '</div>';    // tabs-4 "Matches"

$text .= '<div id="tabs-5">';
switch($event->getField('Type'))
{
	case "One Player Tournament":
	// Show list of players
	$q = "SELECT DISTINCT ".TBL_PLAYERS.".*, "
	.TBL_GAMERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_GAMERS.", "
	.TBL_USERS
	." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
	."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	."   AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)";
	$result = $sql->db_Query($q);
	$numPlayers = mysql_numrows($result);
	if ($numPlayers>0)
	{
		/*Name	Unique ID*/
		$text .= '<table style="width:90%"><tbody>';
		$text .= '<tr>';
		//sc2:	$text .= '<th class="eb_th2">'.EB_EVENT_L78.'</th>';
		$text .= '<th class="eb_th2">'.EB_EVENT_L79.'</th>';
		$text .= '<th class="eb_th2">'.EB_EVENT_L80.'</th>';
		$text .= '</tr>';
		for ($player = 0; $player < $numPlayers; $player++)
		{
			/* sc2:
			$pFactionIcon = mysql_result($result, $player , TBL_FACTIONS.".Icon");
			$pFactionName = mysql_result($result, $player , TBL_FACTIONS.".Name");
			if($pFactionName){
			$pFactionImage = ' <img '.getFactionIconResize($fIcon).' title="'.$fName.'" style="vertical-align:middle"/>';
			} else {
			$pFactionImage = '';
			}
			*/
			$puid = mysql_result($result, $player , TBL_GAMERS.".User");
			$pName = mysql_result($result, $player , TBL_GAMERS.".Name");
			$pGamer = mysql_result($result, $player , TBL_GAMERS.".UniqueGameID");

			$text .= '<tr>';
			//sc2: $text .= '<td class="eb_td">'.$pFactionImage.'</td>';
			$text .= '<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pName.'</a></td>';
			$text .= '<td class="eb_td">'.$pGamer.'</td>';
			$text .= '</tr>';
		}
		$text .= '</tbody></table>';
	}
	break;
	case "Clan Tournament":
	// Show list of teams
	$q_Teams = "SELECT ".TBL_CLANS.".*, "
	.TBL_TEAMS.".*, "
	.TBL_DIVISIONS.".* "
	." FROM ".TBL_CLANS.", "
	.TBL_TEAMS.", "
	.TBL_DIVISIONS
	." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
	." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
	." AND (".TBL_TEAMS.".Event = '$event_id')";
	$result = $sql->db_Query($q_Teams);
	$numTeams = mysql_numrows($result);
	if($numTeams>0)
	{
		$text .= '<table class="eb_table" style="width:95%"><tbody>';
		$text .= '<tr>
		<th class="eb_th2">'.EB_CLANS_L5.'</th>
		<th class="eb_th2">'.EB_CLANS_L6.'</th>
		</tr>';
		for($i=0; $i < $numTeams; $i++){
			// TODO: use Clan
			$clan_id  = mysql_result($result,$i, TBL_CLANS.".ClanID");
			$clan = new Clan($clan_id);

			$image = "";
			if ($pref['eb_avatar_enable_teamslist'] == 1)
			{
				if($clan->getField('Image'))
				{
					$image = '<img '.getAvatarResize(getImagePath($clan->getField('Image'), 'team_avatars')).' style="vertical-align:middle"/>';
				} else if ($pref['eb_avatar_default_team_image'] != ''){
					$image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_team_image'], 'team_avatars')).' style="vertical-align:middle"/>';
				}
			}

			$text .= '<tr>
			<td class="eb_td">'.$image.'&nbsp;<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$clan_id.'">'.$clan->getField('Name').'</a></td>
			<td class="eb_td">'.$clan->getField('Tag').'</td>
			</tr>';
		}
		$text .= '</tbody></table>';
	}
	break;
	default:
}


$text .= '<br />';

$text .= '</div>';    // tabs-5 "Players"
$text .= '</div>';    // tabs

$text .= disclaimer();

?>

