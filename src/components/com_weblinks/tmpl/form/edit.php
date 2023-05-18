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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
$captchaEnabled = false;
$captchaSet = $this->params->get('captcha', Factory::getApplication()->get('captcha', '0'));
foreach (PluginHelper::getPlugin('captcha') as $plugin) {
    if ($captchaSet === $plugin->name) {
        $captchaEnabled = true;
        break;
    }
}

// Create shortcut to parameters.
$params = $this->state->get('params');
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')) :
        ?>
   <div class="page-header">
      <h1>
            <?php echo $this->escape($this->params->get('page_heading')); ?>
      </h1>
  </div>
        <?php
    endif; ?>
    <form action="<?php echo Route::_('index.php?option=com_weblinks&view=form&w_id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">

        <?php echo $this->form->renderField('title'); ?>
        <?php echo $this->form->renderField('alias'); ?>
        <?php echo $this->form->renderField('catid'); ?>
        <?php echo $this->form->renderField('url'); ?>
        <?php echo $this->form->renderField('tags'); ?>

        <?php if ($params->get('save_history', 0)) :
            ?>
            <?php echo $this->form->renderField('version_note'); ?>
            <?php
        endif; ?>

        <?php if ($this->user->authorise('core.edit.state', 'com_weblinks.weblink')) :
            ?>
            <?php echo $this->form->renderField('state'); ?>
            <?php
        endif; ?>
        <?php echo $this->form->renderField('language'); ?>
        <?php echo $this->form->renderField('description'); ?>

        <?php echo $this->form->renderField('image_first', 'images'); ?>
        <?php echo $this->form->renderField('image_first_alt', 'images'); ?>
        <?php echo $this->form->renderField('image_first_alt_empty', 'images'); ?>
        <?php echo $this->form->renderField('float_first', 'images'); ?>
        <?php echo $this->form->renderField('image_first_caption', 'images'); ?>

        <?php echo $this->form->renderField('image_second', 'images'); ?>
        <?php echo $this->form->renderField('image_second_alt', 'images'); ?>
        <?php echo $this->form->renderField('image_second_alt_empty', 'images'); ?>
        <?php echo $this->form->renderField('float_second', 'images'); ?>
        <?php echo $this->form->renderField('image_second_caption', 'images'); ?>


        <?php if ($captchaEnabled) :
            ?>
            <div class="btn-group">
                <?php echo $this->form->renderField('captcha'); ?>
         </div>
            <?php
        endif; ?>

        <div class="mb-2">
         <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('weblink.save')">
               <span class="icon-check" aria-hidden="true"></span>
                <?php echo Text::_('JSAVE'); ?>
            </button>
          <button type="button" class="btn btn-danger" onclick="Joomla.submitbutton('weblink.cancel')">
              <span class="icon-times" aria-hidden="true"></span>
                <?php echo Text::_('JCANCEL'); ?>
          </button>
            <?php if ($this->params->get('save_history', 0) && $this->item->id) :
                ?>
                <?php echo $this->form->getInput('contenthistory'); ?>
                <?php
            endif; ?>
      </div>

        <input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
     <input type="hidden" name="task" value="" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
