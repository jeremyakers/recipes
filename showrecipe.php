<?php include "header.inc"; 
include "layout.inc";

   $recipe = request_int('recipeid', 0, $_GET);
   if(!$recipe)
   {
      echo "You must choose a valid recipe from <A HREF=\"index.php\">the index page</A><BR>";
      exit;
   }

   $query = "SELECT recipes.id, category category_id, categories.name category_name, recipes.name recipe_name, `time`, servings, calories, energy_density, carbs, fat, protein, fiber, instructions FROM recipes INNER JOIN categories ON recipes.category = categories.id WHERE recipes.id = ?";
   $result = dbquery_prepared($query, 'i', array($recipe), $dbh);
   if(!$result || !($row = db_fetch_array($result)))
   {
      echo "No matching recipes found.";
      exit;
   }

   $name = $row['recipe_name'];
   $time = $row['time'];
   $category = $row['category_id'];
   $servings = $row['servings'];
   $calories = $row['calories'];
   $ed = $row['energy_density'];
   $carbs = $row['carbs'];
   $fat = $row['fat'];
   $protein = $row['protein'];
   $fiber = $row['fiber'];
   $instructions = $row['instructions'];
?>

<?php render_page_start('Recipe Details for: ' . $name, $name, 'Recipe Detail'); ?>
<section class="card">
<TABLE class="form-table">
<TR><TD>Time (minutes):<TD><?php echo h($time); ?>
<TR><TD>Servings:<TD><?php echo h($servings); ?>
<TR><TD>Calories:<TD><?php echo h($calories); ?>
<TR><TD>Energy density:<TD><?php echo h($ed); ?>
<TR><TD>Carbs:<TD><?php echo h($carbs); ?>
<TR><TD>Fat:<TD><?php echo h($fat); ?>
<TR><TD>Protein:<TD><?php echo h($protein); ?>
<TR><TD>Fiber:<TD><?php echo h($fiber); ?>
</TABLE>
<BR>
<FORM ACTION="editrecipe.php" METHOD="POST"><INPUT TYPE="HIDDEN" NAME="recipeid" VALUE="<?php echo h($recipe)?>"><INPUT TYPE="SUBMIT" NAME="edit" VALUE="Edit this recipe"></FORM>
</section>
<section class="card table-wrap"><?php print_ingredients($recipe, false, true, 0); ?></section>
<section class="card">
<H3>Instructions</H3>
<?php echo $instructions;?>
</section>
<?php render_page_end(); ?>
