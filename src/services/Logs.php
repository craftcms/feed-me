<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\FileHelper;

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

    public function log(string $message, $method)
    {
        $dateTime = new \DateTime();
        $type = explode('::', $method)[1];

        $options = json_encode([
            'date' => $dateTime->format('Y-m-d H:i:s'),
            'message' => $message,
            'type' => $type,
        ]);

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

                    if (isset($json['date'])) {
                        $json['date'] = \DateTime::createFromFormat('Y-m-d H:i:s', $json['date']);
                    }

                    $logEntries[] = $json;
                }
            }

            // Resort log entries: latest entries first
            $logEntries = array_reverse($logEntries);

            // Kill off any blank lines
            $logEntries = array_values(array_filter($logEntries));
        }

        return $logEntries;
    }
    
}