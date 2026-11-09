<?php

namespace craft\feedme\tests\Feature\Elements;

use Craft;
use craft\elements\Address as AddressElement;
use craft\elements\User as UserElement;
use craft\feedme\elements\User;
use craft\feedme\models\FeedModel;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use craft\helpers\StringHelper;

beforeEach(function() {
    $this->service = new User();
    $this->existingGroup = ElementFactory::createUserGroup();
    $this->newGroup = ElementFactory::createUserGroup();

    // parseGroups() reads $this->service->element->groups to merge/remove against the
    // element's existing group assignments, so it needs a real, saved user already
    // assigned to a group - same as a real import would have.
    $this->element = ElementFactory::createUser();
    Craft::$app->getUsers()->assignUserToGroups($this->element->id, [$this->existingGroup->id]);
    $this->service->element = UserElement::find()->id($this->element->id)->status(null)->one();
});

it('matches groups by type', function(string $matchType) {
    $feedMapping = ['attribute' => true, 'node' => 'groups'];

    $value = $matchType === 'id' ? (string)$this->newGroup->id : $this->newGroup->{$matchType};
    $groupIds = $this->service->parseAttribute(['groups' => $value], 'groups', $feedMapping);

    // Numeric matches are passed straight through without casting, so this comes back as a
    // numeric string rather than an int - toContainEqual compares loosely.
    expect($groupIds)->toContainEqual($this->newGroup->id);
    // Existing group assignments are kept unless `removeFromExisting` is set.
    expect($groupIds)->toContainEqual($this->existingGroup->id);
})->with([
    'id' => ['id'],
    'name' => ['name'],
    'handle' => ['handle'],
]);

it('leaves existing groups untouched when nothing matches', function() {
    $feedMapping = ['attribute' => true, 'node' => 'groups'];

    $groupIds = $this->service->parseAttribute(['groups' => $this->newGroup->name . '-nonexistent'], 'groups', $feedMapping);

    expect($groupIds)->toContain($this->existingGroup->id);
    expect($groupIds)->not->toContain($this->newGroup->id);
});

it('removes from existing groups', function() {
    $feedMapping = [
        'attribute' => true,
        'node' => 'groups',
        'options' => ['removeFromExisting' => true],
    ];

    $groupIds = $this->service->parseAttribute(['groups' => (string)$this->newGroup->id], 'groups', $feedMapping);

    expect(array_values($groupIds))->toBe([(string)$this->newGroup->id]);
});

it('parses status', function() {
    $feedMapping = ['attribute' => true, 'node' => 'status'];

    // parseStatus() just records the value onto $this->status for `afterSave()` to act on
    // later - it doesn't return anything itself.
    $this->service->parseAttribute(['status' => UserElement::STATUS_SUSPENDED], 'status', $feedMapping);

    expect($this->service->status)->toBe(UserElement::STATUS_SUSPENDED);
});

it('parses preferred locale', function() {
    $feedMapping = ['attribute' => true, 'node' => 'locale'];
    $validLocale = Craft::$app->getI18n()->getAppLocaleIds()[0];

    expect($this->service->parseAttribute(['locale' => $validLocale], 'preferredLocale', $feedMapping))
        ->toBe($validLocale);

    expect($this->service->parseAttribute(['locale' => 'not-a-real-locale'], 'preferredLocale', $feedMapping))
        ->toBeNull();

    // An explicitly empty value is passed straight through (rather than being rejected as
    // "not a valid locale"), so it can be used to clear the user's preferred locale.
    expect($this->service->parseAttribute(['locale' => ''], 'preferredLocale', $feedMapping))
        ->toBe('');
});

it('imports custom fields on the user addresses', function() {
    // https://github.com/craftcms/feed-me/issues/1729 - the user's feed wasn't passed down to the
    // addresses, so their custom fields couldn't be saved.
    $fieldsService = Craft::$app->getFields();
    $field = new PlainText(['name' => 'Address note', 'handle' => 'addressNote' . StringHelper::randomString(6)]);
    $fieldsService->saveField($field);

    $layout = Craft::$app->getAddresses()->getFieldLayout();
    $tab = $layout->getTabs()[0];
    $tab->setElements([...$tab->getElements(), new CustomField($field)]);

    $this->service->feed = new FeedModel(['setEmptyValues' => false, 'updateSearchIndexes' => true]);

    try {
        $fieldsService->saveLayout($layout);

        $addresses = $this->service->parseAttribute([
            'addresses/0/label' => 'Home',
            'addresses/0/line1' => '1 Main Street',
            'addresses/0/note' => 'Ring twice',
        ], 'addresses', [
            'nativeFields' => [
                'title' => ['node' => 'addresses/label'],
                'addressLine1' => ['node' => 'addresses/line1'],
            ],
            'fields' => [
                $field->handle => ['node' => 'addresses/note'],
            ],
        ]);

        expect($addresses)->toHaveCount(1);

        $address = AddressElement::find()->id($addresses->first()->id)->one();
        expect($address->addressLine1)->toBe('1 Main Street');
        expect($address->getFieldValue($field->handle))->toBe('Ring twice');
    } finally {
        // Fields and layouts are cached in memory and the field is also in the project config, neither
        // of which gets rolled back with the DB transaction.
        $fieldsService->deleteLayout($layout);
        $fieldsService->deleteField($field);
    }
});
