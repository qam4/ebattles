<?php

//require_once(events.php);
require_once("brackets.php");

echo '<html>';
echo '<head>';
echo '<link rel="stylesheet" type="text/css" href="bracket.css" />';
echo '</head>';
echo '<body>';

// Test bracket
generate_brackets();

/*
$teams = array();
for ($i=0; $i<$nbrPlayers; $i++)
{
$teams[$i]['Name']='Player'.($i+1);
}
*/

$teams = array(
0 => array('Name' => 'Player1'),
1 => array('Name' => 'Player2'),
2 => array('Name' => 'Player3'),
3 => array('Name' => 'Player4'),
4 => array('Name' => 'Player5'),
5 => array('Name' => 'Player6'),
6 => array('Name' => 'Player7')
);


$nbrTeams=count($teams);
for ($i = 0; $i<$nbrTeams; $i++)
{
	$teams[$i]['loss'] = 0;
}
echo "nbrTeams=$nbrTeams<br>";

$nbrRounds = count($matchups);
$results = array();
/*
$results = array(
1=> array(
2 => array( 'winner' => 'top'),
3 => array( 'winner' => 'top'),
4 => array( 'winner' => 'top')
),
2=> array(
1 => array( 'winner' => 'top'),
2 => array( 'winner' => 'top'),
3 => array( 'winner' => 'top'),
4 => array( 'winner' => 'top'),
8 => array( 'winner' => 'top')
),
3=> array(
1 => array( 'winner' => 'top'),
2 => array( 'winner' => 'top'),
4 => array( 'winner' => 'top')
),
4=> array(
1 => array( 'winner' => 'top'),
2 => array( 'winner' => 'bottom'),
),
5=> array(
1 => array( 'winner' => 'top'),
),
6=> array(
));
*/

$brackets = array ();
$content= array();
// Initialize grid
for ($row = 1; $row <= 2*$nbrPlayers; $row ++){
	for ($column = 1; $column <= $nbrRounds; $column++){
		$brackets[$row][2*$column-1] = '<td class="grid empty"></td>';
		$brackets[$row][2*$column] = '<td class="grid border-none"></td>';
	}
}

