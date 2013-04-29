<?php
/**
 * Добавление новой миграции
 *
 * Имеется delta.sql
 *  1. Создаем каталог с названием меткой времени в хранилище с миграций
 *  2. Создаем в каталоге дамп БД  файлы 'scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql'
 *  3. Переносим туда delta.sql, подписанную запросом на вставку в таблицу миграций и декорированную доп директивами sql
 *  4. Добавляем запись в таблицу миграций
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use DBMigrator\Utils\DBMigratorException;
use DBMigrator\Utils\MigrationHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Commit extends BaseCommand
{
	protected function configure()
	{
		$this->setName("commit")
			->setDescription("Commit migration.")
			->addArgument("comment", InputArgument::REQUIRED, "Comment for migartion")
			->setHelp(sprintf('%sCommit migration.%s', PHP_EOL, PHP_EOL));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $comment = $input->getArgument("comment");
		$uid = MigrationHelper::getCurrentTime();

		$output->write("\n<comment>Creating new migraton {$uid}...</comment>");
		$this->migrator->enitityManager->beginTransaction();
		try
		{
			$this->migrator->exportDump($uid);
			$this->migrator->putDelta($uid, $comment);
			$this->migrator->insertMigration($uid, $comment);

			$this->migrator->enitityManager->commit();
		}
		catch (\Exception $e)
		{
			$this->migrator->enitityManager->rollback();
			throw DBMigratorException::create($e);
		}
		$output->writeln("<info>Done</info>\n");
	}

}
