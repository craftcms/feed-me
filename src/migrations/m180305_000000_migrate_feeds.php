<?php

namespace craft\feedme\migrations;

use craft\db\Migration;
use craft\db\Query;
use verbb\comments\elements\Comment;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\elements\User;
use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\Asset;
use craft\helpers\Json;

class m180305_000000_migrate_feeds extends Migration
{
    public array $elements = [
        'Asset' => Asset::class,
        'Category' => Category::class,
        'Entry' => Entry::class,
        'User' => User::class,

        'Commerce_Product' => Product::class,
        'Commerce_Order' => Order::class,
        'Comments_Comment' => Comment::class,
        'Calendar_Event' => 'solspace\calendar\elements\Event',
    ];

    public function safeUp(): bool
    {
        $feeds = (new Query())
            ->select(['*'])
            ->from(['{{%feedme_feeds}}'])
            ->all();

        $table = '{{%feedme_feeds}}';

        if (!empty($feeds)) {
            foreach ($feeds as $feed) {
                $feedId = $feed['id'];

                // Convert the Element Type
                if (isset($feed['elementType'], $this->elements[$feed['elementType']])) {
                    $oldElementType = $feed['elementType'];
                    $newElementType = $this->elements[$oldElementType];

                    $this->update($table, ['elementType' => $newElementType], ['id' => $feedId]);
                }

                // Convert the Element Group
                if (isset($feed['elementGroup'])) {
                    $elementGroup = Json::decode($feed['elementGroup'], true);

                    if ($elementGroup) {
                        foreach ($elementGroup as $key => $value) {
                            if (isset($this->elements[$key])) {
                                $oldElementType = $key;
                                $newElementType = $this->elements[$oldElementType];

                                unset($elementGroup[$key]);
                                $elementGroup[$newElementType] = $value;
                            }
                        }

                        $elementGroup = Json::encode($elementGroup);
                    }

                    $this->update($table, ['elementGroup' => $elementGroup], ['id' => $feedId]);
                }
            }

            // Rename the 'locale' column
            if ($this->db->columnExists($table, 'locale')) {
                $this->renameColumn($table, 'locale', 'siteId');
            }

            // Our old field mapping columns are impossible to update, without extreme thought (if it all).
            // We're going to rename them for posterity, but users will need to re-create feeds.
            if ($this->db->columnExists($table, 'fieldMapping') && !$this->db->columnExists($table, 'fieldMapping_v2')) {
                $this->renameColumn($table, 'fieldMapping', 'fieldMapping_v2');
            }

            if ($this->db->columnExists($table, 'fieldDefaults')) {
                $this->renameColumn($table, 'fieldDefaults', 'fieldDefaults_v2');
            }

            if ($this->db->columnExists($table, 'fieldElementMapping')) {
                $this->renameColumn($table, 'fieldElementMapping', 'fieldElementMapping_v2');
            }

            if ($this->db->columnExists($table, 'fieldElementDefaults')) {
                $this->renameColumn($table, 'fieldElementDefaults', 'fieldElementDefaults_v2');
            }

            if (!$this->db->columnExists($table, 'fieldMapping')) {
                $this->addColumn($table, 'fieldMapping', $this->text()->after('duplicateHandle'));
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m180305_000000_migrate_feeds cannot be reverted.\n";
        return false;
    }
}
