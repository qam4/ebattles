<?php
// function to output form and hold previously entered values.

function user_form($players_name, $players_nickname, $eventid) {
    global $session;

    $reported_by = $session->username;
    $allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
    $allowedTags.='<li><ol><ul><span><div><br /><ins><del>';
    if(isset($_POST['elm1'])) {
      $sHeader = '<h1>Ah, content is king.</h1>';
      $sContent = strip_tags(stripslashes($_POST['elm1']),$allowedTags);
    } else {
      $sHeader = '<h1>Nothing submitted yet</h1>';
      $sContent = '';
    }

    //$players = array('-- select --', 'fred', 'fab', 'nico', 'test', 'test', 'test', 'test', 'test', 'test', 'test', 'test');
    $max_nbr_players = count($players_name)-1;
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
        if (!isset($_POST['player'.$i])) $_POST['player'.$i] = $players_name[0];
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
    echo '<form name="myform" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'?eventid='.$eventid.'" method="post" onsubmit="get_ranks('.$nbr_teams.');">';
    
    // TABLE - Player/Teams Add/Remove
    //----------------------------------
    echo '<table id="matchresult" >';    
    echo '<tr><input type="hidden" name="matchreport" value="1"></tr>';
    // Players
    echo '<tr><td colspan="1">Nbr Players: '.$nbr_players.'</td>';
    echo '<td colspan="1"><input type="hidden" name="nbr_players" value="'.$_POST['nbr_players'].'">';
    // Add Player
    if ($nbr_players < $max_nbr_players)
    {
       echo '<input type="submit" value="Add Player" name="addPlayer"></td>';
    }
    else
    {
       echo '<input type="submit" value="Add Player" name="addPlayer" DISABLED></td>';
    }    
    // Remove Player
    if ($nbr_players>2)
    {
       echo '<td colspan="1"><input type="submit" value="Remove Player" name="removePlayer"></td>';
    }
    else
    {
       echo '<td colspan="1"><input type="submit" value="Remove Player" name="removePlayer" DISABLED></td>';
    }
    
    // Teams
    echo '<tr><td colspan="1">Nbr Teams: '.$nbr_teams.'</td>';
    echo '<td colspan="1"><input type="hidden" name="nbr_teams" value="'.$_POST['nbr_teams'].'">';
    // Add Team
    if ($nbr_teams<$nbr_players)
    {
       echo '<input type="submit" value="Add Team" name="addTeam"></td>';
    }
    else
    {
       echo '<input type="submit" value="Add Team" name="addTeam" DISABLED></td>';
    }
    // Remove Team
    if ($nbr_teams>2)
    {
       echo '<td colspan="1"><input type="submit" value="Remove Team" name="removeTeam"></td>';
    }
    else
    {
       echo '<td colspan="1"><input type="submit" value="Remove Team" name="removeTeam" DISABLED></td>';
    }
    echo "</tr>";
    echo '</table>';

    //echo '<p><input class="inspector" type="button" value="Inspect" onclick="junkdrawer.inspectListOrder(\'matchresultlist\')"/></p>';
    echo "<br /><br /><br />";
   
    // TABLE - Players/Teams Selection
    //----------------------------------
    echo '<table id="matchresult" style="float:left;" >';
    for($i=1;$i<=$nbr_players;$i++)
    {
       echo '<tr><td>Player #'.$i.':</td>';
       
       echo '<td><select name="player'.$i.'">';
       for($j=1;$j <= $max_nbr_players+1;$j++)
       {
          echo '<option value="'.$players_name[($j-1)].'"';
          if (strtolower($_POST['player'.$i]) == strtolower($players_name[($j-1)])) echo ' selected="selected"';
          echo '>'.$players_nickname[($j-1)].'</option>';
       }
       echo '</select></td>';

       echo '<td><select name="team'.$i.'">';
       for($j=1;$j<=$nbr_teams;$j++)
       {
          echo '<option value="Team #'.$j.'"';
          if (strtolower($_POST['team'.$i]) == 'team #'.$j) echo ' selected="selected"';
          echo '>Team #'.$j.'</option>';
       }
       echo '</select></td>';
      echo '</tr>';
    }
    for($i=1;$i<=$nbr_teams;$i++)
    {
       echo '<input type="hidden" name="rank'.$i.'" value="0">';
    }
    echo '</table>';

    // TABLE - Teams Ranks
    //---------------------
    echo '<table id="matchresult" style="float:left;">';
    for($i=1;$i<=$nbr_teams;$i++)
    {
       echo '<tr><td>Rank #'.$i.':</td>';
       echo '</tr>';
    }    
    echo '</table>';

    // TABLE - Teams Rank Selection
    //----------------------------------
    echo '<table id="matchresult" style="float:left;">';
    echo '<td>';
    echo '<ul id="matchresultlist" class="boxy">';
           for($j=1;$j<=$nbr_teams;$j++)
           {
    echo '<li>Team #'.$j.'</li>';
    }
    echo '</ul>';
    echo '</td>';
    echo '</table>';

    echo '<br style="clear:both;">';
    echo '<br /><br />';
    echo '<p class="centered">';
    echo 'Your comments<br />';
    echo '<textarea id="elm1" name="elm1" cols="70" rows="20">'.$sContent.'</textarea>';
    echo '</p>';
    echo '<hr>';
    echo '<input type="hidden" name="reported_by" value="'.$reported_by.'">';
    echo '<input type="submit" value="Submit Match" name="submit">';    
    echo '<br /><br />';
    echo '</form>';
}

?>
