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

require_once 'vendor/autoload.php';

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', __DIR__);
}

/**
 * Modern PHP task runner for Joomla! Browser Automated Tests execution.
 *
 * @package  RoboFile
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
     * Build the Joomla extension package.
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
     * @param   string  $target  The target Joomla instance
     *
     * @return  void
     */
    public function map($target)
    {
        $this->task(\Joomla\Jorobo\Tasks\Map::class, $target)->run();
    }

    /**
     * Run PHP CodeSniffer and PHP Code Beautifier and Fixer.
     *
     * @param   string|null  $tool  The tool to run (optional).
     *
     * @return  void
     */
    public function runChecker($tool = null)
{
    $tools = ['phpcs', 'phpcbf']; // Adding phpcbf for auto-fixing
    foreach ($tools as $t) {
        $cmd = (DIRECTORY_SEPARATOR === '\\') ? "vendor\\bin\\$t.bat" : "./vendor/bin/$t";
        $this->taskExec("$cmd --standard=ruleset.xml src/")
            ->run();
    }
}
}

}
