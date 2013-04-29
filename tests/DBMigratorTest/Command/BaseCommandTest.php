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

abstract class BaseCommandTest extends \PHPUnit_Framework_TestCase
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

        $this->command = $this->app->find(strtolower(str_replace("Test", "", get_called_class())));
        $this->command->init(__DIR__ . "/../../data/dbmigrator.yml");
        $this->commandTester = new CommandTester($this->command);
    }
}
