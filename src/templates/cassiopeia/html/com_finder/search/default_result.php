<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Site\Helper\LanguageHelper;
use Joomla\Component\Finder\Site\Helper\TaxonomyHelper as Taxonomy;
use Joomla\String\StringHelper;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
$user             = $this->getCurrentUser();
$show_description = $this->params->get('show_description', 1);

if ($show_description) {
    $term_length      = StringHelper::strlen($this->query->input);
    $desc_length      = $this->params->get('description_length', 255);
    $pad_length       = $term_length < $desc_length ? (int) floor(($desc_length - $term_length) / 2) : 0;
    $full_description = $this->result->description;

    if (!empty($this->result->summary) && !empty($this->result->body)) {
        $full_description = $this->result->summary . '. ' . $this->result->body;
    }

    $start       = $pad_length;
    $description = HTMLHelper::_('string.truncate', StringHelper::substr($full_description, $start), $desc_length, true);
}

$showImage  = $this->params->get('show_image', 0);
$imageClass = $this->params->get('image_class', '');
$extraAttr  = [];

if ($showImage && !empty($this->result->imageUrl) && $imageClass !== '') {
    $extraAttr['class'] = $imageClass;
}
?>

<div class="result">
    <div class="result__title">
        <?php
        $url = $this->result->route;

        if ($this->result->extension === 'com_weblinks' && !empty($this->result->id)) {
            $itemid = isset($this->result->itemid) ? (int) $this->result->itemid : 0;
            $url    = Route::_(
                'index.php?option=com_weblinks&task=weblink.go&id=' . (int) $this->result->id . '&Itemid=' . $itemid,
                false
            );
        }

        echo HTMLHelper::link(
            $url,
            '<span class="result__title-text">' . $icon . $this->result->title . '</span>' . $show_url,
            [
                'class'  => 'result__title-link',
                'target' => '_blank',
                'rel'    => 'noopener noreferrer',
            ]
        );
        ?>
    </div>

    <?php if ($show_description && !empty($description)) : ?>
        <div class="result__description">
            <?php echo $description; ?>
        </div>
    <?php endif; ?>

    <?php $taxonomies = $this->result->getTaxonomy(); ?>
    <?php if (count($taxonomies) && $this->params->get('show_taxonomy', 1)) : ?>
        <ul class="result__taxonomy">
            <?php foreach ($taxonomies as $type => $taxonomy) : ?>
                <?php if ($type === 'Language' && (!Multilanguage::isEnabled() || (isset($taxonomy[0]) && $taxonomy[0]->title === '*'))) : ?>
                    <?php continue; ?>
                <?php endif; ?>

                <?php $branch = Taxonomy::getBranch($type); ?>
                <?php if ($branch->state === 1 && in_array($branch->access, $user->getAuthorisedViewLevels(), true)) : ?>
                    <?php $taxonomy_text = []; ?>
                    <?php foreach ($taxonomy as $node) : ?>
                        <?php if ($node->state === 1 && in_array($node->access, $user->getAuthorisedViewLevels(), true)) : ?>
                            <?php $taxonomy_text[] = $node->title; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (count($taxonomy_text)) : ?>
                        <li class="result__taxonomy-item result__taxonomy--<?php echo $type; ?>">
                            <span><?php echo Text::_(LanguageHelper::branchSingular($type)); ?>:</span>
                            <?php echo Text::_(LanguageHelper::branchSingular(implode(',', $taxonomy_text))); ?>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
