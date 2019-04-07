<?php

namespace verbb\feedme\elements;

use Cake\Utility\Hash;
use Carbon\Carbon;
use Craft;
use craft\elements\User as UserElement;
use RRule\RfcParser;
use Solspace\Calendar\Calendar;
use Solspace\Calendar\Elements\Event as EventElement;
use Solspace\Calendar\Library\DateHelper;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\Plugin;
use verbb\feedme\services\Process;
use yii\base\Event;

class CalenderEvent extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Calendar Event';
    public static $class = 'Solspace\Calendar\Elements\Event';

    public $element;
    private $rruleInfo = [];
    private $selectDates = [];


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/calendar-event/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/calendar-event/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/calendar-event/map';
    }


    // Public Methods
    // =========================================================================

    public function init()
    {
        Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_SAVE, function(FeedProcessEvent $event) {
            $this->_onBeforeElementSave($event);
        });

        Event::on(Process::class, Process::EVENT_STEP_AFTER_ELEMENT_SAVE, function(FeedProcessEvent $event) {
            $this->_onAfterElementSave($event);
        });
    }

    public function getGroups()
    {
        if (Calendar::getInstance()) {
            return Calendar::getInstance()->calendars->getAllAllowedCalendars();
        }
    }

    public function getQuery($settings, $params = [])
    {
        $query = EventElement::find();

        $criteria = array_merge([
            'status' => null,
            'calendarId' => $settings['elementGroup'][EventElement::class],
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
        $siteId = (int)Hash::get($settings, 'siteId');
        $calendarId = $settings['elementGroup'][EventElement::class];

        $this->element = EventElement::create($siteId, $calendarId);

        return $this->element;
    }


    // Protected Methods
    // =========================================================================

    protected function parseStartDate($feedData, $fieldInfo)
    {
        return $this->_parseDate($feedData, $fieldInfo);
    }

    protected function parseEndDate($feedData, $fieldInfo)
    {
        return $this->_parseDate($feedData, $fieldInfo);
    }

    protected function parseUntil($feedData, $fieldInfo)
    {
        return $this->_parseDate($feedData, $fieldInfo);
    }

    protected function parseAuthorId($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $value = $value[0];
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

            $propagate = isset($this->feed['siteId']) && $this->feed['siteId'] ? false : true;

            if (!Craft::$app->getElements()->saveElement($element, true, $propagate)) {
                Plugin::error('Event error: Could not create author - `{e}`.', ['e' => json_encode($element->getErrors())]);
            } else {
                Plugin::info('Author `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }

    protected function parseRrule($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        try {
            $rules = RfcParser::parseRRule($value);

            foreach ($rules as $ruleKey => $ruleValue) {
                $attributes = [
                    'BYMONTH' => 'byMonth',
                    'BYYEARDAY' => 'byYearDay',
                    'BYMONTHDAY' => 'byMonthDay',
                    'BYDAY' => 'byDay',
                ];

                $attribute = $attributes[$ruleKey] ?? strtolower($ruleKey);

                if ($ruleKey === 'UNTIL') {
                    $ruleValue = new Carbon($ruleValue->format('Y-m-d H:i:s'), DateHelper::UTC);
                }

                // We can't modify other attributes here, so store them until we can
                $this->rruleInfo[$attribute] = $ruleValue;
            }
        } catch (\Throwable $e) {
            Plugin::error($e->getMessage());
        }
    }

    protected function parseSelectDates($feedData, $fieldInfo)
    {
        $value = $this->fetchArrayValue($feedData, $fieldInfo);
        $this->selectDates = $value;
    }


    // Private Methods
    // =========================================================================

    private function _parseDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        $date = $this->parseDateAttribute($value, $formatting);

        // Calendar expects dates as Carbon object, not DateTime
        if ($date) {
            return new Carbon($date->format('Y-m-d H:i:s') ?? 'now', DateHelper::UTC);
        }
    }

    private function _onBeforeElementSave($event)
    {
        // We prepare rrule info earlier on
        foreach ($this->rruleInfo as $key => $value) {
            $event->element->$key = $value;

            // Also update it in our debug info
            $event->contentData[$key] = $value;
        }
    }

    private function _onAfterElementSave($event)
    {
        if (sizeof($this->selectDates)) {
            $EventElement = EventElement::find()->id($event->element->id)->one();
            Calendar::getInstance()->selectDates->saveDates($EventElement, $this->selectDates);
        }
    }


}
