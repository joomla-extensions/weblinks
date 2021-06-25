<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;

$app = Factory::getApplication();

if ($app->isClient('site'))
{
	JSession::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

// Include the component HTML helpers.

HTMLHelper::_('behavior.core');
HTMLHelper::_('script', 'com_weblinks/admin-weblinks-modal.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
HTMLHelper::_('formbehavior.chosen', 'select');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
HTMLHelper::_('bootstrap.tooltip', '#filter_search', array('title' => Text::_($searchFilterDesc), 'placement' => 'bottom'));

$function  = $app->input->getCmd('function', 'jSelectWeblink');
$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	$app->getDocument()->addScriptOptions('xtd-weblinks', array('editor' => $editor));
	$onclick = "jSelectWeblink";
}

$iconStates = array(
	-2 => 'icon-trash',
	0 => 'icon-unpublish',
	1 => 'icon-publish',
	2 => 'icon-archive',
);

?>
<div class="container-popup">

	<form action="<?php echo JRoute::_('index.php?option=com_weblinks&view=weblinks&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1&editor=' . $editor); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"></div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th width="1%" class="center nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php $lang = ''; ?>
					<?php if ($item->language && Multilanguage::isEnabled()) : ?>
						<?php $tag = strlen($item->language); ?>
						<?php if ($tag == 5) : ?>
							<?php $lang = substr($item->language, 0, 2); ?>
						<?php elseif ($tag == 6) : ?>
							<?php $lang = substr($item->language, 0, 3); ?>
						<?php endif; ?>
					<?php endif; ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>"></span>
						</td>
						<td>
							<?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
								. ' data-id="' . $item->id . '"'
								. ' data-title="' . $this->escape(addslashes($item->title)) . '"'
								. ' data-cat-id="' . $this->escape($item->catid) . '"'
								. ' data-uri="' . $this->escape(RouteHelper::getWeblinkRoute($item->id, $item->catid, $item->language)) . '"'
								. ' data-language="' . $this->escape($lang) . '"';
							?>
							<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
								<?php echo $this->escape($item->title); ?>
							</a>
							<div class="small">
								<?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
							</div>
						</td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<td class="nowrap small hidden-phone">
							<?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="nowrap small hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
