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
// Specify if we use WYSIWYG for text areas
global $e_wysiwyg;
$e_wysiwyg = "challenge_comments";  // set $e_wysiwyg before including HEADERF
require_once(HEADERF);

/*
/*
//dbg form
echo "<br>_POST: ";
print_r($_POST);    // show $_POST
echo "<br>_GET: ";
print_r($_GET);     // show $_GET
*/

if (e_WYSIWYG)
{
	$insertjs = "rows='15'";
}
else
{
	require_once(e_HANDLER."ren_help.php");
	$insertjs = "rows='5' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
}

$text = '
<!-- main calendar program -->
<script type="text/javascript" src="./js/calendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="./js/calendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="./js/calendar/calendar-setup.js"></script>
<script type="text/javascript">
<!--//
function clearDate(frm, index)
{
document.getElementById("f_date"+index).value = ""
}
//-->
</script>
';
/* Ladder Name */
$ladder_id = $_GET['LadderID'];
$q = "SELECT ".TBL_LADDERS.".*"
." FROM ".TBL_LADDERS
." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')";
$result = $sql->db_Query($q);

$eMatchesApproval = mysql_result($result,0 , TBL_LADDERS.".MatchesApproval");
$etype = mysql_result($result,0 , TBL_LADDERS.".Type");
$eNumDates = mysql_result($result,0 , TBL_LADDERS.".MaxDatesPerChallenge");
$ename = mysql_result($result,0 , TBL_LADDERS.".Name");

if(isset($_POST['challenge_player']))
{
	$challenger = $_POST['submitted_by'];
	$challenged = $_POST['Challenged'];

	$text .= PlayerChallengeForm($ladder_id, $challenger, $challenged);
}
if(isset($_POST['challenge_player_submit']))
{
	$challenger = $_POST['submitted_by'];
	$challenged = $_POST['Challenged'];

	// Verify form
	$error_str = '';

	for($date=1; $date <= $eNumDates; $date++)
	{
		if ($_POST['date'.$date] == '')
		{
			$error_str .= '<li>'.EB_CHALLENGE_L10.'&nbsp;'.$date.'&nbsp;'.EB_CHALLENGE_L11.'</li>';
		}
	}

	if (!empty($error_str)) {
		// show form again
		$text .= PlayerChallengeForm($ladder_id, $challenger, $challenged);

		// errors have occured, halt execution and show form again.
		$text .= '<p style="color:red">'.EB_MATCHR_L14;
		$text .= '<ul style="color:red">'.$error_str.'</ul></p>';
	}
	else
	{
		SubmitPlayerChallenge($ladder_id, $challenger, $challenged, $eNumDates);
		$text .= EB_CHALLENGE_L12;
		$text .= '<br />';
		$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
	}
}
if(isset($_POST['challenge_team']))
{
	$challenger = $_POST['submitted_by'];
	$challenged = $_POST['Challenged'];

	$text .= TeamChallengeForm($ladder_id, $challenger, $challenged);
}
if(isset($_POST['challenge_team_submit']))
{
	$challenger = $_POST['submitted_by'];
	$challenged = $_POST['Challenged'];

	// Verify form
	$error_str = '';

	for($date=1; $date <= $eNumDates; $date++)
	{
		if ($_POST['date'.$date] == '')
		{
			$error_str .= '<li>'.EB_CHALLENGE_L10.'&nbsp;'.$date.'&nbsp;'.EB_CHALLENGE_L11.'</li>';
		}
	}

	if (!empty($error_str)) {
		// show form again
		$text .= TeamChallengeForm($ladder_id, $challenger, $challenged);

		// errors have occured, halt execution and show form again.
		$text .= '<p style="color:red">'.EB_MATCHR_L14;
		$text .= '<ul style="color:red">'.$error_str.'</ul></p>';
	}
	else
	{
		SubmitTeamChallenge($ladder_id, $challenger, $challenged, $eNumDates);
		$text .= EB_CHALLENGE_L12;
		$text .= '<br />';
		$text .= '<br />'.EB_CHALLENGE_L13.' [<a href="'.e_PLUGIN.'ebattles/ladderinfo.php?LadderID='.$ladder_id.'">'.EB_CHALLENGE_L14.'</a>]<br />';
	}
}
$ns->tablerender("$ename (".ladderType($etype).") - ".EB_CHALLENGE_L1, $text);
require_once(FOOTERF);
exit;

