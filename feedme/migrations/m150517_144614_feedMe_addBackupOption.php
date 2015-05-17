<?php
namespace Craft;

class m150517_144614_feedMe_addBackupOption extends BaseMigration
{
    public function safeUp()
    {
        craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'backup', ColumnType::Bool, 'passkey');

        // populate any existing feed with backup = true
        $feeds = craft()->db->createCommand()->select('*')->from('feedme_feeds')->queryAll();

        foreach ($feeds as $feed) {
            $data = array('backup' => true);
            
            craft()->db->createCommand()->update('feedme_feeds', $data, 'id = :id', array(':id' => $feed['id']));
        }

        return true;
    }
}
