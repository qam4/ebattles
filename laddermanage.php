<?php
/**
* LadderManage.php
*
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/ladder.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/gamer.php");

require_once(HEADERF);
// Include userclass file
require_once(e_HANDLER."userclass_class.php");

/*******************************************************************
********************************************************************/
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");
$text .= '
<script type="text/javascript" src="./js/ladder.js"></script>
<script type="text/javascript" src="./js/slider.js"></script>
';

$ladder_id = $_GET['LadderID'];
$self = $_SERVER['PHP_SELF'];

if (!$ladder_id)
{
	header("Location: ./ladders.php");
	exit();
}
else
{
	$q = "SELECT ".TBL_LADDERS.".*, "
	.TBL_GAMES.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_LADDERS.", "
	.TBL_GAMES.", "
	.TBL_USERS
	." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')"
	."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	."   AND (".TBL_USERS.".user_id = ".TBL_LADDERS.".Owner)";

	$result = $sql->db_Query($q);
	$egame = mysql_result($result,0 , TBL_GAMES.".Name");
	$egameicon  = mysql_result($result,0 , TBL_GAMES.".Icon");
	$egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
	$eowner = mysql_result($result,0 , TBL_USERS.".user_id");
	$eownername = mysql_result($result,0 , TBL_USERS.".user_name");

	$ladder = new Ladder($ladder_id);


	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$eowner) $can_manage = 1;
	if ($can_manage == 0)
	{
		header("Location: ./ladderinfo.php?LadderID=$ladder_id");
		exit();
	}
	else
	{
		$text .= '<div id="tabs">';
		$text .= '<ul>';
		$text .= '<li><a href="#tabs-1">'.EB_LADDERM_L2.'</a></li>';
		$text .= '<li><a href="#tabs-2">'.EB_LADDERM_L3.'</a></li>';
		/*$text .= '<li><a href="#tabs-3">'.EB_LADDERM_L4.'</a></li>';*/
		$text .= '<li><a href="#tabs-4">'.EB_LADDERM_L5.'</a></li>';
		$text .= '<li><a href="#tabs-5">'.EB_LADDERM_L6.'</a></li>';
		$text .= '<li><a href="#tabs-6">'.EB_LADDERM_L7.'</a></li>';
		$text .= '<li><a href="#tabs-7">'.EB_LADDERM_L121.'</a></li>';
		$text .= '</ul>';

		//***************************************************************************************
		// tab-page "Ladder Summary"
		$text .= '<div id="tabs-1">';

		$text .= '<table class="eb_table" style="width:95%">';
		$text .= '<tbody>';
		$text .= '<tr><td>';
		$text .= '
		<form action="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'" method="post">
		'.ebImageTextButton('submit', 'magnify.png', EB_LADDERM_L132).'
		</form>';
		$text .= '</td></tr>';
		$text .= '</tbody>';
		$text .= '</table>';

		$text .= '
		<form action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">
		<table class="eb_table" style="width:95%">
		<tbody>
		';

		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_LADDERM_L9.'<br />';
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
		$text .= '<td><select class="tbox" name="ladderowner">';
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
		$text .= ebImageTextButton('ladderchangeowner', 'user_go.ico', EB_LADDERM_L10);
		$text .= '</td>';
		$text .= '</tr>';
		$text .= '</table>';
		$text .= '</td>';
		$text .= '</tr>';

		$q = "SELECT ".TBL_MODS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_MODS.", "
		.TBL_USERS
		." WHERE (".TBL_MODS.".Ladder = '$ladder_id')"
		."   AND (".TBL_USERS.".user_id = ".TBL_MODS.".User)";
		$result = $sql->db_Query($q);
		$numMods = mysql_numrows($result);
		$text .= '
		<tr>
		';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_LADDERM_L11.'</td>';
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
				$text .= '<input type="hidden" name="laddermod" value="'.$modid.'"/>';
				$text .= ebImageTextButton('ladderdeletemod', 'user_delete.ico', EB_LADDERM_L12, 'negative', EB_LADDERM_L13);
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
		for($i=0; $i<$numUsers; $i++)
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
		'.ebImageTextButton('ladderaddmod', 'user_add.png', EB_LADDERM_L14).'
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
		';  // tab-page "Ladder Summary"

		//***************************************************************************************
		// tab-page "Ladder Settings"
		$text .= '<div id="tabs-2">';

		$text .= $ladder->displayLadderSettingsForm();

		$text .= '
		</div>
		';  // tab-page "Ladder Settings"

		//***************************************************************************************
		// tab-page "Ladder Rules"
		/*
		$text .= '<div id="tabs-3">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">';

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
		'.ebImageTextButton('ladderrulessave', 'disk.png', EB_LADDERM_L39).'
		</div>
		</td></tr></table>

		</form>
		</div>
		';  // tab-page "Ladder Rules"
		*/

		//***************************************************************************************
		// tab-page "Ladder Players/Teams"
		$text .= '<div id="tabs-4">';

		$pages = new Paginator;

		$array = array(
		'name'   => array(EB_LADDERM_L55, TBL_USERS.'.user_name'),
		'rank'   => array(EB_LADDERM_L56, TBL_PLAYERS.'.OverallScore'),
		'games'  => array(EB_LADDERM_L57, TBL_PLAYERS.'.GamesPlayed'),
		'awards' => array(EB_LADDERM_L58, '')
		);

		if (!isset($_GET['orderby'])) $_GET['orderby'] = 'rank';
		$orderby=$_GET['orderby'];

		$sort = "DESC";
		if(isset($_GET["sort"]) && !empty($_GET["sort"]))
		{
			$sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
		}

		$q = "SELECT COUNT(*) as NbrPlayers"
		." FROM ".TBL_PLAYERS.", "
		.TBL_GAMERS.", "
		.TBL_USERS
		." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
		." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
		." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)";
		$result = $sql->db_Query($q);
		$row = mysql_fetch_array($result);
		$numPlayers = $row['NbrPlayers'];

		$totalItems = $numPlayers;
		$pages->items_total = $totalItems;
		$pages->mid_range = eb_PAGINATION_MIDRANGE;
		$pages->paginate();

		/* Number of teams */
		switch($ladder->getField('Type'))
		{
			case "Team Ladder":
			case "ClanWar":
			$q = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Ladder = '$ladder_id')";
			$result = $sql->db_Query($q);
			$row = mysql_fetch_array($result);
			$numTeams = $row['NbrTeams'];

			$text .= '<div class="spacer">';
			$text .= '<p>';
			$text .= $numTeams.' '.EB_LADDERM_L114.'<br />';
			$text .= '</p>';
			$text .= '</div>';
			break;
			default:
		}

		/* Number of players */
		switch($ladder->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			$text .= '<div class="spacer">';
			$text .= '<p>';
			$text .= $numPlayers.' '.EB_LADDERM_L40.'<br />';
			$text .= '</p>';
			$text .= '</div>';
			break;
			default:
		}

		/* Add Team/Player */
		switch($ladder->getField('Type'))
		{
			case "Team Ladder":
			case "ClanWar":
			// Form to add a team's division to the ladder
			$q = "SELECT ".TBL_DIVISIONS.".*, "
			.TBL_CLANS.".*"
			." FROM ".TBL_DIVISIONS.", "
			.TBL_CLANS
			." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
			."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
			$result = $sql->db_Query($q);
			/* Error occurred, return given name by default */
			$numDivisions = mysql_numrows($result);

			$text .= '<form action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%">
			<tbody>
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">
			<b>'.EB_LADDERM_L41.'</b>
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
			'.ebImageTextButton('ladderaddteam', 'user_add.png', EB_LADDERM_L42).'
			<input class="tbox" type="checkbox" name="ladderaddteamnotify"/>'.EB_LADDERM_L43.'
			</td>
			</tr>
			</tbody>
			</table>
			</form>
			';
			break;
			case "One Player Ladder":
			// Form to add a player to the ladder
			$q = "SELECT ".TBL_USERS.".*"
			." FROM ".TBL_USERS;
			$result = $sql->db_Query($q);
			/* Error occurred, return given name by default */
			$numUsers = mysql_numrows($result);
			$text .= '<form action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%">
			<tbody>
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">
			<b>'.EB_LADDERM_L44.'</b>
			</td>
			<td class="eb_td">
			<table class="table_left">
			<tr>
			<td><div><select class="tbox" name="player">
			';
			for($i=0; $i<$numUsers; $i++)
			{
				$uid  = mysql_result($result,$i, TBL_USERS.".user_id");
				$uname  = mysql_result($result,$i, TBL_USERS.".user_name");
				$text .= '<option value="'.$uid.'">'.$uname.'</option>';
			}
			$text .= '
			</select></div></td>
			<td>'.ebImageTextButton('ladderaddplayer', 'user_add.png', EB_LADDERM_L45).'</td>
			<td><div><input class="tbox" type="checkbox" name="ladderaddplayernotify"/>'.EB_LADDERM_L46.'</div></td>
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

		$text .= '<br /><table>';
		$text .= '<tr><td style="vertical-align:top">'.EB_LADDERM_L47.':</td>';
		$text .= '<td>'.EB_LADDERM_L48.'</td></tr>';
		$text .= '<tr><td style="vertical-align:top">'.EB_LADDERM_L49.':</td>';
		$text .= '<td>'.EB_LADDERM_L50.'</td></tr>';
		$text .= '</table>';

		switch($ladder->getField('Type'))
		{
			case "Team Ladder":
			case "ClanWar":
			// Show list of teams here
			$q_Teams = "SELECT ".TBL_CLANS.".*, "
			.TBL_TEAMS.".*, "
			.TBL_DIVISIONS.".* "
			." FROM ".TBL_CLANS.", "
			.TBL_TEAMS.", "
			.TBL_DIVISIONS
			." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
			." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
			." AND (".TBL_TEAMS.".Ladder = '$ladder_id')";
			$result = $sql->db_Query($q_Teams);
			$num_rows = mysql_numrows($result);
			if(!$result || ($num_rows < 0)){
				$text .= EB_LADDERM_L51.'<br />';
			}
			if($num_rows == 0){
				$text .= EB_LADDERM_L115.'<br />';
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
					<td class="eb_td">'.$ctag.'</td></tr>';
				}
				$text .= '</tbody></table>';
			}
			break;
			default:
		}

		switch($ladder->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			$orderby_array = $array["$orderby"];
			$q_Players = "SELECT ".TBL_PLAYERS.".*, "
			.TBL_GAMERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_PLAYERS.", "
			.TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
			." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY $orderby_array[1] $sort"
			." $pages->limit";
			$result = $sql->db_Query($q_Players);
			$num_rows = mysql_numrows($result);
			if(!$result || ($num_rows < 0)){
				$text .= EB_LADDERM_L51.'<br />';
			} else if($num_rows == 0){
				$text .= EB_LADDERM_L52.'<br />';
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
				$text .= '<form id="playersform" action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">';
				$text .= '<table class="eb_table" style="width:95%"><tbody>';
				$text .= '<tr>';
				foreach($array as $opt=>$opt_array)
				{
					$text .= '<th class="eb_th2"><a href="'.e_PLUGIN.'ebattles/laddermanage.php?LadderID='.$ladder_id.'&amp;orderby='.$opt.'&amp;sort='.$sort.'">'.$opt_array[0].'</a></th>';
				}
				$text .= '<th class="eb_th2">'.EB_LADDERM_L59;
				$text .= '<input type="hidden" id="ban_player" name="ban_player" value=""/>';
				$text .= '<input type="hidden" id="unban_player" name="unban_player" value=""/>';
				$text .= '<input type="hidden" id="kick_player" name="kick_player" value=""/>';
				$text .= '<input type="hidden" id="del_player_games" name="del_player_games" value=""/>';
				$text .= '<input type="hidden" id="del_player_awards" name="del_player_awards" value=""/>';
				$text .= '</th></tr>';
				for($i=0; $i<$num_rows; $i++)
				{
					$pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
					$puid = mysql_result($result,$i, TBL_USERS.".user_id");
					$pname  = mysql_result($result,$i, TBL_USERS.".user_name");
					$puniquegameid  = mysql_result($result,$i, TBL_GAMERS.".UniqueGameID");
					$prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
					$pbanned = mysql_result($result,$i, TBL_PLAYERS.".Banned");
					$pgames = mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
					$pteam = mysql_result($result,$i, TBL_PLAYERS.".Team");
					list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);

					$q_awards = "SELECT COUNT(*) as NbrAwards"
					." FROM ".TBL_AWARDS
					." WHERE (".TBL_AWARDS.".Player = '$pid')";
					$result_awards = $sql->db_Query($q_awards);
					$row = mysql_fetch_array($result_awards);
					$pawards = $row['NbrAwards'];

					if ($prank == 0) $prank = EB_LADDERM_L53;

					$text .= '<tr>';
					$text .= '<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a></td>';
					$text .= '<td class="eb_td">'.(($pbanned) ? EB_LADDERM_L54 : $prank).'</td>';
					$text .= '<td class="eb_td">'.$pgames.'</td>';
					$text .= '<td class="eb_td">'.$pawards.'</td>';
					$text .= '<td class="eb_td">';
					if ($pbanned)
					{
						$text .= ' <a href="javascript:unban_player(\''.$pid.'\');" title="'.EB_LADDERM_L60.'" onclick="return confirm(\''.EB_LADDERM_L61.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_go.ico" alt="'.EB_LADDERM_L60.'"/></a>';
					}
					else
					{
						$text .= ' <a href="javascript:ban_player(\''.$pid.'\');" title="'.EB_LADDERM_L62.'" onclick="return confirm(\''.EB_LADDERM_L63.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_delete.ico" alt="'.EB_LADDERM_L62.'"/></a>';
					}
					if (($pgames == 0)&&($pawards == 0))
					{
						$text .= ' <a href="javascript:kick_player(\''.$pid.'\');" title="'.EB_LADDERM_L64.'" onclick="return confirm(\''.EB_LADDERM_L65.'\')"><img src="'.e_PLUGIN.'ebattles/images/cross.png" alt="'.EB_LADDERM_L64.'"/></a>';
					}
					if ($pgames != 0)
					{
						$text .= ' <a href="javascript:del_player_games(\''.$pid.'\');" title="'.EB_LADDERM_L66.'" onclick="return confirm(\''.EB_LADDERM_L67.'\')"><img src="'.e_PLUGIN.'ebattles/images/controller_delete.ico" alt="'.EB_LADDERM_L66.'"/></a>';
					}
					if ($pawards != 0)
					{
						$text .= ' <a href="javascript:del_player_awards(\''.$pid.'\');" title="'.EB_LADDERM_L68.'" onclick="return confirm(\''.EB_LADDERM_L69.'\')"><img src="'.e_PLUGIN.'ebattles/images/award_star_delete.ico" alt="'.EB_LADDERM_L68.'"/></a>';
					}
					$text .= '</td>';
					$text .= '</tr>';
				}
				$text .= '</tbody></table>';
				$text .= '</form>';
			}
			break;
			default:
		}

		$text .= '
		</div>
		';  // tab-page "Ladder Players/Teams"

		//***************************************************************************************
		// tab-page "Ladder Reset"
		$text .= '<div id="tabs-5">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_LADDERM_L70.'<div class="smalltext">'.EB_LADDERM_L71.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('ladderresetscores', 'bin_closed.png', EB_LADDERM_L72, '', EB_LADDERM_L73);
		$text .= '
		</td>
		</tr>
		';
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_LADDERM_L74.'<div class="smalltext">'.EB_LADDERM_L75.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('ladderresetladder', 'bin_closed.png', EB_LADDERM_L76, '', EB_LADDERM_L77);
		$text .= '
		</td>
		</tr>
		';
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_LADDERM_L78.'<div class="smalltext">'.EB_LADDERM_L79.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('ladderdelete', 'delete.png', EB_LADDERM_L80, 'negative', EB_LADDERM_L81);
		$text .= '
		</td>
		</tr>
		';
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_LADDERM_L82.'<div class="smalltext">'.EB_LADDERM_L83.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('ladderupdatescores', 'chart_curve.png', EB_LADDERM_L84, '', EB_LADDERM_L85);
		$text .= '
		</td>
		</tr>
		</tbody>
		</table>
		</form>
		</div>
		';  // tab-page "Ladder Reset"

		//***************************************************************************************
		// tab-page "Ladder Stats"
		$cat_index = 0;
		$text .= '<div id="tabs-6">';
		$text .= EB_LADDERM_L86;
		$text .= "
		<script type='text/javascript'>
		var A_TPL = {
		'b_vertical' : false,
		'b_watch': true,
		'n_controlWidth': 100,
		'n_controlHeight': 16,
		'n_sliderWidth': 17,
		'n_sliderHeight': 16,
		'n_pathLeft' : 0,
		'n_pathTop' : 0,
		'n_pathLength' : 83,
		's_imgControl': 'images/slider/sldr3h_bg.gif',
		's_imgSlider': 'images/slider/sldr3h_sl.gif',
		'n_zIndex': 1
		}
		</script>
		";

		$text .= '<form id="ladderstatsform" action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%"><tbody>';

		$text .= '
		<tr>
		<th class="eb_th2">'.EB_LADDERM_L87.'</th>
		<th class="eb_th2" colspan="2">'.EB_LADDERM_L88.'</th>
		<th class="eb_th2">'.EB_LADDERM_L89.'</th>
		</tr>';
		if ($ladder->getField('Type') != "ClanWar")
		{
			$text .= '
			<tr>
			<td class="eb_td">'.EB_LADDERM_L90.'</td>
			<td class="eb_td">
			<input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
			</td>
			<td class="eb_td">
			';
			$text .= "
			<script type='text/javascript'>
			var A_INIT = {
			's_form' : 'ladderstatsform',
			's_name': 'sliderValue".$cat_index."',
			'n_minValue' : 0,
			'n_maxValue' : 10,
			'n_value' : ".$ladder->getField('nbr_games_to_rank').",
			'n_step' : 1
			}

			new slider(A_INIT, A_TPL);
			</script>
			";
			$text .= '
			</td>
			<td class="eb_td"></td>
			</tr>
			';
			$cat_index ++;
		}

		if (($ladder->getField('Type') == "Team Ladder")||($ladder->getField('Type') == "ClanWar"))
		{
			$text .= '
			<tr>
			<td class="eb_td">'.EB_LADDERM_L91.'</td>
			<td class="eb_td">
			<input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
			</td>
			<td class="eb_td">
			';
			$text .= "
			<script type='text/javascript'>
			var A_INIT = {
			's_form' : 'ladderstatsform',
			's_name': 'sliderValue".$cat_index."',
			'n_minValue' : 0,
			'n_maxValue' : 10,
			'n_value' : ".$ladder->getField('nbr_team_games_to_rank').",
			'n_step' : 1
			}

			new slider(A_INIT, A_TPL);
			</script>
			";
			$text .= '
			</td>
			<td class="eb_td"></td>
			</tr>
			';
			$cat_index ++;
		}

		$q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
		." FROM ".TBL_STATSCATEGORIES
		." WHERE (".TBL_STATSCATEGORIES.".Ladder = '$ladder_id')";

		$result_1 = $sql->db_Query($q_1);
		$numCategories = mysql_numrows($result_1);

		$rating_max=0;
		for($i=0; $i<$numCategories; $i++)
		{
			$cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");
			$cat_min = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMinValue");
			$cat_max = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMaxValue");
			$cat_InfoOnly = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".InfoOnly");

			switch ($cat_name)
			{

				case "ELO":
				$cat_name_display = EB_LADDERM_L92;
				break;
				case "GamesPlayed":
				$cat_name_display = EB_LADDERM_L93;
				break;
				case "VictoryRatio":
				$cat_name_display = EB_LADDERM_L94;
				break;
				case "VictoryPercent":
				$cat_name_display = EB_LADDERM_L95;
				break;
				case "WinDrawLoss":
				$cat_name_display = EB_LADDERM_L96;
				break;
				case "UniqueOpponents":
				$cat_name_display = EB_LADDERM_L97;
				break;
				case "OpponentsELO":
				$cat_name_display = EB_LADDERM_L98;
				break;
				case "Streaks":
				$cat_name_display = EB_LADDERM_L99;
				break;
				case "Skill":
				$cat_name_display = EB_LADDERM_L100;
				break;
				case "Score":
				$cat_name_display = EB_LADDERM_L101;
				break;
				case "ScoreAgainst":
				$cat_name_display = EB_LADDERM_L102;
				break;
				case "ScoreDiff":
				$cat_name_display = EB_LADDERM_L103;
				break;
				case "Points":
				$cat_name_display = EB_LADDERM_L104;
				break;
				default:
			}

			//---------------------------------------------------
			$text .= '
			<tr>
			<td class="eb_td">'.$cat_name_display.'</td>
			<td class="eb_td">
			<input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
			</td>
			<td class="eb_td">
			';
			$text .= "
			<script type='text/javascript'>
			var A_INIT = {
			's_form' : 'ladderstatsform',
			's_name': 'sliderValue".$cat_index."',
			'n_minValue' : 0,
			'n_maxValue' : 100,
			'n_value' : ".$cat_max.",
			'n_step' : 1
			}

			new slider(A_INIT, A_TPL);
			</script>
			";
			$text .= '</td>';

			$text .= '
			<td class="eb_td">
			<input class="tbox" type="checkbox" name="infoonly'.$i.'" value="1"
			';
			if ($cat_InfoOnly == TRUE)
			{
				$text .= ' checked="checked"';
			}
			else
			{
				$rating_max+=$cat_max;

			}
			$text .= '/></td>';

			$text .= '</tr>';
			//----------------------------------------

			$cat_index++;
		}

		$text .= '
		<tr>
		<td class="eb_td">'.EB_LADDERM_L105.'</td>
		<td class="eb_td">'.$rating_max.'</td>
		<td class="eb_td" colspan="2">
		<input class="tbox" type="checkbox" name="hideratings" value="1"
		';
		if ($ladder->getField('hide_ratings_column') == TRUE)
		{
			$text .= ' checked="checked"';
		}
		$text .= '/>&nbsp;'.EB_LADDERM_L106.'</td>';

		$text .= '
		</tr></tbody></table>

		<!-- Save Button -->
		<table><tr><td>
		<div>
		'.ebImageTextButton('ladderstatssave', 'disk.png', EB_LADDERM_L107).'
		</div>
		</td></tr></table>
		</form>
		</div>';   // tab-page "Ladder Stats"

		//***************************************************************************************
		// tab-page "Ladder Settings"
		$text .= '<div id="tabs-7">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/ladderprocess.php?LadderID='.$ladder_id.'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		//<!-- Enable/Disable Challenges -->
		$text .= '
		<tr>
		<td class="eb_td"><b>'.EB_LADDERM_L122.'</b></td>
		<td class="eb_td">
		<div>
		';
		$text .= '<input class="tbox" type="checkbox" name="ladderchallengesenable"';
		if ($ladder->getField('ChallengesEnable') == TRUE)
		{
			$text .= ' checked="checked"/>';
		}
		else
		{
			$text .= '/>';
		}
		$text .= '
		</div>
		</td>
		</tr>
		';

		//<!-- Max number of Dates per Challenge -->
		$text .= '
		<tr>
		<td class="eb_td"><b>'.EB_LADDERM_L124.'</b></td>
		<td class="eb_td">
		<div>
		';
		$text .= '<input class="tbox" type="text" name="ladderdatesperchallenge" size="2" value="'.$ladder->getField('MaxDatesPerChallenge').'"';
		$text .= '
		</div>
		</td>
		</tr>
		';

		// ------------------------------
		$text .= '
		</tbody>
		</table>
		';

		//<!-- Save Button -->
		$text .= '
		<table><tr><td>
		<div>
		'.ebImageTextButton('ladderchallengessave', 'disk.png', EB_LADDERM_L123).'
		</div>
		</td></tr></table>

		</form>
		</div>
		';  // tab-page "Ladder Challenges"

		$text .= '</div>';
	}
}

$ns->tablerender($ladder->getField('Name')." ($egame - ".ladderTypeToString($ladder->getField('Type')).") - ".EB_LADDERM_L1, $text);
require_once(FOOTERF);
exit;
?>
