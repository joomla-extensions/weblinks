<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Helper\ModuleHelper;

if ($params->get('groupby', 0)) {
    $categoryNode = $list;
    require ModuleHelper::getLayoutPath('mod_weblinks', $params->get('layout', 'default') . '_category');
} else {
    $weblinks = $list;
    require ModuleHelper::getLayoutPath('mod_weblinks', $params->get('layout', 'default') . '_items');
}
