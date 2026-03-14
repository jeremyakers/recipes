<?php
   require "header.inc";
   include "layout.inc";

   $name = "";
   $size = "";
   $cost = "";
   $units = "";
   $recipe = 0;
   $serving_size = "";
   $calories = "";
   $carbs = "";
   $fat = "";
   $protein = "";
   $fiber = "";
   $ounces_cup = "";
   $nametext = "";
   $ndtext = "";
   $voltext = "";
   
   $ingredientid = request_int('ingredientid', 0);

   if(request_bool('new', $_POST))
   {
      $ingredientid = 0;
      $new = true;
   }
   else
      $new = false;

   $save = request_bool('save', $_POST);
   $edit = request_bool('edit', $_POST);
   $delete = request_bool('delete', $_POST);
   $returnrecipe = request_int('returnrecipe', 0);
   $format_select = request_value('format_select', '');
   $search = request_value('search', '');
   $ingredient_search = request_value('ingredient_search', '');
   $ndurl = request_value('ndurl', '', $_POST);
   $ndlookup = request_value('ndlookup', '', $_POST);


   if(!$save && !$edit && !$new && !$ingredientid)
   {
      $ingredientid = 0;
      $new = true;
   }

   if(!$delete && !$save && !$edit && !$new && $ingredientid)
      $edit = true;


   if($save)
   {
      $name = request_value('name', '', $_POST);
      $size = request_value('size', '', $_POST);
      $cost = request_value('cost', '', $_POST);
      $units = request_value('units', '', $_POST);
      $recipe = request_int('recipe', 0, $_POST);
      $serving_size = request_value('serving_size', '', $_POST);
      $calories = request_value('calories', '', $_POST);
      $carbs = request_value('carbs', '', $_POST);
      $fat = request_value('fat', '', $_POST);
      $protein = request_value('protein', '', $_POST);
      $fiber = request_value('fiber', '', $_POST);
      $ounces_cup = request_value('ounces_cup', '', $_POST);
      $weight_select = request_int('weight_select', 0, $_POST);
      $volume_select = request_int('volume_select', 0, $_POST);

      $unit = request_int('unit', 0, $_POST);
      $mult = find_unit_mult($unit);
      if($mult == 0)
         $mult = find_count_mult($ingredientid);

      $size = $size * $mult;

      $mult = find_unit_mult($weight_select);
      $in_cup = find_units_cup($volume_select);

      $ounces_cup = $ounces_cup * $mult * $in_cup;
      if(!$ingredientid)
      {
	 $query = "INSERT INTO ingredients (name, `size`, cost, units, recipe, serving_size, calories, carbs, fat, protein, fiber, ounces_cup) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	 $params = array($name, $size, $cost, $units, $recipe, $serving_size, $calories, $carbs, $fat, $protein, $fiber, $ounces_cup);
	 $types = 'sdddiddddddd';
      }
      else
      {
	 $query = "UPDATE ingredients SET name = ?, `size` = ?, cost = ?, units = ?, recipe = ?, serving_size = ?, calories = ?, carbs = ?, fat = ?, protein = ?, fiber = ?, ounces_cup = ? WHERE id = ?";
	 $params = array($name, $size, $cost, $units, $recipe, $serving_size, $calories, $carbs, $fat, $protein, $fiber, $ounces_cup, $ingredientid);
	 $types = 'sdddidddddddi';
      }

      dbquery_prepared($query, $types, $params, $dbh) or die("Error updating records: " . db_error($dbh));
      echo "Ingredient saved successfully.<BR>";
      echo "<FORM METHOD='POST'>";
      if($returnrecipe) 
	 echo "<INPUT TYPE=\"HIDDEN\" NAME=\"returnrecipe\" VALUE=\"" . h($returnrecipe) . "\">";
      echo "<INPUT TYPE=\"HIDDEN\" NAME=\"format_select\" VALUE=\"" . h($format_select) . "\"><INPUT TYPE=\"HIDDEN\" NAME=\"recipe_search\" VALUE=\"" . h($ingredient_search) . "\"><INPUT TYPE=\"HIDDEN\" NAME=\"search\" VALUE=\"" . h($search) . "\"><INPUT TYPE='Submit' NAME='new' VALUE='Add another'></FORM>";
      if(!$ingredientid)
      {
	 $ingredientid = db_insert_id($dbh);
      }
      $edit = 1;
   }


   if($edit)
   {
      if(!$ingredientid)
      {
         echo "You must select an ingredient to edit.";
         exit;
      }

      $result = dbquery_prepared("SELECT name, `size`, cost, recipe, serving_size, calories, carbs, fat, protein, fiber, units, ounces_cup FROM ingredients WHERE id = ?", 'i', array($ingredientid), $dbh) or die("Error in query: " . db_error($dbh));
      if(!$result || !($row = db_fetch_array($result)))
      {
         echo "Ingredient not found: $ingredientid";
         exit;
      }
      $name = $row['name'];
      $size = $row['size'];
      $cost = $row['cost'];
      $recipe = $row['recipe'];
      $serving_size = $row['serving_size'];
      $calories = $row['calories'];
      $carbs = $row['carbs'];
      $fat = $row['fat'];
      $protein = $row['protein'];
      $fiber = $row['fiber'];
      $units = $row['units'];
      $ounces_cup = $row['ounces_cup'];
   }
   else if($delete)
   {
       if(!request_confirmed('deleteconfirm', $_POST))
       {
	 echo "You must select 'Yes' from the confirmation pull down option.";
	 exit;
      }
      if(!$ingredientid)
      {
         echo "Ingredient not found.";
         exit;
      }

       dbquery_prepared("DELETE FROM ingredients WHERE id = ?", 'i', array($ingredientid), $dbh) or die("Error deleting record: " . db_error());
      echo "Ingredient deleted.<BR>";
      if($returnrecipe)
         echo "<A HREF='editrecipe.php?recipeid=$returnrecipe'>&gt; Return to recipe editor &lt;</A><BR>";
      echo "<A HREF=\"ingredients.php\">&gt; Back to Ingredients Search Page &lt;</A><BR>";
      exit;
   }
   
   $ndfoodid = request_int('ndfoodid', 0, $_POST);
   $usda_api_key = usda_api_key();

   if($ndfoodid)
   {
      if(!$usda_api_key)
      {
         $ndtext = " * USDA lookup is not configured. Add an API key or enter nutrition manually.";
      }
      else
      {
         $food = usda_get_food($ndfoodid);
         if($food)
         {
            $nutrients = usda_extract_nutrients($food);
            if(!$ingredientid && isset($food['description']))
            {
               $name = $food['description'];
               $nametext = " * Copied from USDA FoodData Central.";
            }
            $calories = $nutrients['calories'];
            $carbs = $nutrients['carbs'];
            $fat = $nutrients['fat'];
            $protein = $nutrients['protein'];
            $fiber = $nutrients['fiber'];
            if(isset($food['servingSize']) && $food['servingSize'] > 0)
               $serving_size = $food['servingSize'];
            elseif(!$serving_size)
               $serving_size = 100;
            $ndtext = " * Copied from USDA FoodData Central.";
            $voltext = " * Volume data is not available from USDA; enter manually if needed.";
         }
         else
         {
            $ndtext = " * USDA nutrition data not found.";
         }
      }
   }

   if($ndlookup)
   {
      $name = request_value('name', '', $_POST);
      if(!$usda_api_key)
      {
         $ndtext = " * USDA lookup is not configured. Add an API key or enter nutrition manually.";
      }
      else
      {
         $search_results = usda_search_foods($name);
         if($search_results && isset($search_results['foods']) && count($search_results['foods']) > 0)
         {
?>
<TABLE cellpadding=1 cellspacing=0 border=1>
<?php
            foreach($search_results['foods'] as $food)
            {
               $description = isset($food['description']) ? $food['description'] : 'Unknown';
               $fdc_id = isset($food['fdcId']) ? $food['fdcId'] : 0;
               echo '<tr><td><form method="post"><INPUT TYPE="HIDDEN" NAME="ingredientid" VALUE="' . h($ingredientid) . '"><INPUT TYPE="HIDDEN" NAME="ndfoodid" VALUE="' . h($fdc_id) . '"><input type="submit" name="ndcopy" value="Copy"><td>' . h($description) . "</form>\n";
            }
?>
</table>
<?php
         }
         else
         {
            $ndtext = " * No USDA matches found. Enter nutrition manually.";
         }
       }
    }

