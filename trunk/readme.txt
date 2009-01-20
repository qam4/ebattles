ReadMe.txt
-----------

Known issues/improvements:
1. Teams
   a. Team Admin/Creation pages
      - Ban (not done), Kick (done)
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
        . Rank delta, with up/down arrow next to rank
        . streak of victory & loss
      - Improvements

6. Better use of tinyMCE
- Images, Emoticons

7. Misc
- Add tables sorting (by links in the column header)
  &sortby=field_(up/down), field = Rank, Players, ...

8. Credits
  - DHTML calendar 1.0 - http://www.dynarch.com/projects/calendar
  - TinyMCE 2.1.0 - http://tinymce.moxiecode.com/
  - Tab Pane 1.02 - http://webfx.eae.net
  - ToolMan DHTML Library 0.2 (Drag & Drop Sortable Lists ) - http://tool-man.org/
  - TrueSkill™ Ranking system - http://research.microsoft.com/mlp/trueskill/
  - Pagination "Multiple Pages of Data from a Text File" - http://www.codewalkers.com/c/a/Database-Articles/Multiple-Pages-of-Data-from-a-Text-File/
  - Tigra Slider Control - http://www.softcomplex.com/products/tigra_slider_control/
  - Games icons - xfireplus.com
  - Table CSS designs - http://www.robertdenton.org/reference/css-tables-tutorial.html
  - Photoshop/Design - http://www.empiredezign.com, http://www.tutorialstream.com
 
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
- Do not have password check for "join team event"
- Should use md5 for event passord
- Security: check every textarea / input style="text" and see if we use htmlspecialchars when treating the output.

SUGGESTIONS:
- Don: need to sign up everybody in a team when the captain signs up the team.
- Need to be able to delete Teams, or kick division members.
- Database dates should be GMT, and display dates should be user local time. (done)

