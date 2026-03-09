<?php
   require "header.inc";
   include_once("../fckeditor/fckeditor.php");


   if(isset($_POST['recipeid']))
      $recipeid = $_POST['recipeid'];
   elseif(isset($_GET['recipeid']))
      $recipeid = $_GET['recipeid'];
   else
      $recipeid = 0;

   if(!$recipeid)
   {
      echo "You must select a recipe first.";
      exit;
   }

   $query = "SELECT name, servings, instructions FROM recipes WHERE id = '$recipeid'";
   $result = dbquery($query, $dbh) or die("Query error: " . db_error() . "<br>Query was: $query");         
   if(!$result || !($row = db_fetch_array($result)))
   {
      echo "Recipe not found: '$recipeid'";
      exit;
   }
   $name = $row['name'];
   $servings = $row['servings'];
   $instructions = $row['instructions'];



   if(isset($_POST['addingredient']))
      $addingredient = true;
   else
      $addingredient = false;

   if(isset($_POST['remingredient']))
      $remingredient = true;
   else
      $remingredient = false;

   if(isset($_POST['scrollto']))
      $scrollto = $_POST['scrollto'];
   elseif(isset($_GET['scrollto']))
      $scrollto = $_GET['scrollto'];
   else
      $scrollto = "";

   if(isset($_POST['idisplay']))
      $idisplay = $_POST['idisplay'];
   else
      $idisplay = 0;



   if($addingredient)
   {
      $searchterm = addslashes($_POST['searchterm']);
      $amount = $_POST['amount'];
      $unit = $_POST['unit'];
      $comment = addslashes($_POST['comment']);
      $count = $_POST['count'];
      if($amount > 0)
      {
         $query = "SELECT id FROM ingredients WHERE name = '$searchterm'";
         $result = dbquery($query, $dbh) or die("Query error: " . db_error() . "<br>Query was: $query");
         if(!$result || !($row = db_fetch_array($result)))
         {
            echo "Ingredient not found: '$searchterm'";
            exit;
         }
         $ingredient = $row['id'];

         $query = "INSERT INTO recipe_ingredients (recipe, ingredient, amount, unit, comment, pos) VALUES ('$recipeid', '$ingredient', '$amount', '$unit', '$comment', '$count')";
         dbquery($query, $dbh) or die("Query error: " . db_error() . "<br>Query was: $query");
      }
   }
   if($remingredient)
   {
      $ingredient = $_POST['ingredient'];
      $query = "DELETE FROM recipe_ingredients WHERE recipe = '$recipeid' AND ingredient = '$ingredient'";
      dbquery($query, $dbh) or die("Query error: " . db_error() . "<br>Query was: $query");
   }

?>
   <style>
   body {font-family: verdana, arial, sans-serif; font-size: 12px; }
   #search, ul { padding: 3px; width: 150px; border: 1px solid #999; font-family: verdana, arial, sans-serif; font-size: 12px;}
   ul { list-style-type: none; font-family: verdana, arial, sans-serif; font-size: 12px;  margin: 5px 0 0 0}
   li { margin: 0 0 5px 0; cursor: default; color: red;}
   li:hover { background: #ffc; }
   li.selected { background: #ffc; }
   </style>
   <TITLE>Recipe Editor</TITLE>
   <script type="text/javascript" src="../scriptaculous-js/lib/prototype.js"></script>
   <script type="text/javascript" src="../scriptaculous-js/src/effects.js"></script>
   <script type="text/javascript" src="../scriptaculous-js/src/controls.js"></script>
   <!--<script type="text/javascript" src="../scriptaculous-js/src/scriptaculous.js"></script>-->
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
      var elementId = '<?echo $scrollto;?>';
      if(elementId)
      {
    window.scroll(0,findPos(document.getElementById(elementId)));
    document.getElementById(elementId).focus();
      }
      else
    document.form1.name.focus();
      //window.scroll(0,findPos(document.getElementById('<?echo $scrollto;?>')));
   }
</script>
</HEAD>
<BODY onLoad="javascript:myPageLoad()">
<H2><CENTER>Editing ingredients for recipe: <? echo "$name" ?></CENTER></H2>
<A HREF="index.php">&gt; Back to Search Page &lt;</A><BR>
<BR>
<A HREF="editrecipe.php?recipeid=<? echo $recipeid;?>">&gt; Back to Recipe Editor &lt;</A><BR>
<P>
<?php echo $instructions; ?>
<P>
<? list($total_oz, $total_cost, $count, $total_calories, $total_carbs, $total_fat, $total_protein, $total_fiber) = print_ingredients($recipeid, true, true, $idisplay); ?><br><br>
<?
   $oz_serving = round($total_oz / $servings, 1);
   $g_serving = round($total_oz * 28.3495231 / $servings, 1);
   $ed = round($total_calories / ($total_oz * 28.3495231), 2);
   $calories = round($total_calories / $servings, 1);
   $carbs = round($total_carbs / $servings, 1);
   $fat = round($total_fat / $servings, 1);
   $protein = round($total_protein / $servings, 1);
   $fiber = round($total_fiber / $servings, 1);
?>
<TABLE cellpadding=1 cellspacing=0 border=1>
<TR><TH>g(oz)/serving<TH>Calories<TH>Energy Density<TH>Carbs<TH>Fat<TH>Protein<TH>Fiber
<TR><TD><?echo "$g_serving ($oz_serving)";?><TD><?echo $calories?><TD><?echo $ed?><TD><?echo $carbs?><TD><?echo $fat?><TD><?echo $protein?><TD><?echo $fiber?>
</TABLE>

<H3>Add ingredients:</H3>
<br>
<TABLE>
<FORM NAME="form2" METHOD="POST" onSubmit="javascript:saveScrollTo('form2', 'ingredient')">
<INPUT TYPE="HIDDEN" NAME="scrollto">
<INPUT TYPE="HIDDEN" NAME="recipeid" VALUE="<?echo $recipeid?>">
<INPUT TYPE="HIDDEN" NAME="count" VALUE="<?echo $count?>">
<TR><TD>Amount:<TD>Unit:<TD>Ingredient:<TD>Comment:
<TR><TD><INPUT TYPE="TEXT" NAME="amount" ID="ingredient" SIZE="5"><TD><SELECT NAME="unit"><? print_unit_options("") ?></SELECT> of <TD><input type="text" name="searchterm" id="searchterm" />
<div id="hint"></div>
   <script type="text/javascript">
      new Ajax.Autocompleter("searchterm","hint","server.php");
   </script>
<TD><INPUT TYPE="TEXT" NAME="comment">
<TR><TD colspan="2"><INPUT TYPE="SUBMIT" NAME="addingredient" ID="addingredient" VALUE="Add Ingredient"><BR>
<TR><TD>&nbsp;
<TR><TD colspan="2"><A HREF="editingredient.php?returnrecipe=<?echo $recipeid?>">&gt; New ingredient &lt;</A>
</FORM>
</TABLE>
</BODY>
</HTML>

