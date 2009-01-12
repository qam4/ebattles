<?php
/**
 * EventManage.php
 *
 *
 */
ob_start();
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
?>
<div id="main">
<script type="text/javascript" src="./js/slider.js"></script>
<script type="text/javascript" src="./js/tabpane.js"></script>

<!-- main calendar program -->
<script type="text/javascript" src="calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
     adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="calendar-setup.js"></script>
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

<?php
   $event_id = $_GET['eventid'];
   $self = $_SERVER['PHP_SELF'];

   $q = "SELECT ".TBL_EVENTS.".*, "
                 .TBL_GAMES.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_EVENTS.", "
                .TBL_GAMES.", "
                .TBL_USERS
       ." WHERE (".TBL_EVENTS.".eventid = '$event_id')"
       ."   AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"      
       ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTS.".Owner)";   

   $result = $sql->db_Query($q);
   $ename = mysql_result($result,0 , TBL_EVENTS.".Name");
   $epassword = mysql_result($result,0 , TBL_EVENTS.".Password");
   $egame = mysql_result($result,0 , TBL_GAMES.".Name");
   $egameicon  = mysql_result($result,0 , TBL_GAMES.".Icon");
   $egameid = mysql_result($result,0 , TBL_GAMES.".GameID");
   $etype = mysql_result($result,0 , TBL_EVENTS.".Type");
   $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
   $eownername = mysql_result($result,0 , TBL_USERS.".user_name");
   $emingames = mysql_result($result,0 , TBL_EVENTS.".nbr_games_to_rank");
   $eminteamgames = mysql_result($result,0 , TBL_EVENTS.".nbr_team_games_to_rank");
   $erules = mysql_result($result,0 , TBL_EVENTS.".Rules");
   $edescription = mysql_result($result,0 , TBL_EVENTS.".Description");
   $estart = mysql_result($result,0 , TBL_EVENTS.".Start_timestamp");
   $eend = mysql_result($result,0 , TBL_EVENTS.".End_timestamp");
   if($estart!=0) 
   {
     $estart_local = $estart + $session->timezone_offset;
     $date_start = date("m/d/Y h:i A",$estart_local);
   }
   else
   {
     $date_start = "";
   }
   if($eend!=0) 
   {
     $eend_local = $eend + $session->timezone_offset;
     $date_end = date("m/d/Y h:i A",$eend_local);
   }
   else
   {
     $date_end = "";
   }

   $q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
       ." FROM ".TBL_STATSCATEGORIES
       ." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')";
 
   $result_1 = $sql->db_Query($q_1);
   $num_rows = mysql_numrows($result_1);

   $rating_max=0;
   for($i=0; $i<$num_rows; $i++)
   {
      $cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");
      $cat_min = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMinValue");
      $cat_max = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryMaxValue");
      
         if ($cat_name == "ELO")
         {
            $ELO_minpoints = $cat_min;
            $ELO_maxpoints = $cat_max;
            $rating_max += $ELO_maxpoints;
         }
         if ($cat_name == "GamesPlayed")
         {
            $games_played_minpoints = $cat_min;
            $games_played_maxpoints = $cat_max;
            $rating_max += $games_played_maxpoints;
         }
         if ($cat_name == "VictoryRatio")
         {
            $victory_ratio_minpoints = $cat_min;
            $victory_ratio_maxpoints = $cat_max;
            $rating_max += $victory_ratio_maxpoints;
         }
         if ($cat_name == "VictoryPercent")
         {
            $victory_percent_minpoints = $cat_min;
            $victory_percent_maxpoints = $cat_max;
            $rating_max += $victory_percent_maxpoints;
         }
         if ($cat_name == "UniqueOpponents")
         {
            $unique_opponents_minpoints = $cat_min;
            $unique_opponents_maxpoints = $cat_max;
            $rating_max += $unique_opponents_maxpoints;
         }
         if ($cat_name == "OpponentsELO")
         {
            $opponentsELO_minpoints = $cat_min;
            $opponentsELO_maxpoints = $cat_max;
            $rating_max += $opponentsELO_maxpoints;
         }
         if ($cat_name == "Streaks")
         {
            $streaks_minpoints = $cat_min;
            $streaks_maxpoints = $cat_max;
            $rating_max += $streaks_maxpoints;
         }
   }
   echo "<h1><a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">$ename</a> ($etype)</h1>";
   echo "<h2><img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame</h2>";

   $can_manage = 0;
   if ($session->isAdmin()) $can_manage = 1;
   if ({USER_ID}==$eowner) $can_manage = 1;
   if ($can_manage == 0)
   {
      header("Location: index.php");
      ob_end_flush();
   }
   else
   {
      ob_end_flush();
?>

<h1>Manage your event</h1>

<div class="tab-pane" id="tab-pane-3">

<div class="tab-page">
<h2 class="tab">Event Summary</h2>
<br /><br />


<?php
   echo "<p>";
   echo"Owner: <a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$eowner\">$eownername</a><br />";
   echo"</p>";

   $q = "SELECT ".TBL_EVENTMODS.".*, "
                 .TBL_USERS.".*"
       ." FROM ".TBL_EVENTMODS.", "
                .TBL_USERS
       ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
       ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTMODS.".Name)";   
   $result = $sql->db_Query($q);
   $num_rows = mysql_numrows($result);
   echo "Moderators:<br />";
   if ($num_rows>0)
   {
      echo "<table>";
      for($i=0; $i<$num_rows; $i++){
         echo"<tr>";
         $modid  = mysql_result($result,$i, TBL_USERS.".user_id");
         $modname  = mysql_result($result,$i, TBL_USERS.".user_name");
         echo "<form action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
         echo "<td><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$modid\">$modname</a></td>";
         echo "<td>";
         echo "<input type=\"hidden\" name=\"eventmod\" value=\"$modid\"></input>";
         echo "<input type=\"hidden\" name=\"eventdeletemod\" value=\"1\"></input>";
         echo "<input type=\"submit\" value=\"Remove Moderator\" onclick=\"return confirm('Are you sure you want to remove this moderator?');\"></input>";
         echo "</form>";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table>";
   }
   echo "<form name=\"eventaddmodsform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
   $q = "SELECT ".TBL_USERS.".*"
       ." FROM ".TBL_USERS;
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   echo "<table>";
   echo "<tr>";
   echo "<td><select name=\"mod\">\n";
   for($i=0; $i<$num_rows; $i++){
      $uid  = mysql_result($result,$i, TBL_USERS.".user_id");
      $uname  = mysql_result($result,$i, TBL_USERS.".user_name");
         echo "<option value=\"$uname\">$uname ($uid)</option>\n";
   }
   echo "</select>\n";
   echo "</td>\n";
   echo "<td>";
   echo "<input type=\"hidden\" name=\"eventaddmod\"></input>";
   echo "<input type=\"submit\" value=\"Add Moderator\"></input>";
   echo "</td>";
   echo "</tr>";
   echo "</table>";
   echo "</form>";

?>

</div>


<div class="tab-page">
<h2 class="tab">Event Settings</h2>
<br /><br />

<?php 
echo "<form name=\"eventsettingsform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
?>

<table border="0" cellspacing="0" cellpadding="3">
<!-- Event Name -->
<tr>
  <td><b>Name:</b></td>
  <td>
    <input type="text" size="40" name="eventname" value="<?php echo "$ename";?>"></input>
  </td>
</tr>

<!-- Event Password -->
<tr>
  <td><b>Join Event Password:</b></td>
  <td>
    <input type="text" size="40" name="eventpassword" value="<?php echo "$epassword";?>"></input>
  </td>
</tr>

<!-- Event Game -->
<?php
   $q = "SELECT ".TBL_GAMES.".*"
       ." FROM ".TBL_GAMES
       ." ORDER BY Name";
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   echo "<tr><td>\n";
   echo "<b>Game:</b></td>\n";
   echo "<td><select name=\"eventgame\">\n";
   for($i=0; $i<$num_rows; $i++){
      $gname  = mysql_result($result,$i, TBL_GAMES.".name");
      $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
      if ($egame == $gname)
      {
         echo "<option value=\"$gid\" selected=\"selected\">".htmlspecialchars($gname)."</option>\n";
      }
      else
      {
         echo "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
      }
   }
   echo "</select>\n";
   echo "</td></tr>\n";
?>

<!-- Type -->
<tr>
  <td><b>Type:</b></td>
  <td>
<?php
   if ($etype == "Team Ladder")
   {
?>
    <input type="radio" size="40" name="eventtype" value="Individual">Individual</input>
    <input type="radio" size="40" name="eventtype" checked="checked" value="Team">Team</input>
<?php
}
else
{
?>
    <input type="radio" size="40" name="eventtype" checked="checked" value="Individual" />Individual
    <input type="radio" size="40" name="eventtype" value="Team" />Team
<?php
}
?>
  </td>
</tr>

<!-- Start Date -->
<tr>
  <td><b>Start Date:</b></td>
  <td>
<table cellspacing="0" cellpadding="0" style="border-collapse: collapse">
<tr>
  <td>
   <input type="text" name="startdate" id="f_date_start"  value="<?php echo $date_start?>" readonly="readonly" />
  </td>
  <td>
     <img src="img.gif" alt="date selector" id="f_trigger_start" style="cursor: pointer; border: 1px solid red;" title="Date selector"
      onmouseover="this.style.background='red';" onmouseout="this.style.background=''" />
  </td>
  <td>
    <input type="button" value="Reset" onclick="clearStartDate(this.form);"></input>
  </td>
</tr>
</table>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_start",      // id of the input field
        ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_start",   // trigger for the calendar (button ID)
        singleClick    :    true,           // single-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
  </td>
</tr>

<!-- End Date -->
<tr>
  <td><b>End Date:</b></td>
  <td>
<table cellspacing="0" cellpadding="0" style="border-collapse: collapse">
<tr>
  <td>
   <input type="text" name="enddate" id="f_date_end"  value="<?php echo $date_end?>" readonly="readonly" />
  </td>
  <td>
     <img src="img.gif" alt="date selector" id="f_trigger_end" style="cursor: pointer; border: 1px solid red;" title="Date selector"
      onmouseover="this.style.background='red';" onmouseout="this.style.background=''" />
  </td>
  <td>
    <input type="button" value="Reset" onclick="clearEndDate(this.form);"></input>
  </td>
</tr>
</table>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_end",      // id of the input field
        ifFormat       :    "%m/%d/%Y %I:%M %p",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_end",   // trigger for the calendar (button ID)
        singleClick    :    true,           // single-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
  </td>
</tr>

<!-- Description -->
<tr>
  <td><b>Descrition:</b></td>
  <td>
<?php
    echo '<textarea id="eventdescription" name="eventdescription" cols="70" rows="20">'.$edescription.'</textarea>';
?>
  </td>
</tr>
</table>



<br /><br />

<!-- Save Button -->
<p align="center">
    <input type="hidden" name="eventsettingssave" value="1"></input>
    <input type="submit" value="Save"></input>
</p>

</form>
</div>

<div class="tab-page">
<h2 class="tab">Event Rules</h2>
<br /><br />
<?php 
echo "<form name=\"eventrulesform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
?>

<table border="0" cellspacing="0" cellpadding="3">
<!-- Rules -->
<tr>
  <td><b>Rules:</b></td>
  <td>
<?php
    echo '<textarea id="eventrules" name="eventrules" cols="70" rows="20">'.$erules.'</textarea>';
?>
  </td>
</tr>
</table>

<br /><br />

<!-- Save Button -->
<p align="center">
    <input type="hidden" name="eventrulessave" value="1"></input>
    <input type="submit" value="Save"></input>
</p>

</form>
</div>

<div class="tab-page">
<h2 class="tab">Event Reset</h2>
<br /><br />
<div class="news">
<h2>Reset Event</h2>
<?php 
/* fm -- why 2 eventresetforms her!!!? */

echo "<form name=\"eventresetform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
?>
<h3>Reset Players/Teams.</h3>
- Reset Players and Teams Statistics (Rank, Score, ELO, Games Played, Wins, Losses),<br />
- Delete all Matches.
<table border="0" cellspacing="0" cellpadding="3">
<tr>
    <td>
       <input type="hidden" name="eventresetscores" value="1"></input>
       <input type="submit" value="Reset Scores" onclick="return confirm('Are you sure you want to delete this event scores?');"></input>
    </td>
</tr>
</table>
</form>

<?php 
echo "<form name=\"eventresetform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
?>
<h3>Reset Event.</h3>
- Delete all Players and Teams.<br />
- Delete all Matches.
<table border="0" cellspacing="0" cellpadding="3">
<tr>
    <td>
       <input type="hidden" name="eventresetevent" value="1"></input>
       <input type="submit" value="Reset Event" onclick="return confirm('Are you sure you want to reset this event?');"></input>
    </td>
</tr>
</table>
</form>
</div>
<br /><br />
<div class="news">
<h2>Delete Event</h2>
<?php 
echo "<form name=\"eventdeleteform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
?>
- Delete Event.<br />
<table border="0" cellspacing="0" cellpadding="3">
<tr>
    <td>
       <input type="hidden" name="eventdelete" value="1"></input>
       <input type="submit" value="Delete Event" onclick="return confirm('Are you sure you want to delete this event?');"></input>
    </td>
</tr>
</table>
</form>

</div>
</div>

<div class="tab-page">
<h2 class="tab">Event Stats</h2>
<br /><br />
<?php 
echo "<form name=\"eventstatsform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
?>
<table cellpadding="2" border="0">
<tr>
<td >
Number of Matches to Rank:
</td>
<td>
<input name="sliderValue0" id="sliderValue0" type="text" size="3" onchange="A_SLIDERS[0].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
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
		's_imgControl': 'images/sldr3h_bg.gif',
		's_imgSlider': 'images/sldr3h_sl.gif',
		'n_zIndex': 1
	}
	var A_INIT0 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue0',
		'n_minValue' : 0,
		'n_maxValue' : 10,
		'n_value' : <?php echo "$emingames";?>,
		'n_step' : 1
	}

	new slider(A_INIT0, A_TPL);
