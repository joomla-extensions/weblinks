<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
 *
 * @package     Joomla.Site
 * @subpackage  RoboFile
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Jorobo\Tasks\Tasks as loadReleaseTasks;
use Robo\Tasks;

require_once '/Applications/XAMPP/xamppfiles/htdocs/joomla/libraries/vendor/autoload.php';

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', __DIR__);
}

/**
 * Modern php task runner for Joomla! Browser Automated Tests execution
 *
 * @package  RoboFile
 *
 * @since    1.0
 */
class RoboFile extends Tasks
{
    // Load tasks from composer, see composer.json
    use loadReleaseTasks;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set default timezone (so no warnings are generated if it is not set)
        date_default_timezone_set('UTC');
    }

    /**
     * Build the joomla extension package
     *
     * @param   array  $params  Additional params
     *
     * @return  void
     */
    public function build($params = ['dev' => false])
    {
        if (!file_exists('jorobo.ini')) {
            $this->_copy('jorobo.dist.ini', 'jorobo.ini');
        }

        $this->task(\Joomla\Jorobo\Tasks\Build::class, $params)->run();
    }

    /**
     * Update copyright headers for this project. (Set the text up in the jorobo.ini)
     *
     * @return  void
     */
    public function headers()
    {
        if (!file_exists('jorobo.ini')) {
            $this->_copy('jorobo.dist.ini', 'jorobo.ini');
        }

        $this->task(\Joomla\Jorobo\Tasks\CopyrightHeader::class)->run();
    }

    /**
     * Update Version __DEPLOY_VERSION__ in Component. (Set the version up in the jorobo.ini)
     *
     * @return  void
     */
    public function bump()
    {
        $this->task(\Joomla\Jorobo\Tasks\BumpVersion::class)->run();
    }

    /**
     * Map into Joomla installation.
     *
     * @param   String  $target  The target joomla instance
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     *
     */
    public function map($target)
    {
        $this->task(\Joomla\Jorobo\Tasks\Map::class, $target)->run();
    }
      /**
     * Run static code analysis tools.
     *
     * Usage:
     *   vendor/bin/robo run:checker phpcs
     *   vendor/bin/robo run:checker phpcbf
     *
     * @param string|null $tool The tool to run (phpcs, phpcbf, phpmd, phpcpd)
     *
     * @return \Robo\Result
     */
    public function runChecker($tool = null)
    {
        $allowedTools = [
            'phpmd',
            'phpcs',
            'phpcpd',
            'phpcbf', //Added phpcbf support
        ];

        $allowedToolsString = implode(', ', $allowedTools);

        if (!in_array($tool, $allowedTools, true)) {
            $this->say("The tool you required is not known. Valid tools are $allowedToolsString");
            return \Robo\Result::success($this);
        }

        if ($tool === 'phpcs') {
            return $this->taskExec(PHP_BINARY . ' -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" -d display_errors=0 /Applications/XAMPP/xamppfiles/htdocs/joomla/libraries/vendor/bin/phpcs --standard=Joomla .')->run();
        }

        if ($tool === 'phpcbf') {
            return $this->taskExec(PHP_BINARY . ' -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" -d display_errors=0 /Applications/XAMPP/xamppfiles/htdocs/joomla/libraries/vendor/bin/phpcbf --standard=Joomla --extensions=php .')->run();
        }

        if ($tool === 'phpmd') {
            return $this->taskExec("vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode")->run();
        }

        if ($tool === 'phpcpd') {
            return $this->taskExec("vendor/bin/phpcpd src")->run();
        }

        return \Robo\Result::success($this);
    }
}
