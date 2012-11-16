<?php
namespace DBMigrator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

abstract class Base extends Command
{
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
		if (is_null($this->getApplication()->config))
		{
			$configPath = $input->getArgument("config");
			
			if (empty($configPath))
				throw new \Exception("You must specify configuratin file");
			
			if (!is_file($configPath))
				throw new \Exception("Configuration file: '{$configPath}' not found");
				
			$this->getApplication()->config = parse_ini_file($configPath); 
		}
	}		
}
