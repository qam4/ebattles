<?php include("menu.php"); ?>

<?php
		include 'config_db.php';
		include 'connect_db.php';
		
		// Create database
		if (mysql_query("CREATE DATABASE $db_name",$con))
		{
			echo "Database created";
		}
		else
		{
			echo "Error creating database: " . mysql_error();
		}
		include 'close_db.php';
 ?>

</font>
</body>
</html> 
