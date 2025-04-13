<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.ExpireWeblinks
 */

namespace Joomla\Plugin\Task\ExpireWeblinks;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;

/**
 * Task Plugin to expire weblinks.
 */
class PlgTaskExpireWeblinks extends CMSPlugin implements SubscriberInterface
{
    protected const TASK_ROUTINE_ID = 'TASK_ROUTINE_EXPIRE_WEBLINKS';

    /**
     * @var DatabaseInterface
     */
    protected $db = null;

    /**
     * @var CacheControllerFactoryInterface
     */
    protected $cacheFactory = null;

    /**
     *
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Sets the database connection via dependency injection.
     *
     * @param   DatabaseInterface  $db  The database object
     *
     * @return  void
     */
    public function setDatabase(DatabaseInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Sets the cache factory via dependency injection.
     *
     * @param   CacheControllerFactoryInterface  $cacheFactory  The cache factory object
     *
     * @return  void
     */
    public function setCacheFactory(CacheControllerFactoryInterface $cacheFactory): void
    {
        $this->cacheFactory = $cacheFactory;
    }

    /**
     *
     *
     * @return  array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList' => 'advertiseRoutines',
            'onExecuteTask'     => 'standardRoutineHandler',
        ];
    }

    /**
     *
     *
     * @param   \Joomla\Event\Event  $event  The event to handle
     *
     * @return  void
     */
    public function advertiseRoutines(\Joomla\Event\Event $event)
    {
        $routines = [
            [
                'id'          => self::TASK_ROUTINE_ID,
                'title'       => 'Expire weblinks',
                'description' => 'Automatically unpublishes weblinks whose publish down date has passed.',
                'form'        => [],
            ],
        ];

        $event->addArgument('routines', array_merge($event->getArgument('routines') ?? [], $routines));
    }

    /**
     * Standard routine handler for task execution
     *
     * @param   \Joomla\Event\Event  $event  The event to handle
     *
     * @return  void
     */
    public function standardRoutineHandler(\Joomla\Event\Event $event)
    {
        $id = $event->getArgument('routineId');


        if ($id !== self::TASK_ROUTINE_ID) {
            return;
        }

        try {
            $result = $this->expireWeblinks();
            $event->addArgument('result', $result);
        } catch (\Exception $e) {
            $event->addArgument('exception', $e);
        }
    }

    /**
     *
     *
     * @return  boolean
     */
    protected function expireWeblinks()
    {

        $now    = new Date();
        $nowUTC = $now->toSql();

        $query = $this->db->getQuery(true)
            ->update($this->db->quoteName('#__weblinks'))
            ->set($this->db->quoteName('state') . ' = 0')
            ->where($this->db->quoteName('publish_down') . ' IS NOT NULL')
            ->where($this->db->quoteName('publish_down') . ' <= ' . $this->db->quote($nowUTC))
            ->where($this->db->quoteName('state') . ' = 1');

        $this->db->setQuery($query);
        $result = $this->db->execute();

        // Clean weblinks cache
        $cache = $this->cacheFactory->createCacheController('callback');
        $cache->clean('_system');
        $cache->clean('com_weblinks');

        return true;
    }
}
