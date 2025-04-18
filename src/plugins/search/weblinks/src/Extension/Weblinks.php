<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Search\Weblinks\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Search\Administrator\Helper\SearchHelper;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Weblinks search plugin.
 *
 * @since  1.6
 */
final class Weblinks extends CMSPlugin
{
    use DatabaseAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor
     *
     * @param   DispatcherInterface      $dispatcher
     * @param   array                    $config
     * @param   CMSApplicationInterface  $application
     * @param   DatabaseInterface        $database
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, CMSApplicationInterface $application, DatabaseInterface $database)
    {
        parent::__construct($dispatcher, $config);

        $this->setApplication($application);
        $this->setDatabase($database);
    }

    /**
     * Determine areas searchable by this plugin.
     *
     * @return  array  An array of search areas.
     *
     * @since   1.6
     */
    public function onContentSearchAreas()
    {
        static $areas = [
            'weblinks' => 'PLG_SEARCH_WEBLINKS_WEBLINKS',
        ];

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
        $app    = $this->getApplication();
        $db     = $this->getDatabase();
        $groups = $app->getIdentity()->getAuthorisedViewLevels();

        $searchText = $text;

        if (
            \is_array($areas)
            && !array_intersect($areas, array_keys($this->onContentSearchAreas()))
        ) {
            return [];
        }

        $sContent  = $this->params->get('search_content', 1);
        $sArchived = $this->params->get('search_archived', 1);
        $limit     = $this->params->def('search_limit', 50);
        $state     = [];

        if ($sContent) {
            $state[] = 1;
        }

        if ($sArchived) {
            $state[] = 2;
        }

        if (empty($state)) {
            return [];
        }

        $text = trim($text);

        if ($text == '') {
            return [];
        }

        $searchWeblinks = Text::_('PLG_SEARCH_WEBLINKS');

        switch ($phrase) {
            case 'exact':
                $text      = $db->quote('%' . $db->escape($text, true) . '%', false);
                $wheres2   = [];
                $wheres2[] = 'a.url LIKE ' . $text;
                $wheres2[] = 'a.description LIKE ' . $text;
                $wheres2[] = 'a.title LIKE ' . $text;
                $where     = '(' . implode(') OR (', $wheres2) . ')';
                break;

            case 'all':
            case 'any':
            default:
                $words  = explode(' ', $text);
                $wheres = [];

                foreach ($words as $word) {
                    $word      = $db->quote('%' . $db->escape($word, true) . '%', false);
                    $wheres2   = [];
                    $wheres2[] = 'a.url LIKE ' . $word;
                    $wheres2[] = 'a.description LIKE ' . $word;
                    $wheres2[] = 'a.title LIKE ' . $word;
                    $wheres[]  = implode(' OR ', $wheres2);
                }

                $where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
                break;
        }

        switch ($ordering) {
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
        $a_id     = $query->castAs('CHAR', 'a.id');
        $caseWhen .= $query->concatenate([$a_id, 'a.alias'], ':');
        $caseWhen .= ' ELSE ';
        $caseWhen .= $a_id . ' END as slug';

        $caseWhen1 = ' CASE WHEN ';
        $caseWhen1 .= $query->charLength('c.alias', '!=', '0');
        $caseWhen1 .= ' THEN ';
        $c_id      = $query->castAs('CHAR', 'c.id');
        $caseWhen1 .= $query->concatenate([$c_id, 'c.alias'], ':');
        $caseWhen1 .= ' ELSE ';
        $caseWhen1 .= $c_id . ' END as catslug';

        $query->select('a.title AS title, a.created AS created, a.url, a.description AS text, ' . $caseWhen . "," . $caseWhen1)
            ->select($query->concatenate([$db->quote($searchWeblinks), 'c.title'], " / ") . ' AS section')
            ->select('\'1\' AS browsernav')
            ->from('#__weblinks AS a')
            ->join('INNER', '#__categories as c ON c.id = a.catid')
            ->where('(' . $where . ')')
            ->whereIn($db->quoteName('a.state'), $state)
            ->where($db->quoteName('c.published') . ' = 1')
            ->whereIn($db->quoteName('c.access'), $groups)
            ->order($order);

        // Filter by language.

        if ($app->isClient('site') && Multilanguage::isEnabled()) {
            $languages = [$app->getLanguage()->getTag(), '*'];
            $query->whereIn($db->quoteName('a.language'), $languages, ParameterType::STRING)
                ->whereIn($db->quoteName('c.language'), $languages, ParameterType::STRING);
        }

        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList();

        $return = [];

        if ($rows) {
            foreach ($rows as $key => $row) {
                $rows[$key]->href = RouteHelper::getWeblinkRoute($row->slug, $row->catslug);
            }

            $return = $rows;
        }

        return $return;
    }
}
