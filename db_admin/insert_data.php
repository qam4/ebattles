<?php
// Insert SC2 game
$query =
"INSERT INTO ".TBL_GAMES."(Name, ShortName, Icon)
VALUES ('StarCraft 2', 'sc2', 'http://media.xfire.com/xfire/xf/images/icons/sc2b.gif')";
array_push($eplug_tables, $query);

	
?>
