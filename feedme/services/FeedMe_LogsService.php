<?php
namespace Craft;

class FeedMe_LogsService extends BaseApplicationComponent
{

    public function show()
    {
        $criteria = new \CDbCriteria();
        $criteria->order = 'id desc';

        return FeedMe_LogsRecord::model()->findAll($criteria);
    }

    public function showLog($logs)
    {
        $criteria = new \CDbCriteria();
        $criteria->condition = 'logsId = :logs_id';
        $criteria->params = array(
            ':logs_id' => $logs,
        );

        $logItems = FeedMe_LogRecord::model()->findAll($criteria);

        return $logItems;
    }

    public function start($settings)
    {
        $logs              = new FeedMe_LogsRecord();
        $logs->feedId      = $settings['feed']->id;
        $logs->items       = $settings['items'];

        $logs->save(false);

        return $logs->id;
    }

    public function log($settings, $errors, $level)
    {
        // Firstly, store in plugin log file (use $level to control log level)
        FeedMePlugin::log(print_r($errors, true), $level);

        // Save this log to the DB as well
        if (isset($settings->attributes['logsId'])) {
            $logsId = $settings->logsId;

            if (FeedMe_LogsRecord::model()->findById($logsId)) {
                $log = new FeedMe_LogRecord();
                $log->logsId = $logsId;
                $log->errors = print_r($errors, true);

                $log->save(false);
            }
        }
    }

    public function end($settings)
    {
        if (isset($settings->attributes['logsId'])) {
            $logsId = $settings->logsId;

            $logs = FeedMe_LogsRecord::model()->findById($logsId);

            $logs->save(false);
        }
    }
}
