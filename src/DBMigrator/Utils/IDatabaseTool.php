<?php
/**
 * Интерфейс для Database Tools
 *
 * PHP version 5
 *
 * @package
 * @author   Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Utils;

interface IDatabaseTool
{
	public function dumpDataBase($path);

	public function executeSQLFromFile($file);
}
