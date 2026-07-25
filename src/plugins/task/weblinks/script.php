<?php

/**
 * @package    Joomla.Plugin
 * @subpackage Weblinks.weblinks
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Installer service provider of plg_contact_customreply plugin.
 *
 * @since  1.0.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since  1.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            InstallerScriptInterface::class,
            new class (
                $container->get(AdministratorApplication::class)
            ) implements InstallerScriptInterface {
                /**
                 * Minimum Joomla version to check
                 *
                 * @var    string
                 * @since  1.0.0
                 */
                protected $minimumJoomla = '5.0.0';

                /**
                 * Minimum PHP version to check
                 *
                 * @var    string
                 * @since  1.0.0
                */
                protected $minimumPhp = '8.1.0';

                /**
                 * True when we have to update the searchable fields
                 *
                 * @var boolean
                 * @since  1.0.0
                 */
                private $updateSearchable = false;

                /**
                 * Constructor
                 *
                 * @param AdministratorApplication $app The application object
                 *
                 * @since  1.0.0
                 */
                public function __construct(
                    /**
                     * The application object
                     *
                     * @since  1.0.0
                     */
                    private readonly AdministratorApplication $app
                ) {
                }

                /**
                 * Function called after the extension is installed.
                 *
                 * @param   InstallerAdapter  $adapter  The adapter calling this method
                 *
                 * @return  boolean  True on success
                 *
                 * @since  1.0.0
                 */
                public function install(InstallerAdapter $adapter): bool
                {

                    $this->createTable();
                    return true;
                }

                /**
                 * Function called after the extension is updated.
                 *
                 * @param   InstallerAdapter  $adapter  The adapter calling this method
                 *
                 * @return  boolean  True on success
                 *
                 * @since  1.0.0
                 */
                public function update(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                /**
                 * Function called after the extension is uninstalled.
                 *
                 * @param   InstallerAdapter  $adapter  The adapter calling this method
                 *
                 * @return  boolean  True on success
                 *
                 * @since  1.0.0
                 */
                public function uninstall(InstallerAdapter $adapter): bool
                {

                    $this->dropTable();
                    return true;
                }

                /**
                 * Function called before extension installation/update/removal procedure commences.
                 *
                 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
                 * @param   InstallerAdapter  $adapter  The adapter calling this method
                 *
                 * @return  boolean  True on success
                 *
                 * @since  1.0.0
                 */
                public function preflight(string $type, InstallerAdapter $adapter): bool
                {
                    if ($type !== 'uninstall') {
                        // Check for the minimum PHP version before continuing
                        if (!empty($this->minimumPhp) && version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
                            Log::add(
                                Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPhp),
                                Log::WARNING,
                                'jerror'
                            );

                            return false;
                        }

                        // Check for the minimum Joomla version before continuing
                        if (!empty($this->minimumJoomla) && version_compare(JVERSION, $this->minimumJoomla, '<')) {
                            Log::add(
                                Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomla),
                                Log::WARNING,
                                'jerror'
                            );

                            return false;
                        }
                    }

                    return true;
                }

                /**
                 * Function called after extension installation/update/removal procedure commences.
                 *
                 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
                 * @param   InstallerAdapter  $adapter  The adapter calling this method
                 *
                 * @return  boolean  True on success
                 *
                 * @since  1.0.0
                 */
                public function postflight(string $type, InstallerAdapter $adapter): bool
                {
                    if (!\in_array($type, ['install', 'discover_install'])) {
                        return true;
                    }
                    // Auto-publish plugin
                    $adapter->extension->enabled = 1;
                    $adapter->extension->store();

                    echo Text::_('PLG_CONTACT_CUSTOMREPLY_INSTALLERSCRIPT_POSTFLIGHT_INSTALL');

                    return true;
                }
                /**
                 * Insert the template into the #__mail_templates table.
                 *
                 * @return void
                 */
                private function createTable(): void
                {
                    try {
                        $db = Factory::getContainer()->get(DatabaseDriver::class);

                        $templateId  = 'plg_task_weblinks.broken_links';
                        $extension   = 'plg_task_weblinks';
                        $language    = '';
                        $subject     = 'PLG_TASK_WEBLINKS_EMAIL_SUBJECT';
                        $body        = 'PLG_TASK_WEBLINKS_EMAIL_BODY';
                        $htmlbody    = 'PLG_TASK_WEBLINKS_EMAIL_HTMLBODY';
                        $attachments = '';
                        $params      = '{"tags":["broken_count","checked_count","details","sitename"]}';

                        $query = $db->getQuery(true);
                        $query->clear()
                            ->insert($db->quoteName('#__mail_templates'))
                            ->columns($db->quoteName(['template_id', 'extension', 'language', 'subject', 'body', 'htmlbody', 'attachments', 'params']))
                            ->values(':templateid, :extension, :language, :subject, :body, :htmlbody, :attachments, :params')
                            ->bind(':templateid', $templateId)
                            ->bind(':extension', $extension)
                            ->bind(':language', $language)
                            ->bind(':subject', $subject)
                            ->bind(':body', $body)
                            ->bind(':htmlbody', $htmlbody)
                            ->bind(':attachments', $attachments)
                            ->bind(':params', $params);

                        $db->setQuery($query);
                        $db->execute();
                    } catch (\Exception $e) {
                        Factory::getApplication()->enqueueMessage('Error creating template_id ' . $templateId . ': ' . $e->getMessage(), 'error');
                    }
                }

                /**
                 * Delete the template_id in the #__mail_templates table.
                 *
                 * @return void
                 */
                private function dropTable(): void
                {
                    try {
                        $db          = Factory::getContainer()->get(DatabaseDriver::class);
                        $templateId  = 'plg_task_weblinks.broken_links';
                        // Delete the template_id from the #__mail_templates table
                        $query = $db->getQuery(true);
                        $query->clear()
                            ->delete($db->quoteName('#__mail_templates'))
                            ->where($db->quoteName('template_id') . ' = :templateid')
                            ->bind(':templateid', $templateId);
                        $db->setQuery($query);
                        $db->execute();
                    } catch (\Exception $e) {
                        Factory::getApplication()->enqueueMessage('Error dropping template_id ' . $templateId . ': ' . $e->getMessage(), 'error');
                    }
                }
            }
        );
    }
};
