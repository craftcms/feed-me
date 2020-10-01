<?php

namespace craft\feedme\helpers;

class PaginationHelper
{
    public static function getCombinedUrl(string $url, string $relative)
    {
        $parsed = parse_url($url);

        // remove the path from the relative URL
        $path = $parsed['path'];
        $clean = str_replace($path, "", $relative);

        if (strpos($clean, "?")) {
            ltrim($clean, "");
        }

        // combine the params from the
        $params = [];

        parse_str($parsed['query'], $params);

        return $clean;
    }
}
