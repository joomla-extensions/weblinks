<?php

/**
 * @package     Joomla.Site
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\Model;

defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\CMS\Language\Text;
/**
 * Weblinks model for the Joomla Weblinks component.
 *
 * @since  1.6
 */
class WeblinksModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'state', 'a.state',
                'access', 'a.access',
                'ag.title', 'access_level',
                'hits', 'a.hits',
                'ordering', 'a.ordering',
                'catid', 'a.catid',
                'c.title', 'category_title',
                'language', 'a.language',
                'l.title', 'language_title',
                'tag',
                'association',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.title', $direction = 'asc')
    {
        $app = Factory::getApplication();
// Load the parameters
        $params = ComponentHelper::getParams('com_weblinks');
        $this->setState('params', $params);
// Load the filters
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.published', $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string'));
        $this->setState('filter.category_id', $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'int'));
        $this->setState('filter.access', $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'int'));
        $this->setState('filter.language', $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string'));
        $this->setState('filter.tag', $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '', 'int'));
// List state information
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.tag');
        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $user  = $this->getCurrentUser();
// Select the required fields
        $query->select('a.id, a.title, a.alias, a.url, a.state, a.ordering, a.access, a.hits, a.catid, a.language, a.checked_out, a.checked_out_time, ' .
            'a.created, a.created_by, a.publish_up, a.publish_down')
              ->from($db->quoteName('#__weblinks', 'a'));
// Join over access groups
        $query->select($db->quoteName('ag.title', 'access_level'))
              ->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));
// Join over categories
        $query->select($db->quoteName('c.title', 'category_title'))
              ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'));
// Join over languages
        $query->select($db->quoteName('l.title', 'language_title'))
              ->select($db->quoteName('l.image', 'language_image'))
              ->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));
// Join over users for checked out user
        $query->select($db->quoteName('uc.name', 'editor'))
              ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));
