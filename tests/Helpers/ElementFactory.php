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
use craft\fieldlayoutelements\entries\EntryTitleField;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use craft\models\Section_SiteSettings;
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
    /**
     * An offset large enough that adding it to any real element/user ID created in these tests
     * is guaranteed not to collide with an existing row.
     */
    public const NONEXISTENT_ID_OFFSET = 999999;

    private static ?Generator $faker = null;

    public static function createEntry(array $attributes = []): Entry
    {
        // Always create a fresh section rather than reusing `getAllSections()[0]` - Craft's
        // Entries service caches sections in memory for the lifetime of the app, but each test
        // only rolls back its own DB transaction, so a cached section from an earlier test would
        // point at rows that no longer exist.
        $section = self::createSection();
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
        // Always create a fresh category group rather than reusing `getAllGroups()[0]` - same
        // cross-test staleness risk as createEntry()'s section lookup above.
        $group = self::createCategoryGroup();

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
        $group->handle = self::uniqueHandle();

        Craft::configure($group, $attributes);

        if (!Craft::$app->getUserGroups()->saveGroup($group)) {
            throw new Exception('Could not create test user group: ' . json_encode($group->getErrors()));
        }

        return $group;
    }

    public static function createSection(array $attributes = []): Section
    {
        $entryType = new EntryType();
        $entryType->name = self::faker()->unique()->word();
        $entryType->handle = self::uniqueHandle();

        // saveEntryType() derives `hasTitleField` from whether the field layout includes a
        // Title element, not from the property itself - without this, entry titles saved via
        // ElementFactory::createEntry() are silently ignored.
        $fieldLayout = new FieldLayout(['type' => Entry::class]);
        $tab = new FieldLayoutTab(['layout' => $fieldLayout]);
        $tab->setElements([new EntryTitleField()]);
        $fieldLayout->setTabs([$tab]);
        $entryType->setFieldLayout($fieldLayout);

        if (!Craft::$app->getEntries()->saveEntryType($entryType)) {
            throw new Exception('Could not create test entry type: ' . implode(', ', $entryType->getErrorSummary(true)));
        }

        $siteId = Craft::$app->getSites()->getPrimarySite()->id;

        $section = new Section([
            'name' => self::faker()->unique()->word(),
            'handle' => self::uniqueHandle(),
            'type' => Section::TYPE_CHANNEL,
        ]);
        $section->setEntryTypes([$entryType]);
        $section->setSiteSettings([
            $siteId => new Section_SiteSettings(['siteId' => $siteId]),
        ]);

        Craft::configure($section, $attributes);

        if (!Craft::$app->getEntries()->saveSection($section)) {
            throw new Exception('Could not create test section: ' . implode(', ', $section->getErrorSummary(true)));
        }

        return $section;
    }

    public static function createCategoryGroup(array $attributes = []): CategoryGroup
    {
        $group = new CategoryGroup([
            'name' => self::faker()->unique()->word(),
            'handle' => self::uniqueHandle(),
        ]);

        // saveGroup() requires site settings for every site in the install, not just one.
        $siteSettings = [];
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $siteSettings[$site->id] = new CategoryGroup_SiteSettings(['siteId' => $site->id]);
        }
        $group->setSiteSettings($siteSettings);

        Craft::configure($group, $attributes);

        if (!Craft::$app->getCategories()->saveGroup($group)) {
            throw new Exception('Could not create test category group: ' . implode(', ', $group->getErrorSummary(true)));
        }

        // saveGroup() only populates structureId on the DB record, not the passed-in model -
        // re-fetch so callers (e.g. Structures::append()) get a group with structureId set.
        return Craft::$app->getCategories()->getGroupById($group->id);
    }

    /**
     * Resets Faker's `unique()` tracking. The `$faker` Generator is a static singleton that
     * survives the whole test process, but `unique()` only ever grows its "already used" set -
     * without a reset between tests, a long run risks exhausting small pools (like `word()`)
     * and throwing `OverflowException`.
     */
    public static function resetFaker(): void
    {
        self::faker()->unique(true);
    }

    /**
     * A random handle that's guaranteed not to collide with Craft's reserved handle words (e.g.
     * "id", "title") - a plain `faker()->unique()->word()` occasionally generates one of those.
     */
    private static function uniqueHandle(): string
    {
        return self::faker()->unique()->word() . self::faker()->unique()->numberBetween(1, 999999);
    }

    private static function faker(): Generator
    {
        return self::$faker ??= Factory::create();
    }
}
