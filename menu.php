<div id="nav">
    <div class="wrapper">
    <h2 class="accessibility">Navigation</h2>
    <ul class="clearfix">
         <li><strong>Home</strong></li>
         <li><a href="index.php">Main</a></li>
         <li><a href="events.php">Events</a></li>
         <li><a href="clans.php">Teams</a></li>
<!-- fm        <li><a href="archive.html">Archive</a></li>
         <li><a href="photos.html">Photos</a></li>
         <li><a href="about.html">About</a></li>
         <li class="last"><a href="contact.html">Contact</a></li>
-->
<?php
if($session->logged_in)
{
?>
         <li><strong>User</strong></li>
<?php
         echo "<li><a href=\"userinfo.php?user=$session->username\">My Account</a></li>";
         echo "<li><a href=\"useredit.php\">Edit Account</a></li>";
         /* echo "<li><a href=\"pm.php\">My PMs</a></li>"; */
         if($session->isAdmin())
         {
             echo "<li><a href=\"admin.php\">Admin Center</a></li>";
             echo "<li><a href=\"db_admin/index.php\">Admin Database</a></li>";
         }
         echo "<li class=\"last\"><a href=\"process.php\">Logout</a></li>";
}
?>         
         
         
    </ul>
    </div>
</div><!-- end nav -->


