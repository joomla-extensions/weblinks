<?php

/**
 * @package     Joomla.Site
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
$accessOptions = HTMLHelper::_('access.assetgroups');
$categoryOptions = HTMLHelper::_('category.options', 'com_weblinks');
$languageOptions = [];
if (Multilanguage::isEnabled()) {
    $languageOptions = HTMLHelper::_('contentlanguage.existing', true, true);
}
?>

<div class="container">
    <div class="row">
        <div class="form-group col-md-6">
            <label for="batch_access"><?php echo Text::_('JLIB_HTML_BATCH_ACCESS_LABEL'); ?></label>
            <select name="batch[access]" id="batch_access" class="form-select">
                <option value=""><?php echo Text::_('JLIB_HTML_BATCH_NOCHANGE'); ?></option>
                <?php echo HTMLHelper::_('select.options', $accessOptions, 'value', 'text'); ?>
            </select>
        </div>

        <div class="form-group col-md-6">
            <label for="batch_category_id"><?php echo Text::_('JLIB_HTML_BATCH_CATEGORY_LABEL'); ?></label>
            <select name="batch[category]" id="batch_category_id" class="form-select">
                <option value=""><?php echo Text::_('JLIB_HTML_BATCH_NOCHANGE'); ?></option>
                <?php echo HTMLHelper::_('select.options', $categoryOptions, 'value', 'text'); ?>
            </select>
        </div>

        <?php if (Multilanguage::isEnabled()) :
            ?>
            <div class="form-group col-md-6">
                <label for="batch_language"><?php echo Text::_('JLIB_HTML_BATCH_LANGUAGE_LABEL'); ?></label>
                <select name="batch[language]" id="batch_language" class="form-select">
                    <option value=""><?php echo Text::_('JLIB_HTML_BATCH_NOCHANGE'); ?></option>
                    <?php echo HTMLHelper::_('select.options', $languageOptions, 'value', 'text'); ?>
                </select>
            </div>
            <?php
        endif; ?>
    </div>
</div>
