<?php include("menu.php"); ?>

<?php

// TYPE = INNODB
// TYPE = MyISAM

	include 'config_db.php';
	include 'connect_db.php';
	mysql_select_db($db_name, $con) or die ('Error, no Database');
	
	// Create table in my_db database
/*
	//--- Users
	echo "Creating table: users<br />";	 				
	$sql = "CREATE TABLE ".TBL_USERS."
	(
		 username varchar(30) primary key,
		 password varchar(32),
		 userid varchar(32),
		 nickname varchar(32),
		 userlevel tinyint(1) unsigned not null,
		 email varchar(50),
		 timestamp int(11) unsigned not null,
		 joined_timestamp int(11) unsigned not null
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table users failed<br />'. mysql_error());

	//--- active_users
	echo "Creating table: active_users<br />";	 				
	$sql = "CREATE TABLE ".TBL_ACTIVE_USERS."
	(
		 username varchar(30) primary key,
		 timestamp int(11) unsigned not null
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table active_users failed<br />'. mysql_error());
	
	//--- active_guests
	echo "Creating table: active_guests<br />";	 				
	$sql = "CREATE TABLE ".TBL_ACTIVE_GUESTS."
	(
		 ip varchar(63) primary key,
		 timestamp int(11) unsigned not null
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table active_guests failed<br />'. mysql_error());
	
	//--- banned_users
	echo "Creating table: banned_users<br />";	 				
	$sql = "CREATE TABLE ".TBL_BANNED_USERS."
	(
		 username varchar(30) primary key,
		 timestamp int(11) unsigned not null
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table banned_users failed<br />'. mysql_error());

	//--- Games
	echo "Creating table: Games<br />";
	$sql = "CREATE TABLE ".TBL_GAMES."
	(
		GameID int NOT NULL AUTO_INCREMENT, 
		Name varchar(63),
		PRIMARY KEY(GameID),
		Icon varchar(63)
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Games failed<br />'. mysql_error());
	
	//--- Events
	echo "Creating table: Events<br />";
	$sql = "CREATE TABLE ".TBL_EVENTS."
	(
		EventID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(EventID),
		Name varchar(63),
		password varchar(32),
		Game int NOT NULL,
		INDEX (Game),
		FOREIGN KEY (Game) REFERENCES ".TBL_GAMES." (GameID),
		Type varchar(63),
		Start_timestamp int(11) unsigned not null,
		End_timestamp int(11) unsigned not null,
		nbr_games_to_rank int DEFAULT 4,
		nbr_team_games_to_rank int DEFAULT 4,
		ELO_default int DEFAULT 1000,
		ELO_K int DEFAULT 50,
		ELO_M int DEFAULT 100,
		Owner varchar(30),
		INDEX (Owner),
		FOREIGN KEY (Owner) REFERENCES ".TBL_USERS." (username),
		Rules text NOT NULL,
		Description text NOT NULL,
		NextUpdate_timestamp int(11) unsigned not null,
		IsChanged tinyint(1) DEFAULT 1
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Events failed<br />'. mysql_error());
	
	//--- EventModerators
	echo "Creating table: EventModerators<br />";
	$sql = "CREATE TABLE ".TBL_EVENTMODS."
	(
		EventModeratorID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(EventModeratorID),
		Event int NOT NULL,
		INDEX (Event),
		FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
		Name varchar(30),
		INDEX (Name),
		FOREIGN KEY (Name) REFERENCES ".TBL_USERS." (username),
		Level int DEFAULT 0
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table EventModerators failed<br />'. mysql_error());
	
	
	//--- Clans
	echo "Creating table: Clans<br />";
	$sql = "CREATE TABLE ".TBL_CLANS."
	(
		ClanID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(ClanID),
		Name varchar(30),
		Tag varchar(30),
		Owner varchar(30),
		INDEX (Owner),
		FOREIGN KEY (Owner) REFERENCES ".TBL_USERS." (username)
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Clans failed<br />'. mysql_error());

	//--- Divisions
	echo "Creating table: Divisions<br />";
	$sql = "CREATE TABLE ".TBL_DIVISIONS."
	(
		DivisionID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(DivisionID),
		Clan int NOT NULL,
		INDEX (Clan),
		FOREIGN KEY (Clan) REFERENCES ".TBL_CLANS." (ClanID),
		Game int NOT NULL,
		INDEX (Game),
		FOREIGN KEY (Game) REFERENCES ".TBL_GAMES." (GameID),
		Captain varchar(30),
		INDEX (Captain),
		FOREIGN KEY (Captain) REFERENCES ".TBL_USERS." (username)
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Divisions failed<br />'. mysql_error());

	//--- Members
	echo "Creating table: Members<br />";
	$sql = "CREATE TABLE ".TBL_MEMBERS."
	(
		MemberID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(MemberID),
		Division int NOT NULL,
		INDEX (Division),
		FOREIGN KEY (Division) REFERENCES ".TBL_DIVISIONS." (DivisionID),
		Name varchar(30),
		INDEX (Name),
		FOREIGN KEY (Name) REFERENCES ".TBL_USERS." (username),
		timestamp int(11) unsigned not null
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Members failed<br />'. mysql_error());
	
	//--- Teams
	echo "Creating table: Teams<br />";
	$sql = "CREATE TABLE ".TBL_TEAMS."
	(
		TeamID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(TeamID),
		Event int NOT NULL,
		INDEX (Event),
		FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
		Division int NOT NULL,
		INDEX (Division),
		FOREIGN KEY (Division) REFERENCES ".TBL_DIVISIONS." (DivisionID),
		Rank int,
		OverallScore float DEFAULT 0,
		ELORanking int DEFAULT 1000,
		Win int DEFAULT 0,
		Loss int DEFAULT 0
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Teams failed<br />'. mysql_error());
	
	//--- Matchs
	echo "Creating table: Matchs<br />";
	$sql = "CREATE TABLE ".TBL_MATCHS."
	(
		MatchID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(MatchID),
		Event int NOT NULL,
		INDEX (Event),
		FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
		ReportedBy varchar(30),
		INDEX (ReportedBy),
		FOREIGN KEY (ReportedBy) REFERENCES ".TBL_USERS." (username), 
		TimeReported int(11) unsigned not null,
		Comments text NOT NULL
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Matchs failed<br />'. mysql_error());
	
	
	//--- Players
	echo "Creating table: Players<br />";
	$sql = "CREATE TABLE ".TBL_PLAYERS."
	(
		PlayerID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(PlayerID),
		Event int NOT NULL,
		INDEX (Event),
		FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
		Name varchar(30),
		INDEX (Name),
		FOREIGN KEY (Name) REFERENCES ".TBL_USERS." (username),
		Team int NOT NULL,
		INDEX (Team),
		FOREIGN KEY (Team) REFERENCES ".TBL_TEAMS." (TeamID), 
		Rank int,
		RankDelta int DEFAULT 0,
		OverallScore float DEFAULT 0,
		ELORanking int DEFAULT 1000,
		GamesPlayed int DEFAULT 0,
		Win int DEFAULT 0,
		Loss int DEFAULT 0,
		Streak int DEFAULT 0,
		Streak_Best int DEFAULT 0,
		Streak_Worst int DEFAULT 0
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Players failed<br />'. mysql_error());
	

	//--- Scores
	echo "Creating table: Scores<br />";
	$sql = "CREATE TABLE ".TBL_SCORES."
	(
		ScoreID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(ScoreID),
		MatchID int NOT NULL,
		INDEX (MatchID),
		FOREIGN KEY (MatchID) REFERENCES ".TBL_MATCHS." (MatchID),
		Player int NOT NULL,
		INDEX (Player),
		FOREIGN KEY (Player) REFERENCES ".TBL_PLAYERS." (PlayerID), 
		Player_MatchTeam int NOT NULL,
		Player_deltaELO int NOT NULL,
		Player_Score int NOT NULL,
		Player_Rank int NOT NULL
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Scores failed<br />'. mysql_error());
	
	//--- StatsCategories
	echo "Creating table: StatsCategories<br />";
	$sql = "CREATE TABLE ".TBL_STATSCATEGORIES."
	(
		StatsCategoryID int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(StatsCategoryID),
		Event int NOT NULL,
		INDEX (Event),
		FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
		CategoryName varchar(63),
		CategoryMinValue int DEFAULT 1,
		CategoryMaxValue int DEFAULT 100
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table StatsCategories failed<br />'. mysql_error());

	//--- Private Messages
	echo "Creating table: PMs<br />";
	$sql = "CREATE TABLE ".TBL_PMS."
	(
		id INT(11) AUTO_INCREMENT PRIMARY KEY,
		to_id varchar(30) REFERENCES ".TBL_USERS." (username),
		from_id varchar(30) REFERENCES ".TBL_USERS." (username),
		time_sent DATETIME NOT NULL,
		subject VARCHAR(50) NOT NULL DEFAULT '',
		message TEXT NOT NULL DEFAULT '',
		opened CHAR(1) NOT NULL DEFAULT 'n',
		sender_del CHAR(1) NOT NULL DEFAULT 'n',
		receiver_del CHAR(1) NOT NULL DEFAULT 'n',
		time_opened DATETIME DEFAULT NULL
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table PMs failed<br />'. mysql_error());

*/
	//--- Games
	echo "Creating table: Games<br />";
	$sql = "CREATE TABLE ".TBL_GAMES."
	(
		GameID int NOT NULL AUTO_INCREMENT, 
		Name varchar(63),
		PRIMARY KEY(GameID),
		Icon varchar(63)
	) TYPE = MyISAM";
	mysql_query($sql,$con) or die ('Error, create table Games failed<br />'. mysql_error());
	echo "Tables created<br />";
	
	include 'close_db.php';
 ?>

</font>
</body>
</html>
