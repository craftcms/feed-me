<?php

namespace craft\feedme\services;

use Craft;
use craft\base\Component;
use craft\feedme\Plugin;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use DateTime;
use Exception;

class Logs extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public bool $enableRotation = true;

    /**
     * @var int
     */
    public int $maxFileSize = 6656; // 6.5MB limit for support

    /**
     * @var int
     */
    public int $maxLogFiles = 20;

    /**
     * @var
     */
    public mixed $fileMode = null;

    /**
     * @var int
     */
    public int $dirMode = 0775;

    /**
     * @var bool
     */
    public bool $rotateByCopy = true;

    /**
     * @var
     */
    public mixed $logFile = null;


    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $this->logFile = Craft::$app->path->getLogPath() . '/feedme.log';
    }

    /**
     * @param $method
     * @param $message
     * @param array $params
     * @param array $options
     * @throws Exception
     */
    public function log($method, $message, array $params = [], array $options = []): void
    {
        $dateTime = new DateTime();
        $type = explode('::', $method)[1];
        $message = Craft::t('feed-me', $message, $params);

        // Make sure to check if we should log anything
        if (!$this->_canLog($type)) {
            return;
        }

        // Always prepend the feed we're dealing with
        if (Plugin::$feedName) {
            $message = Plugin::$feedName . ': ' . $message;
        }

        $options = array_merge([
            'date' => $dateTime->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
        ], $options);

        // If we're not explicitly sending a key for logging, check if we've started a feed.
        // If we have, our $stepKey variable will have a value and can use it here.
        if (!isset($options['key']) && Plugin::$stepKey) {
            $options['key'] = Plugin::$stepKey;
        }

        $options = Json::encode($options);

        $this->_export($options . PHP_EOL);
    }

    /**
     *
     */
    public function clear(): void
    {
        $this->_clearLogFile($this->logFile);
    }

    /**
     * @param null $type
     * @return array
     * @throws \yii\base\Exception
     */
    public function getLogEntries($type = null): array
    {
        $logEntries = [];

        App::maxPowerCaptain();

        if (@file_exists(Craft::$app->path->getLogPath())) {
            if (@file_exists($this->logFile)) {
                // Split the log file's contents up into arrays where every line is a new item
                $contents = @file_get_contents($this->logFile);
                $lines = explode("\n", $contents);

                foreach ($lines as $line) {
                    $json = Json::decode($line);

                    if (!$json) {
                        continue;
                    }

                    if ($type && $json['type'] !== $type) {
                        continue;
                    }

                    if (isset($json['date'])) {
                        $json['date'] = DateTime::createFromFormat('Y-m-d H:i:s', $json['date'])->format('Y-m-d H:i:s');
                    }

                    // Backward compatibility
                    $key = $json['key'] ?? count($logEntries);

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

    // Private Methods
    // =========================================================================

    /**
     * @param $type
     * @return bool
     */
    private function _canLog($type): bool
    {
        $logging = Plugin::$plugin->service->getConfig('logging');

        // If logging set to false, don't log anything
        if ($logging === false) {
            return false;
        }

        if ($type === 'info' && $logging === 'error') {
            return false;
        }

        return true;
    }

    /**
     * @param $text
     * @throws \yii\base\Exception
     */
    private function _export($text): void
    {
        $logPath = dirname($this->logFile);
        FileHelper::createDirectory($logPath, $this->dirMode, true);

        if (($fp = @fopen($this->logFile, 'ab')) === false) {
            throw new Exception("Unable to append to log file: {$this->logFile}");
        }
        @flock($fp, LOCK_EX);
        if ($this->enableRotation) {
            // clear stat cache to ensure getting the real current file size and not a cached one
            // this may result in rotating twice when cached file size is used on subsequent calls
            clearstatcache();
        }
        if ($this->enableRotation && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            $this->_rotateFiles();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            $writeResult = @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
            if ($writeResult === false) {
                $error = error_get_last();
                throw new Exception("Unable to export log through file!: {$error['message']}");
            }
            $textSize = strlen($text);
            if ($writeResult < $textSize) {
                throw new Exception("Unable to export whole log through file! Wrote $writeResult out of $textSize bytes.");
            }
        } else {
            $writeResult = @fwrite($fp, $text);
            if ($writeResult === false) {
                $error = error_get_last();
                throw new Exception("Unable to export log through file!: {$error['message']}");
            }
            $textSize = strlen($text);
            if ($writeResult < $textSize) {
                throw new Exception("Unable to export whole log through file! Wrote $writeResult out of $textSize bytes.");
            }
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    /**
     *
     */
    private function _rotateFiles(): void
    {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i >= 0; --$i) {
            // $i == 0 is the original log file
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            if (is_file($rotateFile)) {
                // suppress errors because it's possible multiple processes enter into this section
                if ($i === $this->maxLogFiles) {
                    @unlink($rotateFile);
                    continue;
                }
                $newFile = $this->logFile . '.' . ($i + 1);
                $this->rotateByCopy ? $this->_rotateByCopy($rotateFile, $newFile) : $this->_rotateByRename($rotateFile, $newFile);
                if ($i === 0) {
                    $this->_clearLogFile($rotateFile);
                }
            }
        }
    }

    /**
     * @param $rotateFile
     */
    private function _clearLogFile($rotateFile): void
    {
        if ($filePointer = @fopen($rotateFile, 'ab')) {
            @ftruncate($filePointer, 0);
            @fclose($filePointer);
        }
    }

    /**
     * @param $rotateFile
     * @param $newFile
     */
    private function _rotateByCopy($rotateFile, $newFile): void
    {
        @copy($rotateFile, $newFile);
        if ($this->fileMode !== null) {
            @chmod($newFile, $this->fileMode);
        }
    }

    /**
     * @param $rotateFile
     * @param $newFile
     */
    private function _rotateByRename($rotateFile, $newFile): void
    {
        @rename($rotateFile, $newFile);
    }
}
