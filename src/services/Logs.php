<?php

namespace craft\feedme\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\feedme\Plugin;
use craft\helpers\App;
use craft\helpers\Db;
use Exception;
use Illuminate\Support\Collection;
use samdark\log\PsrMessage;
use yii\base\InvalidArgumentException;
use yii\log\DbTarget;
use yii\log\Logger;

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

    public const LOG_CATEGORY = 'feed-me';
    public const LOG_TABLE = '{{%feedme_logs}}';

    public const LOG_LEVEL_MAP = [
        Logger::LEVEL_ERROR => 'error',
        Logger::LEVEL_WARNING => 'warning',
        Logger::LEVEL_INFO => 'info',
        Logger::LEVEL_TRACE => 'trace',
        Logger::LEVEL_PROFILE => 'profile',
    ];

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        Craft::$app->getLog()->targets['feed-me'] = Craft::createObject([
            'class' => DbTarget::class,
            'logTable' => self::LOG_TABLE,
            'levels' => $this->getLogLevels(),
            'enabled' => $this->isEnabled(),
            'categories' => [self::LOG_CATEGORY],
            'prefix' => static function(array $message) {
                /** @var PsrMessage $psrMessage */
                $psrMessage = unserialize($message[0]);
                return $psrMessage->getContext()['feed'] ?? '';
            },
        ]);
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
        $level = explode('::', $method)[1];
        $message = Craft::t('feed-me', $message, $params);

        $context = [
            'feed' => Plugin::$feedName,
            'key' => $options['key'] ?? Plugin::$stepKey,
        ];

        $psrMessage = new PsrMessage($message, $context);

        Craft::getLogger()->log(
            serialize($psrMessage),
            self::logLevelInt($level),
            self::LOG_CATEGORY,
        );
    }

    /**
     *
     */
    public function clear(): void
    {
        Craft::$app->getDb()->createCommand()
            ->truncateTable(self::LOG_TABLE)
            ->execute();
    }

    /**
     * @param null $type
     * @return array
     * @throws \yii\base\Exception
     */
    public function getLogEntries($type = null): array
    {
        $query = (new Query())
            ->select('*')
            ->where(['category' => self::LOG_CATEGORY])
            ->orderBy(['log_time' => SORT_DESC])
            ->from(self::LOG_TABLE);

        if ($type) {
            $query->andWhere(['level' => self::logLevelInt($type)]);
        }

        $logEntries = $query->collect()->reduce(function(Collection $logs, array $row) {
            $psrMessage = unserialize($row['message']);
            $key = $psrMessage->getContext()['key'] ?? $logs->count();
            $log = [
                'type' => self::logLevelName($row['level']),
                'date' => Db::prepareDateForDb($row['log_time']),
                'message' => $psrMessage->getMessage(),
                'key' => $key,
            ];

            if ($logs->has($key)) {
                $parentLog = $logs->get($key);
                $parentLog['items'][] = $log;
                $logs->put($key, $parentLog);
            } else {
                $logs->put($key, $log);
            }

            return $logs;
        }, Collection::make());

        return $logEntries->all();
    }

    // Private Methods
    // =========================================================================

    private function isEnabled(): bool
    {
        $config = Plugin::$plugin->service->getConfig('logging');

        return App::parseBooleanEnv($config) ?? true;
    }

    private function getLogLevels(): array
    {
        $config = Plugin::$plugin->service->getConfig('logging');

        return match ($config) {
            'error' => ['error'],
            default => [],
        };
    }

    private function logLevelInt(string $level): int
    {
        return match ($level) {
            'error' => Logger::LEVEL_ERROR,
            'warning' => Logger::LEVEL_WARNING,
            'info' => Logger::LEVEL_INFO,
            'trace' => Logger::LEVEL_TRACE,
            'profile' => Logger::LEVEL_PROFILE,
        };
    }

    private static function logLevelName(int $level): string
    {
        $level = self::LOG_LEVEL_MAP[$level] ?? null;

        if ($level === null) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        return $level;
    }
}
