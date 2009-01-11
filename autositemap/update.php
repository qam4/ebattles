<?php
  require("config.php");

  $link = mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword);

  mysql_select_db($config_databaseName,$link);

  $sql = "ALTER TABLE `".$config_databaseTablePrefix."urls` CHANGE `url` `url` VARCHAR( 1000 ) NOT NULL";

  mysql_query($sql,$link);

  print "Done.";

  exit();
?>