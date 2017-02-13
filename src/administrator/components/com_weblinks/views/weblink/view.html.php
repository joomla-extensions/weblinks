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
 * View to edit a weblink.
 *
 * @since  1.5
 */
class WeblinksViewWeblink extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();

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
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user       = JFactory::getUser();
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		// Since we don't track these assets at the item level, use the category id.
		$canDo = JHelperContent::getActions('com_weblinks', 'category', $this->item->catid);

		JToolbarHelper::title($isNew ? JText::_('COM_WEBLINKS_MANAGER_WEBLINK_NEW') : JText::_('COM_WEBLINKS_MANAGER_WEBLINK_EDIT'), 'link weblinks');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||(count($user->getAuthorisedCategories('com_weblinks', 'core.create')))))
		{
			JToolbarHelper::apply('weblink.apply');
			JToolbarHelper::save('weblink.save');
		}
		if (!$checkedOut && (count($user->getAuthorisedCategories('com_weblinks', 'core.create'))))
		{
			JToolbarHelper::save2new('weblink.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && (count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0))
		{
			JToolbarHelper::save2copy('weblink.save2copy');
		}
		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('weblink.cancel');
		}
		else
		{
			if ($this->state->params->get('save_history', 0) && $user->authorise('core.edit'))
			{
				JToolbarHelper::versions('com_weblinks.weblink', $this->item->id);
			}

			JToolbarHelper::cancel('weblink.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_WEBLINKS_LINKS_EDIT');
	}
}
