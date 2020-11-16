<?php

namespace craft\feedme\base;

use craft\base\ComponentInterface;

interface FieldInterface extends ComponentInterface
{
    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function getMappingTemplate();

    /**
     * @return mixed
     */
    public function parseField();
}
