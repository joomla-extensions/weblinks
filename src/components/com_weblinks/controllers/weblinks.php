<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 */

namespace Joomla\Component\Weblinks\Site\Controller;

// phpcs:ignoreFile -- allow _JEXEC check for Joomla module security
\defined('_JEXEC') or die;


class WeblinksControllerWeblink extends JControllerForm
{
    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data  = $this->input->get('jform', [], 'array');
        $model = $this->getModel('Weblink');

        if ($model->save($data)) {
            $this->setMessage(JText::_('COM_WEBLINKS_SAVE_SUCCESS'));
            $this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblinks', false));
        } else {
            $this->setMessage(JText::_('COM_WEBLINKS_SAVE_FAILED'));
            $this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=form', false));
        }
    }
}
