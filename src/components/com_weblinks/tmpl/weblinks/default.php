<?php

/**
 * @package     Joomla.Site
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Associations;

/** @var \Joomla\Component\Weblinks\Site\View\Weblinks\HtmlView $this */

$user      = $this->getCurrentUser();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$assoc     = Associations::isEnabled();

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('toolbar', 'com_weblinks/toolbar.css');

?>
<form action="<?php echo Route::_('index.php?option=com_weblinks&view=weblinks'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?php echo $this->getDocument()->getToolbar('toolbar')->render(); ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table" id="weblinkList">
                <thead>
                    <tr>
                        <th width="1%" class="text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                        <th scope="col" class="w-1 text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JCATEGORY', 'c.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($assoc) : ?>
                            <th scope="col" class="d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_WEBLINKS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th scope="col" class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $canEdit = $user->authorise('core.edit', 'com_weblinks.weblink.' . $item->id);
                        $canEditOwn = $user->authorise('core.edit.own', 'com_weblinks.weblink.' . $item->id) && $item->created_by == $userId;
                        $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || !$item->checked_out;
                        $canEditOwn = $user->authorise('core.edit.own', 'com_weblinks') && $item->created_by == $userId;
                        $canChange  = $user->authorise('core.edit.state', 'com_weblinks.category.' . $item->catid) && $canCheckin;
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center">
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'weblinks.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                            </td>
                            <td>
                                <?php if ($canEdit || $canEditOwn) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_weblinks&task=weblink.edit&id=' . (int) $item->id); ?>">
                                        <?php echo $this->escape($item->title); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>
                                <div class="small">
                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape($item->category_title); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape($item->access_level); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo (int) $item->hits; ?>
                            </td>
                            <?php if ($assoc) : ?>
                                <td class="d-none d-md-table-cell">
                                    <?php if ($item->association) : ?>
                                        <?php echo HTMLHelper::_('weblinksadministrator.association', $item->id); ?>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="d-none d-md-table-cell">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php // Load the pagination.
            ?>
            <?php echo $this->pagination->getListFooter(); ?>

            <?php // Load the batch processing form.
            ?>
            <?php if (
                $user->authorise('core.create', 'com_weblinks')
                && $user->authorise('core.edit', 'com_weblinks')
                && $user->authorise('core.edit.state', 'com_weblinks')
) :
    ?>
                <?php echo HTMLHelper::_('bootstrap.renderModal', 'collapseModal', [
                    'title' => Text::_('COM_WEBLINKS_BATCH_OPTIONS'),
                    'footer' => $this->loadTemplate('batch_footer')
                ], $this->loadTemplate('batch_body')); ?>
            <?php
            endif; ?>
            <?php
        endif; ?>
        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
