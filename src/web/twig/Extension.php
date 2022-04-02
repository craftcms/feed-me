<?php

namespace craft\feedme\web\twig;

use Cake\Utility\Hash;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
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
            new TwigFunction('hash_get', [$this, 'hashGet']),
        ];
    }

    public function hashGet($array, $value)
    {
        if (is_array($array)) {
            return Hash::get($array, $value);
        }

        return null;
    }
}
