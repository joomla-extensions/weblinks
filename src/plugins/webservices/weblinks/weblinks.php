<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

/**
 * Web Services adapter for com_weblinks.
 *
 * @since  4.0.0
 */
class PlgWebservicesWeblinks extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_weblinks's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {
        $router->createCRUDRoutes('v1/weblinks', 'weblinks', ['component' => 'com_weblinks']);

        $router->createCRUDRoutes(
            'v1/weblinks/categories',
            'categories',
            ['component' => 'com_categories', 'extension' => 'com_weblinks']
        );

        $this->createFieldsRoutes($router);
    }

    /**
     * Create fields routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function createFieldsRoutes(&$router)
    {
        $router->createCRUDRoutes(
            'v1/fields/weblinks',
            'fields',
            ['component' => 'com_fields', 'context' => 'com_weblinks.weblink']
        );

        $router->createCRUDRoutes(
            'v1/fields/groups/weblinks',
            'groups',
            ['component' => 'com_fields', 'context' => 'com_weblinks.weblink']
        );
    }
}
