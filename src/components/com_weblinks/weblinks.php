<?php

\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
// Execute the task
$controller = BaseController::getInstance('Weblinks');
$controller->execute(Factory::getApplication()->input->get('task', 'display'));
$controller->redirect();