//=================================================================================
// Functions
//=================================================================================
function PlayerChallengeForm($ladder_id, $challengerpuid, $challengedpid)
{
	global $sql;
	global $tp;
	global $time;

	$q = "SELECT ".TBL_LADDERS.".*"
	." FROM ".TBL_LADDERS
	." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')";
	$result = $sql->db_Query($q);

	$eMatchesApproval = mysql_result($result,0 , TBL_LADDERS.".MatchesApproval");
	$etype = mysql_result($result,0 , TBL_LADDERS.".Type");
	$eNumDates = mysql_result($result,0 , TBL_LADDERS.".MaxDatesPerChallenge");

	$output .= '<form action="'.e_PLUGIN.'ebattles/challengerequest.php?LadderID='.$ladder_id.'" method="post">';

	$output .= '<b>'.EB_CHALLENGE_L2.'</b><br />';
	$output .= '<br />';
	// Challenger Info
	// Attention here, we use user_id, so there has to be 1 user for 1 player
	$output .= '<b>'.EB_CHALLENGE_L5.'</b>'; // Challenger
	$q = "SELECT ".TBL_PLAYERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_USERS
	." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
	."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
	."   AND (".TBL_USERS.".user_id = '$challengerpuid')";
	$result = $sql->db_Query($q);

	$pid    = mysql_result($result,0 , TBL_PLAYERS.".PlayerID");
	$puid   = mysql_result($result,0 , TBL_USERS.".user_id");
	$prank  = mysql_result($result,0 , TBL_PLAYERS.".Rank");
	$pname  = mysql_result($result,0 , TBL_USERS.".user_name");
	$pteam  = mysql_result($result,0 , TBL_PLAYERS.".Team");
	list($pclan, $pclantag, $pclanid) = getClanName($pteam);

	if ($prank==0)
	$prank_txt = EB_LADDER_L54;
	else
	$prank_txt = "#$prank";
	$str = $pclantag.$pname.' ('.$prank_txt.')';
	$output .= ' '.$str.'<br />';

	// Challenged Info
	$output .= '<b>'.EB_CHALLENGE_L6.'</b>'; // Challenged
	$q = "SELECT ".TBL_PLAYERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_USERS
	." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
	."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
	."   AND (".TBL_PLAYERS.".PlayerID = '$challengedpid')";
	$result = $sql->db_Query($q);

	$pid    = mysql_result($result, 0, TBL_PLAYERS.".PlayerID");
	$puid   = mysql_result($result, 0, TBL_USERS.".user_id");
	$prank  = mysql_result($result, 0, TBL_PLAYERS.".Rank");
	$pname  = mysql_result($result, 0, TBL_USERS.".user_name");
	$pteam  = mysql_result($result, 0, TBL_PLAYERS.".Team");
	list($pclan, $pclantag, $pclanid) = getClanName($pteam);

	if ($prank==0)
	$prank_txt = EB_LADDER_L54;
	else
	$prank_txt = "#$prank";
	$str = $pclantag.$pname.' ('.$prank_txt.')';
	$output .= ' '.$str.'<br />';

	$output .= '<br />';

	// Select Dates
	$output .= '<b>'.EB_CHALLENGE_L7.'</b><br />'; // Select Dates
	$output .= '<table class="table_left">';
	for($date=1; $date <= $eNumDates; $date++)
	{
		//<!-- Select date Date -->
		$output .= '
		<tr>
		<td><b>'.EB_CHALLENGE_L10.' #'.$date.'</b></td>
		<td>
		<table>
		<tr>
		<td>
		<div><input class="tbox" type="text" name="date'.$date.'" id="f_date'.$date.'" value="'.$_POST['date'.$date].'" readonly="readonly" /></div>
		</td>
		<td>
		<img src="./js/calendar/img.gif" alt="date selector" id="f_trigger'.$date.'" style="cursor: pointer; border: 1px solid red;" title="'.EB_LADDERM_L33.'"
		';
		$output .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
		$output .= '
		</td>
		<td>
		<div><input class="button" type="button" value="'.EB_LADDERM_L34.'" onclick="clearDate(this.form, '.$date.');"/></div>
		</td>
		</tr>
		</table>
		';
		$output .= '
		<script type="text/javascript">
		Calendar.setup({
		inputField     :    "f_date'.$date.'",      // id of the input field
		ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
		showsTime      :    true,            // will display a time selector
		button         :    "f_trigger'.$date.'",   // trigger for the calendar (button ID)
		singleClick    :    true,           // single-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
		});
		</script>
		</td>
		</tr>
		';
	}
	$output .= '</table>';

	// comments
	//----------------------------------
	if(isset($_POST['challenge_comments']))
	{
		$comments = $tp->toDB($_POST['challenge_comments']);
	} else {
		$comments = '';
	}
	$output .= '<br />';
	$output .= '<div>';
	$output .= EB_CHALLENGE_L8.'<br />';
	$output .= '<textarea class="tbox" id="challenge_comments" name="challenge_comments" style="width:500px" cols="70" '.$insertjs.'>'.$comments.'</textarea>';
	if (!e_WYSIWYG)
	{
		$output .= '<br />'.display_help("helpb","comments");
	}
	$output .= '</div>';

	$output .= '<br />';

	$output .= '<div>';
	$output .= '<input type="hidden" name="submitted_by" value="'.$challengerpuid.'"/>';
	$output .= '<input type="hidden" name="Challenged" value="'.$challengedpid.'"/>';

	$output .= '
	</div>
	<div>
	'.ebImageTextButton('challenge_player_submit', 'challenge.png', EB_CHALLENGE_L9).'
	</div>
	</form>
	';

	return $output;
}

