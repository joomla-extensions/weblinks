<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (empty($weblinks)) {
    return;
}
?>
<ul class="weblinks<?php echo $moduleclass_sfx; ?>">
    <?php foreach ($weblinks as $item) :
        ?>
    <li><div class="d-flex flex-wrap">
 <div class="col flex-sm-grow-1">
        <?php
        $link   = $item->link;
        $width  = (int) $item->params->get('width', 600);
        $height = (int) $item->params->get('height', 500);
        switch ($item->params->get('target')) {
            case 1:
                // Open in a new window
                echo '<a href="' . $link . '" target="_blank" rel="' . $params->get('follow', 'nofollow') . '">' .
                htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';

                break;
            case 2:
                // Open in a popup window
                $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' . $width . ',height=' . $height;
                echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '" . $attribs . "'); return false;\">" .
                htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';

                break;
            case 3:
                // Open in a modal window
                $modalId                   = 'weblink-item-modal-' . $item->id;
                $modalParams['title']      = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8');
                $modalParams['url']        = $link;
                $modalParams['height']     = '100%';
                $modalParams['width']      = '100%';
                $modalParams['bodyHeight'] = 70;
                $modalParams['modalWidth'] = 80;
                echo HTMLHelper::_('bootstrap.renderModal', $modalId, $modalParams);
                echo '<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#' . $modalId . '">
                ' . $item->title . '</button>';

                break;
            default:
                // Open in parent window
                echo '<a href="' . $link . '" rel="' . $params->get('follow', 'nofollow') . '">' .
                htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';

                break;
        }
        ?>
  </div>
        <?php echo $params->get('description', 0) ? '<div class="col flex-sm-grow-1">' . $item->description . '</div>' : ''; ?>
        <?php if ($params->get('hits', 0)) :
            ?>
     <div class="col  flex-sm-grow-1">
            <span class="badge bg-info float-md-end"><?php echo $item->hits . ' ' . Text::_('MOD_WEBLINKS_HITS'); ?></span>
      </div>
            <?php
        endif; ?>
    </div>
 </li>
        <?php
    endforeach; ?>
</ul>
