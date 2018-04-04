<?php
namespace verbb\feedme\web\twig;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

use Cake\Utility\Hash;

class Extension extends Twig_Extension
{
    // Public Methods
    // =========================================================================

    public function getName(): string
    {
        return 'Hash - Get';
    }

    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('hash_get', [$this, 'hashGet']),
        ];
    }

    public function hashGet($array, $value)
    {
        if (is_array($array)) {
            return Hash::get($array, $value);
        }
    }
}
