<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Weblink Table class
 *
 * @since  1.5
 */
class WeblinkTable extends Table implements VersionableTableInterface, TaggableTableInterface
{
	use TaggableTableTrait;
	/**
	 * Ensure the params and metadata in json encoded in the bind method
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $_jsonEncode = array('params', 'metadata', 'images');

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  &$db  A database connector object
	 *
	 * @since   1.5
	 */
	public function __construct(&$db)
	{
		$this->typeAlias = 'com_weblinks.weblink';

		parent::__construct('#__weblinks', 'id', $db);

		// Set the published column alias
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Overload the store method for the Weblinks table.
	 *
	 * @param   boolean  $updateNulls  Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		$this->modified = $date->toSql();

		if ($this->id)
		{
			// Existing item
			$this->modified_by = $user->id;
		}
		else
		{
			// New weblink. A weblink created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->id;
			}
		}

		// Set publish_up to null date if not set
		if (!$this->publish_up)
		{
			$this->publish_up = $this->getDbo()->getNullDate();
		}

		// Set publish_down to null date if not set
		if (!$this->publish_down)
		{
			$this->publish_down = $this->getDbo()->getNullDate();
		}

		// Verify that the alias is unique
		$table = new WeblinkTable($this->getDbo());

		if ($table->load(array('language' => $this->language, 'alias' => $this->alias, 'catid' => $this->catid))
			&& ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(\JText::_('COM_WEBLINKS_ERROR_UNIQUE_ALIAS'));

			return false;
		}

		// Convert IDN urls to punycode
		$this->url = PunycodeHelper::urlToPunycode($this->url);

		// Set default value for xreference
		if ($this->xreference === null)
		{
			$this->xreference = '';
		}

		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.5
	 */
	public function check()
	{
		if (\JFilterInput::checkAttribute(array('href', $this->url)))
		{
			$this->setError(\JText::_('COM_WEBLINKS_ERR_TABLES_PROVIDE_URL'));

			return false;
		}

		// Check for valid name
		if (trim($this->title) == '')
		{
			$this->setError(\JText::_('COM_WEBLINKS_ERR_TABLES_TITLE'));

			return false;
		}

		// Check for existing name
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__weblinks'))
			->where($db->quoteName('title') . ' = ' . $db->quote($this->title))
			->where($db->quoteName('language') . ' = ' . $db->quote($this->language))
			->where($db->quoteName('catid') . ' = ' . (int) $this->catid);
		$db->setQuery($query);

		$xid = (int) $db->loadResult();

		if ($xid && $xid != (int) $this->id)
		{
			$this->setError(\JText::_('COM_WEBLINKS_ERR_TABLES_NAME'));

			return false;
		}

		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = Factory::getDate()->format("Y-m-d-H-i-s");
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(\JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

			return false;
		}

		/*
		 * Clean up keywords -- eliminate extra spaces between phrases
		 * and cr (\r) and lf (\n) characters from string
		 */
		if (!empty($this->metakey))
		{
			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", "<", ">");
			$after_clean    = StringHelper::str_ireplace($bad_characters, "", $this->metakey);
			$keys           = explode(',', $after_clean);
			$clean_keys     = array();

			foreach ($keys as $key)
			{
				// Ignore blank keywords
				if (trim($key))
				{
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(", ", $clean_keys);
		}

		return parent::check();
	}

	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
}
