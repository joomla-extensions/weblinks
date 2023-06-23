<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Category;

use Joomla\CMS\MVC\View\CategoryView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
/**
 * HTML View class for the WebLinks component
 *
 * @since  1.5
 */
class HtmlView extends CategoryView
{
    /**
     * @var    string  The name of the extension for the category
     * @since  3.2
     */
    protected $extension = 'com_weblinks';
    /**
         * Execute and display a template script.
         *
         * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
         *
         * @return  mixed  A string if successful, otherwise a Error object.
         */
    public function display($tpl = null)
    {
        parent::commonCategoryDisplay();
        // Prepare the data.
        // Compute the weblink slug & link url.
        foreach ($this->items as $item) {
            $item->slug   = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
            $temp         = $item->params;
            $item->params = clone $this->params;
            $item->params->merge($temp);
            if ($item->params->get('count_clicks', 1) == 1) {
                $item->link = Route::_('index.php?option=com_weblinks&task=weblink.go&id=' . $item->id);
            } else {
                $item->link = $item->url;
            }
        }

        return parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function prepareDocument()
    {
        parent::prepareDocument();
        parent::addFeed();
        if ($this->menuItemMatchCategory) {
            // If the active menu item is linked directly to the category being displayed, no further process is needed
            return;
        }

        // Get ID of the category from active menu item
        $menu = $this->menu;

        if (
            $menu && $menu->component == 'com_weblinks' && isset($menu->query['view'])
            && in_array($menu->query['view'], ['categories', 'category'])
        ) {
            $id = $menu->query['id'];
        } else {
            $id = 0;
        }

        $path     = [['title' => $this->category->title, 'link' => '']];
        $category = $this->category->getParent();
        while ($category !== null && $category->id != $id && $category->id !== 'root') {
            $path[]   = ['title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id, $category->language)];
            $category = $category->getParent();
        }

        $path = array_reverse($path);
        foreach ($path as $item) {
            $this->pathway->addItem($item['title'], $item['link']);
        }
    }
}
