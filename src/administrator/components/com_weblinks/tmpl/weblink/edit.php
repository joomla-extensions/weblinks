<?php

/**
 * @package    Joomla.Administrator
 * @subpackage Weblinks
 *
 * @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
$app = Factory::getApplication();
$input = $app->getInput();
$assoc = Associations::isEnabled();
// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'images', 'item_associations', 'jmetadata');
$this->useCoreUI = true;
// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo Route::_('index.php?option=com_weblinks&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="weblink-form" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_WEBLINKS_NEW_WEBLINK', true) : Text::_('COM_WEBLINKS_EDIT_WEBLINK', true)); ?>
        <div class="row">
          <div class="col-md-9">
             <div class="form-vertical">
                    <div>
                      <fieldset class="adminform">
                            <?php echo $this->form->renderField('url'); ?>
                            <?php echo $this->form->renderField('description'); ?>
                     </fieldset>
                    </div>
             </div>
         </div>
         <div class="col-md-3">
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
                        <?php echo $this->form->renderField('imaJGLOBAL_FIELDSET_IMAGE_OPTIONSges'); ?>
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

        <?php if (!$isModal && $assoc) :
            ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
             <fieldset id="fieldset-associations" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
                    <?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
             </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        elseif ($isModal && $assoc) :
            ?>
            <div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
            <?php
        endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

 </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
