<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 */

namespace Joomla\Component\Weblinks\Site\View\Form;

class WeblinksViewForm extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (\count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        parent::display($tpl);
    }
}
