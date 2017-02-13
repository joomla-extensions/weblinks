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
 * View class for a list of weblinks.
 *
 * @since  1.5
 */
class WeblinksViewWeblinks extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		WeblinksHelper::addSubmenu('weblinks');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/weblinks.php';

		$state = $this->get('State');
		$canDo = JHelperContent::getActions('com_weblinks', 'category', $state->get('filter.category_id'));
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_WEBLINKS_MANAGER_WEBLINKS'), 'link weblinks');

		if (count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0)
		{
			JToolbarHelper::addNew('weblink.add');
		}

		if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('weblink.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('weblinks.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('weblinks.unpublish', 'JTOOLBAR_UNPUBLISH', true);

			JToolbarHelper::archiveList('weblinks.archive');
			JToolbarHelper::checkin('weblinks.checkin');
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'weblinks.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('weblinks.trash');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_weblinks') && $user->authorise('core.edit', 'com_weblinks')
			&& $user->authorise('core.edit.state', 'com_weblinks'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($user->authorise('core.admin', 'com_weblinks') || $user->authorise('core.options', 'com_weblinks'))
		{
			JToolbarHelper::preferences('com_weblinks');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_WEBLINKS_LINKS');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.title' => JText::_('JGLOBAL_TITLE'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'a.hits' => JText::_('JGLOBAL_HITS'),
			'a.language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
