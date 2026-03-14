<?php
   require "header.inc";
   include "layout.inc";

   
   $recipeid = request_int('recipeid', 0);

   if(request_bool('new', $_POST))
   {
      $recipeid = 0;
      $new = true;
   }
   else
      $new = false;

   $save = request_bool('save', $_POST);
   $delete = request_bool('delete', $_POST);

   $name = "";
   $time = "";
   $category = 0;
   $servings = "";
   $calories = "";
   $ed = "";
   $carbs = "";
   $fat = "";
   $protein = "";
   $fiber = "";
   $instructions = "";

   $format_select = request_value('format_select', '', $_POST);
   $search = request_value('search', '', $_POST);
   $recipe_search = request_value('recipe_search', '', $_POST);
   $category_select = request_int('category_select', 0, $_POST);
   $idisplay = request_int('idisplay', 0, $_POST);


   if(isset($_POST['edit']) || ($recipeid && !$delete))
      $edit = true;
   else
      $edit = false;

   if(!$save && !$edit && !$new && !$recipeid)
   {
      $recipeid = 0;
      $new = true;
   }

   if($save)
   {
      $name = request_value('name', '', $_POST);
      $time = request_value('time', '', $_POST);
      $category = request_int('category', 0, $_POST);
      $servings = request_value('servings', '', $_POST);
      $calories = request_value('calories', '', $_POST);
      $ed = request_value('ed', '', $_POST);
      $carbs = request_value('carbs', '', $_POST);
      $fat = request_value('fat', '', $_POST);
      $protein = request_value('protein', '', $_POST);
      $fiber = request_value('fiber', '', $_POST);
      $instructions = request_value('instructions', '', $_POST);


      if(!$recipeid)
      {
	 $query = "INSERT INTO recipes (name, `time`, category, servings, calories, energy_density, carbs, fat, protein, fiber, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	 $params = array($name, $time, $category, $servings, $calories, $ed, $carbs, $fat, $protein, $fiber, $instructions);
	 $types = 'ssissssssss';
      }
      else
      {
	 $query = "UPDATE recipes SET name = ?, `time` = ?, category = ?, servings = ?, calories = ?, energy_density = ?, carbs = ?, fat = ?, protein = ?, fiber = ?, instructions = ? WHERE id = ?";
	 $params = array($name, $time, $category, $servings, $calories, $ed, $carbs, $fat, $protein, $fiber, $instructions, $recipeid);
	 $types = 'ssissssssssi';
      }

      dbquery_prepared($query, $types, $params, $dbh) or die("Error updating records: " . db_error($dbh));
      echo "Recipe saved successfully.<BR>";
      echo "<FORM METHOD='POST'><INPUT TYPE=\"HIDDEN\" NAME=\"format_select\" VALUE=\"" . h($format_select) . "\"><INPUT TYPE=HIDDEN NAME=\"category_select\" VALUE=\"" . h($category_select) . "\"><INPUT TYPE=\"HIDDEN\" NAME=\"recipe_search\" VALUE=\"" . h($recipe_search) . "\"><INPUT TYPE=\"HIDDEN\" NAME=\"search\" VALUE=\"" . h($search) . "\"><INPUT TYPE='Submit' NAME='new' VALUE='Add another'></FORM>";
      if(!$recipeid)
	 $recipeid = db_insert_id($dbh);
      $edit = 1;
   }


   if($edit)
   {
      if(!$recipeid)
      {
         echo "You must select a recipe to edit.";
         exit;
      }

      $result = dbquery_prepared("SELECT name, `time`, category, servings, calories, energy_density, carbs, fat, protein, fiber, instructions FROM recipes WHERE id = ?", 'i', array($recipeid), $dbh) or die("Error in query: " . db_error($dbh));
      if(!$result || !($row = db_fetch_array($result)))
      {
         echo "Recipe not found.: $recipeid";
         exit;
      }
      $name = $row['name'];
      $time = $row['time'];
      $category = $row['category'];
      $servings = $row['servings'];
      $calories = $row['calories'];
      $ed = $row['energy_density'];
      $carbs = $row['carbs'];
      $fat = $row['fat'];
      $protein = $row['protein'];
      $fiber = $row['fiber'];
      $instructions = $row['instructions'];
   }
   else if($delete)
   {
      if(!request_confirmed('deleteconfirm', $_POST))
      {
         echo "You must select 'Yes' from the confirmation pull down option.";
         exit;
      }

      if(!$recipeid)
      {
         echo "Recipe not found.";
         exit;
      }

      dbquery_prepared("DELETE FROM recipes WHERE id = ?", 'i', array($recipeid), $dbh) or die("Error deleting record: " . db_error());
      echo "Recipe deleted.<BR>";
      echo "<A HREF=\"index.php\">&gt; Back to recipes search page &lt;</A><BR>";
      exit;
   }
