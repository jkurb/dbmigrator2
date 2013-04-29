<?php

namespace DBMigratorTest;

use Symfony\Component\Console\Tester\ApplicationTester;
use DBMigrator\DBMigratorApp;

class DBMigratorAppTest extends \PHPUnit_Framework_TestCase
{
	public function testRun()
	{
		$migrator = new DBMigratorApp();
		$migrator->setAutoExit(false);
		$migratorTester = new ApplicationTester($migrator);

		$migratorTester->run(array());

		$disp = $migratorTester->getDisplay();
		$this->assertNotRegExp("/Exception/", $disp);
		$this->assertRegExp("/Welcome to DBMigrator version 2.0/", $disp);
	}
}
