<?php
// Returns an array of all my messages
// Defaults to current user and INBOX messages
// Make the $sent parameter "true" to retrieve sent messages
function getMyMessageList($id = '', $sent = false) {
   global $database;
   global $session;
  $id = empty($id) ? $session->username : $id;

  $where = $sent ? "(p.from_id = '$id') AND (p.sender_del ='n')" : "(p.to_id = '$id') AND (p.receiver_del ='n')";
  $join  = $sent ? "p.to_id = u.username" : "p.from_id = u.username";
  
  $messages = array();

  // Construct query
  $sql = "SELECT p.id, to_id, from_id, time_sent, subject, message, opened, 
          time_opened, username, nickname FROM ".TBL_PMS." p LEFT JOIN ".TBL_USERS." u 
          ON $join WHERE $where order by time_sent DESC";
  $res = mysql_query($sql) or die(mysql_error());

  // If there are records, populate the array to return
  if (mysql_num_rows($res) > 0) {
    while ($row = mysql_fetch_assoc($res)) {
      $messages[] = $row;
    }
  }

  // Return the array of messages to the caller
  return $messages;
}

function getUnreadMessagesCount($id = '', $sent = false) {
   global $database;
   global $session;
  $id = empty($id) ? $session->username : $id;

  $where = $sent ? "(p.from_id = '$id') AND (p.opened = 'n') AND (p.sender_del ='n')" : "(p.to_id = '$id') AND (p.opened = 'n') AND (p.receiver_del ='n')";
  $join  = $sent ? "p.to_id = u.username" : "p.from_id = u.username";
  
  $messages = array();

  // Construct query
  $sql = "SELECT p.id, to_id, from_id, time_sent, subject, message, opened, 
          time_opened, username, nickname FROM ".TBL_PMS." p LEFT JOIN ".TBL_USERS." u 
          ON $join WHERE $where order by time_sent DESC";
  $res = mysql_query($sql) or die(mysql_error());

  // If there are records, populate the array to return
  if (mysql_num_rows($res) > 0) {
    while ($row = mysql_fetch_assoc($res)) {
      $messages[] = $row;
    }
  }

  // Return the array of messages to the caller
  return count($messages);
}

// Gets a specific message, but only if it corresponds to the
// provided user ID and type of message provided. Once again, we
// default to the current user and INCOMING messages.
function getMyMessage($id, $user = '', $sent = false) {
   global $database;
   global $session;
  $user = empty($user) ? $session->username : $user;

  $where = $sent ? "p.from_id = '$user'" : "p.to_id = '$user'";
  $join  = $sent ? "p.to_id = u.username" : "p.from_id = u.username";

  // Construct query
  $sql = "SELECT p.id, to_id, from_id, time_sent, subject, message, opened,
          time_opened, username, nickname FROM ".TBL_PMS." p LEFT JOIN ".TBL_USERS." u
          ON $join WHERE $where AND p.id = '$id'";

  $res = mysql_query($sql);

  // If there is a row found, return it as an associative array,
  // otherwise, return false to show we didn't find anything.
  if (mysql_num_rows($res) == 1) {
    return mysql_fetch_assoc($res);
  } else return false;
}
?> 