<?php
/**
 * TODO: Добавить здесь комментарий
 *
 * PHP version 5
 *
 * @package
 * @author  Eugene Kurbatov <ekur@i-loto.ru>
 */

namespace DBMigratorTest\Command;


class StatusTest extends BaseCommandTest
{
	public function testExecute()
	{
		$this->commandTester->execute(array("command" => $this->command->getName()));
		$this->assertRegExp("/Current migration is/", $this->commandTester->getDisplay());
	}
}
