<?php

/**
 * @package     Joomla.Site
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_WEBLINKS',
    'formURL'    => 'index.php?option=com_weblinks',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Weblinks',
    'icon'       => 'icon-globe weblink',
];
$user = Factory::getApplication()->getIdentity();
if ($user->authorise('core.create', 'com_weblinks') || count($user->getAuthorisedCategories('com_weblinks', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_weblinks&task=weblink.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
