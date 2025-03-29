<?php

/**
 * Modern PHP task runner for Joomla! Browser Automated Tests execution.
 *
 * Facilitates building, testing, and maintaining Joomla extensions with automated tools.
 *
 * @package     Joomla.Site
 * @subpackage  RoboFile
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Jorobo\Tasks\Tasks as LoadReleaseTasks;
use Robo\Tasks;

require_once 'vendor/autoload.php';

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', __DIR__);
}

/**
 * RoboFile class for managing Joomla tasks.
 *
 * This class defines tasks for building, mapping, and running code analysis tools.
 *
 * @since    1.0
 */
class RoboFile extends Tasks
{
	// Load release tasks from composer
	use LoadReleaseTasks;

	/**
	 * Constructor - Sets the default timezone to UTC.
	 */
	public function __construct()
	{

		date_default_timezone_set('UTC');
	}

	/**
	 * Build the Joomla extension package.
	 *
	 * @param   array  $params  Additional parameters, defaults to ['dev' => false].
	 *
	 * @return  void
	 */
	public function build($params = ['dev' => false])
	{
		// Copy default configuration if jorobo.ini is not available
		if (!file_exists('jorobo.ini'))
		{
			$this->_copy('jorobo.dist.ini', 'jorobo.ini');
		}

		// Run the build task with specified params
		$this->task(\Joomla\Jorobo\Tasks\Build::class, $params)->run();
	}

	/**
	 * Update the copyright headers for this project.
	 *
	 * @return  void
	 */
	public function headers()
	{
		// Ensure configuration is available
		if (!file_exists('jorobo.ini'))
		{
			$this->_copy('jorobo.dist.ini', 'jorobo.ini');
		}

		// Apply copyright headers
		$this->task(\Joomla\Jorobo\Tasks\CopyrightHeader::class)->run();
	}

	/**
	 * Bump the version defined in the component files.
	 *
	 * @return  void
	 */
	public function bump()
	{
		$this->task(\Joomla\Jorobo\Tasks\BumpVersion::class)->run();
	}

	/**
	 * Map Joomla extension into the target Joomla instance.
	 *
	 * @param   string  $target  The target Joomla instance directory.
	 *
	 * @return  void

	 */
	public function map($target)
	{
		$this->task(\Joomla\Jorobo\Tasks\Map::class, $target)->run();
	}

	/**
	 * Run code quality tools to check and fix code automatically.
	 *
	 * Usage:
	 *   php vendor/bin/robo run:checker phpcs     // Check for errors
	 *   php vendor/bin/robo run:checker phpcbf    // Auto-fix errors
	 *
	 * @param   string|null  $tool  The tool to execute (phpcs, phpcbf, phpmd, phpcpd).
	 *
	 * @return  \Robo\Result
	 */
	public function runChecker($tool = null)
	{
		// List of supported tools
		$availableTools = [
			'phpcs'  => '/vendor/bin/phpcs --standard=Joomla .',
			'phpcbf' => '/vendor/bin/phpcbf --standard=Joomla --extensions=php .',
			'phpmd'  => 'vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode',
			'phpcpd' => 'vendor/bin/phpcpd src',
		];

		if (!$tool || !isset($availableTools[$tool]))
		{
			$this->say("Invalid tool specified. Available options: " . implode(', ', array_keys($availableTools)));

			return \Robo\Result::success($this);
		}

		// Execute the chosen tool command
		$command = (DIRECTORY_SEPARATOR === '\\')
			? "vendor\\bin\\" . $tool . ".bat"
			: PHP_BINARY . ' -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" -d display_errors=0 ' . __DIR__ . $availableTools[$tool];

		$this->say("Running $tool...");

		return $this->taskExec($command)->run();
	}

	/**
	 * Verify and auto-fix coding standards.
	 *
	 * Combines both phpcs and phpcbf to check and fix errors.
	 *
	 * @return  void
	 */
	public function checkAndFix()
	{
		// Run PHPCS to identify violations
		$this->say("Checking code for issues...");
		$this->runChecker('phpcs');

		// Run PHPCBF to auto-fix errors where possible
		$this->say("Attempting to fix detected issues...");
		$this->runChecker('phpcbf');

		// Run PHPCS again to confirm no issues remain
		$this->say("Re-checking to confirm no remaining issues...");
		$this->runChecker('phpcs');
	}
}
