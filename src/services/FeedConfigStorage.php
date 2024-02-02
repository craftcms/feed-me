<?php

namespace craft\feedme\services;

use craft\base\Component;
use craft\feedme\Plugin;
use Symfony\Component\Yaml\Yaml;

/**
 *
 *
 */
class FeedConfigStorage extends Component
{
    /**
     * @var string $defaultFileName The default file name for the feeds configuration.
     */
    protected $defaultFileName = "config/feed-me/feeds.yaml";

    // Public Methods
    // =========================================================================

    /**
     * Writes the feeds data to a YAML file.
     *
     * @return bool True if the data was successfully written to the file, false otherwise.
     * @throws \yii\base\InvalidConfigException If the plugin instance is not properly configured.
     */
    public function write(): bool {
        $feeds = array_map(
            function($feed) { return ["id" => $feed->id] + $feed->getRecordAttributes(); },
            Plugin::getInstance()->getFeeds()->getFeeds()
        );

        $fileName = $this->defaultFileName;
        $fileContents = Yaml::dump($feeds, JSON_PRETTY_PRINT);

        mkdir(dirname($this->defaultFileName), 0777, true);

        return file_put_contents($fileName, $fileContents) !== false;
    }

    /**
     * Reads feed data from a YAML file and processes each feed.
     *
     * @return \stdClass The result object containing success flag, successful feeds, and failed feeds.
     * @throws \yii\base\InvalidConfigException If the configuration is invalid.
     */
    public function read(): \stdClass {
        $result = (object)["success" => true, "success_feeds" => [], "failed_feeds" => []];
        $fileName = $this->defaultFileName;
        $feeds = Yaml::parse(file_get_contents($fileName));
        foreach ($feeds as $feed) {
            $model = Plugin::getInstance()->getFeeds()->getFeedById($feed['id']);
            if ($model) {
                $model->setAttributes($feed);
                $success = Plugin::getInstance()->getFeeds()->saveFeed($model);
                if ($success) {
                    $result->success_feeds[] = $model;
                } else {
                    $result->failed_feeds[] = $model;
                }
            }
        }

        if(count($result->failed_feeds) > 0) {
            $result->success = false;
        }

        return $result;
    }
}
