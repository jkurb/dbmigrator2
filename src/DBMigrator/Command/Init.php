<?php
/**
 * Init migration repository
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use DBMigrator\DBMigratorApp;
use DBMigrator\Utils\FileSystem;
use DBMigrator\Utils\MigrationHelper;
use DBMigrator\Utils\MigrationManager;
use DBMigrator\Utils\DBMigratorException;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class Init extends BaseCommand
{
	protected function configure()
	{
		$this->setName("init")
			->setDescription("Initializes migration repository, creates config and migration table")
			->setHelp(sprintf("%Initializes migration repository and creates migration table.%s", PHP_EOL, PHP_EOL));
	}

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        if (!is_file(DBMigratorApp::DEFAULT_CONFIG_NAME))
        {
            $this->createConfigInteractive($output);
        }

        $this->getApplication()->readConfig();

        $this->migrator = new MigrationManager($this->getApplication()->config);

		//todo: create database if not found

		$this->migrator->createMigrationTable();

		// проверяем существование начальной миграции
		if ($this->migrator->getMigrationById(1))
			throw new DBMigratorException("Can't apply init migration, because another exists");

		// Идентификатор миграции, название директории в которой хранится дамп
		$uid = MigrationHelper::getCurrentTime();

		MigrationHelper::checkDir($this->getApplication()->config["migration"]["path"], $uid);

		// создаем запись в таблице
		if (!$this->migrator->getMigrationByTime($uid))
		{
			sleep(1);
			$this->migrator->insertMigration($uid, "Init migration");
		}

		// создаем начальный каталог c дампом базы
		$this->migrator->createDump($uid);

		// устанавливаем версию миграции
		$this->migrator->setCurrentVersion($uid);

		$output->writeln("\n<info>Migartion initialized</info>\n");
	}

    /**
     * @param OutputInterface $output
     * @throws DBMigratorException
     */
    private function createConfigInteractive(OutputInterface $output)
    {
        $config = array();

        /** @var $dialog DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');

        if (!$dialog->askConfirmation(
            $output,
            sprintf("Config file '%s' not found. Would you like to generate? (yes|no) ", DBMigratorApp::DEFAULT_CONFIG_NAME),
            false)
        ) {
            $output->writeln("<error>Command aborted</error>");
            exit(0);
        }

	    $config["db"]["driver"] = $dialog->ask($output, "PDO driver (default: pdo_mysql): ", "pdo_mysql");
	    $config["db"]["host"] = $dialog->ask($output, "Host (default: localhost): ", "localhost");
	    $config["db"]["dbname"] = $dialog->askAndValidate($output, "Database: ", function ($value) {
		    if (empty($value))
			    throw new DBMigratorException("Database can not be empty");

		    return $value;
	    });
        $config["db"]["user"] = $dialog->askAndValidate($output, "User name: ", function ($value) {
            if (empty($value))
                throw new DBMigratorException("Username can not be empty");

            return $value;
        });
	    $config["db"]["password"] = $dialog->ask($output, "Password: ", "");
        $config["migration"]["table"] = $dialog->ask($output, "Migration table name (defalut: __migrations): ", "__migration");
	    $config["migration"]["path"] = $dialog->askAndValidate($output, "Migration path: ", function ($value) {
            if (!is_dir($value))
                throw new DBMigratorException("Directory does not exist");

            return $value;
        });

        $output->write("<info>Generating config...</info>");

	    FileSystem::putFile(DBMigratorApp::DEFAULT_CONFIG_NAME, Yaml::dump($config));

        $output->writeln("<info>done</info>");
    }
}
