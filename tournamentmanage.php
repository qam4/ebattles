<?php
/**
* TournamentManage.php
*
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/brackets.php");
require_once(e_PLUGIN."ebattles/include/gamer.php");

require_once(HEADERF);
// Include userclass file
require_once(e_HANDLER."userclass_class.php");

/*******************************************************************
********************************************************************/
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");
$text .= '
<script type="text/javascript" src="./js/tournament.js"></script>
';

$tournament_id = $_GET['TournamentID'];
$self = $_SERVER['PHP_SELF'];

if (!$tournament_id)
{
	header("Location: ./tournaments.php");
	exit();
}
else
{
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
	$egame = mysql_result($result,0 , TBL_GAMES.".Name");
	$egameicon  = mysql_result($result,0 , TBL_GAMES.".Icon");
	$egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
	$eowner = mysql_result($result,0 , TBL_USERS.".user_id");
	$eownername = mysql_result($result,0 , TBL_USERS.".user_name");

	$tournament = new Tournament($tournament_id);
	$tournamentStatus = $tournament->getField('Status');
	$rounds = unserialize($tournament->getField('Rounds'));

	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$eowner) $can_manage = 1;
	if ($can_manage == 0)
	{
		header("Location: ./tournamentinfo.php?TournamentID=$tournament_id");
		exit();
	}
	else
	{
		$text .= '<div id="tabs">';
		$text .= '<ul>';
		$text .= '<li><a href="#tabs-1">'.EB_TOURNAMENTM_L2.'</a></li>';
		$text .= '<li><a href="#tabs-2">'.EB_TOURNAMENTM_L3.'</a></li>';
		/*$text .= '<li><a href="#tabs-3">'.EB_TOURNAMENTM_L4.'</a></li>';*/
		$text .= '<li><a href="#tabs-4">'.EB_TOURNAMENTM_L5.'</a></li>';
		$text .= '<li><a href="#tabs-5">'.EB_TOURNAMENTM_L6.'</a></li>';
		$text .= '<li><a href="#tabs-6">'.EB_TOURNAMENTM_L7.'</a></li>';
		$text .= '</ul>';

		//***************************************************************************************
		// tab-page "Tournament Summary"
		$text .= '<div id="tabs-1">';

		$text .= '<table class="eb_table" style="width:95%">';
		$text .= '<tbody>';
		$text .= '<tr><td>';
		$text .= '
		<form action="'.e_PLUGIN.'ebattles/tournamentinfo.php?TournamentID='.$tournament_id.'" method="post">
		'.ebImageTextButton('submit', 'magnify.png', EB_TOURNAMENTM_L133).'
		</form>';
		$text .= '</td></tr>';
		$text .= '</tbody>';
		$text .= '</table>';

		$text .= '
		<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">
		<table class="eb_table" style="width:95%">
		<tbody>
		';

		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L135.'<br />';
		$text .= '</td>';
		$text .= '<td class="eb_td">';

		switch($tournamentStatus)
		{
			case 'draft':
			$text .= '<table class="table_left">';
			$text .= '<tr>';
			$text .= '<td>'.EB_TOURNAMENTM_L136.'</td>';
			$text .= '<td>'.ebImageTextButton('tournamentpublish', 'thumb_up.png', EB_TOURNAMENTM_L137).'</td>';
			$text .= '</tr>';
			$text .= '</table>';
			break;
			case 'signup':
			$text .= EB_TOURNAMENTM_L138;
			break;
			case 'checkin':
			$text .= EB_TOURNAMENTM_L139;
			break;
			case 'active':
			$text .= EB_TOURNAMENTM_L140;
			break;
			case 'finished':
			$text .= EB_TOURNAMENTM_L141;
			break;
		}

		$text .= '</td>';



		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L9.'<br />';
		$text .= '<a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$eowner.'">'.$eownername.'</a>';
		$text .= '</td>';

		$q_2 = "SELECT ".TBL_USERS.".*"
		." FROM ".TBL_USERS;
		$result_2 = $sql->db_Query($q_2);
		$row = mysql_fetch_array($result_2);
		$num_rows_2 = mysql_numrows($result_2);

		$text .= '<td class="eb_td">';
		$text .= '<table class="table_left">';
		$text .= '<tr>';
		$text .= '<td><select class="tbox" name="tournamentowner">';
		for($j=0; $j<$num_rows_2; $j++)
		{
			$uid  = mysql_result($result_2,$j, TBL_USERS.".user_id");
			$uname  = mysql_result($result_2,$j, TBL_USERS.".user_name");

			if ($eowner == $uid)
			{
				$text .= '<option value="'.$uid.'" selected="selected">'.$uname.'</option>';
			}
			else
			{
				$text .= '<option value="'.$uid.'">'.$uname.'</option>';
			}
		}
		$text .= '</select>';
		$text .= '</td>';
		$text .= '<td>';
		$text .= ebImageTextButton('tournamentchangeowner', 'user_go.ico', EB_TOURNAMENTM_L10);
		$text .= '</td>';
		$text .= '</tr>';
		$text .= '</table>';
		$text .= '</td>';
		$text .= '</tr>';

		$q = "SELECT ".TBL_MODS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_MODS.", "
		.TBL_USERS
		." WHERE (".TBL_MODS.".Tournament = '$tournament_id')"
		."   AND (".TBL_USERS.".user_id = ".TBL_MODS.".User)";
		$result = $sql->db_Query($q);
		$numMods = mysql_numrows($result);
		$text .= '
		<tr>
		';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L11.'</td>';
		$text .= '<td class="eb_td">';
		if ($numMods>0)
		{
			$text .= '<table class="table_left">';
			for($i=0; $i<$numMods; $i++){
				$modid  = mysql_result($result,$i, TBL_USERS.".user_id");
				$modname  = mysql_result($result,$i, TBL_USERS.".user_name");
				$text .= '<tr>';
				$text .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$modid.'">'.$modname.'</a></td>';
				$text .= '<td>';
				$text .= '<div>';
				$text .= '<input type="hidden" name="tournamentmod" value="'.$modid.'"/>';
				$text .= ebImageTextButton('tournamentdeletemod', 'user_delete.ico', EB_TOURNAMENTM_L12, 'negative jq-button', EB_TOURNAMENTM_L13);
				$text .= '</div>';
				$text .= '</td>';
				$text .= '</tr>';
			}
			$text .= '</table>';
		}
		$q = "SELECT ".TBL_USERS.".*"
		." FROM ".TBL_USERS;
		$result = $sql->db_Query($q);
		/* Error occurred, return given name by default */
		$numUsers = mysql_numrows($result);
		$text .= '
		<table class="table_left">
		<tr>
		<td>
		<select class="tbox" name="mod">
		';
		for($i=0; $i < $numUsers; $i++)
		{
			$uid  = mysql_result($result,$i, TBL_USERS.".user_id");
			$uname  = mysql_result($result,$i, TBL_USERS.".user_name");
			$text .= '<option value="'.$uid.'">'.$uname.'</option>';
		}
		$text .= '
		</select>
		</td>
		<td>
		<div>
		'.ebImageTextButton('tournamentaddmod', 'user_add.png', EB_TOURNAMENTM_L14).'
		</div>
		</td>
		</tr>
		</table>
		';
		$text .= '
		</td>
		</tr>
		</tbody>
		</table>
		</form>
		</div>
		';  // tab-page "Tournament Summary"

		//***************************************************************************************
		// tab-page "Tournament Settings"
		$text .= '<div id="tabs-2">';

		$text .= $tournament->displayTournamentSettingsForm();

		$text .= '
		</div>
		';  // tab-page "Tournament Settings"

		//***************************************************************************************
		// tab-page "Tournament"
		/*
		$text .= '<div id="tabs-3">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';



		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		$text .= '
		</tbody>
		</table>
		';

		//<!-- Save Button -->
		$text .= '
		<table><tr><td>
		<div>
		'.ebImageTextButton('tournamentrulessave', 'disk.png', EB_TOURNAMENTM_L39).'
		</div>
		</td></tr></table>

		</form>
		</div>
		';  // tab-page "Tournament Rules"
		*/

		//***************************************************************************************
		// tab-page "Brackets"
		$text .= '<div id="tabs-4">';

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
				$playerID = mysql_result($result,$player , TBL_TPLAYERS.".PlayerID");
				$gamerID = mysql_result($result,$player , TBL_GAMERS.".GamerID");
				$gamer = new Gamer($gamerID);
				$teams[$player]['Name'] = $gamer->getField('UniqueGameID');
				$teams[$player]['PlayerID'] = $playerID;
			}
		}

		$results = unserialize($tournament->getField('Results'));
		list($bracket_html) = brackets($tournament->getField('Type'), $tournament->getField('MaxNumberPlayers'), $teams, $results, $rounds);
		$text .= $bracket_html;
		//$tournament->updateResults($results);
		//$tournament->updateDB($results);

		$text .= '</div>';  // tab-page "Brackets"

		//***************************************************************************************
		// tab-page "Tournament Players/Teams"
		$text .= '<div id="tabs-5">';

		$pages = new Paginator;

		$array = array(
		'name'   => array(EB_TOURNAMENTM_L55, TBL_USERS.'.user_name'),
		'joined'   => array(EB_TOURNAMENTM_L56, TBL_TPLAYERS.'.Joined')
		);

		if (!isset($_GET['orderby'])) $_GET['orderby'] = 'joined';
		$orderby=$_GET['orderby'];

		$sort = "DESC";
		if(isset($_GET["sort"]) && !empty($_GET["sort"]))
		{
			$sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
		}

		$q = "SELECT COUNT(*) as NbrPlayers"
		." FROM ".TBL_TPLAYERS.", "
		.TBL_GAMERS.", "
		.TBL_USERS
		." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
		." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)";
		$result = $sql->db_Query($q);
		$row = mysql_fetch_array($result);
		$numPlayers = $row['NbrPlayers'];

		$totalItems = $numPlayers;
		$pages->items_total = $totalItems;
		$pages->mid_range = eb_PAGINATION_MIDRANGE;
		$pages->paginate();

		/* Number of teams */
		switch($tournament->getField('MatchType'))
		{
			case "2v2":
			case "3v3":
			case "4v4":
			$q = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TTEAMS
			." WHERE (".TBL_TTEAMS.".Tournament = '$tournament_id')";
			$result = $sql->db_Query($q);
			$row = mysql_fetch_array($result);
			$numTeams = $row['NbrTeams'];

			$text .= '<div class="spacer">';
			$text .= '<p>';
			$text .= $numTeams.' '.EB_TOURNAMENTM_L114.'<br />';
			$text .= '</p>';
			$text .= '</div>';
			break;
			default:
		}

		/* Number of players */
		switch($tournament->getField('MatchType'))
		{
			default:
			$text .= '<div class="spacer">';
			$text .= '<p>';
			$text .= $numPlayers.' '.EB_TOURNAMENTM_L40.'<br />';
			$text .= '</p>';
			$text .= '</div>';
			break;
		}

		/* Add Team/Player */
		switch($tournament->getField('MatchType'))
		{
			case "2v2":
			case "3v3":
			case "4v4":
			// Form to add a team's division to the tournament
			$q = "SELECT ".TBL_DIVISIONS.".*, "
			.TBL_CLANS.".*"
			." FROM ".TBL_DIVISIONS.", "
			.TBL_CLANS
			." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
			."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
			$result = $sql->db_Query($q);
			/* Error occurred, return given name by default */
			$numDivisions = mysql_numrows($result);

			$text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%">
			<tbody>
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">
			<b>'.EB_TOURNAMENTM_L41.'</b>
			</td>
			<td class="eb_td">
			<select class="tbox" name="division">
			';
			for($i=0; $i<$numDivisions; $i++)
			{
				$did  = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");
				$dname  = mysql_result($result,$i, TBL_CLANS.".Name");
				$text .= '<option value="'.$did.'">'.$dname.'</option>';
			}
			$text .= '
			</select>
			'.ebImageTextButton('tournamentaddteam', 'user_add.png', EB_TOURNAMENTM_L42).'
			<input class="tbox" type="checkbox" name="tournamentaddteamnotify"/>'.EB_TOURNAMENTM_L43.'
			</td>
			</tr>
			</tbody>
			</table>
			</form>
			';
			break;
			case "1v1":
			// TODO: No good...
			// Form to add a player to the tournament
			$q = "SELECT ".TBL_GAMERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_GAMERS.".Game = '$egameid')"
			."   AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)";
			$result = $sql->db_Query($q);
			$numUsers = mysql_numrows($result);
			$text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%">
			<tbody>
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">
			<b>'.EB_TOURNAMENTM_L44.'</b>
			</td>
			<td class="eb_td">
			<table class="table_left">
			<tr>
			<td><div><select class="tbox" name="player">
			';
			for($i=0; $i<$numUsers; $i++)
			{
				$uid  = mysql_result($result,$i, TBL_USERS.".user_id");
				$uname  = mysql_result($result,$i, TBL_GAMERS.".Name");
				$text .= '<option value="'.$uid.'">'.$uname.'</option>';
			}
			$text .= '
			</select></div></td>
			<td>'.ebImageTextButton('tournamentaddplayer', 'user_add.png', EB_TOURNAMENTM_L45).'</td>
			<td><div><input class="tbox" type="checkbox" name="tournamentaddplayernotify"/>'.EB_TOURNAMENTM_L46.'</div></td>
			</tr>
			</table>
			</td>
			</tr>
			</tbody>
			</table>
			</form>
			';
			break;
			default:
		}

		$text .= '<br />';
		$text .= '<table>';
		$text .= '<tr><td style="vertical-align:top">'.EB_TOURNAMENTM_L47.':</td>';
		$text .= '<td>'.EB_TOURNAMENTM_L48.'</td></tr>';
		$text .= '<tr><td style="vertical-align:top">'.EB_TOURNAMENTM_L49.':</td>';
		$text .= '<td>'.EB_TOURNAMENTM_L50.'</td></tr>';
		$text .= '</table>';

		switch($tournament->getField('MatchType'))
		{
			case "2v2":
			case "3v3":
			case "4v4":
			// Show list of teams here
			$q_Teams = "SELECT ".TBL_CLANS.".*, "
			.TBL_TTEAMS.".*, "
			.TBL_DIVISIONS.".* "
			." FROM ".TBL_CLANS.", "
			.TBL_TTEAMS.", "
			.TBL_DIVISIONS
			." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
			." AND (".TBL_TTEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
			." AND (".TBL_TTEAMS.".Tournament = '$tournament_id')";
			$result = $sql->db_Query($q_Teams);
			$num_rows = mysql_numrows($result);
			if(!$result || ($num_rows < 0)){
				$text .= EB_TOURNAMENTM_L51.'<br />';
			}
			if($num_rows == 0){
				$text .= EB_TOURNAMENTM_L115.'<br />';
			}
			else
			{
				$text .= '<table class="eb_table" style="width:95%"><tbody>';
				$text .= '<tr>
				<th class="eb_th2">'.EB_CLANS_L5.'</th>
				<th class="eb_th2">'.EB_CLANS_L6.'</th>
				</tr>';
				for($i=0; $i < $num_rows; $i++){
					$clanid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
					$cname  = mysql_result($result,$i, TBL_CLANS.".Name");
					$ctag  = mysql_result($result,$i, TBL_CLANS.".Tag");
					$cavatar  = mysql_result($result,$i, TBL_CLANS.".Image");
					$cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");

					$image = "";
					if ($pref['eb_avatar_enable_teamslist'] == 1)
					{
						if($cavatar)
						{
							$image = '<img '.getAvatarResize(getImagePath($cavatar, 'team_avatars')).' style="vertical-align:middle"/>';
						} else if ($pref['eb_avatar_default_team_image'] != ''){
							$image = '<img '.getAvatarResize(getImagePath($pref['eb_avatar_default_team_image'], 'team_avatars')).' style="vertical-align:middle"/>';
						}
					}

					$text .= '<tr>
					<td class="eb_td">'.$image.'&nbsp;<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$clanid.'">'.$cname.'</a></td>
					<td class="eb_td">'.$ctag.'</td>
					</tr>';
				}
				$text .= '</tbody></table>';
			}
			break;
			default:
		}

		switch($tournament->getField('MatchType'))
		{
			default:
			$orderby_array = $array["$orderby"];
			$q_Players = "SELECT ".TBL_TPLAYERS.".*, "
			.TBL_GAMERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_TPLAYERS.", "
			.TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_TPLAYERS.".Tournament = '$tournament_id')"
			." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY $orderby_array[1] $sort"
			." $pages->limit";
			$result = $sql->db_Query($q_Players);
			$num_rows = mysql_numrows($result);
			if(!$result || ($num_rows < 0)){
				$text .= EB_TOURNAMENTM_L51.'<br />';
			} else if($num_rows == 0){
				$text .= EB_TOURNAMENTM_L52.'<br />';
			}
			else
			{
				// Paginate
				$text .= '<br />';
				$text .= '<span class="paginate" style="float:left;">'.$pages->display_pages().'</span>';
				$text .= '<span style="float:right">';
				// Go To Page
				$text .= $pages->display_jump_menu();
				$text .= '&nbsp;&nbsp;&nbsp;';
				// Items per page
				$text .= $pages->display_items_per_page();
				$text .= '</span><br /><br />';
				/* Display table contents */
				$text .= '<form id="playersform" action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
				$text .= '<table class="eb_table" style="width:95%"><tbody>';
				$text .= '<tr>';
				foreach($array as $opt=>$opt_array)
				{
					$text .= '<th class="eb_th2"><a href="'.e_PLUGIN.'ebattles/tournamentmanage.php?TournamentID='.$tournament_id.'&amp;orderby='.$opt.'&amp;sort='.$sort.'">'.$opt_array[0].'</a></th>';
				}
				$text .= '<th class="eb_th2">'.EB_TOURNAMENTM_L59;
				$text .= '<input type="hidden" id="ban_player" name="ban_player" value=""/>';
				$text .= '<input type="hidden" id="unban_player" name="unban_player" value=""/>';
				$text .= '<input type="hidden" id="kick_player" name="kick_player" value=""/>';
				$text .= '<input type="hidden" id="del_player_games" name="del_player_games" value=""/>';
				$text .= '<input type="hidden" id="del_player_awards" name="del_player_awards" value=""/>';
				$text .= '</th></tr>';
				for($i=0; $i<$num_rows; $i++)
				{
					$pid  = mysql_result($result,$i, TBL_TPLAYERS.".TPlayerID");
					$puid = mysql_result($result,$i, TBL_USERS.".user_id");
					$pname  = mysql_result($result,$i, TBL_USERS.".user_name");
					$puniquegameid  = mysql_result($result,$i, TBL_GAMERS.".UniqueGameID");
					$pjoined  = mysql_result($result,$i, TBL_TPLAYERS.".Joined");
					$pjoined_local = $pjoined + TIMEOFFSET;
					$date  = date("d M Y",$pjoined_local);
					$pbanned = mysql_result($result,$i, TBL_TPLAYERS.".Banned");
					$pgames = mysql_result($result,$i, TBL_TPLAYERS.".GamesPlayed");
					$pteam = mysql_result($result,$i, TBL_TPLAYERS.".Team");
					list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);

					$text .= '<tr>';
					$text .= '<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$puniquegameid.'</a></td>';
					$text .= '<td class="eb_td">'.(($pbanned) ? EB_TOURNAMENTM_L54 : $date).'</td>';
					//$text .= '<td class="eb_td">'.$pgames.'</td>';
					$text .= '<td class="eb_td">';
					if ($pbanned)
					{
						$text .= ' <a href="javascript:unban_player(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L60.'" onclick="return confirm(\''.EB_TOURNAMENTM_L61.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_go.ico" alt="'.EB_TOURNAMENTM_L60.'"/></a>';
					}
					else
					{
						$text .= ' <a href="javascript:ban_player(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L62.'" onclick="return confirm(\''.EB_TOURNAMENTM_L63.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_delete.ico" alt="'.EB_TOURNAMENTM_L62.'"/></a>';
					}
					if (($pgames == 0)&&($pawards == 0))
					{
						$text .= ' <a href="javascript:kick_player(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L64.'" onclick="return confirm(\''.EB_TOURNAMENTM_L65.'\')"><img src="'.e_PLUGIN.'ebattles/images/cross.png" alt="'.EB_TOURNAMENTM_L64.'"/></a>';
					}
					if ($pgames != 0)
					{
						$text .= ' <a href="javascript:del_player_games(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L66.'" onclick="return confirm(\''.EB_TOURNAMENTM_L67.'\')"><img src="'.e_PLUGIN.'ebattles/images/controller_delete.ico" alt="'.EB_TOURNAMENTM_L66.'"/></a>';
					}
					if ($pawards != 0)
					{
						$text .= ' <a href="javascript:del_player_awards(\''.$pid.'\');" title="'.EB_TOURNAMENTM_L68.'" onclick="return confirm(\''.EB_TOURNAMENTM_L69.'\')"><img src="'.e_PLUGIN.'ebattles/images/award_star_delete.ico" alt="'.EB_TOURNAMENTM_L68.'"/></a>';
					}
					$text .= '</td>';
					$text .= '</tr>';
				}
				$text .= '</tbody></table>';
				$text .= '</form>';
			}
			break;
		}

		$text .= '
		</div>
		';  // tab-page "Tournament Players/Teams"

		//***************************************************************************************
		// tab-page "Tournament Reset"
		$text .= '<div id="tabs-6">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/tournamentprocess.php?TournamentID='.$tournament_id.'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';

		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L74.'<div class="smalltext">'.EB_TOURNAMENTM_L75.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('tournamentresettournament', 'bin_closed.png', EB_TOURNAMENTM_L76, '', EB_TOURNAMENTM_L77);
		$text .= '
		</td>
		</tr>
		';
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_TOURNAMENTM_L78.'<div class="smalltext">'.EB_TOURNAMENTM_L79.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('tournamentdelete', 'delete.png', EB_TOURNAMENTM_L80, 'negative jq-button', EB_TOURNAMENTM_L81);
		$text .= '
		</td>
		</tr>
		';
		$text .= '
		</tbody>
		</table>
		</form>
		</div>
		';  // tab-page "Tournament Reset"

		$text .= '</div>';
	}
}

$ns->tablerender($tournament->getField('Name')." ($egame - ".tournamentTypeToString($tournament->getField('Type')).") - ".EB_TOURNAMENTM_L1, $text);
require_once(FOOTERF);
exit;
?>
