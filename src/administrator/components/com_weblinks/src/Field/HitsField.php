<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Hits field for Weblinks.
 * Renders a read-only input for hits and a reset button.
 *
 * @since  __DEPLOY_VERSION__
 */
class HitsField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'Hits';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        $onclick = ' onclick="document.getElementById(\'' . $this->id . '\').value=\'0\';"';

        return '<div class="input-group"><input class="form-control" type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" readonly="readonly">'
            . '<button type="button" class="btn btn-secondary" ' . $onclick . '>'
            . '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JRESET') . '</button></div>';
    }
}
