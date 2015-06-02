# Release 0.9.9 #
## New ##
  * Integration with **[Gold system](http://www.e107gold.com/news.php)** plugin version 4.0+ (fixes #79)
    * Gold system admin can set userclass allowed to set gold rewards/costs for eBattles events
    * Gold system admin can set a gold award for playing a match
    * Event admins can set a gold cost for event signup
      * In the case of team events, the captain of the team pays the gold cost.
    * Event admins can set a gold award for the event winner
      * In the case of team events, the captain of the team gets the gold award.
  * Added the **"challenge" button** at the end of each raw of the results table. (fixes #75)
    * Applies only if challenges are enable, and the user is allowed to challenge.
  * Added event option to **hide fixtures** from players ("Never" or "Until event is live") (fixes #77)
  * For fixtured events, do not stop signup if we reach the max number of players limit.
    * If checkin is enabled (checkin duration>0), checkin stops when event starts, or when the when we've reached the max number of players limit.
      * Then only the players who have checked in play in the event
    * If checkin is disabled, and max number of players limit is set, we delete players so that we are left with max number of players.
      * Players are chosen based highest seed first, then the earlier they joined.
  * Let event mods **edit the scheduled time** of scheduled matches (related to [issue #87](https://code.google.com/p/ebattles/issues/detail?id=#87))
  * Events **late signups** (fixes #90 and #50)
    * New event option to allow late signups. (until the max number of players limit is hit, or one game has been played.)
    * If this is disabled, players can not sign up once the event is started.
  * New event option to **enable or disable signups** (fixes #80)
    * If this is disabled, players can not sign up.
  * Added **Round-robin fixtures preview** in event admin page (fixes #89)
## Changed ##
  * UI: replaced draggable brackets by scrollbars
  * For fixtured events, players cannot quit, or be kicked once the event has started
  * Reset event puts it back to "draft" mode instead of "signup"
  * Added a check for db query in database class.

## Fixed ##
  * Do not show rank in tournaments match report (fixes #91)
  * Fixed a bug with fixtured event rounds titles and "best of" values are not initialized properly.
  * Fixed "Event/Team signup does not work on Firefox." (fixes #76)
  * Fixed compatibility issue with krooze arcade plugin (fixes #78)
  * Fixed missing player name in signup tab.
  * Fixed: could not add players to "draft" event.
  * Fixed: Should only be able to approve a match if it is pending.

# Release 0.9.6 #
## Added ##
  * Code cleanup, use functions to get competition type (Tournament/Ladder), and match players type (Players/Teams)
## Fixed ##
  * Round-Robin tournaments issues (fixes #73)
    * For rounds with "best of" matches more than 1, games are not scheduled after 2 games have been played.
    * Deleted matches are not re-scheduled.
    * Tournament will end if a game is played in the last round.
  * Tournament: match is played, but the brackets do not show the result. (fixes #74)
  * Fixed some issues in brackets theming

# Release 0.9.5 #
## Added ##
  * Support for Leagues (round-robin tournaments) (fixes #40)
    * New "Fixtures" tab in event manager
      * If fixtures is enabled, the plugin will automatically schedule matches when the tournament starts.
    * Round-robin and double round robin for 4 or 8 players.
  * Added a "checkin" phase to events, before the event starts.
    * Event admin can choose the checkin duration. If set to 0, the feature is disabled.
    * During checkin phase, players or teams who are signed up will have to check in at the event's main page.
    * At the end of the checkin phase, players/teams who have not checked in are deleted.
    * For team events, only the team captain can check the team in, and all the team members are checked in automatically.
    * Event admin has a list of players/teams who have already checked in, and can check players in himself.
## Changed ##
  * UI: no-wrap on buttons white-spaces
## Fixed ##
  * Fixed issue when using array\_multisort()
  * Teams: Error deleting team. (fixes #72)

# Release 0.9.4 #
## Added ##
  * Add the map selection to the Match schedule (fixes #65)
  * Team invite system (fixes #64)
  * Add placement awards when a ladder finishes. (fixes #68)
  * Improved notifications in ebattles menu to make it easier to see when you have a match pending.

## Changed ##
  * Improved Team divisions page.
    * Show only one game at a time, select the game by clicking on the game icon.
  * Disable the ladder awards for tournaments (fixes #67)
  * Should not be able to change the event's game if players/teams have joined the event. (fixes #63)
  * eBattles menu: make a link to profile matches tab for the pending matches, scheduled matches and challenges (fixes #69)
  * Tournaments: use standard template for double-elimination 8 players. (fixes #70)
    * Only new tournaments will be affected.
## Fixed ##
  * Fix issue with plugin menu link, when tag {USERID} is not set.

# Release 0.9.3 #
## Added ##
  * Added support for "double elimination" tournaments
    * upport 4 or 8 players
  * Added single elimination bracket files for 2, 32, 64 and 128 players
  * Added test script test/brackets\_test.php to generate bracket files.
  * Add red star on required form fields.
  * Match report improvements
    * For tournaments and events with match type for 2 teams (1v1, 2v2, ...)
    * The match report system is simplified
  * Enter match result (team 1 won, team 2 won, draw, team 1 forfeit, team 2 forfeit)
  * Enter players for both teams
  * Added links to Events and Teams in user's profile
  * Individual Ladder: Support to add all users in a userclass to an individual ladder
  * Tournaments: added support to delete matches.
    * Do not support editing tournament matches.

## Changed ##
  * Improved plugin security for database accesses
  * Improve match media list
  * Change "Back to" hyperlinks into buttons.
  * Change in matchs list, game icon should not be a link to the match.
  * Changed plugin link url, so it points to user's profile.
  * shadowbox-js (3.0.3) (refs #46)
  * Admins can see draft events in the events page
  * Need to create a gamer profile for the team division creator
  * Work on PM/emails notifications.
  * Updated eBattles theme, to have logo at top of page.
  * Tournaments: divide list of matches into one section per round.
  * Event Info: should display "Rules" after "Description".

## Fixed ##
  * Gamer profile
    * Players can now edit their gamers information (name, unique game ID) in the profile page.
  * "Show all past events" link is missing from events page.
  * Teams: Can't delete teams.
  * Fixed bug in match report forfeit handling, when there were more than 1 player per team.
  * Disclaimer hyperlink to eBattles.net gets corrupted on some sites
  * Fixed bug in Team ladders stats updates causing:
    * Fatal error: Cannot use object of type Clan as array in updateteamstats.php on line 300
  * Some textareas did not have tinyMCE text editor anymore.
  * Prevent adding players to started tournament from the event admin page.

# Release 0.9.2 #
## Features ##
  * Added support for tournaments seeding
    * Drag-and-drop players/teams to reorder seeds manually
    * Automatic random re-seeding
  * Gamer profile
    * Players can now edit their gamers information (name, unique game ID) in the profile page.

## Changes ##
  * Increased length of fields used to store external links
  * Some improvements in userinfo page
  * Tournaments: Send notifications to players (PM/email) when match is scheduled.
  * Tournament brackets: show "best of" for each round.
    * Each rounds "Best of" is shown if you hover over the rounds header in the bracket
  * Brackets do not show rounds without matchups

# Release 0.9.1 #
## Features ##
  * Added teams single elimination tournaments
## Changes ##
  * Updated language files
  * Tournaments: prevent edit/delete matches for now.
  * Change ebattles hyperlink in disclamer, and admin page to www.ebattles.net
  * In tournaments, matches are now scheduled by event admin rather than site admin.
## Bug Fixes ##
  * Fixed issue when event admin adds a team to event.
  * Fixed bug in clan info page, which did not show old events.
  * Event manager: could not change event's game.
  * Events list was not displayed properly

# Release 0.9.0 #
## Features ##
  * Added one player single elimination tournaments
    * Players sign up
    * Matchs are scheduled by the script
    * Results are shown in brackets.
## Changes ##
  * Use jquery and jquery ui for better form validation, better UI (modal forms, tabs, date picker...)
  * Better event and team creation forms
  * Using classes to handle database tables (OOP)
    * Objects are used to retrieve entries information from the database.
    * Construct uses the primary key to get data from database.
  * Added gamer table, so users can enter different name and game id for each game they play.
  * Improved security when saving data to database.
  * Added custom layout to ebattles theme

# Release 0.8.14 #
## Changes ##
  * Improved events filtering by games & match type
## Bug Fixes ##
  * Fixed bug in forfeit win/loss count.
  * Fixed bug in event score re-calculation.
    * Timestamp for awards was not correct.

# Release 0.8.13 #
## Features ##
  * Support for forfeit
  * Support for match types (1v1, 2v2, ...)

# Release 0.8.12 #
## Features ##
  * Max number of players/teams for events (Fixes [issue 81](https://code.google.com/p/ebattles/issues/detail?id=81))

## Bug Fixes ##
  * Fix issue in database tables creation with some versions of mySql

# Release 0.8.11 #
## Features ##
  * Added team awards (Fixes [issue 78](https://code.google.com/p/ebattles/issues/detail?id=78))
  * Added stats category "streaks" for teams events.

## Bug Fixes ##
  * Fixed typos

# Release 0.8.10 #
## Features ##
  * Improved eBattles links menu.
    * Added "create events" and "create team" button
    * Added number of
      * matches played
      * matches to approve
      * matches pending
      * matches scheduled
      * challenges requested
      * challenges not confirmed
    * Each of these info can be disabled from admin page.

## Changes ##
  * Added number of pending matches in "My Profile" page, Events tab, for events owners and mods.
  * Added "No-one" userclass to emails/pm notification possible choices.
  * Emails and pm notifications are now sent from user 0.
  * Moved "Create Team/Event" buttons at the top of the page, rather than after the lists of Teams/Events.
  * Changed look of buttons, added "simple" button class for buttons with image and no text.
  * Allow challenges withdrawal to event owners and mods only.

## Bug Fixes ##
  * Delete challenges when resetting event.php
  * Fixed issue where the reporter of a scheduled match could approve the match.
    * When a scheduled match was reported, the reporter was the user who had scheduled the match, rather than the user himself.

# Release 0.8.9 #
## Features ##
  * Added userclass for emails and PM notifications.
    * Can be changed from plugin admin area.
  * Added Czech translation (thanks godlion)

## Changes ##
  * Added notifications
    * when challenge is accepted or declined,
    * when a match is scheduled.

## Bug fixes ##
  * Fixed some w3c incompatibilities.

# Release 0.8.8 #
## Features ##
  * Notifications for event invitation or new challenge are now sent by email as well as PM. (Klinge)

## Changes ##
  * Allow negative points per loss (M@vrik)

## Bug fixes ##
  * Fixed disclaimer link display
  * Fixed Scheduled Match report button was not showing for participants in team ladders. (M@vrik)

# Release 0.8.7 #
## Changes ##
  * Prevent players from individually quitting clan events.
  * Improved plugin upgrade, added function array\_push\_associative()
  * In division page, captain go on top of members list. (Aaron)

## Bug fixes ##
  * Fix problem with medias display with IE8 (shadowbox issue)
  * Show challenger's comments in challenge confirmation form.
  * Fix issues in French translation
  * Fix for accepted challenge date.

# Release 0.8.6 #
## Features ##
  * Added "Scheduled Match"
    * Now matches can be scheduled by event admin and mods

## Changes ##
  * Display team's website and email address as hyperlinks.

## Bug fixes ##
  * Fixed bug with multiple maps selection, which was hardcoded to 2 maps max by mistake.

# Release 0.8.5 #
## Features ##
  * Added "Team Challenges system"
    * Now challenge system can be used in team ladders.
    * Any player of a team can challenge another team.
    * Any player of the challenge team can confirm the challenge.
  * Can select multiple maps per match (fixes [issue 92](https://code.google.com/p/ebattles/issues/detail?id=92))
    * Event admin can choose the max number of maps per match.
  * Added some fields for teams: website, email, IM, description.
  * Added team matches display in "My Profile"

## Changes ##
  * Event admin can choose the max number of dates per challenge.

## Bug fixes ##
  * Fixed problem in database creation during plugin installation for 0.8.4.
  * Fixed an issue with challenges comments not being submitted.

# Release 0.8.4 #
## Features ##
  * Added "Player Challenges system"
    * Features:
      * A player (the challenger) can challenge another player (the challenged) in the same ladder.
      * To request a challenge,
        * the challenger selects a player in the ladder and clicks on "Challenge player"
        * on the next page, the challenger selects a number of dates/times (now hardcoded to 3) and can leave a comment, then click "Send challenge".
        * A PM is sent to the challenged player.
        * The challenge appears in the challenger/challenged "My profile" page and in the event's page.
      * The challenger can "Withdraw" the challenge
      * The challenged can "Confirm" the challenge by accepting or declining it.
        * He can select one of the dates proposed by the challenger, and click "Accept"
        * He can click "Decline"
      * The confirmed challenge becomes a scheduled match. It can be played, and reported by either players.
    * Limitations (for now):
      * No support for Team challenges yet.
      * Only 1 option: Events admin can enable/disable challenges
    * Plan:
      * Add team challenges support
      * Add more options
        * How many simultaneous challenges allowed
        * Dates options
          * How many dates required
          * Minimum delay between request and challenge date
        * Points removed for declining challenge
        * Challenge expiration
          * Allow challenge to expire
          * Hours to expire after
          * Penalty for not accepting before expiration
          * Bonus for challenger
  * In match details, only display stats categories that have a non-zero max rating value. (Fixes [issue 85](https://code.google.com/p/ebattles/issues/detail?id=85))
## Fixed ##
  * Improved match info display.

# Release 0.8.3 #
## Features ##
  * Match editing (Fixes [issue 22](https://code.google.com/p/ebattles/issues/detail?id=22))
> Now match reporters can edit their matches if they are pending.
> Also, event admins, event moderators can edit matches.
  * Replaced submit buttons with nicer buttons with image/text/colors
## Bug fixes ##
  * Fixed issue with match approval.
    * The match reporter could see the approval button for teams events where match approval was allowed to opponents only.
  * Fixed Disclaimer floating on the right needed a 'clear'

# Release 0.8.1 #
## Features ##
  * Support for Danish language

# Release 0.8.0 #
## Features ##
  * Support match media submission
    * Now players can submit screenshots/replays/videos for the match they played.
    * Use Shadowbox.js (GNU version) to display match media
    * Plugin userclass for users allowed to submit media when they report a match.
  * Support for Games maps
    * Now, admin can add maps to games.
    * Users can select the match map when reporting a match
    * Plugin preference for maps image max size.
  * Support for Games factions
    * Now, admin can add factions/races to games.
    * Users can select the faction of each player when reporting a match
## Changes ##
  * In events list, sort columns by user selected value, then by latest event created.
  * Use new function getImagePath() to get images path
  * Added Starcraft 2 in games list, and maps/factions as an example.
  * For Team Ladders, members of the teams are allowed to report matches, instead of team captains.
  * For Team Ladders, added to option for members of a team to approve the matches reported by an opponent.

# Release 0.7.229 #
## Features ##
  * Changed teams stats calculation for team ladders.
    * Before, teams stats were calculated as the sum of the players stats.
    * Now they are updated after each match based on the match results.
    * So for example, if a team of 2 players wins a 2v2.
    * Before, the team would have 2 games, 2 wins.
    * Now, the team will have 1 game and 1 win.
  * Moved Games management pages to eBattles admin area (Fixes [issue 79](https://code.google.com/p/ebattles/issues/detail?id=79))
  * If the user is viewing a particular event information, the "Recent Activity" menu now only shows information about that event.
## Changes ##
  * Remove usage of getimagesize() function, which can be too slow for remote urls
  * For teams division creation, show the list of all the available games, rather than only the games used in one or more events.
## Bug fixes ##
  * Fixed players wins/losses/draws/points calculation.
    * Before, players would get one win for every player that is ranked below them. In a 2v2 match, that would result in players getting 2 wins or 2 losses.
    * Now, players get one win for every match team that is ranked below them. In a 2v2 match, the players get 1 win or loss. In a 1v1v1, the first player gets 2 wins, the 2nd gets 1 win, 1 loss, the last player gets 2 losses.
  * Replace hardcoded status "Member" in teams divisions tab with "Member" or "Captain".
  * Fixed HTML div not properly closed in eventinfo page
  * Fixed: Number of matches to be ranked stuck to 4 for Team ladders
  * Fixed error messages in the event page when all the stats categories are set to "Info Only"

# Release 0.7.212 #
## Features ##
  * Changed: Use Points instead of Points Average for ranking calculation
## Bug fixes ##
  * Fixed: Ranking of teams was not done properly for Team Ladders.
  * Fixed: Event update was calling updateStats() function for Teams Ladders.

# Release 0.7.210 #
## Features ##
  * Improved standings tables sorting (Fixes [issue 74](https://code.google.com/p/ebattles/issues/detail?id=74))
  * Added: In Team ladder match report, check if 2 teams are the same
## Bug fixes ##
  * Fixed bug: In Team ladders, match delete was not working.
    * Added deleteTeamsMatchScores() function to handle team ladders match delete.
    * Renamed deleteMatchScores() to deletePlayersMatchScores() for other ladders
  * Fixed bug: resetTeam() function was not working properly
  * Fixed bug: In Team ladders, "Recalculate Event Stats" was not working properly

# Release 0.7.208 #
## Features ##
  * In Match report, assign the score per player instead of per match team (Fixes [issue 75](https://code.google.com/p/ebattles/issues/detail?id=75))
## Bug fixes ##
  * Fixed bug: There was a bug in the players ranking calculation for events using "Classic" ranking type.
    * The Player who should have the last position was set as "Not ranked"

# Release 0.7.204 #
## Bug fixes ##
  * Fix bug: Event admin could not change the event type to "Team/Individual Ladder"

# Release 0.7.203 #
## Features ##
  * New Ladder type "Team"
    * Details:
      * This ladder type is for teams only.
      * Unlike in Individual and Teams/Individual Ladders, in Team Ladder matches are not opposing players, but teams.
      * This format is targeted to team sports leagues (Soccer, ...), and FPS games.
  * Added new Ranking Type
    * Now Event admins have the choice between 2 ranking types:
      * Classic: Players are ranked based on their score in the first stats category, if 2 players are tied, they are ranked based on the second stats category, and so on<br />
      * Combined Stats: Players are ranked based on a combination of their scores in each stats category
        * This is the ranking type that was used in previous versions of the plugin.
  * Automatically add the clan owner to the divisions he creates.
  * For games drop-down list used in events, eventpast and clanmanage pages, only show games used in existing events.
  * Improved SVN pre-commit batch to get latest commit revision correctly.
  * Improved Players/Teams standings table headers.
## Bug fixes ##
  * Fix bug: In team admin page, only show the "Add division" button if there are games used in an event.
  * Fix bug: Fixed position of matches pending in event admin page, "Settings" tab.

# Release 0.6.185 #
## Features ##
  * Added new userclass option for Match Approval: "Match Reporter Opponent"
    * If this option is chosen in the event admin panel, any opponent of the reporter of a match can approve the match.
  * Improved match info display.
    * Added a "magnify" icon with a link to the match details page.
    * Added an "exclamation" icon with a link to the match details page if the match is pending and the user is allowed to approve it.
  * Added avatars in match details page.
## Bug fixes ##
  * Fix bug: in "Recent Activity" menu, matches were not shown in the list because the match time was not retrieved properly.
  * Fixed typo: In User profile page, "Events" tab, "Owner" table, changed the text of the link to event manage page from "Status" to "Manage".
  * Fix title of Clan Create page.

# Release 0.6.180 #
## Features ##
  * Added new userclass option for Match Approval: "**Match Reporter Opponent**"
    * If this option is chosen in the event admin panel, any opponent of the reporter of a match can approve the match.
  * Improved match info display.
    * Added a "magnify" icon with a link to the match details page.
    * Added an "exclamation" icon with a link to the match details page if the match is pending and the user is allowed to approve it.
  * Added avatars in match details page.
## Bug fixes ##
  * Fix bug: in "Recent Activity" menu, matches were not shown in the list because the match time was not retrieved properly.
  * Fixed typo: In User profile page, "Events" tab, "Owner" table, changed the text of the link to event manage page from "Status" to "Manage".
  * Fix title of Clan Create page.

# Release 0.6.179 #
## Features ##
  * Added table listing the event's Teams in the Event Admin page, "Event Players" tab for Team Ladders
  * Added Spanish language pack (courtesy of DelTree)
  * Added some missing strings translations.
## Bug fixes ##
  * Fix [issue 67](https://code.google.com/p/ebattles/issues/detail?id=67): HTML Error in Match Info
    * A "div" tag was not properly closed.
  * Fix [issue 68](https://code.google.com/p/ebattles/issues/detail?id=68): Plugin uninstall does not delete comments and ratings
    * Added uninstall functions purgeComments() and purgeRatings() to clean up comments/ratings during plugin uninstall
  * Fix [issue 69](https://code.google.com/p/ebattles/issues/detail?id=69): Wrong link in Events tab on Team Info page
    * Syntax error in link to event.
  * Fix [issue 70](https://code.google.com/p/ebattles/issues/detail?id=70): Event Matches counter is wrong
    * Event info match counter was counting "pending" games.
  * Fix bug: Match display function was showing a link to a wrong event.
  * Fix bug: When match approval is disable, matches status were not set to "active" automatically after submission.
  * Fix bug: "Quit Event" button is not working
  * Fix "Recent Activity" text non wrapping correctly on narrow menus.
    * Replaced non-breaking spaces by regular spaces so text can wrap.
  * Fix bug: Quick match report players drop-down list did not always show the correct players.

# Release 0.6.175 #
## Features ##
  * Added **Match Submission/Review/Approval** (Fixes [issue 53](https://code.google.com/p/ebattles/issues/detail?id=53))
    * Now Event admin can choose to
      * enable/disable match approval
      * who can review/approve pending matches (Event Admin/Event Mods/No One).
    * If the feature is disabled, a match is "active" as soon as they are reported.
    * If the feature is enabled, a match will be "pending" until a reviewer approves it.
  * Added eBattles plugin disclaimer.
  * Added plugin link for main menu.
## Bug fixes ##
  * Fixed eBattles theme issue
    * When using French language, the page was blank, because the language file was required and missing.

# Release 0.6.172 #
## Features ##
  * Add support for **Team's avatar** (Fixes [issue 50](https://code.google.com/p/ebattles/issues/detail?id=50))
    * eBattles admin can:
      * enable/disable Teams avatars in "Teams Standings" (Teams event info page) and "Teams list" page.
      * choose the default Team avatar
    * Team owners can choose an avatar for their team.
  * Added Admin Help Panel (with plugin logo, version and important links)
  * Improved eBattles theme news style.
## Bug fixes ##
  * Fixed "Event Name" when event is created.
    * Was using a wrong language define.
  * Fixed "Sometimes Username does not show up in the name of newly created events." (Fixes [issue 66](https://code.google.com/p/ebattles/issues/detail?id=66))
  * Fixed some W3C XHTML 1.1 compliance problems

# Release 0.6.167 #
## Bug fixes ##
  * Bug fix: Error in Team Events stats update was preventing Team events page to be displayed.

# Release 0.6.164 #
## Features ##
  * Multi-language support and French translation (Fixes [issue 63](https://code.google.com/p/ebattles/issues/detail?id=63))

# Release 0.5.161 #
## Features ##
  * Added "Check for plugin update" support for admin. ([Issue 65](https://code.google.com/p/ebattles/issues/detail?id=65))
    * Using [e\_version](http://wiki.e107.org/?title=Eversion) plugin from Father Barry
    * Now admins can check if their installation of eBattles plugin is the up-to-date directly from the plugin admin menu.
  * Added "Opponent Rating" for events players. ([Issue 52](https://code.google.com/p/ebattles/issues/detail?id=52))
    * Use e107 star rating system.
    * A player can now go to a match page and rate his opponents.
    * The player's Opponent Rating can be seen in the player's profile on the "Events" tab.
  * Added getClanName() function to get a clan's name/tag from the player's team id.
    * Also replaced underscore by space to separate clan tag and player's name in teams events.
## Bug fixes ##
  * Fixed bug: clan tag was displayed in matches lists for non team events.

# Release 0.5.152 #
## Features ##
  * Added support for event players management with add/kick/ban functions (Fixes [issue 1](https://code.google.com/p/ebattles/issues/detail?id=1) and [issue 51](https://code.google.com/p/ebattles/issues/detail?id=51))
    * Added new "Event Players" tab is event management page
    * "Add Player" or "Add Team" (for team events) button with a player/team drop-down selector and PM notification checkbox.
      * Note: PM notification is not yet implemented.
    * Paginated list of players with actions:
      * Ban:
        * You can temporarily ban/unban a player.
        * A banned player can no longer play in the event or sign up to the event during the duration of the ban.
      * Delete players games
      * Delete players awards
      * Kick:
        * Admin can kick (delete) a player who has no games played and no awards.
        * A kicked player can re-signup to the event.
  * Match Info added a column to show the players "Points"
## Bug fixes ##
  * Fix: Players should be able to submit a game only if they participated in that game. (Fixes [issue 61](https://code.google.com/p/ebattles/issues/detail?id=61))
  * Fix: Number of games played wrong in "Recent Events", when one or more matches have been deleted from the event (Fixes [issue 60](https://code.google.com/p/ebattles/issues/detail?id=60))
  * Fixed bug: "Match Report" button not visible to event players (Fixes [issue 59](https://code.google.com/p/ebattles/issues/detail?id=59))
    * Problem:
      * "Match Report" button should be visible to event players in the PLayer
      * Standings tab of the event, when the event is on going.
      * This is broken for events created after release 107 when "userclass for match report" was introduced.
    * Fix:
      * Fixed the match report userclass check
      * Set default match userclass check to "Event Player" (1) instead of 0.

# Release 0.4.147 #
## Features ##
  * Changed default image size for recent activity menu from 8 to 16 px
## Bug fixes ##
  * Fixed Avatars not showing in Event Player Standings.
    * Was broken since [revision 144](https://code.google.com/p/ebattles/source/detail?r=144).
    * Fix: added global access to $pref in updateStats()
  * Fixed issue in Event stats recalculation.
    * On servers where flush() does not work, the calculation may timeout if the number of matches is great.
    * Split recalculation into batches of 10 matches to avoid timeouts.
  * Improved handling of textareas in forms. (Fixes [issue 9](https://code.google.com/p/ebattles/issues/detail?id=9) and [issue 58](https://code.google.com/p/ebattles/issues/detail?id=58))
    * There were several issues with textareas:
      * line breaks were not saved to database
      * input containing quotes may cause an error when saving data.
      * data was not properly filtered before it was saved to database, or displayed as HTML.
      * no support for BBcode.
    * Now we use the e107 functions to handle textareas.

# Release 0.4.144 #
## Features ##
  * Added an Event Stats re-calculation" in Event Manage page (Fixes [issue 54](https://code.google.com/p/ebattles/issues/detail?id=54))
    * This function resets players stats and awards and re-compute all stats match by match.
    * This function is helpful if the players data needs to be recalculated.
  * Added "Images max size" admin preference for "Recent Activity" menu. (Fixes [issue 48](https://code.google.com/p/ebattles/issues/detail?id=48))
  * For matches/awards lists, use left aligned tables, with 1 row per item.
  * Use more single quotes to output HTML text. ([Issue 20](https://code.google.com/p/ebattles/issues/detail?id=20))
  * Match report, prevent players of the same team to play against each other. (Fixes [issue 4](https://code.google.com/p/ebattles/issues/detail?id=4))
  * Added awards list in Players profiles (userinfo). (Fixes [issue 16](https://code.google.com/p/ebattles/issues/detail?id=16))
## Bug fixes ##
  * Fixed bug in Trueskill calculation (Fixes [issue 55](https://code.google.com/p/ebattles/issues/detail?id=55))
    * Problem: (reported by planettoon) The calculation of scores can be erroneous, with wrong ELO and Trueskill.
    * Cause: the erf (Gauss error function) approximation was not accurate enough
      * That lead to a negative value as the input of a square root.
      * That will interrupt the TS and scores calculation.
    * Fix: Use Rational Chebyshev Approximations for the Error Function.
  * Time/date was not handled properly (Fixes [issue 56](https://code.google.com/p/ebattles/issues/detail?id=56))

# Release 0.4.135 #
## Features ##
  * Added support for Matches comments using e107 comments system.
    * Show a link to matches comments in all matches lists.
    * Added support for eBattles matches comments display in user info and list\_new plugin comments lists.
  * Improved Games management page.
    * Now, admin is presented with the paginated list of all the games.
    * From there, he can add/delect/update/edit one, all or selected games.
  * Added Admin configuration for eBattles links menu and Recent Activity menu.
    * eBattles links preference: Menu Heading
    * Recent Activity preferences: Menu Heading and number of items to be shown.
  * Added eBattles 32x32 icon.
  * Added poker and chess to the games list.
  * Changed time display in all matches lists.
## Bug fixes ##
  * Fixed vertical alignment issue in pagination items with Internet Explorer.

# Release 0.4.130 #
## Features ##
  * In Recent Activity menu, limit the number of recent events to show.
## Bug fixes ##
  * Cleanup HTML and CSS for W3C XHTML 1.1 compliance
  * Fixed bug in matches lists.
> In case a match is deleted, the match is still in the database, but its scores are deleted.
> So when displaying matches lists or number of matches, we need to exclude matches with no scores.

# Release 0.4.127 #
## Features ##
  * Added Avatars support for event's players standing. (Fixes [issue 39](https://code.google.com/p/ebattles/issues/detail?id=39))
> Site admin can choose:
    * the max size of avatars.
    * the pages where avatars will be enabled (for now only players standings)
    * default avatar, for users with no avatar.
> Added 3 default avatars.
## Bug fixes ##
  * SQL query fails for servers where decimal format is using decimal comma instead of decimal point (Europe). (Fixes [issue 47](https://code.google.com/p/ebattles/issues/detail?id=47))
> Added the new floatToSQL() function which formats floats before they are sent to SQL.
  * Fixed problem in matchs lists display. "defeated" or "tied" is now based on the rank of current player.
  * Fix bug in plugin upgrade.
> Now accumulate tables/prefs upgrades based on revision, instead of just taking last tables/prefs upgrades.
  * Some servers can block fopen() access to remote URLs.
> Error: "Failed to open stream: Permission Denied".
> This causes getimagesize() and thus imageResize() to return nothing.
> Work-around: in that case, imageResize() will return width=target\_width.
  * Fixed height issue in "eBattles" and "Dark" tabs CSS themes
  * Fixed up/down arrow selector to sort Events list, which was not working in Internet Explorer.


# Release 0.4.123 #
## Features ##
  * New pagination system.(Fixes [issue 46](https://code.google.com/p/ebattles/issues/detail?id=46))
    * "Digg" style pagination
    * This class provides pagination, with drop-downs selectors for "Go to page" and "Items per page"
    * Used paginator class from http://www.catchmyfame.com/2007/07/28/finally-the-simple-pagination-class
    * Added plugin preference setting for the number of items per pages used for pagination.
  * Events list sorting

# Release 0.4.119 #
## Features ##
  * Fixed tabs default theme "height" issue.
The tab height was different for different e107 themes or on some different browsers.
## Bug fixes ##
  * The plugin installation was failing starting from [revision 98](https://code.google.com/p/ebattles/source/detail?r=98). (Fixes [issue 45](https://code.google.com/p/ebattles/issues/detail?id=45))

# Release 0.4.116 #
## Features ##
  * "Team/Division delete" support (Fixes [issue 32](https://code.google.com/p/ebattles/issues/detail?id=32))
Added support to delete divisions/teams from team manage page.
Division/Team can only be deleted if there are no players who have already played in the division/team.
  * Added "Quit Division" button for members of a division in a team. (Fixes [issue 44](https://code.google.com/p/ebattles/issues/detail?id=44))
  * Changed plugin menus header to show info about the event or team (Fixes [issue 35](https://code.google.com/p/ebattles/issues/detail?id=35))
Also removed html tags `<h1`> and `<h2`> as they might appear to big for some themes.
  * Added option for eBattles admin to resize all games images if they exceed the maximum size allowed. (Fixes [issue 42](https://code.google.com/p/ebattles/issues/detail?id=42))
  * Added confirmation dialog when user quits/deletes an event or a team.
## Bug fixes ##
  * eBattles moderators could not manage teams.
  * Can not reset team password when it has been set. (Fixes [issue 40](https://code.google.com/p/ebattles/issues/detail?id=40))

# Release 0.4.106 #
## Features ##
  * Userclass for match report (Fixes [issue 36](https://code.google.com/p/ebattles/issues/detail?id=36))
> Now event owner can choose who is allowed to report matches in the event.
> "Match Report userclass"
    * Owner: Owner only can report matches
    * Moderator: Owner & Moderators can report matches
    * Player: Owner, Mods & Players can report matches
  * Quick Loss report enable/disable
> Now event owner can choose to enable or disable quick loss report in the event.
  * Change Event owner in Event Manage
> Now event owner can be changed from Event Manage page.

# Release 0.4.101 #
## Features ##
  * Players can now "quit" events if they have not played yet. (Fixes [issue 31](https://code.google.com/p/ebattles/issues/detail?id=31))
  * Tabs themes:
    * Changed default tabs theme to remove background so it can work with any theme
    * Added eBattles tabs theme
  * Added "Team password" support (Fixes [issue 30](https://code.google.com/p/ebattles/issues/detail?id=30))
> The team password is used when user tries to join a division of that team.
  * Added "Hide Ratings column" checkbox in Event manager. (Fixes [issue 37](https://code.google.com/p/ebattles/issues/detail?id=37))
> This is used to hide/unhide the ratings column in events standings.
> It can be useful if only one scoring category is used.
  * Added a link back to the event in eventmatchs.php
## Upgrade Notes ##
  * This release has changes in database.
  * The plugin settings will be reset when upgrading.

# Release 0.3.92 #
## Features ##
  * Add plugin preferences settings for tab theme. (Fixes [issue 34](https://code.google.com/p/ebattles/issues/detail?id=34))
> The site admin can now choose the tabs theme between a choice of pre-defined themes.
    * default (ebattles light color default)
    * dark
    * webfx
    * luna
    * windows classic

# Release 0.3.91 #
## Features ##
  * Add plugin preferences settings for Events/Teams creators userclasses (Fixes [issue 33](https://code.google.com/p/ebattles/issues/detail?id=33))
> The site admin can now decide which class of users are allowed to create teams/events