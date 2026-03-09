<?php

session_name('recipes');
session_start();

function db_connect()
{
   $host = getenv('RECIPES_DB_HOST');
   if($host === false || $host === '')
      $host = '127.0.0.1';

   $port = getenv('RECIPES_DB_PORT');
   if($port === false || $port === '')
      $port = 3306;

   $database = getenv('RECIPES_DB_NAME');
   if($database === false || $database === '')
      $database = 'recipes';

   $username = getenv('RECIPES_DB_USER');
   if($username === false || $username === '')
      $username = 'root';

   $password = getenv('RECIPES_DB_PASS');
   if($password === false)
      $password = '';

   $connection = mysqli_connect($host, $username, $password, $database, (int)$port);
   if(!$connection)
      die('I cannot connect to the database: ' . mysqli_connect_error());

   mysqli_set_charset($connection, 'utf8mb4');

   return $connection;
}

$dbh = db_connect();

if(file_exists('../mobile_device_detect.php'))
   include('../mobile_device_detect.php');

if(!isset($_SESSION['mobile']))
{
   if(function_exists('mobile_device_detect'))
      $_SESSION['mobile'] = mobile_device_detect(false,false,false,true,false,false);
   else
      $_SESSION['mobile'] = false;
}

$mobile = $_SESSION['mobile'];

