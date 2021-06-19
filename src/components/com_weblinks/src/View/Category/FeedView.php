<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Category;

use Joomla\CMS\MVC\View\CategoryFeedView;

defined('_JEXEC') or die;

/**
 * HTML View class for the WebLinks component
 *
 * @since  1.0
 */
class FeedView extends CategoryFeedView
{
	/**
	 * @var    string  The name of the view to link individual items to
	 * @since  3.2
	 */
	protected $viewName = 'weblink';
}
