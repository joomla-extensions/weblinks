<?php

/**
 * @package     Joomla.Site
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
$assoc = Associations::isEnabled();
// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'images', 'item_associations', 'jmetadata');
$this->useCoreUI = true;

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

        <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

        <div class="main-card">
            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_WEBLINKS_NEW_WEBLINK', true) : Text::_('COM_WEBLINKS_EDIT_WEBLINK', true)); ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-vertical">
                        <div>
                            <fieldset class="adminform">
                                <?php echo $this->form->renderField('url'); ?>
                                <?php echo $this->form->renderField('description'); ?>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('JGLOBAL_FIELDSET_IMAGE_OPTIONS', true)); ?>
            <div class="row">
                <div class="col-12">
                    <fieldset id="fieldset-image; ?>" class="options-form">
                        <legend><?php echo Text::_('JGLOBAL_FIELDSET_IMAGE_OPTIONS'); ?></legend>
                        <div>
                            <?php echo $this->form->renderField('JGLOBAL_FIELDSET_IMAGE_OPTIONS'); ?>
                            <?php foreach ($this->form->getGroup('images') as $field) :
                                ?>
                                <?php echo $field->renderField(); ?>
                                <?php
                            endforeach; ?>
                        </div>
                    </fieldset>
                </div>
            </div>

            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <fieldset id="fieldset-publishingdata" class="options-form">
                        <legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
                        <?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                    </fieldset>
                </div>
                <div class="col-12 col-lg-6">
                    <fieldset id="fieldset-metadata" class="options-form">
                        <legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
                        <?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
                    </fieldset>
                </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

            <?php if ($assoc) :
                ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
                <fieldset id="fieldset-associations" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
                    <?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
                </fieldset>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php endif; ?>

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
