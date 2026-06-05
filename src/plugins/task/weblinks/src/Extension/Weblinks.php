<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.weblinks
 *
 * @copyright   (C) 2024 Alikon. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Weblinks\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class Weblinks extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  1.0.0
     */
    protected $app;

    protected $autoloadLanguage = true;

    protected const TASKS_MAP = [
        'check.weblinks' => [
            'langConstPrefix' => 'PLG_TASK_WEBLINKS',
            'method'          => 'checkWeblinks',
        ],
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    protected function checkWeblinks(ExecuteTaskEvent $event): int
    {
        try {
            $model = $this->getApplication()->bootComponent('com_weblinks')
                ->getMVCFactory()->createModel('Weblinks', 'Site', ['ignore_request' => true]);

            $model->setState('filter.state', 1);
            $links = $model->getItems();
        } catch (\Exception $e) {
            $this->logTask('Failed to load weblinks: ' . $e->getMessage(), 'error');
            return Status::KNOCKOUT;
        }

        if (empty($links)) {
            $this->logTask('No published web links found');
            return Status::OK;
        }

        $broken = 0;
        $checked = 0;
        $details = [];
        $http = HttpFactory::getHttp();

        foreach ($links as $link) {
            $url = trim($link->url);
            
            if (!str_starts_with($url, 'http')) {
                $url = 'http://' . $url;
            }

            $checked++;

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $broken++;
                $details[] = sprintf('ID %d [%s]: Malformed URL', $link->id, $link->title);
                continue;
            }

            try {
                $response = $http->head($url, [], 8);
                $statusCode = $response->code;

                if ($statusCode < 200 || $statusCode >= 400) {
                    $broken++;
                    $details[] = sprintf('ID %d [%s]: HTTP %d', $link->id, $link->title, $statusCode);
                }
            } catch (\Exception $e) {
                $broken++;
                $details[] = sprintf('ID %d [%s]: %s', $link->id, $link->title, $e->getMessage());
            }
        }

        if ($broken === 0) {
            $this->logTask(sprintf('Verified %d links - all operational', $checked));
            return Status::OK;
        }

        $summary = sprintf('Checked %d links, %d broken: %s', $checked, $broken, implode('; ', $details));
        $this->logTask($summary);

        $this->sendBrokenLinksEmail($broken, $checked, $details);

        return Status::OK;
    }

    private function sendBrokenLinksEmail(int $broken, int $checked, array $details): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('email'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('sendEmail') . ' = 1');

        try {
            $adminEmails = $db->setQuery($query)->loadColumn();
        } catch (\Exception $e) {
            $this->logTask('Failed to get admin emails: ' . $e->getMessage(), 'warning');
            return;
        }

        if (empty($adminEmails)) {
            return;
        }

        try {
            $mail = new MailTemplate('plg_task_weblinks.broken_links', $this->app->getLanguage()->getTag());
            $mail->addRecipient($adminEmails);
            $mail->addTemplateData([
                'BROKEN_COUNT' => $broken,
                'CHECKED_COUNT' => $checked,
                'DETAILS' => implode("\\n", $details),
                'SITENAME' => $this->app->get('sitename'),
            ]);
            $mail->send();
        } catch (\Exception $e) {
            $this->logTask('Failed to send email: ' . $e->getMessage(), 'warning');
        }
    }
}
