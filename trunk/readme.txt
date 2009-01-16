ReadMe.txt
-----------

Known issues/improvements:
1. Teams
   a. Team Admin/Creation pages
      - Ban, Kick (done)
      - password to join
   b. Divisions Admin page ?

2. Events.
   a. Event Admin/Creation pages.
      - Manage:
        -> Name
        -> Game
        -> Type -> =ladders
        -> Rules
        -> Description
        -> Owner
        -> Mods
        -> Dates
        -> Stats coeffs 
           - ELO K, ELO M, ELO_default (can't be changed if one player has signed up)
           -> % for each stats categorie
           -> nbr_games_to_rank, nbr_team_games_to_rank
   b. Awards
   c. Password to join (done)
   d. Ban/Kick Players/Teams
   
3. Match report
   a. Need improved error checking when match report.
      - Need to check all possible cases.
   b. ELO calculation.
      Maybe need to multiply K/M by the number of players per team, since ELO of a team is sum of players ELOs.
   c. Sort names alphabetically, not by rank in the list boxes?
   d. Clanmates should not be able to play against each other.

4. User Account
   a. Xfire?
   b. Signature?
   c. Team chooser, for each Game?
   d. time user joined

5. Results/Ranking/Ratings
   a. List of categories:
      - Requirements to rank
        . Games required for player/team rating.
        . Nbr of players required for team rating
      - Scoring Catagories
        . Overall Rating
        . Games played
        . Wins, Losses, Victory ratio (W/L), Victory % (100*W/Games Played)
        . Avg score/points per game
        . Avg enemy score/points per game
        . Unique opponents (absolute, %)
        . Avg rating played
        . ELO
      - Improvements
        . Add previous rank, to be able to add up/down arrow next to rank
        . streak of victory or loss

6. PM system

7. Better use of tinyMCE
- Images, Emoticons

8. Misc
- Add tables sorting (by links in the column header)
  &sortby=field_(up/down), field = Rank, Players, ...

9. Credits
  - DHTML calendar 1.0 - http://www.dynarch.com/projects/calendar
  - TinyMCE 2.1.0 - http://tinymce.moxiecode.com/
  - Tab Pane 1.02 - http://webfx.eae.net
  - ToolMan DHTML Library 0.2 (Drag & Drop Sortable Lists ) - http://tool-man.org/
  - PHP Login System with Admin Features (jpmaster77) - http://evolt.org/PHP-Login-System-with-Admin-Features
  - TrueSkill™ Ranking system - http://research.microsoft.com/mlp/trueskill/
  - Pagination "Multiple Pages of Data from a Text File" - http://www.codewalkers.com/c/a/Database-Articles/Multiple-Pages-of-Data-from-a-Text-File/
  - Tigra Slider Control - http://www.softcomplex.com/products/tigra_slider_control/
  - Games icons - xfireplus.com
  - Table CSS designs - http://www.robertdenton.org/reference/css-tables-tutorial.html
  - PM system - http://www.phpfreaks.com/tutorials/148/0.php
  - Photoshop/Design - http://www.empiredezign.com, http://www.tutorialstream.com
  - AutoSitemap - http://www.autositemap.com
  - XpertMailer (XPM4 v.0.2) - http://www.xpertmailer.com
 
BUGS:
- In eventinfo.php, the players standings table should not count players if user does not exist.
- Need to make sure the test to see if ppl can report/modify/delete is performed inside the corresponding file.
- If you are logged out and look at a team with no member, but a division, this division does not appear.
- Forgot password won't work, because we can't send emails...
- Jawad managed to report a game as a guest
  To reproduce this, go to the match report page in a tab, and logout in another tab.
  When you Submit, the match, it will be as a Guest.
- Problem when editing include/main.php in UltraEdit.
  UltraEdit adds "0xFEFF" signature (BOM) at the beginning of the file, creating "header already sent" issues.

SUGGESTIONS:
- Don: need to sign up everybody in a team when the captain signs up the team.
- Need to be able to delete Teams, or kick division members.
- Database dates should be GMT, and display dates should be user local time.


----------------------------------------------------------------------------------------
            <div id="main">
                <div class="news">           
                <h2>Welcome to eBattles</h2>
                <br />
                <p>
                <b>eBattles</b> is a <b>FREE host for Gaming Ladders</b>.<br />
                </p>

                <p>
                eBattles is the perfect way to organize simple ladders for your gaming clan!
                </p>

                It features:
                <ul>
                <li>Fully automated <b>Ladder Ranking System</b>.</li>
                <li>Suitable for <b>all game types</b> especially action games, card and board games and strategy games.</li>
                <li>Create unlimited <b>Teams</b> and <b>Individual</b> Ladders.</li>
                <li>Create unlimited Teams with Divisions for each game.</li>
                <li>Fully automated sign-up and validation system.</li>
                <li>Player login to report matches, change account details.</li>
                <li>Teams/Players ranked using complex ranking algorithm for more accurate ranking. (ELO, ...)</li>
                <li>A Private Messages system for communication between players.</li>
                </ul>
<!--
# Fully automated ladder ranking system.
# Teams/Players ranked using complex ranking algorithm for more accurate ranking.
# Installed easily in minutes.
# Fully customizable design.
# Suitable for all game types especially action games, card and board games and strategy games.
# Built in theme system allowing you to switch between different layouts and designs in seconds.
# Design your own themes easily, if desired.
# Alternatively, integrate the ladder code into your web site design. Html knowledge required.
# Create unlimited ladders for team play or 1v1 play. Users need only sign up once.
# Advanced Admin system with separate admin accounts for each admin.
# Multiple admin access levels. Assign varying access rights to different admin accounts.
# Admins can report matches and edit challenges.
# Higher level admins can ban ips, edit/create ladders and edit/create admin accounts..
# Fully automated and highly advanced challenge feature.
# Each ladder can be set by you to be challenge-only, open-play only or both together.
# Rank, last rank, wins, losses, games played, streak, win % etc. recorded for each player account.
# Full contact info for each player and team account including email, website, MSN, AIM, Yahoo etc.
# Personal profile for each player and team including all their contact information, stats and logo.
# Fully automated sign-up and validation system.
# Completely automated ladder system, standings and statistics updated immediately after report.
# Player login to report matches, change account details and make challenges.
# Team leaders can login and invite/add new members to their team account.
# Match page listing all recent match results and details.
# Lightning fast MySql database.
# Rules and staff page can be changed from admin control panel.
# Search page allowing users to search for individuals or teams.
# Newsletter function allowing admins to email all users on the ladder system.
# They are yours to do with as you please provided you do not violate our terms of service.                 
-->
               
                <p>
                If you want to try this site, please login with 'test' username (password=test).<br />
                You can also sign up.<br />
                </p>
                
                You can then:
                <ul>
                <li>Create/Moderate/Join Events from "My Account" menu.</li>
                <li>Participate in <a href="events.php">Events</a>, by submitting matches ("Quick Loss report" for simple 1v1 matches, or "Match Report" for other games).</li>
                <li>Create/Moderate/Join <a href="clans.php">Teams</a>.</li>
                </ul>