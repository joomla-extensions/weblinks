<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Installer\InstallerScript;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  __DEPLOY_VERSION__
 */
class Pkg_WeblinksInstallerScript extends InstallerScript
{
    /**
     * Allow downgrades of your extension
     *
     * Use at your own risk as if there is a change in functionality people may wish to downgrade.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $allowDowngrades = true;

    /**
     * Extension script constructor.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct()
    {
        $this->minimumJoomla = '5.3.0';
        $this->minimumPhp    = JOOMLA_MINIMUM_PHP;
    }
}
