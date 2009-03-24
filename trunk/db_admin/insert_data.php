<?php

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

	// Insert Events
	// Event 1 - 1v1 ladder test
	$query = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Start_timestamp, End_timestamp, Rules, Description, AllowDraw, AllowScore)
	 VALUES ('1v1 Ladder Test', '', '656', 'One Player Ladder',1, $time, ($time+7*86400), '...', 
	 '<p style=\"text-align: center;\">This is a 1v1 test ladder.</p><p style=\"text-align: center;\">Feel free to join this event and use the \"Match Report\" system.</p><p style=\"text-align: center;\"><img src=\"http://www.visionfutur.com/img/linux/starcraft1.jpg\" border=\"0\" alt=\"Starcraft\" title=\"Starcraft\" width=\"640\" height=\"480\" /></p>',
	 1,1)";
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
	 VALUES ('$last_id', 'WinDrawLoss', '1', '10')";
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
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Skill', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 8<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Score', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 9<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ScoreAgainst', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 10<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ScoreDiff', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 11<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Points', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 12<br />'. mysql_error());
			
	// Insert Mods in Event
	$query = 
	"INSERT INTO ".TBL_EVENTMODS."(Event,User,Level)
	 VALUES ($last_id,1,9)";
	$sql->db_Query($query) or die ('Error, adding moderator 1<br />'. mysql_error());
	$text .= "Added Mods<br />";

	// Insert Players in Event
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User)
	 VALUES ($last_id,1)";
	$sql->db_Query($query) or die ('Error, adding player 1<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User)
	 VALUES ($last_id,2)";
	$sql->db_Query($query) or die ('Error, adding player 2<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User)
	 VALUES ($last_id,3)";
	$sql->db_Query($query) or die ('Error, adding player 3<br />'. mysql_error());

    // Event 2 - team ladder test
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
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Skill', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
	
	// Insert Teams in Event
	$query = 
	"INSERT INTO ".TBL_TEAMS."(Event,Division)
	 VALUES ($last_id,4)";
	$sql->db_Query($query) or die ('Error, adding team 1<br />'. mysql_error());
	$team1_id = mysql_insert_id();

	$query = 
	"INSERT INTO ".TBL_TEAMS."(Event,Division)
	VALUES ($last_id,6)";
	$sql->db_Query($query) or die ('Error, adding team 2<br />'. mysql_error());
	$team2_id = mysql_insert_id();
	$text .= "Added Teams<br />";

	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User,Team)
	 VALUES ($last_id,1,$team1_id)";
	$sql->db_Query($query) or die ('Error, adding player 1<br />'. mysql_error());
	$query = 
	"INSERT INTO ".TBL_PLAYERS."(Event,User,Team)
	 VALUES ($last_id,2,$team2_id)";
	$sql->db_Query($query) or die ('Error, adding player 2<br />'. mysql_error());
	
	
	// Event 3 - Old 1v1 ladder test
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
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Skill', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
	
/*	
	// Event 4 - 1v1 ladder - 1000 players test
	$query = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Start_timestamp, End_timestamp, Rules, Description, AllowDraw, AllowScore)
	 VALUES ('1v1 Ladder Test - many players', '', '2', 'One Player Ladder',1, $time, ($time+7*86400), '...', '<p>This is a 1v1 test ladder with 1000 player.</p><p>Feel free to join this event and use the &quot;Match Report&quot; system. </p>',1,1)";
	$sql->db_Query($query) or die ('Error, adding event 4<br />'. mysql_error());
	
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
	 VALUES ('$last_id', 'WinDrawLoss', '1', '10')";
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
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Skill', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 8<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Score', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 9<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ScoreAgainst', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 10<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ScoreDiff', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 11<br />'. mysql_error());
 	$query = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'Points', '1', '20')";
	$sql->db_Query($query) or die ('Error, adding StatsCategories 12<br />'. mysql_error());
		
	for ($i=1;$i<=40; $i++)
	{
	   admin_update($sql -> db_Insert("user", "0, 'Player".$i."', 'Player".$i."',  '', '".md5("test")."', '$key', 'test@hotmail.com', 'mysig', '', '', '1', '".time()."', '".time()."', '".time()."', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', 'Player".$i."', '', '', '', '".time()."', ''"), 'insert', USRLAN_70);
       $user_id = mysql_insert_id();
	   
	   $query = 
	   "INSERT INTO ".TBL_PLAYERS."(Event,User)
	    VALUES ($last_id,$user_id)";
	   $sql->db_Query($query) or die ('Error, adding player '.$user_id.' in event '.$last_id.'<br />'. mysql_error());
	}	
*/

	$message = $text;
	
?>
