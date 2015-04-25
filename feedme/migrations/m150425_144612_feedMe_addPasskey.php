<?php
namespace Craft;

class m150425_144612_feedMe_addPasskey extends BaseMigration
{
    public function safeUp()
    {
        craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'passkey', ColumnType::Varchar, 'fieldUnique');

        // populate any existing feed with new passkeys
        $feeds = craft()->db->createCommand()->select('*')->from('feedme_feeds')->queryAll();

        foreach ($feeds as $feed) {
            $data = array('passkey' => $this->generateRandomString(10));
            
            craft()->db->createCommand()->update('feedme_feeds', $data, 'id = :id', array(':id' => $feed['id']));
        }

        return true;
    }

    public static function generateRandomString($length = 5)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}
