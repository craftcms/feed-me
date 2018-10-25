<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;

class Logs extends Component
{
    // Properties
    // =========================================================================

    private $_logFile;


    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->_logFile = Craft::$app->path->getLogPath() . '/feedme.log';
    }

    public function log($method, $message, $params = [], $options = [])
    {
        $dateTime = new \DateTime();
        $type = explode('::', $method)[1];
        $message = Craft::t('feed-me', $message, $params);

        // Always prepend the feed we're dealing with
        if (FeedMe::$feedName) {
            $message = FeedMe::$feedName . ': ' . $message;
        }

        $options = array_merge([
            'date' => $dateTime->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
        ], $options);

        // If we're not explicitly sending a key for logging, check if we've started a feed.
        // If we have, our $stepKey variable will have a value and can use it here.
        if (!isset($options['key']) && FeedMe::$stepKey) {
            $options['key'] = FeedMe::$stepKey;
        }

        $options = json_encode($options);

        $fp = fopen($this->_logFile, 'ab');
        fwrite($fp, $options . PHP_EOL);
        fclose($fp);
    }

    public function clear()
    {
        if (@file_exists($this->_logFile)) {
            FileHelper::unlink($this->_logFile);
        }
    }

    public function getLogEntries(): array
    {
        $logEntries = [];

        App::maxPowerCaptain();

        if (@file_exists(Craft::$app->path->getLogPath())) {
            $logEntries = [];

            if (@file_exists($this->_logFile)) {
                // Split the log file's contents up into arrays where every line is a new item
                $contents = @file_get_contents($this->_logFile);
                $lines = explode("\n", $contents);

                foreach ($lines as $line) {
                    $json = json_decode($line, true);

                    if (!$json) {
                        continue;
                    }

                    if (isset($json['date'])) {
                        $json['date'] = \DateTime::createFromFormat('Y-m-d H:i:s', $json['date'])->format('Y-m-d H:i:s');
                    }

                    // Backward compatiblity
                    if (isset($json['key'])) {
                        $key = $json['key'];
                    } else {
                        $key = count($logEntries);
                    }

                    if (isset($logEntries[$key])) {
                        $logEntries[$key]['items'][] = $json;
                    } else {
                        $logEntries[$key] = $json;
                    }
                }
            }

            // Resort log entries: latest entries first
            $logEntries = array_reverse($logEntries);
        }

        return $logEntries;
    }
    
}