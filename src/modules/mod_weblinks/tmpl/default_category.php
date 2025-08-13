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

if (!$categoryNode) {
    return;
}

$hasWeblinks = !empty($categoryNode->weblinks);

$selectedCategories = (array) $params->get('catid', []);

// check if the "Show Parent Category" option is turned off
$hideParent = !$params->get('show_parent_category', 0);

// a category is a root if it's selected and its parent is not
$parent = $categoryNode->category->getParent();

$isCurrentSelected = in_array($categoryNode->category->id, $selectedCategories);
$isParentSelected = in_array($parent->id, $selectedCategories);

$isRootCategory = $isCurrentSelected && !$isParentSelected;

// We should skip rendering the content of this category if it's the root and the "hide parent" option is on
$skipContent = $isRootCategory && $hideParent;

// Render the category content only if it has weblinks and we are not skipping it
if ($hasWeblinks && !$skipContent) {
    $cssClass = 'weblinks-category';

    // Apply padding based on the nesting level. Level 0 is the root.
    $firstDisplayedLevel = $hideParent ? 1 : 0;
    if ($categoryNode->level > $firstDisplayedLevel) {
        $cssClass .= ' ps-4';
    }

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
