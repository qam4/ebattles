<?php


function brackets($type, $nbrPlayers = 16, $teams, &$results = '') {

/*
$teams = array(
'QamFour',
'Artosis',
'TLO',
'LiquidHuk',
'WISPEEL',
'CrunCher',
'Player7',
'Player8',
'Player9',
'Player10',
'Player11',
);
*/
	$nbrTeams=count($teams);

	switch ($type)
	{
		default:
		$file = 'include/brackets/se-'.$nbrPlayers.'.txt';
		break;
	}
	$matchups = unserialize(implode('',file($file)));
	$nbrRounds = count($matchups);

	if ($result = '')
	{
		$results = $matchups;
		init_results($results);
	}

	/* */
	$brackets = array ();
	$content= array();
	// Initialize grid
	for ($row = 1; $row <= 2*$nbrPlayers; $row ++){
		for ($column = 1; $column <= $nbrRounds; $column++){
			$brackets[$row][2*$column-1] = '<td></td>';
			$brackets[$row][2*$column] = '<td class="grid border-none"></td>';
		}
	}

	$rowspan = 1;
	for ($round = 1; $round <= $nbrRounds; $round++){
		$nbrMatchups = count($matchups[$round]);
		if ($round == 1) {
			/* Round 1 */
			for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
				$teamTop    = substr($matchups[$round][$matchup][0],1) - 1;
				$teamBottom = substr($matchups[$round][$matchup][1],1) - 1;

				$teamTopName = '';
				if ($teamTop<$nbrTeams){
					$teamTopName = $teams[$teamTop]['Name'];
				}
				else
				{
					$results[$round][$matchup] = 'bye';
				}
				$teamBottomName = '';
				if ($teamBottom<$nbrTeams){
					$teamBottomName = $teams[$teamBottom]['Name'];
				}
				else
				{
					$results[$round][$matchup] = 'bye';
				}

				if(($teamTopName!='')&&($teamBottomName!='')){
					if ($results[$round][$matchup] == 'top') {
						$brackets[$matchup*4-3][2*$round-1] = '<td><div class="container winner"><div class="player"><img src="images/ranks/a1.jpg" style="vertical-align:middle"/>'.$teamTopName.'</div><div class="wins">W</div></div></td>';
						$brackets[$matchup*4-1][2*$round-1] = '<td><div class="container loser"><div class="player"><img src="images/ranks/d3.jpg" style="vertical-align:middle"/>'.$teamBottomName.'</div><div class="wins">L</div></div></td>';
					}
					else if ($results[$round][$matchup] == 'bottom') {
						$brackets[$matchup*4-3][2*$round-1] = '<td><div class="container loser"><div class="player"><img src="images/ranks/a1.jpg" style="vertical-align:middle"/>'.$teamTopName.'</div><div class="wins">L</div></div></td>';
						$brackets[$matchup*4-1][2*$round-1] = '<td><div class="container winner"><div class="player"><img src="images/ranks/d3.jpg" style="vertical-align:middle"/>'.$teamBottomName.'</div><div class="wins">W</div></div></td>';
					}
					else {
						$brackets[$matchup*4-3][2*$round-1] = '<td><div class="container"><div class="player"><img src="images/ranks/a1.jpg" style="vertical-align:middle"/>'.$teamTopName.'</div></div></td>';
						$brackets[$matchup*4-1][2*$round-1] = '<td><div class="container"><div class="player"><img src="images/ranks/d3.jpg" style="vertical-align:middle"/>'.$teamBottomName.'</div></div></td>';
					}
					$brackets[$matchup*4-2][2*$round-1] = '<td rowspan='.$rowspan.' class="match-details" title="'.'M'.$round.','.$matchup.'"></td>';
				}

				$content[$round][$matchup][0] = $teamTopName;
				$matchupsRows[$round][$matchup][0] = $matchup*4-3;
				$content[$round][$matchup][1] = $teamBottomName;
				$matchupsRows[$round][$matchup][1] = $matchup*4-1;
			}

		}
		else if ($round < $nbrRounds)
		{
			for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
				for($match = 0; $match < 2; $match++){
					$matchupString = $matchups[$round][$matchup][$match];
					if ($matchupString[0]='W') {
						$matchupArray = explode(',',substr($matchupString,1));
						$matchupRound = $matchupArray[0];
						$matchupMatchup = $matchupArray[1];

						$result = $results[$matchupRound][$matchupMatchup];
						$test = $result;

						$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
						$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
						$row = ($rowBottom - $rowTop)/2 + $rowTop;

						if($result != 'bye'){
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

						$matchupsRows[$round][$matchup][$match] = $row;
						if (($result == 'top')||($result == 'bye')) {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
						}
						else if ($result == 'bottom') {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
						}
						else{
							$content[$round][$matchup][$match] = 'not played';
						}
					}
				}
				if (($content[$round][$matchup][0]!='')&&($content[$round][$matchup][1]!='')) {
					if($content[$round][$matchup][0]!='not played') {
						$contentTop = '<img src="images/ranks/a1.jpg" style="vertical-align:middle"/>'.$content[$round][$matchup][0];
					}
					else {
						$contentTop = '&nbsp';
					}
					if($content[$round][$matchup][1]!='not played') {
						$contentBottom = '<img src="images/ranks/d3.jpg" style="vertical-align:middle"/>'.$content[$round][$matchup][1];
					}
					else {
						$contentBottom = '&nbsp';
					}


					if ($results[$round][$matchup] == 'top') {
						$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = '<td><div class="container winner"><div class="player">'.$contentTop.'</div><div class="wins">W</div></div></td>';
						$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = '<td><div class="container loser"><div class="player">'.$contentBottom.'</div><div class="wins">L</div></div></td>';
					}
					else if ($results[$round][$matchup] == 'bottom') {
						$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = '<td><div class="container loser"><div class="player">'.$contentTop.'</div><div class="wins">L</div></div></td>';
						$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = '<td><div class="container winner"><div class="player">'.$contentBottom.'</div><div class="wins">W</div></div></td>';
					}
					else {
						$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = '<td><div class="container"><div class="player">'.$contentTop.'</div></div></td>';
						$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = '<td><div class="container"><div class="player">'.$contentBottom.'</div></div></td>';
					}
					$brackets[$matchupsRows[$round][$matchup][0]+1][2*$round-1] = '<td rowspan='.$rowspan.' class="match-details" title="'.'M'.$round.','.$matchup.'"></div></td>';
				}
				if (($content[$round][$matchup][0]=='')||($content[$round][$matchup][1]=='')) {
					$results[$round][$matchup] = 'bye';
				}
			}
		}
		else
		{
			/* Last round, no match */
			for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
				$match = 0;
				$matchupString = $matchups[$round][$matchup][$match];
				if ($matchupString[$match]='W') {

					$matchupArray = explode(',',substr($matchupString,1));
					$matchupRound = $matchupArray[0];
					$matchupMatchup = $matchupArray[1];

					$result = $results[$matchupRound][$matchupMatchup];

					$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
					$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
					$row = ($rowBottom - $rowTop)/2 + $rowTop;

					if($result != 'bye'){
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
					if (($result == 'top')||($result == 'bye')) {
						$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
					}
					else if ($result = 'bottom'){
						$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
					}


					if($content[$round][$matchup][0]!='not played') {
						$contentFinal = '<img src="images/ranks/a1.jpg" style="vertical-align:middle"/>'.$content[$round][$matchup][0];
					}
					else
					{
						$contentFinal = '&nbsp';
					}
					$brackets[$row][2*$round-1] = '<td><div class="container"><div class="player">'.$contentFinal.'</div></div></td>';
				}
			}
		}
		$rowspan = 2*$rowspan + 1;
	}

	$bracket_html = '<div id="panel_brackets">';
	$bracket_html .= '<div id="brackets_frame">';
	//need jqueryui: $bracket_html .= '<div id="brackets" class="ui-draggable">';
	$bracket_html .= '<table class="brackets">';
	for ($row = 1; $row <= $nbrPlayers*2; $row ++){
		$bracket_html .= '<tr>';
		for ($column = 1; $column <= 2*$nbrRounds; $column++){
			$bracket_html .= $brackets[$row][$column];
		}
		$bracket_html .= '</tr>';
	}
	$bracket_html .= '</table>';
	//$bracket_html .= '</div>'; // brackets
	$bracket_html .= '</div>'; // brackets_frame
	$bracket_html .= '<div class="clearer"></div>';
	$bracket_html .= '</div>'; // panel-brackets

	/**/
	return $bracket_html;

}

function init_results(&$results)
{
	foreach ($results as $matchups) {
		foreach ($matchups as $matchup) {
			$matchup = '';
		}
	}
}

?>
