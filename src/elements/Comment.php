<?php
namespace verbb\feedme\elements;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;

use Craft;
use craft\db\Query;
use craft\elements\User as UserElement;
use craft\helpers\Db;

use verbb\comments\Comments;
use verbb\comments\elements\Comment as CommentElement;

use Cake\Utility\Hash;

class Comment extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Comment';
    public static $class = 'verbb\comments\elements\Comment';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/comments/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/comments/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/comments/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return [];
    }

    public function getQuery($settings, $params = [])
    {
        $query = CommentElement::find();

        $criteria = array_merge([
            'status' => null,
        ], $params);

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $criteria['siteId'] = $siteId;
        }

        Craft::configure($query, $criteria);

        return $query;
    }

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

    public function save($element, $settings)
    {
        $this->element = $element;
        
        $propagate = isset($settings['siteId']) && $settings['siteId'] ? false : true;

        $this->element->setScenario(BaseElement::SCENARIO_ESSENTIALS);

        // We have to turn off validation - otherwise Spam checks will kick in
        if (!Craft::$app->getElements()->saveElement($this->element, false, $propagate)) {
            return false;
        }

        return true;
    }


    // Protected Methods
    // =========================================================================

    protected function parseOwnerId($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $match = Hash::get($fieldInfo, 'options.match');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        $elementId = null;

        if (is_numeric($value)) {
            $elementId = $value;
        } else {
            $result = (new Query())
                ->select(['elements.id', 'elements_sites.elementId'])
                ->from(['{{%elements}} elements'])
                ->innerJoin('{{%elements_sites}} elements_sites', '[[elements_sites.elementId]] = [[elements.id]]')
                ->where(['=', $match, Db::escapeParam($value)])
                ->one();

            if ($result) {
                $elementId = $result['id'];
            }
        }

        if ($elementId) {
            return $elementId;
        }
    }

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
                ->andWhere(['=', $match, Db::escapeParam($value)])
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

            $propagate = isset($this->feed['siteId']) && $this->feed['siteId'] ? false : true;

            if (!Craft::$app->getElements()->saveElement($element, true, $propagate)) {
                FeedMe::error('Comment error: Could not create author - `{e}`.', ['e' => json_encode($element->getErrors())]);
            } else {
                FeedMe::info('Author `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }

}
