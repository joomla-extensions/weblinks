<?php

/**
 * @package    Joomla.Site
 * @subpackage com_weblinksmanager
 *
 * @copyright Copyright (C)
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

?>

<h1>Weblinks Manager Dashboard</h1>

<?php if (empty($this->items)) : ?>
    <div class="alert alert-warning">
        No weblinks found in databasee
    </div>
<?php else : ?>
    <div class="alert alert-success">
        Found <?php echo \count($this->items); ?> weblinks
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>URL</th>
                <th>State</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $item) : ?>
                <tr>
                    <td><?php echo $item->id; ?></td>
                    <td><?php echo $item->title; ?></td>
                    <td>
                        <a href="<?php echo $item->url; ?>" target="_blank">
                            <?php echo $item->url; ?>
                        </a>
                    </td>
                    <td><?php echo $item->state; ?></td>
                    <td>
                        <a href="<?php echo Route::_(
                            'index.php?option=com_weblinksmanager&view=weblink&layout=edit&id=' . $item->id
                        ); ?>" class="btn btn-sm btn-primary">
                            Edit
                        </a>

                        <form action="<?php echo Route::_(
                            'index.php?option=com_weblinksmanager&task=weblink.delete'
                        ); ?>" method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $item->id; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this weblink?');">
                                Delete
                            </button>
                            <?php echo HTMLHelper::_('form.token'); ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Create New Weblink</h2>
<form action="<?php echo Route::_(
    'index.php?option=com_weblinksmanager&task=weblink.save'
); ?>" method="post">
    <div class="mb-3">
        <label for="title">Title</label>
        <input type="text" class="form-control" id="title" name="jform[title]" required>
    </div>
    <div class="mb-3">
        <label for="url">URL</label>
        <input type="url" class="form-control" id="url" name="jform[url]" required>
    </div>
    <input type="hidden" name="jform[state]" value="1">
    <button type="submit" class="btn btn-primary">Save</button>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