$rowspan = 1;
for ($round = 1; $round <= $nbrRounds; $round++){
	$nbrMatchups = count($matchups[$round]);
	$rounds[$round]['nbrMatchups'] = 0;

	if ($round < $nbrRounds)
	{
		for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
			if (!isset($results[$round][$matchup]['winner'])) $results[$round][$matchup]['winner'] = 'not played';
			if (!isset($results[$round][$matchup]['bye'])) $results[$round][$matchup]['bye'] = false;
			for($match = 0; $match < 2; $match++){
				$matchupString = $matchups[$round][$matchup][$match];
				$content[$round][$matchup][$match] = ($matchupString == '') ? 'E' : $matchupString;
				if ($matchupString == '')
				{
					$row = findRow($round, $matchup, $match);
					$matchupsRows[$round][$matchup][$match] = $row;
				} else
				{
					if ($matchupString[0]=='T') {
						$row = findRow($round, $matchup, $match);
						$matchupsRows[$round][$matchup][$match] = $row;
						$team = substr($matchupString,1);
						if ($team > $nbrTeams){
							$content[$round][$matchup][$match] = 'E';
						}
					}
					if ($matchupString[0]=='W') {
						$matchupArray = explode(',',substr($matchupString,1));
						$matchupRound = $matchupArray[0];
						$matchupMatchup = $matchupArray[1];

						// Get result of matchup
						$winner = $results[$matchupRound][$matchupMatchup]['winner'];
						$bye = $results[$matchupRound][$matchupMatchup]['bye'];

						$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
						$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
						$row = ($rowBottom - $rowTop)/2 + $rowTop;

						// If result is not a bye, we draw the grid
						if($bye != true){
							$brackets[$rowTop][2*$round-2] = '<td class="grid border-top"></td>';
							$brackets[$rowBottom][2*$round-2] = '<td class="grid border-bottom"></td>';
							for ($i = $rowTop+1; $i < $rowBottom; $i++){
								$brackets[$i][2*$round-2] = '<td class="grid border-vertical"></td>';
							}
							for ($i = $rowTop+2; $i < $rowBottom; $i++){
								$brackets[$i][2*$round-3] = '';
							}
							$brackets[$row][2*$round-2] = '<td class="grid border-middle"></td>';
						}

						$matchupsRows[$round][$matchup][$match] = $row;
						if ($winner == 'top') {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
						}
						else if ($winner == 'bottom') {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
						}
					}
					if (($matchupString[0]=='L')||($matchupString[0]=='P')) {
						$matchupArray = explode(',',substr($matchupString,1));
						$matchupRound = $matchupArray[0];
						$matchupMatchup = $matchupArray[1];
						
						// Get result of matchup
						$winner = $results[$matchupRound][$matchupMatchup]['winner'];
						$bye = $results[$matchupRound][$matchupMatchup]['bye'];

						$row = findRow($round, $matchup, $match);

						$matchupsRows[$round][$matchup][$match] = $row;
						if ($winner == 'top') {
							$loser = $content[$matchupRound][$matchupMatchup][1];
							if ($loser[0] == 'T')
							{
								$team = substr($loser,1);
								//echo "L2: $team,".$teams[$team-1]['loss'].'<br>';
								if ($teams[$team-1]['loss'] > 1)
								{
									$content[$round][$matchup][$match] = 'N';
								}
								else
								{
									$content[$round][$matchup][$match] = $loser;
								}
							}
							else
							{
								$content[$round][$matchup][$match] = 'E';
							}
						}
						else if ($winner == 'bottom') {
							$loser = $content[$matchupRound][$matchupMatchup][0];
							if ($loser[0] == 'T')
							{
								$team = substr($loser,1);
								//echo "L2: $team,".$teams[$team-1]['loss'].'<br>';
								if ($teams[$team-1]['loss'] > 1)
								{
									$content[$round][$matchup][$match] = 'N';
								}
								else
								{
									$content[$round][$matchup][$match] = $loser;
								}
							}
							else
							{
								$content[$round][$matchup][$match] = 'E';
							}
						}
						else{
						}
						//echo "L$matchupRound,$matchupMatchup: ".$content[$round][$matchup][$match]."<br>";
					}
				}
				
				switch ($content[$round][$matchup][$match])
				{
					case 'E':
					$results[$round][$matchup]['winner'] = ($match==0) ? 'bottom' : 'top';
					$results[$round][$matchup]['bye'] = true;
					break;
					case 'N':
					$results[$round][$matchup]['winner'] = ($match==0) ? 'bottom' : 'top';
					break;
				}
			}

			$topWins = 0; //$results[$round][$matchup]['topWins'];
			$bottomWins = 0; //$results[$round][$matchup]['bottomWins'];
			if($topWins > $bottomWins)
			{
				$topWins .= '+';
				$bottomWins .= '-';
			}
			if($topWins < $bottomWins)
			{
				$topWins .= '-';
				$bottomWins .= '+';
			}

			if (($content[$round][$matchup][0]!='E')&&($content[$round][$matchup][1]!='E')) {
				if ($results[$round][$matchup]['winner'] == 'top') {
					$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], $topWins, 'winner');
					$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], $bottomWins, 'loser');
					$loser = $content[$round][$matchup][1];
					if ($loser[0] == 'T')
					{
						$team = substr($loser,1);
						$teams[$team-1]['loss'] += 1;
						//echo "L1: $team,".$teams[$team-1]['loss'].'<br>';
					}
				} else if ($results[$round][$matchup]['winner'] == 'bottom') {
					$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], $topWins, 'loser');
					$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], $bottomWins, 'winner');
					$loser = $content[$round][$matchup][0];
					if ($loser[0] == 'T')
					{
						$team = substr($loser,1);
						$teams[$team-1]['loss'] += 1;
						//echo "L1: $team,".$teams[$team-1]['loss'].'<br>';
					}
				} else {
					$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], $topWins);
					$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], $bottomWins);
				}
				$brackets[$matchupsRows[$round][$matchup][0]+1][2*$round-1] = '<td rowspan="'.$rowspan.'" class="match-details" title="'.'Matchup '.$round.','.$matchup.'"></td>';
				$rounds[$round]['nbrMatchups']++;
			}
			/*
			echo 'M'.$round.','.$matchup.':<br>';
			echo '- matchup: top='.$matchups[$round][$matchup][0].', bottom='.$matchups[$round][$matchup][1].'<br>';
			echo '- content: top='.$content[$round][$matchup][0].', bottom='.$content[$round][$matchup][1].'<br>';
			echo '- winner='.$results[$round][$matchup]['winner'].', bye='.$results[$round][$matchup]['bye'].'<br>';
			*/
		}
	}
	else
	{
		/* Last round, no match */
		for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
			if (!isset($results[$round][$matchup]['winner'])) $results[$round][$matchup]['winner'] = '';
			if (!isset($results[$round][$matchup]['bye'])) $results[$round][$matchup]['bye'] = false;
			$match = 0;
			$matchupString = $matchups[$round][$matchup][$match];
			$content[$round][$matchup][$match] = ($matchupString == '') ? 'E' : $matchupString;
			if ($matchupString[$match]='W') {

				$matchupArray = explode(',',substr($matchupString,1));
				$matchupRound = $matchupArray[0];
				$matchupMatchup = $matchupArray[1];

				$winner = $results[$matchupRound][$matchupMatchup]['winner'];
				$bye = $results[$matchupRound][$matchupMatchup]['bye'];

				$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
				$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
				$row = ($rowBottom - $rowTop)/2 + $rowTop;

				if($bye != 'bye'){
					$brackets[$rowTop][2*$round-2] = '<td class="grid border-top"></td>';
					$brackets[$rowBottom][2*$round-2] = '<td class="grid border-bottom"></td>';
					for ($i = $rowTop+1; $i < $rowBottom; $i++){
						$brackets[$i][2*$round-2] = '<td class="grid border-vertical"></td>';
					}
					for ($i = $rowTop+2; $i <$rowBottom; $i++){
						$brackets[$i][2*$round-3] = '';
					}
					$brackets[$row][2*$round-2] = '<td class="grid border-middle"></td>';
				}

				$matchupsRows[$round][$matchup][$match] = $rowTop;
				if ($winner == 'top') {
					$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
				} else if ($winner == 'bottom') {
					$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
				}

				$topWins = 0; //$results[$round][$matchup]['topWins'];
				$bottomWins = 0; //$results[$round][$matchup]['bottomWins'];
				if($topWins > $bottomWins)
				{
					$topWins .= '+';
					$bottomWins .= '-';
				}
				if($topWins < $bottomWins)
				{
					$topWins .= '-';
					$bottomWins .= '+';
				}
				if ($content[$round][$matchup][0] != 'E') {
					$brackets[$row][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][$match], $topWins, 'victor');
				} else {
					$brackets[$row][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][$match], $bottomWins);
				}
			}
		}
	}
	$rowspan = 2*$rowspan + 1;
}

