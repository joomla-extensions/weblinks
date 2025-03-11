<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Weblinks\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin for Joomla Web Links.
 *
 * @since  __DEPLOY_VERSION__
 */
final class Weblinks extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Supported Extensions
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $supportedExtensions = [
        'mod_stats',
        'mod_stats_admin',
    ];

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $dispatcher
     * @param   array                $config
     * @param   DatabaseInterface    $database
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, DatabaseInterface $database)
    {
        parent::__construct($dispatcher, $config);

        $this->setDatabase($database);
    }

    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetStats' => 'onGetStats',
        ];
    }

    /**
     * Method to add statistics information to Administrator control panel.
     *
     * @param   string  $extension  The extension requesting information.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onGetStats(Event $event)
    {
        if (!ComponentHelper::isEnabled('com_weblinks')) {
            return;
        }

        [$extension] = $event->getArguments();

        if (!\in_array($extension, $this->supportedExtensions)) {
            return;
        }

        $db       = $this->getDatabase();
        $query    = $db->getQuery(true)
            ->select('COUNT(id) AS count_links')
            ->from('#__weblinks')
            ->where('state = 1');
        $webLinks = $db->setQuery($query)->loadResult();

        if (!$webLinks) {
            return;
        }

        $result   = $event->getArgument('result', []);
        $result[] = [
            [
                'title' => Text::_('PLG_SYSTEM_WEBLINKS_STATISTICS'),
                'icon'  => 'out-2',
                'data'  => $webLinks,
            ],
        ];

        $event->setArgument('result', $result);
    }
}
