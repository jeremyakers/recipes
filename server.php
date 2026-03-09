<?php
require "header.inc";

$searchterm = '';
if(isset($_POST['searchterm']))
   $searchterm = addslashes($_POST['searchterm']);

echo "<ul>";
$sql = "SELECT name FROM ingredients WHERE name LIKE '%" . $searchterm . "%'";
$rs = dbquery($sql);
while($data = db_fetch_array($rs))
{
   echo "<li>" . stripslashes($data['name']) . "</li>";
}
echo "</ul>";
?>
