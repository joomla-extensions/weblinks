<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblink Component HTML Helper.
 *
 * @since  1.5
 */
class JHtmlIcon
{
	/**
	 * Create a link to create a new weblink
	 *
	 * @param   object                     $category  The category information
	 * @param   \Joomla\Registry\Registry  $params    The item parameters
	 *
	 * @return  string
	 */
	public static function create($category, $params)
	{
		return self::getIcon()->create($category, $params);
	}

	/**
	 * Create a link to edit an existing weblink
	 *
	 * @param   object                     $weblink  Weblink data
	 * @param   \Joomla\Registry\Registry  $params   Item params
	 * @param   array                      $attribs  Unused
	 *
	 * @return  string
	 */
	public static function edit($weblink, $params, $attribs = array())
	{
		return self::getIcon()->edit($weblink, $params, $attribs);
	}

	/**
	 * Creates an icon instance.
	 *
	 * @return  \Joomla\Component\Weblinks\Administrator\Service\HTML\Icon
	 */
	private static function getIcon()
	{
		return (new \Joomla\Component\Weblinks\Administrator\Service\HTML\Icon(Joomla\CMS\Factory::getApplication()));
	}
}
