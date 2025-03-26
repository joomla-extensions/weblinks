<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.ExpireWeblinks
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

/**
 * Plugin to automatically expire weblinks.
 */
class PlgSystemExpireWeblinks extends CMSPlugin
{
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
        $db = Factory::getDbo();
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

        Factory::getCache()->clean('_system');
        Factory::getCache()->clean('com_weblinks');
    }
}
