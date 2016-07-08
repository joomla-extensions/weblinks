<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php
if($params->get('groupby', 0)) :

    $cats = array();
    $cols = $params->get('groupby_columns', 3);

    foreach ($list as $l)
        $cats[] = array('catid' => $l->catid, 'title' => $l->category_title);

    $cats = array_values(array_map('unserialize', array_unique(array_map('serialize', $cats))));

    foreach ($cats as $k => $cat) :

        $items = array();

        foreach ($list as $item)
            if ($item->catid == $cat['catid'])
                $items[] = $item;
?>
        <?php if($cols > 1) : ?>
            <?php if($k % $cols == 0) : ?>
                <div class="row row-fluid">
            <?php endif; ?>
                <div class="span<?php echo (12 / $cols); ?>">
        <?php endif; ?>
                    <?php if($params->get('groupby_showtitle', 1)) : ?>
                        <h4><?php echo htmlspecialchars($cat['title']); ?></h4>
                    <?php endif; ?>
                        <ul class="weblinks<?php echo $moduleclass_sfx; ?>">
                            <?php foreach ($items as $item) : ?>
                                <li>
                                    <?php
                                    $link = $item->link;

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

                                    if ($params->get('description', 0))
                                    {
                                        echo nl2br($item->description);
                                    }

                                    if ($params->get('hits', 0))
                                    {
                                        echo '(' . $item->hits . ' ' . JText::_('MOD_WEBLINKS_HITS') . ')';
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
        <?php if($cols > 1) : ?>
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
                <?php
                $link = $item->link;

                switch ($params->get('target', 3))
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

                if ($params->get('description', 0))
                {
                    echo nl2br($item->description);
                }

                if ($params->get('hits', 0))
                {
                    echo '(' . $item->hits . ' ' . JText::_('MOD_WEBLINKS_HITS') . ')';
                }
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