// Join over associations
        if (Associations::isEnabled()) {
            $query->select('COUNT(asso2.id)>1 AS association')
                  ->join('LEFT', $db->quoteName('#__associations', 'asso') . ' ON ' . $db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('asso.context') . ' = ' . $db->quote('com_weblinks.item'))
                  ->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key'))
                  ->group('a.id, ag.title, c.title, l.title, l.image, uc.name');
        }

        // Filter by access level
        if ($access = $this->getState('filter.access')) {
            $query->where($db->quoteName('a.access') . ' = :access')
                  ->bind(':access', $access, ParameterType::INTEGER);
        } elseif (!$user->authorise('core.admin')) {
            $query->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels());
        }

        // Filter by published state
        $published = (string) $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where($db->quoteName('a.state') . ' = :state')
                  ->bind(':state', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->whereIn($db->quoteName('a.state'), [0, 1]);
        }

        // Filter by category
        $categoryId = $this->getState('filter.category_id');
        if (is_numeric($categoryId)) {
            $query->where($db->quoteName('a.catid') . ' = :catid')
                  ->bind(':catid', $categoryId, ParameterType::INTEGER);
        }

        // Filter by language
        $language = $this->getState('filter.language');
        if ($language) {
            $query->where($db->quoteName('a.language') . ' = :language')
                  ->bind(':language', $language);
        }

        // Filter by tag
        $tagId = $this->getState('filter.tag');
        if (is_numeric($tagId)) {
            $query->where($db->quoteName('tagmap.tag_id') . ' = :tagId')
                  ->bind(':tagId', $tagId, ParameterType::INTEGER)
                  ->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
                      . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
                      . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_weblinks.weblink'));
        }

        // Filter by search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')
                      ->bind(':id', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%');
                $query->where('(' . $db->quoteName('a.title') . ' LIKE :title OR ' . $db->quoteName('a.alias') . ' LIKE :alias)')
                  ->bind(':title', $search)
                  ->bind(':alias', $search);
            }
        }

        // Add list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'a.title');
        $orderDirn = $this->state->get('list.direction', 'asc');
        if ($orderCol === 'a.ordering' || $orderCol === 'category_title') {
            $orderCol = 'c.title ' . $orderDirn . ', a.ordering';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        return $query;
    }

    /**
     * Method to get the filter form.
     *
     * @param   array    $data     Data for the form.
     * @param   boolean  $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|null  The form object or null if the form could not be loaded.
     *
     * @since   1.6
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        // Specify the filter form name explicitly
        $form = parent::getFilterForm($data, $loadData, ['name' => 'filter_weblinks']);
        if ($form === null) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_WEBLINKS_ERROR_FILTER_FORM_NOT_FOUND'), 'error');
        }

        return $form;
    }

    /**
     * Method to publish/unpublish/archive/trash weblinks.
     *
     * @param   array    $ids    The IDs of the items to publish.
     * @param   integer  $state  The state to set (1 = published, 0 = unpublished, 2 = archived, -2 = trashed).
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function publish($ids, $state)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $ids   = array_map('intval', (array) $ids);
        try {
            $query->update($db->quoteName('#__weblinks'))
                  ->set($db->quoteName('state') . ' = :state')
                  ->whereIn($db->quoteName('id'), $ids)
                  ->bind(':state', $state, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to delete weblinks.
     *
     * @param   array  $ids  The IDs of the items to delete.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function delete($ids)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $ids   = array_map('intval', (array) $ids);
        try {
            $query->delete($db->quoteName('#__weblinks'))
                  ->whereIn($db->quoteName('id'), $ids);
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to check in weblinks.
     *
     * @param   array  $ids  The IDs of the items to check in.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function checkin($ids)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $ids   = array_map('intval', (array) $ids);
        $user  = $this->getCurrentUser();
        try {
            $query->update($db->quoteName('#__weblinks'))
                  ->set($db->quoteName('checked_out') . ' = 0')
                  ->set($db->quoteName('checked_out_time') . ' = :nullDate')
                  ->whereIn($db->quoteName('id'), $ids)
                  ->where($db->quoteName('checked_out') . ' = :userId')
                  ->bind(':nullDate', $db->getNullDate())
                  ->bind(':userId', $user->id, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to perform batch operations.
     *
     * @param   array   $ids      The IDs of the items.
     * @param   string  $command  The batch command (e.g., 'access', 'category', 'language').
     * @param   string  $value    The value to set.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function batchProcess($ids, $command, $value)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $ids   = array_map('intval', (array) $ids);
        try {
            switch ($command) {
                case 'access':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             $query->update($db->quoteName('#__weblinks'))
                          ->set($db->quoteName('access') . ' = :value')
                          ->whereIn($db->quoteName('id'), $ids)
                          ->bind(':value', $value, ParameterType::INTEGER);

                    break;
                case 'category':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             $query->update($db->quoteName('#__weblinks'))
                          ->set($db->quoteName('catid') . ' = :value')
                          ->whereIn($db->quoteName('id'), $ids)
                          ->bind(':value', $value, ParameterType::INTEGER);

                    break;
                case 'language':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             $query->update($db->quoteName('#__weblinks'))
                          ->set($db->quoteName('language') . ' = :value')
                          ->whereIn($db->quoteName('id'), $ids)
                          ->bind(':value', $value);

                    break;
                default:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             $this->setError(Text::_('COM_WEBLINKS_INVALID_BATCH_COMMAND'));

                    return false;
            }
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to save the reordered items.
     *
     * @param   array  $pks    The IDs of the items.
     * @param   array  $order  The new order values.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function saveorder($pks, $order)
    {
        $db    = $this->getDatabase();
        $table = $this->getTable();
        $conditions = [];
        try {
            foreach ($pks as $i => $pk) {
                $table->load((int) $pk);
                if ($table->ordering != $order[$i]) {
                    $table->ordering = $order[$i];
                    if (!$table->store()) {
                        throw new \Exception($table->getError());
                    }
                    $condition = [$table->getColumnAlias('catid') . '=' . (int) $table->catid];
                    if (!in_array($condition, $conditions)) {
                        $conditions[] = $condition;
                    }
                }
            }
            foreach ($conditions as $cond) {
                $table->load($cond);
                $table->reorder($cond);
            }
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table type.
     * @param   string  $prefix  The class prefix.
     * @param   array   $config  Configuration array for the table.
     *
     * @return  \Joomla\CMS\Table\Table
     *
     * @since   1.6
     */
    public function getTable($type = 'Weblink', $prefix = 'Table', $config = [])
    {
        return Table::getInstance($type, $prefix, $config);
    }
}
