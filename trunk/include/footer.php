</div><!-- end content -->

<div id="footer" class="clearfix">
		<?php
		include_once(e_PLUGIN."ebattles/include/revision.php");
		/**
		 * Just a little page footer, tells how many registered members
		 * there are, how many users currently logged in and viewing site,
		 * and how many guests viewing site. Active users are displayed,
		 * with link to their user information.
		 */
		echo "<table align=\"center\"><tr><td align=\"center\"><br />";
		echo "<b>Member Total:</b> ".$sql->getNumMembers()."<br />";
		echo "There are $sql->num_active_users registered members and ";
		echo "$sql->num_active_guests guests viewing the site.<br /><br />";
		
		?>
		</td></tr>
		</table>
	<p>
	&copy; Copyright 2007 <a href="mailto:<?php echo EMAIL_FROM_ADDR."\">".ADMIN_NAME."</a> - eBattles v$majorRevision.$minorRevision (svn rev $svnRevision)";?>
	<br />
	<?php
	// Insert at the end of your document
        $end_time = time()+microtime();
        $generation_time = round($end_time - $start_time,3);
        echo "This page was generated in $generation_time seconds";
	?>
	</p>

</div><!-- end footer -->
</div><!-- end page -->

<!--   <div id="extra1">&nbsp;</div> -->
<!--   <div id="extra2">&nbsp;</div> -->
    
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1993764-1";
urchinTracker();
</script>

</body>
</html>

        


