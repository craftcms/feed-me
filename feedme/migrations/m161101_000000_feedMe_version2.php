<?php
namespace Craft;

class m161101_000000_feedMe_version2 extends BaseMigration
{
    public function safeUp()
    {
        $table = $this->dbConnection->schema->getTable('{{feedme_feeds}}');

        if (!craft()->db->columnExists('feedme_feeds', 'elementType')) {
            craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'elementType', ColumnType::Varchar, 'primaryElement');
        }

        if (!craft()->db->columnExists('feedme_feeds', 'elementGroup')) {
            craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'elementGroup', ColumnType::Text, 'primaryElement');
        }

        if (!craft()->db->columnExists('feedme_feeds', 'fieldDefaults')) {
            craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'fieldDefaults', ColumnType::Text, 'fieldMapping');
        }

        if (!craft()->db->columnExists('feedme_feeds', 'fieldElementMapping')) {
            craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'fieldElementMapping', ColumnType::Text, 'fieldMapping');
        }

        if (!craft()->db->columnExists('feedme_feeds', 'fieldElementDefaults')) {
            craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'fieldElementDefaults', ColumnType::Text, 'fieldMapping');
        }


        // Remove logging tables
        if (craft()->db->tableExists('feedme_logs')) {
            craft()->db->createCommand('SET FOREIGN_KEY_CHECKS = 0;')->execute();
            craft()->db->createCommand()->dropTable('feedme_logs');
            craft()->db->createCommand('SET FOREIGN_KEY_CHECKS = 1;')->execute();
        }

        if (craft()->db->tableExists('feedme_log')) {
            craft()->db->createCommand('SET FOREIGN_KEY_CHECKS = 0;')->execute();
            craft()->db->createCommand()->dropTable('feedme_log');
            craft()->db->createCommand('SET FOREIGN_KEY_CHECKS = 1;')->execute();
        }

        // Grab current feedme data
        $currentData = craft()->db->createCommand()
            ->select('*')
            ->from('feedme_feeds')
            ->queryAll();

        // Move Section/EntryType IDs into ElementGroup column
        craft()->db->createCommand()->update('feedme_feeds', array('elementType' => 'Entry'));

        foreach ($currentData as $data) {
            if (isset($data['section']) && isset($data['entrytype'])) {
                $elementGroup = array(
                    'Category' => '',
                    'Entry' => array(
                        'section' => $data['section'],
                        'entryType' => $data['entrytype'],
                    ),
                    'Commerce_Product' => '',
                    'User' => '',
                );

                craft()->db->createCommand()->update('feedme_feeds', array('elementGroup' => json_encode($elementGroup)), 'id = ' . $data['id']);
            }
        }

        // Delete old columns
        if ($table->getColumn('section') !== null) {
            $this->dropColumn('feedme_feeds', 'section');
        }
        if ($table->getColumn('entrytype') !== null) {
            $this->dropColumn('feedme_feeds', 'entrytype');
        }

        // Loop through existing mapping - reverse values and keys
        foreach ($currentData as $data) {
            $newMapping = array();

            $fieldMapping = json_decode($data['fieldMapping'], true);

            if ($fieldMapping) {
                foreach ($fieldMapping as $feedHandle => $handle) {
                    $newFieldHandle = $handle;

                    if (preg_match('/^(.*)\[(.*)]$/', $newFieldHandle, $matches)) {
                        $fieldHandle    = $matches[1];
                        $subfieldHandle = $matches[2];

                        $newFieldHandle = $fieldHandle . '--' . $subfieldHandle;
                    }

                    $newMapping[$newFieldHandle] = $feedHandle;
                }
            }

            craft()->db->createCommand()->update('feedme_feeds', array('fieldMapping' => json_encode($newMapping)), 'id = ' . $data['id']);
        }

        // Loop through the Unique fields - a little trickier due to now using field handles over feed handles
        foreach ($currentData as $data) {
            $newMapping = array();

            $fieldMapping = json_decode($data['fieldMapping'], true);
            $fieldUnique = json_decode($data['fieldUnique'], true);

            if ($fieldUnique) {
                foreach ($fieldUnique as $feedHandle => $isUnique) {
                    if (isset($fieldMapping[$feedHandle])) {
                        $handle = $fieldMapping[$feedHandle];

                        $newMapping[$handle] = $isUnique;
                    }
                }
            }

            craft()->db->createCommand()->update('feedme_feeds', array('fieldUnique' => json_encode($newMapping)), 'id = ' . $data['id']);
        }

        return true;
    }
}
