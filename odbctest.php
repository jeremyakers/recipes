<?php
   require "header.inc";

   $query = "SELECT * FROM categories";
   $result = dbquery($query, $dbh);

   while(($row = db_fetch_array($result)))
   {
      echo 'ID: ' . $row['id'] . ', name: ' . $row['name'] . "\n";
   }
   echo "Finished.\n";
?>
