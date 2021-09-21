<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;

if ($this->maxLevelcat != 0 && count($this->items[$this->parent->id]) > 0) :
?>
	<div class="com-weblinks-categories__items">
		<?php foreach ($this->items[$this->parent->id] as $id => $item) : ?>
			<?php if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) : ?>
				<div class="com-content-categories__item">
				<div>
					<a href="<?php echo Route::_(RouteHelper::getCategoryRoute($item->id, $item->language)); ?>">
						<?php echo $this->escape($item->title); ?></a>
						<?php if ($this->params->get('show_cat_num_links_cat') == 1) :?>
							<span class="badge bg-info">
								<?php echo Text::_('COM_WEBLINKS_NUM_ITEMS'); ?>&nbsp;
								<?php echo $item->numitems; ?>
							</span>
						<?php endif; ?>
				</div>
				<?php if ($this->maxLevelcat > 1 && count($item->getChildren()) > 0) : ?>
					<button
							type="button"
							id="category-btn-<?php echo $item->id; ?>"
							data-category-id="<?php echo $item->id; ?>"
							class="btn btn-secondary btn-sm float-end"
							aria-expanded="false"
							aria-label="<?php echo Text::_('JGLOBAL_EXPAND_CATEGORIES'); ?>"
					>
						<span class="icon-plus" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
				<?php if ($this->params->get('show_description_image') && $item->getParams()->get('image')) : ?>
					<img src="<?php echo $item->getParams()->get('image'); ?>" alt="<?php echo htmlspecialchars($item->getParams()->get('image_alt'), ENT_COMPAT, 'UTF-8'); ?>">
				<?php endif; ?>
				<?php if ($this->params->get('show_subcat_desc_cat') == 1) : ?>
					<?php if ($item->description) : ?>
						<div class="com-content-categories__description category-desc">
							<?php echo HTMLHelper::_('content.prepare', $item->description, '', 'com_weblinks.categories'); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ($this->maxLevelcat > 1 && count($item->getChildren()) > 0) : ?>
					<?php if (count($item->getChildren()) > 0 && $this->maxLevelcat > 1) : ?>
					<div class="com-content-categories__children" id="category-<?php echo $item->id; ?>" hidden="">
					<?php
					$this->items[$item->id] = $item->getChildren();
					$this->parent = $item;
					$this->maxLevelcat--;
					echo $this->loadTemplate('items');
					$this->parent = $item->getParent();
					$this->maxLevelcat++;
					?>
					</div>
				<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
