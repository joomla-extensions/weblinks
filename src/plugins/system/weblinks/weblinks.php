<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

/**
 * System plugin for Joomla Web Links.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemWeblinks extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method to add statistics information to Administrator control panel.
	 *
	 * @param   string   $extension  The extension requesting information.
	 *
	 * @return  array containing statistical information.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onGetStats($extension)
	{
		if (!JComponentHelper::isEnabled('com_weblinks'))
		{
			return array();
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(id) AS count_links')
			->from('#__weblinks')
			->where('state = 1');
		$links = $db->setQuery($query)->loadResult();

		if (!$links)
		{
			return array();
		}

		return array(array(
			'title' => JText::sprintf('PLG_SYSTEM_WEBLINKS_STATISTICS'),
			'icon'  => 'out-2',
			'data'  => $links,
			'link'  => JUri::root() . 'administrator/index.php?option=com_weblinks&view=weblinks&filter[published]=1',
		));
	}
}
