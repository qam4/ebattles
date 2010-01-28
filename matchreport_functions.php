<?php
// function to output form and hold previously entered values.
function user_form($players_id, $players_name, $eventid, $allowDraw, $allowScore, $userclass) {
    global $text;
    global $tp;

    if (e_WYSIWYG)
    {
        $insertjs = "rows='15'";
    }
    else
    {
        require_once(e_HANDLER."ren_help.php");
        $insertjs = "rows='5' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
    }
    //dbg form
    //print_r($_POST);    // show $_POST
    //print_r($_GET);     // show $_GET

    $reported_by = USERID;
    if(isset($_POST['match_comment']))
    {
        $comment = $tp->toDB($_POST['match_comment']);
    } else {
        $comment = '';
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
    $text .= '<form id="matchreport" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'?eventid='.$eventid.'" method="post">';
    $text .= '<div>';
    // TABLE - Player/Teams Add/Remove
    //----------------------------------
    $text .= EB_MATCHR_L15;
    $text .= '<table id="matchresult_nbrPlayersTeams"><tbody>';
    $text .= '<tr><td><input type="hidden" name="matchreport" value="1"/></td></tr>';
    // Players
    $text .= '<tr><td>'.$nbr_players.'&nbsp;'.EB_MATCHR_L21.'</td>';
    $text .= '<td><input type="hidden" name="nbr_players" value="'.$_POST['nbr_players'].'"/>';
    // Add Player
    if ($nbr_players < $max_nbr_players)
    {
        $text .= '<input class="button" type="submit" value="'.EB_MATCHR_L16.'" name="addPlayer"/></td>';
    }
    else
    {
        $text .= '<input class="button_disabled" type="submit" value="'.EB_MATCHR_L16.'" name="addPlayer" disabled="disabled"/></td>';
    }
    // Remove Player
    if ($nbr_players>2)
    {
        $text .= '<td><input class="button" type="submit" value="'.EB_MATCHR_L17.'" name="removePlayer"/></td>';
    }
    else
    {
        $text .= '<td><input class="button_disabled" type="submit" value="'.EB_MATCHR_L17.'" name="removePlayer" disabled="disabled"/></td>';
    }
    $text .= '</tr>';

    // Teams
    $text .= '<tr><td>'.$nbr_teams.'&nbsp;'.EB_MATCHR_L22.'</td>';
    $text .= '<td><input type="hidden" name="nbr_teams" value="'.$_POST['nbr_teams'].'"/>';
    // Add Team
    if ($nbr_teams<$nbr_players)
    {
        $text .= '<input class="button" type="submit" value="'.EB_MATCHR_L18.'" name="addTeam"/></td>';
    }
    else
    {
        $text .= '<input class="button_disabled" type="submit" value="'.EB_MATCHR_L18.'" name="addTeam" disabled="disabled"/></td>';
    }
    // Remove Team
    if ($nbr_teams>2)
    {
        $text .= '<td><input class="button" type="submit" value="'.EB_MATCHR_L19.'" name="removeTeam"/></td>';
    }
    else
    {
        $text .= '<td><input class="button_disabled" type="submit" value="'.EB_MATCHR_L19.'" name="removeTeam" disabled="disabled"/></td>';
    }
    $text .= "</tr>";
    $text .= '</tbody></table>';

    //$text .= '<p><input class="inspector" type="button" value="Inspect" onclick="junkdrawer.inspectListOrder(\'matchresultlist\')"/></p>';
    $text .= "<br />";

    // TABLE - Players/Teams Selection
    //----------------------------------
    $text .= EB_MATCHR_L20;
    $text .= '<table id="matchresult_selectPlayersTeams"><tbody>';
    for($i=1;$i<=$nbr_players;$i++)
    {
        $text .= '<tr><td>'.EB_MATCHR_L23.$i.':</td>';

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
            $text .= '>'.EB_MATCHR_L29.$j.'</option>';
        }
        $text .= '</select></td>';
        $text .= '</tr>';
    }
    $text .= '</tbody></table>';
    $text .= "<br />";

    // TABLE - Teams Rank Selection
    //----------------------------------
    $text .= EB_MATCHR_L24;
    $text .= '<table id="matchresult_rankTeams"><tbody>';
    $text .= '<tr><td></td><td>'.EB_MATCHR_L25.'</td>';
    if ($allowScore == TRUE) $text .= '<td>'.EB_MATCHR_L26.'</td>';
    if ($allowDraw == TRUE) $text .= '<td>'.EB_MATCHR_L27.'</td>';
    $text .= '</tr>';

    for($i=1;$i<=$nbr_teams;$i++)
    {
        $text .= '<tr>';
        $text .= '<td>';
        $text .= EB_MATCHR_L28.$i.':';
        $text .= '</td>';
        $text .= '<td><select class="tbox" name="rank'.$i.'" id="rank'.$i.'" onchange = "SwitchSelected('.$i.')">';
        for($j=1;$j<=$nbr_teams;$j++)
        {
            $text .= '<option value="Team #'.$j.'"';
            if (strtolower($_POST['rank'.$i]) == 'team #'.$j) $text .= ' selected="selected"';
            $text .= '>'.EB_MATCHR_L29.$j.'</option>';
        }
        $text .= '</select></td>';
        if ($allowScore == TRUE)
        {
            $text .= '<td>';
            $text .= '<input class="tbox" type="text" name="score'.$i.'" value="'.$_POST['score'.$i].'"/>';
            $text .= '</td>';
        }
        if ($allowDraw == TRUE)
        {
            $text .= '<td>';
            if ($i>1)
            {
                $text .= '<input class="tbox" type="checkbox" name="draw'.$i.'" value="1"';
                if (strtolower($_POST['draw'.$i]) != "") $text .= ' checked="checked"';
                $text .= '/>';
            }
            $text .= '</td>';
        }
        $text .= '</tr>';
    }
    $text .= '</tbody></table>';

    $text .= '<br />';
    $text .= '<div style="display:table; margin-left:auto; margin-right:auto;">';
    $text .= EB_MATCHR_L30.'<br />';
    $text .= '<textarea class="tbox" id="match_comment" name="match_comment" style="width:500px" cols="70" '.$insertjs.'>'.$comment.'</textarea>';
    if (!e_WYSIWYG)
    {
        $text .= "<br />".display_help("helpb","comment");
    }
    $text .= '</div>';
    $text .= '<br />';
    $text .= '<div style="display:table; margin-left:auto; margin-right:auto;">';
    $text .= '<input type="hidden" name="userclass" value="'.$userclass.'"/>';
    $text .= '<input type="hidden" name="reported_by" value="'.$reported_by.'"/>';
    $text .= '<input class="button" type="submit" value="'.EB_MATCHR_L31.'" name="submit"/>';
    $text .= '</div>';
    $text .= '<br /><br />';
    $text .= '</div>';
    $text .= '</form>';
}

?>
