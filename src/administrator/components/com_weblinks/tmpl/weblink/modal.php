<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_weblinks
 *
 * @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
* 
 *
 * @var \Joomla\Component\Weblinks\Administrator\View\Weblink\HtmlView $this 
*/

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['placement' => 'bottom']);
$function = Factory::getApplication()->getInput()->getCmd('function', 'jEditWeblink_' . (int) $this->getItem()->id);
$wa       = $this->getApplication()->getDocument()->getWebAssetManager();

$wa->addInlineScript(
    '
    function jEditWeblinkModal() {
        if (window.parent && document.formvalidator.isValid(document.getElementById("weblink-form"))) {
            return window.parent.' . $this->escape($function) . '(document.getElementById("jform_title").value);
        }
    }
'
);

?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('weblink.apply'); jEditWeblinkModal();"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('weblink.save'); jEditWeblinkModal();"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('weblink.cancel');"></button>

<div class="container-popup">
    <?php $this->setLayout('edit'); ?>
    <?php echo $this->loadTemplate(); ?>
</div>
