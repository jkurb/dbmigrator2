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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class Init extends Base
{
	protected function configure()
	{
		$this->setName("init")
			->setDescription("Initializes migration repository and creates migration table")
			->setDefinition(array(
                 new InputArgument("path", InputArgument::OPTIONAL, "Path to migration repository"),                                  
                 new InputArgument("table", InputArgument::OPTIONAL, "Migration table name")                                  
            ))
			->setHelp(sprintf("%Initializes migration repository and creates migration table.%s", PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$result = $input->getArgument('x') + $input->getArgument('y');		
		$output->writeln($result);
	}
}
