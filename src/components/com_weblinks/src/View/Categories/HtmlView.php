<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Categories;

use Joomla\CMS\MVC\View\CategoriesView;

defined('_JEXEC') or die;

/**
 * Weblinks categories view.
 *
 * @since  1.5
 */
class HtmlView extends CategoriesView
{
	/**
	 * @var    string  Default title to use for page title
	 * @since  3.2
	 */
	protected $pageHeading = 'COM_WEBLINKS_DEFAULT_PAGE_TITLE';

	/**
	 * @var    string  The name of the extension for the category
	 * @since  3.2
	 */
	protected $extension = 'com_weblinks';
}
