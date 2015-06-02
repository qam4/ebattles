# eBattles Permissions #

## eBattles Plugin userclasses ##
eBattles uses 3 userclasses, that can be edited from the plugin settings in Admin Area/Plugins/eBattles
  1. **Moderator** userclass: determines users who can moderate all the events/teams (default = Admin)
  1. **Events Creators** userclass: determines users who can create events (default = Members)
  1. **Teams Creators** userclass:  determines users who can create teams (default = Members)

## Permissions by feature ##
### Events ###
#### Event creating ####
  * Users of the eBattles plugin userclass **Events Creators** can create events
#### Event managing ####
  * Users of the eBattles **Moderator** userclass can manage any event
  * Event's owner can manage his event
#### Match reporting ####
  * To report a match user needs to be logged in (site member)
  * Event owners can select the **Event Match report** userclass in the "Event manage" page.
  * **Event Match report** userclasses are (in order of priority):
    * _Owner_: for the event owner,
    * _Moderator_: for the event moderators,
    * _Player_: for the event players.
  * Users can report a match if their userclass is same or above **Event Match report** userclass:
    * Event's owner can report a match at any time
    * Event's _Moderators_ can report a match at any time if:
      1. "Event Match report" is set to _Moderator_ or _Player_
    * Event's _Players_ can only report a match if:
      1. "Event Match report" is set to _Player_
      1. He participated in that match.
      1. The event is on-going
    * Users of the eBattles **Moderator** userclass can report a match in any event at any time.
#### Quick Loss reporting ####
  * Quick loss match report is enabled:
    * if scoring is disabled for the event
    * if event is ongoing
    * if the user is a player in this event
    * if "Quick report" is enabled for that event
#### Match deleting ####
  * Match can be deleted by the match reporter, if the event is ongoing
  * Users of the eBattles **Moderator** userclass can delete any match at any time
  * Event's owner can delete any match at any time
#### Player Kick/Ban ####
  * A player kicked from an event is deleted from the event. He can re-signup to the event as a new player.
  * A player banned from an event can no longer play, report a match in the event or sign up to the event during the duration of the ban
### Teams ###
#### Team creating ####
  * Users of the eBattles plugin userclass **Teams Creators** can create teams
#### Team managing ####
  * Users of the eBattles **Moderator** userclass can manage any team
  * Team's owner can manage his team