<?php
/**
 * Admin.php
 *
 * This is the Admin Center page. Only administrators
 * are allowed to view this page. This page displays the
 * database table of users and banned users. Admins can
 * choose to delete specific users, delete inactive users,
 * ban users, update user levels, etc.
 *
 */

ob_start();
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");

/**
 * displayUsers - Displays the users database table in
 * a nicely formatted html table.
 */
function displayUsers(){
   global $sql;
   global $session;
   
   if (!isset($_GET['orderby'])) $_GET['orderby'] = "username";
   $orderby=$_GET['orderby'];
   
   $sort = "DESC";
   if(isset($_GET["sort"]) && !empty($_GET["sort"]))
   {
     $sort = ($_GET["sort"]=="ASC") ? "DESC" : "ASC";
   }
   
   /* set pagination variables */
   $rowsPerPage = 5;
   $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
   $start = $rowsPerPage * $pg - $rowsPerPage;

   $q = "SELECT count(*)"
       ."FROM ".TBL_USERS;
   $result = $sql->db_Query($q);
   $totalPages = mysql_result($result, 0);
   
   $q = "SELECT username,name,userlevel,email,joined_timestamp,timestamp"
       ." FROM ".TBL_USERS." ORDER BY $orderby $sort"
       ." LIMIT $start, $rowsPerPage";
       
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error displaying info";
      return;
   }
   if($num_rows == 0){
      echo "Database table empty";
      return;
   }
   /* Display table contents */
   echo "<table class=\"type1\">\n";

   echo "<tr>";
   echo "<td class=\"type1Header\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/admin.php?orderby=username&sort=$sort\"><b>Username</b></a></td>";
   echo "<td class=\"type1Header\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/admin.php?orderby=name&sort=$sort\"><b>Nickname</b></a></td>";
   echo "<td class=\"type1Header\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/admin.php?orderby=userlevel&sort=$sort\"><b>Level</b></a></td>";
   echo "<td class=\"type1Header\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/admin.php?orderby=email&sort=$sort\"><b>Email</b></a></td>";
   echo "<td class=\"type1Header\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/admin.php?orderby=joined_timestamp&sort=$sort\"><b>Joined</b></a></td>";
   echo "<td class=\"type1Header\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/admin.php?orderby=timestamp&sort=$sort\"><b>Last Active</b></a></td>";
   echo "</tr>\n";

/*
if ($_GET['orderby'] == "username")
if ($_GET['orderby'] == "name")
if ($_GET['orderby'] == "userlevel")
if ($_GET['orderby'] == "email")
if ($_GET['orderby'] == "joined_timestamp")
if ($_GET['orderby'] == "timestamp")
*/

   for($i=0; $i<$num_rows; $i++){
      $uname  = mysql_result($result,$i,"username");
      $uname  = mysql_result($result,$i,"name");
      $ulevel = mysql_result($result,$i,"userlevel");
      $email  = mysql_result($result,$i,"email");
      $time_joined   = mysql_result($result,$i,"joined_timestamp");
      $time_joined_local = $time_joined + $session->timezone_offset;
      $date_joined = date("d M Y, h:i:s A",$time_joined_local);

      $time   = mysql_result($result,$i,"timestamp");
      $time_local = $time + $session->timezone_offset;
      
      $date = date("d M Y, h:i:s A",$time_local);
      echo "<tr><td class=\"type1Body\"><a class=\"type1\" href=\"".e_PLUGIN."ebattles/userinfo.php?user=$uname\"><b>$uname</b></a></td><td class=\"type1Body\">$uname</td><td class=\"type1Body\">$ulevel</td><td class=\"type1Body\">$email</td><td class=\"type1Body\">$date_joined</td><td class=\"type1Body\">$date</td></tr>\n";
   }
   echo "</table><br />\n";
   paginate($rowsPerPage, $pg, $totalPages);
}

/**
 * displayBannedUsers - Displays the banned users
 * database table in a nicely formatted html table.
 */
function displayBannedUsers(){
   global $session;
   global $sql;
   $q = "SELECT username,timestamp "
       ."FROM ".TBL_BANNED_USERS." ORDER BY username";
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error displaying info";
      return;
   }
   if($num_rows == 0){
      echo "Database table empty";
      return;
   }
   /* Display table contents */
   echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
   echo "<tr><td><b>Username</b></td><td><b>Time Banned</b></td></tr>\n";
   for($i=0; $i<$num_rows; $i++){
      $uname = mysql_result($result,$i,"username");
      $time  = mysql_result($result,$i,"timestamp");
      $time_local = $time + $session->timezone_offset;
      $date  = date("d M Y",$time_local);

      echo "<tr><td>$uname</td><td>$date</td></tr>\n";
   }
   echo "</table><br />\n";
}
   
