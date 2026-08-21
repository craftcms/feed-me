<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests\Helpers;

use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\models\UserGroup;
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

    public static function createCategory(array $attributes = []): Category
    {
        $group = Craft::$app->getCategories()->getAllGroups()[0] ?? null;
        if (!$group) {
            throw new Exception('No category groups are available to create a test category in.');
        }

        $category = new Category();
        $category->groupId = $group->id;
        $category->title = self::faker()->sentence(3);

        Craft::configure($category, $attributes);

        if (!Craft::$app->getElements()->saveElement($category, true, true, true)) {
            throw new Exception('Could not create test category: ' . implode(', ', $category->getErrorSummary(true)));
        }

        return $category;
    }

    public static function createUserGroup(array $attributes = []): UserGroup
    {
        $group = new UserGroup();
        $group->name = self::faker()->unique()->word();
        $group->handle = self::faker()->unique()->word();

        Craft::configure($group, $attributes);

        if (!Craft::$app->getUserGroups()->saveGroup($group)) {
            throw new Exception('Could not create test user group: ' . json_encode($group->getErrors()));
        }

        return $group;
    }

    private static function faker(): Generator
    {
        return self::$faker ??= Factory::create();
    }
}
