<?php
namespace verbb\feedme\migrations;

use Craft;
use craft\db\Query;
use craft\db\Migration;
use craft\helpers\Db;
use craft\helpers\Json;

class m190201_000000_update_asset_feeds extends Migration
{
    public function safeUp()
    {
        $feeds = (new Query())
            ->select(['*'])
            ->from(['{{%feedme_feeds}}'])
            ->where(['elementType' => 'craft\elements\Asset'])
            ->all();

        foreach ($feeds as $key => $feed) {
            $fieldMapping = Json::decode($feed['fieldMapping']);

            if (!isset($fieldMapping['urlOrPath']) && isset($fieldMapping['filename'])) {
                $fieldMapping['urlOrPath'] = $fieldMapping['filename'];
            
                $this->update('{{%feedme_feeds}}', [
                    'fieldMapping' => Json::encode($fieldMapping),
                ], [
                    'id' => $feed['id']
                ]);
            }
        }
    
        return true;
    }

    public function safeDown()
    {
        echo "m190201_000000_update_asset_feeds cannot be reverted.\n";

        return false;
    }
}
