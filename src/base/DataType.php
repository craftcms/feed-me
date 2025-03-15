<?php

namespace craft\feedme\base;

use Cake\Utility\Hash;
use Craft;
use craft\base\Batchable;
use craft\base\Component;
use craft\helpers\UrlHelper;

/**
 *
 * @property-read mixed $name
 * @property-read mixed $class
 */
abstract class DataType extends Component implements Batchable
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    protected array $feedData = [];

    // Public
    // =========================================================================

    /**
     * @return mixed
     */
    public function getName(): string
    {
        /** @phpstan-ignore-next-line */
        return static::$name;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return get_class($this);
    }

    /**
     * @param $array
     * @param $feed
     */
    public function setupPaginationUrl($array, $feed): void
    {
        if (!$feed->paginationNode) {
            return;
        }

        // Find the URL value in the feed
        $flatten = Hash::flatten($array, '/');
        $url = Hash::get($flatten, $feed->paginationNode);

        // resolve any aliases in the pagination URL
        $url = Craft::getAlias($url);

        // if the feed provides a root relative URL, make it whole again based on the feed.
        if ($url && UrlHelper::isRootRelativeUrl($url)) {
            $url = UrlHelper::hostInfo($feed->feedUrl) . $url;
        }

        // Replace the mapping value with the actual URL
        $feed->paginationUrl = $url;
    }

    /**
     * @inheritdoc
     */
    public function getSlice(int $offset, int $limit): iterable
    {
        $feedData = $this->feedData;

        if ($offset) {
            $feedData = array_slice($feedData, $offset);
        }

        if ($limit) {
            $feedData = array_slice($feedData, 0, $limit);
        }

        return $feedData;
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->feedData);
    }
}
