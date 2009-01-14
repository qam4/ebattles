<?php
// function to output form and hold previously entered values.

function user_form($players_id, $players_name, $eventid) {
    global $text;

    $reported_by = USERID;
    $allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
    $allowedTags.='<li><ol><ul><span><div><br /><ins><del>';
    if(isset($_POST['elm1'])) {
      $sHeader = '<h1>Ah, content is king.</h1>';
      $sContent = strip_tags(stripslashes($_POST['elm1']),$allowedTags);
    } else {
      $sHeader = '<h1>Nothing submitted yet</h1>';
      $sContent = '';
    }

    $max_nbr_players = count($players_id)-1;
    // if vars aren't set, set them as empty.
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
        if (!isset($_POST['rank'.$i])) $_POST['rank'.$i] = '';
    }
    
    /////////////////
    /// MAIN FORM ///
    /////////////////
    $text .= '<form name="myform" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'?eventid='.$eventid.'" method="post" onsubmit="get_ranks('.$nbr_teams.');">';
    
    // TABLE - Player/Teams Add/Remove
    //----------------------------------
    $text .= '<table id="matchresult" >';    
    $text .= '<tr><input type="hidden" name="matchreport" value="1"></tr>';
    // Players
    $text .= '<tr><td colspan="1">Nbr Players: '.$nbr_players.'</td>';
    $text .= '<td colspan="1"><input type="hidden" name="nbr_players" value="'.$_POST['nbr_players'].'">';
    // Add Player
    if ($nbr_players < $max_nbr_players)
    {
       $text .= '<input class="button" type="submit" value="Add Player" name="addPlayer"></td>';
    }
    else
    {
       $text .= '<input class="button" type="submit" value="Add Player" name="addPlayer" DISABLED></td>';
    }    
    // Remove Player
    if ($nbr_players>2)
    {
       $text .= '<td colspan="1"><input class="button" type="submit" value="Remove Player" name="removePlayer"></td>';
    }
    else
    {
       $text .= '<td colspan="1"><input class="button" type="submit" value="Remove Player" name="removePlayer" DISABLED></td>';
    }
    
    // Teams
    $text .= '<tr><td colspan="1">Nbr Teams: '.$nbr_teams.'</td>';
    $text .= '<td colspan="1"><input type="hidden" name="nbr_teams" value="'.$_POST['nbr_teams'].'">';
    // Add Team
    if ($nbr_teams<$nbr_players)
    {
       $text .= '<input class="button" type="submit" value="Add Team" name="addTeam"></td>';
    }
    else
    {
       $text .= '<input class="button" type="submit" value="Add Team" name="addTeam" DISABLED></td>';
    }
    // Remove Team
    if ($nbr_teams>2)
    {
       $text .= '<td colspan="1"><input class="button" type="submit" value="Remove Team" name="removeTeam"></td>';
    }
    else
    {
       $text .= '<td colspan="1"><input class="button" type="submit" value="Remove Team" name="removeTeam" DISABLED></td>';
    }
    $text .= "</tr>";
    $text .= '</table>';

    //$text .= '<p><input class="inspector" type="button" value="Inspect" onclick="junkdrawer.inspectListOrder(\'matchresultlist\')"/></p>';
    $text .= "<br /><br /><br />";
   
    // TABLE - Players/Teams Selection
    //----------------------------------
    $text .= '<table id="matchresult" style="float:left;" >';
    for($i=1;$i<=$nbr_players;$i++)
    {
       $text .= '<tr><td>Player #'.$i.':</td>';
       
       $text .= '<td><select name="player'.$i.'">';
       for($j=1;$j <= $max_nbr_players+1;$j++)
       {
          $text .= '<option value="'.$players_id[($j-1)].'"';
          if (strtolower($_POST['player'.$i]) == strtolower($players_id[($j-1)])) $text .= ' selected="selected"';
          $text .= '>'.$players_name[($j-1)].'</option>';
       }
       $text .= '</select></td>';

       $text .= '<td><select name="team'.$i.'">';
       for($j=1;$j<=$nbr_teams;$j++)
       {
          $text .= '<option value="Team #'.$j.'"';
          if (strtolower($_POST['team'.$i]) == 'team #'.$j) $text .= ' selected="selected"';
          $text .= '>Team #'.$j.'</option>';
       }
       $text .= '</select></td>';
      $text .= '</tr>';
    }
    for($i=1;$i<=$nbr_teams;$i++)
    {
       $text .= '<input type="hidden" name="rank'.$i.'" value="0">';
    }
    $text .= '</table>';

    // TABLE - Teams Ranks
    //---------------------
    $text .= '<table id="matchresult" style="float:left;">';
    for($i=1;$i<=$nbr_teams;$i++)
    {
       $text .= '<tr><td>Rank #'.$i.':</td>';
       $text .= '</tr>';
    }    
    $text .= '</table>';

    // TABLE - Teams Rank Selection
    //----------------------------------
    $text .= '<table id="matchresult" style="float:left;">';
    $text .= '<td>';
    $text .= '<ul id="matchresultlist" class="boxy">';
           for($j=1;$j<=$nbr_teams;$j++)
           {
    $text .= '<li>Team #'.$j.'</li>';
    }
    $text .= '</ul>';
    $text .= '</td>';
    $text .= '</table>';

    $text .= '<br style="clear:both;">';
    $text .= '<br /><br />';
    $text .= '<p class="centered">';
    $text .= 'Your comments<br />';
    $text .= '<textarea id="elm1" name="elm1" cols="70" rows="20">'.$sContent.'</textarea>';
    $text .= '</p>';
    $text .= '<hr>';
    $text .= '<input type="hidden" name="reported_by" value="'.$reported_by.'">';
    $text .= '<input class="button" type="submit" value="Submit Match" name="submit">';    
    $text .= '<br /><br />';
    $text .= '</form>';
}

?>
