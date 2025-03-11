<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Weblinks Main Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'weblinks';
    /**
         * Method to display a view.
         *
         * @param   boolean  $cacheable  If true, the view output will be cached
         * @param   array    $urlparams  An array of safe url parameters and their variable types,
         *                               for valid values see {@link JFilterInput::clean()}.
         *
         * @return  BaseController|boolean  This object to support chaining.
         *
         * @since   1.5
         */
    public function display($cacheable = false, $urlparams = false)
    {
        $view   = $this->input->get('view', 'weblinks');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');
        // Check for edit form.
        if ($view == 'weblink' && $layout == 'edit' && !$this->checkEditId('com_weblinks.edit.weblink', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_weblinks&view=weblinks', false));
            return false;
        }

        return parent::display();
    }
}
