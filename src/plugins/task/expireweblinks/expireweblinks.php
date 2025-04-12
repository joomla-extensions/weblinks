<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.ExpireWeblinks
 */

namespace Joomla\Plugin\Task\ExpireWeblinks;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;

class PlgTaskExpireWeblinks extends CMSPlugin implements SubscriberInterface
{
    /**
     * @var DatabaseInterface
     */
    protected $db;

    /**
     * @var CacheControllerFactoryInterface
     */
    protected $cacheFactory;


    protected const TASK_NAME = 'expire.weblinks';

    /**
     * Constructor.
     *
     * @param   object  &$subject  The object to observe
     * @param   array   $config    An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);


        $this->db           = Factory::getContainer()->get(DatabaseInterface::class);
        $this->cacheFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskExecute'    => 'onTaskExecute',
            'onGetTaskOptions' => 'getTaskOptions',
        ];
    }

    /**
     * Returns the task metadata.
     *
     * @return  array
     */
    public function getTaskOptions()
    {
        return [
            self::TASK_NAME => [
                'label'       => 'Expire expired weblinks',
                'description' => 'Automatically unpublishes weblinks whose publish down date has passed.',
                'params'      => [],
            ],
        ];
    }

    /**
     * Executes the task.
     *
     * @param   object   $context  The context
     * @param   array    $params   The parameters
     *
     * @return  boolean  True on success
     */
    public function onTaskExecute($context, $params)
    {
        // Only run for our specific task
        if ($context->getTaskId() !== self::TASK_NAME) {
            return false;
        }

        // Get current UTC time
        $nowUTC = Factory::getDate()->toSql();

        $query = $this->db->getQuery(true)
            ->update($this->db->quoteName('#__weblinks'))
            ->set($this->db->quoteName('state') . ' = 0')
            ->where($this->db->quoteName('publish_down') . ' IS NOT NULL')
            ->where($this->db->quoteName('publish_down') . ' <= ' . $this->db->quote($nowUTC))
            ->where($this->db->quoteName('state') . ' = 1');

        $this->db->setQuery($query);
        $result = $this->db->execute();


        $cache = $this->cacheFactory->createCacheController('callback');
        $cache->clean('_system');
        $cache->clean('com_weblinks');


        return true;
    }
}
