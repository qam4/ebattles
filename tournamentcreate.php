<?php
/**
*TournamentCreate.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/tournament.php");
require_once(HEADERF);
require_once(e_PLUGIN."ebattles/include/ebattles_header.php");
$text .= '
<script type="text/javascript" src="./js/tournament.js"></script>
';

$text .= '
<script type="text/javascript" src="./js/tournament.js"></script>
';
$text .= '
<script type="text/javascript" src="./js/slider.js"></script>

<!-- main calendar program -->
<script type="text/javascript" src="./js/calendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="./js/calendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="./js/calendar/calendar-setup.js"></script>
<script type="text/javascript">
<!--//
function clearStartDate(frm)
{
frm.startdate.value = ""
}
//-->
</script>
<script type="text/javascript">
<!--//
function clearEndDate(frm)
{
frm.enddate.value = ""
}
//-->
</script>
';
$text .= "
<script type='text/javascript'>
<!--//
function kick_player(v)
{
document.getElementById('kick_player').value=v;
document.getElementById('playersform').submit();
}
function ban_player(v)
{
document.getElementById('ban_player').value=v;
document.getElementById('playersform').submit();
}
function unban_player(v)
{
document.getElementById('unban_player').value=v;
document.getElementById('playersform').submit();
}
function del_player_games(v)
{
document.getElementById('del_player_games').value=v;
document.getElementById('playersform').submit();
}
function del_player_awards(v)
{
document.getElementById('del_player_awards').value=v;
document.getElementById('playersform').submit();
}
//-->
</script>
";
$text .= "
<script type='text/javascript'>
<!--//
	// Forms
	$(function() {
		//$( '#tournamentchangeowner, #tournamentdeletemod, #tournamentaddmod, #tournamentdeletemap, #tournamentaddmap, #tournamentsettingssave, #tournamentrulessave, #tournamentaddteam, #tournamentaddplayer, #tournamentresettournament, #tournamentdelete' ).button();
		$( 'button' ).button();
	});
//-->
</script>
";
/*
$text .= "
<script>
	$(function() {
	    $('#test').datepicker({
	    	duration: '',
	        showTime: true,
	        constrainInput: false
	     });
	});
</script>
";
*/

$tournament = new Tournament();

if ((!isset($_POST['createtournament']))||(!check_class($pref['eb_tournaments_create_class'])))
{
	$text .= '<br />'.EB_TOURNAMENTC_L2.'<br />';
}
else
{
	$text .= $tournament->displayTournamentSettingsForm();
}

$ns->tablerender(EB_TOURNAMENTC_L1, $text);
require_once(FOOTERF);
exit;
?>