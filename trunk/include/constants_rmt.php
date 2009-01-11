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
 * Database Constants - these constants are required
 * in order for there to be a successful connection
 * to the MySQL database. Make sure the information is
 * correct.
 */
define("DB_SERVER", "mysql3.freehostia.com");
define("DB_USER", "fremar9_test");
define("DB_PASS", "gmax76");
define("DB_NAME", "fremar9_test");
/*

define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "fremar9_test");
*/

/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */
define("TBL_PREFIX", "battle_");
define("TBL_USERS", TBL_PREFIX."users");
define("TBL_ACTIVE_USERS",  TBL_PREFIX."active_users");
define("TBL_ACTIVE_GUESTS", TBL_PREFIX."active_guests");
define("TBL_BANNED_USERS",  TBL_PREFIX."banned_users");
define("TBL_GAMES",  TBL_PREFIX."Games");
define("TBL_EVENTS",  TBL_PREFIX."Events");
define("TBL_EVENTMODS",  TBL_PREFIX."EventModerators");
define("TBL_TEAMS",  TBL_PREFIX."Teams");
define("TBL_MATCHS",  TBL_PREFIX."Matchs");
define("TBL_PLAYERS",  TBL_PREFIX."Players");
define("TBL_SCORES",  TBL_PREFIX."Scores");
define("TBL_CLANS",  TBL_PREFIX."Clans");
define("TBL_DIVISIONS",  TBL_PREFIX."Divisions");
define("TBL_MEMBERS",  TBL_PREFIX."Members");
define("TBL_STATSCATEGORIES",  TBL_PREFIX."StatsCategories");
define("TBL_PMS",  TBL_PREFIX."PMs");
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

/**
 * This constant forces all users to have
 * lowercase usernames, capital letters are
 * converted automatically.
 */
define("ALL_LOWERCASE", false);

define("LADDER_DIR","");

define("EVENTS_UDATE_DELAY", 60*60);  // Minimum delay between 2 updates in seconds
                                       // Set to negative value to disable the delayed updates
?>
