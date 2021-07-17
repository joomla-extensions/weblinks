<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Weblinks Component Model for a Weblink record
 *
 * @since  1.5
 */
class WeblinkModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var  string
	 */
	protected $_context = 'com_weblinks.weblink';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load the object state.
		$pk = $app->input->getInt('id');
		$this->setState('weblink.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = $app->getIdentity();

		if (!$user->authorise('core.edit.state', 'com_weblinks') && !$user->authorise('core.edit', 'com_weblinks'))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $pk  The id of the object to get.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$user = Factory::getApplication()->getIdentity();

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('weblink.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select($this->getState('item.select', 'a.*'))
					->from('#__weblinks AS a')
					->where($db->quoteName('a.id') . ' = :id')
					->bind(':id', $pk, ParameterType::INTEGER);

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
					->innerJoin('#__categories AS c on c.id = a.catid')
					->where('c.published > 0');

				// Join on user table.
				$query->select('u.name AS author')
					->join('LEFT', '#__users AS u on u.id = a.created_by');

				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
				}

				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
					->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				if (!$user->authorise('core.edit.state', 'com_weblinks') && !$user->authorise('core.edit', 'com_weblinks'))
				{
					// Filter by start and end dates.
					$nullDate = $db->getNullDate();
					$nowDate = Factory::getDate()->toSql();

					$query->where('(a.publish_up = :publishUpNullDate OR a.publish_up <= :publishUpNowDate)')
						->where('(a.publish_down = :publishDownNullDate OR a.publish_down >= :publishDownNowDate)')
						->bind(':publishUpNullDate', $nullDate)
						->bind(':publishDownNullDate', $nullDate)
						->bind(':publishUpNowDate', $nowDate)
						->bind(':publishDownNowDate', $nowDate);
				}

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					$query->whereIn($db->quoteName('a.state'), [$published, $archived]);
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data))
				{
					throw new \Exception(Text::_('COM_WEBLINKS_ERROR_WEBLINK_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.
				if ((is_numeric($published) || is_numeric($archived)) && (($data->state != $published) && ($data->state != $archived)))
				{
					throw new \Exception(Text::_('COM_WEBLINKS_ERROR_WEBLINK_NOT_FOUND'), 404);
				}

				// Convert parameter fields to objects.
				$data->params   = new Registry($data->params);
				$data->metadata = new Registry($data->metadata);

				// Compute access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$groups = $user->getAuthorisedViewLevels();
					$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
				}

				$this->_item[$pk] = $data;
			}
			catch (\Exception $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Weblink', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to increment the hit counter for the weblink
	 *
	 * @param   integer  $pk  Optional ID of the weblink.
	 *
	 * @return  boolean  True on success
	 */
	public function hit($pk = null)
	{
		if (empty($pk))
		{
			$pk = $this->getState('weblink.id');
		}

		return $this->getTable('Weblink')->hit($pk);
	}
}
