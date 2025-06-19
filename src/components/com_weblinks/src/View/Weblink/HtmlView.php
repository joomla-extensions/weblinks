<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Site\View\Weblink;

use Joomla\CMS\Event\Content;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Weblinks\Site\Model\WeblinkModel;
use Joomla\Registry\Registry;

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
     * @since  __DEPLOY_VERSION__
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
        $model        = $this->getModel();
        $this->item   = $model->getItem();
        $this->state  = $model->getState();
        $this->params = $this->state->get('params');

        $errors = $model->getErrors();

        if (\count($errors) > 0) {
            $this->handleModelErrors($errors);
        }

        // Create a shortcut for $item.
        $item         = $this->item;
        $item->slug   = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
        $temp         = $item->params;
        $registry     = new Registry($temp);
        $item->params = clone $app->getParams();
        $item->params->merge($registry);
        $offset = $this->state->get('list.offset');

        $dispatcher = $this->getDispatcher();

        // Process the content plugins.
        PluginHelper::importPlugin('content', null, true, $dispatcher);

        $contentEventArguments = [
            'context' => 'com_weblinks.weblink',
            'subject' => $item,
            'params'  => $item->params,
            'page'    => $offset,
        ];

        $dispatcher->dispatch(
            'onContentPrepare',
            new Content\ContentPrepareEvent('onContentPrepare', $contentEventArguments)
        );

        // Extra content from events
        $item->event   = new \stdClass();
        $contentEvents = [
            'afterDisplayTitle'    => new Content\AfterTitleEvent('onContentAfterTitle', $contentEventArguments),
            'beforeDisplayContent' => new Content\BeforeDisplayEvent('onContentBeforeDisplay', $contentEventArguments),
            'afterDisplayContent'  => new Content\AfterDisplayEvent('onContentAfterDisplay', $contentEventArguments),
        ];

        foreach ($contentEvents as $resultKey => $event) {
            $results = $dispatcher->dispatch($event->getName(), $event)->getArgument('result', []);

            $item->event->{$resultKey} = $results ? trim(implode("\n", $results)) : '';
        }

        parent::display($tpl);
    }

    /**
     * Handle errors returned by model
     *
     * @param   array  $errors
     *
     * @return void
     * @throws \Exception
     */
    private function handleModelErrors(array $errors): void
    {
        foreach ($errors as $error) {
            // Throws 404 error if weblink item not found
            if ($error instanceof \Exception && $error->getCode() === 404) {
                throw $error;
            }
        }

        // Otherwise, it is database runtime error, and we will throw error 500
        throw new GenericDataException(implode("\n", $errors), 500);
    }
}
