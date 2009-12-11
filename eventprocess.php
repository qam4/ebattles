<?php
/**
*EventProcess.php
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
require_once e_PLUGIN.'ebattles/include/event.php';

/*******************************************************************
********************************************************************/
echo '
<html>
<head>
<style type="text/css">
<!--
.percents {
background: #FFF;
position:absolute;
text-align: center;
}
-->
</style>
</head>
<body>
';

//dbg- print_r($_POST);
//dbg- exit;

$event_id = $_GET['eventid'];
if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    $q = "SELECT ".TBL_EVENTS.".*"
    ." FROM ".TBL_EVENTS
    ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
    $result = $sql->db_Query($q);
    $eowner = mysql_result($result,0 , TBL_EVENTS.".Owner");
    $etype = mysql_result($result,0 , TBL_EVENTS.".Type");

    $can_manage = 0;
    if (check_class($pref['eb_mod_class'])) $can_manage = 1;
    if (USERID==$eowner) $can_manage = 1;
    if ($can_manage == 0)
    {
        header("Location: ./eventinfo.php?eventid=$event_id");
        exit();
    }
    else{

        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);

        if(isset($_POST['eventchangeowner']))
        {
            $event_owner = $_POST['eventowner'];

            /* Event Owner */
            $q2 = "UPDATE ".TBL_EVENTS." SET Owner = '$event_owner' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);

            //echo "-- eventchangeowner --<br />";
            header("Location: eventmanage.php?eventid=$event_id");
        }
        if(isset($_POST['eventdeletemod']))
        {
            $eventmod = $_POST['eventmod'];
            $q2 = "DELETE FROM ".TBL_EVENTMODS
            ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
            ."   AND (".TBL_EVENTMODS.".User = '$eventmod')";
            $result2 = $sql->db_Query($q2);

            //echo "-- eventdeletemod --<br />";
            header("Location: eventmanage.php?eventid=$event_id");
        }
        if(isset($_POST['eventaddmod']))
        {
            $eventmod = $_POST['mod'];

            $q2 = "SELECT ".TBL_EVENTMODS.".*"
            ." FROM ".TBL_EVENTMODS
            ." WHERE (".TBL_EVENTMODS.".Event = '$event_id')"
            ."   AND (".TBL_EVENTMODS.".User = '$eventmod')";
            $result2 = $sql->db_Query($q2);
            $num_rows_2 = mysql_numrows($result2);
            if ($num_rows_2==0)
            {
                $q2 = "INSERT INTO ".TBL_EVENTMODS."(Event,User,Level)"
                ." VALUES ('$event_id','$eventmod',1)";
                $result2 = $sql->db_Query($q2);
            }
            //echo "-- eventaddmod --<br />";
            header("Location: eventmanage.php?eventid=$event_id");
        }

        if(isset($_POST['eventsettingssave']))
        {
            $q2 = "SELECT ".TBL_EVENTS.".*"
            ." FROM ".TBL_EVENTS
            ." WHERE (".TBL_EVENTS.".EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);
            $epassword = mysql_result($result2,0 , TBL_EVENTS.".Password");

            /* Event Name */
            $new_eventname = htmlspecialchars($_POST['eventname']);
            if ($new_eventname != '')
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET Name = '$new_eventname' WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }

            /* Event Password */
            $new_eventpassword = htmlspecialchars($_POST['eventpassword']);
            $q2 = "UPDATE ".TBL_EVENTS." SET Password = '$new_eventpassword' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);

            /* Event Type */
            // Can change only if no players are signed up
            $q2 = "SELECT ".TBL_PLAYERS.".*"
            ." FROM ".TBL_PLAYERS
            ." WHERE (".TBL_PLAYERS.".Event = '$event_id')";
            $result2 = $sql->db_Query($q2);
            $num_rows_2 = mysql_numrows($result2);
            if ($num_rows_2==0)
            {
                $new_eventtype = $_POST['eventtype'];
                if ($new_eventtype == 'Individual')
                {
                    $q2 = "UPDATE ".TBL_EVENTS." SET Type = 'One Player Ladder' WHERE (EventID = '$event_id')";
                    $result2 = $sql->db_Query($q2);
                }
                else
                {
                    $q2 = "UPDATE ".TBL_EVENTS." SET Type = 'Team Ladder' WHERE (EventID = '$event_id')";
                    $result2 = $sql->db_Query($q2);
                }
            }

            /* Event Match report userclass */
            $new_eventmatchreportuserclass = $_POST['eventmatchreportuserclass'];
            $q2 = "UPDATE ".TBL_EVENTS." SET match_report_userclass = '$new_eventmatchreportuserclass' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);

            /* Event Quick Loss Report */
            if ($_POST['eventallowquickloss'] != "")
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET quick_loss_report = 1 WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }
            else
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET quick_loss_report = 0 WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }

            /* Event Allow Score */
            if ($_POST['eventallowscore'] != "")
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET AllowScore = 1 WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }
            else
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET AllowScore = 0 WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }

            /* Event Allow Draw */
            if ($_POST['eventallowdraw'] != "")
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET AllowDraw = 1 WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }
            else
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET AllowDraw = 0 WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }

            /* Points */
            $new_eventpointsperwin = htmlspecialchars($_POST['eventpointsperwin']);
            if (preg_match("/^\d+$/", $new_eventpointsperwin))
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET PointsPerWin = '$new_eventpointsperwin' WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }
            $new_eventpointsperdraw = htmlspecialchars($_POST['eventpointsperdraw']);
            if (preg_match("/^\d+$/", $new_eventpointsperdraw))
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET PointsPerDraw = '$new_eventpointsperdraw' WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }
            $new_eventpointsperloss = htmlspecialchars($_POST['eventpointsperloss']);
            if (preg_match("/^\d+$/", $new_eventpointsperloss))
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET PointsPerLoss = '$new_eventpointsperloss' WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }

            /* Event Game */
            $new_eventgame = $_POST['eventgame'];
            $q2 = "UPDATE ".TBL_EVENTS." SET Game = '$new_eventgame' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);

            /* Event Start Date */
            $new_eventstartdate = $_POST['startdate'];
            if ($new_eventstartdate != '')
            {
                $new_eventstart_local = strtotime($new_eventstartdate);
                $new_eventstart = $new_eventstart_local - TIMEOFFSET;	// Convert to GMT time
            }
            else
            {
                $new_eventstart = 0;
            }
            $q2 = "UPDATE ".TBL_EVENTS." SET Start_timestamp = '$new_eventstart' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);
            //echo "$new_eventstart, $new_eventstartdate";

            /* Event End Date */
            $new_eventenddate = $_POST['enddate'];
            if ($new_eventenddate != '')
            {
                $new_eventend_local = strtotime($new_eventenddate);
                $new_eventend = $new_eventend_local - TIMEOFFSET;	// Convert to GMT time
            }
            else
            {
                $new_eventend = 0;
            }
            if ($new_eventend < $new_eventstart)
            {
                $new_eventend = $new_eventstart;
            }

            $q2 = "UPDATE ".TBL_EVENTS." SET End_timestamp = '$new_eventend' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);
            //echo "$new_eventend, $new_eventenddate";


            /* Event Description */
            $new_eventdescription = $tp->toDB($_POST['eventdescription']);
            $q2 = "UPDATE ".TBL_EVENTS." SET Description = '$new_eventdescription' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);

            //echo "-- eventsettingssave --<br />";
            header("Location: eventmanage.php?eventid=$event_id");
        }
        if(isset($_POST['eventrulessave']))
        {
            /* Event Rules */
            $new_eventrules = $tp->toDB($_POST['eventrules']);
            $q2 = "UPDATE ".TBL_EVENTS." SET Rules = '$new_eventrules' WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);

            //echo "-- eventrulessave --<br />";
            header("Location: eventmanage.php?eventid=$event_id");
        }
        if(isset($_POST['eventresetscores']))
        {
            resetPlayers($event_id);
            resetTeams($event_id);
            deleteMatches($event_id);

            //echo "-- eventresetscores --<br />";
            header("Location: eventmanage.php?eventid=$event_id");
        }
        if(isset($_POST['eventresetevent']))
        {
            deleteMatches($event_id);
            deletePlayers($event_id);
            deleteTeams($event_id);

            //echo "-- eventresetevent --<br />";
            header("Location: eventmanage.php?eventid=$event_id");
        }
        if(isset($_POST['eventdelete']))
        {
            deleteEvent($event_id);

            //echo "-- eventdelete --<br />";
            header("Location: events.php");
        }
        if(isset($_POST['eventupdatescores']))
        {
            if (!isset($_POST['match'])) $_POST['match'] = 0;
            $current_match = $_POST['match'];
            eventScoresUpdate($event_id, $current_match);
        }
        if(isset($_POST['eventstatssave']))
        {
            //echo "-- eventstatssave --<br />";
            $cat_index = 0;

            /* Event Min games to rank */
            $new_eventGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
            if (is_numeric($new_eventGamesToRank))
            {
                $q2 = "UPDATE ".TBL_EVENTS." SET nbr_games_to_rank = '$new_eventGamesToRank' WHERE (EventID = '$event_id')";
                $result2 = $sql->db_Query($q2);
            }
            $cat_index++;

            if ($etype == "Team Ladder")
            {
                /* Event Min Team games to rank */
                $new_eventTeamGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
                if (is_numeric($new_eventTeamGamesToRank))
                {
                    $q2 = "UPDATE ".TBL_EVENTS." SET nbr_team_games_to_rank = '$new_eventTeamGamesToRank' WHERE (EventID = '$event_id')";
                    $result2 = $sql->db_Query($q2);
                }
                $cat_index++;
            }

            $q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
            ." FROM ".TBL_STATSCATEGORIES
            ." WHERE (".TBL_STATSCATEGORIES.".Event = '$event_id')";

            $result_1 = $sql->db_Query($q_1);
            $numCategories = mysql_numrows($result_1);

            for($i=0; $i<$numCategories; $i++)
            {
                $cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");

                $new_eventStat = htmlspecialchars($_POST['sliderValue'.$cat_index]);
                if (is_numeric($new_eventStat))
                {
                    $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_eventStat' WHERE (Event = '$event_id') AND (CategoryName = '$cat_name')";
                    $result2 = $sql->db_Query($q2);
                }

                // Display Only
                if ($_POST['infoonly'.$i] != "")
                $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 1 WHERE (Event = '$event_id') AND (CategoryName = '$cat_name')";
                else
                $q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 0 WHERE (Event = '$event_id') AND (CategoryName = '$cat_name')";
                $result2 = $sql->db_Query($q2);

                $cat_index ++;
            }

            // Hide ratings column
            if ($_POST['hideratings'] != "")
            $q2 = "UPDATE ".TBL_EVENTS." SET hide_ratings_column = 1 WHERE (EventID = '$event_id')";
            else
            $q2 = "UPDATE ".TBL_EVENTS." SET hide_ratings_column = 0 WHERE (EventID = '$event_id')";
            $result2 = $sql->db_Query($q2);

            $q4 = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
            $result = $sql->db_Query($q4);

            header("Location: eventmanage.php?eventid=$event_id");
        }
    }
}

header("Location: eventmanage.php?eventid=$event_id");
exit;

?>
