<?php
include("../include/constants.php");
  if (!file_exists("config.php"))
  {
    print "config.php not found, please copy config.php.dist to config.php to continue";

    exit();
  }

  require("config.php");

  if (isset($_GET["installDB"]))
  {
    $link = mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword);

    mysql_select_db($config_databaseName,$link);

    $fp = fopen("setup.sql","r");

    $data = "";

    while(!feof($fp))
    {
      $line = trim(fgets($fp));

      if (($line) && (substr($line,0,2) <> "--"))
      {
        $data .= $line;
      }
    }

    fclose($fp);

    if ($config_databaseTablePrefix)
    {
      $data = str_replace("CREATE TABLE ","CREATE TABLE ".$config_databaseTablePrefix,$data);
    }

    $queries = explode(";",$data);

    foreach($queries as $sql)
    {
      if ($sql)
      {
        mysql_query($sql);
      }
    }

    header("Location: setup.php");

    exit();
  }

  function testDatabaseConnection()
  {
    global $config_databaseServer;

    global $config_databaseUsername;

    global $config_databasePassword;

    if (@mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword))
    {
       return true;
    }
    else
    {
       return false;
    }
  }

  function testDatabaseSelection()
  {
    global $config_databaseServer;

    global $config_databaseUsername;

    global $config_databasePassword;

    global $config_databaseName;

    if ($link = @mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword))
    {
      if (@mysql_select_db($config_databaseName,$link))
      {
         return true;
      }
    }

    return false;
  }

  function testDatabaseTables()
  {
    global $config_databaseServer;

    global $config_databaseUsername;

    global $config_databasePassword;

    global $config_databaseName;

    global $config_databaseTablePrefix;

    if ($link = @mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword))
    {
      if (@mysql_select_db($config_databaseName,$link))
      {
        if (mysql_query("SELECT count(*) AS num FROM `".$config_databaseTablePrefix."urls`",$link))
        {
          return true;
        }
      }
    }

    return false;
  }

  function test($description,$function,$help,$noPreFailHelp)
  {
    global $testsFailed;

    print "<p>";

    print $description;

    $result = $function();

    print ($result ? "PASS" : "FAIL <strong> ".$help.($testsFailed?"":$noPreFailHelp)."</strong>");

    print "</p>";

    return ($result ? 0 : 1);
  }

  print "<style type='text/css'>body {font-family:sans;font-size:12px;}</style>";

  $testsFailed = 0;

  $testsFailed += test("Checking database connection...","testDatabaseConnection","check \$config_databaseUsername and \$config_databasePassword values","");

  $testsFailed += test("Checking database selection...","testDatabaseSelection","","check \$config_databaseName value");

  $testsFailed += test("Checking database tables...","testDatabaseTables","","<a href='?installDB=1'>click here to install tables</a>");

  if (!$testsFailed)
  {
    $base = $_SERVER["REQUEST_URI"];

    $base = substr($base,0,strrpos($base,"/"));

    $trackingHTML = "<a href='http://www.autositemap.com/'><img border='0' src='".$base."/autositemap.php' alt='Google Sitemap Generator'></a>";

    $base = substr($base,0,strrpos($base,"/"));

    $sitemapURL = "http://".$_SERVER["HTTP_HOST"].$base."/sitemap.php";

    print "<p>Setup Completed.</p>";

    print "<p>To use AutoSitemap, make sure the following HTML is displayed on each page of your site:</p>";

    print "<p><textarea rows='3' cols='100'>".htmlentities($trackingHTML)."</textarea></p>";

    print "<p>Your sitemap URL (register this with Google Sitemaps):</p>";

    print "<p><a href='".$sitemapURL."'>".$sitemapURL."</a></p>";

    print "<p>Using PHP Version ".phpversion()."</p>";

    print "<p>Using MySQL Version ".@mysql_get_server_info()."</p>";
  }
?>