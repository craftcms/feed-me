<?php

namespace craft\feedme\base;

use craft\base\ComponentInterface;

interface FieldInterface extends ComponentInterface
{
    // Public Methods
    // =========================================================================

    public function getMappingTemplate();

    public function parseField();

}
