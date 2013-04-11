<?php
/**
 * Инкапсулиреут в себе низкоуровневые операции с БД и файловой системой
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  MigrationManagerHelper.php 27.05.11 17:40 evkur
 * @link     nolink
 */

namespace DBMigrator\Utils;


class MigrationHelper
{
	public static $sqlFileSet = array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql', 'delta.sql');

	/**
	 * Проверка директорий перед записью
	 *
	 * @throws Exception
	 * @param  $migrStorage Путь, где хранятся миграции
	 * @param  $pathToCreate Путь к директории, которую необходимо создать
	 *
	 * @return void
	 */
	public static function checkDir($migrStorage, $pathToCreate)
	{
		if (!is_dir($migrStorage))
			throw new Exception("Directory '{$migrStorage}' does not exist");

		if (!is_writable($migrStorage))
			throw new Exception("Directory '{$migrStorage}' is not writable");

		if (is_dir($pathToCreate))
			throw new Exception("Directory {$pathToCreate} already exist");
	}

	public static function checkFile($path)
	{
		$hashEmptyDelta = "1b05bbfbef36037f33011dbddedc5d34";

		if (!file_exists($path) || !is_readable($path))
			throw new Exception("Not found temp delta in {$path}. You should use 'create'");

		if ($hashEmptyDelta === md5_file($path))
			throw new Exception("Put your code in {$path}");
	}

	/**
	 * Удаляет директоррии с миграцией
	 *
	 * @static
	 * @param  $path Путь до миграции
	 *
	 * @return void
	 */
	public static function cleanMigrDir($path)
	{
		foreach (self::$sqlFileSet as $fileName)
		{
			@unlink("{$path}/{$fileName}");
		}
		@rmdir("{$path}");
	}

	public static function createEmptyDelta($path)
	{
		file_put_contents("{$path}/delta.sql", self::getDeltaTemplate());
	}

	public static function getDeltaTemplate()
	{
		$str = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
		$str .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
		$str .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
		$str .= "/*!40101 SET NAMES utf8 */;\n";
		$str .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n";
		$str .= "/*!40103 SET TIME_ZONE='+00:00' */;\n";
		$str .= "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n";
		$str .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n";
		$str .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
		$str .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n";
		$str .= "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n";
		$str .= "\n/*YOU CODE HERE*/\n\n";
		$str .= "/*MIGRATION_INSERT_STATEMENT*/\n";
		$str .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n";
		$str .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n";
		$str .= "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n";
		$str .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
		$str .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
		$str .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
		$str .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n";

		return $str;
	}

	public static function getCurrentTime()
	{
		return number_format(microtime(true), 4, '.', '');
	}
}
