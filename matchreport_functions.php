<?php
// function to output form and hold previously entered values.
function user_form($players_id, $players_name, $eventid, $allowDraw, $allowScore) {
    global $text;


    //dbg form
    //print_r($_POST);    // show $_POST
    //print_r($_GET);     // show $_GET

    $reported_by = USERID;
    $allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
    $allowedTags.='<li><ol><ul><span><div><br /><ins><del>';
    if(isset($_POST['elm1'])) {
        $sHeader = '<h1>Ah, content is king.</h1>';
        $sContent = strip_tags(stripslashes($_POST['elm1']),$allowedTags);
        //remove html bbcode (the html bbcode would not be parsed)
		$sContent = preg_replace("/\\[html\](.*?)\[\/html\]/si", '\1', $sContent);
    } else {
        $sHeader = '<h1>Nothing submitted yet</h1>';
        $sContent = '';
    }

    $max_nbr_players = count($players_id)-1;
    // if vars are not set, set them as empty.
    // (prevents "notice" errors showing for those who have them enabled)
    if (!isset($_POST['nbr_players'])) $_POST['nbr_players'] = 2;
    if (!isset($_POST['nbr_teams'])) $_POST['nbr_teams'] = 2;

    // now to output the form HTML.

    $nbr_players = $_POST['nbr_players'];
    $nbr_teams = $_POST['nbr_teams'];

    if (isset($_POST['addPlayer']))
    {
        $nbr_players++;
    }
    if (isset($_POST['removePlayer']))
    {
        if ($nbr_players==$nbr_teams)
        {
            $nbr_teams--;
        }
        $nbr_players--;
    }
    $_POST['nbr_players']=$nbr_players;

    for($i=1;$i<=$nbr_players;$i++)
    {
        if (!isset($_POST['player'.$i])) $_POST['player'.$i] = $players_id[0];
        //debug - echo "Player #".$i.": ".$_POST['player'.$i]."<br />";
    }

    if (isset($_POST['addTeam']))
    {
        $nbr_teams++;
    }
    if (isset($_POST['removeTeam']))
    {
        $nbr_teams--;
    }
    $_POST['nbr_teams']=$nbr_teams;
    for($i=1;$i<=$nbr_players;$i++)
    {
        if (!isset($_POST['team'.$i])) $_POST['team'.$i] = 'Team #'.$i;
    }

    for($i=1;$i<=$nbr_teams;$i++)
    {
        if (!isset($_POST['rank'.$i])) $_POST['rank'.$i] = 'Team #'.$i;
        if (!isset($_POST['score'.$i])) $_POST['score'.$i] = 0;
    }

    /////////////////
    /// MAIN FORM ///
    /////////////////
    $text .= '<form id="f1" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'?eventid='.$eventid.'" method="post">';

    // TABLE - Player/Teams Add/Remove
    //----------------------------------
    $text .= 'Select the number of players and teams:';
    $text .= '<table id="matchresult"><tbody>';
    $text .= '<tr><input type="hidden" name="matchreport" value="1"></tr>';
    // Players
    $text .= '<tr><td>'.$nbr_players.' players</td>';
    $text .= '<td><input type="hidden" name="nbr_players" value="'.$_POST['nbr_players'].'">';
    // Add Player
    if ($nbr_players < $max_nbr_players)
    {
        $text .= '<input class="button" type="submit" value="Add Player" name="addPlayer"></td>';
    }
    else
    {
        $text .= '<input class="button_disabled" type="submit" value="Add Player" name="addPlayer" DISABLED></td>';
    }
    // Remove Player
    if ($nbr_players>2)
    {
        $text .= '<td><input class="button" type="submit" value="Remove Player" name="removePlayer"></td>';
    }
    else
    {
        $text .= '<td><input class="button_disabled" type="submit" value="Remove Player" name="removePlayer" DISABLED></td>';
    }

    // Teams
    $text .= '<tr><td>'.$nbr_teams.' teams</td>';
    $text .= '<td><input type="hidden" name="nbr_teams" value="'.$_POST['nbr_teams'].'">';
    // Add Team
    if ($nbr_teams<$nbr_players)
    {
        $text .= '<input class="button" type="submit" value="Add Team" name="addTeam"></td>';
    }
    else
    {
        $text .= '<input class="button_disabled" type="submit" value="Add Team" name="addTeam" DISABLED></td>';
    }
    // Remove Team
    if ($nbr_teams>2)
    {
        $text .= '<td><input class="button" type="submit" value="Remove Team" name="removeTeam"></td>';
    }
    else
    {
        $text .= '<td><input class="button_disabled" type="submit" value="Remove Team" name="removeTeam" DISABLED></td>';
    }
    $text .= "</tr>";
    $text .= '</tbody></table>';

    //$text .= '<p><input class="inspector" type="button" value="Inspect" onclick="junkdrawer.inspectListOrder(\'matchresultlist\')"/></p>';
    $text .= "<br />";

    // TABLE - Players/Teams Selection
    //----------------------------------
    $text .= 'Select the players and their respective team:';
    $text .= '<table id="matchresult"><tbody>';
    for($i=1;$i<=$nbr_players;$i++)
    {
        $text .= '<tr><td>Player #'.$i.':</td>';

        $text .= '<td><select class="tbox" name="player'.$i.'">';
        for($j=1;$j <= $max_nbr_players+1;$j++)
        {
            $text .= '<option value="'.$players_id[($j-1)].'"';
            if (strtolower($_POST['player'.$i]) == strtolower($players_id[($j-1)])) $text .= ' selected="selected"';
            $text .= '>'.$players_name[($j-1)].'</option>';
        }
        $text .= '</select></td>';

        $text .= '<td><select class="tbox" name="team'.$i.'">';
        for($j=1;$j<=$nbr_teams;$j++)
        {
            $text .= '<option value="Team #'.$j.'"';
            if (strtolower($_POST['team'.$i]) == 'team #'.$j) $text .= ' selected="selected"';
            $text .= '>Team #'.$j.'</option>';
        }
        $text .= '</select></td>';
        $text .= '</tr>';
    }
    $text .= '</tbody></table>';
    $text .= "<br />";

    // TABLE - Teams Rank Selection
    //----------------------------------
    $text .= 'Select the rank of each team:';
    $text .= '<table id="matchresult"><tbody>';
    $text .= '<tr><td></td><td>Team</td>';
    if ($allowScore == TRUE) $text .= '<td>Score</td>';
    if ($allowDraw == TRUE) $text .= '<td>Draw?</td>';
    $text .= '</tr>';
    
    for($i=1;$i<=$nbr_teams;$i++)
    {
        $text .= '<tr>';
        $text .= '<td>';
        $text .= 'Rank #'.$i.':';
        $text .= '</td>';
        $text .= '<td><select class="tbox" name="rank'.$i.'" id="rank'.$i.'" onChange = "SwitchSelected('.$i.')">';
        for($j=1;$j<=$nbr_teams;$j++)
        {
            $text .= '<option value="Team #'.$j.'"';
            if (strtolower($_POST['rank'.$i]) == 'team #'.$j) $text .= ' selected="selected"';
            $text .= '>Team #'.$j.'</option>';
        }
        $text .= '</select></td>';
        if ($allowScore == TRUE)
        {
            $text .= '<td>';
            $text .= '<input class="tbox" type="text" name="score'.$i.'" value="'.$_POST['score'.$i].'">';
            $text .= '</td>';
        }
        if ($allowDraw == TRUE)
        {
            $text .= '<td>';
            if ($i>1)
            {
                $text .= '<input class="tbox" type="checkbox" name="draw'.$i.'" value="1"';
                if (strtolower($_POST['draw'.$i]) != "") $text .= ' checked';
                $text .= '>';
            }
            $text .= '</td>';
        }
        $text .= '</tr>';
    }
    $text .= '</tbody></table>';

    $text .= '<br />';
    $text .= '<div style="display:table; margin-left:auto; margin-right:auto;">';
    $text .= 'Your comments:<br />';
    $text .= '<textarea class="tbox" id="elm1" name="elm1" style="width:500px">'.$sContent.'</textarea>';
    $text .= '</div>';
    $text .= '<br />';
    $text .= '<div style="display:table; margin-left:auto; margin-right:auto;">';
    $text .= '<input type="hidden" name="reported_by" value="'.$reported_by.'">';
    $text .= '<input class="button" type="submit" value="Submit Match" name="submit">';
    $text .= '</div>';
    $text .= '<br /><br />';
    $text .= '</form>';
}

?>
