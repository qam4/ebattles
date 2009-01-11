<?php 
/**
 * Mailer.php
 *
 * The Mailer class is meant to simplify the task of sending
 * emails to users. Note: this email system will not work
 * if your server is not setup to send mail.
 *
 * If you are running Windows and want a mail server, check
 * out this website to see a list of freeware programs:
 * <http://www.snapfiles.com/freeware/server/fwmailserver.html>
 *
 */
 
// manage errors
error_reporting(E_ALL); // php errors
define('DISPLAY_XPM4_ERRORS', true); // display XPM4 errors
// path to 'SMTP.php' file from XPM4 package
require_once 'xpertMailer/SMTP.php';

class Mailer
{
   /**
    * sendWelcome - Sends a welcome message to the newly
    * registered user, also supplying the username and
    * password.
    */
   function sendWelcome($user, $email, $pass){
      $from = EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $to = $email;
      $subject = "eBattles - Welcome!";
      $body = $user.",\r\n\r\n"
             ."Welcome! You've just registered at eBattles "
             ."with the following information:\r\n"
             ."Username: ".$user."\r\n"
             ."Password: ".$pass."\r\n\r\n"
             ."If you ever lose or forget your password, a new "
             ."password will be generated for you and sent to this "
             ."email address, if you would like to change your "
             ."email address you can do so by going to the "
             ."My Account page after signing in.\r\n\r\n"
             ."- eBattles";

      // standard mail message RFC2822
      $message = "From: ".$from."\r\n".
           "To: ".$to."\r\n".
           'Subject: '.$subject."\r\n".
           'Content-Type: text/plain'."\r\n\r\n".$body;

      // connect to MTA server (relay) 'smtp.gmail.com' via SSL (TLS encryption) with authentication using port '465' and timeout '10' secounds
      // make sure you have OpenSSL module (extension) enable on your php configuration
      $c = SMTP::connect('smtp.gmail.com', 465, EMAIL_FROM_ADDR, EMAIL_PASSWORD, 'tls', 10);
      
      // send mail relay
      $s = SMTP::send($c, array($email), $message, EMAIL_FROM_ADDR);
            
      // disconnect
      SMTP::disconnect($c);

//      return mail($email,$subject,$body,$from);
      return $s;
   }
   
   /**
    * sendNewPass - Sends the newly generated password
    * to the user's email address that was specified at
    * sign-up.
    */
   function sendNewPass($user, $email, $pass){
      $from = EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $to = $email;
      $subject = "eBattles - Your new password";
      $body = $user.",\r\n\r\n"
             ."We've generated a new password for you at your "
             ."request, you can use this new password with your "
             ."username to log in to eBattles.\r\n\r\n"
             ."Username: ".$user."\r\n"
             ."New Password: ".$pass."\r\n\r\n"
             ."It is recommended that you change your password "
             ."to something that is easier to remember, which "
             ."can be done by going to the My Account page "
             ."after signing in.\r\n\r\n"
             ."- eBattles";

      // standard mail message RFC2822
      $message = "From: ".$from."\r\n".
           "To: ".$to."\r\n".
           'Subject: '.$subject."\r\n".
           'Content-Type: text/plain'."\r\n\r\n".$body;

      // connect to MTA server (relay) 'smtp.gmail.com' via SSL (TLS encryption) with authentication using port '465' and timeout '10' secounds
      // make sure you have OpenSSL module (extension) enable on your php configuration
      $c = SMTP::connect('smtp.gmail.com', 465, EMAIL_FROM_ADDR, EMAIL_PASSWORD, 'tls', 10);
      
      // send mail relay
      $s = SMTP::send($c, array($email), $message, EMAIL_FROM_ADDR);
            
      // disconnect
      SMTP::disconnect($c);

//      return mail($email,$subject,$body,$from);
      return $s;
   }
};

/* Initialize mailer object */
$mailer = new Mailer;
 
?>
