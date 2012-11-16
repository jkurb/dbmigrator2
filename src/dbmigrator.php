<?php

use DBMigrator\DBMigratorApp;

require realpath(__DIR__) . "/../vendor/autoload.php";

$app = new DBMigratorApp();
$app->run();
