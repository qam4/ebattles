<?php
/*
+---------------------------------------------------------------+
|        e107 website system
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

global $PLUGINS_DIRECTORY;
$lan_file = e_PLUGIN."ebattles/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."ebattles/languages/English.php");
include_once(e_PLUGIN."ebattles/include/constants.php");
include(e_PLUGIN."ebattles/include/revision.php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = 'EBATTLES_L1';
$eplug_version = "$majorRevision.$minorRevision.$svnRevision";
$eplug_author = "Frederic Marchais (qam4)";
$eplug_logo = "";
$eplug_url = "http://ebattles.freehostia.com";
$eplug_email = "frederic.marchais@gmail.com";
$eplug_description = EBATTLES_L2;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";        // leave blank if no readme file


// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "ebattles";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = 'ebattles_menu';

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "";

// Icon image and caption text ------------------------------------------------------------------------------------
//$eplug_icon = $eplug_folder."/icon/list_32.png";
//$eplug_icon_small = $eplug_folder."/icon/list_16.png";
//$eplug_caption =  EBATTLES_L3;

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array();

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
TBL_EVENTS_SHORT,
TBL_EVENTMODS_SHORT,
TBL_TEAMS_SHORT,
TBL_MATCHS_SHORT,
TBL_PLAYERS_SHORT,
TBL_SCORES_SHORT,
TBL_CLANS_SHORT,
TBL_DIVISIONS_SHORT,
TBL_MEMBERS_SHORT,
TBL_STATSCATEGORIES_SHORT,
TBL_GAMES_SHORT
);

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".TBL_EVENTS."
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
	Owner int(10) unsigned NOT NULL,
	INDEX (Owner),
	FOREIGN KEY (Owner) REFERENCES ".TBL_USERS." (user_id),
	Rules text NOT NULL,
	Description text NOT NULL,
	NextUpdate_timestamp int(11) unsigned not null,
	IsChanged tinyint(1) DEFAULT 1
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_EVENTMODS."
(
	EventModeratorID int NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(EventModeratorID),
	Event int NOT NULL,
	INDEX (Event),
	FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
	Name int(10) unsigned NOT NULL,
	INDEX (Name),
	FOREIGN KEY (Name) REFERENCES ".TBL_USERS." (user_id),
	Level int DEFAULT 0
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_CLANS."
(
	ClanID int NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(ClanID),
	Name varchar(30),
	Tag varchar(30),
	Owner int(10) unsigned NOT NULL,
	INDEX (Owner),
	FOREIGN KEY (Owner) REFERENCES ".TBL_USERS." (user_id)
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_DIVISIONS."
(
	DivisionID int NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(DivisionID),
	Clan int NOT NULL,
	INDEX (Clan),
	FOREIGN KEY (Clan) REFERENCES ".TBL_CLANS." (ClanID),
	Game int NOT NULL,
	INDEX (Game),
	FOREIGN KEY (Game) REFERENCES ".TBL_GAMES." (GameID),
	Captain int(10) unsigned NOT NULL,
	INDEX (Captain),
	FOREIGN KEY (Captain) REFERENCES ".TBL_USERS." (user_id)
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_MEMBERS."
(
	MemberID int NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(MemberID),
	Division int NOT NULL,
	INDEX (Division),
	FOREIGN KEY (Division) REFERENCES ".TBL_DIVISIONS." (DivisionID),
	Name int(10) unsigned NOT NULL,
	INDEX (Name),
	FOREIGN KEY (Name) REFERENCES ".TBL_USERS." (user_id),
	timestamp int(11) unsigned not null
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_TEAMS."
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
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_MATCHS."
(
	MatchID int NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(MatchID),
	Event int NOT NULL,
	INDEX (Event),
	FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
	ReportedBy int(10) unsigned NOT NULL,
	INDEX (ReportedBy),
	FOREIGN KEY (ReportedBy) REFERENCES ".TBL_USERS." (user_id), 
	TimeReported int(11) unsigned not null,
	Comments text NOT NULL
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_PLAYERS."
(
	PlayerID int NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(PlayerID),
	Event int NOT NULL,
	INDEX (Event),
	FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
	Name int(10) unsigned NOT NULL,
	INDEX (Name),
	FOREIGN KEY (Name) REFERENCES ".TBL_USERS." (user_id),
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
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_SCORES."
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
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_STATSCATEGORIES."
(
	StatsCategoryID int NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(StatsCategoryID),
	Event int NOT NULL,
	INDEX (Event),
	FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
	CategoryName varchar(63),
	CategoryMinValue int DEFAULT 1,
	CategoryMaxValue int DEFAULT 100
) TYPE = MyISAM;",
"CREATE TABLE ".TBL_GAMES."
(
	GameID int NOT NULL AUTO_INCREMENT, 
	Name varchar(63),
	PRIMARY KEY(GameID),
	Icon varchar(63)
) TYPE = MyISAM;"
);

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = EBATTLES_L4;


// upgrading ... //

$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = "";
$eplug_upgrade_done = "";


?>
