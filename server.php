<?
  require "header.inc";
 echo "<ul>";
 $sql = "SELECT name FROM ingredients WHERE name LIKE '%" . $_POST['searchterm'] . "%'";
 $rs = dbquery($sql);
 while($data = mysql_fetch_assoc($rs)) 
 {
    echo "<li>" . stripslashes($data['name']) . "</li>";
 }
 echo "</ul>";
 ?>
