ReadMe.txt
-----------

Features
1. Events.
   a. Event Admin/Creation pages.
      - Manage:
         . Event summary
            -> Owner
            -> Moderators
         . Event settings
            -> Name
            -> Password to join
            -> Game
            -> Type -> 1v1 or team ladder
            -> Enable/disable scoring, and draws
            -> Dates (start/end)
            -> Description
         . Event rules
            -> Rules
         . Event reset
            -> reset players/teams, reset scores, deleteevent
            -> Stats coeffs 
               -> nbr_games_to_rank, nbr_team_games_to_rank
               -> % for each stats categorie
               -> "Info only" mode, if you want to show the stat but not count it in results.
               -> TBD: ELO K, ELO M, ELO_default (can't be changed if one player has signed up)
               -> TBD: TS beta, epsilon (can't be changed if one player has signed up)
         . TBD: Ban/Kick Players/Teams

2. Events Info: Results/Ranking/Ratings
   a. List of categories:
      - Requirements to rank
        . Games required for player/team rating.
        . Nbr of players required for team rating
      - Scoring Catagories
        . Overall Rating
        . Games played
        . Wins, Losses, W/D/L, Victory ratio (W/L), Victory % (100*W/Games Played)
        . Avg score/points per game
        . Avg enemy score/points per game
        . Unique opponents (absolute, %)
        . Avg rating played
        . ELO
        . Rank delta, with up/down arrow next to rank
        . streak of victory & loss
        . score
        . score difference
        . points 
Suggestions:
- rank delta could be accumulated (like streaks) 
   - until delta is changing sign. if old_delta*delta > 0 -> ...
   - or since last match...
           
3. Match report
Suggestions:
   a. Need improved error checking when match report.
      - Need to check all possible cases.
   b. ELO calculation.
      Maybe need to multiply K/M by the number of players per team, since ELO of a team is sum of players ELOs.
   c. Sort names alphabetically, not by rank in the list boxes?
   d. Clanmates should not be able to play against each other.

4. Teams
   a. Team Admin/Creation pages
      - Kick
      - TBD: Ban
      - password to join
   b. Divisions Admin page ?

5. User Account
   a. TBD: Xfire?
   b. TBD: Team chooser, for each Game?

6. Awards
 - player took 1st place
 - player got into top 10
 - player streaks: 5/10/25 games won in a row

7. tinyMCE
- Images, Emoticons

8. Misc

9. Credits
  - DHTML calendar 1.0 - http://www.dynarch.com/projects/calendar
  - TinyMCE 2.1.0 - http://tinymce.moxiecode.com/
  - Tab Pane 1.02 - http://webfx.eae.net
  - TrueSkill™ Ranking system - http://research.microsoft.com/mlp/trueskill/
  - Pagination "Multiple Pages of Data from a Text File" - http://www.codewalkers.com/c/a/Database-Articles/Multiple-Pages-of-Data-from-a-Text-File/
  - Tigra Slider Control - http://www.softcomplex.com/products/tigra_slider_control/
  - Games icons - xfireplus.com
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
- Should use md5 for event passord
- Security: check every textarea / input style="text" and see if we use htmlspecialchars when treating the output.
- Team events: 2 players of same team should not be able to compete against each other. fixed?
- Use "name" field of submit buttons to differentiate them, avoid using hidden input.
- Match delete: do we need to update "TBL_TEAMS"?
- team "rank delta" not reset after a match in team ladder
- a player was created with user ID = 0
- activity lists matchs with no scores.
- 

SUGGESTIONS:
- Need to be able to delete Teams, or kick division members.
- Teams
  . rankdelta (up/dn arrow)
  . streaks?
- Draws
 . add TS calculations for draw case
 . Manage event, add "Draw proba"
 . matchinfo should have TS match rating
 . When draw occurs, winning or losing streak ends.
- Scores
 . quick loss report -> no score???
- Awards
 . Need to see awards in user profile.
- Team skill != average of players skills.
- Join button is difficult to find

SQL database
- Queries:
 . eventinfo -> . 35 + 2 if signed up
 . updatestats: 3 + 2*players + 2* matches