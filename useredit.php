<?php
/**
 * UserEdit.php
 *
 * This page is for users to edit their account information
 * such as their password, email address, etc. Their
 * usernames can not be edited. When changing their
 * password, they must first confirm their current password.
 *
 */
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
?>
<div id="main">
<div class="news">
<?php
/**
 * User has submitted form without errors and user's
 * account has been edited successfully.
 */
if(isset($_SESSION['useredit'])){
   unset($_SESSION['useredit']);
   
   echo "<h1>User Account Edit Success!</h1>";
   echo "<p><b>{USER_ID}</b>, your account has been successfully updated. "
       ."<a href=\"".e_PLUGIN."ebattles/index.php\">Main</a>.</p>";
}
else{
?>

<?php
/**
 * If user is not logged in, then do not display anything.
 * If user is logged in, then display the form to edit
 * account information, with the current email address
 * already in the field.
 */
if($session->logged_in){
?>
<h1>User Account Edit : <?php echo {USER_ID}; ?></h1>
<?php
if($form->num_errors > 0){
   echo "<td><font size=\"2\" color=\"#ff0000\">".$form->num_errors." error(s) found</font></td>";
}
echo "<form action=\"".e_PLUGIN."ebattles/process.php\" method=\"post\">";
?>
<table border="0" cellspacing="0" cellpadding="3">
<tr>
<td>Current Password:</td>
<td><input type="password" name="curpass" maxlength="30" value="
<?php echo $form->value("curpass"); ?>"></input></td>
<td><?php echo $form->error("curpass"); ?></td>
</tr>
<tr>
<td>New Password:</td>
<td><input type="password" name="newpass" maxlength="30" value="
<?php echo $form->value("newpass"); ?>"></input></td>
<td><?php echo $form->error("newpass"); ?></td>
</tr>
<tr>
<td>Email:</td>
<td><input type="text" name="email" maxlength="50" value="
<?php
if($form->value("email") == ""){
   echo $session->userinfo['email'];
}else{
   echo $form->value("email");
}
?>"></input>
</td>
<td>Nickname:</td>
<td><input type="text" name="name" maxlength="50" value="
<?php
if($form->value("name") == ""){
   echo $session->userinfo['name'];
}else{
   echo $form->value("name");
}
?>"></input>
</td>
<td><?php echo $form->error("email"); ?></td>
</tr>
<tr><td colspan="2" align="right">
<input type="hidden" name="subedit" value="1"></input>
<input type="submit" value="Edit Account"></input></td></tr>
<tr><td colspan="2" align="left"></td></tr>
</table>
</form>

<?php
}
}
?>
</div>
</div>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
