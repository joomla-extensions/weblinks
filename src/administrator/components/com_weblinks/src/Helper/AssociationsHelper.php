<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Association\AssociationExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Table\Category;
use Joomla\CMS\Table\Table;
use Joomla\Component\Weblinks\Administrator\Table\WeblinkTable;
use Joomla\Component\Weblinks\Site\Helper\AssociationHelper;

/**
 * Content associations helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsHelper extends AssociationExtensionHelper
{
    /**
     * The extension name
     *
     * @var     string $extension
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $extension = 'com_weblinks';

    /**
     * Array of item types
     *
     * @var     array $itemTypes
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $itemTypes = ['weblink', 'category'];

    /**
     * Has the extension association support
     *
     * @var     boolean $associationsSupport
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $associationsSupport = true;

    /**
     * Method to get the associations for a given item.
     *
     * @param   integer  $id    Id of the item
     * @param   string   $view  Name of the view
     *
     * @return  array   Array of associations for the item
     *
     * @since  4.0.0
     */
    public function getAssociationsForItem($id = 0, $view = null)
    {
        return AssociationHelper::getAssociations($id, $view);
    }

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
        $type       = $this->getType($typeName);
        $context    = $this->extension . '.item';
        $catidField = 'catid';
        if ($typeName === 'category') {
            $context    = 'com_categories.item';
            $catidField = '';
        }

        // Get the associations.
        $associations = Associations::getAssociations($this->extension, $type['tables']['a'], $context, $id, 'id', 'alias', $catidField);
        return $associations;
    }

    /**
     * Get item information
     *
     * @param   string  $typeName  The item type
     * @param   int     $id        The id of item for which we need the associated items
     *
     * @return  Table|null
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItem($typeName, $id)
    {
        if (empty($id)) {
            return null;
        }

        $table = null;
        switch ($typeName) {
            case 'weblink':
                $table = new WeblinkTable(Factory::getDbo());

                break;
            case 'category':
                $table = new Category(Factory::getDbo());

                break;
        }

        if (empty($table)) {
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
        $tables  = [];
        $joins   = [];
        $support = $this->getSupportTemplate();
        $title   = '';
        if (in_array($typeName, $this->itemTypes)) {
            switch ($typeName) {
                case 'weblink':
                    $support['state']     = true;
                    $support['acl']       = true;
                    $support['checkout']  = true;
                    $support['category']  = true;
                    $support['save2copy'] = true;
                    $tables               = [
                        'a' => '#__weblinks',
                    ];
                    $title = 'weblink';

                    break;
                case 'category':
                    $fields['created_user_id'] = 'a.created_user_id';
                    $fields['ordering']        = 'a.lft';
                    $fields['level']           = 'a.level';
                    $fields['catid']           = '';
                    $fields['state']           = 'a.published';
                    $support['state']          = true;
                    $support['acl']            = true;
                    $support['checkout']       = true;
                    $support['level']          = true;
                    $tables                    = [
                        'a' => '#__categories',
                    ];
                    $title = 'category';

                    break;
            }
        }

        return [
            'fields'  => $fields,
            'support' => $support,
            'tables'  => $tables,
            'joins'   => $joins,
            'title'   => $title,
        ];
    }
}
