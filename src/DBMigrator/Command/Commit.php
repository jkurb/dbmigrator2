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

		$migrPath = $this->getApplication()->config["migration"]["path"];
		//$migrStorage = $this->getApplication()->config["migration"]["path"];

		//var_dump(mb_detect_encoding($comment, mb_detect_order(), true));
		//$comment = iconv(mb_detect_encoding($comment, mb_detect_order(), true), "UTF-8", $comment);

		if (!$this->migrator->getMigrationById(1))
			throw new DBMigratorExeption("Need init migration");

		MigrationHelper::checkFile("{$migrPath}/delta.sql");


		$uid = $this->buildMigration($migrStorage, $comment);

		$path = "{$migrStorage}/{$uid}";

		//copy delta
		if (!copy("{$migrPath}/delta.sql", "{$path}/delta.sql"))
			throw new DBMigratorExeption("Can't copy {$migrPath}/delta.sql to {$path}/delta.sql");

		self::putInsertMigrationSql($uid, $comment, $path);

		FileSystem::delete($migrPath);

		self::setCurrentVersion($migrStorage, $uid);

	}

}
