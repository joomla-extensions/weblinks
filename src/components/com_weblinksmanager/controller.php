<?php

/**
 * Default controller for the Weblinks Manager component.
 *
 * @category   Joomla.Component.Site
 * @package    Joomla.Site
 * @subpackage Com_Weblinksmanager
 * @license    GNU General Public License version 2 or later; see LICENSE.txt

 * @link  https://joomla.org
 * @since 1.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Default controller class for Weblinks Manager.
 *
 * @category   Joomla.Component.Site
 * @package    Joomla.Site
 * @subpackage Com_Weblinksmanager
 * @license    GNU General Public License version 2 or later
 * @link       https://joomla.org
 * @since      1.0
 */
class WeblinksmanagerController extends BaseController
{
    /**
     * Display the requested view.
     *
     * @param boolean $cachable  If true, the view output will be cached.
     * @param array   $urlparams An array of safe URL parameters and their
     *                           variable types.
     *
     * @return WeblinksmanagerController  This object to support chaining.
     *
     * @since 1.0
     */
    public function display($cachable = false, $urlparams = [])
    {
        $view = $this->input->get('view', 'dashboard');
        $this->input->set('view', $view);

        $document   = Factory::getDocument();
        $viewType   = $document->getType();
        $viewName   = $this->input->get('view', 'dashboard');
        $viewLayout = $this->input->get('layout', 'default');

        $view = $this->getView(
            $viewName,
            $viewType,
            '',
            ['base_path' => JPATH_COMPONENT]
        );
        $view->setLayout($viewLayout);

        parent::display($cachable, $urlparams);

        return $this;
    }
}
