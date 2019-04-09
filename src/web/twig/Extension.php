<?php

namespace craft\feedme\web\twig;

use Cake\Utility\Hash;
use Twig_Extension;
use Twig_SimpleFunction;

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
