<?php
  include("../include/constants.php");
  require("config.php");

  $link = mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword);

  mysql_select_db($config_databaseName,$link);

  $url = $_SERVER["HTTP_REFERER"];
  
  if (strlen($url))
  {
    if (strpos($url,$config_baseHREF)===0)
    {
      $hash = md5($url);

      $sql = "INSERT INTO ".$config_databaseTablePrefix."urls SET hash='".$hash."',url='".mysql_escape_string($url)."'";

  mysql_query($sql,$link);
    }
  }
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

  header('Cache-Control: no-store, no-cache, must-revalidate, private, post-check=0, pre-check=0', FALSE);

  header('Pragma: no-cache');

  header("Content-Type: image/png");

  readfile("autositemap.png");

  exit();
?>