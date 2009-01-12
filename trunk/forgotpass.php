<?php
/**
 * ForgotPass.php
 *
 * This page is for those users who have forgotten their
 * password and want to have a new password generated for
 * them and sent to the email address attached to their
 * account in the database. The new password is not
 * displayed on the website for security purposes.
 *
 * Note: If your server is not properly setup to send
 * mail, then this page is essentially useless and it
 * would be better to not even link to this page from
 * your website.
 *
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
?>
<div id="main">
<?php
/**
 * Forgot Password form has been submitted and no errors
 * were found with the form (the username is in the database)
 */
if(isset($_SESSION['forgotpass'])){
   /**
    * New password was generated for user and sent to user's
    * email address.
    */
   if($_SESSION['forgotpass']){
      echo "<h1>New Password Generated</h1>";
      echo "<p>Your new password has been generated "
          ."and sent to the email <br />associated with your account.</p>"
          ."<p><a href=\"".e_PLUGIN."ebattles/index.php\">Main</a>.</p>";
   }
   /**
    * Email could not be sent, therefore password was not
    * edited in the database.
    */
   else{
      echo "<h1>New Password Failure</h1>";
      echo "<p>There was an error sending you the "
          ."email with the new password,<br /> so your password has not been changed.</p>"
          ."<p><a href=\"".e_PLUGIN."ebattles/index.php\">Main</a>.</p>";
   }
       
   unset($_SESSION['forgotpass']);
}
else{

/**
 * Forgot password form is displayed, if error found
 * it is displayed.
 */
?>
<div class="news">
<h2>Forgot Password</h2>

<!--//
<p>
Please contact the webmaster.
</p>
Not supported
//-->

<p>
A new password will be generated for you and sent to the email address<br />
associated with your account, all you have to do is enter your
username.
</p>

<?php echo $form->error("user"); 
echo "<form action=\"".e_PLUGIN."ebattles/process.php\" method=\"post\">";
?>
<b>Username:</b> <input type="text" name="user" maxlength="30" value="<?php echo $form->value("user"); ?>"></input>
<input type="hidden" name="subforgot" value="1"></input>
<input type="submit" value="Get New Password"></input>
</form>
</div>

<?php
}
?>
</div>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
