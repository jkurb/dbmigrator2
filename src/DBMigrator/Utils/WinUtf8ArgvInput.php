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

use Symfony\Component\Console\Input\ArgvInput;

class WinUtf8ArgvInput extends ArgvInput
{
	/**
	 * {@inheritdoc}
	 */
	public function getArgument($name)
	{
		$res = parent::getArgument($name);
		if (stristr(PHP_OS, "WIN"))
		{
			$res = iconv("windows-1251", "UTF-8", $res);
		}
		return $res;
	}
}
