<?php
   require "header.inc";
   include "layout.inc";


   $recipeid = request_int('recipeid', 0);

   if(!$recipeid)
   {
      echo "You must select a recipe first.";
      exit;
   }

   $query = "SELECT name, servings, instructions FROM recipes WHERE id = ?";
   $result = dbquery_prepared($query, 'i', array($recipeid), $dbh) or die("Query error: " . db_error());
   if(!$result || !($row = db_fetch_array($result)))
   {
      echo "Recipe not found: '$recipeid'";
      exit;
   }
   $name = $row['name'];
   $servings = $row['servings'];
   $instructions = $row['instructions'];



   $addingredient = request_bool('addingredient', $_POST);
   $remingredient = request_bool('remingredient', $_POST);
   $scrollto = request_value('scrollto', '');
   $idisplay = request_int('idisplay', 0, $_POST);



   if($addingredient)
   {
      $searchterm = request_value('searchterm', '', $_POST);
      $amount = request_value('amount', '', $_POST);
      $unit = request_int('unit', 0, $_POST);
      $comment = request_value('comment', '', $_POST);
      $count = request_int('count', 0, $_POST);
      if($amount > 0)
      {
         $query = "SELECT id FROM ingredients WHERE name = ?";
         $result = dbquery_prepared($query, 's', array($searchterm), $dbh) or die("Query error: " . db_error());
         if(!$result || !($row = db_fetch_array($result)))
         {
            echo "Ingredient not found: '" . h($searchterm) . "'";
            exit;
         }
         $ingredient = $row['id'];

         $query = "INSERT INTO recipe_ingredients (recipe, ingredient, amount, unit, comment, pos) VALUES (?, ?, ?, ?, ?, ?)";
         dbquery_prepared($query, 'iisdsi', array($recipeid, $ingredient, $amount, $unit, $comment, $count), $dbh) or die("Query error: " . db_error());
      }
   }
   if($remingredient)
   {
      $ingredient = request_int('ingredient', 0, $_POST);
      $query = "DELETE FROM recipe_ingredients WHERE recipe = ? AND ingredient = ?";
      dbquery_prepared($query, 'ii', array($recipeid, $ingredient), $dbh) or die("Query error: " . db_error());
   }

?>
<?php render_page_start('Recipe Ingredient Editor', 'Recipe Ingredient Editor', 'Edit Recipe Ingredients'); ?>
   <script type="text/javascript" language="javascript">
   
   function findPos(obj) 
   {
   var curtop = 0;
   if (obj.offsetParent) 
   {
      do
      {
         curtop += obj.offsetTop;
      } while (obj = obj.offsetParent);
      return [curtop];
   }
   }

   function saveScrollCoordinates(form) 
   {
      document.forms[form].scrollx.value = (document.all)?document.body.scrollLeft:window.pageXOffset;
      document.forms[form].scrolly.value = (document.all)?document.body.scrollTop:window.pageYOffset;
   }

   function saveScrollTo(form, element) 
   {
      document.forms[form].scrollto.value = element;
   }

   function myPageLoad()
   {
      var elementId = '<?php echo $scrollto;?>';
      if(elementId)
      {
    window.scroll(0,findPos(document.getElementById(elementId)));
    document.getElementById(elementId).focus();
      }
      else
         document.getElementById('ingredient').focus();
      //window.scroll(0,findPos(document.getElementById('<?php echo $scrollto;?>')));
   }
</script>
<section class="card">
<p><a href="index.php">Back to Search Page</a></p>
<p><a href="editrecipe.php?recipeid=<?php echo $recipeid;?>">Back to Recipe Editor</a></p>
<?php echo $instructions; ?>
</section>
<section class="card table-wrap">
<?php list($total_oz, $total_cost, $count, $total_calories, $total_carbs, $total_fat, $total_protein, $total_fiber) = print_ingredients($recipeid, true, true, $idisplay); ?><br><br>
<?php
   if($servings > 0)
   {
      $oz_serving = round($total_oz / $servings, 1);
      $g_serving = round($total_oz * 28.3495231 / $servings, 1);
      $calories = round($total_calories / $servings, 1);
      $carbs = round($total_carbs / $servings, 1);
      $fat = round($total_fat / $servings, 1);
      $protein = round($total_protein / $servings, 1);
      $fiber = round($total_fiber / $servings, 1);
   }
   else
   {
      $oz_serving = 0;
      $g_serving = 0;
      $calories = 0;
      $carbs = 0;
      $fat = 0;
      $protein = 0;
      $fiber = 0;
   }

   if($total_oz > 0)
      $ed = round($total_calories / ($total_oz * 28.3495231), 2);
   else
      $ed = 0;
?>
<TABLE cellpadding=1 cellspacing=0 border=1>
<TR><TH>g(oz)/serving<TH>Calories<TH>Energy Density<TH>Carbs<TH>Fat<TH>Protein<TH>Fiber
<TR><TD><?php echo "$g_serving ($oz_serving)";?><TD><?php echo $calories?><TD><?php echo $ed?><TD><?php echo $carbs?><TD><?php echo $fat?><TD><?php echo $protein?><TD><?php echo $fiber?>
</TABLE>
</section>

<section class="card">
<H3>Add ingredients</H3>
<FORM NAME="form2" METHOD="POST" onSubmit="javascript:saveScrollTo('form2', 'ingredient')">
<INPUT TYPE="HIDDEN" NAME="scrollto">
<INPUT TYPE="HIDDEN" NAME="recipeid" VALUE="<?php echo $recipeid?>">
<INPUT TYPE="HIDDEN" NAME="count" VALUE="<?php echo $count?>">
<TABLE class="form-table">
<TR><TD>Amount<TD><INPUT TYPE="TEXT" NAME="amount" ID="ingredient" SIZE="5">
<TR><TD>Unit<TD><SELECT NAME="unit"><?php print_unit_options("") ?></SELECT>
<TR><TD>Ingredient<TD><input type="text" name="searchterm" id="searchterm" list="ingredient-options" />
<TR><TD>Comment<TD><INPUT TYPE="TEXT" NAME="comment">
</TABLE>
<datalist id="ingredient-options"><?php print_ingredient_name_options(); ?></datalist>
<div class="inline-actions">
<INPUT TYPE="SUBMIT" NAME="addingredient" ID="addingredient" VALUE="Add Ingredient">
<A HREF="editingredient.php?returnrecipe=<?php echo $recipeid?>">New ingredient</A>
</div>
</FORM>
</section>
<script>myPageLoad();</script>
<?php render_page_end(); ?>
