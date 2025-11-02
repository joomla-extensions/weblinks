<?php

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

namespace Joomla\Component\Weblinks\Administrator\Script;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Installer script for com_weblinks.
 */
class InstallerScript
{
    /**
     * Get the database object.
     *
     * @return DatabaseInterface
     */
    private function getDatabase(): DatabaseInterface
    {
        return Factory::getDbo();
    }

    /**
     * Ensure that required UCM content type records exist and are valid.
     *
     * This method will insert or update the com_weblinks content type rows if they
     * are missing or malformed (missing 'special'/'prefix').
     *
     * @return void
     */
    private function insertMissingUcmRecords(): void
    {
        $db = $this->getDatabase();

        $types = [
            [
                'alias' => 'com_weblinks.category',
                'title' => 'Category',
                'json'  => json_encode([
                    'special' => [
                        'dbtable' => '#__categories',
                        'key'     => 'id',
                        'type'    => 'Category',
                        'prefix'  => 'JTable',
                        'config'  => 'array()',
                    ],
                    'common' => [
                        'dbtable' => '#__ucm_content',
                        'key'     => 'ucm_id',
                        'type'    => 'Corecontent',
                        'prefix'  => 'JTable',
                        'config'  => 'array()',
                    ],
                ]),
            ],
            [
                'alias' => 'com_weblinks.weblink',
                'title' => 'Web Link',
                'json'  => json_encode([
                    'special' => [
                        'dbtable' => '#__weblinks',
                        'key'     => 'id',
                        'type'    => 'Weblink',
                        'prefix'  => 'JTable',
                        'config'  => 'array()',
                    ],
                    'common' => [
                        'dbtable' => '#__ucm_content',
                        'key'     => 'ucm_id',
                        'type'    => 'Corecontent',
                        'prefix'  => 'JTable',
                        'config'  => 'array()',
                    ],
                ]),
            ],
        ];

        foreach ($types as $type) {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['type_id', 'type_alias', 'rules', 'type', 'params']))
                ->from($db->quoteName('#__content_types'))
                ->where($db->quoteName('type_alias') . ' = ' . $db->quote($type['alias']));

            $db->setQuery($query);

            try {
                $row = $db->loadObject();
            } catch (\RuntimeException $e) {
                // Query failed; skip this type defensively.
                continue;
            }

            $needsUpsert = false;

            if (! $row) {
                $needsUpsert = true;
            } else {
                $storedJson = '';

                if (isset($row->rules) && $row->rules !== '') {
                    $storedJson = $row->rules;
                } elseif (isset($row->type) && $row->type !== '') {
                    $storedJson = $row->type;
                } elseif (isset($row->params) && $row->params !== '') {
                    $storedJson = $row->params;
                }

                if (! $this->isValidUcmJson($storedJson)) {
                    $needsUpsert = true;
                }
            }

            if ($needsUpsert) {
                if (! empty($row) && isset($row->type_id)) {
                    $updated = $this->updateContentTypeRow($db, (int) $row->type_id, $type['json']);
                    if (! $updated) {
                        $this->replaceContentTypeRow($db, $type['alias'], $type['title'], $type['json']);
                    }
                } else {
                    $this->insertContentTypeRow($db, $type['alias'], $type['title'], $type['json']);
                }
            }
        }
    }

    /**
     * Validate the UCM JSON string.
     *
     * @param  string  $json
     * @return bool
     */
    private function isValidUcmJson(string $json): bool
    {
        if ($json === '') {
            return false;
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            return false;
        }

        if (isset($data['special']['prefix'])) {
            return true;
        }

        if (isset($data['common']) && is_array($data['common'])) {
            foreach ($data['common'] as $v) {
                if (is_array($v) && isset($v['prefix'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Attempt to update content types row's JSON columns.
     *
     * @param  DatabaseInterface  $db
     * @param  int                $typeId
     * @param  string             $json
     * @return bool
     */
    private function updateContentTypeRow(DatabaseInterface $db, int $typeId, string $json): bool
    {
        $fieldsToTry = ['rules', 'type', 'params'];

        foreach ($fieldsToTry as $field) {
            $upd = $db->getQuery(true)
                ->update($db->quoteName('#__content_types'))
                ->set($db->quoteName($field) . ' = ' . $db->quote($json))
                ->where($db->quoteName('type_id') . ' = ' . $db->quote($typeId));

            $db->setQuery($upd);

            try {
                $db->execute();

                return true;
            } catch (\RuntimeException $e) {
                // try next field
            }
        }

        return false;
    }

    /**
     * Insert a content type row.
     *
     * @param  DatabaseInterface  $db
     * @param  string             $alias
     * @param  string             $title
     * @param  string             $json
     * @return void
     */
    private function insertContentTypeRow(DatabaseInterface $db, string $alias, string $title, string $json): void
    {
        $ins = $db->getQuery(true)
            ->insert($db->quoteName('#__content_types'))
            ->columns([
                $db->quoteName('created_user_id'),
                $db->quoteName('created_time'),
                $db->quoteName('modified_user_id'),
                $db->quoteName('modified_time'),
                $db->quoteName('type_alias'),
                $db->quoteName('type_title'),
                $db->quoteName('rules'),
            ])
            ->values(implode(',', [
                (int) 0,
                $db->quote(date('Y-m-d H:i:s')),
                (int) 0,
                $db->quote(date('Y-m-d H:i:s')),
                $db->quote($alias),
                $db->quote($title),
                $db->quote($json),
            ]));

        $db->setQuery($ins);

        try {
            $db->execute();
        } catch (\RuntimeException $e) {
            // Intentionally not throwing to avoid breaking installation process.
        }
    }

    /**
     * Replace the content type row as a last resort.
     *
     * @param  DatabaseInterface  $db
     * @param  string             $alias
     * @param  string             $title
     * @param  string             $json
     * @return void
     */
    private function replaceContentTypeRow(DatabaseInterface $db, string $alias, string $title, string $json): void
    {
        $replace = 'REPLACE INTO ' . $db->quoteName('#__content_types')
            . ' (type_alias, type_title, rules) VALUES ('
            . $db->quote($alias) . ', '
            . $db->quote($title) . ', '
            . $db->quote($json) . ')';

        $db->setQuery($replace);

        try {
            $db->execute();
        } catch (\RuntimeException $e) {
            // Skip failure.
        }
    }
}