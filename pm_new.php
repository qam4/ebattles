<h3><?php echo $title; ?></h3>

<?php
if (!isset($_REQUEST['to'])) $_REQUEST['to'] = '';
if (!isset($_POST['subject'])) $_POST['subject'] = '';
if (!isset($_POST['message'])) $_POST['message'] = '';

if (isset($_POST['replymessage']))
{
  $_POST['subject'] = 'RE: '. $_POST['subject'];
  if (isset($_POST['quote']))
  {
    $_POST['message'] = '<blockquote><HR>'.$_REQUEST['to'].' said:<br />'. stripslashes($_POST['message']).'<hr></blockquote><br />';
  }
}

// Process the message once it has been sent
if (isset($_POST['newMessage']))
{
  // Escape and prepare our variables for insertion into the database
  // This is also where you would run any sort of editing, such as BBCode parsing
  $to   = mysql_real_escape_string($_POST['to']);
  $from = $session->username;
  $sub  = mysql_real_escape_string($_POST['subject']);
  $msg  = mysql_real_escape_string($_POST['message']);
    
  // Handle all your specific error checking here
  if (empty($to) || empty($sub) || empty($msg))
  {
    $error = "<p>You must select a recipient and provide a subject and message.</p>\n";
  }
  else
  {
    $time = GMT_time();
    $mysqldate = date( 'Y-m-d H:i:s', $time ); 

    // Notice carefully how we only have to provide the five values we previously discussed
    $sql = "INSERT INTO ".TBL_PMS." (to_id, from_id, time_sent, subject, message) VALUES ('$to', '$from', '$mysqldate' , '$sub', '$msg')";
    if (!mysql_query($sql))
    {
      $error = "<p>Could not send message!</p>\n";
    }
    else
    {
      $succes_message = "<p>Message sent successfully!</p>\n";
    }
  }
}
// Output a "back" link
echo "<p><a href=\"$back\">&laquo; Back</a></p>\n";

echo isset($error) ? $error : '';

if( isset($succes_message))
{
  echo $succes_message;
}
else
{
  
  echo "<form name=\"newMessage\" action=\"?action=send\" method=\"post\">\n";
  echo "<table border=\"1\" cellpadding=\"0\" cellspacing=\"0\">\n";
  echo "<tr>\n";
  echo "<td>To:</td>\n";
  echo "<td><select name=\"to\">\n";
  echo "<option value=\"\"></option>\n";
  
  // Collect and loop through all usernames that are not the current user
  $sql = mysql_query("SELECT * FROM ".TBL_USERS." WHERE username != '$session->username' ORDER BY username");
  if (mysql_num_rows($sql) > 0) {
    while ($x = mysql_fetch_assoc($sql))
    {
       $username = $x['username'];
       $nickname = $x['nickname'];
       echo "<option value=\"$username\"";
       if (strtolower($_REQUEST['to']) == strtolower($username)) 
         echo ' selected="selected"';
       echo ">$nickname ($username)</option>\n";
    }
  }
  
  echo "</select></td>\n";
  echo "</tr>\n";
  echo "<tr>\n";
  echo "<td>Subject:</td>\n";
  echo "<td><input type=\"text\" name=\"subject\" value=\"" . $_POST['subject'] . "\" maxlength=\"50\" /></td>\n";
  echo "</tr>\n";
  echo "<tr>\n";
  echo "<td>Message:</td>\n";
  echo "<td>\n";
  echo "<textarea name=\"message\" cols=\"70\" rows=\"20\">" . $_POST['message'] . "</textarea>\n";
  echo "</td>\n";
  echo "</tr>\n";
  echo "<tr>\n";
  echo "<td></td>\n";
  echo "<td><input type=\"submit\" name=\"newMessage\" value=\"Send\" /></td>\n";
  echo "</tr>\n";
  echo "</table>\n";
  echo "</form>\n";
}
?>

</div>
</div>
<?php
include("include/footer.php");
?>