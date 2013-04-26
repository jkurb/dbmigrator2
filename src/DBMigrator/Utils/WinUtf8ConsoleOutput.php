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

use Symfony\Component\Console\Output\ConsoleOutput;

class WinUtf8ConsoleOutput extends ConsoleOutput
{
	/**
	 * {@inheritdoc}
	 */
	public function doWrite($message, $newline)
	{
		if (stristr(PHP_OS, "WIN"))
		{
			$message = iconv("UTF-8", "CP866", $message);
		}
		parent::doWrite($message, $newline);
	}
}
