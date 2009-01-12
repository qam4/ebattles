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
if(check_class(e_UC_MEMBER))
{
?>
         <li><strong>User</strong></li>
<?php
         echo "<li><a href=\"".e_PLUGIN."ebattles/userinfo.php?user={USER_ID}\">My Account</a></li>";
         echo "<li><a href=\"".e_PLUGIN."ebattles/useredit.php\">Edit Account</a></li>";
         /* echo "<li><a href=\"".e_PLUGIN."ebattles/pm.php\">My PMs</a></li>"; */
         if($session->isAdmin())
         {
             echo "<li><a href=\"".e_PLUGIN."ebattles/admin.php\">Admin Center</a></li>";
             echo "<li><a href=\"".e_PLUGIN."ebattles/db_admin/index.php\">Admin Database</a></li>";
         }
         echo "<li class=\"last\"><a href=\"".e_PLUGIN."ebattles/process.php\">Logout</a></li>";
}
?>         
         
         
    </ul>
    </div>
</div><!-- end nav -->


