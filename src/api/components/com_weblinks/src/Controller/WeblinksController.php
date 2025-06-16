<?php

/**
 * @package     Joomla.API
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The weblinks controller
 *
 * @since  __DEPLOY_VERSION__
 */
class WeblinksController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $contentType = 'weblinks';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $default_view = 'weblinks';

    /**
     * Method to save a record.
     *
     * @param   integer  $recordKey  The primary key of the item (if exists)
     *
     * @return  integer  The record ID on success, false on failure
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function save($recordKey = null)
    {
        $data = (array) json_decode($this->input->json->getRaw(), true);

        foreach (FieldsHelper::getFields('com_weblinks.weblink') as $field) {
            if (isset($data[$field->name])) {
                !isset($data['com_fields']) && $data['com_fields'] = [];

                $data['com_fields'][$field->name] = $data[$field->name];
                unset($data[$field->name]);
            }
        }

        $this->input->set('data', $data);

        return parent::save($recordKey);
    }

    /**
     * Weblinks list view amended to add filtering of data
     *
     * @return  static  A BaseController object to support chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function displayList()
    {
        $apiFilterInfo = $this->input->get('filter', [], 'array');
        $filter        = InputFilter::getInstance();

        if (\array_key_exists('category', $apiFilterInfo)) {
            $this->modelState->set('filter.category_id', $filter->clean($apiFilterInfo['category'], 'INT'));
        }

        if (\array_key_exists('search', $apiFilterInfo)) {
            $this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
        }

        if (\array_key_exists('state', $apiFilterInfo)) {
            $this->modelState->set('filter.published', $filter->clean($apiFilterInfo['state'], 'INT'));
        }

        if (\array_key_exists('tag', $apiFilterInfo)) {
            $this->modelState->set('filter.tag', $filter->clean($apiFilterInfo['tag'], 'INT'));
        }

        if (\array_key_exists('language', $apiFilterInfo)) {
            $this->modelState->set('filter.language', $filter->clean($apiFilterInfo['language'], 'STRING'));
        }

        $apiListInfo = $this->input->get('list', [], 'array');

        if (\array_key_exists('ordering', $apiListInfo)) {
            $this->modelState->set('list.ordering', $filter->clean($apiListInfo['ordering'], 'STRING'));
        }

        if (\array_key_exists('direction', $apiListInfo)) {
            $this->modelState->set('list.direction', $filter->clean($apiListInfo['direction'], 'STRING'));
        }

        return parent::displayList();
    }
}
