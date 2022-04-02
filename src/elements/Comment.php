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
use yii\base\Exception;
use craft\errors\ElementNotFoundException;
use Throwable;
use DateTime;
use Carbon\Carbon;
use ArrayAccess;
use craft\helpers\Json;
use craft\base\ElementInterface;

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
    public static string $name = 'Comment';

    /**
     * @var string
     */
    public static string $class = CommentElement::class;

    /**
     * @var ElementInterface|null
     */
    public ?ElementInterface $element = null;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate(): string
    {
        return 'feed-me/_includes/elements/comments/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate(): string
    {
        return 'feed-me/_includes/elements/comments/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/elements/comments/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups(): array
    {
        return [];
    }
    /**
     * @inheritDoc
     */
    public function getQuery($settings, array $params = []): mixed
    {
        $query = CommentElement::find()
            ->status(null)
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings): ElementInterface
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
     * @return array|ArrayAccess|mixed|string|null
     */
    protected function parseComment($feedData, $fieldInfo): mixed
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $this->element->setComment($value);

        return $value;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|Carbon|DateTime|false|string|null
     * @throws \Exception
     */
    protected function parseCommentDate($feedData, $fieldInfo): DateTime|bool|array|Carbon|string|null
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
    protected function parseOwnerId($feedData, $fieldInfo): mixed
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

        return null;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return int|null
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    protected function parseUserId($feedData, $fieldInfo): ?int
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

            if (!Craft::$app->getElements()->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
                Plugin::error('Comment error: Could not create author - `{e}`.', ['e' => Json::encode($element->getErrors())]);
            } else {
                Plugin::info('Author `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }
}
