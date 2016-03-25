<?php 

/**
 * Team Management Tool
 * 
 * This file is the initializer for all requests made to the TMT
 * 
 * @package team-management-tool
 * @license proprietary
 */

namespace TMT;

// Composer Autoloader
require dirname(__FILE__)."/../vendor/autoload.php";

// Initialize TMT Autoloader
require 'autoload.php';
$al = new Autoloader();
$al->addNamespace("TMT\\app\\", "applications");
$al->addNamespace("TMT\\api\\", "apis");
$al->addNamespace("TMT\\model\\", "models");
$al->addNamespace("TMT\\accessor\\", "accessors");
$al->addNamespace("TMT\\controller\\", "controllers");
$al->addNamespace("TMT\\exception\\", "exceptions");
$al->addNamespace("TMT\\", "libs");
$al->register();

$app = new \TMT\TMT();
$app->handle();




