<?php

namespace craft\feedme\tests\Feature\Fields;

use Craft;
use craft\elements\Category as CategoryElement;
use craft\feedme\fields\Categories;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\feedme\tests\TestCase;
use craft\fields\Categories as CategoriesField;
use craft\models\CategoryGroup;

class CategoriesFieldTest extends TestCase
{
    private Categories $service;

    private CategoryGroup $group;

    private CategoryElement $parent;

    private CategoryElement $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = ElementFactory::createCategoryGroup();

        $this->parent = ElementFactory::createCategory(['groupId' => $this->group->id]);
        $this->child = ElementFactory::createCategory(['groupId' => $this->group->id]);
        Craft::$app->getStructures()->append($this->group->structureId, $this->child, $this->parent);

        $this->service = FieldServiceFactory::create(Categories::class, new CategoriesField([
            'source' => 'group:' . $this->group->uid,
        ]));
        $this->service->fieldInfo = ['node' => 'category', 'options' => ['match' => 'title']];
        $this->service->feedData = ['category' => $this->child->title];
    }

    public function testMaintainHierarchyFillsInParent(): void
    {
        $this->service->field->maintainHierarchy = true;

        // Matching only the child, with `maintainHierarchy` on, should also pull in its parent -
        // Craft's `fillGapsInElements()` fills the structural "gap" left by the unmatched
        // ancestor so the relation reflects the full branch, not just the matched leaf.
        $result = $this->service->parseField();

        $this->assertContains($this->child->id, $result);
        $this->assertContains($this->parent->id, $result);
        $this->assertCount(2, $result);
    }

    public function testWithoutMaintainHierarchyOnlyTheMatchIsReturned(): void
    {
        $this->service->field->maintainHierarchy = false;

        $result = $this->service->parseField();

        $this->assertSame([$this->child->id], $result);
    }
}
