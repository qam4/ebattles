<?php
function show_db_results($result){
    if (($result)||(mysql_errno == 0))
    {
        echo 'Query Info: '.mysql_info();
        echo '<table class="fborder">';
        if (mysql_num_rows($result)>0)
        {
            $fields_num = mysql_num_fields($result);
            echo "<tr>";
            // printing table headers
            for($i=0; $i<$fields_num; $i++)
            {
                $field = mysql_fetch_field($result);
                echo "<td class='forumheader3'>{$field->table}<br/>{$field->name}</td>";
            }
            echo "</tr>";
            // printing table rows
            while($row = mysql_fetch_row($result))
            {
                echo "<tr>";

                // $row is array... foreach( .. ) puts every element
                // of $row to $cell variable
                foreach($row as $cell)
                echo "<td class='forumheader3'>$cell</td>";

                echo "</tr>";
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
