<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\User as UserElement;
use craft\feedme\base\Element as FeedMeElement;
use craft\feedme\tests\Helpers\ElementFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Shared `parseAttribute('author', ...)` coverage for element types (CalenderEvent, Entry) that
 * resolve an author lookup the same way: match by email/username/id, or null on no match/empty
 * value. Consuming classes provide the service under test, the author subject, the target
 * attribute name, its expected result shape, and its feed mapping.
 */
trait MatchesAuthorByTypeTests
{
    abstract protected function authorMatchService(): FeedMeElement;

    abstract protected function authorSubject(): UserElement;

    abstract protected function authorMatchAttribute(): string;

    abstract protected function authorMatchExpected(UserElement $author): mixed;

    abstract protected function authorMatchFeedMapping(string $matchType): array;

    public static function authorMatchTypeProvider(): array
    {
        return [
            'email' => ['email'],
            'username' => ['username'],
            'id' => ['id'],
        ];
    }

    #[DataProvider('authorMatchTypeProvider')]
    public function testAuthorMatchesByType(string $matchType): void
    {
        $author = $this->authorSubject();
        $service = $this->authorMatchService();
        $attribute = $this->authorMatchAttribute();
        $feedMapping = $this->authorMatchFeedMapping($matchType);

        $value = $matchType === 'id' ? (string)$author->id : $author->{$matchType};
        $feedData = ['author' => $value];
        $this->assertEquals($this->authorMatchExpected($author), $service->parseAttribute($feedData, $attribute, $feedMapping));

        // Check invalid match.
        $invalidValue = $matchType === 'id'
            ? $author->id + ElementFactory::NONEXISTENT_ID_OFFSET
            : 'nonexistent-' . $value;
        $feedData = ['author' => $invalidValue];
        $this->assertNull($service->parseAttribute($feedData, $attribute, $feedMapping));
    }

    public function testAuthorEmpty(): void
    {
        $feedData = ['author' => ''];
        $feedMapping = $this->authorMatchFeedMapping('id');

        $this->assertNull($this->authorMatchService()->parseAttribute($feedData, $this->authorMatchAttribute(), $feedMapping));
    }
}
