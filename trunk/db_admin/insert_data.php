<?php 
  include(e_PLUGIN."ebattles/db_admin/menu.php"); 
	include(e_PLUGIN."ebattles/db_admin/config_db.php"); 
	include(e_PLUGIN."ebattles/db_admin/connect_db.php"); 
	mysql_select_db($db_name, $con) or die ('Error, no Database');

	$time = GMT_time();
	// Insert users
/*
	// Insert Games
	if($file_handle = fopen("../images/games_icons/Games List.csv", "r"))
	{
	    while (!feof($file_handle) ) {
	    	$line_of_text = fgetcsv($file_handle, 1024);
	    
	    	$shortname = addslashes($line_of_text[0]);
	    	$longname  = addslashes($line_of_text[1]);
	    	
	    	echo "$shortname - $longname <br />";
	    	$sql = 
	    	"INSERT INTO ".TBL_GAMES."(Name, Icon)
	    	 VALUES ('$longname', '$shortname.gif')";
	    	mysql_query($sql,$con) or die ('Error, adding game 1<br />'. mysql_error());
	    }
	    fclose($file_handle);
	}
	else
	{
	    echo "Failed to read the Games List.<br />";
	}
	echo "Added Games<br />";

	// Insert Clans
	$sql = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('Defenders of Sovereignty','DS','qam4')";
	mysql_query($sql,$con) or die ('Error, adding clan<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('SGF-9 Black Wolves','BW','qam4')";
	mysql_query($sql,$con) or die ('Error, adding clan<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('SGF-7 Crimson Guard','CG','qam4')";
	mysql_query($sql,$con) or die ('Error, adding clan<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_CLANS."(Name,Tag,Owner)
	 VALUES ('SGF-4 Death Corps','DC','qam4')";
	mysql_query($sql,$con) or die ('Error, adding clan<br />'. mysql_error());
	echo "Added clans<br />";

	// Insert Divisions
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (1,1,'qam4')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (1,2,'qam4')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (2,1,'qam4')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (2,2,'qam4')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (3,1,'test1')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (3,2,'test1')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (4,1,'test2')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_DIVISIONS."(Clan,Game,Captain)
	 VALUES (4,2,'test2')";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	echo "Added Divisions<br />";

	// Insert Members
	$sql = 
	"INSERT INTO ".TBL_MEMBERS."(Division,Name,timestamp)
	 VALUES (1,'qam4',$time)";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_MEMBERS."(Division,Name,timestamp)
	 VALUES (1,'test',$time)";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_MEMBERS."(Division,Name,timestamp)
	 VALUES (2,'qam4',$time)";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_MEMBERS."(Division,Name,timestamp)
	 VALUES (4,'qam4',$time)";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_MEMBERS."(Division,Name,timestamp)
	 VALUES (6,'test1',$time)";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_MEMBERS."(Division,Name,timestamp)
	 VALUES (8,'test2',$time)";
	mysql_query($sql,$con) or die ('Error, adding Division<br />'. mysql_error());

	// Insert Event
	$sql = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Start_timestamp, End_timestamp, Rules, Description)
	 VALUES ('1v1 Ladder Test', '', '1', 'One Player Ladder','qam4', $time, ($time+7*86400), '...', '<p>This is a 1v1 test ladder.<br />Feel free to join this event and use the &quot;Match Report&quot; system. </p>')";
	mysql_query($sql,$con) or die ('Error, adding event 1<br />'. mysql_error());
	
        $last_id = mysql_insert_id();
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ELO', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'GamesPlayed', '1', '40')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 2<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryRatio', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryPercent', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'UniqueOpponents', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
        	
			
	$sql = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Description)
	 VALUES ('Team Ladder Test', '', '2', 'Team Ladder','qam4', '<p>This is a Team test ladder.<br />Feel free to join this event and use the &quot;Match Report&quot; system. </p>')";
	mysql_query($sql,$con) or die ('Error, adding event 3<br />'. mysql_error());
        $last_id = mysql_insert_id();
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ELO', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'GamesPlayed', '1', '40')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 2<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryRatio', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryPercent', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'UniqueOpponents', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());

	$sql = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Description)
	 VALUES ('1v1 Ladder Test - 1000 players', '', '2', 'One Player Ladder','test1', '<p>This is a 1v1 test ladder with 1000 player.</p><p>Feel free to join this event and use the &quot;Match Report&quot; system. </p>')";
	mysql_query($sql,$con) or die ('Error, adding event 4<br />'. mysql_error());
        $last_id = mysql_insert_id();
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ELO', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'GamesPlayed', '1', '40')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 2<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryRatio', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryPercent', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'UniqueOpponents', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());

	$sql = 
	"INSERT INTO ".TBL_EVENTS."(Name,Password,Game,Type,Owner, Start_timestamp, End_timestamp, Rules, Description)
	 VALUES ('Old 1v1 Event Test', '', '1', 'One Player Ladder','test', ($time-15*86400), ($time-7*86400), '...', 'This is a test past ladder')";
	mysql_query($sql,$con) or die ('Error, adding event 2<br />'. mysql_error());
        $last_id = mysql_insert_id();
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'ELO', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 1<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'GamesPlayed', '1', '40')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 2<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryRatio', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'VictoryPercent', '1', '10')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
 	$sql = 
	"INSERT INTO ".TBL_STATSCATEGORIES."(Event, CategoryName, CategoryMinValue, CategoryMaxValue)
	 VALUES ('$last_id', 'UniqueOpponents', '1', '20')";
	mysql_query($sql,$con) or die ('Error, adding StatsCategories 3<br />'. mysql_error());
	echo "Added Events<br />";
	
	// Insert Mods in Event
	$sql = 
	"INSERT INTO ".TBL_EVENTMODS."(Event,Name,Level)
	 VALUES (1,'qam4',9)";
	mysql_query($sql,$con) or die ('Error, adding moderator 1<br />'. mysql_error());
	echo "Added Mods<br />";


	// Insert Teams in Events
	$sql = 
	"INSERT INTO ".TBL_TEAMS."(Event,Division)
	 VALUES (2,6)";
	mysql_query($sql,$con) or die ('Error, adding team 1<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_TEAMS."(Event,Division)
	VALUES (2,8)";
	mysql_query($sql,$con) or die ('Error, adding team 2<br />'. mysql_error());
	echo "Added Teams<br />";

	// Insert Players in Event
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name)
	 VALUES (1,'qam4')";
	mysql_query($sql,$con) or die ('Error, adding player 1<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name)
	 VALUES (1,'test1')";
	mysql_query($sql,$con) or die ('Error, adding player 2<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name)
	 VALUES (1,'test2')";
	mysql_query($sql,$con) or die ('Error, adding player 3<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name)
	 VALUES (1,'test3')";
	mysql_query($sql,$con) or die ('Error, adding player 4<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name)
	 VALUES (1,'test4')";
	mysql_query($sql,$con) or die ('Error, adding player 5<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name)
	 VALUES (1,'test5')";
	mysql_query($sql,$con) or die ('Error, adding player 6<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name)
	 VALUES (1,'test')";
	mysql_query($sql,$con) or die ('Error, adding player 6<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name,Team)
	 VALUES (2,'test1',1)";
	mysql_query($sql,$con) or die ('Error, adding player 1<br />'. mysql_error());
	$sql = 
	"INSERT INTO ".TBL_PLAYERS."(Event,Name,Team)
	 VALUES (2,'test2',2)";
	mysql_query($sql,$con) or die ('Error, adding player 2<br />'. mysql_error());

	for ($i=1;$i<=1000; $i++)
	{
	   $sql = 
	   "INSERT INTO ".TBL_PLAYERS."(Event,Name)
	    VALUES (3,'Player".$i."')";
	   mysql_query($sql,$con) or die ('Error, adding player<br />'. mysql_error());
	}
	
	echo "Added Players<br />";

	$sql = 
	"INSERT INTO ".TBL_SCORES."(Player,Player_MatchTeam,Player_deltaELO,Player_Score,Player_Rank)
	 VALUES (1,1,10,1,1)";
	mysql_query($sql,$con) or die ('Error, adding score<br />'. mysql_error());
	echo "Added Scores<br />";

*/

?>
</font>
</body>
</html>
