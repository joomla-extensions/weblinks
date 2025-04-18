<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Weblink;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Weblinks\Site\Model\WeblinkModel;
use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Content\AfterDisplayEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML Weblink View class for the Weblinks component
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The weblink object
     *
     * @var    \JObject
     */
    protected $item;

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     */
    protected $params;

    /**
     * The item model state
     *
     * @var    \Joomla\Registry\Registry
     * @since  1.6
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        /* @var WeblinkModel $model */
        $model = $this->getModel();

        try {
            $this->item   = $model->getItem();
            $this->state  = $model->getState();
            $this->params = $this->state->get('params');
        } catch (\Exception $e) {
            // Handle 404 error if weblink item not found
            if ($e->getCode() === 404) {
                throw $e;
            }

            // Otherwise, it is database runtime error, and we will throw error 500
            throw new GenericDataException($e->getMessage(), 500, $e);
        }

        PluginHelper::importPlugin('content');

        // Create a shortcut for $item.
        $item         = $this->item;
        $item->slug   = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
        $temp         = $item->params;
        $item->params = clone $app->getParams();
        $item->params->merge($temp);
        $offset = $this->state->get('list.offset');

        $dispatcher = $app->getDispatcher();
        $item->event = new \stdClass();
        
        $item->event->afterDisplayTitle = '';
        $item->event->beforeDisplayContent = '';
        $item->event->afterDisplayContent = '';

        $eventAfterTitleArgs = ['context' => 'com_weblinks.weblink', 'subject' => $item, 'params' => $item->params, 'offset' => $offset];
        $eventAfterTitle = AfterTitleEvent::create('onContentAfterTitle', $eventAfterTitleArgs);
        $dispatcher->dispatch('onContentAfterTitle', $eventAfterTitle);

        $eventBeforeDisplayArgs = ['context' => 'com_weblinks.weblink', 'subject' => $item, 'params' => $item->params, 'offset' => $offset];
        $eventBeforeDisplay = BeforeDisplayEvent::create('onContentBeforeDisplay', $eventBeforeDisplayArgs);
        $dispatcher->dispatch('onContentBeforeDisplay', $eventBeforeDisplay);

        $eventAfterDisplayArgs = ['context' => 'com_weblinks.weblink', 'subject' => $item, 'params' => $item->params, 'offset' => $offset];
        $eventAfterDisplay = AfterDisplayEvent::create('onContentAfterDisplay', $eventAfterDisplayArgs);
        $dispatcher->dispatch('onContentAfterDisplay', $eventAfterDisplay);

        parent::display($tpl);
    }
}