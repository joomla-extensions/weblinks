<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Weblinks\Administrator\View\Weblinks\HtmlView $this */

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$assoc     = JLanguageAssociations::isEnabled();

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_weblinks&task=weblinks.saveOrderAjax&tmpl=component';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_weblinks&view=weblinks'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="weblinkList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_WEBLINKS_WEBLINKS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="w-1 text-center d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
							</th>
							<th scope="col" style="min-width:85px" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
							</th>
							<?php if ($assoc) : ?>
								<th scope="col" class="w-10">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WEBLINKS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>
							<?php if (Multilanguage::isEnabled()) : ?>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>
							<th scope="col" class="w-5 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item) : ?>
							<?php $ordering       = ($listOrder == 'a.ordering'); ?>
							<?php $item->cat_link = Route::_('index.php?option=com_categories&extension=com_weblinks&task=edit&type=other&cid[]=' . $item->catid); ?>
							<?php $canCreate      = $user->authorise('core.create',     'com_weblinks.category.' . $item->catid); ?>
							<?php $canEdit        = $user->authorise('core.edit',       'com_weblinks.category.' . $item->catid); ?>
							<?php $canCheckin     = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0; ?>
							<?php $canEditOwn     = $user->authorise('core.edit.own',   'com_weblinks.category.' . $item->catid) && $item->created_by == $user->id; ?>
							<?php $canChange      = $user->authorise('core.edit.state', 'com_weblinks.category.' . $item->catid) && $canCheckin; ?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->catid; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass; ?>">
										<span class="icon-ellipsis-v" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" name="order[]" size="5"
											   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'weblinks.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
								</td>
								<th scope="row" class="has-context">
									<div>
										<?php if ($item->checked_out) : ?>
											<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'weblinks.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($canEdit || $canEditOwn) : ?>
											<a href="<?php echo Route::_('index.php?option=com_weblinks&task=weblink.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
												<?php echo $this->escape($item->title); ?></a>
										<?php else : ?>
											<?php echo $this->escape($item->title); ?>
										<?php endif; ?>
										<span class="small">
											<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
										</span>
										<div class="small">
											<?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
										</div>
									</div>
								</th>
								<td class="small d-none d-md-table-cell">
									<?php echo $this->escape($item->access_level); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->hits; ?>
								</td>
								<?php if ($assoc) : ?>
									<td class="hidden-phone hidden-tablet">
										<?php if ($item->association) : ?>
											<?php echo HTMLHelper::_('weblinksadministrator.association', $item->id); ?>
										<?php endif; ?>
									</td>
								<?php endif; ?>
								<?php if (Multilanguage::isEnabled()) : ?>
									<td class="small d-none d-md-table-cell">
										<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
									</td>
								<?php endif; ?>
								<td class="d-none d-md-table-cell">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // Load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', 'com_weblinks')
						&& $user->authorise('core.edit', 'com_weblinks')
						&& $user->authorise('core.edit.state', 'com_weblinks')) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							[
								'title' => Text::_('com_weblinks_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer')
							],
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