/**
 * User not an administrator, redirect to main page
 * automatically.
 */
if(!$session->isAdmin()){
   header("Location: index.php");
   ob_end_flush();
}
else{
/**
 * Administrator is viewing page, so display all
 * forms.
 */
?>
<div id="main">
<h1>Admin Center</h1>

<font size="1">Logged in as <b><?php echo {USER_ID}; ?></b></font><br /><br />
[<a href="./db_admin/index.php">Database admin</a>]<br /><br />


<p>
Back to [<a href="../index.php">Main Page</a>]<br /><br />
</p>
<?php
if($form->num_errors > 0){
   echo "<font size=\"4\" color=\"#ff0000\">"
       ."!*** Error with request, please fix</font><br /><br />";
}
?>

<div class="news">
<?php
/**
 * Display Users Table
 */
?>
<h2>Users Table Contents:</h2>
<?php
displayUsers();
?>
</div>
<br /><br />

<?php
/**
 * Update User Level
 */
?>
<div class="news">
<h2>Update User Level</h2>
<?php echo $form->error("upduser");
echo "<form action=\"".e_PLUGIN."ebattles/admin/adminprocess.php\" method=\"post\">";
?>
<table>
<tr>
<td>
Username:<br />
<input type="text" name="upduser" maxlength="30" value="<?php echo $form->value("upduser"); ?>"></input>
</td>
<td>

Level:<br />
<select name="updlevel">
<option value="1">1</option>
<option value="9">9</option>
</select>
</td>
<td>
<br />
<input type="hidden" name="subupdlevel" value="1"></input>
<input type="submit" value="Update Level"></input>
</td></tr>
</table>
</form>

</div>
<br /><br />

<?php
/**
 * Delete User
 */
?>
<div class="news">
<h2>Delete User</h2>
<?php echo $form->error("deluser");
echo "<form action=\"".e_PLUGIN."ebattles/admin/adminprocess.php\" method=\"post\">";
?>
Username:<br />
<input type="text" name="deluser" maxlength="30" value="<?php echo $form->value("deluser"); ?>"></input>
<input type="hidden" name="subdeluser" value="1"></input>
<input type="submit" value="Delete User"></input>
</form>
</div>
<br /><br />

<?php
/**
 * Delete Inactive Users
 */
?>
<div class="news">
<h2>Delete Inactive Users</h2>
<p>
This will delete all users (not administrators), who have not logged in to the site<br />
within a certain time period. You specify the days spent inactive.<br />
</p>
<?php
echo "<form action=\"".e_PLUGIN."ebattles/admin/adminprocess.php\" method=\"post\">";
?>
<table>
<tr><td>
Days:<br />
<select name="inactdays">
<option value="3">3</option>
<option value="7">7</option>
<option value="14">14</option>
<option value="30">30</option>
<option value="100">100</option>
<option value="365">365</option>
</select>
</td>
<td>
<br />
<input type="hidden" name="subdelinact" value="1"></input>
<input type="submit" value="Delete All Inactive"></input>
</td>
</tr>
</table>
</form>
</div>
<br /><br />

<?php
/**
 * Ban User
 */
?>
<div class="news">
<h2>Ban User</h2>
<?php echo $form->error("banuser");
echo "<form action=\"".e_PLUGIN."ebattles/admin/adminprocess.php\" method=\"post\">";
?>
Username:<br />
<input type="text" name="banuser" maxlength="30" value="<?php echo $form->value("banuser"); ?>"></input>
<input type="hidden" name="subbanuser" value="1"></input>
<input type="submit" value="Ban User"></input>
</form>
</div>
<br /><br />

<?php
/**
 * Display Banned Users Table
 */
?>
<div class="news">
<h2>Banned Users Table Contents:</h2>
<?php
displayBannedUsers();
?>
</div>
<br /><br />

<?php
/**
 * Delete Banned User
 */
?>
<div class="news">
<h2>Delete Banned User</h2>
<?php echo $form->error("delbanuser"); 
echo "<form action=\"".e_PLUGIN."ebattles/admin/adminprocess.php\" method=\"post\">";
?>
Username:<br />
<input type="text" name="delbanuser" maxlength="30" value="<?php echo $form->value("delbanuser"); ?>"></input>
<input type="hidden" name="subdelbanned" value="1"></input>
<input type="submit" value="Delete Banned User"></input>
</form>
</div>
<br /><br />

</div>
<?php
}
?>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
