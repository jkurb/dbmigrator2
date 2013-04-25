<?php
/**
 * Создание пустой миграции
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use DBMigrator\Utils\FileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends BaseCommand
{
	protected function configure()
	{
		$this->setName("create")
			->setDescription("Create empty migration.")
			->setHelp(sprintf('%sCreate empty migration.%s', PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $this->getApplication()->config["migration"]["path"];

		if (!is_writable($path))
			throw new \Exception("Path '{$path}' is not writable");

		FileSystem::putFile("{$path}/delta.sql", null);

		$output->writeln("\n<info>Empty 'delta.sql' has created</info>\n");
	}
}
