<?php
/**
 * Класс представляет сущность миграции
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigrator\Utils;

/**
 * @Entity
 * @Table(name="__migration")
 */
class Migration
{
    /**
     * Id миграции
     *
     * @Id @Column(type="integer", length=10)
     * @GeneratedValue
     */
	public $id = null;

	/**
	 * Время создания в unixtime
     *
     * @Column(type="decimal", scale=4, precision=14)
	 */
	public $createTime = null;

	/**
	 * Комментарий
     *
     * @Column(type="string", length=255)
	 */
	public $comment = null;

	/**
	 * Является ли миграция текущей
     *
     * @Column(type="boolean")
	 */
	public $isCurrent = false;
}
