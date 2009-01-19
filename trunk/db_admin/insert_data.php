<?php

require_once("../../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");

require_once(HEADERF);

	global $sql;

	$time = GMT_time();

	// Insert Clans
	$query = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('Defenders of Sovereignty','DS',1)";
	$sql->db_Query($query) or die ('Error, adding clan<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('SGF-9 Black Wolves','BW',1)";
	$sql->db_Query($query) or die ('Error, adding clan<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('SGF-7 Crimson Guard','CG',1)";
	$sql->db_Query($query) or die ('Error, adding clan<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('SGF-4 Death Corps','DC',1)";
	$sql->db_Query($query) or die ('Error, adding clan<br />'. mysql_error());
	$text .= "Added Clans<br />";

	// Insert Divisions
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (1,1,1)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (1,2,1)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (2,1,1)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (2,2,1)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (3,1,2)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (3,2,2)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (4,1,3)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (4,2,3)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$text .= "Added Divisions<br />";

	// Insert Members
	$query = 
	"INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
	 VALUES (1,1,$time)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
	 VALUES (1,2,$time)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
	 VALUES (2,1,$time)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
	 VALUES (4,1,$time)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
	 VALUES (6,2,$time)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_MEMBERS."(Division,User,timestamp)
	 VALUES (8,3,$time)";
	$sql->db_Query($query) or die ('Error, adding Division<br />'. mysql_error());

	// Insert Event
	$query = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Start_timestamp, End_timestamp, Rules, Description)
	 VALUES ('1v1 Ladder Test', '', '1', 'One Player Ladder',1, $time, ($time+7*86400), '...', '<p>This is a 1v1 test ladder.<br />Feel free to join this event and use the &quot;Match Report&quot; system. </p>')";
	$sql->db_Query($query) or die ('Error, adding event 1<br />'. mysql_error());
	
        $last_id = mysql_insert_id();
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ELO', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'GamesPlayed', '1', '40')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 2<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryRatio', '1', '10')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryPercent', '1', '10')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 4<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'UniqueOpponents', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 5<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'OpponentsELO', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 6<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Streaks', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 7<br />'. mysql_error());        	
			
	$query = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Description)
	 VALUES ('Team Ladder Test', '', '2', 'Team Ladder',1, '<p>This is a Team test ladder.<br />Feel free to join this event and use the &quot;Match Report&quot; system. </p>')";
	$sql->db_Query($query) or die ('Error, adding event 3<br />'. mysql_error());
        $last_id = mysql_insert_id();
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ELO', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'GamesPlayed', '1', '40')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 2<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryRatio', '1', '10')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryPercent', '1', '10')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 4<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'UniqueOpponents', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 5<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'OpponentsELO', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 6<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Streaks', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 7<br />'. mysql_error());
	
	$query = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Start_timestamp, End_timestamp, Rules, Description)
	 VALUES ('Old 1v1 Event Test', '', '1', 'One Player Ladder',3, ($time-15*86400), ($time-7*86400), '...', 'This is a test past ladder')";
	$sql->db_Query($query) or die ('Error, adding event 2<br />'. mysql_error());
        $last_id = mysql_insert_id();
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ELO', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'GamesPlayed', '1', '40')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 2<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryRatio', '1', '10')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryPercent', '1', '10')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 4<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'UniqueOpponents', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 5<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'OpponentsELO', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 6<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Streaks', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 7<br />'. mysql_error());
	$text .= "Added Events<br />";
	
	// Insert Mods in Event
	$query = 
	"INSERT INTO ".TBL_EVENTMODS."(Event,User,Level)
	 VALUES (1,1,9)";
	$sql->db_Query($query) or die ('Error, adding moderator 1<br />'. mysql_error());
	$text .= "Added Mods<br />";


	// Insert Teams in Events
	$query = 
	"INSERT INTO ".TBL_TEAMS."(Event,Division)
	 VALUES (2,4)";
	$sql->db_Query($query) or die ('Error, adding team 1<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_TEAMS."(Event,Division)
	VALUES (2,6)";
	$sql->db_Query($query) or die ('Error, adding team 2<br />'. mysql_error());
	$text .= "Added Teams<br />";

	// Insert Players in Event
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User)
	 VALUES (1,1)";
	$sql->db_Query($query) or die ('Error, adding player 1<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User)
	 VALUES (1,2)";
	$sql->db_Query($query) or die ('Error, adding player 2<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User)
	 VALUES (1,3)";
	$sql->db_Query($query) or die ('Error, adding player 3<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User,Team)
	 VALUES (2,1,1)";
	$sql->db_Query($query) or die ('Error, adding player 1<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User,Team)
	 VALUES (2,2,2)";
	$sql->db_Query($query) or die ('Error, adding player 2<br />'. mysql_error());
	
	$text .= "Added Players<br />";

	$query = 
	"INSERT INTO ".TBL_SCORES."(Player,Player_MatchTeam,Player_deltaELO,Player_Score,Player_Rank)
	 VALUES (1,1,10,1,1)";
	$sql->db_Query($query) or die ('Error, adding score<br />'. mysql_error());
	$text .= "Added Scores<br />";

	// Insert Games
	if($file_handle = fopen("../images/games_icons/Games List.csv", "r"))
	{
	    while (!feof($file_handle) ) {
	    	$line_of_text = fgetcsv($file_handle, 1024);
	    
	    	$shortname = addslashes($line_of_text[0]);
	    	$longname  = addslashes($line_of_text[1]);
	    	
	    	//$text .= "$shortname - $longname <br />";
	    	$query = 
	    	"INSERT INTO ".TBL_GAMES."(Name, Icon)
	    	 VALUES ('$longname', '$shortname.gif')";
	    	$sql->db_Query($query) or die ('Error, adding game 1<br />'. mysql_error());
	    }
	    fclose($file_handle);
	}
	else
	{
	    $text .= "Failed to read the Games List.<br />";
	}
	$text .= "Added Games<br />";

$ns->tablerender('Insert data', $text);
require_once(FOOTERF);
exit;
?>
