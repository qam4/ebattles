<?php
function show_db_results($result){
if (($result)||(mysql_errno == 0))
{
  echo "<table cellspacing=\"0\" border=\"2\" width='100%'>\n<tr>";
  if (mysql_num_rows($result)>0)
  {
     //loop thru the field names to print the correct headers
     $i = 0;
     while ($i < mysql_num_fields($result))
     {
       echo "<th>". mysql_field_name($result, $i) . "</th>";
       $i++;
     }
     echo "</tr>";
   
     //display the data
     while ($rows = mysql_fetch_array($result,MYSQL_ASSOC))
     {
        echo "<tr>";
        foreach ($rows as $data)
        {
          echo "<td align='center'>". $data . "</td>";
        }
     }
  }else
  {
     echo "<tr><td colspan='" . ($i+1) . "'>No Results found!</td></tr>";
  }
  echo "</table>";
}
else
{
   echo "Error in running query :". mysql_error();
} 
}
?>