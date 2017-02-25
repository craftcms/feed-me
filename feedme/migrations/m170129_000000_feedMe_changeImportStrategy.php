<?php
namespace Craft;

class m170129_000000_feedMe_changeImportStrategy extends BaseMigration
{
    public function safeUp()
    {
        $table = $this->dbConnection->schema->getTable('{{feedme_feeds}}');

        // Change the Duplication Handling column from Enum to Text
        craft()->db->createCommand()->alterColumn('feedme_feeds', 'duplicateHandle', ColumnType::Text);

        // Now, as each column will have a single string value, we turn that into an array
        $currentData = craft()->db->createCommand()
            ->select('*')
            ->from('feedme_feeds')
            ->queryAll();

        foreach ($currentData as $data) {
            $duplicateHandle = array($data['duplicateHandle']);

            // But - we want to ensure backward compatibility:
            // add = Add
            // update = Add + Update
            // delete = Add + Update + Delete
            if ($data['duplicateHandle'] == 'update') {
                $duplicateHandle[] = 'add';
            }

            if ($data['duplicateHandle'] == 'delete') {
                $duplicateHandle[] = 'add';
                $duplicateHandle[] = 'update';
            }

            craft()->db->createCommand()->update('feedme_feeds', array('duplicateHandle' => json_encode($duplicateHandle)), 'id = ' . $data['id']);
        }

        return true;
    }
}
