<?php
/**
 * TODO: Добавить здесь комментарий
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

require_once "../../vendor/autoload.php";

use Symfony\Component\Console\Tester\CommandTester;
use DBMigrator\DBMigratorApp;
use DBMigrator\Command\Create;


class CreateTest extends PHPUnit_Framework_TestCase
{
	public function testExecute() 
	{		
		$app = new DBMigratorApp();
		$app->add(new Create());
		$command = $app->find("create");
		$commandTester = new CommandTester($command);				
		$commandTester->execute(array("command" => $command->getName()));
				
		$this->assertEquals("Empty 'delta.sql' has created" . PHP_EOL, $commandTester->getDisplay());
	}
}
