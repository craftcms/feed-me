<?php

namespace craft\feedme\services;

use craft\base\Component;
use craft\feedme\Plugin;
use craft\feedme\records\FeedRecord;
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

        if(!file_exists($this->defaultFileName)) mkdir(dirname($this->defaultFileName), 0777, true);

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
            Plugin::getInstance()->getFeeds()->deleteFeedById($feed['id']);

            $record = new FeedRecord();
            $record->setAttributes($feed, false);
            $record->save(false);
            $success = (bool) $record->id;
            if ($success) {
                $result->success_feeds[] = $record;
            } else {
                $result->failed_feeds[] = $record;
            }
        }

        if(count($result->failed_feeds) > 0) {
            $result->success = false;
        }

        return $result;
    }
}
