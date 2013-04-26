<?php
/**
 * Исключение мигратора
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Utils;

class DBMigratorException extends \Exception
{
	public static function create(\Exception $e)
	{
		$dbe = new DBMigratorException();
		$dbe->code = $e->code;
		$dbe->file = $e->file;
		$dbe->line = $e->line;
		$dbe->message = $e->message;

		return $dbe;
	}
}
