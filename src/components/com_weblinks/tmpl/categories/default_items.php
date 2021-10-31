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
	<div class="com-content-categories__items">
		<?php foreach ($this->items[$this->parent->id] as $id => $item) : ?>
			<?php if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) : ?>
			<div class="com-content-categories__item">
				<div class="w-100">
					<a href="<?php echo Route::_(RouteHelper::getCategoryRoute($item->id, $item->language)); ?>">
						<?php echo $this->escape($item->title); ?></a>
					<?php if ($this->params->get('show_cat_num_links_cat') == 1) :?>
						<span class="badge bg-info ">
								<?php echo Text::_('COM_WEBLINKS_NUM_ITEMS'); ?>&nbsp;
								<?php echo $item->numitems; ?>
						</span>
					<?php endif; ?>
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
				</div>

				<?php if ($this->params->get('show_subcat_desc_cat') == 1 && !empty($item->description)) : ?>
					<div class="category-desc">
						<?php echo HTMLHelper::_('content.prepare', $item->description, '', 'com_weblinks.categories'); ?>
					</div>
				<?php endif; ?>

				<?php if ($this->params->get('show_description_image') && !empty($item->getParams()->get('image'))) : ?>
					<?php
						$params = $item->getParams();
						$img = HTMLHelper::cleanImageURL($params->get('image'));
						$alt = '';
						if (!empty($params->get('image_alt'))) :
							$alt = 'alt="' . htmlspecialchars($params->get('image_alt'), ENT_COMPAT, 'UTF-8') . '"';
						elseif (!empty($params->get('image_alt_empty'))) :
							$alt = 'alt=""';
						endif;
					?>
					<img src="<?php echo htmlspecialchars($img->url, ENT_COMPAT, 'UTF-8'); ?>"<?php echo $alt; ?>>
				<?php endif; ?>


				<?php if ($this->maxLevelcat > 1 && count($item->getChildren()) > 0) : ?>
					<div class="com-content-categories__children" id="category-<?php echo $item->id; ?>" hidden>
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
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
