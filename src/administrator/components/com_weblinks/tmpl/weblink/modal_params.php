<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
use Joomla\CMS\Language\Text;
$fieldSets = $this->form->getFieldsets('params'); ?>
<?php foreach ($fieldSets as $name => $fieldSet) :
    ?>
    <div class="tab-pane" id="params-<?php echo $name; ?>">
    <?php if (isset($fieldSet->description) && trim($fieldSet->description)) :
        ?>
        <?php echo '<p class="alert alert-info">' . $this->escape(Text::_($fieldSet->description)) . '</p>'; ?>
        <?php
    endif; ?>
    <?php foreach ($this->form->getFieldset($name) as $field) :
        ?>
        <div class="control-group">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
     </div>
        <?php
    endforeach; ?>
    </div>
    <?php
endforeach; ?>
