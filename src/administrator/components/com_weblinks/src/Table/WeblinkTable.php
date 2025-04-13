<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Administrator\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
/**
 * Weblink Table class
 *
 * @since  1.5
 */
class WeblinkTable extends Table implements VersionableTableInterface, TaggableTableInterface
{
    use TaggableTableTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */

    protected $_supportNullValue = true;
    /**
     * Ensure the params and metadata in json encoded in the bind method
     *
     * @var    array
     * @since  3.4
     */

    protected $_jsonEncode = ['params', 'metadata', 'images'];

    /**
     * Constructor
     *
     * @param   \JDatabaseDriver  &$db  A database connector object
     *
     * @since   1.5
     */
    public function __construct($db)
    {
        $this->typeAlias = 'com_weblinks.weblink';
        parent::__construct('#__weblinks', 'id', $db);
        // Set the published column alias
        $this->setColumnAlias('published', 'state');
    }

    /**
     * Overload the store method for the Weblinks table.
     *
     * @param   boolean  $updateNulls  Toggle whether null values should be updated.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function store($updateNulls = true)
    {
        $date           = Factory::getDate()->toSql();
        $user           = Factory::getApplication()->getIdentity();
        $this->modified = $date;

        if ($this->id) {
            // Existing item
            $this->modified_by = $user->id;
            $this->modified    = $date;
        } else {
            // New weblink. A weblink created and created_by field can be set by the user,
            // so we don't touch either of these if they are set.
            if (!(int) $this->created) {
                $this->created = $date;
            }

            if (empty($this->created_by)) {
                $this->created_by = $user->id;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $user->id;
            }

            if (empty($this->hits)) {
                $this->hits = 0;
            }
        }

        // Set publish_up to null if not set
        if (!$this->publish_up) {
            $this->publish_up = null;
        }

        // Set publish_down to null if not set
        if (!$this->publish_down) {
            $this->publish_down = null;
        }

        // Verify that the alias is unique
        $table = new WeblinkTable($this->getDbo());

        if (
            $table->load(['language' => $this->language, 'alias' => $this->alias, 'catid' => (int) $this->catid])
            && ($table->id != $this->id || $this->id == 0)
        ) {
            throw new \RuntimeException(Text::_('COM_WEBLINKS_ERROR_UNIQUE_ALIAS'));

        }

        // Convert IDN urls to punycode
        $this->url = PunycodeHelper::urlToPunycode($this->url);
        return parent::store($updateNulls);
    }

    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return  boolean  True on success.
     *
     * @since   1.5
     */
    public function check()
    {
        if (InputFilter::checkAttribute(['href', $this->url])) {

            throw new \RuntimeException(Text::_('COM_WEBLINKS_ERR_TABLES_PROVIDE_URL'));

        }

        // Check for valid name
        if (trim($this->title) === '') {

            throw new \RuntimeException(Text::_('COM_WEBLINKS_ERR_TABLES_TITLE'));

        }

        // Check for existing name
        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__weblinks'))
            ->where($db->quoteName('title') . ' = :title')
            ->where($db->quoteName('language') . ' = :language')
            ->where($db->quoteName('catid') . ' = :catid')
            ->bind(':title', $this->title)
            ->bind(':language', $this->language)
            ->bind(':catid', $this->catid, ParameterType::INTEGER);
        $db->setQuery($query);
        $xid = (int) $db->loadResult();
        if ($xid && $xid != (int) $this->id) {

            throw new \RuntimeException(Text::_('COM_WEBLINKS_ERR_TABLES_NAME'));

        }

        if (empty($this->alias)) {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);
        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format("Y-m-d-H-i-s");
        }

        // Check the publish down date is not earlier than publish up.
        if ((int) $this->publish_down > 0 && $this->publish_down < $this->publish_up) {

            throw new \RuntimeException(Text::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));


        }

        /*
      * Clean up keywords -- eliminate extra spaces between phrases
      * and cr (\r) and lf (\n) characters from string
         */
        if (!empty($this->metakey)) {
            // Array of characters to remove
            $bad_characters = ["\n", "\r", "\"", "<", ">"];
            $after_clean    = StringHelper::str_ireplace($bad_characters, "", $this->metakey);
            $keys           = explode(',', $after_clean);
            $clean_keys     = [];
            foreach ($keys as $key) {
                // Ignore blank keywords
                if (trim($key)) {
                    $clean_keys[] = trim($key);
                }
            }

            // Put array back together delimited by ", "
            $this->metakey = implode(", ", $clean_keys);
        }

        /**
         * Ensure any new items have compulsory fields set. This is needed for things like
         * frontend editing where we don't show all the fields or using some kind of API
         */
        if (!$this->id) {
            if (!isset($this->xreference)) {
                $this->xreference = '';
            }

            if (!isset($this->metakey)) {
                $this->metakey = '';
            }

            if (!isset($this->metadesc)) {
                $this->metadesc = '';
            }

            if (!isset($this->images)) {
                $this->images = '{}';
            }

            if (!isset($this->metadata)) {
                $this->metadata = '{}';
            }

            if (!isset($this->params)) {
                $this->params = '{}';
            }
        }

        return parent::check();
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
