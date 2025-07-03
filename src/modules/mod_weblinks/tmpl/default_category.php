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

if (!isset($firstRender)) {
    static $firstRender = true;
}

if (!$categoryNode) {
    return;
}

$hasWeblinks = !empty($categoryNode->weblinks);

// check if the current category has any weblinks.
// We only create a div and apply logic if it does.
if ($hasWeblinks) {
    $cssClass = 'weblinks-category';

    // Apply padding only if this is NOT the first category.
    if (!$firstRender) {
        $cssClass .= ' ps-4';
    }

    // To apply padding for the rest of the categories.
    $firstRender = false;

    // Echo the opening tag BEFORE processing children.
    echo '<div class="' . $cssClass . '">';

    if ($params->get('groupby_showtitle', 1)) {
        echo '<strong>' . htmlspecialchars($categoryNode->category->title, ENT_COMPAT, 'UTF-8') . '</strong>';
    }

    $weblinks = $categoryNode->weblinks;
    require ModuleHelper::getLayoutPath('mod_weblinks', $params->get('layout', 'default') . '_items');

    // Now, process any children so they are nested inside the parent.
    foreach ($categoryNode->children as $child) {
        $originalCategoryNode = $categoryNode;
        $categoryNode = $child;
        require ModuleHelper::getLayoutPath('mod_weblinks', $params->get('layout', 'default') . '_category');
        $categoryNode = $originalCategoryNode;
    }

    echo '</div>';
} else {
    // If the category has no weblinks, don't render it.
    // But, we process its children to see if they have weblinks.
    foreach ($categoryNode->children as $child) {
        $originalCategoryNode = $categoryNode;
        $categoryNode = $child;
        require ModuleHelper::getLayoutPath('mod_weblinks', $params->get('layout', 'default') . '_category');
        $categoryNode = $originalCategoryNode;
    }
}
