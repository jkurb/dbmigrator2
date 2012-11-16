<?php
/**
 * Показывает текущую версию
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends Base
{
	protected function configure()
	{
		$this->setName("status")
			->setDescription("Show migration status.")
			->setHelp(sprintf('%sShow migration status.%s', PHP_EOL, PHP_EOL)
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
	}
}
