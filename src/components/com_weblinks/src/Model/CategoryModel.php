<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Category;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Weblinks Component Weblink Model
 *
 * @since  1.5
 */
class CategoryModel extends ListModel
{
    /**
     * Category item data
     *
     * @var CategoryNode|null
     */
    protected $_item = null;

    /**
     * Category left of this one
     *
     * @var    CategoryNode|null
     */
    protected $_leftsibling = null;

    /**
     * Category right right of this one
     *
     * @var    CategoryNode|null
     */
    protected $_rightsibling = null;

    /**
     * Array of child-categories
     *
     * @var    CategoryNode[]|null
     */
    protected $_children = null;

    /**
     * Parent category of the current one
     *
     * @var    CategoryNode|null
     */
    protected $_parent = null;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JControllerLegacy
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'hits', 'a.hits',
                'ordering', 'a.ordering',
            ];
        }

        parent::__construct($config);
    }


    /**
     * Method to get a list of items.
     *
     * @return  mixed  An array of objects on success, false on failure.
     */
    public function getItems()
    {
        // Invoke the parent getItems method to get the main list
        $items = parent::getItems();

        $taggedItems = [];

        // Convert the params field into an object, saving original in _params
        foreach ($items as $item) {
            if (!isset($this->_params)) {
                $item->params = new Registry($item->params);
            }

            // Some contexts may not use tags data at all, so we allow callers to disable loading tag data
            if ($this->getState('load_tags', true)) {
                $item->tags             = new TagsHelper();
                $taggedItems[$item->id] = $item;
            }
        }

        // Load tags of all items.
        if ($taggedItems) {
            $tagsHelper = new TagsHelper();
            $itemIds    = array_keys($taggedItems);

            foreach ($tagsHelper->getMultipleItemTags('com_weblinks.weblink', $itemIds) as $id => $tags) {
                $taggedItems[$id]->tags->itemTags = $tags;
            }
        }

        return $items;
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  \JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
     *
     * @since   1.6
     */
    protected function getListQuery()
{
    $viewLevels = $this->getCurrentUser()->getAuthorisedViewLevels();
    $db = $this->getDatabase();
    $query = $db->getQuery(true);
    $nowUTC = Factory::getDate()->toSql(); // Get current UTC time

    $query->select($this->getState('list.select', 'a.*'))
          ->from($db->quoteName('#__weblinks') . ' AS a')
          ->whereIn($db->quoteName('a.access'), $viewLevels)
          ->where($db->quoteName('a.state') . ' = 1') // Only published weblinks
          ->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= ' . $db->quote($nowUTC) . ')')
          ->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' > ' . $db->quote($nowUTC) . ')');

    if ($categoryId = $this->getState('category.id')) {
        if ($this->getState('category.group', 0)) {
            $query->select('c.title AS category_title')
                  ->where('c.parent_id = :parent_id')
                  ->bind(':parent_id', $categoryId, ParameterType::INTEGER)
                  ->join('LEFT', '#__categories AS c ON c.id = a.catid')
                  ->whereIn($db->quoteName('c.access'), $viewLevels);
        } else {
            $query->where('a.catid = :catid')
                  ->bind(':catid', $categoryId, ParameterType::INTEGER)
                  ->join('LEFT', '#__categories AS c ON c.id = a.catid')
                  ->whereIn($db->quoteName('c.access'), $viewLevels);
        }

        // Filter by published category
        $cpublished = $this->getState('filter.c.published');
        if (is_numeric($cpublished)) {
            $query->where('c.published = :published')
                  ->bind(':published', $cpublished, ParameterType::INTEGER);
        }
    }

    $query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
          ->select("ua.email AS author_email")
          ->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
          ->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

    $query->where('a.state = 1');

    // Do not show trashed links on the front-end
    $query->where('a.state != -2');

    $search = $this->getState('list.filter');
    if (!empty($search)) {
        $search = '%' . trim($search) . '%';
        $query->where('(a.title LIKE :search)')
              ->bind(':search', $search);
    }

    $query->order(
        $db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' .
        $db->escape($this->getState('list.direction', 'ASC'))
    );

    $db->setQuery($query);
    return $query;
}

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app    = Factory::getApplication();

        $params = $app->getParams();
        $this->setState('params', $params);

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
        $this->setState('list.limit', $limit);

        $limitstart = $app->getInput()->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        // Optional filter text
        $this->setState('list.filter', $app->getInput()->getString('filter-search'));

        $orderCol = $app->getInput()->get('filter_order', 'ordering');

        if (!\in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'ordering';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->getInput()->get('filter_order_Dir', 'ASC');

        if (!\in_array(strtoupper($listOrder), ['ASC', 'DESC', ''])) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $id = $app->getInput()->get('id', 0, 'int');
        $this->setState('category.id', $id);

        $user = $this->getCurrentUser();

        if (!$user->authorise('core.edit.state', 'com_weblinks') && !$user->authorise('core.edit', 'com_weblinks')) {
            // Limit to published for people who can't edit or edit.state.
            $this->setState('filter.state', 1);

            // Filter by start and end dates.
            $this->setState('filter.publish_date', true);
        }

        $this->setState('filter.language', Multilanguage::isEnabled());
    }

    /**
     * Method to get category data for the current category
     *
     * @return  object
     *
     * @since   1.5
     */
    public function getCategory()
    {
        if (!\is_object($this->_item)) {
            $params = $this->getState('params', new Registry());

            $options               = [];
            $options['countItems'] = $params->get('show_cat_num_links_cat', 1)
                || $params->get('show_empty_categories', 0);

            $categories  = Categories::getInstance('Weblinks', $options);
            $this->_item = $categories->get($this->getState('category.id', 'root'));

            if (\is_object($this->_item)) {
                $this->_children = $this->_item->getChildren();
                $this->_parent   = false;

                if ($this->_item->getParent()) {
                    $this->_parent = $this->_item->getParent();
                }

                $this->_rightsibling = $this->_item->getSibling();
                $this->_leftsibling  = $this->_item->getSibling(false);
            } else {
                $this->_children = false;
                $this->_parent   = false;
            }
        }

        return $this->_item;
    }

    /**
     * Get the parent category
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function getParent()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_parent;
    }

    /**
     * Get the leftsibling (adjacent) categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getLeftSibling()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_leftsibling;
    }

    /**
     * Get the rightsibling (adjacent) categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getRightSibling()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_rightsibling;
    }

    /**
     * Get the child categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getChildren()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_children;
    }

    /**
     * Increment the hit counter for the category.
     *
     * @param   integer  $pk  Optional primary key of the category to increment.
     *
     * @return  boolean  True if successful; false otherwise and internal error set.
     *
     * @since   3.2
     */
    public function hit($pk = 0)
    {
        $hitcount = Factory::getApplication()->getInput()->getInt('hitcount', 1);

        if ($hitcount) {
            $pk    = (!empty($pk)) ? $pk : (int) $this->getState('category.id');
            $table = new Category($this->getDatabase());
            $table->load($pk);
            $table->hit($pk);
        }

        return true;
    }
}
