<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Weblinks\Extension;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_weblinks.
 *
 * @since  __DEPLOY_VERSION__
 */
class Weblinks extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_weblinks's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onBeforeApiRoute(&$router)
    {
        $isPublic = $this->params->get('public', false);

        $router->createCRUDRoutes(
            'v1/weblinks',
            'weblinks',
            ['component' => 'com_weblinks'],
            $isPublic // <-- Only GET is public
        );

        $router->createCRUDRoutes(
            'v1/weblinks/categories',
            'categories',
            ['component' => 'com_categories', 'extension' => 'com_weblinks'],
            $isPublic // <-- Only GET is public
        );

        $this->createFieldsRoutes($router, $isPublic);
    }

    /**
     * Create fields routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     * @param   boolean    $isPublic  Indicates if the routes are public
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createFieldsRoutes(&$router, $isPublic)
    {
        $router->createCRUDRoutes(
            'v1/fields/weblinks',
            'fields',
            ['component' => 'com_fields', 'context' => 'com_weblinks.weblink'],
            $isPublic // <-- Only GET is public
        );

        $router->createCRUDRoutes(
            'v1/fields/groups/weblinks',
            'groups',
            ['component' => 'com_fields', 'context' => 'com_weblinks.weblink'],
            $isPublic // <-- Only GET is public
        );
    }

    /**
     * Event handler that runs after the API router has processed the request.
     *
     * Applies rate limiting to public Weblinks API endpoints for guest users,
     * using either non-persistent (file-based) or persistent (cache-based) strategies,
     * depending on the current Joomla cache configuration.
     *
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAfterApiRoute()
    {
        $app      = Factory::getApplication();
        $uri      = $app->getInput()->server->get('REQUEST_URI', '', 'string');
        $isPublic = $this->params->get('public', false);

        // Only apply to weblinks-related API requests for guest users
        if (
            strpos($uri, '/weblinks') !== false
            &&
            true !== $app->login(credentials: ['username' => ''], options: ['silent' => true, 'action' => 'core.login.api'])
            &&
            true === $isPublic
        ) {
            $ip     = $_SERVER['REMOTE_ADDR'];
            $limit  = $this->params->get('max_requests', 2);
            $window = $this->params->get('window_seconds', 180);

            $config  = Factory::getApplication()->getConfig();
            $caching = (int) $config->get('caching', 0);

            if ($caching === 0) {
                // Non-persistent (file-based) caching
                $this->applyNonPersistentRateLimit($ip, $limit, $window);
            } else {
                // Persistent caching
                $this->applyPersistentRateLimit($ip, $limit, $window);
            }
        }
    }

    /**
     * Applies rate limiting using a non-persistent file-based storage.
     *
     * This method stores request counts in JSON files within the site's temporary
     * directory. Each file corresponds to a user's IP address.
     *
     * @param   string  $userIp         The IP address of the user.
     * @param   int     $maxRequests    The maximum number of allowed requests in the time window.
     * @param   int     $windowSeconds  The time window in seconds for the rate limit.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function applyNonPersistentRateLimit(string $userIp, int $maxRequests, int $windowSeconds): void
    {
        $storageDir = JPATH_ROOT . '/tmp/api_rate_limit/';
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

        // Calculate remaining requests and reset time
        $remainingRequests = $maxRequests - $rateData['count'];
        $resetTime         = $rateData['start'] + $windowSeconds;

        // Set rate-limiting headers
        $this->setRateLimitHeaders($remainingRequests, $resetTime);

        // Check if the rate limit is exceeded
        if ($rateData['count'] > $maxRequests) {
            $retryAfter = $resetTime - time();
            $this->handleRateLimitExceeded(max($retryAfter, 0));
        }

        // Save the updated rate data
        file_put_contents($file, json_encode($rateData));
    }

    /**
     * Applies rate limiting using Joomla's persistent cache.
     *
     * This method leverages Joomla's Cache to store and retrieve
     * rate limit data, providing a more performant and persistent solution
     * when caching is enabled on the site.
     *
     * @param   string  $userIp         The IP address of the user.
     * @param   int     $maxRequests    The maximum number of allowed requests in the time window.
     * @param   int     $windowSeconds  The time window in seconds for the rate limit.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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

        // Calculate remaining requests and reset time
        $remainingRequests = $maxRequests - $rateData['count'];
        $resetTime         = $rateData['start'] + $windowSeconds;

        // Set rate-limiting headers
        $this->setRateLimitHeaders($remainingRequests, $resetTime);

        // Check if the rate limit is exceeded
        if ($rateData['count'] > $maxRequests) {
            $retryAfter = $resetTime - time();
            $this->handleRateLimitExceeded(max($retryAfter, 0));
        }

        // Save the updated rate data
        $cache->store($rateData, $cacheKey);
    }

    /**
     * Handles the response when a user exceeds the API rate limit.
     *
     * This method sends an HTTP 429 "Too Many Requests" response along with a "Retry-After" header.
     *
     * @param   int  $retryAfterSeconds  The number of seconds after which the client can retry.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function handleRateLimitExceeded(int $retryAfterSeconds): void
    {
        // Customize the behavior here (e.g., log the event, return a response, etc.)
        http_response_code(429); // HTTP 429 Too Many Requests
        $retryTime = gmdate('D, d M Y H:i:s', time() + $retryAfterSeconds) . ' GMT';
        header('Retry-After: ' . $retryTime);
        echo json_encode([
            'errors' => [
                [
                    'title' => 'Rate limit exceeded',
                    'code'  => 429,
                ],
            ],
        ]);

        exit;
    }

    /**
     * Sets the rate limit headers on the response.
     *
     * @param   int  $remaining  The number of requests remaining in the window.
     * @param   int  $resetTime  The Unix timestamp when the rate limit window resets.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setRateLimitHeaders(int $remainingRequests, int $resetTime): void
    {
        header('X-RateLimit-Remaining: ' . max($remainingRequests, 0));
        header('X-RateLimit-Reset: ' . $resetTime - time());
    }
}
