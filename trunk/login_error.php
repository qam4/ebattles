<?php
/**
 * Login_Error.php
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

/**
 * Login Error
 */
?>

<div class="news">
<h2>Login Error</h2>
<?php 
if($form->num_errors > 0){
  echo $form->num_errors." error(s) found";
}
echo $form->error("user");
echo $form->error("pass");

?>
<a href="index.php">Back to Main</a>
</div>
</div>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
