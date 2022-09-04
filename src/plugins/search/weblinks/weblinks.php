<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Search\Administrator\Helper\SearchHelper;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;
use Joomla\Database\ParameterType;

/**
 * Weblinks search plugin.
 *
 * @since  1.6
 */
class PlgSearchWeblinks extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    \Joomla\CMS\Application\CMSApplicationInterface
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Database Driver Instance
	 *
	 * @var    \Joomla\Database\DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'weblinks' => 'PLG_SEARCH_WEBLINKS_WEBLINKS'
		);

		return $areas;
	}

	/**
	 * Search content (weblinks).
	 *
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db     = $this->db;
		$groups = $this->app->getIdentity()->getAuthorisedViewLevels();

		$searchText = $text;

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$sContent = $this->params->get('search_content', 1);
		$sArchived = $this->params->get('search_archived', 1);
		$limit = $this->params->def('search_limit', 50);
		$state = array();

		if ($sContent)
		{
			$state[] = 1;
		}

		if ($sArchived)
		{
			$state[] = 2;
		}

		if (empty($state))
		{
			return array();
		}

		$text = trim($text);

		if ($text == '')
		{
			return array();
		}

		$searchWeblinks = Text::_('PLG_SEARCH_WEBLINKS');

		switch ($phrase)
		{
			case 'exact':
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.url LIKE ' . $text;
				$wheres2[] = 'a.description LIKE ' . $text;
				$wheres2[] = 'a.title LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();

				foreach ($words as $word)
				{
					$word = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.url LIKE ' . $word;
					$wheres2[] = 'a.description LIKE ' . $word;
					$wheres2[] = 'a.title LIKE ' . $word;
					$wheres[] = implode(' OR ', $wheres2);
				}

				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
		}

		$query = $db->getQuery(true);

		// SQLSRV changes.
		$caseWhen = ' CASE WHEN ';
		$caseWhen .= $query->charLength('a.alias', '!=', '0');
		$caseWhen .= ' THEN ';
		$a_id = $query->castAs('CHAR', 'a.id');
		$caseWhen .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$caseWhen .= ' ELSE ';
		$caseWhen .= $a_id . ' END as slug';

		$caseWhen1 = ' CASE WHEN ';
		$caseWhen1 .= $query->charLength('c.alias', '!=', '0');
		$caseWhen1 .= ' THEN ';
		$c_id = $query->castAs('CHAR', 'c.id');
		$caseWhen1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$caseWhen1 .= ' ELSE ';
		$caseWhen1 .= $c_id . ' END as catslug';

		$query->select('a.title AS title, a.created AS created, a.url, a.description AS text, ' . $caseWhen . "," . $caseWhen1)
			->select($query->concatenate(array($db->quote($searchWeblinks), 'c.title'), " / ") . ' AS section')
			->select('\'1\' AS browsernav')
			->from('#__weblinks AS a')
			->join('INNER', '#__categories as c ON c.id = a.catid')
			->where('(' . $where . ')')
			->whereIn($db->quoteName('a.state'), $state)
			->where($db->quoteName('c.published') . ' = 1')
			->whereIn($db->quoteName('c.access'), $groups)
			->order($order);

		// Filter by language.
		if ($this->app->isClient('site') && Multilanguage::isEnabled())
		{
			$languages = [$this->app->getLanguage()->getTag(), '*'];
			$query->whereIn($db->quoteName('a.language'), $languages, ParameterType::STRING)
				->whereIn($db->quoteName('c.language'), $languages, ParameterType::STRING);
		}

		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		$return = array();

		if ($rows)
		{
			foreach ($rows as $key => $row)
			{
				$rows[$key]->href = RouteHelper::getWeblinkRoute($row->slug, $row->catslug);
			}

			foreach ($rows as $weblink)
			{
				if (SearchHelper::checkNoHTML($weblink, $searchText, array('url', 'text', 'title')))
				{
					$return[] = $weblink;
				}
			}
		}

		return $return;
	}
}
