# Requirements #
  * e107 CMS v0.7+

# Step-by-step installation #
  1. Get the plugin files on your server
    1. Download the plugin zip file
    1. Unzip the plugin
    1. Upload ebattles/ folder to e107\_plugins/
    1. Make sure ebattles/cache/ folder is writable (CHMOD 755 or 777)
  1. Install the plugin in e107
    1. In e107, Admin Area>Plugin Manager, click on the eBattles "Install" button
  1. Add plugin menus
    1. In Admin Area>Menus, you can choose an area for eBattles menus
      * ebattles: menu with links to Events, Teams, User Profile pages
      * eb\_activity: menu with list of latest gaming activity
# Step-by-step upgrade from former version #
  1. Get the plugin upgrade files on your server
    1. Download the plugin upgrade zip file
    1. Unzip the plugin
    1. Upload ebattles/ folder in e107\_plugins/
  1. Upgrade the plugin
    1. In e107, Admin Area>Plugin Manager, click on the eBattles "Upgrade" button

# Configure the plugin #
  * In Admin Area>Plugins>eBattles, you can change the plugin settings
    1. Configuration
      * Moderator userclass: determines which userclass has admin privileges on ebattles
      * Events creators userclass: determines which userclass has privileges to create events
      * Teams creators userclass: determines which userclass has privileges to create teams
      * Events delayed updates: If enabled, events stats will be recalculated only if the event has changed and the delay has expired
      * Tabs stylesheet: choose css style for tabs
      * Images max size: If enabled, images will be resized if they exceed the max size (can be slow)
      * Use Avatars: Specify the max size and pages where users avatar will be used in the tables displays
      * Default Avatar: choose default avatar image
      * Default Team Avatar: choose default team avatar image
      * Pagination default number of items per pages
      * Insert debug data in database: for debugging purpose only
      * Plugin disclaimer
    1. Links
      * Menu Heading: choose the header name for the eBattles links menu
    1. Recent Activity
      * Menu Heading menu: choose the header name for the eBattles recent activity menu
      * Number of Items to showthe number of items to show in the recent activity menu
      * Images max size