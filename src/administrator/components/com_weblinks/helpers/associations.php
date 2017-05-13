<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JTable::addIncludePath(__DIR__ . '/../tables');

/**
 * Content associations helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class WeblinksAssociationsHelper extends JAssociationExtensionHelper
{
	/**
	 * The extension name
	 *
	 * @var     array   $extension
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $extension = 'com_weblinks';

	/**
	 * Array of item types
	 *
	 * @var     array   $itemTypes
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $itemTypes = array('weblink', 'category');

	/**
	 * Has the extension association support
	 *
	 * @var     boolean   $associationsSupport
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $associationsSupport = true;

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $typeName  The item type
	 * @param   int     $id        The id of item for which we need the associated items
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAssociations($typeName, $id)
	{
		$type = $this->getType($typeName);

		$context    = $this->extension . '.item';
		$catidField = 'catid';

		if ($typeName === 'category')
		{
			$context    = 'com_categories.item';
			$catidField = '';
		}

		// Get the associations.
		$associations = JLanguageAssociations::getAssociations(
			$this->extension,
			$type['tables']['a'],
			$context,
			$id,
			'id',
			'alias',
			$catidField
		);

		return $associations;
	}

	/**
	 * Get item information
	 *
	 * @param   string  $typeName  The item type
	 * @param   int     $id        The id of item for which we need the associated items
	 *
	 * @return  JTable|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($typeName, $id)
	{
		if (empty($id))
		{
			return null;
		}

		$table = null;

		switch ($typeName)
		{
			case 'weblink':
				$table = JTable::getInstance('Weblink', 'WeblinksTable');
				break;

			case 'category':
				$table = JTable::getInstance('Category');
				break;
		}

		if (empty($table))
		{
			return null;
		}

		$table->load($id);

		return $table;
	}

	/**
	 * Get information about the type
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of item types
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getType($typeName = '')
	{
		$fields  = $this->getFieldsTemplate();
		$tables  = array();
		$joins   = array();
		$support = $this->getSupportTemplate();
		$title   = '';

		if (in_array($typeName, $this->itemTypes))
		{
			switch ($typeName)
			{
				case 'weblink':

					$support['state'] = true;
					$support['acl'] = true;
					$support['checkout'] = true;
					$support['category'] = true;
					$support['save2copy'] = true;

					$tables = array(
						'a' => '#__weblinks'
					);

					$title = 'weblink';
					break;

				case 'category':
					$fields['created_user_id'] = 'a.created_user_id';
					$fields['ordering']        = 'a.lft';
					$fields['level']           = 'a.level';
					$fields['catid']           = '';
					$fields['state']           = 'a.published';

					$support['state']    = true;
					$support['acl']      = true;
					$support['checkout'] = true;
					$support['level'] = true;

					$tables = array(
						'a' => '#__categories'
					);

					$title = 'category';
					break;
			}
		}

		return array(
			'fields'  => $fields,
			'support' => $support,
			'tables'  => $tables,
			'joins'   => $joins,
			'title'   => $title
		);
	}
}
