<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblinks helper.
 *
 * @since  1.6
 */
class WeblinksHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName = 'weblinks')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_WEBLINKS_SUBMENU_WEBLINKS'),
			'index.php?option=com_weblinks&view=weblinks',
			$vName == 'weblinks'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_WEBLINKS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_weblinks',
			$vName == 'categories'
		);
	}

	/**
	 * Adds Count Items for WebLinks Category Manager.
	 *
	 * @param   stdClass[]  &$items  The weblinks category objects.
	 *
	 * @return  stdClass[]  The weblinks category objects.
	 *
	 * @since   3.6.0
	 */
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_archived    = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;

			$query = $db->getQuery(true)
				->select('state, COUNT(*) AS count')
				->from($db->qn('#__weblinks'))
				->where($db->qn('catid') . ' = ' . (int) $item->id)
				->group('state');

			$db->setQuery($query);
			$weblinks = $db->loadObjectList();

			foreach ($weblinks as $weblink)
			{
				if ($weblink->state == 1)
				{
					$item->count_published = $weblink->count;
				}
				elseif ($weblink->state == 0)
				{
					$item->count_unpublished = $weblink->count;
				}
				elseif ($weblink->state == 2)
				{
					$item->count_archived = $weblink->count;
				}
				elseif ($weblink->state == -2)
				{
					$item->count_trashed = $weblink->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The weblink tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.7.0
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;

			$query = $db->getQuery(true);
			$query->select('published as state, count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id')
				->group('state');

			$db->setQuery($query);
			$weblinks = $db->loadObjectList();

			foreach ($weblinks as $weblink)
			{
				if ($weblink->state == 1)
				{
					$item->count_published = $weblink->count;
				}
				if ($weblink->state == 0)
				{
					$item->count_unpublished = $weblink->count;
				}
				if ($weblink->state == 2)
				{
					$item->count_archived = $weblink->count;
				}
				if ($weblink->state == -2)
				{
					$item->count_trashed = $weblink->count;
				}
			}
		}

		return $items;
	}
}
