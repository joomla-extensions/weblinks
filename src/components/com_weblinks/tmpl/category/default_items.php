<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');

// Create a shortcut for params.
$params = &$this->category->params;

// Get the user object.
$user = Factory::getApplication()->getIdentity();

// Check if user is allowed to add/edit based on weblinks permissinos.
$canEdit = $user->authorise('core.edit', 'com_weblinks.category.' . $this->category->id);
$canCreate = $user->authorise('core.create', 'com_weblinks');
$canEditState = $user->authorise('core.edit.state', 'com_weblinks');

$n = count($this->items);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<div class="com-weblinks-category__items">
	<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
		<?php if ($this->params->get('filter_field')) : ?>
			<div class="com-weblinks-category__filter btn-group">
				<label class="filter-search-lbl visually-hidden" for="filter-search">
					<?php echo Text::_('COM_WEBLINKS_FILTER_SEARCH_DESC'); ?>
				</label>
				<input
					type="text"
					name="filter-search"
					id="filter-search"
					value="<?php echo $this->escape($this->state->get('list.filter')); ?>"
					class="inputbox" onchange="document.adminForm.submit();"
					placeholder="<?php echo Text::_('COM_WEBLINKS_FILTER_SEARCH_DESC'); ?>"
				>
				<button type="submit" name="filter_submit" class="btn btn-primary"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
				<button type="reset" name="filter-clear-button" class="btn btn-secondary"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
		<?php endif; ?>
		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="com-weblinks-category__pagination btn-group float-end">
				<label for="limit" class="visually-hidden">
					<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>
		<?php if (empty($this->items)) : ?>
			<?php if ($this->params->get('show_no_contacts', 1)) : ?>
				<div class="alert alert-info">
					<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
					<?php echo Text::_('COM_WEBLINKS_NO_WEBLINKS'); ?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<ul class="category list-unstyled">
				<?php foreach ($this->items as $i => $item) : ?>
                    <?php
                    // Shouldn't this be only for users with admin rights?
                    // @ToDo: what is the difference -class system-unbublished?
                    if ($item->state == 0) : ?>
                        <li class="system-unpublished list-group mt-3">
                    <?php else : ?>
                        <li class="list-group mt-3">
                    <?php endif; ?>

                    <?php if ($canEdit) : ?>
                        <div class="icons list-group-item">
                            <?php echo HTMLHelper::_('weblinkicon.edit', $item, $this->params); ?>
                        </div>
                    <?php endif; ?>

                    <div class="list-title list-group-item ">
                        <?php if (!$this->params->get('icons', 1)) : ?>
                            <?php echo Text::_('COM_WEBLINKS_LINK'); ?>
                        <?php else : ?>
                            <?php // ToDo css icons as variables ?>
                            <?php if (!$this->params->get('link_icons')) : ?>
                                <span class="icon-globe" aria-hidden="true"></span>
                            <?php else: ?>
                                <?php echo '<img src="' . $this->params->get('link_icons') . '" alt="' . Text::_('COM_WEBLINKS_LINK') . '" />'; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php // Compute the correct link ?>
                        <?php $menuclass = 'category' . $this->pageclass_sfx; ?>
                        <?php $link   = $item->link; ?>
                        <?php $width  = $item->params->get('width', 600); ?>
                        <?php $height = $item->params->get('height', 500); ?>
                        
                        <?php if ($item->state == 0) : ?>
                            <span class="badge bg-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
                        <?php endif; ?>

                        <?php
                        switch ($item->params->get('target', $this->params->get('target')))
                        {
                            case 1:
                                // Open in a new window
                                echo '<a href="' . $link . '" target="_blank" class="' . $menuclass . '" rel="nofollow">' .
                                    $this->escape($item->title) . '</a>';
                                break;

                            case 2:
                                // Open in a popup window
                                $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' . $this->escape($width) . ',height=' . $this->escape($height) . '';
                                echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '" . $attribs . "'); return false;\">" .
                                    $this->escape($item->title) . '</a>';
                                break;
                            case 3:
                                // Open in a modal window
                                HTMLHelper::_('behavior.modal', 'a.modal');
                                echo '<a class="modal" href="' . $link . '"  rel="{handler: \'iframe\', size: {x:' . $this->escape($width) . ', y:' . $this->escape($height) . '}}">' .
                                    $this->escape($item->title) . ' </a>';
                                break;

                            default:
                                // Open in parent window
                                echo '<a href="' . $link . '" class="' . $menuclass . '" rel="nofollow">' .
                                    $this->escape($item->title) . ' </a>';
                                break;
                            }
                            ?>
                            <?php if ($this->params->get('show_link_hits', 1)) : ?>
                                <div class="list-hits badge bg-info float-end">
                                    <?php echo Text::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($this->params->get('show_tags', 1) && !empty($item->tags->itemTags)) : ?>
                                <div class="mt-2 mb-2">
                                    <?php echo LayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (($this->params->get('show_link_description')) && ($item->description != '')) : ?>
                                <div class="mt-2 mb-2">
                                    <?php $images = json_decode($item->images); ?>
                                    <?php  if (!empty($images->image_first)) : ?>
                                        <?php $imgfloat = (empty($images->float_first)) ? $this->params->get('float_first') : $images->float_first; ?>
                                        <?php $img      = HTMLHelper::cleanImageURL($images->image_first); ?>
                                        <?php $alt      = empty($images->image_first_alt) && empty($images->image_first_alt_empty)
                                            ? ''
                                            : 'alt="' . htmlspecialchars($images->image_first_alt, ENT_COMPAT, 'UTF-8') . '"'; ?>
                                        <figure class="item-image">
                                            <img src="<?php echo htmlspecialchars($img->url, ENT_COMPAT, 'UTF-8'); ?>"
                                                    <?php echo $alt; ?> itemprop="thumbnail" />
                                            <?php if (!empty($images->image_first_caption)) : ?>
                                                <figcaption class="caption"><?php echo htmlspecialchars($images->image_first_caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
                                            <?php endif; ?>
                                        </figure>
                                    <?php endif; ?>

                                    <?php  if (!empty($images->image_second)) : ?>
                                        <?php $imgfloat = (empty($images->float_second)) ? $this->params->get('float_second') : $images->float_second; ?>
                                        <?php $img      = HTMLHelper::cleanImageURL($images->image_second); ?>
                                        <?php $alt      = empty($images->image_second_alt) && empty($images->image_second_alt_empty)
                                            ? ''
                                            : 'alt="' . htmlspecialchars($images->image_second_alt, ENT_COMPAT, 'UTF-8') . '"'; ?>
                                        <figure class="item-image">
                                            <img src="<?php echo htmlspecialchars($img->url, ENT_COMPAT, 'UTF-8'); ?>"
                                                    <?php echo $alt; ?> itemprop="thumbnail" />
                                            <?php if (!empty($images->image_second_caption)) : ?>
                                                <figcaption class="caption"><?php echo htmlspecialchars($images->image_second_caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
                                            <?php endif; ?>
                                        </figure>
                                    <?php endif; ?>

                                    <?php echo $item->description; ?>

                                </div>
                            <?php endif; ?>

                        </div>

                    </li>
				<?php endforeach; ?>
			</ul>

			<?php if ($this->params->get('show_pagination')) : ?>
				<div class="com-contact-category__counter w-100">
					<?php if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="com-contact-category__counter counter float-end pt-3 pe-2">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
					<?php endif; ?>

					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ($canCreate) : ?>
			<?php echo HTMLHelper::_('weblinkicon.create', $this->category, $this->category->params); ?>
		<?php endif; ?>
	</form>
</div>
