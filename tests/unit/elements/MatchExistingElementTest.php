<?php

use Carbon\Carbon;
use craft\base\ElementInterface as CraftElementInterface;
use craft\feedme\base\Element;
use craft\fields\data\ColorData;

/**
 * Covers the value normalization in Element::matchExistingElement().
 *
 * Fields that are allowed to be unique identifiers don't all parse to strings —
 * Number fields give ints/floats, Lightswitch fields give bools, and Date fields
 * give DateTime or Carbon instances. Those all need to survive matching. Arrays
 * and other objects can't be matched against and should throw.
 */
class MatchExistingElementTest extends \Codeception\Test\Unit
{
    protected $tester;

    /**
     * Runs a value through matchExistingElement() and returns the criteria it built.
     */
    private function criteriaFor($feedValue, string $handle = 'externalId'): array
    {
        $element = new class extends Element {
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

                return new class {
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

    public function testMatchesOnNumbers()
    {
        // A Number field used as an external ID — the common case.
        $this->assertEquals(['externalId' => '999624'], $this->criteriaFor(999624));
        $this->assertEquals(['externalId' => '-5'], $this->criteriaFor(-5));
        $this->assertEquals(['externalId' => '12.5'], $this->criteriaFor(12.5));
    }

    public function testMatchesOnBooleans()
    {
        $this->assertEquals(['externalId' => '1'], $this->criteriaFor(true));
        $this->assertEquals(['externalId' => '0'], $this->criteriaFor(false));
    }

    public function testMatchesOnDates()
    {
        $expected = ['externalId' => '2018-01-01 10:00:00'];

        $this->assertEquals($expected, $this->criteriaFor(new DateTime('2018-01-01 10:00:00')));
        $this->assertEquals($expected, $this->criteriaFor(new DateTimeImmutable('2018-01-01 10:00:00')));
        $this->assertEquals($expected, $this->criteriaFor(new Carbon('2018-01-01 10:00:00')));
    }

    public function testMatchesOnStringables()
    {
        $this->assertEquals(['externalId' => '#ff0000'], $this->criteriaFor(new ColorData('#ff0000')));
    }

    public function testMatchesOnStringsAndStillEscapes()
    {
        $this->assertEquals(['externalId' => 'abc'], $this->criteriaFor('abc'));
        $this->assertEquals(['externalId' => 'a\,b\*c'], $this->criteriaFor('a,b*c'));
        $this->assertEquals(['externalId' => '\>=5'], $this->criteriaFor('>=5'));
    }

    public function testSkipsEmptyValues()
    {
        $this->assertEquals([], $this->criteriaFor(null));
        $this->assertEquals([], $this->criteriaFor(''));
    }

    public function testThrowsOnArrays()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot match on a value of type array for `externalId`.');

        // What a Link field parses to.
        $this->criteriaFor(['type' => 'url', 'value' => 'https://craftcms.com']);
    }

    public function testThrowsOnNonStringableObjects()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot match on a value of type stdClass for `externalId`.');

        $this->criteriaFor(new stdClass());
    }
}
