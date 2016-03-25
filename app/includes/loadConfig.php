<?php
	## This file is used to load configuration values from environment.conf into the $config variable ##

	$configFile = file($_SERVER["DOCUMENT_ROOT"]."/includes/config/psp.conf", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 

	$config = array();

	foreach($configFile as $line)
	{
		if ($line[0] != "#")
		{
			$line = explode("=", $line);
			$config[$line[0]] = $line[1];
		}
	}
	unset($configFile);

?>
