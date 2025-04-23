<?php

/**
 * @package    Joomla.Site
 * @subpackage com_weblinksmanager
 *
 * @copyright Copyright (C)
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinksmanager\Site\View\Dashboard;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * View class for the Weblinksmanager Dashboard
 *
 * @since 1.0.0
 */
class WeblinksmanagerViewDashboard extends HtmlView
{
    /**
     * The list of items to display
     *
     * @var array
     */
    protected $items;

    /**
     * Display the Dashboard view
     *
     * @param string $tpl Template name
     *
     * @return void
     */
    public function display($tpl = null)
    {
        BaseDatabaseModel::addIncludePath(
            JPATH_COMPONENT . '/models',
            'WeblinksmanagerModel'
        );

        $model = BaseDatabaseModel::getInstance(
            'Weblinks',
            'WeblinksmanagerModel'
        );

        $this->items = $model ? $model->getItems() : [];

        parent::display($tpl);
    }
}
