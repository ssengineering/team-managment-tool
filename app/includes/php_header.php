<?php
//FIle is required for all pages to reference the appropriate variables. This page also has a safe email function.


// include server parameters - YOU NEED THIS FOR ALL LINKS TO WORK PROPERLY - needs / at the end
define('HTTP_SERVER', 'http://'.$_SERVER['SERVER_NAME'].'/'); // eg, http://localhost/ - should not be empty for productive servers


// Variables that could be used to reference file locations
//define('WEBROOT_PARENT', '/auto/grp3/etdevweb/');
//define('DIR_SERVER', '/auto/grp3/etdevweb/www/'); 

//This function is used to safely display email addresses to stop spammers from obtaining your email
function safeAddress($emailAddress, $theText, $theTitle, $xhtml, $isItSafe) 
{
// Version 1.5 - by Dan Benjamin - http://www.hivelogic.com/
// set $isItSafe = 1 to get escaped HTML, 0 for normal HTML
// set $xhtml = 1 if you want your page to be valid for XHTML 1.x
// you can call it like this: 
//<?php echo safeAddress($entity, $linkText, $titleText, 1, 1); 

	$ent = "";
	$userName = "";
	$domainName = "";
    
	for ($i = 0; $i < strlen($emailAddress); $i++) 
	{
		$c = substr($emailAddress, $i, 1);
		if ($c == "@") 
		{
			$userName = $ent;
			$ent = "";
		} 
		else 
		{
			$ent .= "&#" . ord($c) . ";";
		}
	} // END FOR

	$domainName = $ent;
    
	if ($xhtml == 1) 
	{
		$endResult = "<script type=\"text/javascript\">
					<!--
					document.write('<a href=\"mailto:$userName&#64;$domainName\" title=\"$theTitle\">$theText<\/a>');
					// -->
					</script>";
	} // END IF
	else 
	{
		$endResult = "<script language=\"JavaScript\" type=\"text/javascript\">
					<!--
					document.write('<a href=\"mailto:$userName&#64;$domainName\" title=\"$theTitle\">$theText<\/a>');
					// -->
				</script>";
	} // END ELSE
	//return $endResult;
	
	if ($isItSafe) {return($endResult);} 
	else {return(htmlentities($endResult));}
	
} // END FUNCION
  

?>
