<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Carbon\Carbon;
use Craft;
use craft\base\ElementInterface;
use craft\elements\User as UserElement;
use craft\errors\ElementNotFoundException;
use craft\feedme\base\Element;
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\Plugin;
use craft\feedme\services\Process;
use craft\helpers\Json;
use Exception;
use RRule\RfcParser;
use Solspace\Calendar\Calendar;
use Solspace\Calendar\Elements\Event as EventElement;
use Solspace\Calendar\Library\DateHelper;
use Throwable;
use yii\base\Event;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read mixed $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class CalenderEvent extends Element
{
    public const RRULE_MAP = [
        'BYMONTH' => 'byMonth',
        'BYYEARDAY' => 'byYearDay',
        'BYMONTHDAY' => 'byMonthDay',
        'BYDAY' => 'byDay',
        'UNTIL' => 'until',
        'INTERVAL' => 'interval',
        'FREQ' => 'freq',
        'COUNT' => 'count',
    ];

    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Calendar Event';

    /**
     * @var string
     */
    public static string $class = 'Solspace\Calendar\Elements\Event';

    /**
     * @var array
     */
    private array $rruleInfo = [];

    /**
     * @var array
     */
    private array $selectDates = [];


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate(): string
    {
        return 'feed-me/_includes/elements/calendar-events/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate(): string
    {
        return 'feed-me/_includes/elements/calendar-events/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/elements/calendar-events/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_SAVE, function(FeedProcessEvent $event) {
            if ($event->feed['elementType'] === EventElement::class) {
                $this->_onBeforeElementSave($event);
            }
        });

        Event::on(Process::class, Process::EVENT_STEP_AFTER_ELEMENT_SAVE, function(FeedProcessEvent $event) {
            if ($event->feed['elementType'] === EventElement::class) {
                $this->_onAfterElementSave($event);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function getGroups(): array
    {
        if (Calendar::getInstance()) {
            return Calendar::getInstance()->calendars->getAllAllowedCalendars();
        }
        
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getQuery($settings, array $params = []): mixed
    {
        $query = EventElement::find()
            ->status(null)
            ->setCalendarId($settings['elementGroup'][EventElement::class])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings): ElementInterface
    {
        $siteId = (int)Hash::get($settings, 'siteId');
        $calendarId = $settings['elementGroup'][EventElement::class];

        $this->element = EventElement::create($siteId, $calendarId);

        return $this->element;
    }


    // Protected Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return Carbon
     * @throws Exception
     */
    protected function parseStartDate($feedData, $fieldInfo): Carbon
    {
        return $this->_parseDate($feedData, $fieldInfo);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return Carbon
     * @throws Exception
     */
    protected function parseEndDate($feedData, $fieldInfo): Carbon
    {
        return $this->_parseDate($feedData, $fieldInfo);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return Carbon
     * @throws Exception
     */
    protected function parseUntil($feedData, $fieldInfo): Carbon
    {
        return $this->_parseDate($feedData, $fieldInfo);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return int|null
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function parseAuthorId($feedData, $fieldInfo): ?int
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

            if (!Craft::$app->getElements()->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
                Plugin::error('Event error: Could not create author - `{e}`.', ['e' => Json::encode($element->getErrors())]);
            } else {
                Plugin::info('Author `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     */
    protected function parseRrule($feedData, $fieldInfo): void
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        try {
            $rules = RfcParser::parseRRule($value);

            foreach ($rules as $ruleKey => $ruleValue) {
                if (!array_key_exists($ruleKey, self::RRULE_MAP)) {
                    continue;
                }

                $attribute = self::RRULE_MAP[$ruleKey];
                if ($ruleKey === 'UNTIL') {
                    $ruleValue = new Carbon($ruleValue->format('Y-m-d H:i:s'), DateHelper::UTC);
                }

                // We can't modify other attributes here, so store them until we can
                $this->rruleInfo[$attribute] = $ruleValue;
            }
        } catch (Throwable $e) {
            Plugin::error($e->getMessage());
            Craft::$app->getErrorHandler()->logException($e);
        }
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     */
    protected function parseSelectDates($feedData, $fieldInfo): void
    {
        $value = $this->fetchArrayValue($feedData, $fieldInfo);
        $this->selectDates = $value;
    }


    // Private Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return Carbon|null
     * @throws Exception
     */
    private function _parseDate($feedData, $fieldInfo): ?Carbon
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        $date = $this->parseDateAttribute($value, $formatting);

        // Calendar expects dates as Carbon object, not DateTime
        if ($date) {
            return new Carbon($date->format('Y-m-d H:i:s') ?? 'now', DateHelper::UTC);
        }
        
        return null;
    }

    /**
     * @param $event
     */
    private function _onBeforeElementSave($event): void
    {
        // We prepare rrule info earlier on
        foreach ($this->rruleInfo as $key => $value) {
            $event->element->$key = $value;

            // Also update it in our debug info
            $event->contentData[$key] = $value;
        }
    }

    /**
     * @param $event
     */
    private function _onAfterElementSave($event): void
    {
        if (count($this->selectDates)) {
            $EventElement = EventElement::find()->id($event->element->id)->one();
            Calendar::getInstance()->selectDates->saveDates($EventElement, $this->selectDates);
        }
    }
}
