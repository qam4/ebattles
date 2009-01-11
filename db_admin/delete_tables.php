 <?php include("menu.php"); ?>

<?php

	include 'config_db.php';
	include 'connect_db.php';
	mysql_select_db($db_name, $con) or die ('Error, no Database');
	
	// Create table in my_db database
/*
	
	//--- Users

	echo "Deleting table: users<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_USERS;
	mysql_query($sql,$con) or die ('Error, erase table users failed<br />'. mysql_error());	

	//--- active_users
	echo "Deleting table: active_users<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_ACTIVE_USERS;
	mysql_query($sql,$con) or die ('Error, erase table active_users failed<br />'. mysql_error());
	 					
	//--- active_guests
	echo "Deleting table: active_guests<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_ACTIVE_GUESTS;
	mysql_query($sql,$con) or die ('Error, erase table active_guests failed<br />'. mysql_error());
	 					
	//--- banned_users
	echo "Deleting table: banned_users<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_BANNED_USERS;
	mysql_query($sql,$con) or die ('Error, erase table banned_users failed<br />'. mysql_error());

	//--- Games
	echo "Deleting table: Games<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_GAMES;
	mysql_query($sql,$con) or die ('Error, erase table Games failed<br />'. mysql_error());
	
	//--- Events
	echo "Deleting table: Events<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_EVENTS;
	mysql_query($sql,$con) or die ('Error, erase table Events failed<br />'. mysql_error());
	
	//--- EventModerators
	echo "Deleting table: EventModerators<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_EVENTMODS;
	mysql_query($sql,$con) or die ('Error, erase table EventModerators failed<br />'. mysql_error());
	
	//--- Clans
	echo "Deleting table: Clans<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_CLANS;
	mysql_query($sql,$con) or die ('Error, erase table Clans failed<br />'. mysql_error());

	//--- Divisions
	echo "Deleting table: Divisions<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_DIVISIONS;
	mysql_query($sql,$con) or die ('Error, erase table Divisions failed<br />'. mysql_error());

	//--- Members
	echo "Deleting table: Members<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_MEMBERS;
	mysql_query($sql,$con) or die ('Error, erase table Members failed<br />'. mysql_error());

	//--- Teams
	echo "Deleting table: Teams<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_TEAMS;
	mysql_query($sql,$con) or die ('Error, erase table Teams failed<br />'. mysql_error());
	
	//--- Matchs
	echo "Deleting table: Matchs<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_MATCHS;
	mysql_query($sql,$con) or die ('Error, erase table Matchs failed<br />'. mysql_error());	
	
	//--- Players
	echo "Deleting table: Players<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_PLAYERS;
	mysql_query($sql,$con) or die ('Error, erase table Players failed<br />'. mysql_error());	
	
	//--- Scores
	echo "Deleting table: Scores<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_SCORES;
	mysql_query($sql,$con) or die ('Error, erase table Scores failed<br />'. mysql_error());	

	//--- StatsCategories
	echo "Deleting table: StatsCategories<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_STATSCATEGORIES;
	mysql_query($sql,$con) or die ('Error, erase table StatsCategories failed<br />'. mysql_error());	

	//--- PMs
	echo "Deleting table: PMs<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_PMS;
	mysql_query($sql,$con) or die ('Error, erase table PMs failed<br />'. mysql_error());	
*/
	//--- Games
	echo "Deleting table: Games<br />";
	$sql = "DROP TABLE IF EXISTS ".TBL_GAMES;
	mysql_query($sql,$con) or die ('Error, erase table Games failed<br />'. mysql_error());

	echo "Tables deleted<br />";
	
	include 'close_db.php';
 ?>

</font>
</body>
</html>
