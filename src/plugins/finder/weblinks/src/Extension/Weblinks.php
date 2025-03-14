<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Finder\Weblinks\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\CMS\Table\Table;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Component\Weblinks\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * Smart Search adapter for Joomla Web Links.
 *
 * @since  2.5
 */
final class Weblinks extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Weblinks';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_weblinks';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'weblink';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Web Link';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__weblinks';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'state';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onFinderCategoryChangeState' => 'onFinderCategoryChangeState',
            'onFinderAfterDelete'         => 'onFinderAfterDelete',
            'onFinderAfterSave'           => 'onFinderAfterSave',
            'onFinderBeforeSave'          => 'onFinderBeforeSave',
            'onFinderChangeState'         => 'onFinderChangeState',
        ]);
    }

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   FinderEvent\AfterCategoryChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderCategoryChangeState(FinderEvent\AfterCategoryChangeStateEvent $event): void
    {
        // Make sure we're handling com_weblinks categories.
        if ($event->getExtension() === 'com_weblinks') {
            $this->categoryStateChange($event->getPks(), $event->getValue());
        }
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * This event will fire when weblinks are deleted and when an indexed item is deleted.
     *
     * @param   FinderEvent\AfterDeleteEvent   $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     * @since   2.5
     */
    public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context === 'com_weblinks.weblink') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        // Remove the item from the index.
        $this->remove($id);
    }

    /**
     * Smart Search after content save method.
     * Reindexes the link information for a weblink that has been saved.
     * It also makes adjustments if the access level of a weblink item or
     * the category to which it belongs has been changed.
     *
     * @param   FinderEvent\AfterSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     * @since   2.5
     */
    public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle web links here. We need to handle front end and back end editing.
        if ($context == 'com_weblinks.weblink' || $context == 'com_weblinks.form') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item.
            $this->reindex($row->id);
        }

        // Check for access changes in the category.
        if ($context === 'com_categories.category') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   FinderEvent\BeforeSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     * @since   2.5
     */
    public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle web links here.
        if ($context === 'com_weblinks.weblink' || $context == 'com_weblinks.form') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category.
        if ($context === 'com_categories.category') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkCategoryAccess($row);
            }
        }
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   FinderEvent\AfterChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        // We only want to handle web links here.
        if ($context === 'com_weblinks.weblink' || $context === 'com_weblinks.form') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled.
        if ($context == 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a FinderIndexerResult object.
     *
     * @param   Result  $item  The item to index as an FinderIndexerResult object.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     * @since   2.5
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled
        if (ComponentHelper::isEnabled($this->extension) == false) {
            return;
        }

        $item->setLanguage();

        // Initialise the item parameters.
        $item->params   = new Registry($item->params);
        $item->metadata = new Registry($item->metadata);

        // Build the necessary route and path information.
        $item->url   = $this->getURL($item->id, $this->extension, $this->layout);
        $item->route = RouteHelper::getWeblinkRoute($item->slug, $item->catslug, $item->language);

        /*
         * Add the meta-data processing instructions based on the newsfeeds
         * configuration parameters.
         */
        // Add the meta-author.
        $item->metaauthor = $item->metadata->get('author');

        // Handle the link to the meta-data.
        $item->addInstruction(Indexer::META_CONTEXT, 'link');
        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');
        $item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');

        // Translate the state. Weblinks should only be published if the category is published and also ensure that 'state' for trashed items is set to zero
        $item->state = $this->translateState($item->state, $item->cat_state);

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'Web Link');

        // Add the category taxonomy data.
        $categories = $this->getApplication()->bootComponent('com_weblinks')->getCategory(['published' => false, 'access' => false]);
        $category   = $categories->get($item->catid);

        // Category does not exist, stop here
        if (!$category) {
            return;
        }

        $item->addNestedTaxonomy('Category', $category, $this->translateState($category->published), $category->access, $category->language);

        // Add the language taxonomy data.
        $item->addTaxonomy('Language', $item->language);

        // Get content extras.
        Helper::getContentExtras($item);

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A JDatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof QueryInterface ? $query : $db->getQuery(true)
            ->select('a.id, a.catid, a.title, a.alias, a.url AS link, a.description AS summary')
            ->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.ordering')
            ->select('a.created_by_alias, a.modified, a.modified_by')
            ->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
            ->select('a.state AS state, a.created AS start_date, a.params')
            ->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');

        // Handle the alias CASE WHEN portion of the query.
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id                 = $query->castAs('CHAR', 'a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
        $case_when_category_alias .= ' THEN ';
        $c_id                     = $query->castAs('CHAR', 'c.id');
        $case_when_category_alias .= $query->concatenate([$c_id, 'c.alias'], ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id . ' END as catslug';
        $query->select($case_when_category_alias)
            ->from('#__weblinks AS a')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        return $query;
    }

    /**
     * Method to get the query clause for getting items to update by time.
     *
     * @param   string  $time  The modified timestamp.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getUpdateQueryByTime($time)
    {
        // Build an SQL query based on the modified time.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->where('a.date >= ' . $db->quote($time));

        return $query;
    }
}
