<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));

$app = JFactory::getApplication();
$input = $app->input;

$assoc = JLanguageAssociations::isEnabled();

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'images', 'item_associations', 'jmetadata');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'weblink.cancel' || document.formvalidator.isValid(document.getElementById('weblink-form'))) {
			" . $this->form->getField('description')->save() . "
			Joomla.submitform(task, document.getElementById('weblink-form'));
		}
	};
");

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo JRoute::_('index.php?option=com_weblinks&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="weblink-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_WEBLINKS_NEW_WEBLINK', true) : JText::_('COM_WEBLINKS_EDIT_WEBLINK', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<div class="form-vertical">
					<?php echo $this->form->renderField('url'); ?>
					<?php echo $this->form->renderField('description'); ?>
				</div>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'images', JText::_('JGLOBAL_FIELDSET_IMAGE_OPTIONS', true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<?php echo $this->form->renderField('images'); ?>
					<?php foreach ($this->form->getGroup('images') as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</div>
			</div>

		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
		
		<?php if (!$isModal && $assoc) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
			<?php echo $this->loadTemplate('associations'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php elseif ($isModal && $assoc) : ?>
			<div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
