<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\Model;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
/**
 * This models supports retrieving lists of article categories.
 *
 * @since  1.6
 */
class CategoriesModel extends ListModel
{
    /**
     * Context string for the model type.  This is used to handle uniqueness
     * when dealing with the getStoreId() method and caching data structures.
     *
     * @var  string
     */
    protected $context = 'com_weblinks.categories';
    /**
         * The category context (allows other extensions to derived from this model).
         *
         * @var  string
         */
    protected $_extension = 'com_weblinks';
    /**
         * Parent category
         *
         * @var CategoryNode|null
         */
    private $_parent = null;
    /**
         * Categories data
         *
         * @var false|array
         */
    private $_items = null;
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
        $app = Factory::getApplication();
        $this->setState('filter.extension', $this->_extension);
        // Get the parent id if defined.
        $parentId = $app->input->getInt('id');
        $this->setState('filter.parentId', $parentId);
        $params = $app->getParams();
        $this->setState('params', $params);
        $this->setState('filter.published', 1);
        $this->setState('filter.access', true);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.parentId');
        return parent::getStoreId($id);
    }

    /**
     * Redefine the function and add some properties to make the styling more easy
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        if ($this->_items === null) {
            $params                = $this->getState('params', new Registry());
            $options               = [];
            $options['access']     = $this->getState('filter.access');
            $options['published']  = $this->getState('filter.published');
            $options['countItems'] = $params->get('show_cat_num_links', 1) || !$params->get('show_empty_categories_cat', 0);
            $categories            = Categories::getInstance('Weblinks', $options);
            $this->_parent         = $categories->get($this->getState('filter.parentId', 'root'));
            if (is_object($this->_parent)) {
                $this->_items = $this->_parent->getChildren();
            } else {
                $this->_items = false;
            }
        }

        return $this->_items;
    }

    /**
     * Get the parent
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getParent()
    {
        if (!is_object($this->_parent)) {
            $this->getItems();
        }

        return $this->_parent;
    }
}
