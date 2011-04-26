<?php
/**
* TournamentInfo.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/show_array.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/match.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$pages = new Paginator;

$text = '
<script type="text/javascript" src="./js/tabpane.js"></script>
';
$text .= '
<script language="JavaScript" type="text/javascript" src="./js/jquery-1.5.1.min.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/jquery-ui-1.8.11.custom.min.js"></script>

<!--
Validation (http://bassistance.de/jquery-plugins/jquery-plugin-validation/)
Form (http://malsup.com/jquery/form/) plugins
-->
<script language="JavaScript" type="text/javascript" src="./js/jquery.form.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/jquery.validate.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/additional-methods.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/signup.js"></script>

<!-- JDR: Themeroller files and my generic base -->
<link type="text/css" href="./css/custom-theme/jquery-ui-1.8.11.custom.css" rel="stylesheet" />
<link type="text/css" href="./css/generic-base.css" rel="stylesheet" />
';

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
	$self = $_SERVER['PHP_SELF'];
	$file = 'cache/sql_cache_tournament_'.$tournament_id.'.txt';
	$file_team = 'cache/sql_cache_tournament_team_'.$tournament_id.'.txt';

	require_once(e_PLUGIN."ebattles/tournamentinfo_process.php");

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

	switch($tournament->getField('MatchType'))
	{
		case "1v1":
		$text .= '<div class="tab-pane" id="tab-pane-15">';
		break;
		default:
		$text .= '<div class="tab-pane" id="tab-pane-15">';
		break;
	}


	$text .= '<div class="tab-page">';
	/* Signup, Join/Quit Tournament */
	$text .= '<div class="tab">'.EB_TOURNAMENT_L35.'</div>';
	$text .= '<table class="signup" style="width:95%"><tbody>';
	$userIsDivisionCaptain = FALSE;
	if(check_class(e_UC_MEMBER))
	{
		// If logged in
		if(($tournament->getField('StartDateTime') == 0) || ($time < $tournament->getField('StartDateTime')))
		{
			// If tournament is not finished
			if (($tournament->getField('Type') == "Team Tournament")||($tournament->getField('Type') == "ClanWar"))
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

						$text .= '<tr>';
						$text .= '<td>'.EB_TOURNAMENT_L7.'&nbsp;'.$div_name.'</td>';
						if( $numTeams == 0)
						{

							if ($tournament->getField('Password') != "")
							{
								$text .= '<td>'.EB_TOURNAMENT_L8.'</td>';
								$text .= '<td>
								<form action="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'" method="post">
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
								<form action="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'" method="post">
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
						$text .= '</tr>';
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
					$text .= '<tr><td>'.EB_TOURNAMENT_L14.'</td>';
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
						." AND (".TBL_TEAMS.".Tournament = '$tournament_id')";
						$result_3 = $sql->db_Query($q_3);
						if(!$result_3 || (mysql_numrows($result_3) == 0))
						{
							if ($captain_id != USERID)
							{
								$text .= '<tr><td>'.EB_TOURNAMENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_TOURNAMENT_L16.'</td>';
								$text .= '<td>'.EB_TOURNAMENT_L17.' <a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$captain_id.'">'.$captain_name.'</a>.</td></tr>';
							}
						}
						else
						{
							$team_id  = mysql_result($result_3,0 , TBL_TEAMS.".TeamID");
							$text .= '<tr><td>'.EB_TOURNAMENT_L15.'&nbsp;'.$clan_name.'&nbsp;'.EB_TOURNAMENT_L18.'</td>';

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
								<form action="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'" method="post">
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
										<form action="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'" method="post">
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
							$text .= '</tr>';
						}
					}
				}
				break;
				case "1v1":
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
					if ($tournament->getField('Password') != "")
					{
						$text .= '<tr><td>'.EB_TOURNAMENT_L25.'</td>';
						$text .= '<td>'.EB_TOURNAMENT_L26.'</td>';
						$text .= '<td>';
						$text .= '
						<form action="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'" method="post">
						<div>
						<input class="tbox" type="password" title="'.EB_TOURNAMENT_L27.'" name="joinTournamentPassword"/>
						</div>
						'.ebImageTextButton('jointournament', 'user_add.png', EB_TOURNAMENT_L19).'
						</form></td></tr>
						';
					}
					else
					{
						$text .= '<tr><td>'.EB_TOURNAMENT_L28.'</td>';
						$text .= '<td>
						<div>
						<input type="hidden" name="joinTournamentPassword" value=""/>
						</div>
						'.ebImageTextButton('jointournament', 'user_add.png', EB_TOURNAMENT_L19, 'ui-button ui-state-default ui-corner-all', '', '', 'id="sign-up"').'
						</td></tr>
						';
					}
				}
				else
				{
					$user_pid  = mysql_result($result,0 , TBL_TPLAYERS.".TPlayerID");
					$user_banned  = mysql_result($result,0 , TBL_TPLAYERS.".Banned");

					if ($user_banned)
					{
						$text .= '<tr><td>'.EB_TOURNAMENT_L29.'<br />
						'.EB_TOURNAMENT_L30.'</td><td></td></tr>';
					}
					else
					{
						$text .= '<tr><td>'.EB_TOURNAMENT_L31.'</td>';

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
							$text .= '<td>
							<form action="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'" method="post">
							<div>
							<input type="hidden" name="player" value="'.$user_pid.'"/>
							'.ebImageTextButton('quittournament', 'user_delete.ico', EB_TOURNAMENT_L32, 'negative', EB_TOURNAMENT_L33).'
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
	}
	else
	{
		$text .= '<tr><td>'.EB_TOURNAMENT_L34.'</td>';
		$text .= '<td></td></tr>';
	}
	$text .= '</tbody></table>';
	
	// Modal form
	$text .= '
	<div id="my-modal-form" title="Game Unique ID">
		
		<!-- JDR: form validation error container -->
        <div class="ui-widget ui-helper-hidden" id="errorblock-div1">
			<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;" id="errorblock-div2" style="display:none;"> 
				<p>
				   <!-- JDR: fancy icon -->
				   <span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span> 
	               <strong>Alert:</strong> Errors detected!
				</p>
				<!-- JDR: validation plugin will target this UL for error messages -->
				<ul></ul>
			</div>
		</div>
		
		<!-- JDR: our form, no buttons (buttons generated by jQuery UI dialog() function) -->
	    <form action="testme.php" name="modal-form-test" id="modal-form-test" method="POST">
	    <fieldset>
		    <label for="charactername">Character Name</label>
		    <input type="text" name="charactername" id="charactername" class="text ui-widget-content ui-corner-all" />
	    	
		    <label for="code">Code</label>
		    <input type="text" name="code" id="code" class="text ui-widget-content ui-corner-all" />
	    </fieldset>
	    </form>
	</div>
	';



	/* Info */
	$text .= '<table class="fborder" style="width:95%"><tbody>';

	$text .= '<tr>';
	$text .= '<td class="forumheader3">'.EB_TOURNAMENT_L36.'</td>';
	$text .= '<td class="forumheader3"><b>'.$tournament->getField('Name').'</b></td>';
	$text .= '</tr>';

	$text .= '<tr>';
	$text .= '<td class="forumheader3">'.EB_TOURNAMENT_L37.'</td>';
	$text .= '<td class="forumheader3">'.$tournament->getField('MatchType').' - '.tournamentTypeToString($tournament->getField('Type')).'</td>';
	$text .= '</tr>';

	$text .= '<tr>';
	$text .= '<td class="forumheader3">'.EB_TOURNAMENT_L38.'</td>';
	$text .= '<td class="forumheader3"><img '.getGameIconResize($egameicon).'/> '.$egame.'</td>';
	$text .= '</tr>';

	$text .= '<tr>';
	$text .= '<td class="forumheader3">'.EB_TOURNAMENT_L39.'</td>';
	$text .= '<td class="forumheader3"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$eowner.'">'.$eownername.'</a>';
	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$eowner) $can_manage = 1;
	if ($can_manage == 1)
	$text .= '<br /><a href="'.e_PLUGIN.'ebattles/tournamentmanage.php?TournamentID='.$tournament_id.'">'.EB_TOURNAMENT_L40.'</a>';
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
	$text .= '<td class="forumheader3">'.EB_TOURNAMENT_L41.'</td>';
	$text .= '<td class="forumheader3">';
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

	$text .= '<tr><td class="forumheader3">'.EB_TOURNAMENT_L42.'</td><td class="forumheader3">'.$date_start.'</td></tr>';
	$text .= '<tr><td class="forumheader3"></td><td class="forumheader3">'.$time_comment.'</td></tr>';
	$text .= '<tr><td class="forumheader3">'.EB_TOURNAMENT_L44.'</td><td class="forumheader3">'.$tp->toHTML($tournament->getField('Rules'), true).'</td></tr>';
	$text .= '<tr><td class="forumheader3"></td><td class="forumheader3">'.$tp->toHTML($tournament->getField('Description'), true).'</td></tr>';
	$text .= '</tbody></table>';
	$text .= '</div>';    // tab-page "Info"

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
		$text .= '<div class="tab-page">';
		$text .= '<div class="tab">'.EB_TOURNAMENT_L45.'</div>';

		if(($can_challenge != 0)&&($tournament->getField('Type') == "ClanWar"))
		{
			$text .= '<form action="'.e_PLUGIN.'ebattles/challengerequest.php?TournamentID='.$tournament_id.'" method="post">';
			$text .= '<table>';
			$text .= '<tr>';
			// "Challenge team" form
			$q = "SELECT ".TBL_TPLAYERS.".*"
			." FROM ".TBL_TPLAYERS.", "
			.TBL_GAMERS
			." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
			."   AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			."   AND (".TBL_GAMERS.".User = '".USERID."')";
			$result = $sql->db_Query($q);
			$uteam = mysql_result($result,0 , TBL_TPLAYERS.".Team");

			$q = "SELECT ".TBL_CLANS.".*, "
			.TBL_TEAMS.".*, "
			.TBL_DIVISIONS.".* "
			." FROM ".TBL_CLANS.", "
			.TBL_TEAMS.", "
			.TBL_DIVISIONS
			." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
			." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
			." AND (".TBL_TEAMS.".Tournament = '$tournament_id')"
			." ORDER BY ".TBL_CLANS.".Name";
			$result = $sql->db_Query($q);
			$num_rows = mysql_numrows($result);

			$text .= '<td><div>
			<select class="tbox" name="Challenged">
			';
			for($i=0; $i<$num_rows; $i++)
			{
				$tid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
				$trank  = mysql_result($result,$i, TBL_TEAMS.".Rank");
				$tname  = mysql_result($result,$i, TBL_CLANS.".Name");

				if(($uteam == 0)||($uteam != $tid))
				{
					if ($trank==0)
					$trank_txt = EB_TOURNAMENT_L54;
					else
					$trank_txt = "#$trank";
					$text .= '<option value="'.$tid.'">'.$tname.' ('.$trank_txt.')</option>';
				}
			}
			$text .= '
			</select>
			</div></td>
			';
			$Challenger = USERID;
			$text .= '<td><div>';
			$text .= '<input type="hidden" name="TournamentID" value="'.$tournament_id.'"/>';
			$text .= '<input type="hidden" name="submitted_by" value="'.$Challenger.'"/>';
			$text .= '</div></td>';

			$text .= '<td>';
			$text .= '<div>';
			$text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
			$text .= ebImageTextButton('challenge_team', 'challenge.png', EB_TOURNAMENT_L71);
			$text .= '</div>';
			$text .= '</td>';
			$text .= '</tr>';
			$text .= '</table>';
			$text .= '</form>';
		}

		if (($time < $nextupdate_timestamp_local) && ($tournamentIsChanged == 1))
		{
			$text .= EB_TOURNAMENT_L46.'&nbsp;'.$date_nextupdate.'<br />';
		}
		$text .= '<div class="spacer">';
		$text .= '<p>';
		$text .= $nbrteams.' teams<br />';
		$text .= EB_TOURNAMENT_L47.'&nbsp;'.$tournament->getField('nbr_team_games_to_rank').'&nbsp;'.EB_TOURNAMENT_L48.'<br /><br />';
		$text .= '</p>';

		// Teams standings stats
		$stats = unserialize(implode('',file($file_team)));
		//print_r($stats);

		// Sorting the stats table
		$header = $stats[0];

		$new_header = array();
		$column = 0;
		foreach ($header as $header_cell)
		{
			//fm echo "column $column: $header_cell<br>";
			$pieces = explode("<br />", $header_cell);

			$new_header[] = '<a href="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'&amp;orderby='.$column.'&amp;sort='.$sort.'">'.$pieces[0].'</a>'.$pieces[1];
			$column++;
		}
		$header = array($new_header);
		$header[0][0] = "header";

		array_splice($stats,0,1);
		multi2dSortAsc($stats, $orderby, $sort_type);
		$stats = array_merge($header, $stats);
		$num_columns = count($stats[0]) - 1;
		$nbr_rows = count($stats);
		$text .= html_show_table($stats, $nbr_rows, $num_columns);

		$text .= '</div>';
		$text .= '</div>';    // tab-page "Teams Standings"
	}

	/* Players Standings */
	if (($tournament->getField('Type') == "Team Tournament")||($tournament->getField('Type') == "One Player Tournament"))
	{
		// Players standings stats
		$stats = unserialize(implode('',file($file)));
		//print_r($stats);

		// Sorting the stats table
		$header = $stats[0];

		$new_header = array();
		$column = 0;
		foreach ($header as $header_cell)
		{
			//fm echo "column $column: $header_cell<br>";
			$pieces = explode("<br />", $header_cell);

			$new_header[] = '<a href="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'&amp;orderby='.$column.'&amp;sort='.$sort.'">'.$pieces[0].'</a>'.$pieces[1];
			$column++;
		}
		$header = array($new_header);
		$header[0][0] = "header";

		array_splice($stats,0,1);
		multi2dSortAsc($stats, $orderby, $sort_type);
		$stats = array_merge($header, $stats);
		//print_r($stats);

		$text .= '<div class="tab-page">';
		$text .= '<div class="tab">'.EB_TOURNAMENT_L49.'</div>';

		if (($time < $nextupdate_timestamp_local) && ($tournamentIsChanged == 1))
		{
			$text .= EB_TOURNAMENT_L50.'&nbsp;'.$date_nextupdate.'<br />';
		}

		/* set pagination variables */
		$totalItems = $nbrplayers;
		$pages->items_total = $totalItems;
		$pages->mid_range = eb_PAGINATION_MIDRANGE;
		$pages->paginate();

		$text .= '<p>';
		$text .= $nbrplayers.'&nbsp;'.EB_TOURNAMENT_L51.'<br />';
		$text .= EB_TOURNAMENT_L52.'&nbsp;'.$tournament->getField('nbr_games_to_rank').'&nbsp;'.EB_TOURNAMENT_L53.'<br />';
		$text .= '</p>';

		$text .= $myPosition_txt;

		$text .= '<br />';
		// Paginate
		$text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
		$text .= '<span style="float:right">';
		// Go To Page
		$text .= $pages->display_jump_menu();
		$text .= '&nbsp;&nbsp;&nbsp;';
		// Items per page
		$text .= $pages->display_items_per_page();
		$text .= '</span><br /><br />';

		// Paginate the statistics array
		$max_row = count($stats);
		$stats_paginate = array($stats[0]);
		$num_columns = count($stats[0]) - 1;
		$nbr_rows = 1;

		for ($i = $pages->low + 1; $i <= $pages->high + 1; $i++)
		{
			if ($i < $max_row)
			{
				$stats_paginate[] = $stats[$i];
				$nbr_rows ++;
			}
		}
		$text .= html_show_table($stats_paginate, $nbr_rows, $num_columns);

		$text .= '</div>';    // tab-page "Players Standings"
	}

	/* Matches */
	$text .= '
	<div class="tab-page">
	<div class="tab">'.EB_TOURNAMENT_L58;
	$text .= '</div>';

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


	$text .= '</div>';    // tab-page "Matches"

	$text .= '<div class="tab-page">';
	$text .= '<div class="tab">'.EB_TOURNAMENT_L63.'</div>';
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
	$text .= '</div>';  // tab-page "Players"
	$text .= '
	</div>
	';    // tab-pane

	$text .= disclaimer();

	$text .= '
	<script type="text/javascript">
	//<![CDATA[
	setupAllTabs();
	//]]>
	</script>
	';
}

$ns->tablerender($tournament->getField('Name')." ($egame - ".tournamentTypeToString($tournament->getField('Type')).")", $text);
require_once(FOOTERF);
exit;

?>

