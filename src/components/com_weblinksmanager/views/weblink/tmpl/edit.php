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

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
?>

<h1>Edit Weblink</h1>

<form action="<?php echo Route::_(
    'index.php?option=com_weblinksmanager&task=weblink.update'
); ?>" method="post">
    <div class="mb-3">
        <label for="title">Title</label>
        <input
            type="text"
            class="form-control"
            id="title"
            name="jform[title]"
            value="<?php echo htmlspecialchars($this->item->title); ?>"
            required
        >
    </div>

    <div class="mb-3">
        <label for="url">URL</label>
        <input
            type="url"
            class="form-control"
            id="url"
            name="jform[url]"
            value="<?php echo htmlspecialchars($this->item->url); ?>"
            required
        >
    </div>

    <div class="mb-3">
        <label for="state">State</label>
        <select class="form-control" id="state" name="jform[state]">
            <option value="1" <?php echo $this->item->state == 1 ? 'selected' : ''; ?>>
                Published
            </option>
            <option value="0" <?php echo $this->item->state == 0 ? 'selected' : ''; ?>>
                Unpublished
            </option>
            <option value="-2" <?php echo $this->item->state == -2 ? 'selected' : ''; ?>>
                Trashed
            </option>
        </select>
    </div>

    <input
        type="hidden"
        name="jform[id]"
        value="<?php echo $this->item->id; ?>"
    >

    <button type="submit" class="btn btn-primary">Update</button>
    <a
        href="<?php echo Route::_('index.php?option=com_weblinksmanager&view=dashboard'); ?>"
        class="btn btn-secondary"
    >
        Cancel
    </a>
    
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
