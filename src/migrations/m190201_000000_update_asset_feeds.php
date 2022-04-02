<?php

namespace craft\feedme\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use craft\elements\Asset;

class m190201_000000_update_asset_feeds extends Migration
{
    public function safeUp(): bool
    {
        $feeds = (new Query())
            ->select(['*'])
            ->from(['{{%feedme_feeds}}'])
            ->where(['elementType' => Asset::class])
            ->all();

        foreach ($feeds as $feed) {
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

    public function safeDown(): bool
    {
        echo "m190201_000000_update_asset_feeds cannot be reverted.\n";

        return false;
    }
}
