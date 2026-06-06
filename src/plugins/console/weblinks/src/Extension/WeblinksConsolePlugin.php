<?php

namespace Joomla\Plugin\Console\Weblinks\Extension;

\defined('_JEXEC') or die;

use Joomla\Application\ApplicationEvents;
use Joomla\Application\Event\ApplicationEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Console\Weblinks\CliCommand\WeblinksCommand;

class WeblinksConsolePlugin extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
        ];
    }

    public function registerCommands(ApplicationEvent $event): void
    {
        $command = new WeblinksCommand();
        $command->setDatabase($this->getDatabase());
        $event->getApplication()->addCommand($command);
    }
}
