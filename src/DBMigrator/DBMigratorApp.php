<?php

namespace DBMigrator;

use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

class DBMigratorApp extends Application
{
	const DEFAULT_CONFIG_NAME = "dbmigrator.yml";

	/**
	 * Конфиг
	 *
	 * @var array
	 */
	public $config = null;

    public function __construct()
    {
    	parent::__construct("Welcome to DBMigrator", "2.0");
	    $this->setCatchExceptions(true);
	    $this->setAutoExit(false);

        // todo: load commands from dir
    	$this->addCommands(array(
			new Command\Check(),
			new Command\Init(),
			new Command\Commit(),
			new Command\Create(),
			new Command\Diff(),
			new Command\Log(),
			new Command\Migrate(),
			new Command\Status()
		));
    }

	public function readConfig()
    {
        if (!is_file(DBMigratorApp::DEFAULT_CONFIG_NAME))
        {
            throw new \Exception(sprintf("Configuration file: '%s' not found", DBMigratorApp::DEFAULT_CONFIG_NAME));
        }

        $this->config = Yaml::parse(DBMigratorApp::DEFAULT_CONFIG_NAME);
    }
}
