<?php
/**
 * TODO: Добавить здесь комментарий
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Utils;

class DBToolFacrory
{
	const DRIVER_PDO_MYSQL = "pdo_mysql";

	const DRIVER_PDO_PGSQL = "pdo_pgsql";

	const DRIVER_PDO_MSSQL = "pdo_mssql";

	const DRIVER_PDO_SQLITE = "pdo_sqlite";

	public static function create($params)
	{
		switch ($params["driver"])
		{
			case self::DRIVER_PDO_MYSQL:
				return new MySQLTool(
					$params["host"],
					$params["dbname"],
					$params["user"],
					$params["password"]
				);
				break;
			default:
				throw new DBMigratorException("Invalid driver type");
		}

	}
}
