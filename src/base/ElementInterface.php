<?php

namespace craft\feedme\base;

use craft\base\ComponentInterface;

interface ElementInterface extends ComponentInterface
{
    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function getElementClass(): string;

    /**
     * @return mixed
     */
    public function getGroupsTemplate(): string;

    /**
     * @return mixed
     */
    public function getColumnTemplate(): string;

    /**
     * @return mixed
     */
    public function getMappingTemplate(): string;

    /**
     * @return array
     */
    public function getGroups(): array;

    /**
     * @param $settings
     * @param array $params
     * @return mixed
     */
    public function getQuery($settings, array $params = []): mixed;

    /**
     * @param $settings
     * @return mixed
     */
    public function setModel($settings): \craft\base\ElementInterface;

    /**
     * @param $data
     * @param $settings
     * @return mixed
     */
    public function matchExistingElement($data, $settings): mixed;

    /**
     * @param $elementIds
     * @return mixed
     */
    public function delete($elementIds): mixed;

    /**
     * @param $elementIds
     * @return mixed
     */
    public function disable($elementIds): bool;

    /**
     * @var int[] $elementIds
     */
    public function disableForSite(array $elementIds): bool;

    /**
     * @param $data
     * @param $settings
     * @return mixed
     */
    public function save($data, $settings): mixed;

    /**
     * @param $data
     * @param $settings
     * @return void
     */
    public function afterSave($data, $settings): void;
}
