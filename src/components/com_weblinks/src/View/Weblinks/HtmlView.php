<?php

/**
 * @package     Joomla.Site
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Weblinks;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * View class for a list of weblinks in the site context.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Is this view an Empty State
     *
     * @var  boolean
     */
    private $isEmptyState = false;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        $model               = $this->getModel();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Check for errors
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!\count($this->items) && ($this->isEmptyState == $this->get('IsEmptyState'))) {
            $this->setLayout('emptystate');
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
        $user       = $this->getCurrentUser();
        $categoryId = $this->state->get('filter.category_id', 0);
        $canDo      = ContentHelper::getActions('com_weblinks', 'category', $categoryId);

        // Debug permissions
        Log::add('Permissions for category ' . $categoryId . ': ' . print_r($canDo, true), Log::INFO, 'com_weblinks');

        $toolbar = Toolbar::getInstance('toolbar');
        ToolbarHelper::title(Text::_('COM_WEBLINKS_MANAGER_WEBLINKS'), 'link weblinks');

        // Ensure New button is always added if user has create permission at component level
        if ($user->authorise('core.create', 'com_weblinks') || \count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0) {
            ToolbarHelper::addNew('weblink.add');
        }

        // Ensure Edit button is always added if user has edit permission
        if ($user->authorise('core.edit', 'com_weblinks') || $user->authorise('core.edit.own', 'com_weblinks')) {
            ToolbarHelper::editList('weblink.edit');
        }

        // Ensure status actions are added if user has edit.state permission
        if ($user->authorise('core.edit.state', 'com_weblinks')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('weblinks.publish')->listCheck(true);
            $childBar->unpublish('weblinks.unpublish')->listCheck(true);
            $childBar->archive('weblinks.archive')->listCheck(true);

            if ($user->authorise('core.admin', 'com_weblinks') || $user->authorise('core.manage', 'com_checkin')) {
                $childBar->checkin('weblinks.checkin')->listCheck(true);
            }

            if ($this->state->get('filter.published') != -2) {
                $childBar->trash('weblinks.trash')->listCheck(true);
            }
        }

        // Ensure Delete button is added for trashed items
        if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_weblinks')) {
            $toolbar->delete('weblinks.delete')
                ->text('JTOOLBAR_DELETE')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        // Ensure Batch button is added
        if ($user->authorise('core.create', 'com_weblinks') && $user->authorise('core.edit', 'com_weblinks') && $user->authorise('core.edit.state', 'com_weblinks')) {
            $toolbar->popupButton('batch')
                ->text('JTOOLBAR_BATCH')
                ->selector('collapseModal')
                ->listCheck(true);
        }



        ToolbarHelper::help('Components_Weblinks_Links');
    }
}
