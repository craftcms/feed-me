<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151014_073754_feedme_addStatusOption extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'status', ColumnType::Bool, 'passkey');

        // populate any existing feed with status = true
        $feeds = craft()->db->createCommand()->select('*')->from('feedme_feeds')->queryAll();

        foreach ($feeds as $feed) {
            $data = array('status' => true);

            craft()->db->createCommand()->update('feedme_feeds', $data, 'id = :id', array(':id' => $feed['id']));
        }

        return true;
	}
}
