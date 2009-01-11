<?php
function GMT_time() {
$gm_time = time() - date('Z', time());
return $gm_time;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>My admin page</title>
	</head>

	<body>
	<font size="1" face="verdana">
		Current date (GMT): 
        <?php
        $time = GMT_time();
        $date = Date("d M Y, h:i:s A", $time);
        print ($date); 
        ?>
		<br />
		<a href="../index.php">Home</a> |
<!--
		<a href="create_db.php">Create database</a> |
		<a href="delete_db.php">Delete database</a>
-->
		<br />
		<a href="delete_tables.php">Delete tables</a> |
		<a href="create_tables.php">Create tables</a> |
		<a href="insert_data.php">Add data</a>
		<br />
