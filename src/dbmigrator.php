<?php

use DBMigrator\DBMigratorApp;
use DBMigrator\Utils\WinUtf8ConsoleOutput;
use DBMigrator\Utils\WinUtf8ArgvInput;

set_error_handler(function($errno, $errstr, $errfile, $errline) {
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

require realpath(__DIR__) . "/../vendor/autoload.php";

$app = new DBMigratorApp();
$app->run(new WinUtf8ArgvInput(), new WinUtf8ConsoleOutput());