</script>
</td>
</tr>

<?php
if ($etype == "Team Ladder")
{
?>
<tr>
<td >
Number of Team Matches to Rank:
</td>
<td>
<input name="sliderValue1" id="sliderValue1" type="text" size="3" onchange="A_SLIDERS[1].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT1 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue1',
		'n_minValue' : 0,
		'n_maxValue' : 10,
		'n_value' : <?php echo "$eminteamgames";?>,
		'n_step' : 1
	}

	new slider(A_INIT1, A_TPL);
</script>
</td>
</tr>
<?php
}
?>

<tr>
<td >
ELO Max:
</td>
<td>
<input name="sliderValue2" id="sliderValue2" type="text" size="3" onchange="A_SLIDERS[2].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT2 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue2',
		'n_minValue' : 0,
		'n_maxValue' : 100,
		'n_value' : <?php echo "$ELO_maxpoints";?>,
		'n_step' : 1
	}

	new slider(A_INIT2, A_TPL);
</script>
</td>
</tr>

<tr>
<td >
Games Played Max:
</td>
<td>
<input name="sliderValue3" id="sliderValue3" type="text" size="3" onchange="A_SLIDERS[3].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT3 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue3',
		'n_minValue' : 0,
		'n_maxValue' : 100,
		'n_value' : <?php echo "$games_played_maxpoints";?>,
		'n_step' : 1
	}

	new slider(A_INIT3, A_TPL);
