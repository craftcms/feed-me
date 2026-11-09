<?php

namespace craft\feedme\tests\Unit\Elements;

use craft\feedme\elements\Asset;

beforeEach(function() {
    $this->service = new Asset();
});

it('parses filename from absolute url', function() {
    $feedMapping = ['attribute' => true, 'node' => 'image'];

    expect($this->service->parseAttribute(['image' => 'http://example.com/path/photo.jpg'], 'filename', $feedMapping))
        ->toBe('photo.jpg');
});

it('parses filename from local path', function() {
    $feedMapping = ['attribute' => true, 'node' => 'image'];

    expect($this->service->parseAttribute(['image' => '/var/some/local/path/photo.jpg'], 'filename', $feedMapping))
        ->toBe('photo.jpg');
});
