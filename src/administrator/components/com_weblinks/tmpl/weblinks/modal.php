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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;

$app = Factory::getApplication();

if ($app->isClient('site'))
{
	Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

HTMLHelper::_('behavior.multiselect');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('com_weblinks.admin-weblinks-modal');

$function  = $app->input->getCmd('function', 'jSelectWeblink');
$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);
$multilang = Multilanguage::isEnabled();

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	$this->document->addScriptOptions('xtd-weblinks', array('editor' => $editor));
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

	<form action="<?php echo Route::_('index.php?option=com_weblinks&view=weblinks&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken() . '=1&editor=' . $editor); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-sm">
				<caption class="visually-hidden">
				<?php echo Text::_('COM_WEBLINKS_WEBLINKS_TABLE_CAPTION'); ?>,
					<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
					<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
				</caption>
				<thead>
					<tr>
						<th scope="col" class="w-1 text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<?php if ($multilang) : ?>
							<th scope="col" class="w-15">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-1 d-none d-md-table-cell">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php $lang = ''; ?>
					<?php if ($item->language && $multilang) : ?>
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
						<th scope="row">
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
						</th>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<?php if ($multilang) : ?>
							<td class="small">
								<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
							</td>
						<?php endif; ?>
						<td class="small d-none d-md-table-cell">
							<?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php // load the pagination. ?>
		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
