<?php
/**
* UserInfo.php
*
* This page is for users to view their account information
* with a link added for them to edit the information.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/paginator.class.php");
require_once(e_HANDLER."rate_class.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/match.php");
require_once(e_PLUGIN."ebattles/include/challenge.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

$pages = new Paginator;
$rater = new rater();

$text .= '';

/* User */
$req_user = $_GET['user'];

if (!$req_user)
{
	header("Location: ./ladders.php"); // should be users.php which does not exist yet
	exit();
}
else
{
	$text .= '<script type="text/javascript" src="./js/tabpane.js"></script>';

	/* Logged in user viewing own account */
	if(strcmp(USERID,$req_user) == 0){
	}
	/* Visitor not viewing own account */
	else{
	}

	$q2 = "SELECT ".TBL_USERS.".*"
	." FROM ".TBL_USERS
	." WHERE (".TBL_USERS.".user_id = $req_user)";
	$result2 = $sql->db_Query($q2);
	$uid  = mysql_result($result2,0, TBL_USERS.".user_id");
	$uname  = mysql_result($result2,0, TBL_USERS.".user_name");

	$text .= '<div class="tab-pane" id="tab-pane-5">';

	/*
	---------------------
	Player Profile
	---------------------
	*/
	$text .= '<div class="tab-page">';    // tab-page"Profile"
	$text .= '<div class="tab">'.EB_USER_L2.'</div>';

	$text .= '<p>';
	$text .= EB_USER_L7.': <a href="'.e_BASE.'user.php?id.'.$req_user.'">'.$uname.'</a>';
	$text .= '</p>';

	$text .= '</div>';    // tab-page "Profile"

	/*
	---------------------
	Ladders
	---------------------
	*/
	$text .= '<div class="tab-page">';    // tab-page "Ladders"
	$text .= '<div class="tab">'.EB_USER_L3.'</div>
	';
	if((strcmp(USERID,$req_user) == 0)&&(check_class($pref['eb_ladders_create_class'])))
	{
		$text .= '<form action="'.e_PLUGIN.'ebattles/laddercreate.php" method="post">';
		$text .= '<div>';
		$text .= '<input type="hidden" name="userid" value="'.$req_user.'"/>';
		$text .= '<input type="hidden" name="username" value="'.$uname.'"/>';
		$text .= ebImageTextButton('createladder', 'add.png', EB_LADDERS_L20);
		$text .= '</div>';
		$text .= '</form><br />';
	}
	/* Display list of ladders where the user is a player */
	$text .= '<div class="spacer"><b>'.EB_USER_L8.'</b></div>';
	$text .= '<div>'.$uname.'&nbsp;'.EB_USER_L9.'</div>';
	$q = " SELECT *"
	." FROM ".TBL_PLAYERS.", "
	.TBL_GAMERS.", "
	.TBL_LADDERS.", "
	.TBL_GAMES
	." WHERE (".TBL_GAMERS.".User = '$req_user')"
	." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	."   AND (".TBL_PLAYERS.".Ladder = ".TBL_LADDERS.".LadderID)"
	."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	if ($num_rows>0)
	{
		/* Display table contents */
		$text .= '<table class="fborder" style="width:95%">';
		$text .= '<tr>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L10;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L11;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L12;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L13;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L14;
		$text .= '</td>';
		$text .= '</tr>';

		for($i=0; $i<$num_rows; $i++)
		{
			$ladder_id = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$lName  = mysql_result($result,$i, TBL_LADDERS.".Name");
			$lOwner = mysql_result($result,$i, TBL_LADDERS.".Owner");
			$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gIcon = mysql_result($result,$i , TBL_GAMES.".Icon");
			$player_id =  mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
			$pRank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
			$pWinLoss  = mysql_result($result,$i, TBL_PLAYERS.".Win")."/".mysql_result($result,$i, TBL_PLAYERS.".Draw")."/".mysql_result($result,$i, TBL_PLAYERS.".Loss");

			$q_Scores = "SELECT ".TBL_SCORES.".*, "
			.TBL_PLAYERS.".*"
			." FROM ".TBL_SCORES.", "
			.TBL_PLAYERS
			." WHERE (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
			." AND (".TBL_PLAYERS.".PlayerID = '$player_id')";

			$result_Scores = $sql->db_Query($q_Scores);
			$numScores = mysql_numrows($result_Scores);
			$prating = 0;
			$prating_votes = 0;
			for($scoreIndex=0; $scoreIndex<$numScores; $scoreIndex++)
			{
				$sid  = mysql_result($result_Scores,$scoreIndex, TBL_SCORES.".ScoreID");

				// Get user rating.
				$rate = $rater->getrating("ebscores", $sid);

				$prating += $rate[0]*($rate[1] + $rate[2]/10);
				$prating_votes += $rate[0];
			}
			if ($prating_votes !=0)
			{
				$prating /= $prating_votes;
			}
			$rating = displayRating($prating, $prating_votes);

			$text .= '<tr>';
			$text .= '<td class="forumheader3">';
			$text .= '<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.$lName.'</a><br />';
			$text .= '<img '.getGameIconResize($gIcon).'/> '.$gName;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			$text .= $pRank;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			$text .= $pWinLoss;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			$text .= $rating;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			if($lOwner == $req_user)
			{
				$text .= EB_USER_L15;
				if ($lOwner == USERID)
				{
					$text .= ' (<a href="'.e_PLUGIN.'ebattles/laddermanage.php?LadderID='.$ladder_id.'">'.EB_USER_L16.'</a>)';
				}
			}
			else
			{
				$text .= EB_USER_L17;
			}

			$text .= '</td>';
			$text .= '</tr>';
		}
		$text .= '</table>';
	}

	/* Display list of ladders where the user is the owner */
	$text .= '<br /><div class="spacer"><b>'.EB_USER_L18.'</b></div>';
	$text .= '<div>'.$uname.'&nbsp;'.EB_USER_L19.'</div>';
	$q = " SELECT *"
	." FROM ".TBL_LADDERS.", "
	.TBL_GAMES
	." WHERE (".TBL_LADDERS.".Owner = '$req_user')"
	."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)";

	$result = $sql->db_Query($q);
	$num_ladders = mysql_numrows($result);

	if ($num_ladders>0)
	{
		/* Display table contents */
		$text .= '<table class="fborder" style="width:95%">';
		$text .= '<tr>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L10;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L14;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L31;
		$text .= '</td>';
		$text .= '</tr>';

		for($i=0; $i<$num_ladders; $i++)
		{
			$ladder_id  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$lName  = mysql_result($result,$i, TBL_LADDERS.".Name");
			$lOwner  = mysql_result($result,$i, TBL_LADDERS.".Owner");
			$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gIcon = mysql_result($result,$i , TBL_GAMES.".Icon");

			$q_pending = "SELECT COUNT(*) as nbrMatchesPending"
			." FROM ".TBL_MATCHS
			." WHERE (".TBL_MATCHS.".Ladder = '$ladder_id')"
			."   AND (".TBL_MATCHS.".Status = 'pending')";
			$result_pending = $sql->db_Query($q_pending);
			$row = mysql_fetch_array($result_pending);
			$nbrMatchesPending = $row['nbrMatchesPending'];

			$text .= '<tr>';
			$text .= '<td class="forumheader3">';
			$text .= '<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.$lName.'</a><br />';
			$text .= '<img '.getGameIconResize($gIcon).'/> '.$gName;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			if($lOwner == $req_user)
			{
				$text .= EB_USER_L15;
				if ($lOwner == USERID)
				{
					$text .= ' (<a href="'.e_PLUGIN.'ebattles/laddermanage.php?LadderID='.$ladder_id.'">'.EB_USER_L16.'</a>)';
				}
			}
			else
			{
				$text .= EB_USER_L17;
			}
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			$text .= ($nbrMatchesPending>0) ? '<div><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/>&nbsp;<b>'.$nbrMatchesPending.'&nbsp;'.EB_LADDER_L64.'</b></div>' : '';
			$text .= '</td>';
			$text .= '</tr>';
		}
		$text .= '</table>';
	}

	/* Display list of ladders where the user is a moderator */
	$text .= '<br /><div class="spacer"><b>'.EB_USER_L20.'</b></div>';
	$text .= '<div>'.$uname.'&nbsp;'.EB_USER_L21.'</div>';
	$q = " SELECT *"
	." FROM ".TBL_MODS.", "
	.TBL_LADDERS.", "
	.TBL_GAMES
	." WHERE (".TBL_MODS.".User = '$req_user')"
	."   AND (".TBL_MODS.".Ladder = ".TBL_LADDERS.".LadderID)"
	."   AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	if ($num_rows>0)
	{
		/* Display table contents */
		$text .= '<table class="fborder" style="width:95%">';
		$text .= '<tr>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L10;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L14;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L31;
		$text .= '</td>';
		$text .= '</tr>';

		for($i=0; $i<$num_rows; $i++)
		{
			$ladder_id  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$lName  = mysql_result($result,$i, TBL_LADDERS.".Name");
			$lOwner  = mysql_result($result,$i, TBL_LADDERS.".Owner");
			$gName  = mysql_result($result,$i, TBL_GAMES.".Name");
			$gIcon = mysql_result($result,$i , TBL_GAMES.".Icon");

			$q_pending = "SELECT COUNT(*) as nbrMatchesPending"
			." FROM ".TBL_MATCHS
			." WHERE (".TBL_MATCHS.".Ladder = '$ladder_id')"
			."   AND (".TBL_MATCHS.".Status = 'pending')";
			$result_pending = $sql->db_Query($q_pending);
			$row = mysql_fetch_array($result_pending);
			$nbrMatchesPending = $row['nbrMatchesPending'];

			$text .= '<tr>';
			$text .= '<td class="forumheader3">';
			$text .= '<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.$lName.'</a><br />';
			$text .= '<img '.getGameIconResize($gIcon).'/> '.$gName;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			if($lOwner == $req_user)
			{
				$text .= EB_USER_L15;
				if ($lOwner == USERID)
				{
					$text .= ' (<a href="'.e_PLUGIN.'ebattles/laddermanage.php?LadderID='.$ladder_id.'">'.EB_USER_L16.'</a>)';
				}
			}
			else
			{
				$text .= EB_USER_L17;
			}
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			$text .= ($nbrMatchesPending>0) ? '<div><img src="'.e_PLUGIN.'ebattles/images/exclamation.png" alt="'.EB_MATCH_L13.'" title="'.EB_MATCH_L13.'" style="vertical-align:text-top;"/>&nbsp;<b>'.$nbrMatchesPending.'&nbsp;'.EB_LADDER_L64.'</b></div>' : '';
			$text .= '</td>';
			$text .= '</tr>';
		}
		$text .= '</table>';
	}
	$text .= '</div>';   // tab-page"Ladders"

	/*
	---------------------
	Divisions
	---------------------
	*/
	$text .= '<div class="tab-page">';   // tab-page "Divisions"
	$text .= '<div class="tab">'.EB_USER_L4.'</div>
	';
	if((strcmp(USERID,$req_user) == 0)&&(check_class($pref['eb_teams_create_class'])))
	{
		$text .= '<form action="'.e_PLUGIN.'ebattles/clancreate.php" method="post">';
		$text .= '<div>';
		$text .= '<input type="hidden" name="userid" value="'.$req_user.'"/>';
		$text .= '<input type="hidden" name="username" value="'.$uname.'"/>';
		$text .= '</div>';
		$text .= ebImageTextButton('createteam', 'add.png', EB_CLANS_L7);
		$text .= '</form><br />';
	}

	/* Display list of divisions where the user is a member */
	$text .= '<div class="spacer"><b>'.EB_USER_L22.'</b></div>';
	$text .= '<div>'.$uname.'&nbsp;'.EB_USER_L23.'</div>';
	$q = "SELECT ".TBL_CLANS.".*, "
	.TBL_DIVISIONS.".*, "
	.TBL_MEMBERS.".*, "
	.TBL_USERS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_CLANS.", "
	.TBL_DIVISIONS.", "
	.TBL_USERS.", "
	.TBL_MEMBERS.", "
	.TBL_GAMES
	." WHERE (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
	." AND (".TBL_MEMBERS.".Division = ".TBL_DIVISIONS.".DivisionID)"
	." AND (".TBL_MEMBERS.".User = ".TBL_USERS.".user_id)"
	." AND (".TBL_USERS.".user_id = '$req_user')"
	." AND (".TBL_GAMES.".GameID = ".TBL_DIVISIONS.".Game)";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	if ($num_rows>0)
	{
		$text .= '<table class="fborder" style="width:95%">';
		$text .= '<tr>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L24;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L25;
		$text .= '</td>';
		$text .= '</tr>';
		/* Display table contents */
		for($i=0; $i<$num_rows; $i++)
		{
			$cname  = mysql_result($result,$i, TBL_CLANS.".Name");
			$dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
			$dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
			$cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
			$cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
			$text .= '<tr>';
			$text .= '<td class="forumheader3">';
			$text .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$cid.'">'.$cname.'</a><br />';
			$text .= '<img '.getGameIconResize($dgameicon).'/> '.$dgame;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			if($cowner == $req_user)
			{
				$text .= EB_USER_L15;
				if ($cowner == USERID)
				{
					$text .= ' (<a href="'.e_PLUGIN.'ebattles/clanmanage.php?clanid='.$cid.'">'.EB_USER_L16.'</a>)';
				}
			}
			else
			{
				$text .= EB_USER_L17;
			}
			$text .= '</td>';
			$text .= '</tr>';

		}
		$text .= '</table>';
	}

	/* Display list of teams where the user is te owner */
	$text .= '<br /><div class="spacer"><b>'.EB_USER_L26.'</b></div>';
	$text .= '<div>'.$uname.'&nbsp;'.EB_USER_L27.'</div>';
	$q = "SELECT ".TBL_CLANS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_CLANS.", "
	.TBL_USERS
	." WHERE (".TBL_CLANS.".Owner = ".TBL_USERS.".user_id)"
	." AND (".TBL_USERS.".user_id = '$req_user')";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	if ($num_rows>0)
	{
		$text .= '<table class="fborder" style="width:95%">';
		$text .= '<tr>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L28;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L14;
		$text .= '</td>';
		$text .= '</tr>';
		/* Display table contents */
		for($i=0; $i<$num_rows; $i++)
		{
			$cname  = mysql_result($result,$i, TBL_CLANS.".Name");
			$cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
			$cowner  = mysql_result($result,$i, TBL_CLANS.".Owner");
			$text .= '<tr>';
			$text .= '<td class="forumheader3">';
			$text .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$cid.'">'.$cname.'</a><br />';
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			if($cowner == $req_user)
			{
				$text .= EB_USER_L15;
				if ($cowner == USERID)
				{
					$text .= ' (<a href="'.e_PLUGIN.'ebattles/clanmanage.php?clanid='.$cid.'">'.EB_USER_L16.'</a>)';
				}
			}
			else
			{
				$text .= EB_USER_L17;
			}
			$text .= '</td>';
			$text .= '</tr>';

		}
		$text .= '</table>';
	}

	/* Display list of divisions where the user is the captain */
	$text .= '<br /><div class="spacer"><b>'.EB_USER_L29.'</b></div>';
	$text .= '<div>'.$uname.'&nbsp;'.EB_USER_L30.'</div>';
	$q = "SELECT ".TBL_CLANS.".*, "
	.TBL_DIVISIONS.".*, "
	.TBL_GAMES.".*"
	." FROM ".TBL_CLANS.", "
	.TBL_DIVISIONS.", "
	.TBL_GAMES
	." WHERE (".TBL_DIVISIONS.".Clan = ".TBL_CLANS.".ClanID)"
	." AND (".TBL_GAMES.".GameId = ".TBL_DIVISIONS.".Game)"
	." AND (".TBL_DIVISIONS.".Captain = '$req_user')";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	if ($num_rows>0)
	{
		$text .= '<table class="fborder" style="width:95%">';
		$text .= '<tr>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L24;
		$text .= '</td>';
		$text .= '<td class="forumheader3">';
		$text .= EB_USER_L14;
		$text .= '</td>';
		$text .= '</tr>';
		/* Display table contents */
		for($i=0; $i<$num_rows; $i++)
		{
			$cname  = mysql_result($result,$i, TBL_CLANS.".Name");
			$cid  = mysql_result($result,$i, TBL_CLANS.".ClanID");
			$dcaptain  = mysql_result($result,$i, TBL_DIVISIONS.".Captain");
			$dgame  = mysql_result($result,$i, TBL_GAMES.".Name");
			$dgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
			$text .= '<tr>';
			$text .= '<td class="forumheader3">';
			$text .= '<a href="'.e_PLUGIN.'ebattles/claninfo.php?clanid='.$cid.'">'.$cname.'</a><br />';
			$text .= '<img '.getGameIconResize($dgameicon).'/> '.$dgame;
			$text .= '</td>';
			$text .= '<td class="forumheader3">';
			if($cowner == $req_user)
			{
				$text .= EB_USER_L15;
				if ($cowner == USERID)
				{
					$text .= ' (<a href="'.e_PLUGIN.'ebattles/clanmanage.php?clanid='.$cid.'">'.EB_USER_L16.'</a>)';
				}
			}
			else
			{
				$text .= EB_USER_L17;
			}
			$text .= '</td>';
			$text .= '</tr>';

		}
		$text .= '</table>';
	}
	$text .= '</div>';   // tab-page "Divisions"

	/*
	---------------------
	Matches
	---------------------
	*/
	$text .= '<div class="tab-page">';   // tab-page "Matches"
	$text .= '<div class="tab">'.EB_USER_L5.'</div>';

	/* Display Active Matches */
	/* set pagination variables */
	$q = "SELECT count(*) "
	." FROM ".TBL_MATCHS.", "
	.TBL_SCORES.", "
	.TBL_PLAYERS.", "
	.TBL_GAMERS
	." WHERE (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
	." AND (".TBL_MATCHS.".Status = 'active')"
	." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
	." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
	." AND   (".TBL_PLAYERS.".Team != 0)))"
	." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	." AND (".TBL_GAMERS.".User = '$req_user')";
	$result = $sql->db_Query($q);
	$totalItems = mysql_result($result, 0);
	$pages->items_total = $totalItems;
	$pages->mid_range = eb_PAGINATION_MIDRANGE;
	$pages->paginate();

	$text .= '<p><b>';
	$text .= $totalItems.'&nbsp;'.EB_LADDER_L59;
	$text .= '</b></p>';

	$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
	." FROM ".TBL_MATCHS.", "
	.TBL_SCORES.", "
	.TBL_PLAYERS.", "
	.TBL_GAMERS
	." WHERE (".TBL_MATCHS.".Status = 'active')"
	." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
	." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
	." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
	." AND   (".TBL_PLAYERS.".Team != 0)))"
	." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	." AND (".TBL_GAMERS.".User = '$req_user')"
	." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
	." $pages->limit";
	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	if ($num_rows>0)
	{
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

		/* Display table contents */
		$text .= '<table class="table_left">';
		for($i=0; $i<$num_rows; $i++)
		{
			$match_id  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
			$match = new Match($match_id);
			$text .= $match->displayMatchInfo();
		}
		$text .= '</table>';
	}
	/* Display Pending Matches */
	$text .= '<br />';

	$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
	." FROM ".TBL_MATCHS.", "
	.TBL_SCORES.", "
	.TBL_PLAYERS.", "
	.TBL_GAMERS
	." WHERE (".TBL_MATCHS.".Status = 'pending')"
	." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
	." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
	." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
	." AND   (".TBL_PLAYERS.".Team != 0)))"
	." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	." AND (".TBL_GAMERS.".User = '$req_user')"
	." ORDER BY ".TBL_MATCHS.".TimeReported DESC";
	$result = $sql->db_Query($q);
	$numMatches = mysql_numrows($result);

	$text .= '<p><b>';
	$text .= $numMatches.'&nbsp;'.EB_LADDER_L64;
	$text .= '</b></p>';
	$text .= '<br />';

	if ($numMatches>0)
	{
		/* Display table contents */
		$text .= '<table class="table_left">';
		for($i=0; $i < $numMatches; $i++)
		{
			$match_id  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
			$match = new Match($match_id);
			$text .= $match->displayMatchInfo();
		}
		$text .= '</table>';
	}

	/* Display Scheduled Matches */
	$text .= '<br />';

	$q = "SELECT DISTINCT ".TBL_MATCHS.".*"
	." FROM ".TBL_MATCHS.", "
	.TBL_SCORES.", "
	.TBL_PLAYERS.", "
	.TBL_GAMERS
	." WHERE (".TBL_MATCHS.".Status = 'scheduled')"
	." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
	." AND ((".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
	." OR   ((".TBL_PLAYERS.".Team = ".TBL_SCORES.".Team)"
	." AND   (".TBL_PLAYERS.".Team != 0)))"
	." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	." AND (".TBL_GAMERS.".User = '$req_user')"
	." ORDER BY ".TBL_MATCHS.".TimeReported DESC";
	$result = $sql->db_Query($q);
	$numMatches = mysql_numrows($result);

	$text .= '<p><b>';
	$text .= $numMatches.'&nbsp;'.EB_LADDER_L70;
	$text .= '</b></p>';
	$text .= '<br />';

	if ($numMatches>0)
	{
		/* Display table contents */
		$text .= '<table class="table_left">';
		for($i=0; $i < $numMatches; $i++)
		{
			$match_id  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
			$match = new Match($match_id);
			$text .= $match->displayMatchInfo(eb_MATCH_SCHEDULED);
		}
		$text .= '</table>';
	}
	/* Display Requested Challenges */
	$text .= '<br />';

	$q = "SELECT DISTINCT ".TBL_CHALLENGES.".*"
	." FROM ".TBL_CHALLENGES
	." WHERE (".TBL_CHALLENGES.".Status = 'requested')"
	." AND (".TBL_CHALLENGES.".ReportedBy = '$req_user')"
	." ORDER BY ".TBL_CHALLENGES.".TimeReported DESC";
	$result = $sql->db_Query($q);
	$numChallenges = mysql_numrows($result);

	$text .= '<p><b>';
	$text .= $numChallenges.'&nbsp;'.EB_LADDER_L66;
	$text .= '</b></p>';
	$text .= '<br />';

	if ($numChallenges>0)
	{
		/* Display table contents */
		$text .= '<table class="table_left">';
		for($i=0; $i < $numChallenges; $i++)
		{
			$challenge_id  = mysql_result($result,$i, TBL_CHALLENGES.".ChallengeID");
			$challenge = new Challenge($challenge_id);
			$text .= $challenge->displayChallengeInfo();
		}
		$text .= '</table>';
	}

	/* Display Unconfirmed Challenges */
	$text .= '<br />';

	$q = "SELECT DISTINCT ".TBL_CHALLENGES.".*"
	." FROM ".TBL_CHALLENGES.", "
	.TBL_PLAYERS.", "
	.TBL_GAMERS
	." WHERE (".TBL_CHALLENGES.".Status = 'requested')"
	."   AND ((".TBL_PLAYERS.".PlayerID = ".TBL_CHALLENGES.".ChallengedPlayer)"
	."    OR  ((".TBL_PLAYERS.".Team = ".TBL_CHALLENGES.".ChallengedTeam)"
	."   AND   (".TBL_PLAYERS.".Team != 0)))"
	."   AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	."   AND (".TBL_GAMERS.".User = '$req_user')"
	." ORDER BY ".TBL_CHALLENGES.".TimeReported DESC";
	$result = $sql->db_Query($q);
	$numChallenges = mysql_numrows($result);

	$text .= '<p><b>';
	$text .= $numChallenges.'&nbsp;'.EB_LADDER_L67;
	$text .= '</b></p>';
	$text .= '<br />';

	if ($numChallenges>0)
	{
		/* Display table contents */
		$text .= '<table class="table_left">';
		for($i=0; $i < $numChallenges; $i++)
		{
			$challenge_id  = mysql_result($result,$i, TBL_CHALLENGES.".ChallengeID");
			$challenge = new Challenge($challenge_id);
			$text .= $challenge->displayChallengeInfo();
		}
		$text .= '</table>';
	}

	$text .= '</div>';   // tab-page "Matches"

	/*
	---------------------
	Awards
	---------------------
	*/
	$text .= '<div class="tab-page">';   // tab-page "Awards"
	$text .= '<div class="tab">'.EB_USER_L6.'</div>';

	/* Stats/Results */
	$q = "SELECT ".TBL_AWARDS.".*, "
	.TBL_LADDERS.".*, "
	.TBL_PLAYERS.".*, "
	.TBL_GAMES.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_AWARDS.", "
	.TBL_PLAYERS.", "
	.TBL_GAMERS.", "
	.TBL_LADDERS.", "
	.TBL_GAMES.", "
	.TBL_USERS
	." WHERE (".TBL_USERS.".user_id = $req_user)"
	." AND (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
	." AND (".TBL_PLAYERS.".Gamer = ".TBL_GAMERS.".GamerID)"
	." AND (".TBL_GAMERS.".User = ".TBL_USERS.".user_id)"
	." AND (".TBL_PLAYERS.".Ladder = ".TBL_LADDERS.".LadderID)"
	." AND (".TBL_LADDERS.".Game = ".TBL_GAMES.".GameID)"
	." ORDER BY ".TBL_AWARDS.".timestamp DESC";

	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);

	$text .= '<br />';
	if ($num_rows>0)
	{
		$text .= '<table class="table_left">';
		/* Display table contents */
		for($i=0; $i<$num_rows; $i++)
		{
			$aID  = mysql_result($result,$i, TBL_AWARDS.".AwardID");
			$aUser  = mysql_result($result,$i, TBL_USERS.".user_id");
			$aUserNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
			$aLadderID  = mysql_result($result,$i, TBL_LADDERS.".LadderID");
			$aLadderName  = mysql_result($result,$i, TBL_LADDERS.".Name");
			$aLaddergame = mysql_result($result,$i , TBL_GAMES.".Name");
			$aLaddergameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
			$aType  = mysql_result($result,$i, TBL_AWARDS.".Type");
			$aTime  = mysql_result($result,$i, TBL_AWARDS.".timestamp");
			$aTime_local = $aTime + TIMEOFFSET;
			$date = date("d M Y, h:i A",$aTime_local);

			switch ($aType) {
				case 'PlayerTookFirstPlace':
				$award = EB_AWARD_L2;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_gold_3.png").' alt="'.EB_AWARD_L3.'" title="'.EB_AWARD_L3.'"/> ';
				break;
				case 'PlayerInTopTen':
				$award = EB_AWARD_L4;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_bronze_3.png").' alt="'.EB_AWARD_L5.'" title="'.EB_AWARD_L5.'"/> ';
				break;
				case 'PlayerStreak5':
				$award = EB_AWARD_L6;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_bronze_3.png").' alt="'.EB_AWARD_L7.'" title="'.EB_AWARD_L7.'"/> ';
				break;
				case 'PlayerStreak10':
				$award = EB_AWARD_L8;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_silver_3.png").' alt="'.EB_AWARD_L9.'" title="'.EB_AWARD_L9.'"/> ';
				break;
				case 'PlayerStreak25':
				$award = EB_AWARD_L10;
				$icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_gold_3.png").' alt="'.EB_AWARD_L11.'" title="'.EB_AWARD_L11.'"/> ';
				break;
			}

			$award_string = '<tr><td style="vertical-align:top">'.$icon.'</td>';
			$award_string .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$aUser.'">'.$aUserNickName.'</a>';
			$award_string .= ' '.$award;
			$award_string .= ' '.EB_MATCH_L12.' <a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$aLadderID.'">'.$aLadderName.'</a> ('.$aLaddergame.')';

			$award_string .= ' <div class="smalltext">';
			if (($time-$aTime) < INT_MINUTE )
			{
				$award_string .= EB_MATCH_L7;
			}
			else if (($time-$aTime) < INT_DAY )
			{
				$award_string .= get_formatted_timediff($aTime, $time).'&nbsp;'.EB_MATCH_L8;
			}
			else
			{
				$award_string .= $date;
			}
			$award_string .= '</div></td></tr>';

			$text .= $award_string;
		}
		$text .= '</table><br />';
	}
	$text .= '<br />';
	$text .= '</div>';   // tab-page "Awards"

	$text .= '
	</div>

	<script type="text/javascript">
	//<![CDATA[
	setupAllTabs();
	//]]>
	</script>
	';
}
$ns->tablerender(EB_USER_L1, $text);
require_once(FOOTERF);
exit;
?>

