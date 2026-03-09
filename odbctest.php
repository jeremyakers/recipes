<?php
   $COMPOSITE_HOME="/opt/Composite_Software/CIS_6.2.0";
   putenv("COMPOSITE_HOME=$COMPOSITE_HOME");
   putenv("ODBCHOME=$COMPOSITE_HOME/apps/odbc64");
   putenv("ODBCINI=$COMPOSITE_HOME/odbc.ini");
   putenv("ODBCINSTINI=$COMPOSITE_HOME/odbcinst.ini");
   $LD_LIBRARY_PATH = getenv("LD_LIBRARY_PATH");
   putenv("LD_LIBRARY_PATH=$COMPOSITE_HOME/apps/odbc64/lib:$LD_LIBRARY_PATH");
   $dbh = odbc_connect("recipes", "", "") or die('I cannot connect to the database:' . odbc_errormsg() . ': ' . odbc_error());

   $query = "SELECT * FROM categories";
   $result = odbc_exec($dbh, $query);

   while(($row = odbc_fetch_array($result)))
   {
      echo 'ID: ' . $row['id'] . ', name: ' . $row['name'] . "\n";
   }
   echo "Finished.\n";
?>
