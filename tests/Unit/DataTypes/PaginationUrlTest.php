<?php

namespace craft\feedme\tests\Unit\DataTypes;

use craft\feedme\datatypes\Json;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;

beforeEach(function() {
    $this->feed = new FeedModel([
        'feedUrl' => 'https://example.com/feed.json',
        'paginationNode' => 'next',
    ]);
});

afterEach(function() {
    Plugin::$plugin->getSettings()->allowPathInPaginationUrl = false;
});

test('a root relative pagination url is made absolute by default', function() {
    (new Json())->setupPaginationUrl(['next' => '/feed.json?page=2'], $this->feed);

    expect($this->feed->paginationUrl)->toBe('https://example.com/feed.json?page=2');
    expect($this->feed->getNextPagination())->toBeTrue();
});

test('a path pagination url is not followed by default', function() {
    $this->feed->paginationUrl = 'feeds/feed.json?page=2';

    expect($this->feed->getNextPagination())->toBeFalse();
});

test('a path pagination url is kept and followed when allowed', function() {
    // https://github.com/craftcms/feed-me/issues/1610 - with `allowPathInPaginationUrl` on, a
    // path can be used as the pagination URL, the same way it can be used as the feed URL.
    Plugin::$plugin->getSettings()->allowPathInPaginationUrl = true;

    (new Json())->setupPaginationUrl(['next' => '/var/feeds/feed.json?page=2'], $this->feed);

    expect($this->feed->paginationUrl)->toBe('/var/feeds/feed.json?page=2');
    expect($this->feed->getNextPagination())->toBeTrue();
    expect($this->feed->feedUrl)->toBe('/var/feeds/feed.json?page=2');
});
