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
 * @since  3.6.0
 */
class plgSearchWeblinksInstallerScript
{
	/**
	 * Method to run after the install routine.
	 *
	 * @param   string                      $type    The action being performed
	 * @param   JInstallerAdapterComponent  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function postflight($type, $parent)
	{
		// Remove old language files if present.
		$this->removeOldLanguageFiles();
	}

	/**
	 * Remove old language files if present.
	 *
	 * From 3.6.0 onwards, language files are included in the plugin rather than in administrator/language.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	private function removeOldLanguageFiles()
	{
		$filesToRemove = array(
			'/administrator/language/en-GB/en-GB.plg_search_weblinks.ini',
			'/administrator/language/en-GB/en-GB.plg_search_weblinks.sys.ini',
			);

		foreach ($filesToRemove as $filename)
		{
			if (file_exists(JPATH_ROOT . $filename))
			{
				unlink(JPATH_ROOT . $filename);
			}
		}
	}
}
