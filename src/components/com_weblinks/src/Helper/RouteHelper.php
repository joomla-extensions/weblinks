<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\Helper;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Language\Multilanguage;

defined('_JEXEC') or die;

/**
 * Weblinks Component Route Helper.
 *
 * @since  1.5
 */
abstract class RouteHelper
{
	/**
	 * Get the route of the weblink
	 *
	 * @param   integer  $id        Web link ID
	 * @param   integer  $catid     Category ID
	 * @param   string   $language  Language
	 *
	 * @return  string
	 */
	public static function getWeblinkRoute($id, $catid, $language = 0)
	{
		// Create the link
		$link = 'index.php?option=com_weblinks&view=weblink&id=' . $id;

		if ($catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		return $link;
	}

	/**
	 * Ge the form route
	 *
	 * @param   integer  $id      The id of the weblink.
	 * @param   string   $return  The return page variable.
	 *
	 * @return  string
	 */
	public static function getFormRoute($id, $return = null)
	{
		// Create the link.
		if ($id)
		{
			$link = 'index.php?option=com_weblinks&task=weblink.edit&w_id=' . $id;
		}
		else
		{
			$link = 'index.php?option=com_weblinks&task=weblink.add&w_id=0';
		}

		if ($return)
		{
			$link .= '&return=' . $return;
		}

		return $link;
	}

	/**
	 * Get the Category Route
	 *
	 * @param   CategoryNode|string|integer  $catid     JCategoryNode object or category ID
	 * @param   integer                      $language  Language code
	 *
	 * @return  string
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof CategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			// Create the link
			$link = 'index.php?option=com_weblinks&view=category&id=' . $id;

			if ($language && $language !== '*' && Multilanguage::isEnabled())
			{
				$link .= '&lang=' . $language;
			}
		}

		return $link;
	}
}
