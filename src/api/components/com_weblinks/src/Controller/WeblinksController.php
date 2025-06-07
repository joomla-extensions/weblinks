<?php

/**
 * @package     Joomla.API
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Api\Controller;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The weblinks controller
 *
 * @since  __DEPLOY_VERSION__
 */
class WeblinksController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $contentType = 'weblinks';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $default_view = 'weblinks';

    /**
     * Override execute to apply rate limiting to GET verb.
     *
     * @param   string|null  $task  The task to execute.
     * @return  mixed
     */
    public function execute($task): mixed
    {
        if (true !== $this->app->login(['username' => ''], ['silent' => true, 'action' => 'core.login.api'])) {
            $this->applyRateLimit();
        }

        return parent::execute($task);
    }

    /**
     * Applies rate limiting for guest users (no API token).
     */
    private function applyRateLimit(): void
    {
        // Load plugin params
        $plugin = PluginHelper::getPlugin('webservices', 'weblinks');
        $params = new Registry($plugin->params);

        $maxRequests   = (int) $params->get('max_requests', 2);
        $windowSeconds = (int) $params->get('window_seconds', 180);

        $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $config  = Factory::getApplication()->getConfig();
        $caching = (int) $config->get('caching', 0);

        if ($caching === 0) {
            // Non-persistent (file-based) caching
            $this->applyNonPersistentRateLimit($userIp, $maxRequests, $windowSeconds);
        } else {
            // Persistent caching
            $this->applyPersistentRateLimit($userIp, $maxRequests, $windowSeconds);
        }
    }

    /**
     * Handles rate limiting with non-persistent (file-based) caching.
     */
    private function applyNonPersistentRateLimit(string $userIp, int $maxRequests, int $windowSeconds): void
    {
        $storageDir = \JPATH_ROOT . '/tmp/api_rate_limit/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $file = $storageDir . md5($userIp) . '.json';

        // Load or initialize rate data
        if (file_exists($file)) {
            $rateData = json_decode(file_get_contents($file), true);
            if (!\is_array($rateData)) {
                $rateData = ['count' => 0, 'start' => time()];
            }
        } else {
            $rateData = ['count' => 0, 'start' => time()];
        }

        // Reset rate data if the time window has passed
        if (time() - $rateData['start'] > $windowSeconds) {
            $rateData = ['count' => 0, 'start' => time()];
        }

        // Increment the request count
        $rateData['count']++;

        // Check if the rate limit is exceeded
        if ($rateData['count'] > $maxRequests) {
            $this->handleRateLimitExceeded();
        }

        // Save the updated rate data
        file_put_contents($file, json_encode($rateData));
    }

    /**
     * Handles rate limiting with persistent caching.
     */
    private function applyPersistentRateLimit(string $userIp, int $maxRequests, int $windowSeconds): void
    {
        // Use Joomla cache if persistent
        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('output', [
                'defaultgroup' => 'weblinks_api_rate_limit',
                'lifetime'     => $windowSeconds,
            ]);

        $cacheKey = md5('api_rate_' . $userIp);

        // Load or initialize rate data
        $rateData = $cache->get($cacheKey);
        if (!$rateData) {
            $rateData = ['count' => 0, 'start' => time()];
        }

        // Reset rate data if the time window has passed
        if (time() - $rateData['start'] > $windowSeconds) {
            $rateData = ['count' => 0, 'start' => time()];
        }

        // Increment the request count
        $rateData['count']++;

        // Check if the rate limit is exceeded
        if ($rateData['count'] > $maxRequests) {
            $this->handleRateLimitExceeded();
        }

        // Save the updated rate data
        $cache->store($rateData, $cacheKey);
    }

    /**
     * Handles the scenario when the rate limit is exceeded.
     */
    private function handleRateLimitExceeded(): void
    {
        // Customize the behavior here (e.g., log the event, return a response, etc.)
        http_response_code(429); // HTTP 429 Too Many Requests
        echo json_encode([
            'errors' => [
                [
                    'title' => 'Rate limit exceeded',
                    'code'  => 429,
                ],
            ],
        ]);

        // Stop further execution
        exit;
    }

    /**
     * Method to save a record.
     *
     * @param   integer  $recordKey  The primary key of the item (if exists)
     *
     * @return  integer  The record ID on success, false on failure
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function save($recordKey = null)
    {
        $data = (array) json_decode($this->input->json->getRaw(), true);

        foreach (FieldsHelper::getFields('com_weblinks.weblink') as $field) {
            if (isset($data[$field->name])) {
                !isset($data['com_fields']) && $data['com_fields'] = [];

                $data['com_fields'][$field->name] = $data[$field->name];
                unset($data[$field->name]);
            }
        }

        $this->input->set('data', $data);

        return parent::save($recordKey);
    }
}
