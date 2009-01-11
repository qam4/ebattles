<?php
/**
 * pm.php
 *
 *
 */
include("include/main.php");
?>
<div id="main">
<h1>Private Messages</h1>
<div class="news">

<?php
require_once("pm_functions.php"); // include the functions we just wrote
define('DATE_FORMAT', 'Y-m-d g:ia'); // define our date format variable

if (isset($_GET['action']) && $_GET['action'] == 'send') {
  $title = "Send Message";
  $back = "?folder=inbox";
  require('pm_new.php');
  exit();
}

if (isset($_POST['delete_received']) && count($_POST['del']) > 0)
{
  $del_ids=$_POST['del'];
  
  for($i=0;$i<count($del_ids);$i++)
  {
    $sql = "UPDATE ".TBL_PMS." SET receiver_del='y' WHERE id = '$del_ids[$i]' AND to_id = '$session->username'";
    // Make sure they are only attempting to delete their own messages!!!
    if (!mysql_query($sql))
    {
      // Could not delete selected messages
    }
    else
    {
      // Successfully deleted messages
    }
    
    $sql = "SELECT sender_del FROM ".TBL_PMS." WHERE id = '$del_ids[$i]' AND to_id = '$session->username'";
    $result = $database->query($sql);
    $num_rows = mysql_numrows($result);
    if($result || ($num_rows > 0))
    {
      $sender_del  = mysql_result($result,0,"sender_del");
      if ($sender_del == 'y')
      {
        $sql = "DELETE FROM ".TBL_PMS." WHERE id = '$del_ids[$i]' AND to_id = '$session->username'";
        if (!mysql_query($sql))
        {
            // Could not delete selected messages
        }
        else
        {
        	// Successfully deleted messages
        }
      }
    }
  }
} 

if (isset($_POST['delete_sent']) && count($_POST['del']) > 0)
{
  $del_ids=$_POST['del'];
  
  for($i=0;$i<count($del_ids);$i++)
  {
    $sql = "UPDATE ".TBL_PMS." SET sender_del='y' WHERE id = '$del_ids[$i]' AND from_id = '$session->username'";
    // Make sure they are only attempting to delete their own messages!!!
    if (!mysql_query($sql)) {
      // Could not delete selected messages
    }
    else
    {
      // Successfully deleted messages
    }
    
    $sql = "SELECT receiver_del FROM ".TBL_PMS." WHERE id = '$del_ids[$i]' AND from_id = '$session->username'";
    $result = $database->query($sql);
    $num_rows = mysql_numrows($result);
    if($result || ($num_rows > 0))
    {
      $receiver_del  = mysql_result($result,0,"receiver_del");
      if ($receiver_del == 'y')
      {
        $sql = "DELETE FROM ".TBL_PMS." WHERE id = '$del_ids[$i]' AND from_id = '$session->username'";
        if (!mysql_query($sql))
        {
            // Could not delete selected messages
        }
        else
        {
        	// Successfully deleted messages
        }
      }
    }
  }
}

$folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';

switch($folder) {
  case 'sent':
    // Show my sent messages
    $title = "Sent Messages";

    // Notice we set the second parameter to "true" to pull sent messages
    $myMessages = getMyMessageList('', true);
    $myUnreadMessagesCount = getUnreadMessagesCount('', true);

    // Set the columns we will be using for our display
    $cols = array('To', 'Subject', 'Time', 'Del');
    break;

  default:
    // Show our inbox
    // Notice we are setting the same variables as above
    $title = "Inbox";
    $folder = "inbox"; // This is in case we have something errant entered
    $myMessages = getMyMessageList();
    $myUnreadMessagesCount = getUnreadMessagesCount();
    $cols = array('From', 'Subject', 'Time', 'Del');
}

// This is so we know how many columns we actually have
$span = count($cols);
?> 

<h2><?php echo $title; ?></h2> 

