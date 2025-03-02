<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\Component\Categories\Administrator\Helper\CategoryAssociationHelper;

/**
 * Weblinks Component Association Helper
 *
 * @since  3.0
 */
abstract class AssociationHelper extends CategoryAssociationHelper
{
    /**
     * Method to get the associations for a given item
     *
     * @param   integer  $id    Id of the item
     * @param   string   $view  Name of the view
     *
     * @return  array   Array of associations for the item
     *
     * @since   3.0
     */
    public static function getAssociations($id = 0, $view = null)
    {
        $input = Factory::getApplication()->input;
        $view  = \is_null($view) ? $input->get('view') : $view;
        $id    = empty($id) ? $input->getInt('id') : $id;
        if ($view === 'weblink') {
            if ($id) {
                $associations = Associations::getAssociations('com_weblinks', '#__weblinks', 'com_weblinks.item', $id);
                $return       = [];
                foreach ($associations as $tag => $item) {
                    $return[$tag] = RouteHelper::getWeblinkRoute($item->id, (int) $item->catid, $item->language);
                }

                return $return;
            }
        }

        if ($view == 'category' || $view == 'categories') {
            return self::getCategoryAssociations($id, 'com_weblinks');
        }

        return [];
    }
}
