<?php


defined('_JEXEC') or die;
?>


<form action="<?php echo JRoute::_('index.php?option=com_weblinks&task=weblink.save'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset>
        <legend><?php echo JText::_('COM_WEBLINKS_FORM_LBL_LINK'); ?></legend>
        <div>
            <?php echo $this->form->renderField('title'); ?>
            <?php echo $this->form->renderField('url'); ?>
            <?php echo $this->form->renderField('description'); ?>
            <?php echo $this->form->renderField('catid'); ?>
            <?php echo $this->form->renderField('state'); ?>
            

        </div>
    </fieldset>

    <input type="hidden" name="task" value="weblink.save" />
    <?php echo JHtml::_('form.token'); ?>
</form>
