<?php

/**
 * @package    Joomla.Site
 * @subpackage com_weblinksmanager
 *
 * @copyright Copyright (C)
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;


/**
 * View class for a single weblink.
 */
class WeblinksmanagerViewWeblink extends HtmlView
{
    /**
     * @var object  The weblink item.
     */
    protected $item;

    /**
     * Display the view.
     *
     * @param string|null $tpl The name of the template file to parse.
     *
     * @return void|boolean
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $id = $app->input->getInt('id');

        if (!$id) {
            $app->enqueueMessage('Invalid weblink ID', 'error');
            $app->redirect(
                'index.php?option=com_weblinksmanager&view=dashboard'
            );
            return false;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'url', 'state']))
            ->from($db->quoteName('#__weblinks'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);

        $db->setQuery($query);
        $this->item = $db->loadObject();

        if (!$this->item) {
            $app->enqueueMessage('Weblink not found', 'error');
            $app->redirect(
                'index.php?option=com_weblinksmanager&view=dashboard'
            );
            return false;
        }

        parent::display($tpl);
    }
}
