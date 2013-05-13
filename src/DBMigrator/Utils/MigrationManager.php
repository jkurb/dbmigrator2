<?php
/**
 * Менеджер миграций: CRUD, операции с базой
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  MigrationManager.php 27.05.11 17:39 evkur
 * @link     nolink
 */

namespace DBMigrator\Utils;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\Logging\EchoSQLLogger;


class MigrationManager
{
	/**
	 * @var EntityManager
	 */
	public $entityManager;

	public $dbTool = null;

	private $host = null;
	private $user = null;
	private $password = null;
	private $dbname = null;
	private $migrationTable = null;
	private $migrationPath = null;


	/**
	 * Устанавливает соединение с базой
	 *
	 * @param $config
	 */
	public function __construct($config)
	{
		$this->host = $config["db"]["host"];
		$this->user = $config["db"]["user"];
		$this->password = $config["db"]["password"];
		$this->dbname = $config["db"]["dbname"];

		$this->migrationTable = $config["migration"]["table"];
		$this->migrationPath = $config["migration"]["path"];

		$configDB = Setup::createAnnotationMetadataConfiguration(array("Migration.php"), true);
		$this->entityManager = EntityManager::create($config["db"], $configDB);

		//$this->entityManager->getConfiguration()->setSQLLogger(new EchoSQLLogger());

		$this->dbTool = DBToolFacrory::create($config["db"]);
	}

	/**
	 * Создает таблицу migration
	 *
	 * @return void
	 */
	public function createMigrationTable()
	{
		$conn = $this->entityManager->getConnection();
		if (!$conn->getSchemaManager()->tablesExist(array($this->migrationTable)))
		{
			$st = new SchemaTool($this->entityManager);

			$sqls = $st->getCreateSchemaSql(
				array($this->entityManager->getClassMetadata(__NAMESPACE__ . "\\Migration"))
			);

			$this->entityManager->getConnection()->executeQuery($sqls[0]);
		}
	}

	/**
	 * Очищает таблицу migration
	 *
	 * @return void
	 */
	public function emptyMigrationTable()
	{
		$this->entityManager->getConnection()->getSchemaManager()->dropAndCreateTable($this->migrationTable);
	}

	/**
	 * Удаляет все из базы
	 *
	 * @return void
	 */
	public function emptyDatabase()
	{
		$this->entityManager->getConnection()->getSchemaManager()->dropAndCreateDatabase($this->dbname);
	}

	/**
	 * Возвращает все миграции
	 *
	 * @param string $order Порядок сортировки
	 *
	 * @return Migration[]
	 */
	public function getAllMigrations($order = "ASC")
	{
		return $this->getRepository()->findBy(array(), array("createTime" => $order));
	}

	/**
	 * Возвращает последнюю миграцию
	 *
	 * @return Migration
	 */
	public function getLastMigration()
	{
		return $this->getRepository()->findOneBy(array(), array("createTime" => "DESC"));
	}

	/**
	 * Возвращает миграцию по времени создания
	 *
	 * @param $time
	 *
	 * @return Migration
	 */
	public function getMigrationByTime($time)
	{
		return $this->getRepository()->findOneBy(array("createTime" => $time), array("createTime" => "DESC"));
	}

	/**
	 * Возвращает миграцию id
	 *
	 * @param $id
	 *
	 * @return Migration
	 */
	public function getMigrationById($id)
	{
		return $this->getRepository()->find($id);
	}

	public function getCurrentVersion()
	{
		return $this->getRepository()->findOneBy(array("isCurrent" => true))->createTime;
	}

	public function setCurrentVersion($uid)
	{
		$m = $this->getRepository()->findOneBy(array("createTime" => floatval($uid)));
		$m->isCurrent = true;

		$this->entityManager->persist($m);
		$this->entityManager->flush();
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getRepository()
	{
		return $this->entityManager->getRepository(__NAMESPACE__ . "\\Migration");
	}

	public function executeQuery($sql)
	{
		$this->entityManager->getConnection()->executeQuery($sql);
	}

	public function insertMigration($createTime, $comment)
	{
		$m = new Migration();
		$m->createTime = floatval($createTime);
		$m->comment = $comment;

		$this->entityManager->persist($m);
		$this->entityManager->flush();
	}

	/**
	 * Выполняет набор sql файлов из директории
	 *
	 * @param  $dir     Директория с файлами миграции
	 * @param  $fileSet Набор имен файлов для выполнения
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function importDump($dir, $fileSet)
	{
		foreach ($fileSet as $fileName)
		{
			$path = "{$dir}/{$fileName}";
			if (!is_readable($path))
				throw new \Exception("Can't read {$path}");

			$this->dbTool->executeSQLFromFile($path);
		}
	}

    public function exportDump($uid)
    {
        $fullPath = "{$this->migrationPath}/{$uid}";

        if (!mkdir($fullPath, 0777))
            throw new DBMigratorException("Can't create {$fullPath}");

        $this->dbTool->dumpDataBase($fullPath);
    }


	public function putDelta($uid, $comment)
	{
		$sql = "\nINSERT INTO {$this->migrationTable} (createTime, comment) VALUES ({$uid}, '{$comment}');\n";

		$content = FileSystem::getFile("{$this->migrationPath}/delta.sql");
		$content .= $sql;

		FileSystem::putFile("{$this->migrationPath}/{$uid}/delta.sql", MigrationHelper::decorateDelta($content));
		FileSystem::delete("{$this->migrationPath}/delta.sql");
	}



//	public function getDeltaByBinLog($binaryLogPath, $migrStorage, $unique = false)
//	{
//		$currMigration = $this->getMigrationByTime($this->getCurrentVersion($migrStorage));
//
//		if (!$currMigration)
//			throw new DBMigratorExeption("Incorrect current migration");
//
//		$r = $this->helper->executeQuery("SELECT NOW()");
//		$endTime = mysql_result($r, 0);
//
//		$res = $this->helper->executeQuery("SELECT FROM_UNIXTIME({$currMigration->createTime})");
//		$startTime = mysql_result($res, 0);
//
//		$queries = $this->helper->getDeltaByBinLog($binaryLogPath, $startTime, $unique);
//
//		echo "# Delta from {$startTime} to {$endTime}";
//		if ($unique)
//			echo " (Unique)";
//
//		echo "\n\n";
//
//		return $queries . "\n";
//	}

}
