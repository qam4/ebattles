<?php
/**
 * EventManage.php
 *
 *
 */

require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text = '
<script type="text/javascript" src="./js/slider.js"></script>
<script type="text/javascript" src="./js/tabpane.js"></script>

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
    <script language="javascript" type="text/javascript" src="./js/tiny_mce/tiny_mce.js"></script>
    <script language="javascript" type="text/javascript">
    tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	skin : "o2k7",
	skin_variant : "black",
	plugins : "table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,searchreplace,print,contextmenu",
	theme_advanced_buttons1 : "save,print,preview,separator,bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright, justifyfull",
	theme_advanced_buttons2: "cut,copy,paste,separator,undo,redo,bullist,numlist,separator,outdent,indent",
	theme_advanced_buttons2_add : "separator,forecolor,backcolor",
	theme_advanced_buttons3 : "link,unlink,image,charmap,emotions,insertdate,inserttime",
	theme_advanced_toolbar_location : "bottom",
	theme_advanced_toolbar_align : "left",
	plugin_insertdate_dateFormat : "%Y-%m-%d",
	plugin_insertdate_timeFormat : "%H:%M:%S",
	extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
   });
    </script>
';

$event_id = $_GET['eventid'];
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
     $estart_local = $estart + GMT_TIMEOFFSET;
     $date_start = date("m/d/Y h:i A",$estart_local);
   }
   else
   {
     $date_start = "";
   }
   if($eend!=0) 
   {
     $eend_local = $eend + GMT_TIMEOFFSET;
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
   $text .= "<h1><a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">$ename</a> ($etype)</h1>";
   $text .= "<h2><img src=\"".e_PLUGIN."ebattles/images/games_icons/$egameicon\" alt=\"$egameicon\"></img> $egame</h2>";

   $can_manage = 0;
   if (check_class(e_UC_MAINADMIN)) $can_manage = 1;
   if (USERID==$eowner) $can_manage = 1;
   if ($can_manage == 0)
   {
	   header("Location: ./eventinfo.php?eventid=$event_id");
	   exit();
   }
   else
   {
      //***************************************************************************************
      $text .='
      <div class="tab-pane" id="tab-pane-3">
      
      <div class="tab-page">
      <div class="tab">Event Summary</div>
      ';

      $text .= '
        <table class="fborder">
        <tbody>
          <tr>
      ';
      $text .= '<td class="forumheader3">Owner</td>';
      $text .= '<td class="forumheader3">';
      $text .= "<a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$eowner\">$eownername</a></td>";
      $text .= '
          </tr>
      ';
      
      $q = "SELECT ".TBL_EVENTMODS.".*, "
                    .TBL_USERS.".*"
          ." FROM ".TBL_EVENTMODS.", "
                   .TBL_USERS
          ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"  
          ."   AND (".TBL_USERS.".user_id = ".TBL_EVENTMODS.".Name)";   
      $result = $sql->db_Query($q);
      $num_rows = mysql_numrows($result);
      $text .= '
          <tr>
      ';
      $text .= '<td class="forumheader3">Moderators</td>';
      $text .= '<td class="forumheader3">';
      if ($num_rows>0)
      {
         $text .= "<table>";
         for($i=0; $i<$num_rows; $i++){
            $modid  = mysql_result($result,$i, TBL_USERS.".user_id");
            $modname  = mysql_result($result,$i, TBL_USERS.".user_name");
            $text .="<tr>";
            $text .= "<form action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
            $text .= "<td><a href=\"".e_PLUGIN."ebattles/userinfo.php?user=$modid\">$modname</a></td>";
            $text .= "<td>";
            $text .= "<input type=\"hidden\" name=\"eventmod\" value=\"$modid\"></input>";
            $text .= "<input type=\"hidden\" name=\"eventdeletemod\" value=\"1\"></input>";
            $text .= "<input class=\"button\" type=\"submit\" value=\"Remove Moderator\" onclick=\"return confirm('Are you sure you want to remove this moderator?');\"></input>";
            $text .= "</form>";
            $text .= "</td>";
            $text .= "</tr>";
         }
         $text .= "</table>";
      }
      $text .= "<form name=\"eventaddmodsform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
      $q = "SELECT ".TBL_USERS.".*"
          ." FROM ".TBL_USERS;
      $result = $sql->db_Query($q);
      /* Error occurred, return given name by default */
      $num_rows = mysql_numrows($result);
      $text .= '
                <table>
                  <tr>
                    <td>
                      <select name="mod">
      ';
      for($i=0; $i<$num_rows; $i++){
         $uid  = mysql_result($result,$i, TBL_USERS.".user_id");
         $uname  = mysql_result($result,$i, TBL_USERS.".user_name");
            $text .= "<option value=\"$uid\">$uname</option>\n";
      }
      $text .= '
                      </select>
                    </td>
                    <td>
                      <input type="hidden" name="eventaddmod"></input>
                      <input class="button" type="submit" value="Add Moderator"></input>
                    </td>
                  </tr>
                </table>
                </form>
      ';
      $text .= '
            </td>
          </tr>
        </tbody>
        </table>
        </div>
      ';
      
      //***************************************************************************************
      $text .= '
      <div class="tab-page">
      <div class="tab">Event Settings</div>
      ';
      $text .= "<form name=\"eventsettingsform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
      $text .= '
      <table class="fborder">
      <tbody">
      ';
      //<!-- Event Name -->
      $text .= '
      <tr>
        <td class="forumheader3"><b>Name</b></td>
        <td class="forumheader3">
          <input type="text" size="40" name="eventname" value="'.$ename.'"></input>
        </td>
      </tr>
      ';
      
      //<!-- Event Password -->
      $text .= '
      <tr>
        <td class="forumheader3"><b>Join Event Password</b></td>
        <td class="forumheader3">
          <input type="text" size="40" name="eventpassword" value="'.$epassword.'"></input>
        </td>
      </tr>
      ';
      //<!-- Event Game -->
      
      $q = "SELECT ".TBL_GAMES.".*"
          ." FROM ".TBL_GAMES
          ." ORDER BY Name";
      $result = $sql->db_Query($q);
      /* Error occurred, return given name by default */
      $num_rows = mysql_numrows($result);
      $text .= '<tr>';
      $text .= '<td class="forumheader3"><b>Game</b></td>';
      $text .= '<td class="forumheader3"><select name="eventgame">';
      for($i=0; $i<$num_rows; $i++){
         $gname  = mysql_result($result,$i, TBL_GAMES.".name");
         $gid  = mysql_result($result,$i, TBL_GAMES.".GameID");
         if ($egame == $gname)
         {
            $text .= "<option value=\"$gid\" selected=\"selected\">".htmlspecialchars($gname)."</option>\n";
         }
         else
         {
            $text .= "<option value=\"$gid\">".htmlspecialchars($gname)."</option>\n";
         }
      }
      $text .= '</select>';
      $text .= '</td></tr>';
      
      //<!-- Type -->
      $text .= '
      <tr>
        <td class="forumheader3"><b>Type</b></td>
        <td class="forumheader3">
      ';
         if ($etype == "Team Ladder")
         {
          $text .= '<input type="radio" size="40" name="eventtype" value="Individual">Individual</input>';
          $text .= '<input type="radio" size="40" name="eventtype" checked="checked" value="Team">Team</input>';
         }
         else
         {
          $text .= '<input type="radio" size="40" name="eventtype" checked="checked" value="Individual" />Individual';
          $text .= '<input type="radio" size="40" name="eventtype" value="Team" />Team';
         }
      $text .='
        </td>
      </tr>
      ';
      
      //<!-- Start Date -->
      $text .= '
      <tr>
        <td class="forumheader3"><b>Start Date</b></td>
        <td class="forumheader3">
          <table>
            <tr>
              <td>
               <input type="text" name="startdate" id="f_date_start"  value="'.$date_start.'" readonly="readonly" />
              </td>
              <td>
                 <img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_start" style="cursor: pointer; border: 1px solid red;" title="Date selector"
            ';
      $text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
      $text .= '
              </td>
              <td>
                <input class="button" type="button" value="Reset" onclick="clearStartDate(this.form);"></input>
              </td>
            </tr>
          </table>
      ';
      $text .= '
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
      ';
      
      //<!-- End Date -->
      $text .= '
      <tr>
        <td class="forumheader3"><b>End Date</b></td>
        <td class="forumheader3">
      <table>
      <tr>
        <td>
         <input type="text" name="enddate" id="f_date_end"  value="'.$date_end.'" readonly="readonly" />
        </td>
        <td>
           <img src="./js/calendar/img.gif" alt="date selector" id="f_trigger_end" style="cursor: pointer; border: 1px solid red;" title="Date selector"
      ';
      $text .= "onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\" />";
      $text .= '
        </td>
        <td>
          <input class="button" type="button" value="Reset" onclick="clearEndDate(this.form);"></input>
        </td>
      </tr>
      </table>
      ';
      $text .= '
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
      ';
      
      //<!-- Description -->
      $text .= '
      <tr>
        <td class="forumheader3"><b>Description</b></td>
        <td class="forumheader3">
      ';
      $text .= '<textarea id="eventdescription" name="eventdescription" cols="70" rows="20">'.$edescription.'</textarea>';
      $text .= '
        </td>
      </tr>
      </tbody">
      </table>
      ';
      
      $text .= '
      <br /><br />
      ';
      
      //<!-- Save Button -->
      $text .= '
      <p align="center">
          <input type="hidden" name="eventsettingssave" value="1"></input>
          <input class="button" type="submit" value="Save"></input>
      </p>
      
      </form>
      </div>
      ';
      //***************************************************************************************
      $text .= '
      <div class="tab-page">
      <div class="tab">Event Rules</div>
      ';
      $text .= "<form name=\"eventrulesform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
      
      $text .= '
      <table class="fborder">
      <tbody>
      ';
      //<!-- Rules -->
      $text .= '
      <tr>
        <td class="forumheader3"><b>Rules</b></td>
        <td class="forumheader3">
      ';
      $text .= '<textarea id="eventrules" name="eventrules" cols="70" rows="20">'.$erules.'</textarea>';
      $text .= '
        </td>
      </tr>
      </tbody>
      </table>
      
      <br /><br />
      ';
      //<!-- Save Button -->
      $text .= '
      <p align="center">
          <input type="hidden" name="eventrulessave" value="1"></input>
          <input class="button" type="submit" value="Save"></input>
      </p>
      
      </form>
      </div>
      ';
      
      //***************************************************************************************
      $text .='
      <div class="tab-page">
      <div class="tab">Event Reset</div>
      <table class="fborder">
      <tbody>
        <tr>
      ';
      $text .= "<form name=\"scoreresetform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
      $text .= '
          <td class="forumheader3"><b>Reset Players/Teams.</b><br>
          - Reset Players and Teams Statistics (Rank, Score, ELO, Games Played, Wins, Losses)<br />
          - Delete all Matches
          </td>
          <td class="forumheader3">
             <input type="hidden" name="eventresetscores" value="1"></input>
      ';
      $text .= "<input class=\"button\" type=\"submit\" value=\"Reset Scores\" onclick=\"return confirm('Are you sure you want to delete this event scores?');\"></input>";
      $text .= '
          </td>
        </form>
        </tr>
        <tr>
      ';
      $text .= "<form name=\"eventresetform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
      $text .= '
          <td class="forumheader3"><b>Reset Event.</b><br>
          - Delete all Players and Teams.<br />
          - Delete all Matches.
          </td>
          <td class="forumheader3">
             <input type="hidden" name="eventresetevent" value="1"></input>
      ';
      $text .= "<input class=\"button\" type=\"submit\" value=\"Reset Event\" onclick=\"return confirm('Are you sure you want to reset this event?');\"></input>";
      $text .= '
          </td>
        </form>
        </tr>
        <tr>
      ';
      $text .= "<form name=\"eventdeleteform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
      $text .= '
          <td class="forumheader3"><b>Delete Event.</b><br>
          - Delete Event.<br />
          </td>
          <td class="forumheader3">
             <input type="hidden" name="eventdelete" value="1"></input>
      ';
      $text .= "<input class=\"button\" type=\"submit\" value=\"Delete Event\" onclick=\"return confirm('Are you sure you want to delete this event?');\"></input>";
      $text .= '
           </td>
        </form>
        </tr>
      </tbody>
      </table>
      </div>
      ';
      //***************************************************************************************
      $text .= '
      <div class="tab-page">
      <div class="tab">Event Stats</div>
      ';
      $text .= "<form name=\"eventstatsform\" action=\"".e_PLUGIN."ebattles/eventprocess.php?eventid=$event_id\" method=\"post\">";
      $text .= '
      <table class="fborder">
      <tr>
      <td class="forumheader3">
      Number of Matches to Rank:
      </td>
      <td class="forumheader3">
      <input name="sliderValue0" id="sliderValue0" type="text" size="3" onchange="A_SLIDERS[0].f_setValue(this.value)"></input>
      </td>
      <td class="forumheader3">
      <script type="text/javascript">
      ';
      $text .= "
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
      		'n_value' : ".$emingames.",
      		'n_step' : 1
      	}
      
         	new slider(A_INIT0, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
      ';   
      
      if ($etype == "Team Ladder")
      {
      $text .= '
         <tr>
         <td class="forumheader3">
         Number of Team Matches to Rank:
         </td>
         <td class="forumheader3">
         <input name="sliderValue1" id="sliderValue1" type="text" size="3" onchange="A_SLIDERS[1].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT1 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue1',
         		'n_minValue' : 0,
         		'n_maxValue' : 10,
         		'n_value' : ".$eminteamgames.",
         		'n_step' : 1
         	}
         
         	new slider(A_INIT1, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
      ';
      }   
      $text .= '
         <tr>
         <td class="forumheader3">
         ELO Max:
         </td>
         <td class="forumheader3">
         <input name="sliderValue2" id="sliderValue2" type="text" size="3" onchange="A_SLIDERS[2].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT2 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue2',
         		'n_minValue' : 0,
         		'n_maxValue' : 100,
         		'n_value' : ".$ELO_maxpoints.",
         		'n_step' : 1
         	}
         
         	new slider(A_INIT2, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
         
         <tr>
         <td class="forumheader3">
         Games Played Max:
         </td>
         <td class="forumheader3">
         <input name="sliderValue3" id="sliderValue3" type="text" size="3" onchange="A_SLIDERS[3].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT3 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue3',
         		'n_minValue' : 0,
         		'n_maxValue' : 100,
         		'n_value' : ".$games_played_maxpoints.",
         		'n_step' : 1
         	}
         
         	new slider(A_INIT3, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
         
         <tr>
         <td class="forumheader3">
         Victory Ratio Max:
         </td>
         <td class="forumheader3">
         <input name="sliderValue4" id="sliderValue4" type="text" size="3" onchange="A_SLIDERS[4].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT4 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue4',
         		'n_minValue' : 0,
         		'n_maxValue' : 100,
         		'n_value' : ".$victory_ratio_maxpoints.",
         		'n_step' : 1
         	}
         
         	new slider(A_INIT4, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
         
         <tr>
         <td class="forumheader3">
         Victory Percent Max:
         </td>
         <td class="forumheader3">
         <input name="sliderValue5" id="sliderValue5" type="text" size="3" onchange="A_SLIDERS[5].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT5 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue5',
         		'n_minValue' : 0,
         		'n_maxValue' : 100,
         		'n_value' : ".$victory_percent_maxpoints.",
         		'n_step' : 1
         	}
         
         	new slider(A_INIT5, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
         
         <tr>
         <td class="forumheader3">
         Unique Opponents Max:
         </td>
         <td class="forumheader3">
         <input name="sliderValue6" id="sliderValue6" type="text" size="3" onchange="A_SLIDERS[6].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT6 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue6',
         		'n_minValue' : 0,
         		'n_maxValue' : 100,
         		'n_value' : ".$unique_opponents_maxpoints.",
         		'n_step' : 1
         	}
         
         	new slider(A_INIT6, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
         
         <tr>
         <td class="forumheader3">
         Opponents Avg ELO:
         </td>
         <td class="forumheader3">
         <input name="sliderValue7" id="sliderValue7" type="text" size="3" onchange="A_SLIDERS[7].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT7 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue7',
         		'n_minValue' : 0,
         		'n_maxValue' : 100,
         		'n_value' : ".$opponentsELO_maxpoints.",
         		'n_step' : 1
         	}
         
         	new slider(A_INIT7, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
         
         <tr>
         <td class="forumheader3">
         Streaks:
         </td>
         <td class="forumheader3">
         <input name="sliderValue8" id="sliderValue8" type="text" size="3" onchange="A_SLIDERS[8].f_setValue(this.value)"></input>
         </td>
         <td class="forumheader3">
         <script type="text/javascript">
      ';
      $text .= "
         	var A_INIT8 = {
         		's_form' : 'eventstatsform',
         		's_name': 'sliderValue8',
         		'n_minValue' : 0,
         		'n_maxValue' : 100,
         		'n_value' : ".$streaks_maxpoints.",
         		'n_step' : 1
         	}
      
         new slider(A_INIT8, A_TPL);
      ";
      $text .= '
         </script>
         </td>
         </tr>
         
         <tr>
         <td class="forumheader3">
         Ranking Max:
         </td>
         <td class="forumheader3" colspan="2">
      ';
         
      $text .= $rating_max;
      $text .='
         </td>
         </tr>
         </table>
         
         <br /><br />
         
         <!-- Save Button -->
         <p align="center">
             <input type="hidden" name="eventstatssave" value="1"></input>
             <input class="button" type="submit" value="Save"></input>
         </p>
         </form>
         </div>
         </div>
         <script type="text/javascript">
         //<![CDATA[
         
         setupAllTabs();
         
         //]]>
         </script>
      ';
   }
}

$ns->tablerender('Manage Event', $text);
require_once(FOOTERF);
exit;
?>
