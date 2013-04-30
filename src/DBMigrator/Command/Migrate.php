<?php
/**
 * Выполнение миграции
 * Usage:
 *
 * migrate 1366709075.1069:1366972554.9822 - миграция с версии V1 к версии V2
 * migrate :1366972554.9822 - миграция до версии V2
 * migrate 1366972554.9822: - миграция с версии V1 к конечной
 * migrate - миграция с текущей к конечной
 * migrate --dry-run - вывод sql запросов, без выполнения
 * migrate --force 1366972554.9822 - выполнить дамп миграции
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Command;

use DBMigrator\Utils\DBMigratorException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use DBMigrator\Utils\MigrationHelper;

class Migrate extends BaseCommand
{
	private $from = null;

	private $to = null;

	private $force = false;

	private $dryRun = false;

	protected function configure()
	{
		$this->setName("migrate")
			->setDescription("Apply migraion.")
			->setHelp(sprintf('%sApply migraion by name or index.%s', PHP_EOL, PHP_EOL))
			->setDefinition(array(
				new InputArgument("from:to", InputArgument::OPTIONAL, "Version or migration index range"),
			    new InputOption("force", "f", InputOption::VALUE_NONE, "Migrate from backup"),
			    new InputOption("dry-run", null, InputOption::VALUE_NONE, "Outputs the operations but will not execute anything")
            ));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->prepareArguments($input);

		$migrations = $this->migrator->getAllMigrations();
		$this->migrator->emptyDatabase();
		$path = $this->config["migration"]["path"];

		if ($this->force)
		{
			$this->migrator->importDump("{$path}/{$this->to}",
				array("scheme.sql", "data.sql", "procedures.sql", "triggers.sql")
			);
		}
		else
		{
			foreach ($migrations as $m)
			{
				if ($m->id == 1)
				{
					$this->migrator->importDump("{$path}/{$m->createTime}",
						array("scheme.sql", "data.sql", "procedures.sql", "triggers.sql"));
				}
				else
				{
					$this->migrator->importDump("{$path}/{$m->createTime}", array("delta.sql"));
				}

				if ($m->createTime == $this->to)
				{
					break;
				}
			}
		}
		$this->migrator->setCurrentVersion($this->to);
	}

	private function prepareArguments(InputInterface $input)
	{
		$fromTo = $input->getArgument("from:to");
        $this->dryRun = $input->getOption("dry-run");
        $this->force = $input->getOption("force");

        if ($this->force)
        {
            if (!$this->isVersion($fromTo))
            {
                throw new DBMigratorException("Invalid migration version 'to'");
            }

            $this->to = $fromTo;
        }
        else if (!empty($fromTo))
        {
            list($from, $to) = explode(":", $fromTo);
            $from = (empty($from)) ? null : $from;
            $to = (empty($to)) ? null : $to;

            if ($from != null && !$this->isVersion($from))
            {
                throw new \Exception("Invalid migration version 'from'");
            }

            if ($to != null && !$this->isVersion($to))
            {
                throw new \Exception("Invalid migration version 'to'");
            }

            $this->from = $from;
            $this->to = $to;
        }
	}

	private function isVersion($val)
	{
		return preg_match("/^[0-9]+\.[0-9]+$/", $val) == 1;
	}
}
