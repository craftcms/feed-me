<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;
use verbb\feedme\base\ElementInterface;
use verbb\feedme\elements\Asset;
use verbb\feedme\elements\CalenderEvent;
use verbb\feedme\elements\Category;
use verbb\feedme\elements\Comment;
use verbb\feedme\elements\CommerceOrder;
use verbb\feedme\elements\CommerceProduct;
use verbb\feedme\elements\DigitalProduct;
use verbb\feedme\elements\Entry;
use verbb\feedme\elements\Tag;
use verbb\feedme\elements\User;
use verbb\feedme\events\RegisterFeedMeElementsEvent;

use Craft;
use craft\base\Component;
use craft\helpers\Component as ComponentHelper;

use Cake\Utility\Hash;

class Elements extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_FEED_ME_ELEMENTS = 'registerFeedMeElements';


    // Properties
    // =========================================================================

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

    public function getRegisteredElement($handle)
    {
        if (isset($this->_elements[$handle])) {
            return $this->_elements[$handle];
        }
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

        if (FeedMe::$plugin->is(FeedMe::EDITION_PRO)) {
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
        } else {
            $elements = [
                Entry::class,
            ];
        }

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
