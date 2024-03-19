<?php
$myfile = fopen($_REQUEST["scope"] . ".txt", "w");
//$txt = $_REQUEST["textbox"];
$txt = $_REQUEST["contenu"] . "\n\r---------------------------\n\r \n\r ";
fwrite($myfile, $txt);
fclose($myfile);
?>
done