function SubmitPlayerChallenge($ladder_id, $challengerpuid, $challengedpid)
{
	global $sql;
	global $text;
	global $tp;
	global $time;
	global $pref;

	$q = "SELECT ".TBL_LADDERS.".*"
	." FROM ".TBL_LADDERS
	." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')";
	$result = $sql->db_Query($q);

	$eMatchesApproval = mysql_result($result,0 , TBL_LADDERS.".MatchesApproval");
	$etype = mysql_result($result,0 , TBL_LADDERS.".Type");
	$ename = mysql_result($result,0 , TBL_LADDERS.".Name");
	$eNumDates = mysql_result($result,0 , TBL_LADDERS.".MaxDatesPerChallenge");

	// Challenger Info
	// Attention here, we use user_id, so there has to be 1 user for 1 player
	$q = "SELECT ".TBL_PLAYERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_USERS
	." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
	."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
	."   AND (".TBL_USERS.".user_id = '$challengerpuid')";
	$result = $sql->db_Query($q);
	$challengerpid   = mysql_result($result, 0,TBL_PLAYERS.".PlayerID");
	// $challengerpuid   = mysql_result($result, 0, TBL_USERS.".user_id");
	$challengerpname  = mysql_result($result, 0, TBL_USERS.".user_name");
	$challengerpemail  = mysql_result($result, 0, TBL_USERS.".user_email");


	// Challenged Info
	$q = "SELECT ".TBL_PLAYERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_USERS
	." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
	."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
	."   AND (".TBL_PLAYERS.".PlayerID = '$challengedpid')";
	$result = $sql->db_Query($q);

	// $challengedpid    = mysql_result($result, 0, TBL_PLAYERS.".PlayerID");
	$challengedpuid   = mysql_result($result, 0, TBL_USERS.".user_id");
	$challengedpname  = mysql_result($result, 0, TBL_USERS.".user_name");
	$challengedpemail  = mysql_result($result, 0, TBL_USERS.".user_email");

	$challenge_times = '';
	for($date=1; $date <= $eNumDates; $date++)
	{
		$challenge_date = $_POST['date'.$date];
		$challenge_time_local = strtotime($challenge_date);
		$challenge_time_local = $challenge_time_local - TIMEOFFSET;	// Convert to GMT time
		if ($date > 1) $challenge_times .= ',';
		$challenge_times .= $challenge_time_local;
	}

	// comments
	//----------------------------------
	$comments = $tp->toDB($_POST['challenge_comments']);
	$time_reported = $time;

	// Create Challenge ------------------------------------------
	$q =
	"INSERT INTO ".TBL_CHALLENGES."(Ladder,ChallengerPlayer,ChallengedPlayer,ReportedBy,TimeReported,Comments,Status,MatchDates)
	VALUES (
	'$ladder_id',
	'$challengerpid',
	'$challengedpid',
	'$challengerpuid',
	'$time_reported',
	'$comments',
	'requested',
	'$challenge_times'
	)";
	$result = $sql->db_Query($q);

	$subject = SITENAME." ".EB_CHALLENGE_L23;
	$message = EB_CHALLENGE_L24.$challengedpname.EB_CHALLENGE_L25.$challengerpname.EB_CHALLENGE_L26.$ename.EB_CHALLENGE_L27;
	if (check_class($pref['eb_pm_notifications_class']))
	{
		// Send PM
		$sendto = $challengedpuid;
		$fromid = 0;
		sendNotification($sendto, $subject, $message, $fromid);
	}

	if (check_class($pref['eb_email_notifications_class']))
	{
		// Send email
		require_once(e_HANDLER."mail.php");
		sendemail($challengedpemail, $subject, $message);
	}
}

