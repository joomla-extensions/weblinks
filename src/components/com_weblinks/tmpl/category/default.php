<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Layout\LayoutHelper;

?>
<div class="com-weblinks-category">
    <?php
    $this->subtemplatename = 'items';
    echo LayoutHelper::render('joomla.content.category_default', $this);
    ?>
</div>
