<?php
/*
+---------------------------------------------------------------+
|        e107 website system
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN."ebattles/include/main.php");
include(e_PLUGIN."ebattles/include/revision.php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = EB_L1;
$eplug_version = "$majorRevision.$minorRevision.$svnRevision";
$eplug_author = "Frederic Marchais (qam4)";
$eplug_logo = "";
$eplug_url = "http://ebattles.freehostia.com";
$eplug_email = "frederic.marchais@gmail.com";
$eplug_description = EB_L2;
$eplug_compatible = "e107v0.7+";
$eplug_compliant = TRUE; // indicator if plugin is XHTML compliant, shows icon
$eplug_readme = "";        // leave blank if no readme file

$eb_SQL = new db;
$eb_SQL->db_Select("plugin", "plugin_version", "plugin_path='ebattles' AND plugin_installflag > 0");
list($eb_version_string) = $eb_SQL->db_Fetch();
$eb_version_string = preg_replace("/[a-zA-z\s]/", '', $eb_version_string);
$eb_version = explode('.', $eb_version_string, 3);

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "ebattles";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = 'ebattles_menu';

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/ebattles_32.ico";
$eplug_icon_small = $eplug_folder."/images/ebattles_16.ico";
//$eplug_caption =  EB_L3;

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
    "eb_events_update_delay" => 60,
    "eb_events_update_delay_enable" => 0,
    "eb_mod_class" => e_UC_ADMIN,
    "eb_events_create_class" => e_UC_MEMBER,
    "eb_teams_create_class" => e_UC_MEMBER,
    "eb_tab_theme" => 'default',
    "eb_max_image_size_check" => 0,
    "eb_max_image_size" => 16,
    "eb_default_items_per_page" => 25,
    "eb_max_avatar_size" => 32,
    "eb_avatar_enable_playersstandings" => 1,
    "eb_avatar_default_image" => 'anonymous.png',
    "eb_avatar_enable_teamsstandings" => 1,
    "eb_avatar_enable_teamslist" => 1,
    "eb_avatar_default_team_image" => 'default_group_avatar.png',
    "eb_links_menuheading" => EB_ADMIN_L29,
    "eb_activity_menuheading" => EB_ADMIN_L31,
    "eb_activity_number_of_items" => 10,
    "eb_activity_max_image_size_check" => 1,
    "eb_activity_max_image_size" => 16,
    "eb_disclaimer" => EB_ADMIN_L37
);

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
    TBL_GAMES_SHORT,
    TBL_EVENTS_SHORT,
    TBL_EVENTMODS_SHORT,
    TBL_CLANS_SHORT,
    TBL_DIVISIONS_SHORT,
    TBL_MEMBERS_SHORT,
    TBL_TEAMS_SHORT,
    TBL_MATCHS_SHORT,
    TBL_PLAYERS_SHORT,
    TBL_SCORES_SHORT,
    TBL_STATSCATEGORIES_SHORT,
    TBL_AWARDS_SHORT,
    TBL_PLAYERS_RESULTS_SHORT,
    TBL_GAMES_GENRES_SHORT,
    TBL_GAMES_PLATFORMS_SHORT
);

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
    "CREATE TABLE ".TBL_GAMES."
    (
    GameID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(GameID),
    Name varchar(63),
    Icon varchar(63),
    Style varchar(63) NOT NULL default '',
    Genre varchar(63) NOT NULL default '',
    Description text NOT NULL default '',
    Developer varchar(63) NOT NULL default '',
    Publisher varchar(63) NOT NULL default '',
    ReleaseDate varchar(63) NOT NULL default '',
    OfficialWebsite varchar(63) NOT NULL default '',
    ESRB varchar(63) NOT NULL default '',
    Banner varchar(63) NOT NULL default ''
    ) TYPE = MyISAM;",
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
    nbr_games_to_rank int DEFAULT '4',
    nbr_team_games_to_rank int DEFAULT '4',
    ELO_default int DEFAULT '".ELO_DEFAULT."',
    ELO_K int DEFAULT '".ELO_K."',
    ELO_M int DEFAULT '".ELO_M."',
    TS_default_mu float DEFAULT '".floatToSQL(TS_Mu0)."',
    TS_default_sigma float DEFAULT '".floatToSQL(TS_sigma0)."',
    TS_beta float DEFAULT '".floatToSQL(TS_beta)."',
    TS_epsilon float DEFAULT '".floatToSQL(TS_epsilon)."',
    Owner int(10) unsigned NOT NULL,
    INDEX (Owner),
    FOREIGN KEY (Owner) REFERENCES ".TBL_USERS." (user_id),
    Rules text NOT NULL,
    Description text NOT NULL,
    NextUpdate_timestamp int(11) unsigned not null,
    IsChanged tinyint(1) DEFAULT '1',
    AllowDraw tinyint(1) DEFAULT '0',
    AllowScore tinyint(1) DEFAULT '0',
    PointsPerWin int default '".PointsPerWin_DEFAULT."',
    PointsPerDraw int default '".PointsPerDraw_DEFAULT."',
    PointsPerLoss int default '".PointsPerLoss_DEFAULT."',
    match_report_userclass tinyint(3) unsigned NOT NULL DEFAULT '".eb_UC_EVENT_PLAYER."',
    quick_loss_report tinyint(1) DEFAULT '1',
    hide_ratings_column tinyint(1) DEFAULT '0',
    MatchesApproval tinyint(3) unsigned NOT NULL DEFAULT '".eb_UC_NONE."'
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_EVENTMODS."
    (
    EventModeratorID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(EventModeratorID),
    Event int NOT NULL,
    INDEX (Event),
    FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
    User int(10) unsigned NOT NULL,
    INDEX (User),
    FOREIGN KEY (User) REFERENCES ".TBL_USERS." (user_id),
    Level int DEFAULT '0'
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_CLANS."
    (
    ClanID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(ClanID),
    Name varchar(30),
    Tag varchar(30),
    Owner int(10) unsigned NOT NULL,
    INDEX (Owner),
    FOREIGN KEY (Owner) REFERENCES ".TBL_USERS." (user_id),
    password varchar(32),
    Image varchar(100) NOT NULL default ''
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
    User int(10) unsigned NOT NULL,
    INDEX (User),
    FOREIGN KEY (User) REFERENCES ".TBL_USERS." (user_id),
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
    Rank int DEFAULT '0',
    RankDelta int DEFAULT '0',
    OverallScore float DEFAULT '0',
    ELORanking int DEFAULT '".ELO_DEFAULT."',
    TS_mu float DEFAULT '".floatToSQL(TS_Mu0)."',
    TS_sigma float DEFAULT '".floatToSQL(TS_sigma0)."',
    GamesPlayed int DEFAULT '0',
    Win int DEFAULT '0',
    Draw int DEFAULT '0',
    Loss int DEFAULT '0',
    Streak int DEFAULT '0',
    Streak_Best int DEFAULT '0',
    Streak_Worst int DEFAULT '0',
    Score int DEFAULT '0',
    ScoreAgainst int DEFAULT '0',
    Points int DEFAULT '0',
    Banned tinyint(1) DEFAULT '0'
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
    Comments text NOT NULL,
    Status varchar(20) DEFAULT 'active'
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_PLAYERS."
    (
    PlayerID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(PlayerID),
    Event int NOT NULL,
    INDEX (Event),
    FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
    User int(10) unsigned NOT NULL,
    INDEX (User),
    FOREIGN KEY (User) REFERENCES ".TBL_USERS." (user_id),
    Team int NOT NULL,
    INDEX (Team),
    FOREIGN KEY (Team) REFERENCES ".TBL_TEAMS." (TeamID),
    Rank int DEFAULT '0',
    RankDelta int DEFAULT '0',
    OverallScore float DEFAULT '0',
    ELORanking int DEFAULT '".ELO_DEFAULT."',
    TS_mu float DEFAULT '".floatToSQL(TS_Mu0)."',
    TS_sigma float DEFAULT '".floatToSQL(TS_sigma0)."',
    GamesPlayed int DEFAULT '0',
    Win int DEFAULT '0',
    Draw int DEFAULT '0',
    Loss int DEFAULT '0',
    Streak int DEFAULT '0',
    Streak_Best int DEFAULT '0',
    Streak_Worst int DEFAULT '0',
    Score int DEFAULT '0',
    ScoreAgainst int DEFAULT '0',
    Points int DEFAULT '0',
    Banned tinyint(1) DEFAULT '0'
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
    Team int NOT NULL,
    INDEX (Team),
    FOREIGN KEY (Team) REFERENCES ".TBL_TEAMS." (TeamID),
    Player_MatchTeam int DEFAULT '0',
    Player_deltaELO int DEFAULT '0',
    Player_deltaTS_mu float DEFAULT '0',
    Player_deltaTS_sigma float DEFAULT '0',
    Player_Score int DEFAULT '0',
    Player_ScoreAgainst int DEFAULT '0',
    Player_Rank int DEFAULT '0',
    Player_Win int DEFAULT '0',
    Player_Loss int DEFAULT '0',
    Player_Draw int DEFAULT '0',
    Player_Points int DEFAULT '0'
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_STATSCATEGORIES."
    (
    StatsCategoryID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(StatsCategoryID),
    Event int NOT NULL,
    INDEX (Event),
    FOREIGN KEY (Event) REFERENCES ".TBL_EVENTS." (EventID),
    CategoryName varchar(63),
    CategoryMinValue int DEFAULT '1',
    CategoryMaxValue int DEFAULT '100',
    InfoOnly tinyint(1) DEFAULT '0'
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_AWARDS."
    (
    AwardID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(AwardID),
    Player int NOT NULL,
    INDEX (Player),
    FOREIGN KEY (Player) REFERENCES ".TBL_PLAYERS." (PlayerID),
    Type varchar(63),
    timestamp int(11) unsigned not null
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_PLAYERS_RESULTS."
    (
    PlayerResultID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(PlayerResultID),
    Player int NOT NULL,
    INDEX (Player),
    FOREIGN KEY (Player) REFERENCES ".TBL_PLAYERS." (PlayerID),
    Type varchar(63),
    Value float DEFAULT '0',
    Display varchar(63),
    timestamp int(11) unsigned not null
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_GAMES_GENRES."
    (
    GameGenreID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(GameGenreID),
    Game int NOT NULL,
    INDEX (Game),
    FOREIGN KEY (Game) REFERENCES ".TBL_GAMES." (GameID),
    Genre varchar(63)
    ) TYPE = MyISAM;",
    "CREATE TABLE ".TBL_GAMES_PLATFORMS."
    (
    GamePlatformID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(GamePlatformID),
    Game int NOT NULL,
    INDEX (Game),
    FOREIGN KEY (Game) REFERENCES ".TBL_GAMES." (GameID),
    Platform varchar(63)
    ) TYPE = MyISAM;"
);

// Insert "Unknown Game"
$query =
"INSERT INTO ".TBL_GAMES."(Name, Icon)
VALUES ('".EB_GAME_L1."', 'unknown.gif')";
array_push($eplug_tables, $query);

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = EB_L5;
$eplug_link_url = e_PLUGIN."ebattles/events.php";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = EB_L4;

// upgrading ... //
$upgrade_add_prefs = array();
$upgrade_remove_prefs = array();
$upgrade_alter_tables = array();

$revision = $eb_version[2];
//$revision = 100;

if ($revision < 98)
{
    // To revision 98
    $upgrade_alter_tables += array(
        "ALTER TABLE ".TBL_GAMES." ADD Style varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD Genre varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD Description text NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD Developer varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD Publisher varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD ReleaseDate varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD OfficialWebsite varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD ESRB varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." ADD Banner varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_EVENTS." ADD match_report_userclass tinyint(3) unsigned NOT NULL DEFAULT '".eb_UC_EVENT_PLAYER."'",
        "ALTER TABLE ".TBL_EVENTS." ADD quick_loss_report tinyint(1) DEFAULT '1'",
        "ALTER TABLE ".TBL_EVENTS." ADD hide_ratings_column tinyint(1) DEFAULT '0'",
        "ALTER TABLE ".TBL_CLANS." ADD password varchar(32)",
        "ALTER TABLE ".TBL_TEAMS." ADD Streak int DEFAULT '0'",
        "ALTER TABLE ".TBL_TEAMS." ADD Streak_Best int DEFAULT '0'",
        "ALTER TABLE ".TBL_TEAMS." ADD Streak_Worst int DEFAULT '0'",
        "ALTER TABLE ".TBL_TEAMS." ADD Banned tinyint(1) DEFAULT '0'",
        "ALTER TABLE ".TBL_PLAYERS." ADD Banned tinyint(1) DEFAULT '0'",
        "CREATE TABLE ".TBL_PLAYERS_RESULTS."
        (
        PlayerResultID int NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(PlayerResultID),
        Player int NOT NULL,
        INDEX (Player),
        FOREIGN KEY (Player) REFERENCES ".TBL_PLAYERS." (PlayerID),
        Type varchar(63),
        Value float DEFAULT '0',
        timestamp int(11) unsigned not null
        ) TYPE = MyISAM;"
    );

    $upgrade_add_prefs += array(
        "eb_events_update_delay" => 60,
        "eb_mod_class" => e_UC_ADMIN,
        "eb_events_create_class" => e_UC_MEMBER,
        "eb_teams_create_class" => e_UC_MEMBER,
        "eb_tab_theme" => 'default'
    );
}

if ($revision < 114)
{
    // To revision 114
    $upgrade_add_prefs += array(
        "eb_max_image_size_check" => 0,
        "eb_max_image_size" => 16
    );
}

if ($revision < 121)
{
    // To revision 121
    $upgrade_add_prefs += array(
        "eb_default_items_per_page" => 25
    );
}

if ($revision < 122)
{
    // To revision 122
    $upgrade_add_prefs += array(
        "eb_max_avatar_size" => 32,
        "eb_avatar_enable_playersstandings" => 1,
        "eb_avatar_default_image" => 'anonymous.png'
    );
}

if ($revision < 133)
{
    // To revision 133
    $upgrade_add_prefs += array(
        "eb_links_menuheading" => EB_ADMIN_L29,
        "eb_activity_menuheading" => EB_ADMIN_L31,
        "eb_activity_number_of_items" => 10
    );
}

if ($revision < 141)
{
    // To revision 141
    $upgrade_alter_tables += array(
        "ALTER TABLE ".TBL_PLAYERS_RESULTS." ADD Display varchar(63) NOT NULL default ''",
        "ALTER TABLE ".TBL_GAMES." DROP Style",
        "ALTER TABLE ".TBL_GAMES." DROP Genre",
        "CREATE TABLE ".TBL_GAMES_GENRES."
        (
        GameGenreID int NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(GameGenreID),
        Game int NOT NULL,
        INDEX (Game),
        FOREIGN KEY (Game) REFERENCES ".TBL_GAMES." (GameID),
        Genre varchar(63)
        ) TYPE = MyISAM;",
        "CREATE TABLE ".TBL_GAMES_PLATFORMS."
        (
        GamePlatformID int NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(GamePlatformID),
        Game int NOT NULL,
        INDEX (Game),
        FOREIGN KEY (Game) REFERENCES ".TBL_GAMES." (GameID),
        Platform varchar(63)
        ) TYPE = MyISAM;"
    );
    $upgrade_add_prefs += array(
        "eb_activity_max_image_size_check" => 1,
        "eb_activity_max_image_size" => 16
    );
}

if ($revision < 171)
{
    // To revision 171
    $upgrade_alter_tables += array(
        "ALTER TABLE ".TBL_CLANS." ADD Image varchar(100) NOT NULL default ''"
    );
    $upgrade_add_prefs += array(
    "eb_avatar_enable_teamsstandings" => 1,
    "eb_avatar_enable_teamslist" => 1,
    "eb_avatar_default_team_image" => 'default_group_avatar.png'
    );
}
if ($revision < 173)
{
    // To revision 173
    $upgrade_add_prefs += array(
    "eb_disclaimer" => EB_ADMIN_L37
    );
}
if ($revision < 175)
{
    // To revision 175
    $upgrade_alter_tables += array(
        "ALTER TABLE ".TBL_EVENTS." ADD MatchesApproval tinyint(3) unsigned NOT NULL DEFAULT '".eb_UC_NONE."'",
        "ALTER TABLE ".TBL_MATCHS." ADD Status varchar(20) DEFAULT 'active'"
    );
}
if ($revision < 196)
{
    // To revision 196
    $upgrade_alter_tables += array(
        "ALTER TABLE ".TBL_TEAMS." ADD GamesPlayed int DEFAULT '0'",
        "ALTER TABLE ".TBL_SCORES." ADD Team int NOT NULL",
        "ALTER TABLE ".TBL_SCORES." ADD INDEX (Team)",
        "ALTER TABLE ".TBL_SCORES." ADD FOREIGN KEY (Team) REFERENCES ".TBL_TEAMS." (TeamID)"
    );
}


/* dbg
echo "<br>Prefs upgrade:";
print_r($upgrade_add_prefs);
echo "<br>Tables upgrade:";
print_r($upgrade_alter_tables);
echo "<br>";
*/

$eplug_upgrade_done = $eplug_name.' successfully upgraded, now using version: '.$eplug_version;

if(!function_exists("ebattles_uninstall"))
{
	function ebattles_uninstall()
	{
		global $sql;
		$sql->db_Delete("menus", "menu_name = 'eb_activity_menu'");
		$sql->db_Delete("menus", "menu_name = 'eb_activity_menu'");

  	    //Remove comments and ratings during uninstall
	    purgeComments("ebmatches");
	    purgeRatings("ebscores");
	}
}
?>
