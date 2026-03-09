<HTML>
<HEAD>
<TITLE>Get Data From nutritiondata.com</TITLE>
</HEAD>
<BODY>
<?php
if(isset($_GET['url']))
   $url = $_GET['url'];
else
   $url = "";

if($url)
{
   $ndata = file_get_contents($url);
   $pattern = "/1 cup  \((.*)g\)/i";
   preg_match($pattern, $ndata, $matches);
   echo "Grams per cup: " . $matches[1] . "<BR>\n\r";
   $pattern = "/ NUTRIENT_0:\"([0-9\.]*)\",/";
   preg_match($pattern, $ndata, $matches);
   echo "Calories per 100 grams: " . $matches[1]  . "<BR>\n\r";
   $pattern = "/ NUTRIENT_4:\"([0-9\.]*)\",/";
   preg_match($pattern, $ndata, $matches);
   echo "Carbs per 100 grams: " . $matches[1] . "<BR>\n\r";
   $pattern = "/ NUTRIENT_5:\"([0-9\.]*)\",/";
   preg_match($pattern, $ndata, $matches);
   echo "Fiber per 100 grams: " . $matches[1] . "<BR>\n\r";
   $pattern = "/ NUTRIENT_14:\"([0-9\.]*)\",/";
   preg_match($pattern, $ndata, $matches);
   echo "Fat per 100 grams: " . $matches[1] . "<BR>\n\r";
   $pattern = "/ NUTRIENT_77:\"([0-9\.]*)\",/";
   preg_match($pattern, $ndata, $matches);
   echo "Protein per 100 grams: " . $matches[1] . "<BR>\n\r";

   #echo "<PRE>";
   #print_r($matches);
   #echo "</PRE>";

}
?>
<FORM METHOD="GET">
<INPUT TYPE="TEXT" NAME="url" SIZE=100 VALUE="<?php echo $url?>"><BR>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Submit">
</BODY>
</HTML>
