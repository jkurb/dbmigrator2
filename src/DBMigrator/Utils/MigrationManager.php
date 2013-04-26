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

use DBMigratorExeption;

class MigrationManager
{
	/**
	 * @var Doctrine\ORM\EntityManager
	 */
	public $enitityManager;

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
		$this->enitityManager = EntityManager::create($config["db"], $configDB);

		$this->dbTool = DBToolFacrory::create($config["db"]);
	}

	/**
	 * Создает таблицу migration
	 *
	 * @return void
	 */
	public function createMigrationTable()
	{
		$conn = $this->enitityManager->getConnection();
		if (!$conn->getSchemaManager()->tablesExist(array($this->migrationTable)))
		{
			$st = new SchemaTool($this->enitityManager);

			$sqls = $st->getCreateSchemaSql(
				array($this->enitityManager->getClassMetadata(__NAMESPACE__ . "\\Migration"))
			);

			$this->enitityManager->getConnection()->executeQuery($sqls[0]);
		}
	}

	/**
	 * Очищает таблицу migration
	 *
	 * @return void
	 */
	public function emptyMigrationTable()
	{
		$this->enitityManager->getConnection()->getSchemaManager()->dropAndCreateTable($this->migrationTable);
	}

	/**
	 * Удаляет все из базы
	 *
	 * @return void
	 */
	public function emptyDatabase()
	{
		$this->enitityManager->getConnection()->getSchemaManager()->dropAndCreateDatabase($this->dbname);
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
	 * Возвращает последний Uid миграции из директории миграций
	 *
	 * @param $migrStorage
	 * @return mixed
	 */
	public function getLastMigrationUidFromDiretories($migrStorage)
	{
		$migrationsUids = $this->getMigrationUidsByDirectories($migrStorage);
		return array_shift($migrationsUids);
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
		$m = $this->getRepository()->findOneBy(array("createTime" => $uid));


		$m->isCurrent = true;

		$this->enitityManager->persist($m);
		$this->enitityManager->flush();
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getRepository()
	{
		return $this->enitityManager->getRepository(__NAMESPACE__ . "\\Migration");
	}

	public function executeQuery($sql)
	{
		$this->enitityManager->getConnection()->executeQuery($sql);
	}

	public function insertMigration($createTime, $comment)
	{
		$m = new Migration();
		$m->createTime = $createTime;
		$m->comment = $comment;

		$this->enitityManager->persist($m);
		$this->enitityManager->flush();
	}

	/**
	 * Восстанавливает записи в таблице миграций
	 *
	 * @param  $migrations
	 *
	 * @return void
	 */
	public function restoreMigrations($migrations)
	{
		$this->emptyMigrationTable();

		/* @var $m Migration */
		foreach ($migrations as $m)
		{
			$this->enitityManager->persist($m);
		}
		$this->enitityManager->flush();
	}

	/**
	 * Выполняет набор sql файлов из директории
	 *
	 * @param  $dir     Директория с файлами миграции
	 * @param  $fileSet Набор имен файлов для выполнения
	 *
	 * @throws Exception
	 * @return void
	 */
	public function importFiles($dir, $fileSet)
	{
		foreach ($fileSet as $fileName)
		{
			$path = "{$dir}/{$fileName}";
			if (!is_readable($path))
				throw new Exception("Can't read {$path}");

			$this->dbTool->executeSQLFromFile($path);
		}
	}

	public function putDelta($uid, $comment)
	{
		$sql = "\nINSERT INTO __migration (createTime, comment) VALUES ({$uid}, '{$comment}');\n";

		$content = FileSystem::getFile("{$this->migrationPath}/delta.sql");
		$content .= $sql;

		FileSystem::putFile("{$this->migrationPath}/{$uid}/delta.sql", MigrationHelper::decorateDelta($content));
		FileSystem::delete("{$this->migrationPath}/delta.sql");
	}

	public function createDump($uid)
	{
		$fullPath = "{$this->migrationPath}/{$uid}";

		if (!mkdir($fullPath, 0777))
			throw new DBMigratorException("Can't create {$fullPath}");

		$this->dbTool->dumpDataBase($fullPath);
	}





	//todo: command
	/**
	 * Накатывает миграции от 0 до $number из хранилища,
	 * если установлен force, то накатывает только $number
	 *
	 * @param string $migrStorage Директория, где хранятся миграции
	 * @param string $uid Уникальный идентификатор миграции
	 * @param bool $force Флаг
	 *
	 *
	 * @internal param $uuid Номер миграции
	 * @return void
	 */
	public function gotoMigration($migrStorage, $uid, $force = false)
	{
        $fileSet = array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql');

		$migrations = $this->getAllMigrations();

		$this->checkMigrations($migrations);
		$this->checkMigration($uid, $migrations);

		$this->helper->makeDBEmpty();

		if ($force)
		{

            $this->helper->importFiles("{$migrStorage}/{$uid}", $fileSet);
		}
		else
		{
			/**
			 * @var $m Migration
			 */
			foreach ($migrations as $m)
			{
				if ($m->id == 1)
				{
					$this->helper->importFiles("{$migrStorage}/{$m->createTime}", $fileSet);
				}
				else
				{
					$this->helper->importFiles("{$migrStorage}/{$m->createTime}", array('delta.sql'));
				}

				if ($m->createTime == $uid)
				{
					break;
				}
			}
		}
		$this->restoreMigrations($migrations);
		self::setCurrentVersion($migrStorage, $uid);
	}


   /**
	* Возврщает номер последней папки
	*
	* @param $migrStorage
	*
	* @return mixed
	*/
	private function getMigrationUidsByDirectories($migrStorage)
	{
		$pattern = "/^\d{10}\.\d{4}$/is";
		return FileSystem::fileList($migrStorage, $pattern, true);
	}


	//todo: to command
	public function gotoLastMigration($migrStorage)
	{
		 $migrationsUids = $this->getMigrationUidsByDirectories($migrStorage);
		 if (empty($migrationsUids))
			 throw new DBMigratorExeption("Can't found migrations");

		 $this->applyMigrationsByUids($migrStorage, $migrationsUids);
	}


	//todo: to command
	private function applyMigrationsByUids($migrStorage, $migrationsUids)
	{
		$this->helper->makeDBEmpty();

		// apply init migration
		$_migrationsUids = $migrationsUids;
		$uid = array_shift($_migrationsUids);
		$this->helper->importFiles("{$migrStorage}/{$uid}",
				array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql'));

		foreach ($_migrationsUids as $uid)
		{
			$this->helper->importFiles("{$migrStorage}/{$uid}", array('delta.sql'));
		}

		self::setCurrentVersion($migrStorage, end($migrationsUids));
	}

//	/**
//	 * Проверяет валидность Миграций в базе
//	 *
//	 * @throws DBMigratorExeption
//	 * @param  $migrations массив сущностей миграций
//	 *
//	 * @return void
//	 */
//	public function checkMigrations($migrations)
//	{
//		if (is_null($migrations) || empty($migrations))
//			throw new DBMigratorExeption("Can't found migrations");
//
//		if ($migrations[0]->id != 1)
//			throw new DBMigratorExeption("Can't found initial migration (with id 1)");
//	}
//
//	/**
//	 * Проверяет номер миграции
//	 *
//	 * @throws DBMigratorExeption
//	 * @param  $uuid
//	 *
//	 * @return void
//	 */
//	public function checkMigration($uuid)
//	{
//		if (!$this->getMigrationByTime($uuid))
//			throw new DBMigratorExeption("Migration {$uuid} not found");
//	}

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
