<?php

namespace DBMigrator\Command;
    
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class Test extends Base 
{
	protected function configure()
	{		
		$this->setName('calc:add')
			->setDescription('Calculates the sum of two numbers.')
			->setDefinition(array(
                 new InputArgument('x', InputArgument::REQUIRED, 'First addend'),
                 new InputArgument('y', InputArgument::REQUIRED, 'Second addend'),
                 new InputOption('round', null, null, 'Round result')
            ))
			->setHelp(sprintf('%sCalculates the sum of two numbers.%s', PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$result = $input->getArgument('x') + $input->getArgument('y');
		if ($input->getOption('round'))
		{
			$result = round($result);
		}

		$output->writeln($result);
	}	    

}
