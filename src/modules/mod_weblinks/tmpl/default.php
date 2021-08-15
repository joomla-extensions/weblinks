<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if ($params->get('groupby', 0)) :
	$cats = [];
	$cols = $params->get('groupby_columns', 3);

	foreach ($list as $l) :
		$cats[] = array('catid' => $l->catid, 'title' => $l->category_title);
	endforeach;

	$cats = array_values(array_map('unserialize', array_unique(array_map('serialize', $cats))));

	foreach ($cats as $k => $cat) :
		$items = [];

		foreach ($list as $item) :
			if ($item->catid == $cat['catid']) :
				$items[] = $item;
			endif;
		endforeach;

		if ($cols > 1) :
			if ($k % $cols == 0) :
				echo '<div class="row row-fluid">';
			endif;

			echo '<div class="col-' . 12 / $cols . '">';
		endif;

		if ($params->get('groupby_showtitle', 1)) :
			echo '<strong>' . htmlspecialchars($cat['title'], ENT_COMPAT, 'UTF-8') . '</strong>';
		endif;

		echo '<ul class="mod-list weblinks ' .  $moduleclass_sfx . '">';

		foreach ($items as $item) :
			echo '<li><div class="d-flex flex-wrap">';
			echo '<div class="col flex-sm-grow-1">';

			$link = $item->link;
			switch ($item->params->get('target', 3)) :
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
			endswitch;
			echo '</div>';

			echo $params->get('description', 0) ? '<div class="colvflex-sm-grow-1">' . nl2br($item->description) . '</div>' : '';

			if ($params->get('hits', 0)) :
				echo '<div class="col flex-sm-grow-1">';
					echo '<span class="badge bg-info float-md-end">' . $item->hits . ' ' . Text::_('MOD_WEBLINKS_HITS') . '</span>';
				echo '</div>';
			endif;

			echo '</li>';

		endforeach;
		echo '</ul>';

		if ($cols > 1) :
			echo '</div>';

			if (($k + 1) % $cols == 0 || $k == count($cats) - 1) :
				echo '</div>';
			endif;

		endif;
	endforeach;
else :
	echo '<ul class="mod-list weblinks ' .  $moduleclass_sfx . '">';

	foreach ($list as $item) :
		echo '<li><div class="d-flex flex-wrap">';
		echo '<div class="col flex-sm-grow-1">';

		$link = $item->link;
		switch ($item->params->get('target', 3)) :
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
		endswitch;
		echo '</div>';

		echo $params->get('description', 0) ? '<div class="col flex-sm-grow-1">' . nl2br($item->description) . '</div>' : '';

		if ($params->get('hits', 0)) :
			echo '<div class="col  flex-sm-grow-1">';
				echo '<span class="badge bg-info float-md-end">' . $item->hits . ' ' . Text::_('MOD_WEBLINKS_HITS') . '</span>';
			echo '</div>';
		endif;

		echo '</li>';

	endforeach;
	echo '</ul>';
endif; ?>
