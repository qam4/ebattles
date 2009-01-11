<?php include("menu.php"); ?>

<?php
	include 'config_db.php';
	include 'connect_db.php';

	// delete database
	if (mysql_query("DROP DATABASE $db_name",$con))
	{
		echo "Database deleted";
	}
	else
	{
		echo "Error deleting database: " . mysql_error();
	}

	include 'close_db.php';
?>
    
</font>
</body>
</html> 
