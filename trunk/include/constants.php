<?php
/**
 * Constants.php
 *
 * This file is intended to group all constants to
 * make it easier for the site administrator to tweak
 * the login script.
 *
 */

/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */
define("TBL_PREFIX", "ebattles_");

define("TBL_USERS_SHORT",           "user");
define("TBL_EVENTS_SHORT",          TBL_PREFIX."Events");
define("TBL_EVENTMODS_SHORT",       TBL_PREFIX."EventModerators");
define("TBL_TEAMS_SHORT",           TBL_PREFIX."Teams");
define("TBL_MATCHS_SHORT",          TBL_PREFIX."Matchs");
define("TBL_PLAYERS_SHORT",         TBL_PREFIX."Players");
define("TBL_SCORES_SHORT",          TBL_PREFIX."Scores");
define("TBL_CLANS_SHORT",           TBL_PREFIX."Clans");
define("TBL_DIVISIONS_SHORT",       TBL_PREFIX."Divisions");
define("TBL_MEMBERS_SHORT",         TBL_PREFIX."Members");
define("TBL_STATSCATEGORIES_SHORT", TBL_PREFIX."StatsCategories");
define("TBL_GAMES_SHORT",           TBL_PREFIX."Games");

define("TBL_USERS",           MPREFIX."user");
define("TBL_EVENTS",          MPREFIX.TBL_EVENTS_SHORT);
define("TBL_EVENTMODS",       MPREFIX.TBL_EVENTMODS_SHORT);
define("TBL_TEAMS",           MPREFIX.TBL_TEAMS_SHORT);
define("TBL_MATCHS",          MPREFIX.TBL_MATCHS_SHORT);
define("TBL_PLAYERS",         MPREFIX.TBL_PLAYERS_SHORT);
define("TBL_SCORES",          MPREFIX.TBL_SCORES_SHORT);
define("TBL_CLANS",           MPREFIX.TBL_CLANS_SHORT);
define("TBL_DIVISIONS",       MPREFIX.TBL_DIVISIONS_SHORT);
define("TBL_MEMBERS",         MPREFIX.TBL_MEMBERS_SHORT);
define("TBL_STATSCATEGORIES", MPREFIX.TBL_STATSCATEGORIES_SHORT);
define("TBL_GAMES",           MPREFIX.TBL_GAMES_SHORT);

/**
 * Email Constants - these specify what goes in
 * the from field in the emails that the script
 * sends to users, and whether to send a
 * welcome email to newly registered users.
 */
define("EMAIL_FROM_NAME", "eBattles");
define("EMAIL_FROM_ADDR", "frederic.marchais@gmail.com");
define("EMAIL_PASSWORD", "gmax76");
define("EMAIL_WELCOME", true);

define("EVENTS_UDATE_DELAY", 60*60);  // Minimum delay between 2 updates in seconds
define("DEBUG", 1);                   //  

?>
