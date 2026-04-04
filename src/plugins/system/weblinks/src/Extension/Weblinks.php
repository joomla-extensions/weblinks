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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
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
            'onGetStats'    => 'onGetStats',
            'onAfterRender' => 'onAfterRender',
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
        $this->loadLanguage();

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

    /**
     * Method to grab help URLs and replace them in the body.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAfterRender()
    {
        $input     = Factory::getApplication()->getInput();
        $option    = $input->get('option');
        $view      = $input->get('view');
        $layout    = $input->get('layout');
        $component = $input->get('component');
        $extension = $input->get('extension');
        $body      = Factory::getApplication()->getBody();
        $lang      = Factory::getLanguage()->getTag();
        $modified  = false;

        // 1. Handle com_categories for weblinks
        if ($option === 'com_categories' && $extension === 'com_weblinks' && $layout !== 'edit') {
            $helpUrl = Uri::root() . 'administrator/components/com_weblinks/help/' . $lang . '/weblinks-categories.html';

            // Multiple patterns to catch different URL formats
            $patterns = [
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Components_Weblinks_Categories[^"\']*#',
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Components_Weblinks_&lang=[^"\']*#',
                '#https?://help\.joomla\.org/[0-9]+/en-GB/Components_Weblinks_Categories\.html[^"\']*#',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $body)) {
                    $body     = preg_replace($pattern, $helpUrl, $body);
                    $modified = true;
                }
            }
        }

        // 1a. Handle com_categories edit for weblinks
        if ($option === 'com_categories' && $extension === 'com_weblinks' && $layout === 'edit') {
            $helpUrl = Uri::root() . 'administrator/components/com_weblinks/help/' . $lang . '/weblinks-categories-edit.html';

            // Multiple patterns to catch different URL formats
            $patterns = [
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Components_Weblinks_Categories_Edit[^"\']*#',
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Components_Weblinks_Categories_Edit&lang=[^"\']*#',
                '#https?://help\.joomla\.org/[0-9]+/en-GB/0:Components_Weblinks_Categories_Edit&\.html[^"\']*#',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $body)) {
                    $body     = preg_replace($pattern, $helpUrl, $body);
                    $modified = true;
                }
            }
        }

        // 2. Handle main weblinks view (list view)
        if ($option === 'com_weblinks' && ($view === 'weblinks' || $view === null)) {
            $helpUrl = Uri::root() . 'administrator/components/com_weblinks/help/' . $lang . '/weblinks-links.html';

            $patterns = [
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Components_Weblinks_Links[^"\']*#',
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Components_Weblinks[^"\']*#',
                '#https?://help\.joomla\.org/[0-9]+/en-GB/Components_Weblinks_Links\.html[^"\']*#',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $body)) {
                    $body     = preg_replace($pattern, $helpUrl, $body);
                    $modified = true;
                }
            }
        }

        // 3. Handle weblinks options view
        if ($option === 'com_config' && $view === 'component' && $component === 'com_weblinks') {
            $helpUrl = Uri::root() . 'administrator/components/com_weblinks/help/' . $lang . '/weblinks-options.html';

            $patterns = [
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Help[^"\']*#',
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9]+:Help[^"\']*#',
                '#https?://help\.joomla\.org/[0-9]+/en-GB/Help\.html[^"\']*#',
                '#https?://help\.joomla\.org/proxy\?keyref=Help[0-9][^"\']*#',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $body)) {
                    $body     = preg_replace($pattern, $helpUrl, $body);
                    $modified = true;
                }
            }
        }

        // Set the modified body back if changes were made
        if ($modified) {
            Factory::getApplication()->setBody($body);
        }

        return true;
    }
}
