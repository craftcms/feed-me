<?php

use Codeception\Test\Unit;

class InstallTest extends Unit
{
    public function testPhpUnitIsWorking()
    {
        $this->assertEquals(1, 1);
    }

    public function testCraftHasDatabase()
    {
        $this->assertTrue(Craft::$app->getDb()->getIsActive());
    }

    public function testFeedMeIsInstalled()
    {
        $this->assertNotNull(Craft::$app->plugins->getPlugin('feed-me'));
    }
}
