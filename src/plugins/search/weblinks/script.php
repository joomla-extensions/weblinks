<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSearchWeblinksInstallerScript extends JInstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->minimumJoomla = '3.6';
		$this->minimumPhp    = JOOMLA_MINIMUM_PHP;

		$this->deleteFiles = array(
			'/administrator/language/en-GB/en-GB.plg_search_weblinks.ini',
 			'/administrator/language/en-GB/en-GB.plg_search_weblinks.sys.ini',
		);
	}

	/**
	 * Method to run after the install routine.
	 *
	 * @param   string                      $type    The action being performed
	 * @param   JInstallerAdapterComponent  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function postflight($type, $parent)
	{
		// Remove files
		$this->removeFiles();
	}
}
