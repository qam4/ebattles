<?php
session_start();
?>
<html>
	<head>
	<title>eBattles</title>
	</head>

	<body>
	<font size=1 face=verdana>
		Current date: 
        <?php
        $time = time();
        $date = Date("d M Y, h:i:s A", $time);
        print ($date); 
        ?>
		<h1>eBattles</h1>
		<br>
		The site is currently being updated.<br>
		Please come back later...
		<br>
	</body>
</html>
