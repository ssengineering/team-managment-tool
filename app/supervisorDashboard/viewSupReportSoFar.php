<?php

require("../includes/includeMeBlank.php");

if (isset($_SESSION['supReport']))
{
	$soFar = $_SESSION['supReport'];

	echo "<table style='margin: auto; max-width: 555;'><tr><th style='width: 30%;'>Time</th><th style='width: 70%;'>Entry</th></tr>";
	
	foreach ($soFar as $entry)
	{
		echo '<tr><td>'.$entry[0].'</td><td>'.$entry[2].'</td></tr>';
	}
	
	echo "</table>";

}

else 
{
	echo '<h4 stlye="width: 100%; margin-left: auto; margin-right: auto; text-align: center; float: none; font-size: 1.5em; font-weight: bold;">You have no entries so far.</h4>';
}

?>
