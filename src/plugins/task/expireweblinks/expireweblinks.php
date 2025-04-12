<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.ExpireWeblinks
 */

namespace Joomla\Plugin\System\ExpireWeblinks;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;

/**
 * Plugin to automatically expire weblinks.
 */
class PlgSystemExpireWeblinks extends CMSPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        // Prevent direct access
        if (!\defined('_JEXEC')) {
            die;
        }
    }
    /**
     * Runs on every Joomla request to check and unpublish expired weblinks.
     */
    public function onAfterInitialise()
    {
        $this->expireWeblinks();
    }

    /**
     * Function to unpublish expired weblinks.
     */
    protected function expireWeblinks()
    {


        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true);

        // Get current UTC time
        $nowUTC = Factory::getDate()->toSql();

        $query->update($db->quoteName('#__weblinks'))
              ->set($db->quoteName('state') . ' = 0') // Unpublish
              ->where($db->quoteName('publish_down') . ' IS NOT NULL')
              ->where($db->quoteName('publish_down') . ' <= ' . $db->quote($nowUTC))
              ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);
        $db->execute();



        $cacheFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
        $cache        = $cacheFactory->createCacheController('callback'); // You can change the handler if needed
        $cache->clean('_system');
        $cache->clean('com_weblinks');
    }
}  
