<?php
/**
* EventManage.php
*
*
*/

require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/event.php");
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
<script type="text/javascript" src="./js/event.js"></script>
<script type="text/javascript" src="./js/slider.js"></script>
';

$event_id = $_GET['eventid'];

if (!$event_id)
{
	header("Location: ./events.php");
	exit();
}
else
{
	$q = "SELECT ".TBL_EVENTS.".*, "
	.TBL_GAMES.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_EVENTS.", "
	.TBL_GAMES.", "
	.TBL_USERS
	." WHERE (".TBL_EVENTS.".EventID = '$event_id')"
	."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
	."   AND (".TBL_USERS.".user_id = ".TBL_EVENTS.".Owner)";

	$result = $sql->db_Query($q);
	$egame = mysql_result($result,0 , TBL_GAMES.".Name");
	$egameicon  = mysql_result($result,0 , TBL_GAMES.".Icon");
	$egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
	$eowner = mysql_result($result,0 , TBL_USERS.".user_id");
	$eownername = mysql_result($result,0 , TBL_USERS.".user_name");

	$event = new Event($event_id);
	$eventStatus = $event->getField('Status');
	$rounds = unserialize($event->getFieldHTML('Rounds'));

	$type = $event->getField('Type');
	switch($type)
	{
		case "One Player Ladder":
		case "Team Ladder":
		case "Clan Ladder":
		$event_type = 'Ladder';
		$seeding_enabled = false;
		break;
		case "One Player Tournament":
		case "Clan Tournament":
		$event_type = 'Tournament';
		$seeding_enabled = true;
		default:
	}
	if($event->getField('Status')=='active')
	{
		$seeding_enabled = false;
	}

	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$eowner) $can_manage = 1;
	if ($can_manage == 0)
	{
		header("Location: ./eventinfo.php?eventid=$event_id");
		exit();
	}
	else
	{
		$text .= '<div id="tabs">';
		$text .= '<ul>';
		$text .= '<li><a href="#tabs-1">'.EB_EVENTM_L2.'</a></li>';
		$text .= '<li><a href="#tabs-2">'.EB_EVENTM_L3.'</a></li>';
		$text .= '<li><a href="#tabs-4">'.EB_EVENTM_L5.'</a></li>';
		$text .= '<li><a href="#tabs-5">'.EB_EVENTM_L6.'</a></li>';
		switch($event_type)
		{
			case 'Ladder':
			$text .= '<li><a href="#tabs-6">'.EB_EVENTM_L7.'</a></li>';
			$text .= '<li><a href="#tabs-7">'.EB_EVENTM_L121.'</a></li>';
			break;
			case 'Tournament':
			$text .= '<li><a href="#tabs-6">'.EB_EVENTM_L143.'</a></li>';
			break;
		}
		$text .= '</ul>';

		//***************************************************************************************
		// tab-page "Event Summary"
		$text .= '<div id="tabs-1">';

		$text .= '<table class="eb_table" style="width:95%">';
		$text .= '<tbody>';
		$text .= '<tr><td>';
		$text .= '
		<form action="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'" method="post">
		'.ebImageTextButton('submit', 'magnify.png', EB_EVENTM_L133).'
		</form>';
		$text .= '</td></tr>';
		$text .= '</tbody>';
		$text .= '</table>';

		$text .= '
		<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">
		<table class="eb_table" style="width:95%">
		<tbody>
		';

		$text .= '<!-- Event Status -->';
		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L135.'<br />';
		$text .= '</td>';
		$text .= '<td class="eb_td">';

		$text .= '<table class="table_left">';
		$text .= '<tr>';
		$text .= '<td>'.$event->eventStatusToString().'</td>';

		if($eventStatus == 'draft')
		{
			$text .= '<td>'.ebImageTextButton('eventpublish', 'thumb_up.png', EB_EVENTM_L137).'</td>';
		}

		$text .= '</tr>';
		$text .= '</table>';

		$text .= '</td>';
		$text .= '</tr>';

		$text .= '<!-- Event Owner -->';
		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L9.'<br />';
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
		$text .= '<td><select class="tbox" name="eventowner">';
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
		$text .= ebImageTextButton('eventchangeowner', 'user_go.ico', EB_EVENTM_L10);
		$text .= '</td>';
		$text .= '</tr>';
		$text .= '</table>';
		$text .= '</td>';
		$text .= '</tr>';

		$text .= '<!-- Event Mods -->';
		$q = "SELECT ".TBL_EVENTMODS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_EVENTMODS.", "
		.TBL_USERS
		." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
		."   AND (".TBL_USERS.".user_id = ".TBL_EVENTMODS.".User)";
		$result = $sql->db_Query($q);
		$numMods = mysql_numrows($result);
		$text .= '
		<tr>
		';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L11.'</td>';
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
				$text .= '<input type="hidden" name="eventmod" value="'.$modid.'"/>';
				$text .= ebImageTextButton('eventdeletemod', 'user_delete.ico', EB_EVENTM_L12, 'negative jq-button', EB_EVENTM_L13);
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
		'.ebImageTextButton('eventaddmod', 'user_add.png', EB_EVENTM_L14).'
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
		';  // tab-page "Event Summary"

		//***************************************************************************************
		// tab-page "Event Settings"
		$text .= '<div id="tabs-2">';

		$text .= $event->displayEventSettingsForm();

		$text .= '
		</div>
		';  // tab-page "Event Settings"

		//***************************************************************************************
		// tab-page "Event Players/Teams"
		$text .= '<div id="tabs-4">';

		$pages = new Paginator;

		$array = array(
		'name'   => array(EB_EVENTM_L55, TBL_USERS.'.user_name'),
		'joined'   => array(EB_EVENTM_L56, TBL_PLAYERS.'.Joined')
		);

		if (!isset($_GET['orderby'])) $_GET['orderby'] = 'joined';
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
		." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
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
		switch($event->getField('Type'))
		{
			case "Team Ladder":
			case "Clan Ladder":
			case "Clan Tournament":
			$q = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TEAMS
			." WHERE (".TBL_TEAMS.".Event = '$event_id')";
			$result = $sql->db_Query($q);
			$row = mysql_fetch_array($result);
			$numTeams = $row['NbrTeams'];

			$text .= '<div class="spacer">';
			$text .= '<p>';
			$text .= $numTeams.' '.EB_EVENTM_L114.'<br />';
			$text .= '</p>';
			$text .= '</div>';
			break;
			default:
		}

		/* Number of players */
		switch($event->getField('Type'))
		{
			case "One Player Ladder":
			case "Team Ladder":
			case "One Player Tournament":
			$text .= '<div class="spacer">';
			$text .= '<p>';
			$text .= $numPlayers.' '.EB_EVENTM_L40.'<br />';
			$text .= '</p>';
			$text .= '</div>';
			break;
			default:
		}

		/* Add Team/Player */
		switch($event->getField('Type'))
		{
			case "Team Ladder":
			case "Clan Ladder":
			case "Clan Tournament":
			if ($numTeams<$event->getField('MaxNumberPlayers'))
			{
				// Form to add a team's division to the event
				$q = "SELECT ".TBL_DIVISIONS.".*, "
				.TBL_CLANS.".*"
				." FROM ".TBL_DIVISIONS.", "
				.TBL_CLANS
				." WHERE (".TBL_DIVISIONS.".Game = '$egameid')"
				."   AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
				$result = $sql->db_Query($q);
				/* Error occurred, return given name by default */
				$numDivisions = mysql_numrows($result);

				$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
				$text .= '
				<table class="eb_table" style="width:95%">
				<tbody>
				<tr>
				<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L41.'</td>
				<td class="eb_td">
				<table class="table_left">
				<tr>
				<td><div><select class="tbox" name="division">
				';
				for($i=0; $i<$numDivisions; $i++)
				{
					$did  = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");
					$dname  = mysql_result($result,$i, TBL_CLANS.".Name");
					
					$q_Teams = "SELECT COUNT(*) as nbrTeams"
					." FROM ".TBL_TEAMS
					." WHERE (".TBL_TEAMS.".Event = '$event_id')"
					." AND (".TBL_TEAMS.".Division = '$did')";
					$result_Teams = $sql->db_Query($q_Teams);
					$row = mysql_fetch_array($result_Teams);
					$nbrTeams = $row['nbrTeams'];
					if ($nbrTeams==0)
					{
						$text .= '<option value="'.$did.'">'.$dname.'</option>';
					}
				}
				$text .= '
				</select></div></td>
				<td>'.ebImageTextButton('eventaddteam', 'user_add.png', EB_EVENTM_L42).'</td>
				<td><div><input class="tbox" type="checkbox" name="eventaddteamnotify"/>'.EB_EVENTM_L43.'</div></td>
				</tr>
				</table>
				</td>
				</tr>
				</tbody>
				</table>
				</form>
				';
			}
			break;
			case "One Player Ladder":
			case "One Player Tournament":
			if ($numPlayers<$event->getField('MaxNumberPlayers'))
			{
				// Form to add a player to the event
				$q = "SELECT ".TBL_GAMERS.".*, "
				.TBL_USERS.".*"
				." FROM ".TBL_GAMERS.", "
				.TBL_USERS
				." WHERE (".TBL_GAMERS.".Game = '$egameid')"
				."   AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)";
				$result = $sql->db_Query($q);
				$numUsers = mysql_numrows($result);
				$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
				$text .= '
				<table class="eb_table" style="width:95%">
				<tbody>
				<tr>
				<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L44.'</td>
				<td class="eb_td">
				<table class="table_left">
				<tr>
				<td><div><select class="tbox" name="player">
				';
				for($i=0; $i<$numUsers; $i++)
				{
					$uid  = mysql_result($result,$i, TBL_USERS.".user_id");
					$uname  = mysql_result($result,$i, TBL_GAMERS.".Name");

					// fm: can we do this in 1 query?
					$q_Players = "SELECT COUNT(*) as NbrPlayers"
					." FROM ".TBL_PLAYERS.", "
					.TBL_GAMERS
					." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
					." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
					." AND (".TBL_GAMERS.".User = '$uid')";
					$result_Players = $sql->db_Query($q_Players);
					$row = mysql_fetch_array($result_Players);
					$nbrPlayers = $row['NbrPlayers'];
					if ($nbrPlayers==0)
					{
						$text .= '<option value="'.$uid.'">'.$uname.'</option>';
					}
				}
				$text .= '
				</select></div></td>
				<td>'.ebImageTextButton('eventaddplayer', 'user_add.png', EB_EVENTM_L45).'</td>
				<td><div><input class="tbox" type="checkbox" name="eventaddplayernotify"/>'.EB_EVENTM_L46.'</div></td>
				</tr>
				</table>
				</td>
				</tr>
				</tbody>
				</table>
				</form>
				';
			}
			break;
			default:
		}

		$text .= '<br />';
		$text .= '<table class="table_left">';
		$text .= '<tr><td style="vertical-align:top">'.EB_EVENTM_L47.':</td>';
		$text .= '<td>'.EB_EVENTM_L48.'</td></tr>';
		$text .= '<tr><td style="vertical-align:top">'.EB_EVENTM_L49.':</td>';
		$text .= '<td>'.EB_EVENTM_L50.'</td></tr>';
		$text .= '</table>';

		switch($event->getField('Type'))
		{
			case "Team Ladder":
			case "Clan Ladder":
			case "Clan Tournament":
			// Show list of teams here
			switch($event_type)
			{
				case 'Ladder':
				$order_by_str = " ORDER BY ".TBL_CLANS.".Name";
				break;
				case 'Tournament':
				$order_by_str = " ORDER BY ".TBL_TEAMS.".Seed";
				break;
			}

			$q_Teams = "SELECT ".TBL_CLANS.".*, "
			.TBL_TEAMS.".*, "
			.TBL_DIVISIONS.".* "
			." FROM ".TBL_CLANS.", "
			.TBL_TEAMS.", "
			.TBL_DIVISIONS
			." WHERE (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)"
			." AND (".TBL_TEAMS.".Division = ".TBL_DIVISIONS.".DivisionID)"
			." AND (".TBL_TEAMS.".Event = '$event_id')"
			.$order_by_str;
			$result = $sql->db_Query($q_Teams);
			$num_rows = mysql_numrows($result);
			if(!$result || ($num_rows < 0)){
				$text .= EB_EVENTM_L51.'<br />';
			}
			if($num_rows == 0){
				$text .= EB_EVENTM_L115.'<br />';
			}
			else
			{
				if($seeding_enabled == true)
				{
					$text .= '<table class="table_left">';
					$text .= '<tr>';
					$text .= '<td>'.EB_EVENTM_L156.'</td>';
					$text .= '<td><form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
					$text .= ebImageTextButton('eventteamsshuffle', '', EB_EVENTM_L155);
					$text .= '</form></td>';
					$text .='<td>
					<div id="ajaxSpinnerContainer">
					<img src="'.e_PLUGIN.'ebattles/images/ajax-loader.gif" title="working...">
					'.EB_EVENTM_L157.'
					</div>
					</td>';
					$text .= '</tr>';
					$text .= '</table>';
				}
								
				$teams_list_id = ($seeding_enabled == true) ? 'teams_list_sortable' : 'teams_list';

				$text .= '<table id="'.$teams_list_id.'" class="eb_table" style="width:95%"><thead>';
				$text .= '<tr>';
				if($event_type == 'Tournament')
				{
					// Column "Seed"
					$text .= '<th class="eb_th2">'.EB_EVENTM_L154.'</th>';
				}
				$text .= '<th class="eb_th2">'.EB_CLANS_L5.'</th>';
				$text .= '<th class="eb_th2">'.EB_CLANS_L6.'</th>';
				$text .= '</tr></thead>';
				$text .= '<tbody>';
				for($i=0; $i < $num_rows; $i++){
					$clan_id  = mysql_result($result,$i, TBL_CLANS.".ClanID");
					$clan = new Clan($clan_id);
					$tid  = mysql_result($result,$i, TBL_TEAMS.".TeamID");
					$tseed  = mysql_result($result,$i, TBL_TEAMS.".Seed");
					if($tseed == 0) $tseed = $i+1;

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

					$text .= '<tr id="team_'.$tid.'">';
					if($event_type == 'Tournament')
					{
						// Column "Seed"
						$text .= '<td class="eb_td">'.$tseed.'</td>';
					}
					$text .= '<td class="eb_td">'.$image.'&nbsp;<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$clan_id.'">'.$clan->getField('Name').'</a></td>';
					$text .= '<td class="eb_td">'.$clan->getField('Tag').'</td>';
					$text .= '</tr>';
				}
				$text .= '</tbody></table>';
			}
			break;
			default:
		}

		switch($event->getField('Type'))
		{
			// TODO: paginate/sort only for ladders? Does it conflict with seeding?
			case "One Player Ladder":
			case "Team Ladder":
			case "One Player Tournament":
			// Show list of players here
			$orderby_array = $array["$orderby"];
			switch($event_type)
			{
				case 'Ladder':
				$order_by_str = " ORDER BY $orderby_array[1] $sort";
				break;
				case 'Tournament':
				$order_by_str = " ORDER BY ".TBL_PLAYERS.".Seed";
				break;
			}

			$q_Players = "SELECT ".TBL_PLAYERS.".*, "
			.TBL_GAMERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_PLAYERS.", "
			.TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
			." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			.$order_by_str
			." $pages->limit";
			$result = $sql->db_Query($q_Players);
			$num_rows = mysql_numrows($result);
			if(!$result || ($num_rows < 0)){
				$text .= EB_EVENTM_L51.'<br />';
			} else if($num_rows == 0){
				$text .= EB_EVENTM_L52.'<br />';
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
				if($seeding_enabled == true)
				{
					$text .= '<table class="table_left">';
					$text .= '<tr>';
					$text .= '<td>'.EB_EVENTM_L156.'</td>';
					$text .= '<td><form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
					$text .= ebImageTextButton('eventplayersshuffle', '', EB_EVENTM_L155);
					$text .= '</form></td>';
					$text .='<td>
					<div id="ajaxSpinnerContainer">
					<img src="'.e_PLUGIN.'ebattles/images/ajax-loader.gif" title="working...">
					'.EB_EVENTM_L157.'
					</div>
					</td>';
					$text .= '</tr>';
					$text .= '</table>';
				}
				
				$players_list_id = ($seeding_enabled == true) ? 'players_list_sortable' : 'players_list';
				
				$text .= '<form id="playersform" action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
				$text .= '<table id="'.$players_list_id.'" class="eb_table" style="width:95%"><thead>';
				$text .= '<tr>';

				if($event_type == 'Tournament')
				{
					// Column "Seed"
					$text .= '<th class="eb_th2">'.EB_EVENTM_L154.'</th>';
				}

				foreach($array as $opt=>$opt_array)
				{
					$text .= '<th class="eb_th2"><a href="'.e_PLUGIN.'ebattles/eventmanage.php?eventid='.$event_id.'&amp;orderby='.$opt.'&amp;sort='.$sort.'">'.$opt_array[0].'</a></th>';
				}
				$text .= '<th class="eb_th2">'.EB_EVENTM_L59;
				$text .= '<input type="hidden" id="ban_player" name="ban_player" value=""/>';
				$text .= '<input type="hidden" id="unban_player" name="unban_player" value=""/>';
				$text .= '<input type="hidden" id="kick_player" name="kick_player" value=""/>';
				$text .= '<input type="hidden" id="del_player_games" name="del_player_games" value=""/>';
				$text .= '<input type="hidden" id="del_player_awards" name="del_player_awards" value=""/>';
				$text .= '</th></tr></thead>';
				$text .= '<tbody>';
				for($i=0; $i<$num_rows; $i++)
				{
					$pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
					$puid = mysql_result($result,$i, TBL_USERS.".user_id");
					$pname  = mysql_result($result,$i, TBL_GAMERS.".Name");
					$puniquegameid  = mysql_result($result,$i, TBL_GAMERS.".UniqueGameID");
					$pjoined  = mysql_result($result,$i, TBL_PLAYERS.".Joined");
					$pjoined_local = $pjoined + TIMEOFFSET;
					$pseed  = mysql_result($result,$i, TBL_PLAYERS.".Seed");
					if($pseed == 0) $pseed = $i+1;
					$date  = date("d M Y",$pjoined_local);
					$pbanned = mysql_result($result,$i, TBL_PLAYERS.".Banned");
					$pgames = mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
					$pteam = mysql_result($result,$i, TBL_PLAYERS.".Team");
					list($pclan, $pclantag, $pclanid) = getClanInfo($pteam);

					$text .= '<tr id="player_'.$pid.'">';
					if($event_type == 'Tournament')
					{
						// Column "Seed"
						$text .= '<td class="eb_td">'.$pseed.'</td>';
					}

					$text .= '<td class="eb_td"><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$puid.'">'.$pclantag.$pname.'</a></td>';
					$text .= '<td class="eb_td">'.(($pbanned) ? EB_EVENTM_L54 : $date).'</td>';
					$text .= '<td class="eb_td">';
					if ($pbanned)
					{
						$text .= ' <a href="javascript:unban_player(\''.$pid.'\');" title="'.EB_EVENTM_L60.'" onclick="return confirm(\''.EB_EVENTM_L61.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_go.ico" alt="'.EB_EVENTM_L60.'"/></a>';
					}
					else
					{
						$text .= ' <a href="javascript:ban_player(\''.$pid.'\');" title="'.EB_EVENTM_L62.'" onclick="return confirm(\''.EB_EVENTM_L63.'\')"><img src="'.e_PLUGIN.'ebattles/images/user_delete.ico" alt="'.EB_EVENTM_L62.'"/></a>';
					}
					if (($pgames == 0)&&($pawards == 0))
					{
						$text .= ' <a href="javascript:kick_player(\''.$pid.'\');" title="'.EB_EVENTM_L64.'" onclick="return confirm(\''.EB_EVENTM_L65.'\')"><img src="'.e_PLUGIN.'ebattles/images/cross.png" alt="'.EB_EVENTM_L64.'"/></a>';
					}
					if ($pgames != 0)
					{
						$text .= ' <a href="javascript:del_player_games(\''.$pid.'\');" title="'.EB_EVENTM_L66.'" onclick="return confirm(\''.EB_EVENTM_L67.'\')"><img src="'.e_PLUGIN.'ebattles/images/controller_delete.ico" alt="'.EB_EVENTM_L66.'"/></a>';
					}
					if ($pawards != 0)
					{
						$text .= ' <a href="javascript:del_player_awards(\''.$pid.'\');" title="'.EB_EVENTM_L68.'" onclick="return confirm(\''.EB_EVENTM_L69.'\')"><img src="'.e_PLUGIN.'ebattles/images/award_star_delete.ico" alt="'.EB_EVENTM_L68.'"/></a>';
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
		';  // tab-page "Event Players/Teams"

		//***************************************************************************************
		// tab-page "Event Reset"
		$text .= '<div id="tabs-5">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';
		switch($event_type)
		{
			case Ladder:
			case Tournament:
			$text .= '
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L70.'<div class="smalltext">'.EB_EVENTM_L71.'</div></td>
			<td class="eb_td">
			';
			$text .= ebImageTextButton('eventresetscores', 'bin_closed.png', EB_EVENTM_L72, '', EB_EVENTM_L73);
			$text .= '
			</td>
			</tr>
			';
			break;
		}
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L74.'<div class="smalltext">'.EB_EVENTM_L75.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('eventresetevent', 'bin_closed.png', EB_EVENTM_L76, '', EB_EVENTM_L77);
		$text .= '
		</td>
		</tr>
		';
		$text .= '
		<tr>
		<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L78.'<div class="smalltext">'.EB_EVENTM_L79.'</div></td>
		<td class="eb_td">
		';
		$text .= ebImageTextButton('eventdelete', 'delete.png', EB_EVENTM_L80, 'negative jq-button', EB_EVENTM_L81);
		$text .= '
		</td>
		</tr>
		';
		switch($event_type)
		{
			case Ladder:
			$text .= '
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L82.'<div class="smalltext">'.EB_EVENTM_L83.'</div></td>
			<td class="eb_td">
			';
			$text .= ebImageTextButton('eventupdatescores', 'chart_curve.png', EB_EVENTM_L84, '', EB_EVENTM_L85);
			$text .= '
			</td>
			</tr>
			';
			break;
		}
		$text .= '
		</tbody>
		</table>
		</form>
		</div>
		';  // tab-page "Event Reset"

		switch($event_type)
		{
			case 'Ladder':
			//***************************************************************************************
			// tab-page "Event Stats"
			$cat_index = 0;
			$text .= '<div id="tabs-6">';
			$text .= EB_EVENTM_L86;
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

			$text .= '<form id="eventstatsform" action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%"><tbody>';

			$text .= '
			<tr>
			<th class="eb_th2">'.EB_EVENTM_L87.'</th>
			<th class="eb_th2" colspan="2">'.EB_EVENTM_L88.'</th>
			<th class="eb_th2">'.EB_EVENTM_L89.'</th>
			</tr>';
			if ($event->getField('Type') != "Clan Ladder")
			{
				$text .= '
				<tr>
				<td class="eb_td">'.EB_EVENTM_L90.'</td>
				<td class="eb_td">
				<input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
				</td>
				<td class="eb_td">
				';
				$text .= "
				<script type='text/javascript'>
				var A_INIT = {
				's_form' : 'eventstatsform',
				's_name': 'sliderValue".$cat_index."',
				'n_minValue' : 0,
				'n_maxValue' : 10,
				'n_value' : ".$event->getField('nbr_games_to_rank').",
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

			if (($event->getField('Type') == "Team Ladder")||($event->getField('Type') == "Clan Ladder"))
			{
				$text .= '
				<tr>
				<td class="eb_td">'.EB_EVENTM_L91.'</td>
				<td class="eb_td">
				<input name="sliderValue'.$cat_index.'" id="sliderValue'.$cat_index.'" class="tbox" type="text" size="3" onchange="A_SLIDERS['.$cat_index.'].f_setValue(this.value)"/>
				</td>
				<td class="eb_td">
				';
				$text .= "
				<script type='text/javascript'>
				var A_INIT = {
				's_form' : 'eventstatsform',
				's_name': 'sliderValue".$cat_index."',
				'n_minValue' : 0,
				'n_maxValue' : 10,
				'n_value' : ".$event->getField('nbr_team_games_to_rank').",
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
			." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')";

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
					$cat_name_display = EB_EVENTM_L92;
					break;
					case "GamesPlayed":
					$cat_name_display = EB_EVENTM_L93;
					break;
					case "VictoryRatio":
					$cat_name_display = EB_EVENTM_L94;
					break;
					case "VictoryPercent":
					$cat_name_display = EB_EVENTM_L95;
					break;
					case "WinDrawLoss":
					$cat_name_display = EB_EVENTM_L96;
					break;
					case "UniqueOpponents":
					$cat_name_display = EB_EVENTM_L97;
					break;
					case "OpponentsELO":
					$cat_name_display = EB_EVENTM_L98;
					break;
					case "Streaks":
					$cat_name_display = EB_EVENTM_L99;
					break;
					case "Skill":
					$cat_name_display = EB_EVENTM_L100;
					break;
					case "Score":
					$cat_name_display = EB_EVENTM_L101;
					break;
					case "ScoreAgainst":
					$cat_name_display = EB_EVENTM_L102;
					break;
					case "ScoreDiff":
					$cat_name_display = EB_EVENTM_L103;
					break;
					case "Points":
					$cat_name_display = EB_EVENTM_L104;
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
				's_form' : 'eventstatsform',
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
			<td class="eb_td">'.EB_EVENTM_L105.'</td>
			<td class="eb_td">'.$rating_max.'</td>
			<td class="eb_td" colspan="2">
			<input class="tbox" type="checkbox" name="hideratings" value="1"
			';
			if ($event->getField('hide_ratings_column') == TRUE)
			{
				$text .= ' checked="checked"';
			}
			$text .= '/>&nbsp;'.EB_EVENTM_L106.'</td>';

			$text .= '
			</tr></tbody></table>

			<!-- Save Button -->
			<table><tr><td>
			<div>
			'.ebImageTextButton('eventstatssave', 'disk.png', EB_EVENTM_L107).'
			</div>
			</td></tr></table>
			</form>
			</div>';   // tab-page "Event Stats"

			//***************************************************************************************
			// tab-page "Event Challenges"
			$text .= '<div id="tabs-7">';
			$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?eventid='.$event_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%">
			<tbody>
			';
			//<!-- Enable/Disable Challenges -->
			$text .= '
			<tr>
			<td class="eb_td"><b>'.EB_EVENTM_L122.'</b></td>
			<td class="eb_td">
			<div>
			';
			$text .= '<input class="tbox" type="checkbox" name="eventchallengesenable"';
			if ($event->getField('ChallengesEnable') == TRUE)
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
			<td class="eb_td"><b>'.EB_EVENTM_L124.'</b></td>
			<td class="eb_td">
			<div>
			';
			$text .= '<input class="tbox" type="text" name="eventdatesperchallenge" size="2" value="'.$event->getField('MaxDatesPerChallenge').'"/>';
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
			'.ebImageTextButton('eventchallengessave', 'disk.png', EB_EVENTM_L123).'
			</div>
			</td></tr></table>

			</form>
			</div>
			';  // tab-page "Event Challenges"
			break;
			case 'Tournament':
			//***************************************************************************************
			// tab-page "Brackets"
			$text .= '<div id="tabs-6">';

			list($bracket_html) = $event->brackets();
			$text .= $bracket_html;

			$text .= '</div>';  // tab-page "Brackets"
			break;
		}
		$text .= '</div>';
	}
}

$ns->tablerender($event->getField('Name')." ($egame - ".$event->eventTypeToString().") - ".EB_EVENTM_L1, $text);
require_once(FOOTERF);
exit;
?>
