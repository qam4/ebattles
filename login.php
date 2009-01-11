<?php
require_once("pm_functions.php"); // include the functions we just wrote

if($session->logged_in)
{
/* echo "debug - Logged in"; *?
   /**
   * User has already logged in, so display relavent links, including
   * a link to the admin center if the user is an administrator.
   */
   $time_local = GMT_time() + $session->timezone_offset;
   $date = date("d M Y, h:i A",$time_local);
   $myUnreadMessagesCount = getUnreadMessagesCount();

   echo "$date.<br />";
   echo "<p>Welcome <b>$session->username</b>, ";
   if ($myUnreadMessagesCount>1)
   {
      echo "you have <a href=\"pm.php\">$myUnreadMessagesCount</a> new messages.<br />";
   }
   else
   {
      echo "you have <a href=\"pm.php\">$myUnreadMessagesCount</a> new message.<br />";
   }
   echo "</p>";
}
else
{
/* echo "debug - Login"; */
   /**
   * User not logged in, display the login form.
   * If user has already tried to login, but errors were
   * found, display the total number of errors.
   * If errors occurred, they will be displayed.
   */
?>
    <script language="javaScript" type="text/javascript">
    <!--//
    function getTZO(frm)
    {
        frm.tzo.value = (new Date().getTimezoneOffset()/60)*(-1);
        //alert('TimeZone debug.');
    } 
    //-->
    </script>


   <form action="process.php" method="post">
      <table border="0" cellspacing="0" cellpadding="3">
      <tr>
          <td>
              Username:
          </td>
          <td>
              <input type="text" name="user" maxlength="30" value="<?php echo $form->value("user"); ?>"></input>
          </td>
              <td>Password:
          </td>
          <td>
              <input type="password" name="pass" maxlength="30" value="<?php echo $form->value("pass"); ?>"></input>
          </td>
          <td>
              <input type="hidden" name="sublogin" value="1"></input>
              <input type="hidden" name="tzo" value="0"></input>
              <input type="submit" value="Login" onclick="getTZO(this.form);"></input>
          </td>
          <td>
              Not registered? <a href="register.php">Sign-Up!</a>
              | <a href="forgotpass.php">Forgot Password?</a>
          </td>
<!--//
          <td>
              <input style="float:left" align="left" type="checkbox" name="remember" <?php if($form->value("remember") != ""){ echo "checked"; } ?>></input>
              <p>Remember me next time</p>
          </td>
//-->
      </tr>
      </table>
<!--//
Not supported
//-->
   </form>
<?php
}
?>