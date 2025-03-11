<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;
$published = $this->state->get('filter.published');
?>

<div class="p-3">
  <div class="row">
        <?php if (Multilanguage::isEnabled()) :
            ?>
           <div class="form-group col-md-6">
              <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
               </div>
         </div>
            <?php
        endif; ?>
     <div class="form-group col-md-6">
          <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
         </div>
     </div>
 </div>
 <div class="row">
        <?php if ($published >= 0) :
            ?>
          <div class="form-group col-md-6">
              <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_weblinks']); ?>
              </div>
         </div>
            <?php
        endif; ?>
     <div class="form-group col-md-6">
          <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.tag', []); ?>
            </div>
     </div>
 </div>
</div>
