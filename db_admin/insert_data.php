<?php
// Insert SC2 game
$query =
"INSERT INTO ".TBL_GAMES."(Name, ShortName, Icon)
VALUES ('StarCraft 2', 'sc2', 'http://media.xfire.com/xfire/xf/images/icons/sc2b.gif')";
array_push($eplug_tables, $query);
$last_id = 1; //mysql_insert_id();

// Add Factions
$query =
"INSERT INTO ".TBL_FACTIONS."(Game, Name, Icon)
VALUES ($last_id, 'Protoss', 'sc2-Protoss.png')";
array_push($eplug_tables, $query);
$query =
"INSERT INTO ".TBL_FACTIONS."(Game, Name, Icon)
VALUES ($last_id, 'Terran', 'sc2-Terran.png')";
array_push($eplug_tables, $query);
$query =
"INSERT INTO ".TBL_FACTIONS."(Game, Name, Icon)
VALUES ($last_id, 'Zerg', 'sc2-Zerg.png')";
array_push($eplug_tables, $query);
$query =
"INSERT INTO ".TBL_FACTIONS."(Game, Name, Icon)
VALUES ($last_id, 'Random', 'sc2-Random.png')";
array_push($eplug_tables, $query);

// Add Maps
$query =
"INSERT INTO ".TBL_MAPS."(Game, Name, Image)
VALUES ($last_id, 'Blistering Sands', 'sc2-BlisteringSands.jpg')";
array_push($eplug_tables, $query);
$query =
"INSERT INTO ".TBL_MAPS."(Game, Name, Image)
VALUES ($last_id, 'Kulas Ravine', 'sc2-KulasRavine.jpg')";
array_push($eplug_tables, $query);
$query =
"INSERT INTO ".TBL_MAPS."(Game, Name, Image)
VALUES ($last_id, 'Lost Temple', 'sc2-LostTemple.jpg')";
array_push($eplug_tables, $query);
$query =
"INSERT INTO ".TBL_MAPS."(Game, Name, Image)
VALUES ($last_id, 'Scrapyard', 'sc2-Scrapyard.jpg')";
array_push($eplug_tables, $query);
$query =
"INSERT INTO ".TBL_MAPS."(Game, Name, Image)
VALUES ($last_id, 'Steppes Of War', 'sc2-SteppesOfWar.jpg')";
array_push($eplug_tables, $query);

?>
