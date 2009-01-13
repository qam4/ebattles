<?php
/**
 * clans.php
 *
 */

require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
include_once(e_PLUGIN."ebattles/include/pagination.php");

/**
 * displayClans - Displays the Clans database table in
 * a nicely formatted html table.
 */
function displayClans(){
   global $sql;

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
      echo "Error displaying info";
      return;
   }
   if($num_rows == 0){
      echo "Database table empty";
      return;
   }
   /* Display table contents */
   echo "<table class=\"type1Border\">\n";
   echo "<tr><td class=\"type1Header\"><b>Team</b></td><td class=\"type1Header\"><b>Tag</b></td><td class=\"type1Header\"><b>Owner</b></td></tr>\n";
   for($i=0; $i<$num_rows; $i++){
      $clanid  = mysql_result($result,$i, TBL_CLANS.".clanid");
      $cname  = mysql_result($result,$i, TBL_CLANS.".name");
      $ctag  = mysql_result($result,$i, TBL_CLANS.".tag");
      $cowner  = mysql_result($result,$i, TBL_CLANS.".owner");
      
      echo "<tr><td class=\"type1Body2\"><a class=\"type1Border\" href=\"".e_PLUGIN."ebattles/claninfo.php?clanid=$clanid\">$cname</a></td><td class=\"type1Body2\">$ctag</td><td class=\"type1Body2\">$cowner</td></tr>\n";
   }
   echo "</table><br />\n";

   paginate($rowsPerPage, $pg, $totalPages);
   echo "<br />";
   
}
?>



<div id="main">
<h1>Teams</h1>
<div class="news">
<h2>Teams</h2>
<?php
if(check_class(e_UC_MEMBER))
{
   echo "<form action=\"".e_PLUGIN."ebattles/clancreate.php\" method=\"post\">";
   echo "<input type=\"hidden\" name=\"userid\" value=\"".USERID."\"></input>";
   echo "<input type=\"submit\" name=\"createteam\" value=\"Create new Team\"></input>";
   echo "</form>";
   echo "<br>";
}
?>
<?php
/**
 * Display Clans Table
 */
?>
<?php
displayClans();
?>
</div>

<p>
Back to [<a href="./index.php">Main Page</a>]
</p>

</div>
<?php
include_once(e_PLUGIN."ebattles/include/footer.php");
?>