</script>
</td>
</tr>

<tr>
<td >
Victory Ratio Max:
</td>
<td>
<input name="sliderValue4" id="sliderValue4" type="text" size="3" onchange="A_SLIDERS[4].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT4 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue4',
		'n_minValue' : 0,
		'n_maxValue' : 100,
		'n_value' : <?php echo "$victory_ratio_maxpoints";?>,
		'n_step' : 1
	}

	new slider(A_INIT4, A_TPL);
</script>
</td>
</tr>

<tr>
<td >
Victory Percent Max:
</td>
<td>
<input name="sliderValue5" id="sliderValue5" type="text" size="3" onchange="A_SLIDERS[5].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT5 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue5',
		'n_minValue' : 0,
		'n_maxValue' : 100,
		'n_value' : <?php echo "$victory_percent_maxpoints";?>,
		'n_step' : 1
	}

	new slider(A_INIT5, A_TPL);
</script>
</td>
</tr>

<tr>
<td >
Unique Opponents Max:
</td>
<td>
<input name="sliderValue6" id="sliderValue6" type="text" size="3" onchange="A_SLIDERS[6].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT6 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue6',
		'n_minValue' : 0,
		'n_maxValue' : 100,
		'n_value' : <?php echo "$unique_opponents_maxpoints";?>,
		'n_step' : 1
	}

	new slider(A_INIT6, A_TPL);
