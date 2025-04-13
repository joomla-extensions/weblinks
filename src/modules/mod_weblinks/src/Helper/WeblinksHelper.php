<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Weblinks\Site\Helper;
// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

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
     * @param   Registry                 $params  The module parameters
     * @param   CMSApplicationInterface  $app     The application
     *
     * @return  array   Array containing all weblinks, including a separate entry for parent category weblinks
     *
     * @since   1.5
     */
    public function getWeblinks($params, $app)
    {
        $parentModel = $app->bootComponent('com_weblinks')->getMVCFactory()
            ->createModel('Category', 'Site', ['ignore_request' => true]);

        $cParams = ComponentHelper::getParams('com_weblinks');
        $parentModel->setState('params', $cParams);
        $parentModel->setState('list.start', 0);
        $parentModel->setState('list.limit', (int) $params->get('count', 5));
        $parentModel->setState('filter.state', 1);
        $parentModel->setState('filter.publish_date', true);
        $parentModel->setState('filter.access', !ComponentHelper::getParams('com_weblinks')->get('show_noauth'));
        $parentModel->setState('list.ordering', $params->get('ordering', 'ordering') == 'order' ? 'ordering' : $params->get('ordering'));
        $parentModel->setState('list.direction', $params->get('direction', 'asc'));
        $parentModel->setState('filter.language', $app->getLanguageFilter());
        $parentModel->setState('filter.c.published', 1);

        $parentWeblinks = [];
        $catid          = (int) $params->get('catid', 0);
        if ($catid) {
            $parentModel->setState('category.id', $catid);
            $parentModel->setState('category.group', 0);
            $case_when1 = ' CASE WHEN ' . $parentModel->getDbo()->getQuery(true)->charLength('a.alias', '!=', '0') .
                         ' THEN ' . $parentModel->getDbo()->getQuery(true)->concatenate([$parentModel->getDbo()->getQuery(true)->castAs('CHAR', 'a.id'), 'a.alias'], ':') .
                         ' ELSE ' . $parentModel->getDbo()->getQuery(true)->castAs('CHAR', 'a.id') . ' END as slug';
            $case_when2 = ' CASE WHEN ' . $parentModel->getDbo()->getQuery(true)->charLength('c.alias', '!=', '0') .
                         ' THEN ' . $parentModel->getDbo()->getQuery(true)->concatenate([$parentModel->getDbo()->getQuery(true)->castAs('CHAR', 'c.id'), 'c.alias'], ':') .
                         ' ELSE ' . $parentModel->getDbo()->getQuery(true)->castAs('CHAR', 'c.id') . ' END as catslug';
            $parentModel->setState('list.select', 'a.*, c.description AS c_description, c.published AS c_published,' . $case_when1 . ',' . $case_when2);
            $parentItems = $parentModel->getItems();
            if ($parentItems) {
                foreach ($parentItems as $item) {
                    $temp         = $item->params;
                    $item->params = clone $cParams;
                    $item->params->merge($temp);
                    $item->link = $item->params->get('count_clicks', 1) == 1
                        ? Route::_('index.php?option=com_weblinks&task=weblink.go&catid=' . $item->catslug . '&id=' . $item->slug)
                        : $item->url;
                    $parentWeblinks[] = $item;
                }
            }
        }

        $categoryModel = $app->bootComponent('com_weblinks')->getMVCFactory()
            ->createModel('Category', 'Site', ['ignore_request' => true]);
        $categoryModel->setState('params', $cParams);
        $categoryModel->setState('list.start', 0);
        $categoryModel->setState('list.limit', (int) $params->get('count', 5));
        $categoryModel->setState('filter.state', 1);
        $categoryModel->setState('filter.publish_date', true);
        $categoryModel->setState('filter.access', !ComponentHelper::getParams('com_weblinks')->get('show_noauth'));
        $categoryModel->setState('list.ordering', $params->get('ordering', 'ordering') == 'order' ? 'ordering' : $params->get('ordering'));
        $categoryModel->setState('list.direction', $params->get('direction', 'asc'));
        $categoryModel->setState('filter.language', $app->getLanguageFilter());
        $categoryModel->setState('filter.c.published', 1);

        if ($catid) {
            $categoryModel->setState('category.id', $catid);
            $categoryModel->setState('category.group', $params->get('groupby', 0));
            $categoryModel->setState('category.ordering', $params->get('groupby_ordering', 'c.lft'));
            $categoryModel->setState('category.direction', $params->get('groupby_direction', 'ASC'));
            $case_when1 = ' CASE WHEN ' . $categoryModel->getDbo()->getQuery(true)->charLength('a.alias', '!=', '0') .
                         ' THEN ' . $categoryModel->getDbo()->getQuery(true)->concatenate([$categoryModel->getDbo()->getQuery(true)->castAs('CHAR', 'a.id'), 'a.alias'], ':') .
                         ' ELSE ' . $categoryModel->getDbo()->getQuery(true)->castAs('CHAR', 'a.id') . ' END as slug';
            $case_when2 = ' CASE WHEN ' . $categoryModel->getDbo()->getQuery(true)->charLength('c.alias', '!=', '0') .
                         ' THEN ' . $categoryModel->getDbo()->getQuery(true)->concatenate([$categoryModel->getDbo()->getQuery(true)->castAs('CHAR', 'c.id'), 'c.alias'], ':') .
                         ' ELSE ' . $categoryModel->getDbo()->getQuery(true)->castAs('CHAR', 'c.id') . ' END as catslug';
            $categoryModel->setState('list.select', 'a.*, c.description AS c_description, c.published AS c_published,' . $case_when1 . ',' . $case_when2);
            $items = $categoryModel->getItems();
        } else {
            $items = [];
        }

        if ($items) {
            foreach ($items as $item) {
                $temp         = $item->params;
                $item->params = clone $cParams;
                $item->params->merge($temp);
                $item->link = $item->params->get('count_clicks', 1) == 1
                    ? Route::_('index.php?option=com_weblinks&task=weblink.go&catid=' . $item->catslug . '&id=' . $item->slug)
                    : $item->url;
            }
        } else {
            $items = [];
        }

        return [
            'parentWeblinks'   => $parentWeblinks,
            'categoryWeblinks' => $items,
        ];
    }

    /**
     * Retrieve list of weblinks
     *
     * @param   Registry                 $params  The module parameters
     * @param   CMSApplicationInterface  $app     The application
     *
     * @return  mixed   Null if no weblinks based on input parameters else an array containing all the weblinks.
     *
     * @since   1.5
     *
     * @deprecated 5.0 Use the none static function getWeblinks
     */
    public static function getList($params, $app)
    {
        return (new self())->getWeblinks($params, $app);
    }
}
