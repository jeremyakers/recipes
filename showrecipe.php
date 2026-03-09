<? include "header.inc"; 

   if(isset($_GET['recipeid']))
     $recipe = $_GET['recipeid'];
   else
   {
      echo "You must choose a valid recipe from <A HREF=\"index.php\">the index page</A><BR>";
      exit;
   }

   $query = "SELECT recipes.id, category category_id, categories.name category_name, recipes.name recipe_name, \"time\", servings, calories, energy_density, carbs, fat, protein, fiber, instructions FROM recipes INNER JOIN categories ON recipes.category = categories.id WHERE recipes.id = $recipe";
   $result = dbquery($query, $dbh);
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

<HTML>
<HEAD><TITLE>Recipe Details for: <? echo $name; ?></TITLE></HEAD>
<BODY>
<H3><? echo $name; ?></H3><BR>
<TABLE>
<TR><TD>Time (minutes):<TD><? echo $time; ?>
<TR><TD>Servings:<TD><? echo $servings; ?>
<TR><TD>Calories:<TD><? echo $calories; ?>
<TR><TD>Energy density:<TD><? echo $ed; ?>
<TR><TD>Carbs:<TD><? echo $carbs; ?>
<TR><TD>Fat:<TD><? echo $fat; ?>
<TR><TD>Protein:<TD><? echo $protein; ?>
<TR><TD>Fiber:<TD><? echo $fiber; ?>
</TABLE>
<BR>
<FORM ACTION="editrecipe.php" METHOD="POST"><INPUT TYPE="HIDDEN" NAME="recipeid" VALUE="<?echo $recipe?>"><INPUT TYPE="SUBMIT" NAME="edit" VALUE="Edit this recipe"></FORM>
<? print_ingredients($recipe, false, true); ?>
<H3>Instructions</H3>
<? echo $instructions;?>
</BODY>
</HTML>
