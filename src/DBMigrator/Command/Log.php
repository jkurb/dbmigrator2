<?php
/**
 * Показывает лог миграций
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Log extends Base
{
	protected function configure()
	{
		$this->setName("log")
			->setDescription("Show migration log.")
			->setHelp(sprintf('%sShow migration log.%s', PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
	}
}
