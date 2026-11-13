<?php

namespace craft\feedme\tests\Unit\Elements;

use Carbon\Carbon;
use craft\base\ElementInterface as CraftElementInterface;
use craft\feedme\base\Element;
use craft\fields\data\ColorData;
use DateTime;
use DateTimeImmutable;
use Exception;
use LogicException;
use stdClass;

/**
 * Covers the value normalization in Element::matchExistingElement().
 *
 * Fields that are allowed to be unique identifiers don't all parse to strings —
 * Number fields give ints/floats, Lightswitch fields give bools, and Date fields
 * give DateTime or Carbon instances. Those all need to survive matching. Arrays
 * and other objects can't be matched against and should throw.
 */

/**
 * Runs a value through matchExistingElement() and returns the criteria it built.
 */
function criteriaFor($feedValue, string $handle = 'externalId'): array
{
    $element = new class() extends Element {
        public array $criteria = [];

        public static function displayName(): string
        {
            return 'Test';
        }

        public function getGroups(): array
        {
            return [];
        }

        public function getGroupsTemplate(): string
        {
            return '';
        }

        public function getColumnTemplate(): string
        {
            return '';
        }

        public function getMappingTemplate(): string
        {
            return '';
        }

        public function setModel($settings): CraftElementInterface
        {
            throw new LogicException('Not used by these tests.');
        }

        public function getQuery($settings, $params = []): mixed
        {
            $this->criteria = $params;

            return new class() {
                public function one()
                {
                    return null;
                }
            };
        }
    };

    $element->matchExistingElement([$handle => $feedValue], [
        'fieldUnique' => [$handle => 1],
        // Skip the "no criteria to match on" guard, so we can assert on skipped values.
        'singleton' => true,
    ]);

    return $element->criteria;
}

it('matches on numbers', function() {
    // A Number field used as an external ID — the common case.
    expect(criteriaFor(999624))->toEqual(['externalId' => '999624']);
    expect(criteriaFor(-5))->toEqual(['externalId' => '-5']);
    expect(criteriaFor(12.5))->toEqual(['externalId' => '12.5']);
});

it('matches on booleans', function() {
    expect(criteriaFor(true))->toEqual(['externalId' => '1']);
    expect(criteriaFor(false))->toEqual(['externalId' => '0']);
});

it('matches on dates', function() {
    $expected = ['externalId' => '2018-01-01 10:00:00'];

    expect(criteriaFor(new DateTime('2018-01-01 10:00:00')))->toEqual($expected);
    expect(criteriaFor(new DateTimeImmutable('2018-01-01 10:00:00')))->toEqual($expected);
    expect(criteriaFor(new Carbon('2018-01-01 10:00:00')))->toEqual($expected);
});

it('matches on stringables', function() {
    expect(criteriaFor(new ColorData('#ff0000')))->toEqual(['externalId' => '#ff0000']);
});

it('matches on strings and still escapes', function() {
    expect(criteriaFor('abc'))->toEqual(['externalId' => 'abc']);
    expect(criteriaFor('a,b*c'))->toEqual(['externalId' => 'a\,b\*c']);
    expect(criteriaFor('>=5'))->toEqual(['externalId' => '\>=5']);
});

it('skips empty values', function() {
    expect(criteriaFor(null))->toEqual([]);
    expect(criteriaFor(''))->toEqual([]);
});

it('throws on arrays', function() {
    // What a Link field parses to.
    criteriaFor(['type' => 'url', 'value' => 'https://craftcms.com']);
})->throws(Exception::class, 'Cannot match on a value of type array for `externalId`.');

it('throws on non stringable objects', function() {
    criteriaFor(new stdClass());
})->throws(Exception::class, 'Cannot match on a value of type stdClass for `externalId`.');
