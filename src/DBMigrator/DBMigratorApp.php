<?php

namespace DBMigrator;

use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

class DBMigratorApp extends Application
{
    public function __construct()
    {
    	parent::__construct("\nWelcome to DBMigrator", "2.0");
	    $this->setCatchExceptions(true);
	    $this->setAutoExit(false);
	    $this->setCatchExceptions(false);

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
}
