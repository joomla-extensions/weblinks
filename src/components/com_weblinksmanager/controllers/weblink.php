<?php

/**
 * Weblink controller for the Weblinks Manager component.
 *
 * @category   Joomla.Component.Site
 * @package    Joomla.Site
 * @subpackage Com_Weblinksmanager
 * @license    GNU General Public License version 2 or later
 * @link       https://joomla.org
 * @since      1.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Controller for managing individual weblinks in the frontend.
 *
 * @category   Joomla.Component.Site
 * @package    Joomla.Site
 * @subpackage Com_Weblinksmanager
 * @license    GNU General Public License version 2 or later
 * @link       https://joomla.org
 * @since      1.0
 */
class WeblinksmanagerControllerWeblink extends FormController
{
    /**
     * Save a new weblink.
     *
     * @param string $key    The primary key name.
     * @param string $urlVar The URL variable name.
     *
     * @return boolean  True on success, false on failure.
     *
     * @since 1.0
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $app   = Factory::getApplication();
        $input = $app->input;
        $data  = $input->get('jform', [], 'array');

        if (empty($data['title']) || empty($data['url'])) {
            $app->enqueueMessage('Title and URL are required', 'error');
            $app->redirect(
                Route::_(
                    'index.php?option=com_weblinksmanager&view=dashboard',
                    false
                )
            );

            return false;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__weblinks'))
            ->columns(
                $db->quoteName(
                    [
                    'catid',
                    'title',
                    'alias',
                    'url',
                    'description',
                    'hits',
                    'state',
                    'ordering',
                    'access',
                    'params',
                    'language',
                    'created',
                    'created_by',
                    'created_by_alias',
                    'modified',
                    'modified_by',
                    'metakey',
                    'metadesc',
                    'metadata',
                    'featured',
                    'xreference',
                    'version',
                    'images',
                    ]
                )
            )
            ->values(
                implode(
                    ', ',
                    [
                        (int) 0,
                        $db->quote($data['title']),
                        $db->quote($data['alias'] ?? ''),
                        $db->quote($data['url']),
                        $db->quote($data['description'] ?? ''),
                        (int) 0,
                        (int) ($data['state'] ?? 0),
                        (int) 0,
                        (int) 1,
                        $db->quote('{}'),
                        $db->quote('*'),
                        $db->quote(date('Y-m-d H:i:s')),
                        (int) ($data['created_by'] ?? 0),
                        $db->quote($data['created_by_alias'] ?? ''),
                        $db->quote(date('Y-m-d H:i:s')),
                        (int) ($data['modified_by'] ?? 0),
                        $db->quote($data['metakey'] ?? ''),
                        $db->quote($data['metadesc'] ?? ''),
                        $db->quote($data['metadata'] ?? ''),
                        (int) ($data['featured'] ?? 0),
                        $db->quote($data['xreference'] ?? ''),
                        (int) 1,
                        $db->quote($data['images'] ?? ''),
                    ]
                )
            );

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            $app->enqueueMessage(
                'Error saving weblink: ' . $e->getMessage(),
                'error'
            );
        }

        $app->redirect(
            Route::_(
                'index.php?option=com_weblinksmanager&view=dashboard',
                false
            )
        );
    }

    /**
     * Redirect to the edit form for a weblink.
     *
     * @param string $key    The primary key name.
     * @param string $urlVar The URL variable name.
     *
     * @return boolean  True on success, false on failure.
     *
     * @since 1.0
     */
    public function edit($key = null, $urlVar = null)
    {
        $app = Factory::getApplication();
        $id  = $app->input->getInt('id');

        if (!$id) {
            $app->enqueueMessage('Invalid weblink ID', 'error');
            $app->redirect(
                Route::_(
                    'index.php?option=com_weblinksmanager&view=dashboard',
                    false
                )
            );

            return false;
        }

        $app->redirect(
            Route::_(
                'index.php?option=com_weblinksmanager&view=weblink'
                    . '&layout=edit&id=' . $id,
                false
            )
        );
    }

    /**
     * Update an existing weblink.
     *
     * @return void
     *
     * @since 1.0
     */
    public function update()
    {
        $this->checkToken();

        $app   = Factory::getApplication();
        $input = $app->input;
        $data  = $input->get('jform', [], 'array');

        if (empty($data['id']) || empty($data['title']) || empty($data['url'])) {
            $app->enqueueMessage('Invalid data for update', 'error');
            $app->redirect(
                Route::_(
                    'index.php?option=com_weblinksmanager&view=dashboard',
                    false
                )
            );

            return false;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__weblinks'))
            ->set($db->quoteName('title') . ' = ' . $db->quote($data['title']))
            ->set($db->quoteName('url') . ' = ' . $db->quote($data['url']))
            ->set($db->quoteName('state') . ' = ' . (int) $data['state'])
            ->where($db->quoteName('id') . ' = ' . (int) $data['id']);
        $db->setQuery($query);

        try {
            $db->execute();
            $app->enqueueMessage('Weblink updatedd successfully', 'success');
        } catch (Exception $e) {
            $app->enqueueMessage(
                'Error updatiing weblink: ' . $e->getMessage(),
                'error'
            );
        }

        $app->redirect(
            Route::_(
                'index.php?option=com_weblinksmanager&view=dashboard',
                false
            )
        );
    }

    /**
     * Delete a weblink.
     *
     * @return void
     *
     * @since 1.0
     */
    public function delete()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $id  = $app->input->getInt('id');

        if (!$id) {
            $app->enqueueMessage('Invalid weblink ID', 'error');
            $app->redirect(
                Route::_(
                    'index.php?option=com_weblinksmanager&view=dashboard',
                    false
                )
            );

            return false;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__weblinks'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);
        $db->setQuery($query);
        $db->execute();

        $app->enqueueMessage('Weblink deleted successfully', 'success');
        $app->redirect(
            Route::_(
                'index.php?option=com_weblinksmanager&view=dashboard',
                false
            )
        );
    }
}