$bracket_html = '<div id="panel_brackets">';
$bracket_html .= '<div id="brackets_frame">';
//$bracket_html .= '<div id="brackets">';
$bracket_html .= '<table class="brackets">';

/*
$bracket_html .= '<thead><tr>';
for ($i = 1; $i < $nbrRounds; $i++) {
if ($rounds[$i]['nbrMatchups'] != 0)
{
$bracket_html .= '<th colspan="2" title="'.EB_EVENTM_L146.' '.$rounds[$i]['BestOf'].'">'.$rounds[$i]['Title'].'</th>';
}
else
{
$bracket_html .= '<th colspan="2"></th>';
}
}
$bracket_html .= '</tr></thead>';
*/
$bracket_html .= '<tbody>';
for ($row = 1; $row <= $nbrPlayers*2; $row ++){
	$bracket_html .= '<tr>';
	for ($column = 1; $column <= 2*$nbrRounds; $column++){
		$bracket_html .= $brackets[$row][$column];
	}
	$bracket_html .= '</tr>';
}
$bracket_html .= '</tbody>';
$bracket_html .= '</table>';
//$bracket_html .= '</div>'; // brackets
$bracket_html .= '</div>'; // brackets_frame
$bracket_html .= '<div class="clearer"></div>';
$bracket_html .= '</div>'; // panel-brackets