$formats = array("Wide", "Mobile");


   function db_normalize_query($query)
   {
      return str_replace(
         array('"time"', '"size"', '"type"'),
         array('`time`', '`size`', '`type`'),
         $query
      );
   }

   function dbquery($query, $mydbh = null)
   {
       global $dbh;
       if(isset($mydbh))
          $usedbh = $mydbh;
       else
          $usedbh = $dbh;
      return mysqli_query($usedbh, db_normalize_query($query));
   }

   function db_fetch_array($result)
   {
      return mysqli_fetch_assoc($result);
   }

   function db_error($mydbh = null)
   {
      global $dbh;
      if(isset($mydbh))
         $usedbh = $mydbh;
      else
         $usedbh = $dbh;
      return mysqli_error($usedbh);
   }

   function db_insert_id($mydbh = null)
   {
      global $dbh;
      if(isset($mydbh))
         $usedbh = $mydbh;
      else
         $usedbh = $dbh;
      return mysqli_insert_id($usedbh);
   }


   function print_format_options($format)
   {
      global $formats;
      if(!$format)
	 $format = "Mobile";

      while($value = current($formats))
      {
	 if($format == $value)
	    $selected = " SELECTED";
	 else
	    $selected = "";

	 echo "<OPTION$selected VALUE=\"$value\">$value";
	 next($formats);
      }
   }

   function print_weight_options()
   {
      $type = 'w';
      $unit = 2;
      print_unit_options($unit, $type);
   }

   function print_volume_options()
   {
      $unit = 6;
      $type = 'v';
      print_unit_options($unit, $type);
   }


   function print_unit_options($unit = 6, $type = '')
   {
       global $dbh;
       if(!$unit)
	 $unit = 6;
      if(!empty($type))
         $where = "WHERE \"type\" = '$type'";
      else
	 $where = '';
      $result = dbquery("SELECT id, sname FROM units $where ORDER BY id", $dbh);
      while(($row = db_fetch_array($result)))
      {
	 $id = $row['id'];
	 $sname = $row['sname'];
         if($unit == $id)
            $selected = " SELECTED";
         else
            $selected = "";

         echo "<OPTION$selected VALUE=\"$id\">$sname";
      }
   }

   function find_units_cup($unit)
   {
      global $dbh;
      $query = "SELECT in_cup FROM units WHERE id = '$unit'";
      $result = dbquery($query, $dbh);
      $row = db_fetch_array($result);
      return $row['in_cup'];
   }

   function find_unit_mult($unit)
   {
      global $dbh;
      $query = "SELECT multiplier FROM units WHERE id = '$unit'";
      $result = dbquery($query, $dbh);
      $row = db_fetch_array($result);
      return $row['multiplier'];
   }

   function find_count_mult($ingredient)
   {
       global $dbh;
      $query = "SELECT (`size` / units) count_mult FROM ingredients WHERE id = '$ingredient'";
      $result = dbquery($query, $dbh);
      $row = db_fetch_array($result);
      return $row['count_mult'];
   }

   function find_ingredient_data($ingredient)
   {
       global $dbh;
      $query = "SELECT MAX(name) name, MAX(`size`) `size`, MAX(cost) cost, ingredients.recipe ingredients_recipe, SUM(amount) sum_amount FROM ingredients INNER JOIN recipe_ingredients ON recipe_ingredients.recipe = ingredients.recipe WHERE id = '$ingredient' GROUP BY ingredients.recipe";
      $result = dbquery($query, $dbh);
      $row = db_fetch_array($result);
      return array($row['name'], $row['size'], $row['cost'], $row['ingredients_recipe'], $row['sum_amount']);
   }

   function print_ingredient_rows($recipeid, $edit, $recur, $ounces, $full, $display)
   {
       global $dbh;
      $total_oz = 0;
      $total_cost = 0;
      $total_calories = 0;
      $total_carbs = 0;
      $total_fat = 0;
      $total_protein = 0;
      $total_fiber = 0;
      $count = 0;

      if($ounces > 0)
      {
	 //$query = "SELECT SUM(amount) FROM recipe_ingredients WHERE recipe = $recipeid";
	 $query = "SELECT SUM((recipe_ingredients.amount / units.in_cup) * ingredients.ounces_cup) amount FROM recipe_ingredients INNER JOIN units ON recipe_ingredients.unit = units.id INNER JOIN ingredients ON recipe_ingredients.ingredient = ingredients.id WHERE recipe_ingredients.recipe = $recipeid GROUP BY recipe_ingredients.recipe";
         $result = dbquery($query, $dbh) or die("Error with query: " . db_error());
         if(!($row = db_fetch_array($result)))
            return array(0, 0);
         $portion_mult = $ounces / $row['amount'];
      }
      else
	 $portion_mult = 1;

      if($edit)
         $editblock = "<TD><B>--</B>";
      else
         $editblock = "";

      if($full)
         $commentblock = "<TD><B>--</B>";
      else
         $commentblock = "";



      # IF multiplier AND units.in_cup --> VOLUME
      # IF multiplier AND NOT units.in_cup --> WEIGHT
      # IF NOT multiplier AND NOT units.in_cup --> COUNT

      /*$query = "SELECT ingredient, ingredients.name, "
	 . "IF(multiplier, IF(units.in_cup, ((recipe_ingredients.amount / units.in_cup) * ingredients.ounces_cup), (recipe_ingredients.amount * multiplier)), (recipe_ingredients.amount * (ingredients.size / ingredients.units))) AS ounces, "
	 . "IF(multiplier, IF(units.in_cup, ((cost / size) * (recipe_ingredients.amount / units.in_cup) * ingredients.ounces_cup), (cost / size) * (recipe_ingredients.amount * multiplier)), (recipe_ingredients.amount * (cost / ingredients.units))) AS cost, "
	 . "sname, multiplier, ingredients.recipe, recipes.name, recipe_ingredients.comment, amount, "
	 . "ingredients.serving_size, ingredients.calories, ingredients.carbs, ingredients.fat, ingredients.protein, ingredients.fiber "
	 . "FROM ingredients, recipe_ingredients, units, recipes "
	 . "WHERE recipe_ingredients.recipe = $recipeid AND recipes.id = recipe_ingredients.recipe AND ingredients.id = recipe_ingredients.ingredient AND units.id = recipe_ingredients.unit "
	 . "ORDER BY ingredients.recipe, pos"; */
      $query = "SELECT ingredient, name, ounces, cost, sname, multiplier, ingredient_recipe, recipe_name, comment, amount, serving_size, calories, carbs, fat, protein, fiber FROM v_recipe_ingredients WHERE recipe = $recipeid";
      $result = dbquery($query, $dbh) or die("Error with query: " . db_error());
      while(($row = db_fetch_array($result)))
      {
	 $ingredient = $row['ingredient'];
	 $iname = $row['name'];
	 $iamount = $row['ounces'];
	 $icost = $row['cost'];
	 $sname = $row['sname'];
	 if($sname == "count")
	    $sname = "";
	 $mult = $row['multiplier'];
	 $recipe = $row['ingredient_recipe'];
	 $rname = $row['recipe_name'];
	 $comment = $row['comment'];
	 $amount = $row['amount'];
	 $servsize = $row['serving_size'];
	 $calories = $row['calories'];
	 $carbs = $row['carbs'];
	 $fat = $row['fat'];
	 $protein = $row['protein'];
	 $fiber = $row['fiber'];

         if($mult == 0)
	    $mult = find_count_mult($ingredient);
	 else
	    $mult = 1;
         #$amount = round($iamount / $mult, 2);

         if($edit)
	    $edittext = "<TD><FORM METHOD=\"POST\" NAME=\"remform$ingredient\" onSubmit=\"javascript:saveScrollTo('remform$ingredient', 'ingredient')\"><INPUT TYPE=\"HIDDEN\" NAME=\"scrollto\"><INPUT TYPE=\"HIDDEN\" NAME=\"recipeid\" VALUE=\"$recipeid\"><INPUT TYPE=\"HIDDEN\" NAME=\"ingredient\" VALUE=\"$ingredient\"><INPUT TYPE=\"SUBMIT\" NAME=\"remingredient\" VALUE=\"Remove\"></FORM>";
         else
            $edittext = "";
         if($full)
            $commenttext = "<TD>$comment";
         else
            $commenttext = "";

	 if($recipe)
	 {
	    echo "<TR><TD><B>-- Subtotal: " . round($total_oz, 1) . " ounces</B>$editblock<TD><B>$" . round($total_cost, 2) . "</B>$commentblock\n";
	    echo "<TR><TD>$amount $sname <B><A HREF=\"showrecipe.php?recipeid=$recipe\">$iname:</A></B>$edittext<TD>--$commenttext\n";
	    list($ounces, $cost, $icount, $calories, $carbs, $fat, $protein, $fiber) = print_ingredient_rows($recipe, $edit, $ingredient, $iamount, $full, $display);
	    echo "<TR><TD><B>-- Subtotal: " . round($ounces, 1) . " ounces</B>$editblock<TD><B>$" . round($cost, 2) . "</B>$commentblock\n";
	    $total_oz += $ounces;
	    $total_cost += $cost;
            $total_calories += $calories;
            $total_carbs += $carbs;
            $total_fat += $fat;
            $total_protein += $protein;
            $total_fiber += $fiber;
	    $count += $icount;
	 }
	 else
	 {
	    $amount = round($amount * $portion_mult, 2);
       $cost = round($icost * $portion_mult, 2);
	    $grams = ($iamount * $portion_mult) * 28.3495231;
	    $servings = ($grams / $servsize);
       $total_oz += ($iamount * $portion_mult);
	    $total_cost += ($icost * $portion_mult);
	    $total_calories += $calories * $servings;
	    $total_carbs += $carbs * $servings;
	    $total_fat += $fat * $servings;
	    $total_protein += $protein * $servings;
	    $total_fiber += $fiber * $servings;

       switch($display)
       {
       case 0:
          $statistic = '$' . $cost;
          break;
       case 1:
          $statistic = round($iamount * $portion_mult, 2);
          break;
       case 2:
          $statistic = $servings;
          break;
       case 3:
          $statistic = $calories * $servings;
          break;
       case 4:
          $statistic = ($calories * $servings) / $grams;
          break;

       }
	    echo "<TR><TD>$amount $sname <A HREF=\"editingredient.php?returnrecipe=$recipeid&ingredientid=$ingredient\">$iname</A>$edittext<TD>$statistic$commenttext\n";
	    $count++;
	 }
      }
      if(!$recur)
	 echo "<TR><TD><B>TOTAL: " . round($total_oz, 1) . " ounces</B>$editblock<TD><B>$" . round($total_cost, 2) . "</B>$commentblock\n";

      return array($total_oz, $total_cost, $count, $total_calories, $total_carbs, $total_fat, $total_protein, $total_fiber);
   }

   function print_display_options($display)
   {
      $display_options = array("Cost", "Ounces", "Servings", "Calories", "E.D.");
      $val = 0;

      while($value = current($display_options))
      {
         if($display == $val)
            $selected = " SELECTED";
         else
            $selected = "";

         echo "<OPTION VALUE='$val'$selected>$value";
         $val++;
         next($display_options);
      }

   }

   function print_ingredients($recipeid, $edit, $full, $display)
   {
      global $dbh;
      echo "<TABLE cellpadding=1 cellspacing=0 border=1>";
      echo "<COL width='200'>";
      if($edit)
      {
	 echo "<COL>";
	 $editheader = "<TH>Remove?";
      }
      else
	 $editheader = "";
      echo "<COL>\n";
      if($full)
	 echo "<COL width='100'>";
      echo "<TR><TH>Ingredient$editheader<TH><form method=\"post\"><select name=\"idisplay\">";
      print_display_options($display);
      echo "</select><input type=\"submit\" name=\"update_display\" value=\"Update\"></form>";
      if($full)
	 echo "<TH>Comment";
      list($total_oz, $total_cost, $count, $total_calories, $total_carbs, $total_fat, $total_protein, $total_fiber) = print_ingredient_rows($recipeid, $edit, false, 0, $full, $display);
      echo "</TABLE>";
      if($total_oz > 0)
         echo "<br>Cost per ounce: $" . round($total_cost / $total_oz, 3);
      return array($total_oz, $total_cost, $count, $total_calories, $total_carbs, $total_fat, $total_protein, $total_fiber);
   }


   function print_category_options($category)
   {
      global $dbh;
      $result = dbquery("SELECT id, name FROM categories ORDER BY id", $dbh);
      while($row = db_fetch_array($result))
      {
         $id = $row['id'];
         $name = $row['name'];
         if($category == $id)
            $selected = " SELECTED";
         else
            $selected = "";
         echo "<OPTION$selected VALUE=\"$id\">$name";
      }
   }

   function print_ingredient_options($ingredients)
   {
       global $dbh;
      if(!is_array($ingredients))
         $ingredients = array();
      $count=0;
      $result = dbquery("SELECT id, name FROM ingredients ORDER BY name", $dbh);
      while($row = db_fetch_array($result))
      {
	 $id = $row['id'];
	 $name = $row['name'];
         if(in_array($id, $ingredients))
            $selected = " SELECTED";
         else
            $selected = "";
         echo "<OPTION$selected VALUE=\"$id\">$name";
         $count++;
      }
   }

   function print_recipe_options($recipe)
   {
      global $dbh;
      $result = dbquery("SELECT id, name FROM recipes ORDER BY name", $dbh);
      while($row = db_fetch_array($result))
      {
	 $id = $row['id'];
	 $name = $row['name'];
         if($recipe == $id)
            $selected = " SELECTED";
         else
            $selected = "";
         echo "<OPTION$selected VALUE=\"$id\">$name";
      }
   }
?>
