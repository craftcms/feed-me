<?php

namespace craft\feedme\tests\Feature;

use Craft;
use craft\feedme\tests\TestCase;

class InstallTest extends TestCase
{
    public function testCraftHasDatabase(): void
    {
        $this->assertTrue(Craft::$app->getDb()->getIsActive());
    }

    public function testFeedMeIsInstalled(): void
    {
        $this->assertNotNull(Craft::$app->plugins->getPlugin('feed-me'));
    }
}
