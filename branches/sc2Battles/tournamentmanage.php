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
';

$event_id = $_GET['EventID'];
$self = $_SERVER['PHP_SELF'];

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
	$rounds = unserialize($event->getField('Rounds'));

	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$eowner) $can_manage = 1;
	if ($can_manage == 0)
	{
		header("Location: ./eventinfo.php?EventID=$event_id");
		exit();
	}
	else
	{
		$text .= '<div id="tabs">';
		$text .= '<ul>';
		$text .= '<li><a href="#tabs-1">'.EB_EVENTM_L2.'</a></li>';
		$text .= '<li><a href="#tabs-2">'.EB_EVENTM_L3.'</a></li>';
		/*$text .= '<li><a href="#tabs-3">'.EB_EVENTM_L4.'</a></li>';*/
		$text .= '<li><a href="#tabs-4">'.EB_EVENTM_L5.'</a></li>';
		$text .= '<li><a href="#tabs-5">'.EB_EVENTM_L6.'</a></li>';
		$text .= '<li><a href="#tabs-6">'.EB_EVENTM_L7.'</a></li>';
		$text .= '</ul>';

		//***************************************************************************************
		// tab-page "Event Summary"
		$text .= '<div id="tabs-1">';

		$text .= '<table class="eb_table" style="width:95%">';
		$text .= '<tbody>';
		$text .= '<tr><td>';
		$text .= '
		<form action="'.e_PLUGIN.'ebattles/eventinfo.php?EventID='.$event_id.'" method="post">
		'.ebImageTextButton('submit', 'magnify.png', EB_EVENTM_L133).'
		</form>';
		$text .= '</td></tr>';
		$text .= '</tbody>';
		$text .= '</table>';

		$text .= '
		<form action="'.e_PLUGIN.'ebattles/eventprocess.php?EventID='.$event_id.'" method="post">
		<table class="eb_table" style="width:95%">
		<tbody>
		';

		$text .= '<tr>';
		$text .= '<td class="eb_td eb_tdc1 eb_w40">'.EB_EVENTM_L135.'<br />';
		$text .= '</td>';
		$text .= '<td class="eb_td">';

		switch($eventStatus)
		{
			case 'draft':
			$text .= '<table class="table_left">';
			$text .= '<tr>';
			$text .= '<td>'.EB_EVENTM_L136.'</td>';
			$text .= '<td>'.ebImageTextButton('eventpublish', 'thumb_up.png', EB_EVENTM_L137).'</td>';
			$text .= '</tr>';
			$text .= '</table>';
			break;
			case 'signup':
			$text .= EB_EVENTM_L138;
			break;
			case 'checkin':
			$text .= EB_EVENTM_L139;
			break;
			case 'active':
			$text .= EB_EVENTM_L140;
			break;
			case 'finished':
			$text .= EB_EVENTM_L141;
			break;
		}

		$text .= '</td>';
		$text .= '</tr>';

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

		$q = "SELECT ".TBL_MODS.".*, "
		.TBL_USERS.".*"
		." FROM ".TBL_MODS.", "
		.TBL_USERS
		." WHERE (".TBL_MODS.".Event = '$event_id')"
		."   AND (".TBL_USERS.".user_id = ".TBL_MODS.".User)";
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
		// tab-page "Event"
		/*
		$text .= '<div id="tabs-3">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?EventID='.$event_id.'" method="post">';



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
		'.ebImageTextButton('eventrulessave', 'disk.png', EB_EVENTM_L39).'
		</div>
		</td></tr></table>

		</form>
		</div>
		';  // tab-page "Event Rules"
		*/

		//***************************************************************************************
		// tab-page "Brackets"
		$text .= '<div id="tabs-4">';

		$teams = array();
		$type = $event->getField('MatchType');
		switch($type)
		{
			default:
				$q_Players = "SELECT ".TBL_GAMERS.".*, "
				.TBL_TPLAYERS.".*"
				." FROM ".TBL_GAMERS.", "
				.TBL_TPLAYERS.", "
				.TBL_USERS
				." WHERE (".TBL_TPLAYERS.".Event = '".$event->getField('EventID')."')"
				." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
				." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
				." ORDER BY ".TBL_TPLAYERS.".Joined";
				$result = $sql->db_Query($q_Players);
				$nbrPlayers = mysql_numrows($result);
				for ($player = 0; $player < $nbrPlayers; $player++)
				{
					$playerID = mysql_result($result, $player, TBL_TPLAYERS.".TPlayerID");
					$gamerID = mysql_result($result, $player, TBL_GAMERS.".GamerID");
					$gamer = new Gamer($gamerID);
					$teams[$player]['Name'] = $gamer->getField('UniqueGameID');
					$teams[$player]['PlayerID'] = $playerID;
				}
		}

		$results = unserialize($event->getField('Results'));
		list($bracket_html) = brackets($event->getField('Type'), $event->getField('MaxNumberPlayers'), $teams, $results, $rounds);
		$text .= $bracket_html;
		//$event->updateResults($results);
		//$event->updateDB($results);

		$text .= '</div>';  // tab-page "Brackets"

		//***************************************************************************************
		// tab-page "Event Players/Teams"
		$text .= '<div id="tabs-5">';

		$pages = new Paginator;

		$array = array(
		'name'   => array(EB_EVENTM_L55, TBL_USERS.'.user_name'),
		'joined'   => array(EB_EVENTM_L56, TBL_TPLAYERS.'.Joined')
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
		." WHERE (".TBL_TPLAYERS.".Event = '$event_id')"
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
		switch($event->getField('MatchType'))
		{
			case "2v2":
			case "3v3":
			case "4v4":
			$q = "SELECT COUNT(*) as NbrTeams"
			." FROM ".TBL_TTEAMS
			." WHERE (".TBL_TTEAMS.".Event = '$event_id')";
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
		switch($event->getField('MatchType'))
		{
			default:
			$text .= '<div class="spacer">';
			$text .= '<p>';
			$text .= $numPlayers.' '.EB_EVENTM_L40.'<br />';
			$text .= '</p>';
			$text .= '</div>';
			break;
		}

		/* Add Team/Player */
		switch($event->getField('MatchType'))
		{
			case "2v2":
			case "3v3":
			case "4v4":
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

			$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?EventID='.$event_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%">
			<tbody>
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">
			<b>'.EB_EVENTM_L41.'</b>
			</td>
			<td class="eb_td">
			<select class="tbox" name="division">
			';
			for($i=0; $i<$numDivisions; $i++)
			{
				// TODO: remove teams already signed up
				$did  = mysql_result($result,$i, TBL_DIVISIONS.".DivisionID");
				$dname  = mysql_result($result,$i, TBL_CLANS.".Name");
				$text .= '<option value="'.$did.'">'.$dname.'</option>';
			}
			$text .= '
			</select>
			'.ebImageTextButton('eventaddteam', 'user_add.png', EB_EVENTM_L42).'
			<input class="tbox" type="checkbox" name="eventaddteamnotify"/>'.EB_EVENTM_L43.'
			</td>
			</tr>
			</tbody>
			</table>
			</form>
			';
			break;
			case "":
			case "1v1":
			// TODO: No good...
			// Form to add a player to the event
			$q = "SELECT ".TBL_GAMERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_GAMERS.".Game = '$egameid')"
			."   AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)";
			$result = $sql->db_Query($q);
			$numUsers = mysql_numrows($result);
			$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?EventID='.$event_id.'" method="post">';
			$text .= '
			<table class="eb_table" style="width:95%">
			<tbody>
			<tr>
			<td class="eb_td eb_tdc1 eb_w40">
			<b>'.EB_EVENTM_L44.'</b>
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
				
				$q_Players = "SELECT COUNT(*) as NbrPlayers"
				." FROM ".TBL_TPLAYERS.", "
				.TBL_GAMERS
				." WHERE (".TBL_TPLAYERS.".Event = '$event_id')"
				." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
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
			break;
			default:
		}

		$text .= '<br />';
		$text .= '<table>';
		$text .= '<tr><td style="vertical-align:top">'.EB_EVENTM_L47.':</td>';
		$text .= '<td>'.EB_EVENTM_L48.'</td></tr>';
		$text .= '<tr><td style="vertical-align:top">'.EB_EVENTM_L49.':</td>';
		$text .= '<td>'.EB_EVENTM_L50.'</td></tr>';
		$text .= '</table>';

		switch($event->getField('MatchType'))
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
			." AND (".TBL_TTEAMS.".Event = '$event_id')";
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

		switch($event->getField('MatchType'))
		{
			default:
			$orderby_array = $array["$orderby"];
			$q_Players = "SELECT ".TBL_TPLAYERS.".*, "
			.TBL_GAMERS.".*, "
			.TBL_USERS.".*"
			." FROM ".TBL_TPLAYERS.", "
			.TBL_GAMERS.", "
			.TBL_USERS
			." WHERE (".TBL_TPLAYERS.".Event = '$event_id')"
			." AND (".TBL_TPLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
			." AND (".TBL_USERS.".user_id = ".TBL_GAMERS.".User)"
			." ORDER BY $orderby_array[1] $sort"
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
				$text .= '<form id="playersform" action="'.e_PLUGIN.'ebattles/eventprocess.php?EventID='.$event_id.'" method="post">';
				$text .= '<table class="eb_table" style="width:95%"><tbody>';
				$text .= '<tr>';
				foreach($array as $opt=>$opt_array)
				{
					$text .= '<th class="eb_th2"><a href="'.e_PLUGIN.'ebattles/eventmanage.php?EventID='.$event_id.'&amp;orderby='.$opt.'&amp;sort='.$sort.'">'.$opt_array[0].'</a></th>';
				}
				$text .= '<th class="eb_th2">'.EB_EVENTM_L59;
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
					$text .= '<td class="eb_td">'.(($pbanned) ? EB_EVENTM_L54 : $date).'</td>';
					//$text .= '<td class="eb_td">'.$pgames.'</td>';
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
		}

		$text .= '
		</div>
		';  // tab-page "Event Players/Teams"

		//***************************************************************************************
		// tab-page "Event Reset"
		$text .= '<div id="tabs-6">';
		$text .= '<form action="'.e_PLUGIN.'ebattles/eventprocess.php?EventID='.$event_id.'" method="post">';
		$text .= '
		<table class="eb_table" style="width:95%">
		<tbody>
		';

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
		$text .= '
		</tbody>
		</table>
		</form>
		</div>
		';  // tab-page "Event Reset"

		$text .= '</div>';
	}
}

$ns->tablerender($event->getField('Name')." ($egame - ".eventTypeToString($event->getField('Type')).") - ".EB_EVENTM_L1, $text);
require_once(FOOTERF);
exit;
?>
