<?php

namespace DBMigrator;

use Symfony\Component\Console\Application;
	

class DBMigratorApp extends Application 
{		
	const DEFAULT_CONFIG_NAME = "dbmigrator.ini";
	
	/**
	 * Менеджер миграций
	 *
	 * @var MigrationManager
	 */
	public $manager = null;

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
	    
	    $path = $this->getConfigPath();	    
	    if (is_file($path))	    
		    $this->config = parse_ini_file($path);	    
	    
	    $this->manager = new \stdClass();	    
		    	    	    
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

	private function getConfigPath()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . self::DEFAULT_CONFIG_NAME;
	}
}
