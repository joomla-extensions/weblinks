<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\View\Weblinks;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Weblinks\Administrator\Model\WeblinksModel;

/**
 * View class for a list of weblinks.
 *
 * @since  1.5
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
     * @since 4.0.0
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

        // We don't need toolbar in the modal layout.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        } else {
            // In article associations modal we need to remove language filter if forcing a language.
            // We also need to change the category filter to show show categories with All or the forced language.
            if ($forcedLanguage = Factory::getApplication()->getInput()->get('forcedLanguage', '', 'CMD')) {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into an hidden field.
                $languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);

                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
            }
        }

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
        $canDo = ContentHelper::getActions('com_weblinks', 'category', $this->state->get('filter.category_id'));
        $user  = $this->getCurrentUser();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

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
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', 'com_weblinks') || $user->authorise('core.options', 'com_weblinks')) {
            ToolbarHelper::preferences('com_weblinks');
        }

        ToolbarHelper::help('Components_Weblinks_Links');
    }
}
