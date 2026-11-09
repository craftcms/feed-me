<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests\Helpers;

use Closure;

/**
 * Shared test groups registered by the test files that call them.
 *
 * Each function registers its tests via `it()`, so it must be called at file level of a test file.
 * The registered tests read the objects under test from properties the calling file's
 * `beforeEach()` sets on the test case:
 *
 * - `$this->service` - the Feed Me element/field service under test (all functions)
 * - `$this->author` - the author user element ({@see matchesAuthorByType()})
 * - `$this->parent` - the parent element ({@see parsesParentAttribute()})
 * - `$this->subject` - the related element expected to be matched ({@see matchesRelatedByType()})
 */

/**
 * Registers shared `parseAttribute('author', ...)` coverage for element types (CalenderEvent, Entry)
 * that resolve an author lookup the same way: match by email/username/id, or null on no match/empty
 * value. The calling file provides the target attribute name, its expected result shape, and its
 * feed mapping; the service and author are read from `$this->service` and `$this->author`.
 *
 * @param string $attribute
 * @param Closure $expected
 * @param Closure $feedMapping
 * @return void
 */
function matchesAuthorByType(string $attribute, Closure $expected, Closure $feedMapping): void
{
    it('matches the author by type', function(string $matchType) use ($attribute, $expected, $feedMapping) {
        $author = $this->author;
        $mapping = $feedMapping($matchType);

        $value = $matchType === 'id' ? (string)$author->id : $author->{$matchType};
        $feedData = ['author' => $value];
        expect($this->service->parseAttribute($feedData, $attribute, $mapping))->toEqual($expected($author));

        // Check invalid match.
        $invalidValue = $matchType === 'id'
            ? $author->id + ElementFactory::NONEXISTENT_ID_OFFSET
            : 'nonexistent-' . $value;
        $feedData = ['author' => $invalidValue];
        expect($this->service->parseAttribute($feedData, $attribute, $mapping))->toBeNull();
    })->with([
        'email' => ['email'],
        'username' => ['username'],
        'id' => ['id'],
    ]);

    it('returns null for an empty author', function() use ($attribute, $feedMapping) {
        $feedData = ['author' => ''];

        expect($this->service->parseAttribute($feedData, $attribute, $feedMapping('id')))->toBeNull();
    });
}

/**
 * Registers shared `parseAttribute('parent', ...)` coverage for element types (Category, Entry) that
 * resolve their `parent` mapping the same way: match by title/id/slug, or fall back to a configured
 * default. The service and parent element are read from `$this->service` and `$this->parent`.
 *
 * @return void
 */
function parsesParentAttribute(): void
{
    it('matches the parent by type', function(string $matchType) {
        $parent = $this->parent;

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => $matchType],
        ];

        $value = $matchType === 'id' ? (string)$parent->id : $parent->{$matchType};
        $feedData = ['parent' => $value];
        expect($this->service->parseAttribute($feedData, 'parent', $feedMapping))->toEqual($parent->id);

        // Check invalid match.
        $invalidValue = $matchType === 'id'
            ? $parent->id + ElementFactory::NONEXISTENT_ID_OFFSET
            : $value . '-nonexistent';
        $feedData = ['parent' => $invalidValue];
        expect($this->service->parseAttribute($feedData, 'parent', $feedMapping))->toBeNull();
    })->with([
        'title' => ['title'],
        'id' => ['id'],
        'slug' => ['slug'],
    ]);

    it('falls back to the parent default', function() {
        $parent = $this->parent;

        $feedData = ['parent' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => (string)$parent->id,
            'options' => ['match' => 'title'],
        ];
        expect($this->service->parseAttribute($feedData, 'parent', $feedMapping))->toEqual($parent->id);

        $feedMapping['default'] = '';
        expect($this->service->parseAttribute($feedData, 'parent', $feedMapping))->toBeNull();
    });
}

/**
 * Registers shared `parseField()` coverage for relational field services (Users, Entries) that
 * resolve a match the same way: match by a configured attribute, or null/empty-array on no
 * match/empty value. The calling file provides its own match types, since the supported ones
 * differ per field (e.g. email/username/id vs title/id), and the feed node name; the field service
 * and matched subject are read from `$this->service` and `$this->subject`.
 *
 * @param array $matchTypes
 * @param string $node
 * @return void
 */
function matchesRelatedByType(array $matchTypes, string $node): void
{
    it('matches by type', function(string $matchType) use ($node) {
        $subject = $this->subject;

        $this->service->fieldInfo = ['node' => $node, 'options' => ['match' => $matchType]];
        $this->service->feedData = [$node => $matchType === 'id' ? (string)$subject->id : $subject->{$matchType}];

        expect($this->service->parseField())->toBe([$subject->id]);
    })->with($matchTypes);

    it('returns null when nothing matches', function() use ($node) {
        $this->service->fieldInfo = ['node' => $node, 'options' => ['match' => 'id']];
        $this->service->feedData = [$node => $this->subject->id + ElementFactory::NONEXISTENT_ID_OFFSET];

        // No matches found and nothing to fall back to - relation gets cleared, not left alone.
        expect($this->service->parseField())->toBeNull();
    });

    it('returns an empty array for an empty value', function() use ($node) {
        $this->service->fieldInfo = ['node' => $node, 'options' => ['match' => 'id']];
        $this->service->feedData = [$node => ''];

        expect($this->service->parseField())->toBe([]);
    });
}
