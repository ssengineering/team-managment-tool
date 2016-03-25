<?php 

$appBaseDir = dirname(__FILE__)."/../app/";

require $appBaseDir."autoload.php";
require $appBaseDir."../vendor/autoload.php";


$al = new \TMT\Autoloader();
$al->addNamespace("TMT\\app\\", $appBaseDir."applications");
$al->addNamespace("TMT\\api\\", $appBaseDir."apis");
$al->addNamespace("TMT\\model\\", $appBaseDir."models");
$al->addNamespace("TMT\\accessor\\", $appBaseDir."accessors");
$al->addNamespace("TMT\\controller\\", $appBaseDir."controllers");
$al->addNamespace("TMT\\exception", $appBaseDir."exceptions");
$al->addNamespace("TMT\\", $appBaseDir."libs");
$al->register();

