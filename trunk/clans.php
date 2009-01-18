<?php
/**
 * clans.php
 *
 */

require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");
/*******************************************************************
********************************************************************/
require_once(HEADERF);
$text = '';

/**
 * Display Clans Table
 */
displayClans();

if(check_class(e_UC_MEMBER))
{
   $text .= "<form action=\"".e_PLUGIN."ebattles/clancreate.php\" method=\"post\">";
   $text .= "<input type=\"hidden\" name=\"userid\" value=\"".USERID."\"></input>";
   $text .= "<input type=\"hidden\" name=\"username\" value=\"".USERNAME."\"></input>";
   $text .= "<input class=\"button\" type=\"submit\" name=\"createteam\" value=\"Create new team\"></input>";
   $text .= "</form>";
   $text .= "<br>";
}

$ns->tablerender('Teams', $text);
require_once(FOOTERF);
exit;


/***************************************************************************************
 Functions
***************************************************************************************/
/**
 * displayClans - Displays the Clans database table in
 * a nicely formatted html table.
 */
function displayClans(){
   global $sql;
   global $text;

   /* set pagination variables */
   $rowsPerPage = 5;
   $pg = (isset($_REQUEST['pg']) && ctype_digit($_REQUEST['pg'])) ? $_REQUEST['pg'] : 1;
   $start = $rowsPerPage * $pg - $rowsPerPage;

   $q = "SELECT count(*) "
       ." FROM ".TBL_CLANS;
   $result = $sql->db_Query($q);
   $totalPages = mysql_result($result, 0);
   
   $q = "SELECT ".TBL_CLANS.".*"
       ." FROM ".TBL_CLANS
       ." ORDER BY Name"
       ." LIMIT $start, $rowsPerPage";
       
   $result = $sql->db_Query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      $text .= "Error displaying info";
      return;
   }
   if($num_rows == 0){
      $text .= "Database table empty";
      return;
   }
   /* Display table contents */
   $text .= "<table class=\"type1Border\">\n";
   $text .= "<tr><td class=\"type1Header\"><b>Team</b></td><td class=\"type1Header\"><b>Tag</b></td></tr>\n";
   for($i=0; $i<$num_rows; $i++){
      $clanid  = mysql_result($result,$i, TBL_CLANS.".clanid");
      $cname  = mysql_result($result,$i, TBL_CLANS.".name");
      $ctag  = mysql_result($result,$i, TBL_CLANS.".tag");
      $cowner  = mysql_result($result,$i, TBL_CLANS.".owner");
      
      $text .= "<tr><td class=\"type1Body2\"><a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$clanid\">$cname</a></td><td class=\"type1Body2\">$ctag</td></tr>\n";
   }
   $text .= "</table><br />\n";

   $text .= paginate($rowsPerPage, $pg, $totalPages);
}
?>

