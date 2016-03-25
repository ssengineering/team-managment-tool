<?php

$pdo_options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ);

$host       = getenv('DB_HOST');
$dbusername = getenv('DB_USER');
$dbpassword = getenv('DB_PASS');
$database   = getenv('DB_NAME');
$pdo_string = "mysql:host=".$host.";dbname=".$database;

// Connect using PDO
try {
    $db = new PDO($pdo_string, $dbusername, $dbpassword, $pdo_options);
} catch (PDOException $e) {
    exit("Database connection could not be established.");
}

?>
