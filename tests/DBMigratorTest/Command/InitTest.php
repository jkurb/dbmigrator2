<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eugene
 * Date: 30.04.13
 * Time: 14:37
 * To change this template use File | Settings | File Templates.
 */

namespace DBMigratorTest\Command;

use DBMigrator\Command\Init;
use DBMigrator\Utils\FileSystem;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

class InitTest extends BaseCommandTest
{
	public function testCreateConfig()
	{
		$this->command = $this->app->find("init");

		$dialog = $this->getMock("Symfony\\Component\\Console\\Helper\\DialogHelper",
			array("askConfirmation", "ask", "askAndValidate")
		);

		$dialog->expects($this->once())
			->method("askConfirmation")
			->will($this->returnValue(true));

		$dialog->expects($this->at(1))
			->method("ask")
			->with($this->anything(), $this->stringStartsWith("PDO driver"))
			->will($this->returnValue("pdo_mysql"));

		$dialog->expects($this->at(2))
			->method("ask")
			->with($this->anything(), $this->stringStartsWith("Host"))
			->will($this->returnValue("192.168.11.230"));

		$dialog->expects($this->at(3))
			->method("askAndValidate")
			->with($this->anything(), $this->stringStartsWith("Database"))
			->will($this->returnValue("test"));

		$dialog->expects($this->at(4))
			->method("askAndValidate")
			->with($this->anything(), $this->stringStartsWith("User name"))
			->will($this->returnValue("root"));

		$dialog->expects($this->at(5))
			->method("ask")
			->with($this->anything(), $this->stringStartsWith("Password"))
			->will($this->returnValue("toor"));

		$dialog->expects($this->at(6))
			->method("ask")
			->with($this->anything(), $this->stringStartsWith("Migration table"))
			->will($this->returnValue("__migration"));

		$dialog->expects($this->at(7))
			->method("askAndValidate")
			->with($this->anything(), $this->stringStartsWith("Migration path"))
			->will($this->returnValue("./"));

		// We override the standard helper with our mock
		$this->command->getHelperSet()->set($dialog, "dialog");

		$this->command->createConfigInteractive(new ConsoleOutput());

		$config = Yaml::parse(FileSystem::getFile(Init::DEFAULT_CONFIG_NAME));

		FileSystem::delete(Init::DEFAULT_CONFIG_NAME);

		$this->assertNotNull($config);
		$this->assertEquals("pdo_mysql", $config["db"]["driver"]);
		$this->assertEquals("192.168.11.230", $config["db"]["host"]);
		$this->assertEquals("test", $config["db"]["dbname"]);
		$this->assertEquals("root", $config["db"]["user"]);
		$this->assertEquals("toor", $config["db"]["password"]);
		$this->assertEquals("__migration", $config["migration"]["table"]);
		$this->assertEquals("./", $config["migration"]["path"]);
	}

	public function testExecute()
	{
		$this->commandTester->execute(array("command" => $this->command->getName()));
		$this->assertRegExp("/Migartion initialized/", $this->commandTester->getDisplay());

		$this->command->migrator->emptyDatabase();
	}

}
