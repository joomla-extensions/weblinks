<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Category;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Dispatcher;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

return new class () implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->set(
            InstallerScriptInterface::class,
            new class (
                $container->get(AdministratorApplication::class),
                $container->get(DatabaseInterface::class)
            ) implements InstallerScriptInterface {
                private AdministratorApplication $app;
                private DatabaseInterface $db;
                private string $minimumJoomla = '5.0.0';
                private string $minimumPhp    = '8.1.0';


                public function __construct(AdministratorApplication $app, DatabaseInterface $db)
                {
                    $this->app = $app;
                    $this->db  = $db;
                }

                public function install(InstallerAdapter $parent): bool
                {
                    $this->createCategory();
                    $this->app->enqueueMessage(Text::_('COM_WEBLINKS_SUCCESS_INSTALL'));

                    return true;
                }

                public function update(InstallerAdapter $parent): bool
                {
                    $this->app->enqueueMessage(Text::_('COM_WEBLINKS_SUCCESS_UPDATE'));

                    return true;
                }

                public function uninstall(InstallerAdapter $parent): bool
                {
                    $this->app->enqueueMessage(Text::_('COM_WEBLINKS_SUCCESS_UNINSTALL'));

                    return true;
                }

                public function preflight(string $type, InstallerAdapter $parent): bool
                {

                    if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
                        $this->app->enqueueMessage(\sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp), 'error');
                        return false;
                    }

                    if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
                        $this->app->enqueueMessage(\sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla), 'error');
                        return false;
                    }

                    return true;
                }

                public function postflight(string $type, InstallerAdapter $parent): bool
                {
                    $this->deleteUnexistingFiles();

                    return true;
                }

                private function createCategory(): bool
                {
                    // Initialize a new category
                    $category = new Category($this->db);
                    $category->setDispatcher(new Dispatcher());

                    // Check if the Uncategorised category exists before adding it
                    if (!$category->load(['extension' => 'com_weblinks', 'title' => 'Uncategorised'])) {
                        $category->extension        = 'com_weblinks';
                        $category->title            = 'Uncategorised';
                        $category->description      = '';
                        $category->published        = 1;
                        $category->access           = 1;
                        $category->params           = '{"category_layout":"","image":""}';
                        $category->metadata         = '{"author":"","robots":""}';
                        $category->metadesc         = '';
                        $category->metakey          = '';
                        $category->language         = '*';
                        $category->checked_out_time = null;
                        $category->version          = 1;
                        $category->hits             = 0;
                        $category->modified_user_id = 0;
                        $category->checked_out      = null;

                        // Set the location in the tree
                        $category->setLocation(1, 'last-child');

                        try {
                            // Check to make sure our data is valid
                            $category->check();
                        } catch (\Exception $e) {
                            $this->app->enqueueMessage(Text::sprintf('COM_WEBLINKS_ERROR_INSTALL_CATEGORY', $e->getMessage()), 'error');

                            return false;
                        }

                        try {
                            // Now store the category
                            $category->store(true);
                        } catch (\Exception $e) {
                            $this->app->enqueueMessage(Text::sprintf('COM_WEBLINKS_ERROR_INSTALL_CATEGORY', $e->getMessage()), 'error');

                            return false;
                        }

                        // Build the path for our category
                        $category->rebuildPath($category->id);
                    }

                    return true;
                }

                private function deleteUnexistingFiles()
                {
                    $files = [
                      '/administrator/components/com_weblinks/helpers/associations.php',
                      '/administrator/components/com_weblinks/sql/install.sqlsrv.sql',
                      '/administrator/components/com_weblinks/sql/uninstall.sqlsrv.sql',
                      '/administrator/language/en-GB/en-GB.com_weblinks.ini',
                      '/administrator/language/en-GB/en-GB.com_weblinks.sys.ini',
                      '/components/com_weblinks/helpers/association.php',
                      '/components/com_weblinks/helpers/category.php',
                      '/language/en-GB/en-GB.com_weblinks.ini',
                      '/language/en-GB/en-GB.mod_weblinks.ini',
                      '/language/en-GB/en-GB.mod_weblinks.sys.ini',
                      '/language/en-GB/en-GB.pkg_weblinks.sys.ini',
                      '/modules/mod_weblinks/helper.php',
                      '/modules/mod_weblinks/mod_weblinks.php',
                    ];

                    $folders = [
                        '/administrator/components/com_weblinks/helpers/html',
                        '/administrator/components/com_weblinks/sql/updates/sqlsrv',
                    ];

                    if (empty($files)) {
                        return;
                    }

                    foreach ($files as $file) {
                        try {
                            File::delete(JPATH_ROOT . $file);
                        } catch (FilesystemException $e) {
                            echo Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br>';
                        }
                    }

                    foreach ($folders as $folder) {
                        if (is_dir(JPATH_ROOT . $folder)) {
                            try {
                                Folder::delete(JPATH_ROOT . $folder);
                            } catch (FilesystemException | \UnexpectedValueException $exception) {
                                echo Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br>';
                            }
                        }
                    }
                }
            }
        );
    }
};