<?php
if (isset($_GET['id']))
{
  $id = $_GET['id'];
  switch($folder)
  {
    case 'sent':
      $msg  = getMyMessage($id, '', true);
      $back = "?folder=sent"; // Set my link back to Sent Messages
      $from = "To";
      break;

    case 'inbox':
      $msg  = getMyMessage($id);
      $back = "?folder=inbox";
      $from = "From";
      break;

    // Obviously, if you choose, you can easily add more boxes without
    // too much difficulty.
  }

  // Output a "back" link
  echo "<p><a href=\"$back\">&laquo; Back</a></p>\n";

  // If there is no message returned, we have an error
  if (!$msg)
  {
    echo "<p>Invalid message requested</p>\n";
  } 
  else
  { 
    // Define our variables (removing slashes)
    // Add any other formating you like here (including BBCode, etc)
    $user = stripslashes($msg['username']);
    $usernickname = stripslashes($msg['nickname']);
    $subject = stripslashes($msg['subject']);
    $message = nl2br(stripslashes($msg['message']));
    $opened  = $msg['opened'];
    $time_sent = strtotime($msg['time_sent']);
    $time_sent_local = $time_sent + $session->timezone_offset;
    $date = date(DATE_FORMAT, $time_sent_local);

    $time = GMT_time();
    $mysqldate = date( 'Y-m-d H:i:s', $time ); 

    // Mark a received message "read" when it's opened
    if ($msg['to_id'] == $session->username && $opened == 'n') {
      $sql = "UPDATE ".TBL_PMS." SET opened = 'y', time_opened = '$mysqldate' WHERE id = '$id'";
      mysql_query($sql);
    }
    
    // Output our message
    echo "<h2>$subject</h2>\n";
    echo "<p>$from <b><a href=\"userinfo.php?user=$user\">$usernickname</a></b><br />on $date</p>\n";
    echo "<hr />\n";
    echo "<p>$message</p>\n";
    echo "<hr />\n";
    
    if ($folder == 'inbox')
    {
      echo "
      <form action=\"?action=send\" method=\"post\">
          Quote the original
          <input type=\"checkbox\" name=\"quote\" checked=\"checked\"></input>
          <input type=\"hidden\" name=\"to\" value=\"$user\"></input>
          <input type=\"hidden\" name=\"subject\" value=\"$subject\"></input>
          <input type=\"hidden\" name=\"message\" value='$message'></input>
          <input type=\"hidden\" name=\"replymessage\" value=\"1\"></input>
          <input type=\"submit\" value=\"Reply\"></input>
      </form>
      ";
    }
  }
}
else
{ // They haven't chosen a message, so show the box!

  $nbr_messages = count($myMessages);

  echo "<p><a href=\"?folder=inbox\">Inbox</a> |\n";
  echo "<a href=\"?folder=sent\">Sent Messages</a></p>\n";

  // If we're in the inbox, show a Create link
  if ($folder == 'inbox') {
    echo "<p><a href=\"?action=send\">Create New Message</a></p>\n";
  }

  if ($folder == 'inbox')
  {
    echo "<p>$nbr_messages total, $myUnreadMessagesCount unread.</p>\n";
  }
  else
  {
    echo "<p>$nbr_messages total.</p>\n";
  }
  

    echo "<form name=\"deleteMessages\" action=\"\" method=\"post\">\n";

  echo "<table class=\"pm\">\n";
  echo "<tr>\n";

  // Create our headings with the column names we defined previously
  echo "<td class=\"header\">" . implode("</td>\n<td class=\"header\">", $cols) . "</td>\n";
  echo "</tr>\n";
 

  // Make sure we have some messages to display
  if ($nbr_messages > 0) {

    // Loop through each message and display it on a row
    foreach ($myMessages as $msg) {

      // Determine to show the message as read or unread

      if ($folder == 'inbox')
      {
        $class = $msg['opened'] == 'y' ? 'read' : 'unread';
      }
      else
      {
        $class = 'read';
      }
      $time_sent = strtotime($msg['time_sent']);
      $time_sent_local = $time_sent + $session->timezone_offset;
      $date = date(DATE_FORMAT, $time_sent_local);
      echo "<tr>\n";
      echo "<td><a class=\"pm\" href=\"userinfo.php?user=$msg[username]\">$msg[nickname]</a></td>\n";

      // Hyperlink subject to display message
      echo "<td class=\"$class\"><a class=\"pm\" href=\"?folder=$folder&amp;id=$msg[id]\">$msg[subject]</a></td>\n";
      echo "<td>$date</td>\n";

      // Checkbox to select which messages to delete
        echo "<td><input type=\"checkbox\" name=\"del[]\" value=\"$msg[id]\" /></td>\n";

      echo "</tr>\n";
    }

    // More of our delete form
    // This will be our submit button to delete selected entries
    if ($folder == 'inbox') {
      echo "<tr class=\"deleteRow\">\n";
      echo "<td colspan=\"$span\"><input type=\"submit\" name=\"delete_received\" value=\"Delete Selected\" /></td>\n";
      echo "</tr>\n";
    }
    if ($folder == 'sent') {
      echo "<tr class=\"deleteRow\">\n";
      echo "<td colspan=\"$span\"><input type=\"submit\" name=\"delete_sent\" value=\"Delete Selected\" /></td>\n";
      echo "</tr>\n";
    }
  }
  else
  {
    // We have no messages in this box.
    echo "<tr>\n";
    echo "<td colspan=\"$span\"></p>You have no messages</p></td>\n";
    echo "</tr>\n";
  }
 
  echo "</table>\n";

  if ($folder == 'inbox') {
    echo "</form>\n";
  }

} // End Script 
?>

</div>
</div>
<?php
include("include/footer.php");
?>
