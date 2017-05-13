<?php
namespace Craft;

class m170516_000000_feedMe_refactorFieldMapping extends BaseMigration
{
    public function safeUp()
    {
        $table = $this->dbConnection->schema->getTable('{{feedme_feeds}}');

        // Change any field-mapping to remove `[]` and `/.../`
        $currentData = craft()->db->createCommand()
            ->select('*')
            ->from('feedme_feeds')
            ->queryAll();

        foreach ($currentData as $data) {
            $fieldMapping = json_decode($data['fieldMapping'], true);

            if (!is_array($fieldMapping)) {
                continue;
            }

            foreach ($fieldMapping as $key => $value) {
                $value = str_replace('/.../', '/', $value);
                $value = str_replace('[]', '', $value);
            }

            craft()->db->createCommand()->update('feedme_feeds', array('fieldMapping' => json_encode($fieldMapping)), 'id = ' . $data['id']);
        }

        return true;
    }
}
