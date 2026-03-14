<?php
require "header.inc";

$query = request_value('query', '', $_GET);
$fdc_id = request_int('fdc_id', 0, $_GET);
$foods = array();
$food = false;

if($fdc_id)
{
   $food = usda_get_food($fdc_id);
}
elseif($query)
   $foods = usda_search_foods($query);
?>
<HTML>
<HEAD>
<TITLE>USDA FoodData Central Lookup</TITLE>
</HEAD>
<BODY>
<FORM METHOD="GET">
<INPUT TYPE="TEXT" NAME="query" SIZE=100 VALUE="<?php echo h($query)?>"><BR>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Submit">
</FORM>
<?php
if(!usda_api_key())
   echo "USDA lookup is not configured. Add `usda_api_key` to your local config file.<BR>";
elseif($food)
{
   $nutrients = usda_extract_nutrients($food);
   echo "Description: " . h($food['description']) . "<BR>\n";
   echo "Serving size: " . h(isset($food['servingSize']) ? $food['servingSize'] : 100) . "<BR>\n";
   echo "Calories: " . h($nutrients['calories']) . "<BR>\n";
   echo "Carbs: " . h($nutrients['carbs']) . "<BR>\n";
   echo "Fiber: " . h($nutrients['fiber']) . "<BR>\n";
   echo "Fat: " . h($nutrients['fat']) . "<BR>\n";
   echo "Protein: " . h($nutrients['protein']) . "<BR>\n";
}
elseif($query && $foods && isset($foods['foods']))
{
   echo "<TABLE BORDER=1 CELLPADDING=5>";
   foreach($foods['foods'] as $item)
      echo "<TR><TD><A HREF=\"?fdc_id=" . h($item['fdcId']) . "\">" . h($item['description']) . "</A></TD></TR>";
   echo "</TABLE>";
}
elseif($query)
   echo "No USDA matches found.<BR>";
?>
</BODY>
</HTML>
