
<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Weblinks\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_weblinks.
 *
 * @since  __DEPLOY_VERSION__
 */
class Weblinks extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_weblinks's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onBeforeApiRoute(&$router)
    {
        $isPublic = $this->params->get('public', false);

        $router->createCRUDRoutes(
            'v1/weblinks',
            'weblinks',
            ['component' => 'com_weblinks'],
            $isPublic // <-- Only GET is public
        );

        $router->createCRUDRoutes(
            'v1/weblinks/categories',
            'categories',
            ['component' => 'com_categories', 'extension' => 'com_weblinks'],
            $isPublic // <-- Only GET is public

        );

        $this->createFieldsRoutes($router, $isPublic);
    }

    /**
     * Create fields routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     * @param   boolean    $isPublic  Indicates if the routes are public
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createFieldsRoutes(&$router, $isPublic)
    {
        $router->createCRUDRoutes(
            'v1/fields/weblinks',
            'fields',
            ['component' => 'com_fields', 'context' => 'com_weblinks.weblink'],
            $isPublic // <-- Only GET is public
        );

        $router->createCRUDRoutes(
            'v1/fields/groups/weblinks',
            'groups',
            ['component' => 'com_fields', 'context' => 'com_weblinks.weblink'],
            $isPublic // <-- Only GET is public
        );
    }
}
