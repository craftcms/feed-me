<?php

namespace craft\feedme\tests\Feature\Elements;

use Craft;
use craft\elements\User as UserElement;
use craft\feedme\elements\User;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;
use craft\models\UserGroup;

class UserTest extends TestCase
{
    private User $service;

    private UserElement $element;

    private UserGroup $existingGroup;

    private UserGroup $newGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new User();
        $this->existingGroup = ElementFactory::createUserGroup();
        $this->newGroup = ElementFactory::createUserGroup();

        // parseGroups() reads $this->service->element->groups to merge/remove against the
        // element's existing group assignments, so it needs a real, saved user already
        // assigned to a group - same as a real import would have.
        $this->element = ElementFactory::createUser();
        Craft::$app->getUsers()->assignUserToGroups($this->element->id, [$this->existingGroup->id]);
        $this->service->element = UserElement::find()->id($this->element->id)->status(null)->one();
    }

    public function testMatchById(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'groups'];

        $groupIds = $this->service->parseAttribute(['groups' => (string)$this->newGroup->id], 'groups', $feedMapping);

        // Numeric matches are passed straight through without casting, so this comes back as a
        // numeric string rather than an int - assertContainsEquals compares loosely.
        $this->assertContainsEquals($this->newGroup->id, $groupIds);
        // Existing group assignments are kept unless `removeFromExisting` is set.
        $this->assertContainsEquals($this->existingGroup->id, $groupIds);
    }

    public function testMatchByName(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'groups'];

        $groupIds = $this->service->parseAttribute(['groups' => $this->newGroup->name], 'groups', $feedMapping);

        $this->assertContains($this->newGroup->id, $groupIds);
        $this->assertContains($this->existingGroup->id, $groupIds);
    }

    public function testMatchByHandle(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'groups'];

        $groupIds = $this->service->parseAttribute(['groups' => $this->newGroup->handle], 'groups', $feedMapping);

        $this->assertContains($this->newGroup->id, $groupIds);
    }

    public function testNoMatchLeavesExistingGroupsUntouched(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'groups'];

        $groupIds = $this->service->parseAttribute(['groups' => $this->newGroup->name . '-nonexistent'], 'groups', $feedMapping);

        $this->assertContains($this->existingGroup->id, $groupIds);
        $this->assertNotContains($this->newGroup->id, $groupIds);
    }

    public function testRemoveFromExisting(): void
    {
        $feedMapping = [
            'attribute' => true,
            'node' => 'groups',
            'options' => ['removeFromExisting' => true],
        ];

        $groupIds = $this->service->parseAttribute(['groups' => (string)$this->newGroup->id], 'groups', $feedMapping);

        $this->assertSame([(string)$this->newGroup->id], array_values($groupIds));
    }

    public function testParseStatus(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'status'];

        // parseStatus() just records the value onto $this->status for `afterSave()` to act on
        // later - it doesn't return anything itself.
        $this->service->parseAttribute(['status' => UserElement::STATUS_SUSPENDED], 'status', $feedMapping);

        $this->assertSame(UserElement::STATUS_SUSPENDED, $this->service->status);
    }

    public function testParsePreferredLocale(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'locale'];
        $validLocale = Craft::$app->getI18n()->getAppLocaleIds()[0];

        $this->assertSame(
            $validLocale,
            $this->service->parseAttribute(['locale' => $validLocale], 'preferredLocale', $feedMapping),
        );

        $this->assertNull(
            $this->service->parseAttribute(['locale' => 'not-a-real-locale'], 'preferredLocale', $feedMapping),
        );

        // An explicitly empty value is passed straight through (rather than being rejected as
        // "not a valid locale"), so it can be used to clear the user's preferred locale.
        $this->assertSame(
            '',
            $this->service->parseAttribute(['locale' => ''], 'preferredLocale', $feedMapping),
        );
    }
}
