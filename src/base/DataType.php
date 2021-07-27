<?php

namespace craft\feedme\base;

use Cake\Utility\Hash;
use craft\base\Component;
use craft\helpers\UrlHelper;

/**
 *
 * @property-read mixed $name
 * @property-read mixed $class
 */
abstract class DataType extends Component
{
    // Public
    // =========================================================================

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this::$name;
    }

    /**
     * @return false|string
     */
    public function getClass()
    {
        return get_class($this);
    }

    /**
     * @param $array
     * @param $feed
     */
    public function setupPaginationUrl($array, $feed)
    {
        if (!$feed->paginationNode) {
            return;
        }
        // Find the URL value in the feed
        $flatten = Hash::flatten($array, '/');
        $url = Hash::get($flatten, $feed->paginationNode);
        $totalPages = Hash::get($flatten, $feed->paginationTotalNode);

        //check if the pagination url provided is just a page number
        $pagedUrl = $this->_generateNextPaginationFromPageNumber($feed->feedUrl, $url, $totalPages);
        if($pagedUrl) {
            $url = $pagedUrl;
        }

        // if the feed provides a root relative URL, make it whole again based on the feed.
        if ($url && UrlHelper::isRootRelativeUrl($url)) {
            $url = UrlHelper::hostInfo($feed->feedUrl).$url;
        }

        // Replace the mapping value with the actual URL
        $feed->paginationUrl = $url;
    }

    /**
     * Generates the next page url if the next page provided in the field is a page number
     * instead of a url. In this case, the total pages field needs to be provided as well.
     * @param $feedUrl
     * @param $url
     * @param $totalPages
     */
    private function _generateNextPaginationFromPageNumber($feedUrl, $url, $totalPages) {
        if (is_numeric($url) && is_numeric($totalPages)) {
            $nextPage = $url + 1;
            if($nextPage > $totalPages) {
                return null;
            }

            $feedUrl = UrlHelper::removeParam($feedUrl, "page");
            return UrlHelper::urlWithParams($feedUrl, array("page" => $nextPage));
        }
        return null;
    }

}
