<?php
  include("./include/constants.php");
  require("autositemap/config.php");

  header("Content-Type: text/xml");

  print "<?xml version='1.0' encoding='UTF-8'?>";

  $pageLimit = 50000;

  $base = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];

  $base = substr($base,0,strrpos($base,"/")+1);

  $link = mysql_connect($config_databaseServer,$config_databaseUsername,$config_databasePassword);

  mysql_select_db($config_databaseName,$link);

  if (isset($_GET["page"]))
  {
    print "<urlset xmlns='http://www.google.com/schemas/sitemap/0.84' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd'>";

    $page = intval($_GET["page"]);

    $from = (($page-1)*$pageLimit);

    $sql = "SELECT * FROM ".$config_databaseTablePrefix."urls LIMIT ".$from.",".$pageLimit;

    $result = mysql_unbuffered_query($sql,$link);

    while($row = mysql_fetch_array($result,MYSQL_ASSOC))
    {
      print "<url>";

      print "<loc>".xmlentities($row["url"])."</loc>";

      print "</url>";
    }

    print "</urlset>";
  }
  else
  {
    print "<sitemapindex xmlns='http://www.google.com/schemas/sitemap/0.84' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/siteindex.xsd'>";

    $sql = "SELECT count(*) as count FROM ".$config_databaseTablePrefix."urls";

    $result = mysql_query($sql,$link);

    $row = mysql_fetch_array($result,MYSQL_ASSOC);

    $pages = ceil($row["count"] / $pageLimit);

    for($i=1;$i<=$pages;$i++)
    {
      print "<sitemap>";

      $loc = $base."sitemap.php?page=".$i;

      print "<loc>".xmlentities($loc)."</loc>";

      print "</sitemap>";
    }

    print "</sitemapindex>";
  }

  exit();

  function xmlentities($text)
  {
    $search = array('&','<','>','"','\'');

    $replace = array('&amp;','&lt;','&gt;','&quot;','&apos;');

    $text = str_replace($search,$replace,$text);

    return $text;
  }
?>