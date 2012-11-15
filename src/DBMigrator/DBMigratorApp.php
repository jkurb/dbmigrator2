<?php

namespace DBMigrator;

use Symfony\Component\Console\Application;
	

class DBMigratorApp extends Application 
{
    public function __construct() 
    {
    	parent::__construct("Welcome to DBMigrator", "2.0");
    	
    	$this->addCommands(array(
			new Command\Test(),			
			new Command\Init(),			
		));
    }
}
