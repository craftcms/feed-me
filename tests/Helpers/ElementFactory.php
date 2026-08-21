<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests\Helpers;

use Craft;
use craft\elements\Entry;
use craft\elements\User;
use Faker\Factory;
use Faker\Generator;
use yii\base\Exception;

/**
 * Builds and saves real Craft elements for tests to use, so tests don't need to assume
 * specific rows already exist in the database. Anything created here lives inside the
 * current test's DB transaction (see {@see \craft\feedme\tests\TestCase}), so it's rolled
 * back automatically once the test finishes.
 */
class ElementFactory
{
    private static ?Generator $faker = null;

    public static function createEntry(array $attributes = []): Entry
    {
        $section = Craft::$app->getEntries()->getAllSections()[0] ?? null;
        if (!$section) {
            throw new Exception('No sections are available to create a test entry in.');
        }

        $entryType = $section->getEntryTypes()[0];

        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->typeId = $entryType->id;
        $entry->title = self::faker()->sentence(3);

        Craft::configure($entry, $attributes);

        if (!Craft::$app->getElements()->saveElement($entry, true, true, true)) {
            throw new Exception('Could not create test entry: ' . implode(', ', $entry->getErrorSummary(true)));
        }

        return $entry;
    }

    public static function createUser(array $attributes = []): User
    {
        $user = new User();
        $user->username = self::faker()->unique()->userName();
        $user->email = self::faker()->unique()->safeEmail();
        // Craft 5 uses a single `fullName` field by default (firstName/lastName are only used
        // when the `showFirstAndLastNameFields` config setting is on), so set it directly.
        $user->fullName = self::faker()->name();

        Craft::configure($user, $attributes);

        if (!Craft::$app->getElements()->saveElement($user, true, true, true)) {
            throw new Exception('Could not create test user: ' . implode(', ', $user->getErrorSummary(true)));
        }

        return $user;
    }

    private static function faker(): Generator
    {
        return self::$faker ??= Factory::create();
    }
}
