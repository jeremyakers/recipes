<?php
   require "header.inc";

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
   
   if(isset($_POST['ingredientid']))
      $ingredientid = $_POST['ingredientid'];
   elseif (isset($_GET['ingredientid']))
      $ingredientid = $_GET['ingredientid'];
   else
      $ingredientid = 0;

   if(isset($_POST['new']))
   {
      $ingredientid = 0;
      $new = true;
   }
   else
      $new = false;

   if(isset($_POST['save']))
      $save = true;
   else
      $save = false;

   if(isset($_POST['edit']))
      $edit = true;
   else
      $edit = false;

   if(isset($_POST['delete']))
      $delete = true;
   else
      $delete = false;

   if(isset($_GET['returnrecipe']))
      $returnrecipe = $_GET['returnrecipe'];
   elseif(isset($_POST['returnrecipe']))
      $returnrecipe = $_POST['returnrecipe'];
   else
      $returnrecipe = 0;

   if(isset($_POST['format_select']))
      $format_select = $_POST['format_select'];
   elseif(isset($_GET['format_select']))
      $format_select = $_GET['format_select'];
   if(isset($_POST['search']))
      $search = $_POST['search'];
   elseif(isset($_GET['search']))
      $search = $_GET['search'];
   if(isset($_POST['ingredient_search']))
      $ingredient_search = $_POST['ingredient_search'];
   elseif(isset($_GET['ingredient_search']))
      $ingredient_search = $_GET['ingredient_search'];

   if(isset($_POST['ndurl']))
      $ndurl = $_POST['ndurl'];
   else
      $ndurl = "";

   if(isset($_POST['ndlookup']))
      $ndlookup = $_POST['ndlookup'];
   else
      $ndlookup = "";


   if(!$save && !$edit && !$new && !$ingredientid)
   {
      $ingredientid = 0;
      $new = true;
   }

   if(!$delete && !$save && !$edit && !$new && $ingredientid)
      $edit = true;


   if($save)
   {
      $name = $_POST['name'];
      $size = $_POST['size'];
      $cost = $_POST['cost'];
      $units = $_POST['units'];
      $recipe = $_POST['recipe'];
      $serving_size = $_POST['serving_size'];
      $calories = $_POST['calories'];
      $carbs = $_POST['carbs'];
      $fat = $_POST['fat'];
      $protein = $_POST['protein'];
      $fiber = $_POST['fiber'];
      $ounces_cup = $_POST['ounces_cup'];
      $weight_select = $_POST['weight_select'];
      $volume_select = $_POST['volume_select'];

      $unit = $_POST['unit'];
      $mult = find_unit_mult($unit);
      if($mult == 0)
         $mult = find_count_mult($ingredientid);

      $size = $size * $mult;

      $mult = find_unit_mult($weight_select);
      $in_cup = find_units_cup($volume_select);

      $ounces_cup = $ounces_cup * $mult * $in_cup;

      $dbname = addslashes($_POST['name']);

      if(!$ingredientid)
      {
	 $query = "INSERT INTO ingredients (name, `size`, cost, units, recipe, serving_size, calories, carbs, fat, protein, fiber, ounces_cup) VALUES ('$dbname', '$size', '$cost', '$units', '$recipe', '$serving_size', '$calories', '$carbs', '$fat', '$protein', '$fiber', '$ounces_cup')";
      }
      else
      {
	 $query = "UPDATE ingredients SET name = '$dbname', `size` = '$size', cost = '$cost', units = '$units', recipe = '$recipe', serving_size = '$serving_size', calories = '$calories', carbs = '$carbs', fat = '$fat', protein = '$protein', fiber = '$fiber', ounces_cup = '$ounces_cup' WHERE id = '$ingredientid'";
      }

      dbquery($query, $dbh) or die("Error updating records: " . db_error($dbh) . "<BR>Query: $query");
      echo "Ingredient saved successfully.<BR>";
      echo "<FORM METHOD='POST'>";
      if($returnrecipe) 
	 echo "<INPUT TYPE=\"HIDDEN\" NAME=\"returnrecipe\" VALUE=\"$returnrecipe\">";
      echo "<INPUT TYPE=\"HIDDEN\" NAME=\"format_select\" VALUE=\"$format_select\"><INPUT TYPE=\"HIDDEN\" NAME=\"recipe_search\" VALUE=\"$recipe_search\"><INPUT TYPE=\"HIDDEN\" NAME=\"search\" VALUE=\"$search\"><INPUT TYPE='Submit' NAME='new' VALUE='Add another'></FORM>";
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

      $result = dbquery("SELECT name, `size`, cost, recipe, serving_size, calories, carbs, fat, protein, fiber, units, ounces_cup FROM ingredients WHERE id = '$ingredientid'", $dbh) or die("Error in query: " . db_error($dbh));
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
      if($_POST['deleteconfirm'] != 2)
      {
	 echo "You must select 'Yes' from the confirmation pull down option.";
	 exit;
      }
      if(!$ingredientid)
      {
         echo "Ingredient not found.";
         exit;
      }

      dbquery("DELETE FROM ingredients WHERE id = '$ingredientid'", $dbh) or die("Error deleting record: " . db_error());
      echo "Ingredient deleted.<BR>";
      if($returnrecipe)
         echo "<A HREF='editrecipe.php?recipeid=$returnrecipe'>&gt; Return to recipe editor &lt;</A><BR>";
      echo "<A HREF=\"ingredients.php\">&gt; Back to Ingredients Search Page &lt;</A><BR>";
      exit;
   }
   
   if($ndurl)
   {
      echo "Copying from nutritiondata.com...<br>\n";
      $ndfound = true;
      $ndvolfound = true;
      $namefound = true;

      $ndata = file_get_contents($ndurl);
      if(preg_match("/1 cup.*\((.*)g\)/i", $ndata, $matches))
	 $ounces_cup = $matches[1] / 28.3495231;
      elseif(preg_match("/1 tbsp.*\((.*)g\)/i", $ndata, $matches))
	 $ounces_cup = $matches[1] / 28.3495231 * 16;
      elseif(preg_match("/1 tsp.*\((.*)g\)/i", $ndata, $matches))
	 $ounces_cup = $matches[1] / 28.3495231 * 48;
      else
	 $ndvolfound = false;

      if(!isset($_POST['recipeid']) || !$_POST['recipeid'])
      {
	 if(preg_match("/<h1>(.*)<\/h1>/", $ndata, $matches))
	 {
	    $nametext = " * Copied from nutritiondata.com.";
	    $name = $matches[1];
	 }
	 else
	 {
	    $namefound = false;
	    $nametext = " * !Name NOT FOUND!";
	 }
      }
      else
	 $nametext = "";


      if(preg_match("/ NUTRIENT_0:\"([0-9\.]*)\",/", $ndata, $matches))
         $calories = $matches[1];
      else
         $ndfound = false;

      if(preg_match("/ NUTRIENT_4:\"([0-9\.]*)\",/", $ndata, $matches))
         $carbs = $matches[1];
      else
         $carbs = 0;

      if(preg_match("/ NUTRIENT_5:\"([0-9\.]*)\",/", $ndata, $matches))
         $fiber = $matches[1];
      else
         $fiber = 0;
      if(preg_match("/ NUTRIENT_14:\"([0-9\.]*)\",/", $ndata, $matches))
         $fat = $matches[1];
      else
         $fat = 0;
      if(preg_match("/ NUTRIENT_77:\"([0-9\.]*)\",/", $ndata, $matches))
         $protein = $matches[1];
      else
         $protein = 0;

      if(!$ndfound)
         $ndtext = " * !Nutrition data NOT FOUND!";
      else
      {
         $serving_size = 100;
         $ndtext = " * Copied from nutritiondata.com.";
      }

      if(!$ndvolfound)
         $voltext = " * !Volume data NOT FOUND!";
      else
         $voltext = " * Copied from nutritiondata.com.";
   }

   if($ndlookup)
   {
      $i = 0;
      $name = $_POST['name'];
      $ndname = urlencode($name);
      $ndata = file_get_contents('http://www.nutritiondata.com/foods-' . $ndname . '000000000000000000000.html');
      if(preg_match_all('/<a href="(.*)" class="list">(.*)<\/a>/', $ndata, $matches))
      {
?>
<TABLE cellpadding=1 cellspacing=0 border=1>
<?php
          foreach($matches[1] as $url)
         {
            $url = 'http://www.nutritiondata.com' . $url;
            echo '<tr><td><form method="post"><INPUT TYPE="HIDDEN" NAME="ingredientid" VALUE="' . $ingredientid . '"><input type="hidden" name="ndurl" value="' . $url . '"><input type="submit" name="ndcopy" value="Copy"><td><a href="' . $url . '">' . $matches[2][$i] . "</a></form>\n";
            $i++;
         }
?>
</table>
<?php
      }
      else
         echo 'No matches found at: <a href="http://www.nutritiondata.com/foods-' . $ndname . '000000000000000000000.html">http://www.nutritiondata.com/foods-' . $ndname . '000000000000000000000.html<br>';


   }

