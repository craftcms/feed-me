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
    public function getElementClass();

    /**
     * @return mixed
     */
    public function getGroupsTemplate();

    /**
     * @return mixed
     */
    public function getColumnTemplate();

    /**
     * @return mixed
     */
    public function getMappingTemplate();

    /**
     * @return mixed
     */
    public function getGroups();

    /**
     * @param $settings
     * @param array $params
     * @return mixed
     */
    public function getQuery($settings, $params = []);

    /**
     * @param $settings
     * @return mixed
     */
    public function setModel($settings);

    /**
     * @param $data
     * @param $settings
     * @return mixed
     */
    public function matchExistingElement($data, $settings);

    /**
     * @param $elementIds
     * @return mixed
     */
    public function delete($elementIds);

    /**
     * @param $elementIds
     * @return mixed
     */
    public function disable($elementIds);

    /**
     * @var int[] $elementIds
     */
    public function disableForSite($elementIds);

    /**
     * @param $data
     * @param $settings
     * @return mixed
     */
    public function save($data, $settings);

    /**
     * @param $data
     * @param $settings
     * @return mixed
     */
    public function afterSave($data, $settings);
}
