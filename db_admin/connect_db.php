<?php
	$con = mysql_connect($db_host,$db_user,$db_password);
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}
?>
