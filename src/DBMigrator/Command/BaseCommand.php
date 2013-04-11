<?php
namespace DBMigrator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;
use DBMigrator\Utils\MigrationManager;

/**
 * @method DBMigrator\DBMigratorApp getApplication()
 */
abstract class BaseCommand extends Command
{
    /**
     * @var MigrationManager
     */
    public $migrator = null;

	/**
	 * Initializes the command just after the input has been validated.
	 *
	 * This is mainly useful when a lot of commands extends one main command
	 * where some things need to be initialized based on the input arguments and options.
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @throws \Exception
	 * @return void
	 */
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
        $this->getApplication()->readConfig();

		$this->migrator = new MigrationManager($this->getApplication()->config);
	}
}