?>
<?php render_page_start('Recipe Editor', 'Recipe Editor', 'Edit Recipe'); ?>
<section class="card">
<p><a href="index.php">Back to Search Page</a></p>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<FORM NAME="form1" METHOD="POST">
   <TABLE class="form-table">
   <TR><TD>Name:           <TD><INPUT TYPE="TEXT" NAME="name"     VALUE="<?php echo $name?>" ID="name" SIZE=70>
   <TR><TD>Category:       <TD><SELECT NAME="category"><?php print_category_options($category); ?></SELECT>
   <TR><TD>Time:           <TD><INPUT TYPE="TEXT" NAME="time"     VALUE="<?php echo $time?>">
   <TR><TD>Servings:       <TD><INPUT TYPE="TEXT" NAME="servings" VALUE="<?php echo $servings?>">
   <TR><TD>Calories:       <TD><INPUT TYPE="TEXT" NAME="calories" VALUE="<?php echo $calories?>">
   <TR><TD>Energy Density: <TD><INPUT TYPE="TEXT" NAME="ed"       VALUE="<?php echo $ed?>">
   <TR><TD>Carbs           <TD><INPUT TYPE="TEXT" NAME="carbs"    VALUE="<?php echo $carbs?>">
   <TR><TD>Fat:            <TD><INPUT TYPE="TEXT" NAME="fat"      VALUE="<?php echo $fat?>">
   <TR><TD>Protein         <TD><INPUT TYPE="TEXT" NAME="protein"  VALUE="<?php echo $protein?>">
   <TR><TD>Fiber:          <TD><INPUT TYPE="TEXT" NAME="fiber"    VALUE="<?php echo $fiber?>">
</TABLE></p>
<H3>Instructions:</H3>
<?php
   echo '<TEXTAREA NAME="instructions" ID="instructions" COLS="100" ROWS="20">' . htmlspecialchars($instructions) . '</TEXTAREA>';
?><BR>
<INPUT TYPE="HIDDEN" NAME="recipeid" VALUE="<?php echo $recipeid?>">
<div class="inline-actions">
<INPUT TYPE="SUBMIT" NAME="save" VALUE="Save">
<label><input type="checkbox" name="deleteconfirm" value="1"> Confirm delete</label>
<INPUT TYPE="SUBMIT" NAME="delete" VALUE="Delete">
</div>
</FORM><br>
</section>
<section class="card">
<A HREF="editrecipeingredients.php?recipeid=<?php echo $recipeid;?>">Modify ingredients for this recipe</A>
</section>
<section class="card table-wrap">
<?php
if($recipeid)
   list($total_oz, $total_cost, $count, $total_calories, $total_carbs, $total_fat, $total_protein, $total_fiber) = print_ingredients($recipeid, true, true, $idisplay);
else
   list($total_oz, $total_cost, $count, $total_calories, $total_carbs, $total_fat, $total_protein, $total_fiber) = array(0, 0, 0, 0, 0, 0, 0, 0);
?><br><br>
<?php
   if($total_oz > 0)
      $ed = round($total_calories / ($total_oz * 28.3495231), 2);
   else
      $ed = 0;

   if($servings > 0)
   {
      $calories = round($total_calories / $servings, 1);
      $carbs = round($total_carbs / $servings, 1);
      $fat = round($total_fat / $servings, 1);
      $protein = round($total_protein / $servings, 1);
      $fiber = round($total_fiber / $servings, 1);
   }
   else
   {
      $calories = 0;
      $carbs = 0;
      $fat = 0;
      $protein = 0;
      $fiber = 0;
   }
?>
<TABLE cellpadding=1 cellspacing=0 border=1>
<TR><TH>Calories<TH>Energy Density<TH>Carbs<TH>Fat<TH>Protein<TH>Fiber
<TR><TD><?php echo $calories?><TD><?php echo $ed?><TD><?php echo $carbs?><TD><?php echo $fat?><TD><?php echo $protein?><TD><?php echo $fiber?>
</TABLE>
</section>
<script>
if (window.ClassicEditor) {
  ClassicEditor.create(document.querySelector('#instructions')).catch(function () {});
}
</script>
<?php render_page_end(); ?>
