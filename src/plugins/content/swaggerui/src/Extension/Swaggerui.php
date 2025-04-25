<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.swaggerui
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Swaggerui\Extension;

use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Swagger UI Plugin.
 *
 * @since  5.0.0
 */

final class Swaggerui extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(ContentPrepareEvent $event)
    {
        // Get content item
        $item = $event->getItem();
        $yaml = $this->params->get('openapiyaml', 'openapi.yaml');

        if (strpos($item->text, '{swaggerui}') === false) {
            return;
        }
        // Get the WebAssetManager
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();
        // Populate the media config
        $config = [
            'baseUrl'     => Uri::base(),
            'openApiYaml' => $yaml,
        ];
        $this->getApplication()->getDocument()->addScriptOptions('swagger-ui', $config);
        // Register and use the Swagger UI CSS
        $wa->registerAndUseStyle('plg_content_swaggerui_index', 'media/plg_content_swaggerui/js/index.css');
        $wa->registerAndUseStyle('plg_content_swaggerui', 'media/plg_content_swaggerui/js/swagger-ui.css');

        // Register and use the Swagger UI JS bundle
        $wa->registerAndUseScript('plg_content_swaggerui_bundle', 'plg_content_swaggerui/swagger-ui-bundle.js', [], ['defer' => true]);
        $wa->registerAndUseScript('plg_content_swaggerui_preset', 'plg_content_swaggerui/swagger-ui-standalone-preset.js', [], ['defer' => true]);
        // Add the Swagger UI initialization as an inline script
        $wa->addInlineScript(
            <<<JS
      const options = window.Joomla.getOptions('swagger-ui');

window.onload = function() {
window.ui = SwaggerUIBundle({
  url: options.baseUrl + "media/plg_content_swaggerui/js/" + options.openApiYaml,
  dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout",
});
}
JS
        );
        $swaggerHtml = <<<HTML
<div id="swagger-ui"></div>
HTML;

        $item->text = str_replace('{swaggerui}', $swaggerHtml, $item->text);
    }
}
