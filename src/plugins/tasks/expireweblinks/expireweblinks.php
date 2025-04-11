<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.ExpireWeblinks
 */

namespace Joomla\Plugin\Task\ExpireWeblinks;


use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Date\DateFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Extension\PluginInterface;
use Joomla\Scheduler\Task\AbstractTaskPlugin;
use Joomla\Scheduler\Task\TaskContext;

/**
 * Task Plugin to expire weblinks.
 */
final class PlgTaskExpireWeblinks extends AbstractTaskPlugin implements PluginInterface
{
    protected const TASK_NAME = 'expire.weblinks';

    protected DatabaseInterface $db;
    protected CacheControllerFactoryInterface $cacheFactory;
    protected DateFactory $dateFactory;

    public function __construct(
        $subject,
        array $config,
        DatabaseInterface $db,
        CacheControllerFactoryInterface $cacheFactory,
        DateFactory $dateFactory
    ) {
        parent::__construct($subject, $config);

        $this->db           = $db;
        $this->cacheFactory = $cacheFactory;
        $this->dateFactory  = $dateFactory;
    }

    /**
     * Returns the task metadata.
     */
    public function getTaskOptions(): array
    {
        return [
            'label'       => 'Expire expired weblinks',
            'description' => 'Automatically unpublishes weblinks whose publish down date has passed.',
        ];
    }

    /**
     * Executes the task.
     */
    public function onExecute(TaskContext $context): void
    {
        $now = $this->dateFactory->getDate()->toSql();

        $query = $this->db->getQuery(true)
            ->update($this->db->quoteName('#__weblinks'))
            ->set($this->db->quoteName('state') . ' = 0')
            ->where($this->db->quoteName('publish_down') . ' IS NOT NULL')
            ->where($this->db->quoteName('publish_down') . ' <= ' . $this->db->quote($now))
            ->where($this->db->quoteName('state') . ' = 1');

        $this->db->setQuery($query)->execute();

        // Clean weblinks cache
        $cache = $this->cacheFactory->createCacheController('callback');
        $cache->clean('_system');
        $cache->clean('com_weblinks');
    }
}
