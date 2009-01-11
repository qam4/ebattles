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

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = 'EBATTLES_L1';
$eplug_version = "0.1";
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
$eplug_table_names = "";

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = "";


// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = EBATTLES_L5;
$eplug_link_url = e_PLUGIN."ebattles/ebattles.php";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = EBATTLES_L4;


// upgrading ... //

$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = "";
$eplug_upgrade_done = "";


?>
