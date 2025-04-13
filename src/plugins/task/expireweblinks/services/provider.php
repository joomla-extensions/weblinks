<?php

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\Task\ExpireWeblinks\PlgTaskExpireWeblinks;

return new class implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->set(
            PlgTaskExpireWeblinks::class,
            function (Container $container) {
                $plugin = new PlgTaskExpireWeblinks(
                    $container->get('dispatcher'),
                    (array) PluginHelper::getPlugin('task', 'expireweblinks')
                );

                // Inject services
                $plugin->setDatabase($container->get(DatabaseInterface::class));
                $plugin->setCacheFactory($container->get(CacheControllerFactoryInterface::class));

                return $plugin;
            }
        );
    }
};