?>
<?php render_page_start('Ingredient Editor', 'Ingredient Editor', 'Edit Ingredient'); ?>
<?php
   if($returnrecipe)
   {
      echo "<section class=\"card\"><A HREF='editrecipe.php?recipeid=$returnrecipe&scrollto=ingredient'>Return to recipe's editor</A><BR>";
      echo "<A HREF='editrecipeingredients.php?recipeid=$returnrecipe&scrollto=ingredient'>Return to recipe's ingredient editor</A></section>";
   }
   else
      echo "<section class=\"card\"><A HREF=\"ingredients.php\">Back to Search Page</A></section>";
?>
<section class="card">
<FORM NAME="ndform" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="ingredientid" VALUE="<?php echo $ingredientid?>">
USDA FoodData Central lookup requires a local API key. Enter nutrition manually if no key is configured.
</FORM>
<FORM NAME="form1" METHOD="POST">
   <TABLE class="form-table">
   <TR><TD>Name:            <TD><INPUT TYPE="TEXT" NAME="name" VALUE="<?php echo $name?>" ID="name" SIZE=70> <?php echo $nametext?>
   <TR><TD>                 <TD><INPUT TYPE="SUBMIT" NAME="ndlookup" VALUE="Look up in USDA FoodData Central">
   <TR><TD>Size:            <TD><INPUT TYPE="TEXT" NAME="size" VALUE="<?php echo $size?>"><SELECT NAME="unit"><?php print_unit_options(2); ?></SELECT>
   <TR><TD>Cost:            <TD><INPUT TYPE="TEXT" NAME="cost" VALUE="<?php echo $cost?>">
   <TR><TD>Units/container: <TD><INPUT TYPE="TEXT" NAME="units" VALUE="<?php echo $units?>">
   <TR><TD>Nutrition Facts: <TD><HR>
   <TR><TD>Serving Size:    <TD><INPUT TYPE="TEXT" NAME="serving_size" VALUE="<?php echo $serving_size?>"> grams (or milliliters) <?php echo $ndtext?>
   <TR><TD>Volume:          <TD><INPUT TYPE="TEXT" NAME="ounces_cup" VALUE="<?php echo $ounces_cup?>"><SELECT NAME="weight_select"><?php print_weight_options();?></SELECT> per <SELECT NAME="volume_select"><?php print_volume_options();?></SELECT> <?php echo $voltext?>
   <TR><TD>Calories:        <TD><INPUT TYPE="TEXT" NAME="calories" VALUE="<?php echo $calories?>"> <?php echo $ndtext?>
   <TR><TD>Carbs:           <TD><INPUT TYPE="TEXT" NAME="carbs" VALUE="<?php echo $carbs?>"> <?php echo $ndtext?>
   <TR><TD>Fat:             <TD><INPUT TYPE="TEXT" NAME="fat" VALUE="<?php echo $fat?>"> <?php echo $ndtext?>
   <TR><TD>Protein:         <TD><INPUT TYPE="TEXT" NAME="protein" VALUE="<?php echo $protein?>"> <?php echo $ndtext?>
   <TR><TD>Fiber:           <TD><INPUT TYPE="TEXT" NAME="fiber" VALUE="<?php echo $fiber?>"> <?php echo $ndtext?>
   <TR><TD>Recipe:          <TD><SELECT NAME="recipe"><OPTION VALUE="0">None<?php print_recipe_options($recipe); ?></SELECT>
</TABLE></p>
<?php if($returnrecipe) echo "<INPUT TYPE=\"HIDDEN\" NAME=\"returnrecipe\" VALUE=\"$returnrecipe\">"; ?>
<INPUT TYPE="HIDDEN" NAME="ingredientid" VALUE="<?php echo $ingredientid?>">
<div class="inline-actions">
<INPUT TYPE="SUBMIT" NAME="save" VALUE="Save">
<label><input type="checkbox" name="deleteconfirm" value="1"> Confirm delete</label>
<INPUT TYPE="SUBMIT" NAME="delete" VALUE="Delete">
</div>
</FORM>
</section>
<script>document.form1.name.focus();</script>
<?php render_page_end(); ?>