echo $bracket_html;
/**/



echo '</body>';
echo '</html>';

function seed($depth, $player)
{
	if($depth == 0)
	{
		return 1;
	}
	else
	{
		if ($player%2)
		{
			// impair
			return seed($depth-1, intval(($player+1)/2));
		}
		else
		{
			return pow(2,$depth)+1-seed($depth-1, intval(($player+1)/2));
		}
	}
}

function html_bracket_team_cell($teams, $content, $score, $container_class='') {
	
	//echo "html_bracket_team_cell: $teams, $content, $score, $container_class<br>";
	$text = '<td><div class="container '.$container_class.'">';
	if ($container_class=='victor')
	{
		$victor_image = 'images/awards/trophy_gold.png';
		$victor_str = '<img src="'.$victor_image.'" style="vertical-align:middle" alt=""/>';
	}
	$score_class = 'score';
	if (preg_match("/^\d+\+$/",$score))
	{
		$score_class = 'score win';
	}
	if (preg_match("/^\d+\-$/",$score))
	{
		$score_class = 'score loss';
	}
	$score = preg_replace("/[\+\-]/","", $score);
	switch ($content[0]) {
		case 'N':
		$text .= 'Not needed';
		break;
		case 'W':
		$text .= '&nbsp;';
		break;
		case 'L':
		$teams = substr($content,1);
		$text .= 'Loser of '.$teams;
		break;
		case 'P':
		$teams = substr($content,1);
		$text .= 'Loser of '.$teams. ' (if necessary)';
		break;
		case 'T':
		$team = substr($content,1);
		$team_name = $teams[$team-1]['Name'];
		$team_image = 'images/ranks/a1.jpg';
		$text .= '<table><tbody><tr>';

		$text .= '<td class="player"><div class="player">';
		//$text .= '<img src="'.$team_image.'" style="vertical-align:middle" alt=""/>';
		$text .= $team_name;
		$text .= '</div></td>';
		$text .= '<td class="wins">';
		switch($container_class)
		{
			case 'winner':
			//$text .= '<div class="wins">W</div>';
			break;
			case 'loser':
			//$text .= '<div class="wins">L</div>';
			break;
			case 'victor':
			$text .= '<div class="wins">'.$victor_str.'</div>';
			break;
		}
		$text .= '</td>';
		$text .= '<td class="'.$score_class.'"><div class="'.$score_class.'">';
		$text .= $score;
		$text .= '</div></td>';
		$text .= '</tr></tbody></table>';
		break;
		default:
		break;
	}

	$text .= '</div></td>';
	return $text;
}

function findRow($round, $matchup, $match)
{
	if ($round==1)
	{
		$row = $matchup*4-3+2*$match;
	}
	else
	{
		if($match == 0)
		{
			$rowTop    = findRow($round-1, 2*$matchup-1, 0);
			$rowBottom = findRow($round-1, 2*$matchup-1, 1);
		}
		else
		{
			$rowTop    = findRow($round-1, 2*$matchup, 0);
			$rowBottom = findRow($round-1, 2*$matchup, 1);
		}
		$row = ($rowBottom - $rowTop)/2 + $rowTop;
	}
	return $row;
}

?>
