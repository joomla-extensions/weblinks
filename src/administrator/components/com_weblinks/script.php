<?php
// Installer script for Weblinks (partial file - only function updated)

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

class com_weblinksInstallerScript
{
    private function getDatabase()
    {
        return \JFactory::getDbo();
    }

    private function insertMissingUcmRecords()
    {
        $db = $this->getDatabase();

        // Helper to validate stored JSON and ensure it contains required keys
        $validateTypeJson = function ($json) {
            if (empty($json)) {
                return false;
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                return false;
            }

            // Ensure 'special' and 'prefix' exist where expected
            if (isset($data['special']) && is_array($data['special']) && isset($data['special']['prefix'])) {
                return true;
            }

            // Also accept legacy structure where 'common' contains mapping, but require prefix
            if (isset($data['common']) && is_array($data['common'])) {
                foreach ($data['common'] as $k => $v) {
                    if (is_array($v) && isset($v['prefix'])) {
                        return true;
                    }
                }
            }

            return false;
        };

        // Template JSON values for the two types
        $categoryTypeJson = '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}';

        $weblinkTypeJson = '{"special":{"dbtable":"#__weblinks","key":"id","type":"Weblink","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}';

        $types = [
            ['alias' => 'com_weblinks.category', 'title' => 'Category', 'json' => $categoryTypeJson],
            ['alias' => 'com_weblinks.weblink', 'title' => 'Web Link', 'json' => $weblinkTypeJson],
        ];

        foreach ($types as $type) {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['type_id', 'type_title', 'rules']))
                ->from($db->quoteName('#__content_types'))
                ->where($db->quoteName('type_alias') . ' = ' . $db->quote($type['alias']));

            $db->setQuery($query);
            $row = $db->loadObject();

            $needsUpsert = false;

            if (!$row) {
                $needsUpsert = true;
            } else {
                // Determine which column stores the JSON: prefer 'rules' or 'type' or 'params'
                $storedJson = '';
                if (isset($row->rules)) {
                    $storedJson = $row->rules;
                } elseif (isset($row->type)) {
                    $storedJson = $row->type;
                } elseif (isset($row->params)) {
                    $storedJson = $row->params;
                }

                if (!$validateTypeJson($storedJson)) {
                    $needsUpsert = true;
                }
            }

            if ($needsUpsert) {
                if ($row && isset($row->type_id)) {
                    // Update existing row: try to update the first available JSON column
                    $upd = $db->getQuery(true)
                        ->update($db->quoteName('#__content_types'))
                        ->where($db->quoteName('type_id') . ' = ' . (int) $row->type_id);

                    // Pick a field to update. Use 'rules' if present, otherwise use 'type' or 'params'.
                    $fieldsToTry = ['rules', 'type', 'params'];
                    $updated = false;
                    foreach ($fieldsToTry as $field) {
                        // Check column existence is non-trivial here; we will attempt to set 'rules' first.
                        $upd2 = clone $upd;
                        $upd2->set($db->quoteName($field) . ' = ' . $db->quote($type['json']));
                        $db->setQuery($upd2);
                        try {
                            $db->execute();
                            $updated = true;
                            break;
                        } catch (\Exception $e) {
                            // ignore and try next field
                        }
                    }

                    if (!$updated) {
                        // As a last resort, replace the entire row using REPLACE (may differ by DB driver)
                        $replace = "REPLACE INTO " . $db->quoteName('#__content_types') . " (type_alias, type_title, rules) VALUES (" . $db->quote($type['alias']) . "," . $db->quote($type['title']) . "," . $db->quote($type['json']) . ")";
                        try {
                            $db->setQuery($replace);
                            $db->execute();
                        } catch (\Exception $e) {
                            // Give up silently to avoid breaking install; core will still handle gracefully.
                        }
                    }
                } else {
                    // Insert a new row with minimal required columns. Adjust for Joomla version schema as needed.
                    $ins = $db->getQuery(true)
                        ->insert($db->quoteName('#__content_types'))
                        ->columns([
                            $db->quoteName('created_user_id'),
                            $db->quoteName('created_time'),
                            $db->quoteName('modified_user_id'),
                            $db->quoteName('modified_time'),
                            $db->quoteName('type_alias'),
                            $db->quoteName('type_title'),
                            $db->quoteName('rules')
                        ])
                        ->values(implode(',', [
                            (int) 0,
                            $db->quote(date('Y-m-d H:i:s')),
                            (int) 0,
                            $db->quote(date('Y-m-d H:i:s')),
                            $db->quote($type['alias']),
                            $db->quote($type['title']),
                            $db->quote($type['json'])
                        ]));

                    try {
                        $db->setQuery($ins);
                        $db->execute();
                    } catch (\Exception $e) {
                        // Ignore failures to avoid breaking install; this is defensive.
                    }
                }
            }
        }
    }
}

?>