</script>
</td>
</tr>

<tr>
<td >
Opponents Avg ELO:
</td>
<td>
<input name="sliderValue7" id="sliderValue7" type="text" size="3" onchange="A_SLIDERS[7].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT7 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue7',
		'n_minValue' : 0,
		'n_maxValue' : 100,
		'n_value' : <?php echo "$opponentsELO_maxpoints";?>,
		'n_step' : 1
	}

	new slider(A_INIT7, A_TPL);
</script>
</td>
</tr>

<tr>
<td >
Streaks:
</td>
<td>
<input name="sliderValue8" id="sliderValue8" type="text" size="3" onchange="A_SLIDERS[8].f_setValue(this.value)"></input>
</td>
</tr>
<tr>
<td>
<script type="text/javascript">
	var A_INIT8 = {
		's_form' : 'eventstatsform',
		's_name': 'sliderValue8',
		'n_minValue' : 0,
		'n_maxValue' : 100,
		'n_value' : <?php echo "$streaks_maxpoints";?>,
		'n_step' : 1
	}

	new slider(A_INIT8, A_TPL);
</script>
</td>
</tr>

<tr>
<td>
Ranking Max:
</td>
<td>
<?php echo $rating_max;?>
</td>
</tr>
</table>

<br /><br />

<!-- Save Button -->
<p align="center">
    <input type="hidden" name="eventstatssave" value="1"></input>
    <input type="submit" value="Save"></input>
</p>
</form>
</div>


</div>
<?php
}
?>
<p>
Back to [<a href="./index.php">Main Page</a>]
</p>

</div>
<script type="text/javascript">
//<![CDATA[

setupAllTabs();

//]]>
</script>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
