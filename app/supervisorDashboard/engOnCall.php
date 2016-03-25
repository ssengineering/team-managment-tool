<?php

require("../includes/includeMeBlank.php");

$returnsAndTabs = array("&#09;", "&#10;", "&#11;", "&#13;",	"&#12;", "\r", "\n", "\r\n", "\t");
$engOnCallRegularExpression = "<B>".date('j')."</B>.*?<TABLE.*?<TABLE.*?(ENG-ON CALL).*?(SECONDARY).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?(ENG-COMM).*?NEWWINDOW\('(.*?)'.*?>(.*?)</a>.*?(ENG_DBA).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?(INT-PRIM).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?(INT-SEC).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?(NE).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?(OPS  REMOTE MGR & BACKUPS).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?(OPS REMOTE MGR BACKUP).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>.*?</TABLE>";

/* OPSEC STUFF .*?(OPSSEC).*?NEWWINDOW\('(.*?)'.*?>(.*?)</A>  */

$netId = $netID;
$password = passDecrypt($_GET['p']);

$cookieFilename = tempnam('/tmp', 'CURLCOOKIE');

$postFields = 'netid=' . urlencode($netId) . '&passpass=' . urlencode($password);

//log in first
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, getenv("ONCALL_LOGIN_URL"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFilename);
curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false);
$loginReply = curl_exec ($curl);

if(curl_errno($curl))
{
    echo 'error:' . curl_error($curl).'<br />';
}

//could check for the string 'You are now authorized' in $loginReply here if you want to make sure login was successful
curl_close ($curl);

$curl = curl_init(getenv("ONCALL_CALENDAR_URL"));
curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFilename);
curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false);
$pageData = curl_exec ($curl);
curl_close($curl);

$pageData = str_replace($returnsAndTabs, "", $pageData);

preg_match('~'.$engOnCallRegularExpression.'~i', $pageData, $group);

echo "<div style='font-size: 65%; border: 0px;'>".$group[0].'</div>';

?>
