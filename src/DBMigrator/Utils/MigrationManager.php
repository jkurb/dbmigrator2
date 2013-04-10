<?php
/**
 * Управление логикой миграций
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  MigrationManager.php 27.05.11 17:39 evkur
 * @link     nolink
 */

namespace DBMigrator\Utils;

use Exception;

class MigrationManager
{
	/**
	 * Инкапсулиреут в себе низкоуровневые операции с БД и файловой системой
	 *
	 * @var MigrationManagerHelper
	 */
	public $helper = null;

	function __construct($host, $user, $password, $dbname)
	{
		$this->helper = new MigrationManagerHelper($host, $user, $password, $dbname);
	}
	
	public function init($migrStorage)
	{
		// создаем таблицу
		$this->helper->createTable();

		// проверяем существование начальной миграции
		if ($this->getMigrationById(1))
			throw new \Exception("Can't apply init migration, because another exists");

		// строим миграцию
		$uid = $this->buildMigration($migrStorage, 'Init migration');

		// устанавливаем версию миграции
		self::setCurrentVersion($migrStorage, $uid);

	}

    /**
     * Добавляет миграцию в хранилище
     *
     * @param  $migrPath Директория, где хранится миграция для добаваления
     * @param  $migrStorage Директория, где хранятся миграции
     * @param string $comment Комментарий к миграции
     *
     * @throws Exception
     * @return void
     */
	public function commitMigration($migrPath, $migrStorage, $comment = '')
	{
		if (!$this->getMigrationById(1))
			throw new Exception("Need init migration");

		$this->helper->checkFile("{$migrPath}/delta.sql");

		$uid = $this->buildMigration($migrStorage, $comment);

		$path = "{$migrStorage}/{$uid}";

		//copy delta
		if (!copy("{$migrPath}/delta.sql", "{$path}/delta.sql"))
			throw new Exception("Can't copy {$migrPath}/delta.sql to {$path}/delta.sql");

		self::putInsertMigrationSql($uid, $comment, $path);

		FileSystem::delete($migrPath);

		self::setCurrentVersion($migrStorage, $uid);
	}

	/**
	 * @static
	 * @param $createTime
	 * @param $comment
	 * @param $path
	 * @return void
	 */
	public static function putInsertMigrationSql($createTime, $comment, $path)
	{
		$sql = "\nINSERT INTO __migration (createTime, comment) VALUES ({$createTime}, '{$comment}');\n";
		$file = file_get_contents("{$path}/delta.sql");
		file_put_contents("{$path}/delta.sql", str_replace("/*MIGRATION_INSERT_STATEMENT*/", $sql, $file));
	}

	/**
	 * Строит миграцию
	 *
	 * @param $migrStorage
	 * @param $comment
	 *
	 * @return mixed
	 */
	public function buildMigration($migrStorage, $comment)
	{
		$time = $this->getCurrentTime();
		$path = "{$migrStorage}/{$time}";

		$this->helper->checkDir($migrStorage, $path);

		// создаем запись в таблице
		if (!$this->getMigrationByTime($time))
		{
			sleep(1);
			$this->insertMigration($time, $comment);
		}

		// создаем начальный каталог c дампом базы
		$this->helper->createDump($path);

		return $time;
	}

	public function getCurrentTime()
	{
		return number_format(microtime(true), 4, '.', '');
	}

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
	 * Возвращает все миграции
	 *
	 * @param string $order Порядок сортировки
	 *
	 * @return Migration[]
	 */
	public function getAllMigrations($order = "ASC")
	{
		return $this->helper->enitityManager->getRepository("Migration")
            ->findAll(array(), array("createTime" => $order));
	}

	/**
	 * Возвращает последнюю миграцию
	 *
	 * @return Migration
	 */
	public function getLastMigration()
	{
        return $this->helper->enitityManager->getRepository("Migration")
            ->findOneBy(array(), array("createTime" => "DESC"));
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
        return $this->helper->enitityManager->getRepository("Migration")
            ->findOneBy(array("createTime" => $time), array("createTime" => "DESC"));
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
        return $this->helper->enitityManager->getRepository("Migration")->find($id);
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

	/**
	 * Создает файл delta.sql в указанной директории
	 *
	 * @throws Exception
	 * @param  $migrPath Имя директории, где создать миграцию
	 *
	 * @return void
	 */
	public static function createTempMigration($migrPath)
	{
		if (!@mkdir($migrPath, 0777, true))
			throw new Exception("Can't create {$migrPath}");

		MigrationManagerHelper::createEmptyDelta($migrPath);
	}

	public function insertMigration($createTime, $comment)
	{
        $m = new Migration();
        $m->createTime = $createTime;
        $m->comment = $comment;

        $this->helper->enitityManager->persist($m);
        $this->helper->enitityManager->flush();
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
		$this->helper->dropAndCreateTable();

		/* @var $m Migration */
		foreach ($migrations as $m)
		{
			$this->helper->enitityManager->persist($m);
		}
        $this->helper->enitityManager->flush();
	}

	public function gotoLastMigration($migrStorage)
	{
		 $migrationsUids = $this->getMigrationUidsByDirectories($migrStorage);
		 if (empty($migrationsUids))
			 throw new Exception("Can't found migrations");

		 $this->applyMigrationsByUids($migrStorage, $migrationsUids);
	}


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

	/**
	 * Проверяет валидность Миграций в базе
	 *
	 * @throws Exception
	 * @param  $migrations массив сущностей миграций
	 *
	 * @return void
	 */
	public function checkMigrations($migrations)
	{
		if (is_null($migrations) || empty($migrations))
			throw new Exception("Can't found migrations");

		if ($migrations[0]->id != 1)
			throw new Exception("Can't found initial migration (with id 1)");
	}

	/**
	 * Проверяет номер миграции
	 *
	 * @throws Exception
	 * @param  $uuid
	 *
	 * @return void
	 */
	public function checkMigration($uuid)
	{
		if (!$this->getMigrationByTime($uuid))
			throw new Exception("Migration {$uuid} not found");
	}

	public function getCurrentVersion()
	{
        return $this->helper->enitityManager->getRepository("Migration")
            ->findOneBy(array("isCurrent" => true))->id;
	}

	public function setCurrentVersion($id)
	{
        $m = $this->helper->enitityManager->getRepository("Migration")
            ->find($id);

        $m->isCurrent = true;

        $this->helper->enitityManager->persist($m);
        $this->helper->enitityManager->flush();
    }

	public function getDeltaByBinLog($binaryLogPath, $migrStorage, $unique = false)
	{
//		$currMigration = $this->getMigrationByTime($this->getCurrentVersion($migrStorage));
//
//		if (!$currMigration)
//			throw new Exception("Incorrect current migration");
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
	}

}
