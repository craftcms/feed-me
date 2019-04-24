<?php

namespace craft\feedme\services;

use craft\base\Component;
use craft\feedme\base\ElementInterface;
use craft\feedme\elements\Asset;
use craft\feedme\elements\CalenderEvent;
use craft\feedme\elements\Category;
use craft\feedme\elements\Comment;
use craft\feedme\elements\CommerceProduct;
use craft\feedme\elements\DigitalProduct;
use craft\feedme\elements\Entry;
use craft\feedme\elements\Tag;
use craft\feedme\elements\User;
use craft\feedme\events\RegisterFeedMeElementsEvent;
use craft\helpers\Component as ComponentHelper;

class Elements extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_FEED_ME_ELEMENTS = 'registerFeedMeElements';


    // Properties
    // =========================================================================

    /**
     * @var ElementInterface[]
     */
    private $_elements = [];


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        foreach ($this->getRegisteredElements() as $elementClass) {
            $element = $this->createElement($elementClass);

            // Does this element exist in Craft right now?
            if (!class_exists($element::$class)) {
                continue;
            }

            $handle = $element::$class;

            $this->_elements[$handle] = $element;
        }
    }

    /**
     * @param string $handle
     * @return ElementInterface|null
     */
    public function getRegisteredElement($handle)
    {
        if (isset($this->_elements[$handle])) {
            return $this->_elements[$handle];
        }
        return null;
    }

    public function elementsList()
    {
        $list = [];

        foreach ($this->_elements as $handle => $element) {
            $list[$handle] = $element::$name;
        }

        return $list;
    }

    public function getRegisteredElements()
    {
        if (count($this->_elements)) {
            return $this->_elements;
        }

        $elements = [
            Asset::class,
            Category::class,
            // CommerceOrder::class,
            CommerceProduct::class,
            Entry::class,
            Tag::class,
            User::class,

            // Third-party
            CalenderEvent::class,
            Comment::class,
            DigitalProduct::class,
        ];

        $event = new RegisterFeedMeElementsEvent([
            'elements' => $elements,
        ]);

        $this->trigger(self::EVENT_REGISTER_FEED_ME_ELEMENTS, $event);

        return $event->elements;
    }

    public function createElement($config)
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $element = ComponentHelper::createComponent($config, ElementInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $element = new MissingDataType($config);
        }

        return $element;
    }
}
