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
use DBMigrator\Utils\MigrationManager;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class Init extends Base
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

        $this->migrator = new MigrationManager(
            $this->getApplication()->config["host"],
            $this->getApplication()->config["username"],
            $this->getApplication()->config["password"],
            $this->getApplication()->config["database"]
        );

        $this->migrator->init($this->getApplication()->config["path"]);

        //create migartion table


	}

    /**
     * @param OutputInterface $output
     * @throws \Exception
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

        $host = $dialog->ask($output, "Host (default: localhost): ", "localhost");

	    $dbname = $dialog->askAndValidate($output, "Database: ", function ($value) {
		    if (empty($value))
			    throw new \Exception("Database can not be empty");

		    return $value;
	    });

	    $config["db"]["dsn"] = "mysql:dbname={$dbname};host={$host}";

        $config["db"]["user"] = $dialog->askAndValidate($output, "User name: ", function ($value) {
            if (empty($value))
                throw new \Exception("Username can not be empty");

            return $value;
        });

	    $config["db"]["pass"] = $dialog->ask($output, "Password: ", "");

        $config["migration"]["table"] = $dialog->ask($output, "Migration table name (defalut: __migrations): ", "__migration");

	    $config["migration"]["path"] = $dialog->askAndValidate($output, "Migration path: ", function ($value) {
            if (!is_dir($value))
                throw new \Exception("Directory does not exist");

            return $value;
        });

        $output->write("<info>Generating config...</info>");

        file_put_contents(DBMigratorApp::DEFAULT_CONFIG_NAME, Yaml::dump($config));

        $output->writeln("<info>done</info>");
    }
}
