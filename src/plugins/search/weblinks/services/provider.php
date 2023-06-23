<?php

/**
 * @package         Joomla.Plugin
 * @subpackage      Search.weblinks
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Search\Weblinks\Extension\Weblinks;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $app        = Factory::getApplication();
                $dispatcher = $container->get(DispatcherInterface::class);
                $database   = $container->get(DatabaseInterface::class);

                return new Weblinks(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('finder', 'weblinks'),
                    $app,
                    $database
                );
            }
        );
    }
};