?>
<HTML>
<HEAD>
<TITLE>Ingredient Editor</TITLE>
</script>
</HEAD>
<BODY onload="document.form1.name.focus()">
<H2><CENTER>Editing ingredient: <?php echo "$name" ?></CENTER></H2>
<?php
   if($returnrecipe)
   {
      echo "<A HREF='editrecipe.php?recipeid=$returnrecipe&scrollto=ingredient'>&gt; Return to recipe's editor &lt;</A><BR>";
      echo "<A HREF='editrecipeingredients.php?recipeid=$returnrecipe&scrollto=ingredient'>&gt; Return to recipe's ingredient editor &lt;</A><BR>";
   }
   else
      echo "<A HREF=\"ingredients.php\">&gt; Back to Search Page &lt;</A><BR>";
?>
<P>
<FORM NAME="ndform" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="ingredientid" VALUE="<?php echo $ingredientid?>">
Copy nutrition data from: <INPUT TYPE="TEXT" NAME="ndurl" VALUE="<?php echo $ndurl?>" ID="name" SIZE=70>
<INPUT TYPE="SUBMIT" NAME="ndcopy" VALUE="Copy">
</FORM>
<FORM NAME="form1" METHOD="POST">
   <TABLE>
   <TR><TD>Name:            <TD><INPUT TYPE="TEXT" NAME="name" VALUE="<?php echo $name?>" ID="name" SIZE=70> <?php echo $nametext?>
   <TR><TD>                 <TD><INPUT TYPE="SUBMIT" NAME="ndlookup" VALUE="Look up on nutritiondata.com"> <!--onClick="javascript:window.open('http://www.nutritiondata.com/foods-' + escape(document.form1.name.value) + '000000000000000000000.html','blank','')">-->
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
<INPUT TYPE="SUBMIT" NAME="save" VALUE="Save"><BR>
<BR>
<SELECT NAME="deleteconfirm"><OPTION VALUE="0">Delete?<OPTION VALUE="1">No<OPTION VALUE="2">Yes</SELECT>
<INPUT TYPE="SUBMIT" NAME="delete" VALUE="Delete">
</FORM>
</BODY>
</HTML>
