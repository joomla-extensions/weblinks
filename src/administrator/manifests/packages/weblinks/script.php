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

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\DatabaseInterface;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  5.1.0
 */
class Pkg_WeblinksInstallerScript extends InstallerScript
{
    /**
     * Allow downgrades of your extension
     *
     * Use at your own risk as if there is a change in functionality people may wish to downgrade.
     *
     * @var    boolean
     * @since  5.1.0
     */
    protected $allowDowngrades = true;

    /**
     * Extension script constructor.
     *
     * @since   5.1.0
     */
    public function __construct()
    {
        $this->minimumJoomla = '5.2.0';
        $this->minimumPhp    = JOOMLA_MINIMUM_PHP;
    }

    /**
     * Function called after the package is installed, updated or uninstalled.
     *
     * @param   string            $type    The type of change (install, update, discover_install or uninstall)
     * @param   InstallerAdapter  $parent  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function postflight($type, $parent)
    {
        if ($type === 'update') {
            $this->removeSearchPlugin();
        }

        return true;
    }

    /**
     * Uninstall the search plugin, which was part of older versions of the package.
     * com_search and the search plugin group no longer exist since Joomla 4.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function removeSearchPlugin(): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('search'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('weblinks'));

        $extensionId = (int) $db->setQuery($query)->loadResult();

        if (!$extensionId) {
            return;
        }

        // Detach the plugin from the package, otherwise it can't be uninstalled on its own
        $query = $db->createQuery()
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('package_id') . ' = 0')
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query)->execute();

        $installer = new Installer();
        $installer->setDatabase($db);
        $installer->uninstall('plugin', $extensionId);
    }
}
