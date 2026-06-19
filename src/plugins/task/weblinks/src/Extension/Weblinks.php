<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.weblinks
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Weblinks\Extension;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Table\TaskTable;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Http\HttpFactory;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin for checking weblinks with WILL_RESUME batch support
 *
 * @since  1.0.0
 */
final class Weblinks extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * Application object
     *
     * @var \Joomla\CMS\Application\CMSApplication
     * @since 1.0.0
     */
    protected $app;

    /**
     * Auto-load plugin language files
     *
     * @var boolean
     * @since 1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Time limit for the current task session (seconds).
     * Must be lower than PHP's max_execution_time.
     *
     * @var int
     * @since 1.0.0
     */
    private $timeLimit;

    /**
     * Snapshot key used in task parameters
     *
     * @var string
     */
    private const SNAPSHOT_KEY = 'snapshot';

    /**
     * Snapshot key for last processed ID
     *
     * @var string
     */
    private const SNAPSHOT_LASTID = 'lastId';

    /**
     * Snapshot key for broken links count
     *
     * @var string
     */
    private const SNAPSHOT_BROKEN = 'broken';

    /**
     * Snapshot key for checked links count
     *
     * @var string
     */
    private const SNAPSHOT_CHECKED = 'checked';

    /**
     * Snapshot key for details array
     *
     * @var string
     */
    private const SNAPSHOT_DETAILS = 'details';

    /**
     * Maximum number of details to store
     *
     * @var int
     */
    private const MAX_DETAILS = 1000;

    /**
     * Task routines map
     *
     * @var array
     * @since 1.0.0
     */
    protected const TASKS_MAP = [
        'check.weblinks' => [
            'langConstPrefix' => 'PLG_TASK_WEBLINKS',
            'form'            => 'weblinks_params',
            'method'          => 'checkWeblinks',
        ],
    ];

    /**
     * Returns the subscribed events for this plugin
     *
     * @return array
     * @since 1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * Clears the snapshot from the task parameters
     *
     * @param   object  $task    The task object
     * @param   int     $taskId  The task ID
     *
     * @return  bool  True on success, false on failure
     * @since   1.0.0
     */
    private function clearSnapshot(object $task, int $taskId): bool
    {
        if ($taskId === 0) {
            return false;
        }

        try {
            $taskTable = new TaskTable($this->getDatabase());

            if (!$taskTable->load($taskId)) {
                return false;
            }

            $params = json_decode($taskTable->params, true) ?? [];
            unset($params[self::SNAPSHOT_KEY]);
            $taskTable->params = json_encode($params, JSON_UNESCAPED_UNICODE);
            $taskTable->store();

            return true;
        } catch (\Exception $e) {
            $this->logTask('Snapshot deletion error: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Checks weblinks in batches with WILL_RESUME support.
     *
     * The snapshot persists between executions and contains:
     *   - offset      : next record to read from the database
     *   - broken      : broken links accumulated so far
     *   - checked     : links checked so far
     *   - details     : array of descriptive strings of broken links
     *
     * @param   ExecuteTaskEvent  $event  The task execution event
     *
     * @return  int  Task status code (Status::OK, Status::KNOCKOUT, or Status::WILL_RESUME)
     * @since   1.0.0
     */
    protected function checkWeblinks(ExecuteTaskEvent $event): int
    {
        $task        = $event->getArgument('subject');
        $taskId      = $event->getTaskId();
        $httpTimeout = (int) $event->getArgument('params')->http_timeout ?? 8;
        $batchSize   = (int) $event->getArgument('params')->batch_size ?? 3;
        $timelimit   = max(5, (int)\ini_get('max_execution_time') - 10);
        $startTime   = microtime(true);

        // Restore previous state
        $snapshot = $this->loadSnapshot($task, $taskId);
        $lastId   = (int) ($snapshot[self::SNAPSHOT_LASTID] ?? 0);
        $broken   = (int) ($snapshot[self::SNAPSHOT_BROKEN] ?? 0);
        $checked  = (int) ($snapshot[self::SNAPSHOT_CHECKED] ?? 0);
        $details  = (array) ($snapshot[self::SNAPSHOT_DETAILS] ?? []);

        // Read current batch using lastId
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'url']))
            ->from($db->quoteName('#__weblinks'))
            ->where($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('id') . ' > :id')
            ->bind(':id', $lastId, ParameterType::INTEGER)
            ->order($db->quoteName('id') . ' ASC')
            ->setLimit($batchSize);

        try {
            $links = $db->setQuery($query)->loadObjectList();
        } catch (\Exception $e) {
            $this->logTask('Weblinks query error: ' . $e->getMessage(), 'error');
            $this->clearSnapshot($task, $taskId);
            return Status::KNOCKOUT;
        }

        // No links → processing complete
        if (empty($links)) {
            return $this->finalize($task, $broken, $checked, $details, $taskId);
        }

        // Process batch with timeout check
        $http             = (new HttpFactory())->getHttp();
        $processedInBatch = 0;
        $acceptedCodes    = [200, 301, 302, 307, 308];

        foreach ($links as $link) {
            // Check time limit
            if ((microtime(true) - $startTime) > $timelimit) {
                break;
            }

            $url = $this->normalizeUrl($link->url);

            if ($url === null) {
                $broken++;
                $checked++;
                $processedInBatch++;
                $lastId = (int) $link->id;

                $this->addDetail($details, \sprintf('ID %d [%s]: Invalid URL', $link->id, $link->title));
                continue;
            }

            $checked++;
            $processedInBatch++;
            $lastId = (int) $link->id;

            try {
                $response   = $http->head($url, [], $httpTimeout);
                $statusCode = $response->getStatusCode();

                if (!\in_array($statusCode, $acceptedCodes, true)) {
                    $broken++;
                    $this->addDetail($details, \sprintf('ID %d [%s]: HTTP %d', $link->id, $link->title, $statusCode));
                }
            } catch (\Exception $e) {
                $broken++;
                $this->addDetail($details, \sprintf('ID %d [%s]: %s', $link->id, $link->title, $e->getMessage()));
            }
        }

        // Check if there are more links (search for next ID after lastId)
        $hasMoreQuery = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__weblinks'))
            ->where($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('id') . ' > :id')
            ->bind(':id', $lastId, ParameterType::INTEGER)
            ->order($db->quoteName('id') . ' ASC')
            ->setLimit(1);

        try {
            $nextId = $db->setQuery($hasMoreQuery)->loadResult();
        } catch (\Exception $e) {
            $this->logTask('Error checking for remaining weblinks: ' . $e->getMessage(), 'error');
            $this->clearSnapshot($task, $taskId);
            return Status::KNOCKOUT;
        }

        // Remaining links → continue
        if ($nextId !== null) {
            $saved = $this->saveSnapshot($task, [
                self::SNAPSHOT_LASTID  => $lastId,
                self::SNAPSHOT_BROKEN  => $broken,
                self::SNAPSHOT_CHECKED => $checked,
                self::SNAPSHOT_DETAILS => $details,
            ], $taskId);

            if ($saved) {
                $this->logTask(\sprintf(
                    'Batch: %d checked (%d broken), lastId=%d. Will resume shortly.',
                    $checked,
                    $broken,
                    $lastId
                ), 'info');
            }

            return Status::WILL_RESUME;
        }

        // All links processed
        return $this->finalize($task, $broken, $checked, $details, $taskId);

    }

    /**
     * Finalizes the task: resets snapshot, logs and sends email if needed.
     *
     * @param   object  $task     Task object
     * @param   int     $broken   Number of broken links
     * @param   int     $checked  Number of checked links
     * @param   array   $details  Broken links details
     * @param   int     $taskId   The task ID
     *
     * @return  int  Task status code
     * @since   1.0.0
     */
    private function finalize(object $task, int $broken, int $checked, array $details, int $taskId): int
    {
        // Remove snapshot
        $this->clearSnapshot($task, $taskId);

        if ($broken === 0) {
            $this->logTask(\sprintf('Verification completed: %d links checked, all reachable.', $checked), 'info');
            return Status::OK;
        }

        $this->logTask(\sprintf(
            'Verification completed: %d links checked, %d unreachable.',
            $checked,
            $broken
        ), 'info');

        return $this->sendBrokenLinksEmail($broken, $checked, $details);
    }

    /**
     * Sends a summary email to Super Users.
     *
     * @param   int    $broken   Number of broken links
     * @param   int    $checked  Number of checked links
     * @param   array  $details  Array of broken link details
     *
     * @return  int  Task status code
     * @since   1.0.0
     */
    private function sendBrokenLinksEmail(int $broken, int $checked, array $details): int
    {
        $superUsers = $this->getSuperUsers();

        if (empty($superUsers)) {
            $this->logTask('No Super Users found for email notification.', 'error');
            return Status::KNOCKOUT;
        }

        $substitutions = [
            'broken_count'  => $broken,
            'checked_count' => $checked,
            'details'       => implode("\n", $details),
            'sitename'      => $this->getApplication()->get('sitename'),
        ];

        foreach ($superUsers as $superUser) {
            try {
                $mailer = new MailTemplate('plg_task_weblinks.broken_links', $this->app->getLanguage()->getTag());
                $mailer->addRecipient($superUser->email);
                $mailer->addTemplateData($substitutions);
                $mailer->send();
            } catch (\Joomla\CMS\Mail\Exception\MailDisabledException | phpMailerException $exception) {
                $this->logTask($exception->getMessage(), 'error');
            }
        }

        return Status::OK;
    }

    /**
     * Retrieves Super Users who have email notification enabled.
     *
     * @param   string|null  $email  Comma-separated email list (optional)
     *
     * @return  array  Array of user objects with id, username, and email
     * @since   1.0.0
     */
    private function getSuperUsers(?string $email = null): array
    {
        $db = $this->getDatabase();

        try {
            // Get groups with core.admin permission
            $table     = new Asset($db);
            $rootId    = $table->getRootId();
            $rules     = Access::getAssetRules($rootId)->getData();
            $rawGroups = $rules['core.admin']->getData() ?? [];

            $groups = array_keys(array_filter($rawGroups));

            if (empty($groups)) {
                return [];
            }

            // Unified query with JOIN
            $query = $db->getQuery(true)
                ->select($db->quoteName(['u.id', 'u.username', 'u.email']))
                ->from($db->quoteName('#__users', 'u'))
                ->innerJoin(
                    $db->quoteName('#__user_usergroup_map', 'ug'),
                    $db->quoteName('u.id') . ' = ' . $db->quoteName('ug.user_id')
                )
                ->whereIn($db->quoteName('ug.group_id'), $groups)
                ->where($db->quoteName('u.block') . ' = 0')
                ->where($db->quoteName('u.sendEmail') . ' = 1');

            if (!empty($email)) {
                $emails = array_filter(array_map('trim', explode(',', $email)));
                if (!empty($emails)) {
                    $quotedEmails = array_map(
                        fn ($e) => $db->quote(strtolower(trim($e))),
                        $emails
                    );
                    $query->where('LOWER(' . $db->quoteName('u.email') . ') IN (' . implode(',', $quotedEmails) . ')');
                }
            }

            return $db->setQuery($query)->loadObjectList();
        } catch (\Exception $e) {
            $this->logTask('getSuperUsers error: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Normalizes a URL: adds scheme if missing
     *
     * @param   string  $url  The URL to normalize
     *
     * @return  string|null  Normalized URL or null if invalid
     * @since   1.0.0
     */
    private function normalizeUrl(string $url): ?string
    {
        $url = trim($url);

        // Block suspicious URLs
        if (empty($url) || stripos($url, 'javascript:') === 0) {
            return null;
        }

        if (!str_starts_with(strtolower($url), 'http')) {
            $url = 'http://' . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    /**
     * Saves the snapshot in the task parameters using the Table class
     *
     * @param   object  $task     The task object
     * @param   array   $data     The data to save
     * @param   int     $taskId   The task ID
     *
     * @return  bool  True on success, false on failure
     * @since   1.0.0
     */
    private function saveSnapshot(object $task, array $data, int $taskId): bool
    {
        if ($taskId === 0) {
            $this->logTask('Unable to get task ID to save snapshot', 'error');
            return false;
        }

        try {
            $taskTable = new TaskTable($this->getDatabase());

            if (!$taskTable->load($taskId)) {
                $this->logTask('Task ID ' . $taskId . ' not found for saving', 'error');
                return false;
            }

            $params                     = json_decode($taskTable->params, true) ?? [];
            $params[self::SNAPSHOT_KEY] = $data;
            $taskTable->params          = json_encode($params, JSON_UNESCAPED_UNICODE);
            $taskTable->store();
            return true;

        } catch (\Exception $e) {
            $this->logTask('Snapshot save error: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Reads the snapshot from the task parameters using the Table class
     *
     * @param   object  $task    The task object
     * @param   int     $taskId  The task ID
     *
     * @return  array  The snapshot data as an associative array
     * @since   1.0.0
     */
    private function loadSnapshot(object $task, int $taskId): array
    {
        if ($taskId === 0) {
            $this->logTask('Unable to get task ID for snapshot', 'error');
            return [];
        }

        try {
            $taskTable = new TaskTable($this->getDatabase());

            if (!$taskTable->load($taskId)) {
                $this->logTask('Task ID ' . $taskId . ' not found', 'error');
                return [];
            }

            $params   = json_decode($taskTable->params, true) ?? [];
            $snapshot = $params[self::SNAPSHOT_KEY] ?? [];
            //$this->logTask('load snapshot: ' . isset($snapshot['lastId']) ? $snapshot['lastId'] : 0, 'info');
            return \is_array($snapshot) ? $snapshot : [];
        } catch (\Exception $e) {
            $this->logTask('Snapshot load error: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Adds a detail to the list, limiting the maximum number
     *
     * @param   array   &$details  The details array (passed by reference)
     * @param   string  $detail    The detail to add
     *
     * @return  void
     * @since   1.0.0
     */
    private function addDetail(array &$details, string $detail): void
    {
        if (\count($details) < self::MAX_DETAILS) {
            $details[] = $detail;
        }
    }
}
