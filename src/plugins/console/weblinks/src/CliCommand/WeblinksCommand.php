<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Weblinks.console
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Console\Weblinks\CliCommand;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class WeblinksCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    // Name configuration of the CLI action
    protected static $defaultName = 'weblinks:sync-csv';

    protected function configure(): void
    {
        $this->setDescription('Allows exporting or importing data from com_weblinks via a CSV file.');
        $this->setHelp('Run this tool to back up your web links to a local file, or parse a modified CSV to import rows back into the application.');
        $this->addOption(
            'action',
            'a',
            InputOption::VALUE_REQUIRED,
            'Specify the operational task: "export" or "import".',
            'export'
        )
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Target absolute pathway for the CSV data asset file.',
                JPATH_ROOT . '/tmp/weblinks_export.csv'
            );
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $action   = strtolower($input->getOption('action'));
        $filePath = $input->getOption('file');

        if ($action === 'export') {
            return $this->handleExport($io, $filePath);
        } elseif ($action === 'import') {
            return $this->handleImport($io, $filePath);
        }

        $io->error(\sprintf('Invalid action: "%s". Please explicitly specify either "--action=export" or "--action=import".', $action));
        return Command::INVALID;
    }

    /**
     * Handles exporting logic from database to CSV
     */
    private function handleExport(SymfonyStyle $io, string $filePath): int
    {
        $io->title('Starting com_weblinks Data Export to CSV');

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Fetching all columns from com_weblinks
        $query->select('*')
            ->from($db->quoteName('#__weblinks'))
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);
            $rows = $db->loadAssocList();
        } catch (\Exception $e) {
            $io->error('Database error encountered: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if (empty($rows)) {
            $io->warning('No data records found in the com_weblinks table to export.');
            return Command::SUCCESS;
        }

        // Verify or create pathing directory validity
        $directory = \dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Open file pointer for writing
        $fileHandle = fopen($filePath, 'w');

        if ($fileHandle === false) {
            $io->error('Unable to open target file path for writing operations: ' . $filePath);
            return Command::FAILURE;
        }

        // Add Byte Order Mark (BOM) to fix UTF-8 formatting anomalies inside MS Excel
        fprintf($fileHandle, \chr(0xEF) . \chr(0xBB) . \chr(0xBF));

        // Inject Table Headers using first row keys array mapping
        fputcsv($fileHandle, array_keys($rows[0]), ',', '"', '\\');

        // Parse and push data sets
        foreach ($rows as $row) {
            fputcsv($fileHandle, $row, ',', '"', '\\');
        }

        fclose($fileHandle);

        $io->success(\sprintf('Successfully exported %d links into file path: %s', \count($rows), $filePath));

        return Command::SUCCESS;
    }

    /**
     * Handles parsing an external CSV file to import rows into database
     */
    private function handleImport(SymfonyStyle $io, string $filePath): int
    {
        $io->title('Starting CSV Data Import into com_weblinks');

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $io->error(\sprintf('The targeting CSV source file does not exist or cannot be parsed: %s', $filePath));
            return Command::FAILURE;
        }

        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle === false) {
            $io->error('Failed to open file resource handle stream.');
            return Command::FAILURE;
        }

        // Strip unexpected BOM characters if present (e.g., from Excel saves)
        $bom = fread($fileHandle, 3);
        if ($bom !== \chr(0xEF) . \chr(0xBB) . \chr(0xBF)) {
            rewind($fileHandle);
        }

        // FIXED: Explicitly passed default separator, enclosure, and escape character
        $headers = fgetcsv($fileHandle, 0, ',', '"', '\\');
        if (!$headers) {
            $io->error('The target CSV file structure appears empty or corrupted.');
            fclose($fileHandle);
            return Command::FAILURE;
        }

        $db = $this->getDatabase();

        // --- CATEGORY SAFETY PRE-CHECK ---
        // Fetch all valid active category IDs and aliases for com_weblinks
        $catQuery = $db->getQuery(true)
            ->select($db->quoteName(['id', 'alias']))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('extension') . ' = ' . $db->quote('com_weblinks'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($catQuery);
        $categoriesList = $db->loadObjectList();

        $validCategoryIds = [];
        $fallbackCatId    = null;

        if (!empty($categoriesList)) {
            foreach ($categoriesList as $cat) {
                $validCategoryIds[] = (int) $cat->id;

                // Match the official Joomla "Uncategorised" alias
                if ($cat->alias === 'uncategorized') {
                    $fallbackCatId = (int) $cat->id;
                }
            }

            // If explicit "Uncategorised" category is missing/renamed, use the first valid category as a fallback
            if ($fallbackCatId === null) {
                $fallbackCatId = (int) $categoriesList[0]->id;
            }
        } else {
            // Ultimate fallback to Joomla's traditional default ID if no categories exist at all
            $fallbackCatId = 2;
        }
        // ----------------------------------

        $importedCount = 0;
        $updatedCount  = 0;

        // FIXED: Explicitly passed default length, separator, enclosure, and escape character
        while (($row = fgetcsv($fileHandle, 0, ',', '"', '\\')) !== false) {
            $data = array_combine($headers, $row);
            if (!$data) {
                continue;
            }

            $id          = !empty($data['id']) ? (int)$data['id'] : null;
            $title       = $data['title'] ?? 'Untitled Link';
            $alias       = !empty($data['alias']) ? OutputFilter::stringURLSafe($data['alias']) : OutputFilter::stringURLSafe($title);
            $url         = $data['url'] ?? '';
            $description = $data['description'] ?? '';
            $hits        = isset($data['hits']) ? (int)$data['hits'] : 0;
            $state       = isset($data['state']) ? (int)$data['state'] : 1;
            $checked_out = !empty($data['checked_out']) ? (int)$data['checked_out'] : null;
            $checked_out_time = !empty($data['checked_out_time']) && $data['checked_out_time'] !== 'NULL' ? $data['checked_out_time'] : null;
            $ordering    = isset($data['ordering']) ? (int)$data['ordering'] : 0;
            $access       = isset($data['access']) ? (int)$data['access'] : 1;
            $params       = $data['params'] ?? '';
            $language     = $data['language'] ?? '*';            
            $created     = !empty($data['created']) ? $data['created'] : date('Y-m-d H:i:s');
            $createdBy   = !empty($data['created_by']) ? (int)$data['created_by'] : 990;
            $createdByAlias = $data['created_by_alias'] ?? '';
            $modified    = !empty($data['modified']) && $data['modified'] !== 'NULL' && $data['modified'] !== '0000-00-00 00:00:00' ? $data['modified'] : date('Y-m-d H:i:s');
            $modifiedBy  = !empty($data['modified_by']) ? (int)$data['modified_by'] : $createdBy;
            $metakey     = $data['metakey'] ?? '';
            $metadesc    = $data['metadesc'] ?? '';
            $metadata     = $data['metadata'] ?? '';
            $featured      = isset($data['featured']) ? (int)$data['featured'] : 0;
            $xreference      = $data['xreference'] ?? '';
            $publish_up      = !empty($data['publish_up']) && $data['publish_up'] !== 'NULL' ? $data['publish_up'] : null;
            $publish_down      = !empty($data['publish_down']) && $data['publish_down'] !== 'NULL' ? $data['publish_down'] : null;
            $version      = !empty($data['version']) ? (int)$data['version'] : 1;
            $images     = $data['images'] ?? '';


            // --- CATEGORY VALIDATION LOGIC ---
            $csvCatId = !empty($data['catid']) ? (int)$data['catid'] : 0;

            if (\in_array($csvCatId, $validCategoryIds)) {
                $catid = $csvCatId;
            } else {
                // The CSV ID is invalid or missing. Assign fallback and notify the operator via terminal.
                $catid = $fallbackCatId;
                $io->warning(\sprintf('Category ID %d for link "%s" was not found or is unpublished. Fallback applied: Cat ID %d ("uncategorized").', $csvCatId, $title, $fallbackCatId));
            }
            // ----------------------------------

            // Check if record already exists to determine update vs insert operations
            $exists = false;
            if ($id) {
                $checkQuery = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__weblinks'))
                    ->where($db->quoteName('id') . ' = ' . $id);
                $db->setQuery($checkQuery);
                $exists = (bool) $db->loadResult();
            }

            $object = new \stdClass();
            if ($exists) {
                $object->id = $id;
            }
            $object->title        = $title;
            $object->alias        = $alias;
            $object->url          = $url;
            $object->description  = $description;
            $object->hits         = $hits;
            $object->state        = $state;
            $object->catid        = $catid;
            $object->created      = $created;
            $object->created_by   = $createdBy;
            $object->checked_out = $checked_out;
            $object->checked_out_time = $checked_out_time;
            $object->ordering = $ordering;
            $object->access = $access;
            $object->params = $params;
            $object->language = $language;
            $object->created_by_alias = $createdByAlias;
            $object->modified = $modified;
            $object->modified_by = $modifiedBy;
            $object->metakey = $metakey;
            $object->metadesc = $metadesc;
            $object->metadata = $metadata;
            $object->featured = $featured;
            $object->xreference = $xreference;
            $object->publish_up = $publish_up;
            $object->publish_down = $publish_down;
            $object->version = $version;
            $object->images = $images;
            try {
                if ($exists) {
                    $db->updateObject('#__weblinks', $object, 'id');
                    $updatedCount++;
                } else {
                    if (empty($object->id)) {
                        unset($object->id);
                    }
                    $db->insertObject('#__weblinks', $object);
                    $importedCount++;
                }
            } catch (\Exception $e) {
                $io->warning(\sprintf('Skipped processing item ("%s") due to engine error: %s', $title, $e->getMessage()));
            }
        }

        fclose($fileHandle);

        $io->success([
            'Data synchronization run finalized completed successfully!',
            \sprintf('New Links Inserted: %d', $importedCount),
            \sprintf('Existing Links Modified: %d', $updatedCount),
        ]);

        return Command::SUCCESS;
    }
}
