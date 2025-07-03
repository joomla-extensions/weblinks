<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Weblinks\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
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
     * @return  array   Array containing all the weblinks.
     *
     * @since   __DEPLOY_VERSION__
     **/
    public function getWeblinks($params, $app)
    {
        // @var \Joomla\Component\Weblinks\Site\Model\CategoryModel $model
        if ($params->get('groupby')) {
            return $this->getCategoryTree($params->get('catid', 1), $params, $app, 0, $params->get('maxLevel', -1)) ?? [];
        }

        return $this->getCategoryWeblinks($params->get('catid', 1), $params, $app);
    }

    /**
     * Recursively retrieves a category, its weblinks, and its children categories to build a tree structure.
     *
     * @param   int                      $catid     The ID of the parent category to start building the tree from.
     * @param   Registry                 $params    The module parameters.
     * @param   CMSApplicationInterface  $app       The application object.
     * @param   int                      $level     The current depth level of the recursion.
     * @param   int                      $maxLevel  The maximum depth to recurse.
     *
     * @return  \stdClass|null  An object representing the category tree, or null if the initial category is not found.
     */
    private function getCategoryTree($catid, $params, $app, $level, $maxLevel)
    {
        $categories = Factory::getApplication()->bootComponent('com_weblinks')->getCategory();
        $category   = $categories->get($catid);
        $tree       = null;

        if ($category) {
            $tree           = new \stdClass();
            $tree->category = $category;
            $tree->weblinks = $this->getCategoryWeblinks($category->id, $params, $app);
            $tree->children = [];

            if ($maxLevel == -1 || $level < $maxLevel) {
                $children = $category->getChildren();

                foreach ($children as $child) {
                    $tree->children[] = $this->getCategoryTree($child->id, $params, $app, $level + 1, $maxLevel);
                }
            }
        }

        return $tree;
    }

    /**
     * Retrieve a list of weblinks for a specific category.
     *
     * @param   int                      $catid   The ID of the category.
     * @param   Registry                 $params  The module parameters.
     * @param   CMSApplicationInterface  $app     The application object.
     *
     * @return  array  An array of weblink items.
     */
    private function getCategoryWeblinks($catid, $params, $app)
    {
        $model = $app->bootComponent('com_weblinks')->getMVCFactory()
            ->createModel('Category', 'Site', ['ignore_request' => true]);

        // Set application parameters in model
        $cParams = ComponentHelper::getParams('com_weblinks');
        $model->setState('params', $cParams);

        // Set the filters based on the module params
        $model->setState('list.start', 0);
        $model->setState('list.limit', (int) $params->get('count', 5));

        $model->setState('filter.state', 1);
        $model->setState('filter.publish_date', true);

        // Access filter
        $access = !ComponentHelper::getParams('com_weblinks')->get('show_noauth');
        $model->setState('filter.access', $access);

        $ordering = $params->get('ordering', 'ordering');
        $model->setState('list.ordering', $ordering == 'order' ? 'ordering' : $ordering);
        $model->setState('list.direction', $params->get('direction', 'asc'));

        $model->setState('category.id', $catid);
        $model->setState('category.ordering', $params->get('groupby_ordering', 'c.lft'));
        $model->setState('category.direction', $params->get('groupby_direction', 'ASC'));

        // Create query object
        $db    = $model->getDbo();
        $query = $db->getQuery(true);

        $case_when1 = ' CASE WHEN ';
        $case_when1 .= $query->charLength('a.alias', '!=', '0');
        $case_when1 .= ' THEN ';
        $a_id       = $query->castAs('CHAR', 'a.id');
        $case_when1 .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when1 .= ' ELSE ';
        $case_when1 .= $a_id . ' END as slug';

        $case_when2 = ' CASE WHEN ';
        $case_when2 .= $query->charLength('c.alias', '!=', '0');
        $case_when2 .= ' THEN ';
        $c_id       = $query->castAs('CHAR', 'c.id');
        $case_when2 .= $query->concatenate([$c_id, 'c.alias'], ':');
        $case_when2 .= ' ELSE ';
        $case_when2 .= $c_id . ' END as catslug';

        $model->setState(
            'list.select',
            'a.*, c.description AS c_description, c.published AS c_published,' . $case_when1 . ',' . $case_when2
        );

        $model->setState('filter.c.published', 1);

        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());

        $items = $model->getItems();

        if ($items) {
            foreach ($items as $item) {
                $temp         = $item->params;
                $item->params = clone $cParams;
                $item->params->merge($temp);

                if ($item->params->get('count_clicks', 1) == 1) {
                    $item->link = Route::_('index.php?option=com_weblinks&task=weblink.go&catid=' . $item->catslug . '&id=' . $item->slug);
                } else {
                    $item->link = $item->url;
                }
            }

            return $items;
        }

        return [];
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
     **/
    public static function getList($params, $app)
    {
        return (new self())->getWeblinks($params, $app);
    }
}
