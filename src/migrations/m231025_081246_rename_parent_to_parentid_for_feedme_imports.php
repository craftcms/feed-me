<?php

namespace craft\feedme\migrations;

use craft\db\Migration;
use craft\db\Query;

/**
 * m231025_081246_rename_parent_to_parentid_for_feedme_imports migration.
 */
class m231025_081246_rename_parent_to_parentid_for_feedme_imports extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $rows = (new Query())
            ->select(['id', 'fieldMapping'])
            ->from('{{%feedme_feeds}}')
            ->all();

        foreach ($rows as $row) {
            $fieldMapping = \json_decode($row['fieldMapping']);

            // Rename the `parent` argument into `parentId` if it exists
            if (isset($fieldMapping->parent)) {
                $fieldMapping->parentId = $fieldMapping->parent;
                unset($fieldMapping->parent);

                $this->update(
                    '{{%feedme_feeds}}',
                    ['fieldMapping' => \json_encode($fieldMapping)],
                    ['id' => $row['id']]
                );
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $rows = (new Query())
            ->select(['id', 'fieldMapping'])
            ->from('{{%feedme_feeds}}')
            ->all();

        foreach ($rows as $row) {
            $fieldMapping = \json_decode($row['fieldMapping']);

            // Rename the `parentId` argument back into `parent` if it exists
            if (isset($fieldMapping->parentId)) {
                $fieldMapping->parent = $fieldMapping->parentId;
                unset($fieldMapping->parentId);

                $this->update(
                    '{{%feedme_feeds}}',
                    ['fieldMapping' => \json_encode($fieldMapping)],
                    ['id' => $row['id']]
                );
            }
        }

        return true;
    }
}
