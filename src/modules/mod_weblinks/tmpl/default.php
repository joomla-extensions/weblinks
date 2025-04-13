<?php
/**
 * @package    Joomla.Site
 * @subpackage mod_weblinks
 */

use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;


HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('bootstrap.modal');
$db = JoomlaFactory::getDbo();


if (!\function_exists('getCategoryTree')) {
    function getCategoryTree($parentId, $db)
    {
        $tree = [];


        $query = $db->getQuery(true)
            ->select('id, title, parent_id')
            ->from('#__categories')
            ->where('id = ' . (int) $parentId)
            ->where('extension = ' . $db->quote('com_weblinks'))
            ->where('published = 1');
        $db->setQuery($query);
        $parent = $db->loadObject();

        if (!$parent) {
            return [];
        }


        $node = [
            'catid'     => $parent->id,
            'title'     => $parent->title,
            'parent_id' => $parent->parent_id,
            'children'  => [],
        ];


        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__categories')
            ->where('parent_id = ' . (int) $parentId)
            ->where('extension = ' . $db->quote('com_weblinks'))
            ->where('published = 1');
        $db->setQuery($query);
        $children = $db->loadColumn();

        foreach ($children as $childId) {
            $node['children'][] = getCategoryTree($childId, $db);
        }

        return $node;
    }
}

if (!\function_exists('renderCategoryNode')) {
    function renderCategoryNode($node, $list, $params, $moduleclass_sfx, $parentWeblinks = [])
    {
        if (!$node || !isset($node['catid'])) {
            return;
        }

        $catid    = $node['catid'];
        $items    = array_filter($list, fn ($item) => (int) $item->catid === (int) $catid);
        $hasItems = !empty($items);

        echo '<div class="category-block">';

        if ($params->get('groupby_showtitle', 1)) {
            echo '<h4 class="weblink-category-title">' . htmlspecialchars($node['title'], ENT_COMPAT, 'UTF-8') . '</h4>';
        }


        if ($node['catid'] == (int) $params->get('catid') && !empty($parentWeblinks)) {
            echo '<ul class="weblinks' . $moduleclass_sfx . '">';
            foreach ($parentWeblinks as $item) {
                $link   = $item->link;
                $width  = (int) $item->params->get('width', 600);
                $height = (int) $item->params->get('height', 500);

                echo '<li><div class="d-flex flex-wrap"><div class="col flex-sm-grow-1">';

                switch ($item->params->get('target')) {
                case 1:
                    echo '<a href="' . $link . '" target="_blank" rel="' . $params->get('follow', 'nofollow') . '">' .
                        htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                    break;
                case 2:
                    $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' . $width . ',height=' . $height;
                    echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '$attribs'); return false;\">" .
                        htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                    break;
                case 3:
                    $modalId     = 'weblink-item-modal-' . $item->id;
                    $modalParams = [
                        'title'      => htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'),
                        'url'        => $link,
                        'height'     => '100%',
                        'width'      => '100%',
                        'bodyHeight' => 70,
                        'modalWidth' => 80,
                    ];
                    echo HTMLHelper::_('bootstrap.renderModal', $modalId, $modalParams);
                    echo '<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#' . $modalId . '">' . $item->title . '</button>';
                    break;
                default:
                    echo '<a href="' . $link . '" rel="' . $params->get('follow', 'nofollow') . '">' .
                        htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                }

                echo '</div>';

                if ($params->get('description', 0)) {
                    echo '<div class="col flex-sm-grow-1">' . $item->description . '</div>';
                }

                if ($params->get('hits', 0)) {
                    echo '<div class="col flex-sm-grow-1"><span class="badge bg-info float-md-end">' .
                        $item->hits . ' ' . Text::_('MOD_WEBLINKS_HITS') . '</span></div>';
                }

                echo '</div></li>';
            }
            echo '</ul>';
        }


        echo '<ul class="weblinks' . $moduleclass_sfx . '">';
        if (!$hasItems && ($node['catid'] != (int) $params->get('catid') || empty($parentWeblinks))) {
            echo '<li><em>' . Text::_('MOD_WEBLINKS_NO_ITEMS') . '</em></li>';
        }

        foreach ($items as $item) {
            $link   = $item->link;
            $width  = (int) $item->params->get('width', 600);
            $height = (int) $item->params->get('height', 500);

            echo '<li><div class="d-flex flex-wrap"><div class="col flex-sm-grow-1">';

            switch ($item->params->get('target')) {
            case 1:
                echo '<a href="' . $link . '" target="_blank" rel="' . $params->get('follow', 'nofollow') . '">' .
                    htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                break;
            case 2:
                $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' . $width . ',height=' . $height;
                echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '$attribs'); return false;\">" .
                    htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                break;
            case 3:
                $modalId     = 'weblink-item-modal-' . $item->id;
                $modalParams = [
                    'title'      => htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'),
                    'url'        => $link,
                    'height'     => '100%',
                    'width'      => '100%',
                    'bodyHeight' => 70,
                    'modalWidth' => 80,
                ];
                echo HTMLHelper::_('bootstrap.renderModal', $modalId, $modalParams);
                echo '<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#' . $modalId . '">' . $item->title . '</button>';
                break;
            default:
                echo '<a href="' . $link . '" rel="' . $params->get('follow', 'nofollow') . '">' .
                    htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
            }

            echo '</div>';

            if ($params->get('description', 0)) {
                echo '<div class="col flex-sm-grow-1">' . $item->description . '</div>';
            }

            if ($params->get('hits', 0)) {
                echo '<div class="col flex-sm-grow-1"><span class="badge bg-info float-md-end">' .
                    $item->hits . ' ' . Text::_('MOD_WEBLINKS_HITS') . '</span></div>';
            }

            echo '</div></li>';
        }

        echo '</ul>';


        foreach ($node['children'] as $childNode) {
            renderCategoryNode($childNode, $list, $params, $moduleclass_sfx, $parentWeblinks);
        }

        echo '</div>';
    }
}


