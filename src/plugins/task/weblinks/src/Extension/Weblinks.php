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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Asset;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Http\HttpFactory;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Factory;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin for checking weblinks
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
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Load plugin language files automatically
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Map of task types to handler methods
     *
     * @var    array
     * @since  1.0.0
     */
    protected const TASKS_MAP = [
        'check.weblinks' => [
            'langConstPrefix' => 'PLG_TASK_WEBLINKS',
            'method'          => 'checkWeblinks',
        ],
    ];

    /**
     * Returns an array of events this subscriber will listen to
     *
     * @return  array
     *
     * @since   1.0.0
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
     * Check weblinks for broken URLs
     *
     * Iterates through all published weblinks and performs HTTP HEAD requests
     * to verify their availability. Uses Joomla\Http\HttpFactory from the framework.
     *
     * @param   ExecuteTaskEvent  $event  The task execution event
     *
     * @return  integer  The task status code
     *
     * @since   1.0.0
     */
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
        
        /**
         * Create HTTP client using framework HttpFactory
         * 
         * @var \Joomla\Http\Http
         */
        $http = (new HttpFactory())->getHttp();

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
                $statusCode = $response->getStatusCode();

                if ($response === null || $response->getStatusCode() !== 200) {
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

        return $this->sendBrokenLinksEmail($broken, $checked, $details);

        //return Status::OK;
    }

    /**
     * Send email notification about broken links to site administrators
     *
     * @param   integer  $broken   Number of broken links found
     * @param   integer  $checked  Total number of links checked
     * @param   array    $details  Details of broken links
     *
     * @return  integer  The task status code
     *
     * @since   1.0.0
     */
    private function sendBrokenLinksEmail(int $broken, int $checked, array $details): int
    {        
        $superUsers = $this->getSuperUsers();

        if (empty($superUsers)) {
            return Status::KNOCKOUT;
        }

        $sitename = $this->getApplication()->get('sitename');

        $substitutions = [
            'broken_count'  => $broken,
            'checked_count' => $checked,
            'details'       => implode("\n", $details),
            'sitename'      => $sitename,
        ];


        // Send the emails to the Super Users
        foreach ($superUsers as $superUser) {
            try {
                $mailer = new MailTemplate('plg_task_weblinks.broken_links', $this->app->getLanguage()->getTag());
                $mailer->addRecipient($superUser->email);
                $mailer->addTemplateData($substitutions);
                $mailer->send();
            } catch (MailDisabledException | phpMailerException $exception) {
                try {
                    $this->logTask($exception->getMessage());
                } catch (\RuntimeException) {
                    return Status::KNOCKOUT;
                }
            }
        }
        return Status::OK;
    }

    /**
     * Returns the Super Users email information. If you provide a comma separated $email list
     * we will check that these emails do belong to Super Users and that they have not blocked
     * system emails.
     *
     * @param   null|string  $email  A list of Super Users to email
     *
     * @return  array  The list of Super User emails
     *
     * @since   3.5
     */
    private function getSuperUsers($email = null)
    {
        $db     = $this->getDatabase();
        $emails = [];

        // Convert the email list to an array
        if (!empty($email)) {
            $temp   = explode(',', $email);

            foreach ($temp as $entry) {
                $emails[] = trim($entry);
            }

            $emails = array_unique($emails);
        }

        // Get a list of groups which have Super User privileges
        $ret = [];

        try {
            $table     = new Asset($db);
            $rootId    = $table->getRootId();
            $rules     = Access::getAssetRules($rootId)->getData();
            $rawGroups = $rules['core.admin']->getData();
            $groups    = [];

            if (empty($rawGroups)) {
                return $ret;
            }

            foreach ($rawGroups as $g => $enabled) {
                if ($enabled) {
                    $groups[] = $g;
                }
            }

            if (empty($groups)) {
                return $ret;
            }
        } catch (\Exception $exc) {
            return $ret;
        }

        // Get the user IDs of users belonging to the SA groups
        try {
            $query = $db->createQuery()
                ->select($db->quoteName('user_id'))
                ->from($db->quoteName('#__user_usergroup_map'))
                ->whereIn($db->quoteName('group_id'), $groups);

            $db->setQuery($query);
            $userIDs = $db->loadColumn(0);

            if (empty($userIDs)) {
                return $ret;
            }
        } catch (\Exception $exc) {
            return $ret;
        }

        // Get the user information for the Super Administrator users
        try {
            $query = $db->createQuery()
                ->select($db->quoteName(['id', 'username', 'email']))
                ->from($db->quoteName('#__users'))
                ->whereIn($db->quoteName('id'), $userIDs)
                ->where($db->quoteName('block') . ' = 0')
                ->where($db->quoteName('sendEmail') . ' = 1');

            if (!empty($emails)) {
                $lowerCaseEmails = array_map('strtolower', $emails);
                $query->whereIn('LOWER(' . $db->quoteName('email') . ')', $lowerCaseEmails, ParameterType::STRING);
            }

            $db->setQuery($query);
            $ret = $db->loadObjectList();
        } catch (\Exception) {
            return $ret;
        }

        return $ret;
    }
}
