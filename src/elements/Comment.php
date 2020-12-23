<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\db\Query;
use craft\elements\User as UserElement;
use craft\feedme\base\Element;
use craft\feedme\Plugin;
use verbb\comments\Comments;
use verbb\comments\elements\Comment as CommentElement;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read array $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class Comment extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Comment';

    /**
     * @var string
     */
    public static $class = 'verbb\comments\elements\Comment';

    /**
     * @var
     */
    public $element;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/comments/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/comments/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/comments/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups()
    {
        return [];
    }
    /**
     * @inheritDoc
     */
    public function getQuery($settings, $params = [])
    {
        $query = CommentElement::find()
            ->anyStatus()
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings)
    {
        $this->element = new CommentElement();
        $this->element->structureId = Comments::getInstance()->getSettings()->structureId;

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|\ArrayAccess|mixed|string|null
     */
    protected function parseComment($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $this->element->setComment($value);

        return $value;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|\Carbon\Carbon|\DateTime|false|string|null
     */
    protected function parseCommentDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return mixed|null
     */
    protected function parseOwnerId($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $match = Hash::get($fieldInfo, 'options.match');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        $elementId = null;

        // Because we can match on element attributes and custom fields, AND we're directly using SQL
        // queries in our `where` below, we need to check if we need a prefix for custom fields accessing
        // the content table.
        $columnName = $match;

        if (Craft::$app->getFields()->getFieldByHandle($match)) {
            $columnName = Craft::$app->getFields()->oldFieldColumnPrefix . $match;
        }

        $result = (new Query())
            ->select(['elements.id', 'elements_sites.elementId'])
            ->from(['{{%elements}} elements'])
            ->innerJoin('{{%elements_sites}} elements_sites', '[[elements_sites.elementId]] = [[elements.id]]')
            ->innerJoin('{{%content}} content', '[[content.elementId]] = [[elements.id]]')
            ->where(['=', $columnName, $value])
            ->one();

        if ($result) {
            $elementId = $result['id'];
        }

        if ($elementId) {
            return $elementId;
        }
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return int|null
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function parseUserId($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $match = 'elements.id';
        }

        if ($match === 'fullName') {
            $element = UserElement::findOne(['search' => $value, 'status' => null]);
        } else {
            $element = UserElement::find()
                ->status(null)
                ->andWhere(['=', $match, $value])
                ->one();
        }

        if ($element) {
            return $element->id;
        }

        // Check if we should create the element. But only if email is provided (for the moment)
        if ($create && $match === 'email') {
            $element = new UserElement();
            $element->username = $value;
            $element->email = $value;

            if (!Craft::$app->getElements()->saveElement($element)) {
                Plugin::error('Comment error: Could not create author - `{e}`.', ['e' => json_encode($element->getErrors())]);
            } else {
                Plugin::info('Author `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }
}
