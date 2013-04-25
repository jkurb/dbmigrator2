<?php
namespace DBMigrator\Utils;

use Symfony\Component\Console\Output\OutputInterface;

class ConsolePrinter
{

	/** @var OutputInterface */
	static private $channel = null;

	/**
	 * public static function printT($body, $head = null)
	 *
	 * This function performs the full print of the table
	 *
	 * @author      Antoine Durieux
	 *
	 * @param      $body
	 * @param null $head
	 *
	 * @return      string The resulting string
	 */
	public static function printT($body, $head = null)
	{
		// ---------------------------------------------------------------------
		// 1. Find column widths to use
		// ---------------------------------------------------------------------
		$columnWidths = self::computeColumnWidths($body,$head);

		// ---------------------------------------------------------------------
		// 2. Print header
		// ---------------------------------------------------------------------
		if($head !== null)
		{
			self::printConsole(self::printBlankLine($columnWidths));
			self::printConsole(self::printLine($head,$columnWidths));
		}

		// ---------------------------------------------------------------------
		// 3. Print body
		// ---------------------------------------------------------------------
		self::printConsole(self::printBlankLine($columnWidths));
		foreach($body as $index => $row)
		{
			self::printConsole(self::printLine($row,$columnWidths));
		}
		self::printConsole(self::printBlankLine($columnWidths));
	}

	/**
	 * private static function computeColumnWidths($body,$head = null)
	 *
	 * This function computes the sizes of the columns depending on the size of
	 * what they will contain.
	 *
	 * @author      Antoine Durieux
	 *
	 * @version     1.0
	 *
	 * @param       array       $body           The body of the table
	 * @param       array       $head           The header of the table
	 * @return      array                       The maximum size of a columnt
	 */
	private static function computeColumnWidths($body,$head = null)
	{
		$columnWidths = array();
		// In case we have no head
		if($head!==null)
		{
			$columnWidths = array_map('mb_strlen',$head);
		}
		else
		{
			$columnWidths = array_map('mb_strlen',$body[key($body)]);
		}
		foreach($body as $index => $row)
		{
			foreach($row as $jndex => $value)
			{
				$columnWidths[$jndex] = max(mb_strlen($value),$columnWidths[$jndex]);
			}
		}
		return $columnWidths;
	}

	/**
	 * private static function printBlankLine($columnWidths)
	 *
	 * This function returns a string that corresponds to a decorating line.
	 *
	 * @author      Antoine Durieux
	 *
	 * @version     1.0
	 *
	 * @param       array       $columnWidths   The widths of the columns
	 * @return      string                      The resulting string
	 */
	private static function printBlankLine($columnWidths)
	{
		$line = '+';
		foreach($columnWidths as $index => $value)
		{
			$line .= str_repeat('-',$columnWidths[$index]+2).'+';
		}
		return $line;
	}

	/**
	 * private static function printLine($line,$columnWidths)
	 *
	 * This function returns a string that corresponds to a regular line of the
	 * table.
	 *
	 * @author      Antoine Durieux
	 *
	 * @version     1.0
	 *
	 * @param       array       $line           The line to be printed
	 * @param       array       $columnWidths   The widths of the columns
	 * @return      string                      The resulting string
	 */
	private static function printLine($line,$columnWidths)
	{
		$string = '|';
		foreach($line as $index => $value)
		{
			$string .= ' '.$value.str_repeat(' ',$columnWidths[$index]-mb_strlen($value)).' |';
		}
		return $string;
	}

	/**
	 * public static function printConsole($string)
	 *
	 * This function prints a line in the console
	 *
	 * @author      Antoine Durieux
	 *
	 * @version     1.0
	 *
	 * @param       string       $string           The string to print in the console
	 */
	public static function printConsole($string)
	{
		self::$channel->writeln($string);
	}

	// =========================================================================
	//                                   SETTERS
	// =========================================================================

	/**
	 * public static function setChannel(OutputInterface $channel)
	 *
	 * This function sets the outputting channel.
	 *
	 * @author      Antoine Durieux
	 *
	 * @version     1.0
	 *
	 * @param OutputInterface $channel
	 */
	public static function setChannel(OutputInterface $channel)
	{
		self::$channel = $channel;
	}

}
