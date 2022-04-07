<?php

namespace craft\feedme\services;

use Craft;
use craft\base\Component;
use craft\base\ComponentInterface;
use craft\errors\MissingComponentException;
use craft\feedme\base\ElementInterface;
use craft\feedme\elements\Asset;
use craft\feedme\elements\CalenderEvent;
use craft\feedme\elements\Category;
use craft\feedme\elements\CommerceProduct;
use craft\feedme\elements\DigitalProduct;
use craft\feedme\elements\Entry;
use craft\feedme\elements\GlobalSet;
use craft\feedme\elements\Tag;
use craft\feedme\elements\User;
use craft\feedme\events\RegisterFeedMeElementsEvent;
use craft\helpers\Component as ComponentHelper;
use yii\base\InvalidConfigException;

/**
 *
 * @property-read ElementInterface[] $registeredElements
 */
class Elements extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_REGISTER_FEED_ME_ELEMENTS = 'registerFeedMeElements';


    // Properties
    // =========================================================================

    /**
     * @var ElementInterface[]
     */
    private array $_elements = [];


    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        $pluginsService = Craft::$app->getPlugins();

        foreach ($this->getRegisteredElements() as $elementClass) {
            $element = $this->createElement($elementClass);

            // Does this element exist in Craft right now?
            $class = $element->getElementClass();
            if (!class_exists($class)) {
                continue;
            }

            // If it belongs to a plugin, is the plugin enabled?
            $pluginHandle = $pluginsService->getPluginHandleByClass($class);
            if ($pluginHandle !== null && !$pluginsService->isPluginEnabled($pluginHandle)) {
                continue;
            }

            $this->_elements[$class] = $element;
        }
    }

    /**
     * @param string $handle
     * @return ElementInterface|null
     */
    public function getRegisteredElement(string $handle): ?ElementInterface
    {
        return $this->_elements[$handle] ?? null;
    }

    /**
     * @return array
     */
    public function elementsList(): array
    {
        $list = [];

        foreach ($this->_elements as $handle => $element) {
            $list[$handle] = $element::$name;
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getRegisteredElements(): array
    {
        if (count($this->_elements)) {
            return $this->_elements;
        }

        $elements = [
            Asset::class,
            Category::class,
            CommerceProduct::class,
            Entry::class,
            Tag::class,
            User::class,
            GlobalSet::class,

            // Third-party
            CalenderEvent::class,
            DigitalProduct::class,
        ];

        $event = new RegisterFeedMeElementsEvent([
            'elements' => $elements,
        ]);

        $this->trigger(self::EVENT_REGISTER_FEED_ME_ELEMENTS, $event);

        return $event->elements;
    }

    /**
     * @param $config
     * @return ComponentInterface
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function createElement($config): ComponentInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        return ComponentHelper::createComponent($config, ElementInterface::class);
    }
}
