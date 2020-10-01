<?php

use Codeception\Test\Unit;
use craft\feedme\helpers\PaginationHelper;

class PaginationHelperTest extends Unit
{
    public function testCanGetTheCombinedURL()
    {
        // Arrange
        $feedUrl = "https://some-api.example.com/v1/results?per_page=200";
        $relativeUrl = "/v1/results?page=2";

        // Act
        $url = PaginationHelper::getCombinedUrl($feedUrl, $relativeUrl);

        // Assert
        $this->assertEquals('https://some-api.example.com/v1/results?page=2&per_page=200', $url);
    }
}
