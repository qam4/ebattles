<?php

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
		case 'E':
		$text .= '&nbsp;';
		break;
		case 'N':
		$text .= 'Not needed';
		break;
		case 'W':
		$text .= '&nbsp;';
		break;
		case 'L':
		$teams = substr($content,1);
		$text .= EB_EVENT_L85.' '.$teams;
		break;
		case 'P':
		$teams = substr($content,1);
		$text .= EB_EVENT_L85.' '.$teams.' '.EB_EVENT_L86;
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
/* Helper function to generate brackets
*/
function generate_brackets($depth, $type, $serialize=true, $display_bracket_array=true)
{
	/*
	$depth=5;	// depths (number of rounds + 1)
	$type = 'de-4';
	$serialize = TRUE;
	$display_bracket_array = FALSE;
	*/

	$nbrPlayers = pow(2,$depth);
	if($display_bracket_array)
	{
		echo "1=>array(<br>";
		for($m=1;$m <= pow(2,$depth)/2;$m++)
		{
			echo "$m => array('T".seed($depth,$m*2-1)."', 'T".seed($depth,$m*2)."'),<br>";
		}
		echo "),<br>";
		for($l=0;$l < $depth-1;$l++)
		{
			echo ($l+2)."=>array(<br>";
			$n=1;
			for($m=1;$m <= pow(2,$depth-$l-1)/2;$m++)
			{
				echo "$m => array('W".($l+1).",".($n)."', 'W".($l+1).",".($n+1)."'),<br>";
				$n+=2;
			}
			echo "),<br>";
		}
		echo ($depth+1)."=>array(<br>";
		echo "1 => array('W".($depth).",1'),<br>";
		echo ")<br>";
	};


	$file = $type.'.txt';
	if ($serialize){
		switch ($type)
		{
			case 'se-128':
			$matchups = array(
			1=> array(
			1 => array('T1', 'T128'),
			2 => array('T64', 'T65'),
			3 => array('T32', 'T97'),
			4 => array('T33', 'T96'),
			5 => array('T16', 'T113'),
			6 => array('T49', 'T80'),
			7 => array('T17', 'T112'),
			8 => array('T48', 'T81'),
			9 => array('T8', 'T121'),
			10 => array('T57', 'T72'),
			11 => array('T25', 'T104'),
			12 => array('T40', 'T89'),
			13 => array('T9', 'T120'),
			14 => array('T56', 'T73'),
			15 => array('T24', 'T105'),
			16 => array('T41', 'T88'),
			17 => array('T4', 'T125'),
			18 => array('T61', 'T68'),
			19 => array('T29', 'T100'),
			20 => array('T36', 'T93'),
			21 => array('T13', 'T116'),
			22 => array('T52', 'T77'),
			23 => array('T20', 'T109'),
			24 => array('T45', 'T84'),
			25 => array('T5', 'T124'),
			26 => array('T60', 'T69'),
			27 => array('T28', 'T101'),
			28 => array('T37', 'T92'),
			29 => array('T12', 'T117'),
			30 => array('T53', 'T76'),
			31 => array('T21', 'T108'),
			32 => array('T44', 'T85'),
			33 => array('T2', 'T127'),
			34 => array('T63', 'T66'),
			35 => array('T31', 'T98'),
			36 => array('T34', 'T95'),
			37 => array('T15', 'T114'),
			38 => array('T50', 'T79'),
			39 => array('T18', 'T111'),
			40 => array('T47', 'T82'),
			41 => array('T7', 'T122'),
			42 => array('T58', 'T71'),
			43 => array('T26', 'T103'),
			44 => array('T39', 'T90'),
			45 => array('T10', 'T119'),
			46 => array('T55', 'T74'),
			47 => array('T23', 'T106'),
			48 => array('T42', 'T87'),
			49 => array('T3', 'T126'),
			50 => array('T62', 'T67'),
			51 => array('T30', 'T99'),
			52 => array('T35', 'T94'),
			53 => array('T14', 'T115'),
			54 => array('T51', 'T78'),
			55 => array('T19', 'T110'),
			56 => array('T46', 'T83'),
			57 => array('T6', 'T123'),
			58 => array('T59', 'T70'),
			59 => array('T27', 'T102'),
			60 => array('T38', 'T91'),
			61 => array('T11', 'T118'),
			62 => array('T54', 'T75'),
			63 => array('T22', 'T107'),
			64 => array('T43', 'T86')
			),
			2=> array(
			1 => array('W1,1', 'W1,2'),
			2 => array('W1,3', 'W1,4'),
			3 => array('W1,5', 'W1,6'),
			4 => array('W1,7', 'W1,8'),
			5 => array('W1,9', 'W1,10'),
			6 => array('W1,11', 'W1,12'),
			7 => array('W1,13', 'W1,14'),
			8 => array('W1,15', 'W1,16'),
			9 => array('W1,17', 'W1,18'),
			10 => array('W1,19', 'W1,20'),
			11 => array('W1,21', 'W1,22'),
			12 => array('W1,23', 'W1,24'),
			13 => array('W1,25', 'W1,26'),
			14 => array('W1,27', 'W1,28'),
			15 => array('W1,29', 'W1,30'),
			16 => array('W1,31', 'W1,32'),
			17 => array('W1,33', 'W1,34'),
			18 => array('W1,35', 'W1,36'),
			19 => array('W1,37', 'W1,38'),
			20 => array('W1,39', 'W1,40'),
			21 => array('W1,41', 'W1,42'),
			22 => array('W1,43', 'W1,44'),
			23 => array('W1,45', 'W1,46'),
			24 => array('W1,47', 'W1,48'),
			25 => array('W1,49', 'W1,50'),
			26 => array('W1,51', 'W1,52'),
			27 => array('W1,53', 'W1,54'),
			28 => array('W1,55', 'W1,56'),
			29 => array('W1,57', 'W1,58'),
			30 => array('W1,59', 'W1,60'),
			31 => array('W1,61', 'W1,62'),
			32 => array('W1,63', 'W1,64')
			),
			3=> array(
			1 => array('W2,1', 'W2,2'),
			2 => array('W2,3', 'W2,4'),
			3 => array('W2,5', 'W2,6'),
			4 => array('W2,7', 'W2,8'),
			5 => array('W2,9', 'W2,10'),
			6 => array('W2,11', 'W2,12'),
			7 => array('W2,13', 'W2,14'),
			8 => array('W2,15', 'W2,16'),
			9 => array('W2,17', 'W2,18'),
			10 => array('W2,19', 'W2,20'),
			11 => array('W2,21', 'W2,22'),
			12 => array('W2,23', 'W2,24'),
			13 => array('W2,25', 'W2,26'),
			14 => array('W2,27', 'W2,28'),
			15 => array('W2,29', 'W2,30'),
			16 => array('W2,31', 'W2,32')
			),
			4=> array(
			1 => array('W3,1', 'W3,2'),
			2 => array('W3,3', 'W3,4'),
			3 => array('W3,5', 'W3,6'),
			4 => array('W3,7', 'W3,8'),
			5 => array('W3,9', 'W3,10'),
			6 => array('W3,11', 'W3,12'),
			7 => array('W3,13', 'W3,14'),
			8 => array('W3,15', 'W3,16')
			),
			5=> array(
			1 => array('W4,1', 'W4,2'),
			2 => array('W4,3', 'W4,4'),
			3 => array('W4,5', 'W4,6'),
			4 => array('W4,7', 'W4,8')
			),
			6=> array(
			1 => array('W5,1', 'W5,2'),
			2 => array('W5,3', 'W5,4')
			),
			7=> array(
			1 => array('W6,1','W6,2')
			),
			8=> array(
			1 => array('W7,1')
			)
			);
			break;
			case 'se-64':
			$matchups = array(
			1=> array(
			1 => array('T1', 'T64'),
			2 => array('T32', 'T33'),
			3 => array('T16', 'T49'),
			4 => array('T17', 'T48'),
			5 => array('T8', 'T57'),
			6 => array('T25', 'T40'),
			7 => array('T9', 'T56'),
			8 => array('T24', 'T41'),
			9 => array('T4', 'T61'),
			10 => array('T29', 'T36'),
			11 => array('T13', 'T52'),
			12 => array('T20', 'T45'),
			13 => array('T5', 'T60'),
			14 => array('T28', 'T37'),
			15 => array('T12', 'T53'),
			16 => array('T21', 'T44'),
			17 => array('T2', 'T63'),
			18 => array('T31', 'T34'),
			19 => array('T15', 'T50'),
			20 => array('T18', 'T47'),
			21 => array('T7', 'T58'),
			22 => array('T26', 'T39'),
			23 => array('T10', 'T55'),
			24 => array('T23', 'T42'),
			25 => array('T3', 'T62'),
			26 => array('T30', 'T35'),
			27 => array('T14', 'T51'),
			28 => array('T19', 'T46'),
			29 => array('T6', 'T59'),
			30 => array('T27', 'T38'),
			31 => array('T11', 'T54'),
			32 => array('T22', 'T43')
			),
			2=> array(
			1 => array('W1,1', 'W1,2'),
			2 => array('W1,3', 'W1,4'),
			3 => array('W1,5', 'W1,6'),
			4 => array('W1,7', 'W1,8'),
			5 => array('W1,9', 'W1,10'),
			6 => array('W1,11', 'W1,12'),
			7 => array('W1,13', 'W1,14'),
			8 => array('W1,15', 'W1,16'),
			9 => array('W1,17', 'W1,18'),
			10 => array('W1,19', 'W1,20'),
			11 => array('W1,21', 'W1,22'),
			12 => array('W1,23', 'W1,24'),
			13 => array('W1,25', 'W1,26'),
			14 => array('W1,27', 'W1,28'),
			15 => array('W1,29', 'W1,30'),
			16 => array('W1,31', 'W1,32')
			),
			3=> array(
			1 => array('W2,1', 'W2,2'),
			2 => array('W2,3', 'W2,4'),
			3 => array('W2,5', 'W2,6'),
			4 => array('W2,7', 'W2,8'),
			5 => array('W2,9', 'W2,10'),
			6 => array('W2,11', 'W2,12'),
			7 => array('W2,13', 'W2,14'),
			8 => array('W2,15', 'W2,16')
			),
			4=> array(
			1 => array('W3,1', 'W3,2'),
			2 => array('W3,3', 'W3,4'),
			3 => array('W3,5', 'W3,6'),
			4 => array('W3,7', 'W3,8')
			),
			5=> array(
			1 => array('W4,1', 'W4,2'),
			2 => array('W4,3', 'W4,4')
			),
			6=> array(
			1 => array('W5,1','W5,2')
			),
			7=> array(
			1 => array('W6,1')
			)
			);
			break;
			case 'se-32':
			$matchups = array(
			1=> array(
			1 => array('T1', 'T32'),
			2 => array('T16', 'T17'),
			3 => array('T8', 'T25'),
			4 => array('T9', 'T24'),
			5 => array('T4', 'T29'),
			6 => array('T13', 'T20'),
			7 => array('T5', 'T28'),
			8 => array('T12', 'T21'),
			9 => array('T2', 'T31'),
			10 => array('T15', 'T18'),
			11 => array('T7', 'T26'),
			12 => array('T10', 'T23'),
			13 => array('T3', 'T30'),
			14 => array('T14', 'T19'),
			15 => array('T6', 'T27'),
			16 => array('T11', 'T22')
			),
			2=> array(
			1 => array('W1,1', 'W1,2'),
			2 => array('W1,3', 'W1,4'),
			3 => array('W1,5', 'W1,6'),
			4 => array('W1,7', 'W1,8'),
			5 => array('W1,9', 'W1,10'),
			6 => array('W1,11', 'W1,12'),
			7 => array('W1,13', 'W1,14'),
			8 => array('W1,15', 'W1,16')
			),
			3=> array(
			1 => array('W2,1', 'W2,2'),
			2 => array('W2,3', 'W2,4'),
			3 => array('W2,5', 'W2,6'),
			4 => array('W2,7', 'W2,8')
			),
			4=> array(
			1 => array('W3,1', 'W3,2'),
			2 => array('W3,3', 'W3,4')
			),
			5=> array(
			1 => array('W4,1', 'W4,2')
			),
			6=> array(
			1 => array('W5,1')
			)
			);
			break;
			case 'se-16':
			$matchups = array(
			1=> array(
			1 => array('T1', 'T16'),
			2 => array('T8', 'T9'),
			3 => array('T4', 'T13'),
			4 => array('T5', 'T12'),
			5 => array('T2', 'T15'),
			6 => array('T7', 'T10'),
			7 => array('T3', 'T14'),
			8 => array('T6', 'T11')
			),
			2=> array(
			1 => array('W1,1', 'W1,2'),
			2 => array('W1,3', 'W1,4'),
			3 => array('W1,5', 'W1,6'),
			4 => array('W1,7', 'W1,8'),
			),
			3=> array(
			1 => array('W2,1', 'W2,2'),
			2 => array('W2,3', 'W2,4'),
			),
			4=> array(
			1 => array('W3,1', 'W3,2'),
			),
			5=> array(
			1 => array('W4,1'),
			)
			);
			break;
			case 'se-8':
			$matchups = array(
			1=> array(
			1 => array('T1', 'T8'),
			2 => array('T4', 'T5'),
			3 => array('T2', 'T7'),
			4 => array('T3', 'T6')
			),
			2=> array(
			1 => array('W1,1', 'W1,2'),
			2 => array('W1,3', 'W1,4')
			),
			3=> array(
			1 => array('W2,1', 'W2,2'),
			),
			4=> array(
			1 => array('W3,1'),
			)
			);
			break;
			case 'se-4':
			$matchups = array(
			1=> array(
			1 => array('T1', 'T4'),
			2 => array('T2', 'T3')
			),
			2=> array(
			1 => array('W1,1', 'W1,2')
			),
			3=> array(
			1 => array('W2,1'),
			)
			);
			break;
			case 'se-2':
			$matchups = array(
			1=>array(
			1 => array('T1', 'T2'),
			),
			2=>array(
			1 => array('W1,1'),
			)
			);
			break;
			case 'de-4':
			$matchups = array(
			1=>array(
			1 => array('T1', 'T4'),
			2 => array('T2', 'T3'),
			3 => array('', ''),
			4 => array('L1,1', 'L1,2'),
			5 => array('', ''),
			6 => array('', ''),
			7 => array('', ''),
			8 => array('', ''),
			),
			2=>array(
			1 => array('W1,1', 'W1,2'),
			2 => array('L2,1', 'W1,4'),
			3 => array('', ''),
			4 => array('', ''),
			),
			3=>array(
			1 => array('W2,1', 'W2,2'),
			2 => array('', ''),
			),
			4=>array(
			1 => array('W3,1', 'P3,1'),
			),
			5=>array(
			1 => array('W4,1'),
			)
			);
			break;
			case 'de-8':
			$matchups = array(
			1=> array(
			1 => array('T1', 'T8'),
			2 => array('T4', 'T5'),
			3 => array('T2', 'T7'),
			4 => array('T3', 'T6'),
			5 => array('', ''),
			6 => array('', ''),
			7 => array('', ''),
			8 => array('', ''),
			9 => array('', ''),
			10 => array('', ''),
			11 => array('', ''),
			12 => array('', ''),
			13 => array('', ''),
			14 => array('', ''),
			15 => array('', ''),
			16 => array('', ''),
			17 => array('', ''),
			18 => array('', ''),
			19 => array('', ''),
			20 => array('', ''),
			21 => array('', ''),
			22 => array('', ''),
			23 => array('', ''),
			24 => array('', ''),
			25 => array('', ''),
			26 => array('', ''),
			27 => array('', ''),
			28 => array('', ''),
			29 => array('', ''),
			30 => array('', ''),
			31 => array('', ''),
			32 => array('', '')
			),
			2=> array(
			1 => array('W1,1', 'W1,2'),
			2 => array('W1,3', 'W1,4'),
			3 => array('L1,2', 'L1,1'),
			4 => array('L1,4', 'L1,3'),
			5 => array('', ''),
			6 => array('', ''),
			7 => array('', ''),
			8 => array('L2,2', 'L2,1'),
			9 => array('', ''),
			10 => array('', ''),
			11 => array('', ''),
			12 => array('', ''),
			13 => array('', ''),
			14 => array('', ''),
			15 => array('', ''),
			16 => array('', '')
			),
			3=> array(
			1 => array('W2,1', 'W2,2'),
			2 => array('W2,3', 'W2,4'),
			3 => array('', ''),
			4 => array('L3,1', 'W2,8'),
			5 => array('', ''),
			6 => array('', ''),
			7 => array('', ''),
			8 => array('', '')
			),
			4=> array(
			1 => array('W3,1', 'W3,2'),
			2 => array('P4,1', 'W3,4'),
			3 => array('', ''),
			4 => array('', '')
			),
			5=> array(
			1 => array('W4,1', 'W4,2'),
			2 => array('', '')
			),
			6=> array(
			1 => array('W5,1','P5,1')
			),
			7=> array(
			1 => array('W6,1')
			)
			);
			break;
			case 'de-8-1':
			$matchups = array(
			1=> array(
			1 => array('', ''),
			2 => array('', ''),
			3 => array('', ''),
			4 => array('', ''),
			5 => array('', ''),
			6 => array('', ''),
			7 => array('', ''),
			8 => array('', ''),
			9 => array('', ''),
			10 => array('', ''),
			11 => array('', ''),
			12 => array('', ''),
			13 => array('', ''),
			14 => array('L2,1', 'L2,2'),
			15 => array('', ''),
			16 => array('L2,3', 'L2,4'),
			17 => array('', ''),
			18 => array('', ''),
			19 => array('', ''),
			20 => array('', ''),
			21 => array('', ''),
			22 => array('', ''),
			23 => array('', ''),
			24 => array('', ''),
			25 => array('', ''),
			26 => array('', ''),
			27 => array('', ''),
			28 => array('', ''),
			29 => array('', ''),
			30 => array('', ''),
			31 => array('', ''),
			32 => array('', '')
			),
			2=> array(
			1 => array('T1', 'T8'),
			2 => array('T4', 'T5'),
			3 => array('T2', 'T7'),
			4 => array('T3', 'T6'),
			5 => array('', ''),
			6 => array('', ''),
			7 => array('L3,2', 'W1,14'),
			8 => array('L3,1', 'W1,16'),
			9 => array('', ''),
			10 => array('', ''),
			11 => array('', ''),
			12 => array('', ''),
			13 => array('', ''),
			14 => array('', ''),
			15 => array('', ''),
			16 => array('', '')
			),
			3=> array(
			1 => array('W2,1', 'W2,2'),
			2 => array('W2,3', 'W2,4'),
			3 => array('', ''),
			4 => array('W2,7', 'W2,8'),
			5 => array('', ''),
			6 => array('', ''),
			7 => array('', ''),
			8 => array('', '')
			),
			4=> array(
			1 => array('W3,1', 'W3,2'),
			2 => array('L4,1', 'W3,4'),
			3 => array('', ''),
			4 => array('', '')
			),
			5=> array(
			1 => array('W4,1', 'W4,2'),
			2 => array('', '')
			),
			6=> array(
			1 => array('W5,1','P5,1')
			),
			7=> array(
			1 => array('W6,1')
			)
			);
			break;
		}
		$OUTPUT = serialize($matchups);
		$fp = fopen($file,"w"); // open file with Write permission

		if ($fp == FALSE) {
			// handle error
			$error .= "Error!";
			echo $error;
			exit();
		}

		fputs($fp, $OUTPUT);
		fclose($fp);
	} else {
		//echo 'test';
		$matchups = unserialize(implode('',file($file)));
	}
	
	return $matchups;
}

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
?>
