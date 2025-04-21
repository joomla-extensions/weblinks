<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 */

// phpcs:ignoreFile -- allow _JEXEC check for Joomla module security

namespace Joomla\Component\Weblinks\Site\Model;

\defined('_JEXEC') or die;

class WeblinksModelWeblink extends JModelAdmin
{
    public function getTable($type = 'Weblink', $prefix = 'WeblinksTable', $config = [])
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_weblinks.weblink', 'weblink', ['control' => 'jform', 'load_data' => $loadData]);
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        return JFactory::getApplication()->getUserState('com_weblinks.edit.weblink.data', []);
    }
}
