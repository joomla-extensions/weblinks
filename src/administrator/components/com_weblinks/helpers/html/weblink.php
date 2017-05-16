<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('WeblinksHelper', JPATH_ADMINISTRATOR . '/components/com_weblinks/helpers/weblinks.php');

/**
 * Weblink HTML helper class.
 *
 * @since  1.6
 */
abstract class JHtmlWeblink
{
	/**
	 * Get the associated language flags
	 *
	 * @param   integer  $weblinkid  The item id to search associations
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  Exception
	 */
	public static function association($weblinkid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = JLanguageAssociations::getAssociations('com_weblinks', '#__weblinks', 'com_weblinks.item', $weblinkid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated weblinks items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.title as title')
				->select('l.sef as lang_sef, lang_code')
				->from('#__weblinks as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text = strtoupper($item->lang_sef);
					$url = JRoute::_('index.php?option=com_weblinks&task=weblink.edit&id=' . (int) $item->id);

					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br />' . JText::sprintf('JCATEGORY_SPRINTF', $item->category_title);
					$classes = 'hasPopover label label-association label-' . $item->lang_sef;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			JHtml::_('bootstrap.popover');

			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