if ($params->get('groupby', 0)) {
    $rootCatId    = (int) $params->get('catid');
    $categoryTree = getCategoryTree($rootCatId, $db);
    renderCategoryNode($categoryTree, $list['categoryWeblinks'], $params, $moduleclass_sfx, $list['parentWeblinks']);
} else {

    ?>
    <ul class="weblinks<?php echo $moduleclass_sfx; ?>">
        <?php foreach (array_merge($list['parentWeblinks'], $list['categoryWeblinks']) as $item) :
            $link   = $item->link;
            $width  = (int) $item->params->get('width', 600);
            $height = (int) $item->params->get('height', 500);
            ?>
            <li>
                <div class="d-flex flex-wrap">
                    <div class="col flex-sm-grow-1">
                        <?php switch ($item->params->get('target')) :
                        case 1:
                            echo '<a href="' . $link . '" target="_blank" rel="' . $params->get('follow', 'nofollow') . '">' .
                                htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                            break;
                        case 2:
                            $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' . $width . ',height=' . $height;
                            echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '$attribs'); return false;\">" .
                                htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                            break;
                        case 3:
                            $modalId     = 'weblink-item-modal-' . $item->id;
                            $modalParams = [
                                'title'      => htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'),
                                'url'        => $link,
                                'height'     => '100%',
                                'width'      => '100%',
                                'bodyHeight' => 70,
                                'modalWidth' => 80,
                            ];
                            echo HTMLHelper::_('bootstrap.renderModal', $modalId, $modalParams);
                            echo '<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#' . $modalId . '">' . $item->title . '</button>';
                            break;
                        default:
                            echo '<a href="' . $link . '" rel="' . $params->get('follow', 'nofollow') . '">' .
                                htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</a>';
                        endswitch; ?>
                    </div>

                    <?php if ($params->get('description', 0)) : ?>
                        <div class="col flex-sm-grow-1"><?php echo $item->description; ?></div>
                    <?php endif; ?>

                    <?php if ($params->get('hits', 0)) : ?>
                        <div class="col flex-sm-grow-1">
                            <span class="badge bg-info float-md-end"><?php echo $item->hits . ' ' . Text::_('MOD_WEBLINKS_HITS'); ?></span>
                        </div>
                        
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
        
    </ul>
    <?php
}
?>
