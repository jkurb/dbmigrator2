<?php
/**
 * Проверяет валидность репозитория миграций:
 *  - проверяет сущестование начальной миграции
 *  - проверяет соответсвие записей в базе в каталогам
 *  - проверят структуру каталогов
 *  - проверяет синтаксис запросов (выполнение в транзакции с откатом)
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Check extends BaseCommand
{
	protected function configure()
	{
		$this->setName("check")
			->setDescription("Check the repository migration.")
			->setHelp(sprintf('%sCheck the repository migration.%s', PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		throw new \Exception("Not implemented yet");
	}
}
