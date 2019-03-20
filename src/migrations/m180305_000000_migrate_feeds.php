<?php
namespace verbb\feedme\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\MigrationHelper;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\StringHelper;

class m180305_000000_migrate_feeds extends Migration
{
    public $elements = [
        'Asset' => 'craft\elements\Asset',
        'Category' => 'craft\elements\Category',
        'Entry' => 'craft\elements\Entry',
        'User' => 'craft\elements\User',

        'Commerce_Product' => 'craft\commerce\elements\Product',
        'Commerce_Order' => 'craft\commerce\elements\Order',
        'Comments_Comment' => 'verbb\comments\elements\Comment',
        'Calendar_Event' => 'solspace\calendar\elements\Product',
    ];

    public function safeUp()
    {
        $feeds = (new Query())
            ->select(['*'])
            ->from(['{{%feedme_feeds}}'])
            ->all();

        $table = '{{%feedme_feeds}}';

        if (!empty($feeds)) {
            foreach ($feeds as $key => $feed) {
                $feedId = $feed['id'];

                // Convert the Element Type
                if (isset($feed['elementType'])) {
                    if (isset($this->elements[$feed['elementType']])) {
                        $oldElementType = $feed['elementType'];
                        $newElementType = $this->elements[$oldElementType];

                        $this->update($table, ['elementType' => $newElementType], ['id' => $feedId]);
                    }
                }

                // Convert the Element Group
                if (isset($feed['elementGroup'])) {
                    $elementGroup = json_decode($feed['elementGroup'], true);

                    if ($elementGroup) {
                        foreach ($elementGroup as $key => $value) {
                            if (isset($this->elements[$key])) {
                                $oldElementType = $key;
                                $newElementType = $this->elements[$oldElementType];

                                unset($elementGroup[$key]);
                                $elementGroup[$newElementType] = $value;
                            }
                        }

                        $elementGroup = json_encode($elementGroup);
                    }

                    $this->update($table, ['elementGroup' => $elementGroup], ['id' => $feedId]);
                }
            }

            // Rename the 'locale' column
            if ($this->db->columnExists($table, 'locale')) {
                MigrationHelper::renameColumn($table, 'locale', 'siteId', $this);
            }

            // Our old field mapping columns are impossible to update, without extreme thought (if it all).
            // We're going to rename them for posterity, but users will need to re-create feeds.
            if ($this->db->columnExists($table, 'fieldMapping') && !$this->db->columnExists($table, 'fieldMapping_v2')) {
                MigrationHelper::renameColumn($table, 'fieldMapping', 'fieldMapping_v2', $this);
            }
            
            if ($this->db->columnExists($table, 'fieldDefaults')) {
                MigrationHelper::renameColumn($table, 'fieldDefaults', 'fieldDefaults_v2', $this);
            }
            
            if ($this->db->columnExists($table, 'fieldElementMapping')) {
                MigrationHelper::renameColumn($table, 'fieldElementMapping', 'fieldElementMapping_v2', $this);
            }
            
            if ($this->db->columnExists($table, 'fieldElementDefaults')) {
                MigrationHelper::renameColumn($table, 'fieldElementDefaults', 'fieldElementDefaults_v2', $this);
            }

            if (!$this->db->columnExists($table, 'fieldMapping')) {
                $this->addColumn($table, 'fieldMapping', $this->text()->after('duplicateHandle'));
            }
        }

        return true;
    }

    public function safeDown()
    {
        echo "m180305_000000_migrate_feeds cannot be reverted.\n";
        return false;
    }
}
