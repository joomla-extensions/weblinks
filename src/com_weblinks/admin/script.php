<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       3.4
 */
class Com_WeblinksInstallerScript
{
	/**
	 * Function to perform changes during install
	 *
	 * @param   JInstallerAdapterComponent  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function install($parent)
	{
		// Initialize a new category
		/** @type  JTableCategory  $category  */
		$category = JTable::getInstance('Category');

		// Check if the Uncategorised category exists before adding it
		if (!$category->load(array('extension' => 'com_weblinks', 'title' => 'Uncategorised')))
		{
			$category->extension = 'com_weblinks';
			$category->title = 'Uncategorised';
			$category->description = '';
			$category->published = 1;
			$category->access = 1;
			$category->params = '{"category_layout":"","image":""}';
			$category->metadata = '{"author":"","robots":""}';
			$category->language = '*';

			// Set the location in the tree
			$category->setLocation(1, 'last-child');

			// Check to make sure our data is valid
			if (!$category->check())
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_WEBLINKS_ERROR_INSTALL_CATEGORY', $category->getError()));

				return;
			}

			// Now store the category
			if (!$category->store(true))
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_WEBLINKS_ERROR_INSTALL_CATEGORY', $category->getError()));

				return;
			}

			// Build the path for our category
			$category->rebuildPath($category->id);
		}
	}

	/**
	 * method to run after an install/downloads/uninstall method
	 *
	 * @return void
	 *
	 * @since  3.4.1
	 */
	function postflight($type, $parent)
	{
		$tasks = array(
			'0' => 'sid',
			'1' => 'date',
			'2' => 'archived',
			'3' => 'approved',
		);

		$db    = JFactory::getDbo();
		$table = $db->getTableColumns('#__weblinks');

		foreach ($tasks as $column)
		{
			foreach ($table as $columName => $columType)
			{
				if ($columName == $column)
				{
					$sql = "ALTER TABLE `#__weblinks` DROP COLUMN `" . $column . "`";
					$db->clean();
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}
	}
}
