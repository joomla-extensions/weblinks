<?php
/**
 * @package    Joomla.Component
 * @subpackage com_weblinksmanager
 *
 * @copyright Copyright (C) 2025.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model class for retrieving Weblinks data.
 *
 * @since 1.0.0
 */
class WeblinksmanagerModelWeblinks extends ListModel
{
    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array('id', 'title', 'url', 'state');
        }

        parent::__construct($config);
    }

    /**
     * Method to build a query to retrieve the list of weblinks.
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName(array('a.id', 'a.title', 'a.url', 'a.state')))
            ->from($db->quoteName('#__weblinks', 'a'));

        return $query;
    }

    /**
     * Override getItems to add debug message and possible state filtering.
     *
     * @return array
     */
    public function getItems()
    {
        $items = parent::getItems();

        Factory::getApplication()->enqueueMessage(
            'dBModeel returned ' . (is_array($items) ? count($items) : '0') . ' items',
            'notice'
        );

        if ($items) {
            $states = array_unique(array_column($items, 'state'));
        }

        return $items ?: array();
    }
}
