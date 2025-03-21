<?php

/**
 * @package     Joomla.API
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Api\View\Weblinks;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The weblinks view
 *
 * @since  4.0.0
 */
class JsonApiView extends BaseApiView
{
    /**
     * The fields to render item in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderItem = [
        'id',
        'catid',
        'title',
        'alias',
        'url',
        'description',
        'hits',
        'state',
        'checked_out',
        'checked_out_time',
        'ordering',
        'access',
        'params',
        'language',
        'created',
        'created_by',
        'created_by_alias',
        'modified',
        'modified_by',
        'metakey',
        'metadesc',
        'metadata',
        'featured',
        'xreference',
        'publish_up',
        'publish_down',
        'version',
        'images',
        'tags',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'alias',
        'checked_out',
        'checked_out_time',
        'catid',
        'created',
        'created_by',
        'hits',
        'state',
        'access',
        'ordering',
        'language',
        'publish_up',
        'publish_down',
        'language_title',
        'language_image',
        'editor',
        'access_level',
        'category_title',
    ];

    /**
     * Execute and display a template script.
     *
     * @param   array|null  $items  Array of items
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function displayList(?array $items = null)
    {
        foreach (FieldsHelper::getFields('com_weblinks.weblink') as $field) {
            $this->fieldsToRenderList[] = $field->name;
        }

        return parent::displayList();
    }

    /**
     * Execute and display a template script.
     *
     * @param   object  $item  Item
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function displayItem($item = null)
    {
        foreach (FieldsHelper::getFields('com_weblinks.weblink') as $field) {
            $this->fieldsToRenderItem[] = $field->name;
        }

        return parent::displayItem();
    }

    /**
     * Prepare item before render.
     *
     * @param   object  $item  The model item
     *
     * @return  object
     *
     * @since   4.0.0
     */
    protected function prepareItem($item)
    {
        foreach (FieldsHelper::getFields('com_weblinks.weblink', $item, true) as $field) {
            $item->{$field->name} = isset($field->apivalue) ? $field->apivalue : $field->rawvalue;
        }

        return parent::prepareItem($item);
    }
}
