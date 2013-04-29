<?php
namespace DBMigrator\Command;

use DBMigrator\Utils\DBMigratorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use DBMigrator\Utils\MigrationManager;

/**
 * @method DBMigrator\DBMigratorApp getApplication()
 */
abstract class BaseCommand extends Command
{
    const DEFAULT_CONFIG_NAME = "dbmigrator.yml";

    /**
     * Конфиг
     *
     * @var array
     */
    public $config = null;

    /**
     * @var MigrationManager
     */
    public $migrator = null;

    /**
     * @var null
     */
    public $path = null;

    public function init($path = self::DEFAULT_CONFIG_NAME)
    {
        if (!is_file($path))
        {
            throw new DBMigratorException(sprintf("Configuration file: '%s' not found", $path));
        }

        $this->config = Yaml::parse($path);
        $this->migrator = new MigrationManager($this->config);
    }


    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$this->config)
        {
            $this->init();
        }
    }

}
