<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Weblinks\Site\Helper;

// phpcs:ignoreFile -- allow _JEXEC check for Joomla module security
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\CMS\Router\Route;

/**
 * Helper for mod_weblinks
 *
 * @since  1.5
 */
class WeblinksHelper
{
    /**
     * Retrieve list of weblinks
     *
     * @param   \Joomla\Registry\Registry  $params  The module parameters
     * @param   \Joomla\CMS\Application\CMSApplicationInterface  $app     The application
     *
     * @return  array   Array containing all weblinks, including a separate entry for parent category weblinks
     *
     * @since   1.5
     */
    public function getWeblinks($params, $app)
    {
        $db             = JoomlaFactory::getDbo();
        $catid          = (int) $params->get('catid', 0);
        $parentWeblinks = [];
        $allWeblinks    = [];

        if ($catid > 0) {
            $cParams = ComponentHelper::getParams('com_weblinks');

            $categoryQuery = $db->getQuery(true)
                ->select('lft, rgt')
                ->from('#__categories')
                ->where('id = ' . (int) $catid)
                ->where('extension = ' . $db->quote('com_weblinks'))
                ->where('published = 1');
            $db->setQuery($categoryQuery);
            $rootCategory = $db->loadObject();

            if ($rootCategory) {
                $categoryIdsQuery = $db->getQuery(true)
                    ->select('id')
                    ->from('#__categories')
                    ->where('extension = ' . $db->quote('com_weblinks'))
                    ->where('published = 1')
                    ->where('lft >= ' . (int) $rootCategory->lft)
                    ->where('rgt <= ' . (int) $rootCategory->rgt);
                $db->setQuery($categoryIdsQuery);
                $categoryIds = $db->loadColumn();

                if (!empty($categoryIds)) {
                    $weblinksQuery = $db->getQuery(true)
                        ->select('a.*, c.title AS category_title')
                        ->from('#__weblinks AS a')
                        ->join('LEFT', '#__categories AS c ON a.catid = c.id')
                        ->where('a.state = 1')
                        ->where('a.catid IN (' . implode(',', $categoryIds) . ')');

                    if ($app->getLanguageFilter()) {
                        $weblinksQuery->where('a.language IN (' . $db->quote($app->getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
                    }

                    $weblinksQuery->order($params->get('ordering', 'ordering') . ' ' . $params->get('direction', 'ASC'));
                    $db->setQuery($weblinksQuery);
                    $items = $db->loadObjectList();

                    foreach ($items as $item) {
                        $item->params  = clone $cParams;
                        $item->slug    = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                        $item->catslug = $item->catid . ':' . (isset($item->category_alias) ? $item->category_alias : $item->category_title);
                        $item->link    = $item->params->get('count_clicks', 1) == 1
                            ? Route::_('index.php?option=com_weblinks&task=weblink.go&catid=' . $item->catslug . '&id=' . $item->slug)
                            : $item->url;

                        $allWeblinks[] = $item;
                        if ($item->catid == $catid) {
                            $parentWeblinks[] = $item;
                        }
                    }
                }
            }
        }

        file_put_contents('debug.log', "Fetched Weblinks: " . print_r($allWeblinks, true) . "\n", FILE_APPEND);

        return [
            'parentWeblinks'   => $parentWeblinks,
            'categoryWeblinks' => $allWeblinks,
        ];
    }

    /**
     * Retrieve list of weblinks
     *
     * @param   \Joomla\Registry\Registry  $params  The module parameters
     * @param   \Joomla\CMS\Application\CMSApplicationInterface  $app     The application
     *
     * @return  mixed   Null if no weblinks based on input parameters else an array containing all the weblinks.
     *
     * @since   1.5
     *
     * @deprecated 5.0 Use the non-static function getWeblinks
     */
    public static function getList($params, $app)
    {
        return (new self())->getWeblinks($params, $app);
    }
}
