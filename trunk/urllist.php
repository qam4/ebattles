<?php
  include("./include/constants.php");
  require("autositemap/config.php");

  header("Content-Type: application/octet-stream");

  header("Content-Disposition: attachment; filename=urllist.txt");

  $base = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];

  $base = substr($base,0,strrpos($base,"/")+1);

  $link = mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword);

  mysql_select_db($config_databaseName,$link);

  $sql = "SELECT * FROM ".$config_databaseTablePrefix."urls";

  $result = mysql_unbuffered_query($sql,$link);

  while($row = mysql_fetch_array($result,MYSQL_ASSOC))
  {
    print $row["url"]."\n";
  }

  exit();
?>