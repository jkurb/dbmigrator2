<?php
/**
 * Добавление миграции
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Commit extends Base
{
	protected function configure()
	{
		$this->setName("commit")
			->setDescription("Commit migration.")
			->setHelp(sprintf('%sCommit migration.%s', PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{		
	}
	
}
