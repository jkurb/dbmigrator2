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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use DBMigrator\Utils\ConsolePrinter;
use DBMigrator\Utils\TableHelper;

class Log extends BaseCommand
{
	protected function configure()
	{
		$this->setName("log")
			->setDescription("Show migration log.")
			->setHelp(sprintf('%sShow migration log.%s', PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$migrations = $this->migrator->getAllMigrations("DESC");

		$head = array("", "ID", "UID", "Create time", "Comment");
		$body = array();
		foreach ($migrations as $m)
		{
			$curr = $m->isCurrent ? "*" : "";
			$body[] = array($curr, $m->id, $m->createTime, date('Y-m-d H:i:s', $m->createTime), $m->comment);
		}

		$output->writeln("\n");
		ConsolePrinter::setChannel($output);
		ConsolePrinter::printT($body, $head);
		$output->writeln("\n");
	}
}