function TeamChallengeForm($ladder_id, $challengerpuid, $challengedtid)
{
	global $sql;
	global $tp;
	global $time;

	$q = "SELECT ".TBL_LADDERS.".*"
	." FROM ".TBL_LADDERS
	." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')";
	$result = $sql->db_Query($q);

	$eMatchesApproval = mysql_result($result,0 , TBL_LADDERS.".MatchesApproval");
	$etype = mysql_result($result,0 , TBL_LADDERS.".Type");
	$eNumDates = mysql_result($result,0 , TBL_LADDERS.".MaxDatesPerChallenge");

	$output .= '<form action="'.e_PLUGIN.'ebattles/challengerequest.php?LadderID='.$ladder_id.'" method="post">';

	$output .= '<b>'.EB_CHALLENGE_L3.'</b><br />';
	$output .= '<br />';
	// Challenger Info
	// Attention here, we use user_id, so there has to be 1 user for 1 player
	$output .= '<b>'.EB_CHALLENGE_L5.'</b>'; // Challenger
	$q = "SELECT ".TBL_PLAYERS.".*, "
	.TBL_USERS.".*, "
	.TBL_TEAMS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_USERS.", "
	.TBL_TEAMS
	." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
	."   AND (".TBL_TEAMS.".TeamID = ".TBL_PLAYERS.".Team)"
	."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
	."   AND (".TBL_USERS.".user_id = '$challengerpuid')";
	$result = $sql->db_Query($q);

	$uteam  = mysql_result($result,0 , TBL_PLAYERS.".Team");
	$trank  = mysql_result($result,0 , TBL_TEAMS.".Rank");
	list($tclan, $tclantag, $tclanid) = getClanName($uteam);

	if ($trank==0)
	$trank_txt = EB_LADDER_L54;
	else
	$trank_txt = "#$trank";
	$str = $tclan.' ('.$trank_txt.')';
	$output .= ' '.$str.'<br />';

	// Challenged Info
	$output .= '<b>'.EB_CHALLENGE_L6.'</b>'; // Challenged

	$q = "SELECT ".TBL_TEAMS.".*"
	."   AND (".TBL_TEAMS.".TeamID = '$challengedtid')";
	$result = $sql->db_Query($q);

	$uteam  = mysql_result($result,0 , TBL_PLAYERS.".Team");
	$trank  = mysql_result($result, 0, TBL_TEAMS.".Rank");
	list($tclan, $tclantag, $tclanid) = getClanName($uteam);

	if ($trank==0)
	$trank_txt = EB_LADDER_L54;
	else
	$trank_txt = "#$trank";
	$str = $tclan.' ('.$trank_txt.')';
	$output .= ' '.$str.'<br />';

	$output .= '<br />';

	// Select Dates
	$output .= '<b>'.EB_CHALLENGE_L7.'</b><br />'; // Select Dates
	$output .= '<table class="table_left">';
	for($date=1; $date <= $eNumDates; $date++)
	{
		//<!-- Select date Date -->
		$output .= '
		<tr>
		<td><b>'.EB_CHALLENGE_L10.' #'.$date.'</b></td>
		<td>
		<table>
		<tr>
		<td>
		<div><input class="tbox" type="text" name="date'.$date.'" id="f_date'.$date.'" value="'.$_POST['date'.$date].'" readonly="readonly" /></div>
		</td>
		<td>
		<img src="./js/calendar/img.gif" alt="date selector" id="f_trigger'.$date.'" style="cursor: pointer; border: 1px solid red;" title="'.EB_LADDERM_L33.'"
		';
		$output .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
		$output .= '
		</td>
		<td>
		<div><input class="button" type="button" value="'.EB_LADDERM_L34.'" onclick="clearDate(this.form, '.$date.');"/></div>
		</td>
		</tr>
		</table>
		';
		$output .= '
		<script type="text/javascript">
		Calendar.setup({
		inputField     :    "f_date'.$date.'",      // id of the input field
		ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
		showsTime      :    true,            // will display a time selector
		button         :    "f_trigger'.$date.'",   // trigger for the calendar (button ID)
		singleClick    :    true,           // single-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
		});
		</script>
		</td>
		</tr>
		';
	}
	$output .= '</table>';

	// comments
	//----------------------------------
	if(isset($_POST['challenge_comments']))
	{
		$comments = $tp->toDB($_POST['challenge_comments']);
	} else {
		$comments = '';
	}
	$output .= '<br />';
	$output .= '<div>';
	$output .= EB_CHALLENGE_L8.'<br />';
	$output .= '<textarea class="tbox" id="challenge_comments" name="challenge_comments" style="width:500px" cols="70" '.$insertjs.'>'.$comments.'</textarea>';
	if (!e_WYSIWYG)
	{
		$output .= '<br />'.display_help("helpb","comments");
	}
	$output .= '</div>';

	$output .= '<br />';

	$output .= '<div>';
	$output .= '<input type="hidden" name="submitted_by" value="'.$challengerpuid.'"/>';
	$output .= '<input type="hidden" name="Challenged" value="'.$challengedtid.'"/>';

	$output .= '
	</div>
	<div>
	'.ebImageTextButton('challenge_team_submit', 'challenge.png', EB_CHALLENGE_L9).'
	</div>
	</form>
	';

	return $output;
}

