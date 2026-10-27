<?php

namespace craft\feedme\tests\Feature\Fields;

use Craft;
use craft\feedme\fields\Categories;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\Categories as CategoriesField;

beforeEach(function() {
    $this->group = ElementFactory::createCategoryGroup();

    $this->parent = ElementFactory::createCategory(['groupId' => $this->group->id]);
    $this->child = ElementFactory::createCategory(['groupId' => $this->group->id]);
    Craft::$app->getStructures()->append($this->group->structureId, $this->child, $this->parent);

    $this->service = FieldServiceFactory::create(Categories::class, new CategoriesField([
        'source' => 'group:' . $this->group->uid,
    ]));
    $this->service->fieldInfo = ['node' => 'category', 'options' => ['match' => 'title']];
    $this->service->feedData = ['category' => $this->child->title];
});

it('fills in the parent when maintaining hierarchy', function() {
    $this->service->field->maintainHierarchy = true;

    // Matching only the child, with `maintainHierarchy` on, should also pull in its parent -
    // Craft's `fillGapsInElements()` fills the structural "gap" left by the unmatched
    // ancestor so the relation reflects the full branch, not just the matched leaf.
    $result = $this->service->parseField();

    expect($result)->toContain($this->child->id);
    expect($result)->toContain($this->parent->id);
    expect($result)->toHaveCount(2);
});

it('only returns the match without maintaining hierarchy', function() {
    $this->service->field->maintainHierarchy = false;

    $result = $this->service->parseField();

    expect($result)->toBe([$this->child->id]);
});
