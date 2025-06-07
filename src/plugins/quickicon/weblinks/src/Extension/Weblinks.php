<?php

/**
 * @copyright   Copyright (C) 2025. All rights reserved
 * @license     GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Joomla\Plugin\Quickicon\Weblinks\Extension;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

\defined('_JEXEC') or die;

/**
 * Weblinks Quick Icon plugin.
 *
 * @since       1.0.0
 */
final class Weblinks extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetIcons' => 'onGetIcons',
        ];
    }

    /**
     * Event to get the quick icons.
     *
     * @param   string  $context  The context of the event.
     *
     * @return  array|null  An array of icon definition arrays, or null if not applicable.
     *
     * @since   1.0.0
     */
    public function onGetIcons(QuickIconsEvent $event): void
    {
        $context = $event->getContext();

        if (
            $context !== $this->params->get('context', 'mod_quickicon')
            || !$this->getApplication()->getIdentity()->authorise('core.manage', 'com_weblinks')
        ) {
            return;
        }

        $iconDefinition = [
            [
                'image'   => 'icon-link',
                'link'    => 'index.php?option=com_weblinks',
                'linkadd' => 'index.php?option=com_weblinks&task=weblink.add',
                'text'    => Text::_('PLG_QUICKICON_WEBLINKS_TITLE'),
                'id'      => 'PLG_QUICKICON_WEBLINKS',
            ],
        ];

        if ($this->params->get('show_count', 1)) {
            $iconDefinition[0]['ajaxurl'] = 'index.php?option=com_weblinks&task=weblinks.getQuickiconContent&format=json';
        } else {
            unset($iconDefinition[0]['ajaxurl']);
        }

        // Add the icon to the result array
        $result = $event->getArgument('result', []);

        $result[] = $iconDefinition;

        $event->setArgument('result', $result);
    }
}
