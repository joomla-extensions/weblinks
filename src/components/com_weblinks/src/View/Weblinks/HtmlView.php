<?php

/**
 * @package     Joomla.Site
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Weblinks;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Weblinks\Administrator\Model\WeblinksModel;

/**
 * View class for a list of weblinks.
 *
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    private $isEmptyState = false;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        /** @var WeblinksModel $model */
        $model = $this->getModel();

        $this->state         = $model->getState();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
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
     * @since  __DEPLOY_VERSION__
     */
    protected function addToolbar()
    {
        $canDo = ContentHelper::getActions('com_weblinks', 'category', $this->state->get('filter.category_id'));
        $user  = $this->getCurrentUser();

        // Get the toolbar object instance
        $toolbar = $this->getDocument()->getToolbar('toolbar');

        ToolbarHelper::title(Text::_('COM_WEBLINKS_MANAGER_WEBLINKS'), 'link weblinks');

        if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0) {
            ToolbarHelper::addNew('weblink.add');
        }

        if (!$this->isEmptyState && $canDo->get('core.edit.state')) {
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

            if ($user->authorise('core.admin')) {
                $childBar->checkin('weblinks.checkin')->listCheck(true);
            }

            if ($this->state->get('filter.published') != -2) {
                $childBar->trash('weblinks.trash')->listCheck(true);
            }

            // Add a batch button
            if (
                $user->authorise('core.create', 'com_weblinks')
                && $user->authorise('core.edit', 'com_weblinks')
                && $user->authorise('core.edit.state', 'com_weblinks')
            ) {
                $childBar->popupButton('batch')
                    ->text('JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

        if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('weblinks.delete')
                ->text('JTOOLBAR_DELETE')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        ToolbarHelper::help('Components_Weblinks_Links');
    }
}
