<?php
/**
 * TODO: Добавить здесь комментарий
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigratorTest\Command;

use DBMigrator\Command\BaseCommand;
use DBMigrator\DBMigratorApp;
use Symfony\Component\Console\Tester\CommandTester;

abstract class BaseDBCommandTest extends \PHPUnit_Extensions_Database_TestCase
{
	protected $app = null;

	/**
	 * @var BaseCommand
	 */
	protected $command = null;

	/**
	 * @var CommandTester|null
	 */
	protected $commandTester = null;

	public function __construct()
	{
		parent::__construct();
		$this->app = new DBMigratorApp();
	}

	public function setUp()
	{
		parent::setUp();

		preg_match("/.*\\\(.*?)Test$/is", get_called_class(), $match);
		$cmd = strtolower($match[1]);

		$this->command = $this->app->find($cmd);
		$this->command->init(__DIR__ . "/../../data/dbmigrator.yml");
		$this->commandTester = new CommandTester($this->command);
	}

	/**
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	public function getConnection()
	{
		$pdo = new \PDO("mysql:host=192.168.11.230;dbname=test", "root", "toor",
			array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);

		$pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
		return $this->createDefaultDBConnection($pdo);
	}

	/**
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . "/../../data/test.xml");
	}

	protected function getSetUpOperation()
	{
		return \PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
	}

	protected function getTearDownOperation()
	{
		return \PHPUnit_Extensions_Database_Operation_Factory::NONE();
	}
}
