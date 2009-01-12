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
 * Special Names and Level Constants - the admin
 * page will only be accessible to the user with
 * the admin name and also to those users at the
 * admin user level. Feel free to change the names
 * and level constants as you see fit, you may
 * also add additional level specifications.
 * Levels must be digits between 0-9.
 */
define("ADMIN_NAME", "qam4");
define("GUEST_NAME", "Guest");
define("ADMIN_LEVEL", 9);
define("USER_LEVEL",  1);
define("GUEST_LEVEL", 0);

/**
 * This boolean constant controls whether or
 * not the script keeps track of active users
 * and active guests who are visiting the site.
 */
define("TRACK_VISITORS", true);

/**
 * Timeout Constants - these constants refer to
 * the maximum amount of time (in minutes) after
 * their last page fresh that a user and guest
 * are still considered active visitors.
 */
define("USER_TIMEOUT", 10);
define("GUEST_TIMEOUT", 5);

/**
 * Cookie Constants - these are the parameters
 * to the setcookie function call, change them
 * if necessary to fit your website. If you need
 * help, visit www.php.net for more info.
 * <http://www.php.net/manual/en/function.setcookie.php>
 */
define("COOKIE_EXPIRE", 60*60*24*100);  //100 days by default
define("COOKIE_PATH", "/");  //Avaible in whole domain

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

define("LADDER_DIR","");
define("EVENTS_UDATE_DELAY", 60*60);  // Minimum delay between 2 updates in seconds
                                       // Set to negative value to disable the delayed updates

?>
