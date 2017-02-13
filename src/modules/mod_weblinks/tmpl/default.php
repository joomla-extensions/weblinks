<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php if ($params->get('groupby', 0)) : ?>
	<?php $cats = array(); ?>
	<?php $cols = $params->get('groupby_columns', 3); ?>
	<?php foreach ($list as $l) : ?>
		<?php $cats[] = array('catid' => $l->catid, 'title' => $l->category_title); ?>
	<?php endforeach; ?>
	<?php $cats = array_values(array_map('unserialize', array_unique(array_map('serialize', $cats)))); ?>
	<?php foreach ($cats as $k => $cat) : ?>
		<?php $items = array(); ?>
		<?php foreach ($list as $item) : ?>
			<?php if ($item->catid == $cat['catid']) : ?>
				<?php $items[] = $item; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ($cols > 1) : ?>
			<?php if ($k % $cols == 0) : ?>
				<div class="row row-fluid">
			<?php endif; ?>
			<div class="span<?php echo (12 / $cols); ?>">
		<?php endif; ?>
		<?php if ($params->get('groupby_showtitle', 1)) : ?>
			<h4><?php echo htmlspecialchars($cat['title'], ENT_COMPAT, 'UTF-8'); ?></h4>
		<?php endif; ?>
			<ul class="weblinks<?php echo $moduleclass_sfx; ?>">
				<?php foreach ($items as $item) : ?>
					<li>
						<?php $link = $item->link; ?>
						<?php
						switch ($item->params->get('target', 3))
						{
							case 1:
								// Open in a new window
								echo '<a href="' . $link . '" target="_blank" rel="' . $params->get('follow', 'nofollow') . '">' .
									htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
								break;

							case 2:
								// Open in a popup window
								echo "<a href=\"#\" onclick=\"window.open('" . $link . "', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\">" .
									htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
								break;

							default:
								// Open in parent window
								echo '<a href="' . $link . '" rel="' . $params->get('follow', 'nofollow') . '">' .
									htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
								break;
						}
						?>
						<?php if ($params->get('description', 0)) : ?>
							<?php echo nl2br($item->description); ?>
						<?php endif; ?>

						<?php if ($params->get('hits', 0)) : ?>
							<?php echo '(' . $item->hits . ' ' . JText::_('MOD_WEBLINKS_HITS') . ')'; ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php if ($cols > 1) : ?>
			</div>
			<?php if(($k + 1) % $cols == 0 || $k == count($cats) - 1) : ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
<?php else : ?>
	<ul class="weblinks<?php echo $moduleclass_sfx; ?>">
		<?php foreach ($list as $item) : ?>
			<li>
				<?php $link = $item->link; ?>
				<?php
				switch ($item->params->get('target', 3))
				{
					case 1:
						// Open in a new window
						echo '<a href="' . $link . '" target="_blank" rel="' . $params->get('follow', 'nofollow') . '">' .
							htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
						break;

					case 2:
						// Open in a popup window
						echo "<a href=\"#\" onclick=\"window.open('" . $link . "', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\">" .
							htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
						break;

					default:
						// Open in parent window
						echo '<a href="' . $link . '" rel="' . $params->get('follow', 'nofollow') . '">' .
							htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
						break;
				}
				?>

				<?php if ($params->get('description', 0)) : ?>
					<?php echo nl2br($item->description); ?>
				<?php endif; ?>

				<?php if ($params->get('hits', 0)) : ?>
					<?php echo '(' . $item->hits . ' ' . JText::_('MOD_WEBLINKS_HITS') . ')'; ?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
