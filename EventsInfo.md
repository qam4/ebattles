# Event information page #
  * Events are listed in the "Events" page
  * To sign-up to an event:
    * Log in
    * Go to the Event page, "Sign up" tab
    * Click on "Sign up" button

## Match reporting ##
eBattles supports any kind of multiplayer matches.

Any number of teams and any number of players per team is allowed: 1v1, 2v2, 1v1v1, 3v1...
  * To report a match:
    * Log in
    * Go to the Event page, "Player Standings" tab
    * Click on "Quick loss report" or "Match Report" button
      * Quick Loss report: Choose an opponent in the drop-down list and click on "Submit Loss"
      * Match report:
        * Select the number of players and teams
        * Select the players and their respective team
        * Enter the score of each player (if applicable).
        * Enter the faction of each player (if applicable).
        * Select the rank of each team
        * Enter the map for the match (if applicable).
        * Check the draw checkbox if rank is same as previous team (if applicable)
        * Add games comments

## Ladders types ##
There are 3 type of ladders available to event admins
  * _Individual Ladder_:
    * Players vs. players matches
  * _Team/Individual Ladder_:
    * Players vs. players matches.
    * Players must be member of a team to play.
    * The score of each player goes toward his teams score.
  * _Team Ladder_:
    * Teams vs. teams matches.
    * This ladder type is for teams only.
    * Unlike in Individual and Teams/Individual Ladders, in Team Ladder matches are opposing teams, not players.
    * This format is targeted to team sports leagues (Soccer, ...), and FPS games (clan wars).

## Rankings ##
There are 2 types of ranking calculation available to event admins:
  * _Classic_:
    * Players are ranked based on their score in the first stats category, if 2 players are tied, they are ranked based on the second stats category, and so on.
  * _Combined Stats_:
    * the rating calculation is based on each players score in a set of ranking categories.
    * Each category is assigned a max value which is attributed to the player who scores best in that category.
    * Other players get a portion of the max value, proportional to their score.
    * Overall score is the sum of the categories scores.

## List of supported statistics categories ##
  * Requirements to rank
    * Games required for player/team rating.
    * Number of players required for team rating
  * Scoring Catagories
    * Overall Rating
    * ELO
    * [TrueSkill™](http://research.microsoft.com/en-us/projects/trueskill/)
    * Games played
    * Wins, Losses, W/D/L, Victory ratio (W/L), Victory % (100\*W/Games Played)
    * Unique opponents
    * Opponent average ELO
    * Rank delta, with up/down arrow next to rank
    * streak of victory & loss
    * Avg score per game
    * Avg enemy score per game
    * Avg score difference per game
    * points