function SubmitTeamChallenge($ladder_id, $challengerpuid, $challengedtid)
{
	global $sql;
	global $text;
	global $tp;
	global $time;
	global $pref;

	$q = "SELECT ".TBL_LADDERS.".*"
	." FROM ".TBL_LADDERS
	." WHERE (".TBL_LADDERS.".LadderID = '$ladder_id')";
	$result = $sql->db_Query($q);

	$eMatchesApproval = mysql_result($result,0 , TBL_LADDERS.".MatchesApproval");
	$etype = mysql_result($result,0 , TBL_LADDERS.".Type");
	$ename = mysql_result($result,0 , TBL_LADDERS.".Name");
	$eNumDates = mysql_result($result,0 , TBL_LADDERS.".MaxDatesPerChallenge");

	// Challenger Info
	// Attention here, we use user_id, so there has to be 1 user for 1 player
	$q = "SELECT ".TBL_PLAYERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_PLAYERS.", "
	.TBL_USERS
	." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')"
	."   AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
	."   AND (".TBL_USERS.".user_id = '$challengerpuid')";
	$result = $sql->db_Query($q);
	$challengerpid   = mysql_result($result, 0,TBL_PLAYERS.".PlayerID");
	$challengertid   =mysql_result($result, 0,TBL_PLAYERS.".Team");
	list($challengertclan, $challengertclantag, $challengertclanid) = getClanName($challengertid);

	// Challenged Info
	// Nothing needed here
	// ...

	$challenge_times = '';
	for($date=1; $date <= $eNumDates; $date++)
	{
		$challenge_date = $_POST['date'.$date];
		$challenge_time_local = strtotime($challenge_date);
		$challenge_time_local = $challenge_time_local - TIMEOFFSET;	// Convert to GMT time
		if ($date > 1) $challenge_times .= ',';
		$challenge_times .= $challenge_time_local;
	}

	// comments
	//----------------------------------
	$comments = $tp->toDB($_POST['challenge_comments']);
	$time_reported = $time;

	// Create Challenge ------------------------------------------
	$q =
	"INSERT INTO ".TBL_CHALLENGES."(Ladder,ChallengerTeam,ChallengedTeam,ReportedBy,TimeReported,Comments,Status,MatchDates)
	VALUES (
	'$ladder_id',
	'$challengertid',
	'$challengedtid',
	'$challengerpuid',
	'$time_reported',
	'$comments',
	'requested',
	'$challenge_times'
	)";
	$result = $sql->db_Query($q);

	// Send PM
	$fromid = 0;
	$subject = SITENAME." ".EB_CHALLENGE_L23;

	// All members of the challenged division will receive the PM
	$q = "SELECT ".TBL_TEAMS.".*, "
	.TBL_MEMBERS.".*, "
	.TBL_USERS.".*"
	." FROM ".TBL_TEAMS.", "
	.TBL_USERS.", "
	.TBL_MEMBERS
	." WHERE (".TBL_TEAMS.".TeamID = '$challengedtid')"
	." AND (".TBL_MEMBERS.".Division = ".TBL_TEAMS.".Division)"
	." AND (".TBL_USERS.".user_id = ".TBL_MEMBERS.".User)";
	$result = $sql->db_Query($q);
	$num_rows = mysql_numrows($result);
	if($num_rows > 0)
	{
		for($j=0; $j < $num_rows; $j++)
		{
			$challengedpname = mysql_result($result, $j, TBL_USERS.".user_name");
			$challengedpemail = mysql_result($result, $j, TBL_USERS.".user_email");
			$message = EB_CHALLENGE_L24.$challengedpname.EB_CHALLENGE_L25.$challengertclan.EB_CHALLENGE_L26.$ename.EB_CHALLENGE_L27;
			if (check_class($pref['eb_pm_notifications_class']))
			{
				$sendto = mysql_result($result, $j, TBL_USERS.".user_id");
				sendNotification($sendto, $subject, $message, $fromid);
			}
			if (check_class($pref['eb_email_notifications_class']))
			{
				// Send email
				require_once(e_HANDLER."mail.php");
				sendemail($challengedpemail, $subject, $message);
			}
		}
	}
}
?>



