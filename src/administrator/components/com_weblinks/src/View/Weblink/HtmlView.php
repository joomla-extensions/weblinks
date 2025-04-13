<?php

/**
 * @package    Joomla.Administrator
 * @subpackage Weblinks
 *
 * @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\View\Weblink;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Weblinks\Administrator\Model\WeblinkModel;

/**
 * View to edit a weblink.
 *
 * @since 1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The active item
     *
     * @var object
     */
    protected $item;

    /**
     * The model state
     *
     * @var \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Display the view.
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        /**
         * @var WeblinkModel $model
         */
        $model = $this->getModel();


        try {
            $this->state = $model->getState();
            $this->item  = $model->getItem();
            $this->form  = $model->getForm();
        } catch (\Exception $e) {
            throw new \Joomla\CMS\Exception\GenericDataException($e->getMessage(), 500);
        }




        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->getInput()->get('forcedLanguage', '', 'cmd')) {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');

            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);

            // Only allow to select tags with All language or with the forced language.
            $this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since 1.6
     */
    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $isNew      = ($this->item->id == 0);
        $checkedOut = $this->item->checked_out && $this->item->checked_out !== $user->id;

        // Since we don't track these assets at the item level, use the category id.
        $canDo = ContentHelper::getActions('com_weblinks', 'category', $this->item->catid);

        ToolbarHelper::title($isNew ? Text::_('COM_WEBLINKS_MANAGER_WEBLINK_NEW') : Text::_('COM_WEBLINKS_MANAGER_WEBLINK_EDIT'), 'link weblinks');

        // Build the actions for new and existing records.
        if ($isNew) {
            // For new records, check the create permission.
            if (\count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0) {
                ToolbarHelper::apply('weblink.apply');

                ToolbarHelper::saveGroup(
                    [
                        ['save', 'weblink.save'],
                        ['save2new', 'weblink.save2new'],
                    ],
                    'btn-success'
                );
            }

            ToolbarHelper::cancel('weblink.cancel');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $user->id);

            $toolbarButtons = [];

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                ToolbarHelper::apply('weblink.apply');

                $toolbarButtons[] = ['save', 'weblink.save'];

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    $toolbarButtons[] = ['save2new', 'weblink.save2new'];
                }
            }

            // If checked out, we can still save
            if ($canDo->get('core.create')) {
                $toolbarButtons[] = ['save2copy', 'weblink.save2copy'];
            }

            ToolbarHelper::saveGroup(
                $toolbarButtons,
                'btn-success'
            );

            ToolbarHelper::cancel('weblink.cancel', 'JTOOLBAR_CLOSE');

            if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->get('params.save_history', 0) && $itemEditable) {
                ToolbarHelper::versions('com_weblinks.weblink', $this->item->id);
            }

            if (Associations::isEnabled() && ComponentHelper::isEnabled('com_associations')) {
                ToolbarHelper::custom('weblink.editAssociations', 'contract', '', 'JTOOLBAR_ASSOCIATIONS', false, false);
            }
        }

        ToolbarHelper::help('Components_Weblinks_Links_Edit');
    }
}
