<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\Category as CategoryElement;
use craft\feedme\elements\Category;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;

class CategoryTest extends TestCase
{
    private Category $service;

    private CategoryElement $parentCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Category();
        // parseParent() reads $this->service->element->groupId to scope its match query (see
        // issue #1154), so it needs a real element rather than null, same as a real import
        // would set via setModel().
        $this->parentCategory = ElementFactory::createCategory();
        $this->service->element = new CategoryElement();
        $this->service->element->groupId = $this->parentCategory->groupId;
    }

    public function testParentTitle(): void
    {
        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'title'],
        ];

        $feedData = ['parent' => $this->parentCategory->title];
        $this->assertEquals($this->parentCategory->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => $this->parentCategory->title . '-nonexistent'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentId(): void
    {
        $feedData = ['parent' => (string)$this->parentCategory->id];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'id'],
        ];

        $this->assertEquals($this->parentCategory->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => $this->parentCategory->id + 999999];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentSlug(): void
    {
        $feedData = ['parent' => $this->parentCategory->slug];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'slug'],
        ];

        $this->assertEquals($this->parentCategory->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => $this->parentCategory->slug . '-nonexistent'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentDefault(): void
    {
        $feedData = ['parent' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => (string)$this->parentCategory->id,
            'options' => ['match' => 'title'],
        ];

        $this->assertEquals($this->parentCategory->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'title'],
        ];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }
}
