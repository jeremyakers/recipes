<?php 
include "header.inc";

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
else
{
}

if(isset($_GET['adv_search']))
   $_SESSION['adv_search'] = $_GET['adv_search'];


if(isset($_GET['format_select']))
   $_SESSION['format_select'] = $_GET['format_select'];
elseif($mobile)
   $_SESSION['format_select'] = "Mobile";
else
   $_SESSION['format_select'] = "Wide";

if(isset($_GET['search']))
   $_SESSION['search'] = $_GET['search'];

if(isset($_GET['recipe_search']))
   $_SESSION['recipe_search'] = $_GET['recipe_search'];

if(isset($_GET['category_select']))
   $_SESSION['category_select'] = $_GET['category_select'];

$format = isset($_SESSION['format_select']) ? $_SESSION['format_select'] : "";
$search = isset($_SESSION['search']) ? $_SESSION['search'] : "";
$recipe = isset($_SESSION['recipe_search']) ? $_SESSION['recipe_search'] : "";
$category = isset($_SESSION['category_select']) ? $_SESSION['category_select'] : 0;
$dbsort_order = addslashes($sort_order);
$adv_search = isset($_SESSION['adv_search']) ? $_SESSION['adv_search'] : 0;

if(!$sort)
   $sort = "mycategory";
$dbsort = addslashes($sort);
?>
<HTML>
<HEAD><TITLE>Recipe Search</TITLE></HEAD>
<BODY>
<FORM METHOD="GET">
<TABLE>
   <TR><TD>Format: <TD><SELECT NAME="format_select">
   <?php print_format_options($format); ?>
   </SELECT>
   <TR><TD>Category:<TD><SELECT NAME="category_select">
   <OPTION VALUE="0">All
<?php print_category_options($category);   ?>
   </SELECT>
   <TR><TD>Recipe name:<BR>(Leave blank for all)<TD><INPUT TYPE="TEXT" NAME="recipe_search" VALUE="<?php echo $recipe;?>">
<?php if(!$adv_search) { ?>
</TABLE>
<A HREF="index.php?adv_search=1">More options &gt;&gt;&gt;</A><BR>
<?php } else { ?>
<TR><TD>Ingredients:<TD> <SELECT MULTIPLE NAME="ingredients[]"><?php print_ingredient_options(isset($_GET['ingredients']) ? $_GET['ingredients'] : array()); ?></SELECT>
</TABLE>
<A HREF="index.php?adv_search=0">&lt;&lt;&lt; Basic search</A><BR>
<?php } ?>
   <INPUT TYPE="SUBMIT" NAME="search" VALUE="Search"><BR>
</FORM><BR>
<FORM ACTION="editrecipe.php" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="format_select" VALUE="<?php echo $format;?>">
<INPUT TYPE="HIDDEN" NAME="category_select" VALUE="<?php echo $category;?>">
<INPUT TYPE="HIDDEN" NAME="recipe_search" VALUE="<?php echo $recipe;?>">
<INPUT TYPE="SUBMIT" NAME="new" VALUE="New Recipe"><BR>
</FORM><BR>
<A HREF="ingredients.php">&gt; Search Ingredients &lt;</A><BR>
<?php
if($category > 0)
   $cat_search = "AND category = '$category'";
else
   $cat_search = "";

$ingwhere = "";
$inghaving = "";
$i = 0;
if(isset($_GET['ingredients']))
{
   foreach ($_GET['ingredients'] AS $ing) 
   { 
      if($i) 
         $ingwhere .= ", "; 
      $i++; 
      $ingwhere .= $ing;
   }
}
if($ingwhere)
{
   $ingwhere = " AND ingredient IN ($ingwhere)";
   $inghaving = " HAVING ing_count > 0";
}


if($search)
{
   echo "<BR><BR>\n";
   $query = "SELECT 
         recipes.id id,  -- 0
	    MAX(category) mycategory,  -- 1
	       MAX(categories.name) category_name,  -- 2
	          MAX(recipes.name) recipe_name,  -- 3
		     MAX(\"time\") recipe_time,  -- 4
		        MAX(servings) servings, -- 5
		           MAX(calories) calories, -- 6
		       MAX(energy_density) energy_density, -- 7
	        MAX(carbs) carbs, -- 8
     MAX(fat) fat, -- 9
    MAX(protein) protein, -- 10
    MAX(fiber) fiber, -- 11
    COUNT(ingredient) AS ing_count -- 12
    FROM categories 
    INNER JOIN recipes 
      ON recipes.category = categories.id 
    LEFT OUTER JOIN recipe_ingredients 
      ON recipe_ingredients.recipe = recipes.id 
    WHERE recipes.name LIKE '%$recipe%' $cat_search $ingwhere 
    GROUP BY recipes.id $inghaving 
    ORDER BY $dbsort $sort_order, recipes.id";
   $result = dbquery($query, $dbh) or die("Error searching for recipes: " . db_error() . "<br>Query was: " . $query);
   if(!$result)
   {
      echo "No matching recipes found.";
      exit;
   }
   echo "<TABLE cellpadding=1 cellspacing=1 border=1><THEAD>\n\r";
   echo "<COL width='110'><COL width='200'>";
   if($format == "Wide")
      echo "<COL><COL><COL><COL><COL><COL><COL><COL><COL>";
   echo "<TR><TH><A HREF='index.php?sort=category_name'>Category</A><TH><A HREF='index.php?sort=recipe_name'>Name</A>";
   if($format == "Wide")
      echo "<TH>Edit<TH><A HREF='index.php?sort=recipe_time'>Time</A><TH><A HREF='index.php?sort=servings'>Servings</A><TH><A HREF='index.php?sort=calories'>Calories</A><TH><A HREF='index.php?sort=energy_density'>Energy Density</A><TH><A HREF='index.php?sort=carbs'>Carbs</A><TH><A HREF='index.php?sort=fat'>Fat</A><TH><A HREF='index.php?sort=protein'>Protein</A><TH><A HREF='index.php?sort=fiber'>Fiber</A><TH><A HREF='index.php?sort=COUNT(ingredient)'># Ingredients</A>";
   echo "\n\r<TBODY>\n\r";
   while($row = db_fetch_array($result))
   {
      $edittext = "<FORM ACTION=\"editrecipe.php\" METHOD=\"GET\"><INPUT TYPE=\"HIDDEN\" NAME=\"recipeid\" VALUE=\"".$row['id']."\"><INPUT TYPE=\"SUBMIT\" NAME=\"edit\" VALUE=\"Edit\"></FORM> ";
      echo "<TR><TD>".$row['category_name']."<TD><A HREF=\"showrecipe.php?recipeid=".$row['id']."\">".$row['recipe_name']."</A>";
      if($format == "Wide")
         echo "<TD>$edittext<TD>".$row['recipe_time']."<TD>".$row['servings']."<TD>".$row['calories']."<TD>".$row['energy_density']."<TD>".$row['carbs']."<TD>".$row['fat']."<TD>".$row['protein']."<TD>".$row['fiber']."<TD>".$row['ing_count']."\n\r";
   }
   echo "</TABLE>";
}
?>
</BODY>
</HTML>
