<?php
/**
 * Показывает различие двух миграций, либо разницу (по бинарным логам) с последней миграции по времени
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Diff extends BaseCommand
{
	protected function configure()
	{
		$this->setName("diff")
			->setDescription("Show diff between two migraion or new changes from binary log since last migration.")
			->setHelp(
				sprintf('%sShow diff between two migraion or new changes from binary log since last migration.%s',
					PHP_EOL, PHP_EOL
				)
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
	}
}
