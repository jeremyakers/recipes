<HTML>
<HEAD><TITLE>Ingredient Price List</TITLE></HEAD>
<BODY>
<?

include "header.inc";

if(isset($_POST['update']))
{
   foreach($_POST['id'] AS $id)
   {
      $size = $_POST['size'][$id];
      $cost = $_POST['cost'][$id];

      $query = "UPDATE ingredients SET \"size\" = '$size', cost = '$cost' WHERE id = '$id'";
      dbquery($query, $dbh) or die("Error updating ingredients: " . db_error() . "<br>Query was: " . $query);
      echo "Updated successfully.<BR>";
   }
}

$query = "SELECT id, name, \"size\", cost FROM ingredients WHERE cost = 0";
$result = dbquery($query, $dbh) or die("Error searching for ingredients: " . db_error() . "<br>Query was: " . $query);
if(!$result)
{
   echo "No matching ingredients found.";
   exit;
}
?>
<FORM METHOD="POST">
<TABLE CELLPADDING=10 CELLSPACING=3 BORDER=1>
<tr><td>Name</td><td>Size in ounces</td><TD>Cost in dollars</TD>
<?
      while($row = db_fetch_array($result))
      {
	 $id = $row['id'];
	 $name = $row['name'];
	 $size = $row['size'];
	 $cost = $row['cost'];
         echo "<tr><td><INPUT TYPE='HIDDEN' NAME='id[]' VALUE='$id'>$name</td><td><INPUT TYPE='TEXT' NAME='size[$id]' VALUE='$size'></td><td><INPUT TYPE='TEXT' NAME='cost[$id]' VALUE='$cost'></td>\n";
      }
?>
</TABLE>
<INPUT TYPE="SUBMIT" NAME="update" VALUE="Update">
</FORM>
</BODY>
</HTML>
