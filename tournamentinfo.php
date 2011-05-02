<?php
/**
* tournamentinfo.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/show_array.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/match.php");
require_once(e_PLUGIN."ebattles/include/gamer.php");
require_once(e_PLUGIN."ebattles/include/brackets.php");

/*******************************************************************
********************************************************************/

require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");

$pages = new Paginator;

$text .= '
<script type="text/javascript" src="./js/signup.js"></script>
';
$text .= "
<script type='text/javascript'>
<!--//
	// Forms
	$(function() {
		//$( '#submit, #teamjointournament, #teamjointournament, #jointeamtournament, #quittournament, #jointournament, #quittournament, #matchreport' ).button();
		$( 'button' ).button();
	});
//-->
</script>
";
if (!isset($_GET['orderby'])) $_GET['orderby'] = 1;
$orderby=$_GET['orderby'];

$sort = "DESC";
if(isset($_GET["sort"]) && !empty($_GET["sort"]))
{
	$sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
	$sort_type = ($_GET["sort"]=="ASC") ? SORT_ASC : SORT_DESC;
}

/* Tournament Name */
$tournament_id = $_GET['TournamentID'];

if (!$tournament_id)
{
	header("Location: ./tournaments.php");
	exit();
}
else
{
	$file = 'cache/sql_cache_tournament_'.$tournament_id.'.txt';
	$file_team = 'cache/sql_cache_tournament_team_'.$tournament_id.'.txt';

	$q = "SELECT ".TBL_TOURNAMENTS.".*, "
	.TBL_GAMES.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_TOURNAMENTS.", "
	.TBL_GAMES.", "
	.TBL_USERS
	." WHERE (".TBL_TOURNAMENTS.".TournamentID = '$tournament_id')"
	."   AND (".TBL_TOURNAMENTS.".Game = ".TBL_GAMES.".GameID)"
	."   AND (".TBL_USERS.".user_id = ".TBL_TOURNAMENTS.".Owner)";
	$result = $sql->db_Query($q);

	$tournament = new Tournament($tournament_id);

	$rounds = unserialize($tournament->getField('Rounds'));
	$egame = mysql_result($result,0 , TBL_GAMES.".Name");
	$egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
	$egameicon = mysql_result($result,0 , TBL_GAMES.".Icon");
	$eowner = mysql_result($result,0 , TBL_USERS.".user_id");
	$eownername = mysql_result($result,0 , TBL_USERS.".user_name");

	$tournamentIsChanged = $tournament->getField('IsChanged');

	if ($pref['eb_update_delay_enable'] == 1)
	{
		$eneedupdate = 0;
	}
	else
	{
		// Force always update
		$eneedupdate = 1;
	}

	/*
	if (
	(($time > $nextupdate_timestamp_local) && ($tournamentIsChanged == 1))
	||(file_exists($file) == FALSE)
	||((file_exists($file_team) == FALSE) && (($tournament->getField('Type') == "Team Tournament")||($tournament->getField('Type') == "ClanWar")))
	)
	{
	$eneedupdate = 1;
	}
	*/

	if($tournament->getField('StartDateTime')!=0)
	{
		$start_timestamp_local = $tournament->getField('StartDateTime') + TIMEOFFSET;
		$date_start = date("d M Y, h:i A",$start_timestamp_local);
	}
	else
	{
		$date_start = "-";
	}
	/*
	if($tournament->getField('End_timestamp')!=0)
	{
	$end_timestamp_local = $tournament->getField('End_timestamp') + TIMEOFFSET;
	$date_end = date("d M Y, h:i A",$end_timestamp_local);
	}
	else
	{
	$date_end = "-";
	}
	*/

	$time_comment = '';
	if (  ($tournament->getField('StartDateTime') != 0)
	&&($time <= $tournament->getField('StartDateTime'))
	)
	{
		$time_comment = EB_TOURNAMENT_L2.'&nbsp;'.get_formatted_timediff($time, $tournament->getField('StartDateTime'));
	}
	/*
	else if (  ($tournament->getField('End_timestamp') != 0)
	&&($time <= $tournament->getField('End_timestamp'))
	)
	{
	$time_comment = EB_TOURNAMENT_L3.'&nbsp;'.get_formatted_timediff($time, $tournament->getField('End_timestamp'));
	}
	else if (  ($tournament->getField('End_timestamp') != 0)
	&&($time > $tournament->getField('End_timestamp'))
	)
	{
	$time_comment = EB_TOURNAMENT_L4;
	}
	*/

	/* Nbr players */
	$q = "SELECT COUNT(*) as NbrPlayers"
	." FROM ".TBL_TPLAYERS
	." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbrplayers = $row['NbrPlayers'];

	$q = "SELECT COUNT(*) as NbrPlayers"
	." FROM ".TBL_TPLAYERS
	." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
	."   AND (".TBL_TPLAYERS.".Banned != 1)";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbrplayersNotBanned = $row['NbrPlayers'];

	/* Nbr Teams */
	$q = "SELECT COUNT(*) as NbrTeams"
	." FROM ".TBL_TEAMS
	." WHERE (Tournament = '$tournament_id')";
	$result = $sql->db_Query($q);
	$row = mysql_fetch_array($result);
	$nbrteams = $row['NbrTeams'];

	/* Update Stats */
	if ($eneedupdate == 1)
	{
		$new_nextupdate = $time + 60*$pref['eb_update_delay'];
		$q = "UPDATE ".TBL_TOURNAMENTS." SET NextUpdate_timestamp = $new_nextupdate WHERE (TournamentID = '$tournament_id')";
		$result = $sql->db_Query($q);
		$nextupdate_timestamp_local = $new_nextupdate;

		$q = "UPDATE ".TBL_TOURNAMENTS." SET IsChanged = 0 WHERE (TournamentID = '$tournament_id')";
		$result = $sql->db_Query($q);
		$tournamentIsChanged = 0;

		// TODO: update brackets here
		/*
		switch($tournament->getField('Type'))
		{
		case "One Player Tournament":
		updateStats($tournament_id, $time, TRUE);
		break;
		case "Team Tournament":
		updateStats($tournament_id, $time, TRUE);
		updateTeamStats($tournament_id, $time, TRUE);
		case "ClanWar":
		updateTeamStats($tournament_id, $time, TRUE);
		break;
		default:
		}
		*/

	}

	$text .= '<div id="tabs">';
	$text .= '<ul>';
	$text .= '<li><a href="#tabs-1">'.EB_TOURNAMENT_L35.'</a></li>'; /* Signup, Join/Quit Tournament */
	if (($tournament->getField('MatchType') == "2v2")||($tournament->getField('MatchType') == "3v3"))
	{
		$text .= '<li><a href="#tabs-2">'.EB_TOURNAMENT_L45.'</a></li>';
	}
	$text .= '<li><a href="#tabs-3">'.EB_TOURNAMENT_L49.'</a></li>';
	$text .= '<li><a href="#tabs-4">'.EB_TOURNAMENT_L58.'</a></li>';
	$text .= '<li><a href="#tabs-5">'.EB_TOURNAMENT_L63.'</a></li>';
	$text .= '</ul>';


	/* Signup, Join/Quit Tournament */
	$text .= '<div id="tabs-1">';
	$text .= '<table style="width:95%"><tbody>';
	$text .= '<tr>';
	
	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$eowner) $can_manage = 1;
	if ($can_manage == 1)
	$text .= '<td>
	<form action="'.e_PLUGIN.'ebattles/tournamentmanage.php?TournamentID='.$tournament_id.'" method="post">
	'.ebImageTextButton('submit', 'page_white_edit.png', EB_TOURNAMENT_L40).'
	</form>';

	$userIsDivisionCaptain = FALSE;
	if(check_class(e_UC_MEMBER))
	{
		// If logged in
		if(($tournament->getField('StartDateTime') == 0) || ($time < $tournament->getField('StartDateTime')))
		{
			// If tournament is not finished
			if (($tournament->getField('MatchType') == "2v2")||($tournament->getField('MatchType') == "3v3"))
			{
				// Find if user is captain of a division playing that game
				// if yes, propose to join this tournament
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
						." WHERE (".TBL_TEAMS.".Tournament = '$tournament_id')"
						." AND (".TBL_TEAMS.".Division = '$div_id')";
						$result_2 = $sql->db_Query($q_2);
						$numTeams = mysql_numrows($result_2);

						$text .= '<td>'.EB_TOURNAMENT_L7.'&nbsp;'.$div_name.'</td>';
						if( $numTeams == 0)
						{

							if ($tournament->getField('Password') != "")
							{
								$text .= '<td>'.EB_TOURNAMENT_L8.'</td>';
								$text .= '<td>
								<form action="'.e_PLUGIN.'ebattles/tournamentinfo_process.php?TournamentID='.$tournament_id.'" method="post">
								<div>
								<input class="tbox" type="password" title="'.EB_TOURNAMENT_L9.'" name="joinTournamentPassword"/>
								<input type="hidden" name="division" value="'.$div_id.'"/>
								</div>
								'.ebImageTextButton('teamjointournament', 'user_add.png', EB_TOURNAMENT_L10).'
								';
								$text .= '</form>';
								$text .= '</td>';
							}
							else
							{
								$text .= '<td>'.EB_TOURNAMENT_L11.'</td>';
								$text .= '<td>
								<form action="'.e_PLUGIN.'ebattles/tournamentinfo_process.php?TournamentID='.$tournament_id.'" method="post">
								<div>
								<input type="hidden" name="joinTournamentPassword" value=""/>
								<input type="hidden" name="division" value="'.$div_id.'"/>
								</div>
								'.ebImageTextButton('teamjointournament', 'user_add.png', EB_TOURNAMENT_L12).'
								';
								$text .= '</form>';
								$text .= '</td>';
							}
						}
						else
						{
							// Team signed up.
							$text .= '<td>'.EB_TOURNAMENT_L13.'</td>';
						}
					}
				}
			}

			switch($tournament->getField('MatchType'))
			{
				case "2v2":
				case "3v3":
				case "4v4":
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
					$text .= '<td>'.EB_TOURNAMENT_L14.'</td>';
					$text .= '<td></td>';
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
						." AND (".TBL_TEAMS.".Tournament = '$tournament_id')";
						$result_3 = $sql->db_Query($q_3);
						if(!$result_3 || (mysql_numrows($result_3) == 0))
						{
							if ($captain_id != USERID)
							{
								$text .= '<td>'.EB_TOURNAMENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_TOURNAMENT_L16.'</td>';
								$text .= '<td>'.EB_TOURNAMENT_L17.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$captain_id.'">'.$captain_name.'</a>.</td>';
							}
						}
						else
						{
							$team_id  = mysql_result($result_3,0 , TBL_TEAMS.".TeamID");
							$text .= '<td>'.EB_TOURNAMENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_TOURNAMENT_L18.'</td>';

							// Is the user already signed up with that team?
							$q = "SELECT ".TBL_TPLAYERS.".*"
							." FROM ".TBL_TPLAYERS.", "
							.TBL_GAMERS
							." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
							."   AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
							."   AND (".TBL_GAMERS.".User = ".USERID.")"
							."   AND (".TBL_TPLAYERS.".Team = '$team_id')";
							$result = $sql->db_Query($q);
							if(!$result || (mysql_numrows($result) == 0))
							{
								$text .= '<td>
								<form action="'.e_PLUGIN.'ebattles/tournamentinfo_process.php?TournamentID='.$tournament_id.'" method="post">
								<div>
								<input type="hidden" name="team" value="'.$team_id.'"/>
								</div>
								'.ebImageTextButton('jointeamtournament', 'user_add.png', EB_TOURNAMENT_L19).'
								</form></td>
								';
							}
							else
							{
								$user_pid  = mysql_result($result,0 , TBL_TPLAYERS.".TPlayerID");
								$user_banned  = mysql_result($result,0 , TBL_TPLAYERS.".Banned");

								if ($user_banned)
								{
									$text .= '<td>'.EB_TOURNAMENT_L20.'<br />
									'.EB_TOURNAMENT_L21.'</td>';
								}
								else
								{
									// Player signed up
									$text .= '<td>'.EB_TOURNAMENT_L22.'</td>';

									// Player can quit an tournament if he has not played yet
									$q = "SELECT ".TBL_TPLAYERS.".*"
									." FROM ".TBL_TPLAYERS.", "
									.TBL_SCORES
									." WHERE (".TBL_TPLAYERS.".TPlayerID = '$user_pid')"
									." AND (".TBL_SCORES.".Player = ".TBL_TPLAYERS.".TPlayerID)";
									$result = $sql->db_Query($q);
									$nbrscores = mysql_numrows($result);

									// TODO: change conditions to quit
									$nbrscores = 0;
									if (($nbrscores == 0)&&($user_banned!=1)&&($tournament->getField('Type')!="ClanWar"))
									{
										$text .= '<td>
										<form action="'.e_PLUGIN.'ebattles/tournamentinfo_process.php?TournamentID='.$tournament_id.'" method="post">
										<div>
										<input type="hidden" name="player" value="'.$user_pid.'"/>
										'.ebImageTextButton('quittournament', 'user_delete.ico', EB_TOURNAMENT_L23, 'negative', EB_TOURNAMENT_L24).'
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
						}
					}
				}
				break;
				case "1v1":
				// Find gamer for that user
				$q = "SELECT ".TBL_GAMERS.".*"
				." FROM ".TBL_GAMERS
				." WHERE (".TBL_GAMERS.".Game = '".$tournament->getField('Game')."')"
				."   AND (".TBL_GAMERS.".User = ".USERID.")";
				$result = $sql->db_Query($q);
				$num_rows = mysql_numrows($result);
				if ($num_rows!=0)
				{
					$gamerID = mysql_result($result,0 , TBL_GAMERS.".GamerID");
					$gamer = new SC2Gamer($gamerID);
					$gamerCharacterName = $gamer->getGamerName();
					$gamerCharacterCode = $gamer->getGamerCode();
				}
				else
				{
					$gamerID = 0;
					$gamerCharacterName = '';
					$gamerCharacterCode = '';
				}

				// Is the user already signed up?
				$q = "SELECT ".TBL_TPLAYERS.".*"
				." FROM ".TBL_TPLAYERS.", "
				.TBL_GAMERS
				." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
				."   AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				."   AND (".TBL_GAMERS.".User = ".USERID.")";
				$result = $sql->db_Query($q);
				if(!$result || (mysql_numrows($result) < 1))
				{
					$hide_password = ($tournament->getField('password') == "") ?  'hide ignore' : '';
					
					$text .= '<td style="text-align:right;">
					'.ebImageTextButton('jointournament', 'user_add.png', EB_TOURNAMENT_L19, '', '', EB_TOURNAMENT_L28).'
					</td>
					';

					// Modal form
					$text .= '
					<div id="modal-form-signup" title="Sign Up">

					<!-- form validation error container -->
					<div class="ui-widget ui-helper-hidden" id="errorblock-div1">
					<div class="ui-state-error ui-corner-all" id="errorblock-div2" style="padding: 0pt 0.7em; display:none;">
					<p>
					<!-- fancy icon -->
					<span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
					<strong>Alert:</strong> Errors detected!
					</p>
					<!-- validation plugin will target this UL for error messages -->
					<ul></ul>
					</div>
					</div>

					<!-- our form, no buttons (buttons generated by jQuery UI dialog() function) -->
					<form action="tournamentinfo_process.php?TournamentID='.$tournament_id.'" name="form-signup" id="form-signup" method="post">
					<input type="hidden" name="jointournament" value=""/>
					<input type="hidden" name="gamerID" value="'.$gamerID.'"/>
					<fieldset>
					<label for="joinTournamentPassword" class="'.$hide_password.'">'.EB_TOURNAMENT_L27.'</label>
					<input type="password" name="joinTournamentPassword" id="joinTournamentPassword" class="'.$hide_password.' text ui-widget-content ui-corner-all" />

					<label for="charactername">Character Name</label>
					<input type="text" name="charactername" id="charactername" class="text ui-widget-content ui-corner-all" value="'.$gamerCharacterName.'"/>

					<label for="code">Code</label>
					<input type="text" name="code" id="code" class="text ui-widget-content ui-corner-all" value="'.$gamerCharacterCode.'"/>
					</fieldset>
					</form>
					</div>
					';
				}
				else
				{
					$user_pid  = mysql_result($result,0 , TBL_TPLAYERS.".TPlayerID");
					$user_banned  = mysql_result($result,0 , TBL_TPLAYERS.".Banned");

					if ($user_banned)
					{
						$text .= '<td>'.EB_TOURNAMENT_L29.'<br />
						'.EB_TOURNAMENT_L30.'</td><td></td>';
					}
					else
					{
						// Player can quit an tournament if he has not played yet
						$q = "SELECT ".TBL_TPLAYERS.".*"
						." FROM ".TBL_TPLAYERS.", "
						.TBL_SCORES
						." WHERE (".TBL_TPLAYERS.".TPlayerID = '$user_pid')"
						." AND (".TBL_SCORES.".Player = ".TBL_TPLAYERS.".TPlayerID)";
						$result = $sql->db_Query($q);
						$nbrscores = mysql_numrows($result);
						// TODO: change conditions to quit
						$nbrscores = 0;
						if ($nbrscores == 0)
						{
							$text .= '<td style="text-align:right;">
							<form action="'.e_PLUGIN.'ebattles/tournamentinfo_process.php?TournamentID='.$tournament_id.'" method="post">
							<div>
							<input type="hidden" name="player" value="'.$user_pid.'"/>
							'.ebImageTextButton('quittournament', 'user_delete.ico', EB_TOURNAMENT_L32, 'negative', EB_TOURNAMENT_L33, EB_TOURNAMENT_L31).'
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
				break;
				default:
			}
		}
	}
	else
	{
		$text .= '<td>'.EB_TOURNAMENT_L34.'</td>';
		$text .= '<td></td>';
	}
	$text .= '</tr>';
	$text .= '</tbody></table>';

	/* Info */
	$text .= '<table class="eb_table" style="width:95%"><tbody>';

	$text .= '<tr>';
	$text .= '<td class="eb_td1">'.EB_TOURNAMENT_L36.'</td>';
	$text .= '<td class="eb_td1"><b>'.$tournament->getField('Name').'</b></td>';
	$text .= '</tr>';

	$text .= '<tr>';
	$text .= '<td class="eb_td1">'.EB_TOURNAMENT_L37.'</td>';
	$text .= '<td class="eb_td1">'.$tournament->getField('MatchType').' - '.tournamentTypeToString($tournament->getField('Type')).'</td>';
	$text .= '</tr>';

	$text .= '<tr>';
	$text .= '<td class="eb_td1">'.EB_TOURNAMENT_L38.'</td>';
	$text .= '<td class="eb_td1"><img '.getGameIconResize($egameicon).'/> '.$egame.'</td>';
	$text .= '</tr>';

	$text .= '<tr>';
	$text .= '<td class="eb_td1">'.EB_TOURNAMENT_L39.'</td>';
	$text .= '<td class="eb_td1"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$eowner.'">'.$eownername.'</a>';
	$text .= '</td></tr>';

	$text .= '<tr>';
	$q = "SELECT ".TBL_MODS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_MODS.", "
	.TBL_USERS
	." WHERE (".TBL_MODS.".Tournament = '$tournament_id')"
	."   AND (".TBL_USERS.".user_id = ".TBL_MODS.".User)";
	$result = $sql->db_Query($q);
	$numMods = mysql_numrows($result);
	$text .= '<td class="eb_td1">'.EB_TOURNAMENT_L41.'</td>';
	$text .= '<td class="eb_td1">';
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

	$text .= '<tr><td class="eb_td1">'.EB_TOURNAMENT_L42.'</td><td class="eb_td1">'.$date_start.'</td></tr>';
	$text .= '<tr><td class="eb_td1"></td><td class="eb_td1">'.$time_comment.'</td></tr>';
	$text .= '<tr><td class="eb_td1">'.EB_TOURNAMENT_L44.'</td><td class="eb_td1">'.$tp->toHTML($tournament->getField('Rules'), true).'</td></tr>';
	$text .= '<tr><td class="eb_td1"></td><td class="eb_td1">'.$tp->toHTML($tournament->getField('Description'), true).'</td></tr>';
	$text .= '</tbody></table>';
	$text .= '</div>';    // tabs-1 "Info"

	/* Teams Standings */
	$can_approve = 0;
	$can_report = 0;
	$can_schedule = 0;
	$can_report_quickloss = 0;
	$can_challenge = 0;
	$userclass = 0;
	// Check if user can report
	// Is the user admin?
	if (check_class($pref['eb_mod_class']))
	{
		$userclass |= eb_UC_EB_MODERATOR;
		$can_report = 1;
		$can_schedule = 1;
		$can_approve = 1;
	}
	// Is the user tournament owner?
	if (USERID==$eowner)
	{
		$userclass |= eb_UC_TOURNAMENT_OWNER;
		$can_report = 1;
		$can_schedule = 1;
		$can_approve = 1;
	}
	// Is the user a moderator?
	$q_2 = "SELECT ".TBL_MODS.".*"
	." FROM ".TBL_MODS
	." WHERE (".TBL_MODS.".Tournament = '$tournament_id')"
	."   AND (".TBL_MODS.".User = ".USERID.")";
	$result_2 = $sql->db_Query($q_2);
	$numMods = mysql_numrows($result_2);
	if ($numMods>0)
	{
		$userclass |= eb_UC_TOURNAMENT_MODERATOR;
		$can_report = 1;
		$can_schedule = 1;
		$can_approve = 1;
	}
	/*
	if ($userIsDivisionCaptain == TRUE)
	{
	$userclass |= eb_UC_TOURNAMENT_PLAYER;
	$can_report = 1;
	}
	*/

	// Is the user a player?
	$q = "SELECT *"
	." FROM ".TBL_TPLAYERS
	." WHERE (Tournament = '$tournament_id')"
	."   AND (User = ".USERID.")";
	$result = $sql->db_Query($q);

	$pbanned=0;
	if(mysql_numrows($result) == 1)
	{
		$userclass |= eb_UC_TOURNAMENT_PLAYER;

		// Show link to my position
		$row = mysql_fetch_array($result);
		$prank = $row['Rank'];
		$pbanned = $row['Banned'];
	}

	switch($tournament->getField('Type'))
	{
		case "One Player Tournament":
		case "Team Tournament":
		if (($nbrplayersNotBanned < 2)||($pbanned))
		{
			$can_report = 0;
			$can_schedule = 0;
			$can_report_quickloss = 0;
			$can_challenge = 0;
		}
		break;
		case "ClanWar":
		if ($nbrteams < 2)
		{
			$can_report = 0;
			$can_schedule = 0;
			$can_report_quickloss = 0;
			$can_challenge = 0;
		}
		break;
		default:
	}

	// check if only 1 player with this userid
	$q = "SELECT DISTINCT ".TBL_TPLAYERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_TPLAYERS.", "
	.TBL_GAMERS.", "
	.TBL_USERS
	." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
	."   AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	."   AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
	."   AND (".TBL_USERS.".user_id = ".USERID.")";
	$result = $sql->db_Query($q);
	$numPlayers = mysql_numrows($result);
	if ($numPlayers>1)
	$can_report_quickloss = 0;

	// Check if AllowScore is set
	if ($tournament->getField('AllowScore')==TRUE)
	$can_report_quickloss = 0;

	if($tournament->getField('Type') == "ClanWar") $can_report_quickloss = 0;  // Disable quick loss report for clan wars for now
	if($tournament->getField('quick_loss_report')==FALSE) $can_report_quickloss = 0;
	if($userclass < $tournament->getField('match_report_userclass')) $can_report = 0;

	if($userclass < $tournament->getField('MatchesApproval')) $can_approve = 0;
	if($tournament->getField('MatchesApproval') == eb_UC_NONE) $can_approve = 0;

	if($tournament->getField('ChallengesEnable')==FALSE) $can_challenge= 0;

	//fm: Need userclass for match scheduling

	$nextupdate_timestamp_local_local = $nextupdate_timestamp_local + TIMEOFFSET;
	$date_nextupdate = date("d M Y, h:i A",$nextupdate_timestamp_local_local);

	if (($tournament->getField('Type') == "Team Tournament")||($tournament->getField('Type') == "ClanWar"))
	{
		$text .= '<div id="tabs-2">';

		if (($time < $nextupdate_timestamp_local) && ($tournamentIsChanged == 1))
		{
			$text .= EB_TOURNAMENT_L46.'&nbsp;'.$date_nextupdate.'<br />';
		}


		$text .= '</div>';    // tabs-2 "Teams Standings"
	}

	/* Players Standings */
	$text .= '<div id="tabs-3">';

	if (($time < $nextupdate_timestamp_local) && ($tournamentIsChanged == 1))
	{
		$text .= EB_TOURNAMENT_L50.'&nbsp;'.$date_nextupdate.'<br />';
	}

		$teams = array();
		switch($tournament->getField('MatchType'))
		{
			default:
			$q_Players = "SELECT ".TBL_GAMERS.".*"
			." FROM ".TBL_TPLAYERS.", "
			.TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
			." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY ".TBL_TPLAYERS.".Joined";
			$result = $sql->db_Query($q_Players);
			$nbrPlayers = mysql_numrows($result);
			for ($player = 0; $player < $nbrPlayers; $player++)
			{
				$gamerID = mysql_result($result,$player , TBL_GAMERS.".GamerID");
				$gamer = new Gamer($gamerID);
				$teams[$player]['Name'] = $gamer->getField('UniqueGameID');
			}
		}
		
		$results = unserialize($tournament->getField('Results'));
		$text .= brackets($tournament->getField('Type'), $tournament->getField('MaxNumberPlayers'), $teams, &$results, $rounds);
		$tournament->updateResults($results);


	$text .= '</div>';    // tabs-3 "Brackets"

	/* Matches */
	$text .= '<div id="tabs-4">';

	/* Display Match Report buttons */
	if(($can_report_quickloss != 0)||($can_report != 0))
	{
		$text .= '<table>';
		$text .= '<tr>';
		if($can_report != 0)
		{
			$text .= '<td>';
			$text .= '<form action="'.e_PLUGIN.'ebattles/matchreport.php?TournamentID='.$tournament_id.'" method="post">';
			$text .= '<div>';
			$text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
			$text .= ebImageTextButton('matchreport', 'page_white_edit.png', EB_TOURNAMENT_L57);
			$text .= '</div>';
			$text .= '</form>';
			$text .= '</td>';
		}

		$text .= '</tr>';
		$text .= '</table>';
	}
	$text .= '<br />';


	$text .= '</div>';    // tabs-4 "Matches"

	$text .= '<div id="tabs-5">';
	$q = "SELECT DISTINCT ".TBL_TPLAYERS.".*, "
	.TBL_GAMERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_TPLAYERS.", "
	.TBL_GAMERS.", "
	.TBL_USERS
	." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
	."   AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	."   AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)";
	$result = $sql->db_Query($q);
	$numPlayers = mysql_numrows($result);
	if ($numPlayers>0)
	{
		$text .= '<table style="width:90%"><tbody>';
		$text .= '<tr>';
		$text .= '<td><b>'.EB_TOURNAMENT_L64.'</b></td>';
		$text .= '<td><b>'.EB_TOURNAMENT_L65.'</b></td>';
		$text .= '<td><b>'.EB_TOURNAMENT_L66.'</b></td>';
		$text .= '</tr>';
		for ($player = 0; $player < $numPlayers; $player++)
		{
			/* Race	Name	BNet name	BNet Stats	Record*/

			$pFactionIcon = mysql_result($result, $player , TBL_FACTIONS.".Icon");
			$pFactionName = mysql_result($result, $player , TBL_FACTIONS.".Name");
			if($pFactionName){
				$pFactionImage = ' <img '.getFactionIconResize($fIcon).' title="'.$fName.'" style="vertical-align:middle"/>';
			} else {
				$pFactionImage = '';
			}
			$pName = mysql_result($result, $player , TBL_USERS.".user_name");
			$pGamer = mysql_result($result, $player , TBL_GAMERS.".UniqueGameID");

			$text .= '<tr>';
			$text .= '<td>'.$pFactionImage.'</td>';
			$text .= '<td>'.$pName.'</td>';
			$text .= '<td>'.$pGamer.'</td>';
			$text .= '</tr>';
		}
		$text .= '</tbody></table>';
	}
	$text .= '<br />';
	$text .= '</div>';  // tabs-5 "Players"
	$text .= '</div>';  // tabs




	$text .= disclaimer();

}

$ns->tablerender($tournament->getField('Name')." ($egame - ".tournamentTypeToString($tournament->getField('Type')).")", $text);
require_once(FOOTERF);
exit;

?>

