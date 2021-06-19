<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Weblink;

defined('_JEXEC') or die;

/**
 * HTML Weblink View class for the Weblinks component
 *
 * @since  __DEPLOY_VERSION__
 */
class WeblinksViewWeblink extends JViewLegacy
{
	protected $item;

	protected $params;

	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$dispatcher = JEventDispatcher::getInstance();

		$this->item   = $this->get('Item');
		$this->state  = $this->get('State');
		$this->params = $this->state->get('params');

		// Create a shortcut for $item.
		$item = $this->item;

		$offset = $this->state->get('list.offset');

		$dispatcher->trigger('onContentPrepare', array ('com_weblinks.weblink', &$item, &$item->params, $offset));

		$item->event = new stdClass;

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_weblinks.weblink', &$item, &$item->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_weblinks.weblink', &$item, &$item->params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_weblinks.weblink', &$item, &$item->params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		parent::display($tpl);
	}
}
