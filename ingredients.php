<?php include "header.inc";
   include "layout.inc";
   $sort = isset($_SESSION['sort']) ? $_SESSION['sort'] : "";
   $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : "";
   if(isset($_GET['sort']))
   {
      if($sort == $_GET['sort'] && ($sort_order == "" || $sort_order == "ASC"))
         $_SESSION['sort_order'] = "DESC";
      else
      {
         $_SESSION['sort_order'] = "ASC";
         $_SESSION['sort'] = $_GET['sort'];
      }

      $sort = $_SESSION['sort'];
      $sort_order = $_SESSION['sort_order'];
   }

   if(isset($_GET['format_select']))
      $_SESSION['format_select'] = $_GET['format_select'];
   if(isset($_GET['search']))
      $_SESSION['search'] = $_GET['search'];
   if(isset($_GET['ingredient_search']))
      $_SESSION['ingredient_search'] = $_GET['ingredient_search'];

   $format = isset($_SESSION['format_select']) ? $_SESSION['format_select'] : "";
   $search = isset($_SESSION['search']) ? $_SESSION['search'] : "";
   $ingredient = isset($_SESSION['ingredient_search']) ? $_SESSION['ingredient_search'] : "";
   $sort = isset($_SESSION['sort']) ? $_SESSION['sort'] : $sort;
   $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : $sort_order;

   if(!$sort)
      $sort = "name";

?>
<?php render_page_start('Ingredient Search', 'Ingredient Search', 'Ingredients'); ?>
<section class="card">
<FORM METHOD="GET">
   <TABLE class="form-table">
   <TR><TD>Format<TD><SELECT NAME="format_select">
    <?php print_format_options($format); ?>
    </SELECT><BR>
   <TR><TD>Ingredient name<TD><INPUT TYPE="TEXT" NAME="ingredient_search" VALUE="<?php echo $ingredient;?>">
   </TABLE>
   <INPUT TYPE="SUBMIT" NAME="search" VALUE="Search">
</FORM><BR>
<div class="inline-actions">
<A HREF="editingredient.php">New Ingredient</A>
<A HREF="index.php">Search Recipes</A>
</div>
<BR>
</section>
<?php
   if(isset($search))
   {
      $query = "SELECT id, name, ROUND(cost / `size`, 3) cost_oz, ROUND(`size`, 1) `size`, ROUND(cost, 2) cost, ROUND(calories / serving_size, 2) cals_serv FROM ingredients WHERE name LIKE '%$ingredient%'";
      $result = dbquery($query, $dbh) or die("Error searching for ingredients: " . db_error() . "<br>Query was: " . $query);
      if(!$result)
      {
         echo "No matching ingredients found.";
         exit;
      }
?>
<section class="card"><div class="table-wrap"><TABLE cellpadding=1 cellspacing=0 border=1>
   <COL width='0*'><COL width='30'>
<?php if($format == "Wide") { ?>
   <COL width='30'><COL width='30'><COL width='30'>
<?php } ?>
<THEAD>
<TR><TH>Name<TH>$/oz
<?php
      if($format == "Wide")
	 echo "<TH>Size<TH>Cost<TH>E.D.";
      echo "\n\r<TBODY>\n\r";
      while($row = db_fetch_array($result))
      {
	 $id = $row['id'];
	 $name = $row['name'];
	 $cost_oz = $row['cost_oz'];
	 $size = $row['size'];
	 $cost = $row['cost'];
	 $cals_serv = $row['cals_serv'];
	 echo "<TR><TD><A HREF=\"editingredient.php?ingredientid=$id&format_select=$format&search=$search&ingredient_search=$ingredient\">$name</A><TD align='right'>$$cost_oz";
	 if($format == "Wide")
	    echo "<TD align='right'>$size oz<TD align='right'>$$cost<TD align='right'>$cals_serv\n\r";
      }
      echo "</TABLE></div></section>\n\r";
   }
?>
<?php render_page_end(); ?>
