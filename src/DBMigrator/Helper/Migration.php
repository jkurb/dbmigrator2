<?php
/**
 * Класс представляет сущность миграции
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace Helper;

class Migration
{
	/**
	 * @var int Id миграции
	 */
	public $id = null;

	/**
	 * @var string Врем создания в unixtime
	 */
	public $createTime = null;

	/**
	 * @var string Комментарий
	 */
	public $comment = null;

	/**
	 * @var bool Являетмя ли миграция текущей
	 */
	public $isCurrent = false;
}
