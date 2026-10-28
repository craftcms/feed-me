<?php

namespace craft\feedme\tests\Feature\Fields;

use Craft;
use craft\elements\User as UserElement;
use craft\feedme\fields\Users;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\Users as UsersField;
use function craft\feedme\tests\Helpers\matchesRelatedByType;

beforeEach(function() {
    $this->subject = ElementFactory::createUser();

    // `sources: '*'` means "search every group" - avoids having to resolve a specific
    // source UID for the field settings.
    $this->service = FieldServiceFactory::create(Users::class, new UsersField(['sources' => '*']));
});

matchesRelatedByType(
    matchTypes: [
        'email' => ['email'],
        'username' => ['username'],
        'id' => ['id'],
    ],
    node: 'author',
);

it('returns the default array as-is for usedefault', function() {
    // 'usedefault' is Feed Me's "always use the configured default, regardless of feed
    // data" sentinel node value - the default is assumed to already be an array of ids,
    // used as-is without a DB lookup.
    $this->service->fieldInfo = [
        'node' => 'usedefault',
        'default' => (string)$this->subject->id,
        'options' => ['match' => 'email'],
    ];
    $this->service->feedData = ['author' => 'irrelevant'];

    expect($this->service->parseField())->toBe([(string)$this->subject->id]);
});

it('scopes matches to the field groups', function() {
    $group = ElementFactory::createUserGroup();
    $otherUser = ElementFactory::createUser();
    Craft::$app->getUsers()->assignUserToGroups($this->subject->id, [$group->id]);

    $this->service->field = new UsersField(['sources' => ['group:' . $group->uid]]);
    $this->service->fieldInfo = ['node' => 'author', 'options' => ['match' => 'email']];

    // In the group - matches.
    $this->service->feedData = ['author' => $this->subject->email];
    expect($this->service->parseField())->toBe([$this->subject->id]);

    // Not in the group - doesn't match, even though the user exists.
    $this->service->feedData = ['author' => $otherUser->email];
    expect($this->service->parseField())->toBeNull();
});

it('creates a user when nothing matches', function() {
    $newEmail = 'created-' . $this->subject->email;

    $this->service->fieldInfo = [
        'node' => 'author',
        'options' => ['match' => 'email', 'create' => true],
    ];
    $this->service->feedData = ['author' => $newEmail];

    $ids = $this->service->parseField();

    expect($ids)->not->toBeNull();
    $created = UserElement::find()->id($ids)->status(null)->one();
    expect($created->email)->toBe($newEmail);
});
