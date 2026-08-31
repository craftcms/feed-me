<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\Category as CategoryElement;
use craft\feedme\elements\Category;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;

class CategoryTest extends TestCase
{
    use ParsesParentAttributeTests;

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

    protected function parentMatchService(): Category
    {
        return $this->service;
    }

    protected function parentElement(): CategoryElement
    {
        return $this->parentCategory;
    }
}
