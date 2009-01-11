<?php
function do_offset($level){
    $offset = "";             // offset for subarry 
    for ($i=1; $i<$level;$i++){
    $offset = $offset . "<td></td>";
    }
    return $offset;
}

function show_array($array, $level, $sub){
    if (is_array($array) == 1){          // check if input is an array
       foreach($array as $key_val => $value) {
           $offset = "";
           if (is_array($value) == 1){   // array is multidimensional
           echo "<tr>";
           $offset = do_offset($level);
           echo $offset . "<td>" . $key_val . "</td>";
           show_array($value, $level+1, 1);
           }
           else{                        // (sub)array is not multidim
           if ($sub != 1){          // first entry for subarray
               echo "<tr nosub>";
               $offset = do_offset($level);
           }
           $sub = 0;
           echo $offset . "<td main ".$sub." width=\"120\">" . $key_val . 
               "</td><td width=\"120\">" . $value . "</td>"; 
           echo "</tr>\n";
           }
       } //foreach $array
    }  
    else{ // argument $array is not an array
        return;
    }
}

function html_show_array($array){
  echo "<table cellspacing=\"0\" border=\"2\">\n";
  show_array($array, 1, 0);
  echo "</table>\n";
}

function html_show_table($array, $rows, $columns)
{
   echo "<table class=\"type1\">\n";
      
   for ($i=0; $i<$rows; $i++)
   {
     echo "<tr>\n";
     for($j=1; $j<=$columns; $j++)
     {
       if (strcasecmp($array[$i][0],"header")==0)
       {
            echo "<td class=\"type1Header\">".$array[$i][$j]."</td>";
       }
       elseif (strcasecmp($array[$i][0],"row_highlight")==0)
       {
            echo "<td class=\"highlight\">".$array[$i][$j]."</td>";
       }
       elseif ( $i % 2 == 1 )
       {
            echo "<td class=\"type1Body\">".$array[$i][$j]."</td>";
       }
       else
       {
            echo "<td class=\"type1Body2\">".$array[$i][$j]."</td>";
       }
     }
     echo "</tr>\n";
   }
   echo "</table>";
}
?> 