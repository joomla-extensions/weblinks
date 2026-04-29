<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\User\User;
use Joomla\Component\Weblinks\Site\Model\FormModel;

/**
 * HTML Article View class for the Weblinks component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var    \Joomla\CMS\Form\Form
     * @since  4.0.0
     */
    protected $form;

    /**
     * @var    object
     * @since  4.0.0
     */
    protected $item;

    /**
     * @var    string
     * @since  4.0.0
     */
    protected $return_page;

    /**
     * @var    string
     * @since  4.0.0
     */
    protected $pageclass_sfx;

    /**
     * @var    \Joomla\Registry\Registry
     * @since  4.0.0
     */
    protected $state;

    /**
     * @var    \Joomla\Registry\Registry
     * @since  4.0.0
     */
    protected $params;

    /**
     * @var    User
     * @since  __DEPLOY_VERSION__
     */
    protected $user;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $user = $this->getCurrentUser();

        // Get model data.
        /* @var FormModel $model */
        $model = $this->getModel();

        $this->state       = $model->getState();
        $this->item        = $model->getItem();
        $this->form        = $model->getForm();
        $this->return_page = $model->getReturnPage();

        if (empty($this->item->id)) {
            $authorised = $user->authorise('core.create', 'com_weblinks') || \count($user->getAuthorisedCategories('com_weblinks', 'core.create'));
        } else {
            $authorised = $user->authorise('core.edit', 'com_weblinks.category.' . $this->item->catid);
        }

        if ($authorised !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if (!empty($this->item)) {
            // Override the base weblink data with any data in the session.
            $temp = (array) Factory::getApplication()->getUserState('com_weblinks.edit.weblink.data', []);

            foreach ($temp as $k => $v) {
                $this->item->$k = $v;
            }

            $this->form->bind($this->item);
        }

        // Create a shortcut to the parameters.
        $params = &$this->state->params;

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

        $this->params = $params;
        $this->user   = $user;

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function prepareDocument()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = Factory::getApplication()->getMenu()->getActive();

        if (empty($this->item->id)) {
            $head = Text::_('COM_WEBLINKS_FORM_SUBMIT_WEBLINK');
        } else {
            $head = Text::_('COM_WEBLINKS_FORM_EDIT_WEBLINK');
        }

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', $head);
        }

        $title = $this->params->def('page_title', $head);

        $this->setDocumentTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->getDocument()->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
