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
}
