<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Weblinks list controller class.
 *
 * @since  1.6
 */
class WeblinksController extends AdminController
{
    /**
     * Proxy for getModel
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Weblink', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
    /**
     * Method to get the JSON-encoded amount of published articles
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('Weblinks');

        $model->setState('filter.published', 1);

        $amount = 0;
        if ($model) {
            $amount = (int) $model->getTotal();
        }

        $responseData = [
            'amount' => $amount,
        ];

        echo new JsonResponse($responseData);
    }
}
