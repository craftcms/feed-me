<?php

namespace craft\feedme\tests\Feature\Fields;

use Craft;
use craft\elements\User as UserElement;
use craft\feedme\fields\Users;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\feedme\tests\TestCase;
use craft\fields\Users as UsersField;
use PHPUnit\Framework\Attributes\DataProvider;

class UsersFieldTest extends TestCase
{
    private Users $service;

    private UserElement $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = ElementFactory::createUser();

        // `sources: '*'` means "search every group" - avoids having to resolve a specific
        // source UID for the field settings.
        $this->service = FieldServiceFactory::create(Users::class, new UsersField(['sources' => '*']));
    }

    public static function matchTypeProvider(): array
    {
        return [
            'email' => ['email'],
            'username' => ['username'],
            'id' => ['id'],
        ];
    }

    #[DataProvider('matchTypeProvider')]
    public function testMatchesByType(string $matchType): void
    {
        $this->service->fieldInfo = ['node' => 'author', 'options' => ['match' => $matchType]];
        $this->service->feedData = ['author' => $matchType === 'id' ? (string)$this->user->id : $this->user->{$matchType}];

        $this->assertSame([$this->user->id], $this->service->parseField());
    }

    public function testNoMatchReturnsNull(): void
    {
        $this->service->fieldInfo = ['node' => 'author', 'options' => ['match' => 'email']];
        $this->service->feedData = ['author' => 'nonexistent-' . $this->user->email];

        // No matches found and nothing to fall back to - relation gets cleared, not left alone.
        $this->assertNull($this->service->parseField());
    }

    public function testEmptyValueReturnsEmptyArray(): void
    {
        $this->service->fieldInfo = ['node' => 'author', 'options' => ['match' => 'email']];
        $this->service->feedData = ['author' => ''];

        $this->assertSame([], $this->service->parseField());
    }

    public function testUsedefaultReturnsDefaultArrayAsIs(): void
    {
        // 'usedefault' is Feed Me's "always use the configured default, regardless of feed
        // data" sentinel node value - the default is assumed to already be an array of ids,
        // used as-is without a DB lookup.
        $this->service->fieldInfo = [
            'node' => 'usedefault',
            'default' => (string)$this->user->id,
            'options' => ['match' => 'email'],
        ];
        $this->service->feedData = ['author' => 'irrelevant'];

        $this->assertSame([(string)$this->user->id], $this->service->parseField());
    }

    public function testGroupScoping(): void
    {
        $group = ElementFactory::createUserGroup();
        $otherUser = ElementFactory::createUser();
        Craft::$app->getUsers()->assignUserToGroups($this->user->id, [$group->id]);

        $this->service->field = new UsersField(['sources' => ['group:' . $group->uid]]);
        $this->service->fieldInfo = ['node' => 'author', 'options' => ['match' => 'email']];

        // In the group - matches.
        $this->service->feedData = ['author' => $this->user->email];
        $this->assertSame([$this->user->id], $this->service->parseField());

        // Not in the group - doesn't match, even though the user exists.
        $this->service->feedData = ['author' => $otherUser->email];
        $this->assertNull($this->service->parseField());
    }

    public function testCreateOnNoMatch(): void
    {
        $newEmail = 'created-' . $this->user->email;

        $this->service->fieldInfo = [
            'node' => 'author',
            'options' => ['match' => 'email', 'create' => true],
        ];
        $this->service->feedData = ['author' => $newEmail];

        $ids = $this->service->parseField();

        $this->assertNotNull($ids);
        $created = UserElement::find()->id($ids)->status(null)->one();
        $this->assertSame($newEmail, $created->email);
    }
}
