<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\Category as CategoryElement;
use craft\feedme\elements\Category;
use craft\feedme\tests\Helpers\ElementFactory;
use function craft\feedme\tests\Helpers\parsesParentAttribute;

beforeEach(function() {
    $this->service = new Category();
    // parseParent() reads $this->service->element->groupId to scope its match query (see
    // issue #1154), so it needs a real element rather than null, same as a real import
    // would set via setModel().
    $this->parent = ElementFactory::createCategory();
    $this->service->element = new CategoryElement();
    $this->service->element->groupId = $this->parent->groupId;
});

parsesParentAttribute();
