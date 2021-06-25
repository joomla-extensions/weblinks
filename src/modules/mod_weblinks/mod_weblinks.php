<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Weblinks\Site\Helper\WeblinksHelper;

$model = $app->bootComponent('com_weblinks')->getMVCFactory()
	->createModel('Category', 'Site', ['ignore_request' => true]);

$list = WeblinksHelper::getList($params, $model, $app);

if (!count($list))
{
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require ModuleHelper::getLayoutPath('mod_weblinks', $params->get('layout', 'default'));
