<?php
   require "header.inc";
   if(file_exists("../fckeditor/fckeditor.php"))
      include_once("../fckeditor/fckeditor.php");

   
   if(isset($_POST['recipeid']))
      $recipeid = $_POST['recipeid'];
   elseif(isset($_GET['recipeid']))
      $recipeid = $_GET['recipeid'];
   else
      $recipeid = 0;

   if(isset($_POST['new']))
   {
      $recipeid = 0;
      $new = true;
   }
   else
      $new = false;

   if(isset($_POST['save']))
      $save = true;
   else
      $save = false;

   if(isset($_POST['delete']))
      $delete = true;
   else
      $delete = false;

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

   if(isset($_POST['format_select']))
      $format_select = $_POST['format_select'];
   else
      $format_select = "";
   if(isset($_POST['search']))
      $search = $_POST['search'];
   else
      $search = "";
   if(isset($_POST['recipe_search']))
      $recipe_search = $_POST['recipe_search'];
   else
      $recipe_search = "";
   if(isset($_POST['category_select']))
      $category_select = $_POST['category_select'];
   else
      $category_select = 0;

   if(isset($_POST['idisplay']))
      $idisplay = $_POST['idisplay'];
   else
      $idisplay = 0;


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
      $name = $_POST['name'];
      $time = $_POST['time'];
      $category = $_POST['category'];
      $servings = $_POST['servings'];
      $calories = $_POST['calories'];
      $ed = $_POST['ed'];
      $carbs = $_POST['carbs'];
      $fat = $_POST['fat'];
      $protein = $_POST['protein'];
      $fiber = $_POST['fiber'];
      $instructions = $_POST['instructions'];

      $dbname = addslashes($_POST['name']);
      $dbtime = addslashes($_POST['time']);
      $dbcategory = addslashes($_POST['category']);
      $dbservings = addslashes($_POST['servings']);
      $dbcalories = addslashes($_POST['calories']);
      $dbed = addslashes($_POST['ed']);
      $dbcarbs = addslashes($_POST['carbs']);
      $dbfat = addslashes($_POST['fat']);
      $dbprotein = addslashes($_POST['protein']);
      $dbfiber = addslashes($_POST['fiber']);
      $dbinstructions = addslashes($_POST['instructions']);


      if(!$recipeid)
      {
	 $query = "INSERT INTO recipes (name, `time`, category, servings, calories, energy_density, carbs, fat, protein, fiber, instructions) VALUES ('$dbname', '$dbtime', '$dbcategory', '$dbservings', '$dbcalories', '$dbed', '$dbcarbs', '$dbfat', '$dbprotein', '$dbfiber', '$dbinstructions')";
      }
      else
      {
	 $query = "UPDATE recipes SET name = '$dbname', `time` = '$dbtime', category = '$dbcategory', servings = '$dbservings', calories = '$dbcalories', energy_density = '$dbed', carbs = '$dbcarbs', fat = '$dbfat', protein = '$dbprotein', fiber = '$dbfiber', instructions = '$dbinstructions' WHERE id = '$recipeid'";
      }

      dbquery($query, $dbh) or die("Error updating records: " . db_error($dbh));
      echo "Recipe saved successfully.<BR>";
      echo "<FORM METHOD='POST'><INPUT TYPE=\"HIDDEN\" NAME=\"format_select\" VALUE=\"$format_select\"><INPUT TYPE=HIDDEN NAME=\"category_select\" VALUE=\"$category_select\"><INPUT TYPE=\"HIDDEN\" NAME=\"recipe_search\" VALUE=\"$recipe_search\"><INPUT TYPE=\"HIDDEN\" NAME=\"search\" VALUE=\"$search\"><INPUT TYPE='Submit' NAME='new' VALUE='Add another'></FORM>";
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

      $result = dbquery("SELECT name, `time`, category, servings, calories, energy_density, carbs, fat, protein, fiber, instructions FROM recipes WHERE id = '$recipeid'", $dbh) or die("Error in query: " . db_error($dbh));
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
      if($_POST['deleteconfirm'] != 2)
      {
         echo "You must select 'Yes' from the confirmation pull down option.";
         exit;
      }

      if(!$recipeid)
      {
         echo "Recipe not found.";
         exit;
      }

      dbquery("DELETE FROM recipes WHERE id = '$recipeid'", $dbh) or die("Error deleting record: " . db_error());
      echo "Recipe deleted.<BR>";
      echo "<A HREF=\"index.php\">&gt; Back to recipes search page &lt;</A><BR>";
      exit;
   }
?>
<HTML>
<HEAD>
   <TITLE>Recipe Editor</TITLE>
</HEAD>
<BODY>
<H2><CENTER>Editing recipe: <?php echo "$name" ?></CENTER></H2>
<A HREF="index.php">&gt; Back to Search Page &lt;</A><BR>
<P>
<FORM NAME="form1" METHOD="POST">
   <TABLE>
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
   if(class_exists('FCKeditor'))
   {
      $oFCKeditor = new FCKeditor('instructions');
      $oFCKeditor->BasePath = '/fckeditor/';
      $oFCKeditor->Config['EnterMode'] = 'br';
      $oFCKeditor->Value = $instructions;
      $oFCKeditor->Width = 800;
      $oFCKeditor->Height = 400;
      $oFCKeditor->Create();
   }
   else
   {
      echo '<TEXTAREA NAME="instructions" COLS="100" ROWS="20">' . htmlspecialchars($instructions) . '</TEXTAREA>';
   }
?><BR>
<INPUT TYPE="HIDDEN" NAME="recipeid" VALUE="<?php echo $recipeid?>">
<INPUT TYPE="SUBMIT" NAME="save" VALUE="Save"><BR>
<BR>
<SELECT NAME="deleteconfirm"><OPTION VALUE="0">Delete?<OPTION VALUE="1">No<OPTION VALUE="2">Yes</SELECT>
<INPUT TYPE="SUBMIT" NAME="delete" VALUE="Delete">
</FORM><br>
<A HREF="editrecipeingredients.php?recipeid=<?php echo $recipeid;?>">&gt; Modify ingredients for this recipe &lt;</A><BR>
<BR>
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
</BODY>
</HTML>
