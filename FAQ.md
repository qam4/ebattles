# General #

Add your content here.


# Features #
## Scoring ##
Here is how the rating is calculated in eBattles:
  * An event administrator can edit/manage/customize the event he created by going to the event's page, "Event Info" tab, and follow the "_Click here to Manage event_" link.
  * In the "Event stats" tab, the event administrator has 13 categories to choose from. (ELO, TrueSkill, number of games played, Win/Loss ratio, ...)
  * For each of these categories, the admin chooses a "_maximum rating_" value.
  * That max value is attributed to the player who scores best in that category.
  * The player who scores worst in that category get 1.
  * Other players get a portion of the max value, proportional to their score compared to the best & worst players.
  * Overall score is the sum of the categories scores.

Example:
  * Let's say you have an event where the category "Games played" is attributed a max value of 100.
  * We have 3 players, player 1 played 1 game, player 2 played 5 games, and player 3 played 3 games.
    * Player 2 (best score) gets 100 points.
    * Player 1 (worst score) gets 1 point.
    * Player 3 (somewhere in between) gets 60 points.

We do that for each category, and add the results together to get the overall rating.
A player needs to play a minimum number of games to be ranked.
