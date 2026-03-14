<?php

include "header.inc";
include "layout.inc";

if(isset($_POST['update']))
{
   foreach($_POST['id'] AS $id)
   {
      $size = $_POST['size'][$id];
      $cost = $_POST['cost'][$id];

      $query = "UPDATE ingredients SET `size` = ?, cost = ? WHERE id = ?";
      dbquery_prepared($query, 'ddi', array((float)$size, (float)$cost, (int)$id), $dbh) or die("Error updating ingredients: " . db_error());
      echo "Updated successfully.<BR>";
   }
}

$query = "SELECT id, name, `size`, cost FROM ingredients WHERE cost = 0";
$result = dbquery($query, $dbh) or die("Error searching for ingredients: " . db_error() . "<br>Query was: " . $query);
if(!$result)
{
   echo "No matching ingredients found.";
   exit;
}
?>
<?php render_page_start('Ingredient Price List', 'Ingredient Price List', 'Price Report'); ?>
<section class="card table-wrap">
<FORM METHOD="POST">
<TABLE CELLPADDING=10 CELLSPACING=3 BORDER=1>
<tr><td>Name</td><td>Size in ounces</td><TD>Cost in dollars</TD>
<?php
      while($row = db_fetch_array($result))
      {
	 $id = $row['id'];
	 $name = $row['name'];
	 $size = $row['size'];
	 $cost = $row['cost'];
         echo "<tr><td><INPUT TYPE='HIDDEN' NAME='id[]' VALUE='" . h($id) . "'>" . h($name) . "</td><td><INPUT TYPE='TEXT' NAME='size[$id]' VALUE='" . h($size) . "'></td><td><INPUT TYPE='TEXT' NAME='cost[$id]' VALUE='" . h($cost) . "'></td>\n";
      }
?>
</TABLE>
<div class="inline-actions"><INPUT TYPE="SUBMIT" NAME="update" VALUE="Update"></div>
</FORM>
</section>
<?php render_page_end(); ?>
