<?php

namespace craft\feedme\models;

use ArrayAccess;
use Cake\Utility\Hash;
use Craft;
use craft\base\Model;
use craft\feedme\base\Element;
use craft\feedme\base\ElementInterface;
use craft\feedme\helpers\DuplicateHelper;
use craft\feedme\Plugin;
use DateTime;

/**
 * Class FeedModel
 *
 * @property-read mixed $duplicateHandleFriendly
 * @property-read mixed $dataType
 * @property-read bool $nextPagination
 * @property-read ElementInterface|Element|null $element
 */
class FeedModel extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string|null
     */
    public ?string $feedUrl = null;

    /**
     * @var string|null
     */
    public ?string $feedType = null;

    /**
     * @var string|null
     */
    public ?string $primaryElement = null;

    /**
     * @var string|null
     */
    public ?string $elementType = null;

    /**
     * @var array|null
     */
    public ?array $elementGroup = null;

    /**
     * @var int|null
     */
    public ?int $siteId = null;

    /**
     * @var int|null
     */
    public ?int $sortOrder = null;

    /**
     * @var bool
     * @since 4.3.0
     */
    public ?bool $singleton = false;

    /**
     * @var array|null
     */
    public ?array $duplicateHandle = null;

    /**
     * @var bool
     * @since 4.4.0
     */
    public ?bool $updateSearchIndexes = true;

    /**
     * @var string|null
     */
    public ?string $paginationNode = null;

    /**
     * @var
     */
    public mixed $fieldMapping = null;

    /**
     * @var
     */
    public mixed $fieldUnique = null;

    /**
     * @var string|null
     */
    public ?string $passkey = null;

    /**
     * @var bool|null
     */
    public ?bool $backup = null;

    /**
     * @var DateTime|null
     */
    public ?DateTime $dateCreated = null;

    /**
     * @var DateTime|null
     */
    public ?DateTime $dateUpdated = null;

    /**
     * @var string|null
     */
    public ?string $uid = null;

    // Model-only properties

    /**
     * @var bool|null
     */
    public ?bool $debug = null;

    /**
     * @var string|null
     */
    public ?string $paginationUrl = null;


    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString()
    {
        return Craft::t('feed-me', $this->name);
    }

    /**
     * @return string
     */
    public function getDuplicateHandleFriendly(): string
    {
        return DuplicateHelper::getFriendly($this->duplicateHandle);
    }

    /**
     * @return mixed|null
     */
    public function getDataType(): mixed
    {
        return Plugin::$plugin->data->getRegisteredDataType($this->feedType);
    }

    /**
     * @return ElementInterface|Element|null
     */
    public function getElement(): ElementInterface|Element|null
    {
        $element = Plugin::$plugin->elements->getRegisteredElement($this->elementType);

        if ($element) {
            /** @var Element $element */
            $element->feed = $this;
        }

        return $element;
    }

    /**
     * @param bool $usePrimaryElement
     * @return array|ArrayAccess|mixed|null
     */
    public function getFeedData(bool $usePrimaryElement = true): mixed
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        return Hash::get($feedDataResponse, 'data');
    }

    /**
     * @param false $usePrimaryElement
     * @return mixed
     */
    public function getFeedNodes(bool $usePrimaryElement = false): mixed
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = Plugin::$plugin->data->getFeedNodes($feedData);

        return $feedDataResponse;
    }

    /**
     * @param bool $usePrimaryElement
     * @return mixed
     */
    public function getFeedMapping(bool $usePrimaryElement = true): mixed
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = Plugin::$plugin->data->getFeedMapping($feedData);

        return $feedDataResponse;
    }

    /**
     * @return bool
     */
    public function getNextPagination(): bool
    {
        if (!$this->paginationUrl || !filter_var($this->paginationUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Set the URL dynamically on the feed, then kick off processing again
        $this->feedUrl = $this->paginationUrl;

        return true;
    }

    /**
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'feedUrl', 'feedType', 'elementType', 'duplicateHandle', 'passkey'], 'required'],
            [['backup'], 'boolean'],
        ];
    }
}
