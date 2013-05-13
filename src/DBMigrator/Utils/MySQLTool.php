<?php
/**
 * Класс для работы с MySQL Tools
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Utils;

class MySQLTool implements IDatabaseTool
{
	private $host = null;
	private $user = null;
	private $password = null;
	private $dbname = null;
	private $encoding = "utf8";

	public function __construct($host, $dbname, $user, $password)
	{
		$this->host = $host;
		$this->dbname = $dbname;
		$this->user = $user;
		$this->password = $password;
	}

	/**
	 * Генерация схемы
	 *
	 * @param string $path Директория, где будет сохранен дамп
	 *
	 * @throws DBMigratorException
	 * @return void
	 */
	public function dumpScheme($path)
	{
		$retVal = null;
		$output = null;
		exec("mysqldump --host={$this->host} --password={$this->password} -u {$this->user}"
			. " --dump-date=false --skip-triggers --no-autocommit"
		    . " --disable-keys --add-drop-table --set-charset"
		    . " --default-character-set={$this->encoding}"
		    . " --no-data {$this->dbname}"
		    . " --skip-comments 2>&1", $output, $retVal
		);

		if ($retVal !== 0)
		{
			throw new DBMigratorException("Error: " . print_r($output, 1));
		}

		// склеить возвращенную строку с SQL-кодом
		$schemeData = implode("\n", $output);

		// нужно удалить строки типа AUTO_INCREMENT=123
		$schemeData = preg_replace('/[\s]?(AUTO_INCREMENT=[\d]+)[\s]?/si', ' ', $schemeData);

		// нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/
		$schemeData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $schemeData);

		FileSystem::putFile("{$path}/scheme.sql", $schemeData);
	}

	/**
	 * Генерация данных
	 *
	 * @param string $path Директория, где будет сохранен дамп
	 *
	 * @throws DBMigratorException
	 * @return void
	 */
	public function dumpData($path)
	{
		$retVal = null;
		$output = null;
		system("mysqldump --host={$this->host} --password={$this->password} -u {$this->user}"
			. " --dump-date=false --skip-triggers --no-autocommit --disable-keys"
			. " --set-charset --default-character-set={$this->encoding} --no-create-info"
			. " --extended-insert=false  --result-file={$path}/data.sql {$this->dbname} --skip-comments 2>&1", $retVal
		);

		if ($retVal !== 0)
		{
			throw new DBMigratorException("Error: " . print_r($output, 1));
		}
	}

	/**
	 * Генерация триггеров
	 *
	 * @param string $path Директория, где будет сохранен дамп
	 *
	 * @throws DBMigratorException
	 * @return void
	 */
	public function dumpTriggers($path)
	{
		$output = null;
		$retVal = null;
		exec("mysqldump --host={$this->host} --password={$this->password} -u {$this->user}"
			. " --dump-date=false --disable-keys  --default-character-set={$this->encoding}"
			. " --no-create-info --no-data --extended-insert=false --triggers=true {$this->dbname}"
			. " --skip-comments 2>&1 ", $output, $retVal
		);

		if ($retVal !== 0)
		{
			throw new DBMigratorException("Error: " . print_r($output, 1));
		}

		// склеить возвращенную строку с SQL-кодом
		$triggersData = implode("\n", $output);

		// нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/
		$triggersData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $triggersData);
		FileSystem::putFile("{$path}/triggers.sql", $triggersData);
	}

	/**
	 * Генерация хранимых процедур
	 *
	 * @param string $path Директория, где будет сохранен дамп
	 *
	 * @throws DBMigratorException
	 * @return void
	 */
	public function dumpProcedures($path)
	{
		$output = null;
		$retVal = null;
		exec("mysqldump --host={$this->host} --password={$this->password} -u {$this->user} --dump-date=false"
			. " --routines --default-character-set={$this->encoding} --no-create-info --no-data"
			. " --extended-insert=false --triggers=false {$this->dbname} --skip-comments 2>&1", $output, $retVal
		);
		if ($retVal !== 0)
		{
			throw new DBMigratorException("Error: " . print_r($output, 1));
		}

		// склеить возвращенную строку с SQL-кодом
		$spData = implode("\n", $output);

		// нужно удалить строки типа /*!50017 DEFINER=`root`@`localhost`*/
		$spData = preg_replace('%/\*![\d]+\sDEFINER.*?\*/%si', '', $spData);
		FileSystem::putFile("{$path}/procedures.sql", $spData);
	}

	public function dumpDataBase($path)
	{
		$this->dumpScheme($path);
		$this->dumpData($path);
		$this->dumpTriggers($path);
		$this->dumpProcedures($path);
	}

	/**
	 * Загружает файл с SQL кодом напрямую в БД
	 *
	 * @param string $file путь к файлу с SQL кодом
	 *
	 * @throws DBMigratorException
	 */
	public function executeSQLFromFile($file)
	{
		$retVal = null;
		$output = null;
		exec("mysql --host={$this->host} --password={$this->password} -u {$this->user} {$this->dbname} < {$file} 2>&1",
			$output, $retVal
		);

		if ($retVal !== 0)
		{
			throw new DBMigratorException("File: {$file}\n" . $this->parseConsoleError($output));
		}
	}

	/**
	 * Создает файл дельты из бинарных логов
	 *
	 * @param      $logs
	 * @param      $logPath   Путь к бинарным логам MySQL
	 * @param      $startTime Время, с которого начинать искать дельту
	 * @param      $dbname
	 * @param bool $unique
	 *
	 * @return string
	 */
	public function getDeltaByBinLog($logs, $logPath, $startTime, $dbname, $unique = false)
	{
		$sLogs = "";

		foreach ($logs as $log)
		{
			$sLogs .= $logPath . DIRECTORY_SEPARATOR . $log['Log_name'] . " ";
		}

		$command = "mysqlbinlog -s -d {$dbname} --start-datetime=\"{$startTime}\" -t {$sLogs}" . "\n";
		exec($command, $q);
		$out = implode("\n", $q);

		preg_match("/DELIMITER\s(.*?)\n/is", $out, $result);

		$delimeter = $result[1];
		$queries = explode($delimeter, $out);
		$queries = self::filterQueries($queries);

		if ($unique)
		{
			$queries = self::getUniqueQueries($queries);
		}

		$strQueries = implode($delimeter . PHP_EOL, $queries);

		return $strQueries;
	}

	private function getUniqueQueries($queries)
	{
		$qArray  = array();
		foreach ($queries as $q)
		{
			$qArray[] = $q;
		}

		$patters = array(
			"/\t+/is" => " ",
			"/\n+/is" => " ",
			"/\s+/is" => " "
		);
		$res = preg_replace(array_keys($patters), array_values($patters), $qArray);
		$res = array_map('strtolower', $res);
		$res = array_unique($res);

		$uniqueQueries = array();
		foreach ($res as $k => $v)
		{
			$uniqueQueries[] = $qArray[$k];
		}

		return $uniqueQueries;
	}

	private function filterQueries($queries)
	{
		$patters = array(
			"{/\*!.+?\*/;}is"    => "", // директивы вида /*!40019 SET ....*/;
			"/^\s*SET.*/is"      => "", // запросы SET ....*/;
			"/^\s*use.*/is"      => "", // запросы use...
			"{/\*!\\\C.+?\*/}is" => "", // запросы /*!\C utf8 */
			"/^\n*/is"           => "", // пустые строки в начале запроса
			"/\n*$/is"           => ""  // пустые строки в конце запроса
		);
		$res = preg_replace(array_keys($patters), array_values($patters), $queries);
		$res = array_filter($res);

		return $res;
	}

	/**
	 * Преобразует список сообщений в строку
	 *
	 * @param array $array список сообщений
	 *
	 * @return string
	 */
	private function parseConsoleError($array)
	{
		return implode(PHP_EOL, $array);
	}
}
