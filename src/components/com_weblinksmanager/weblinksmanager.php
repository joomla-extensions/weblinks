<?php

/**
 * @package    Joomla.Site
 * @subpackage com_weblinksmanager
 *
 * @copyright Copyright (C)
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// Execute the component's controller
$controller = JControllerLegacy::getInstance('Weblinksmanager');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
