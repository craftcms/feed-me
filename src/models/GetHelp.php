<?php

namespace craft\feedme\models;

use Craft;
use craft\base\Model;

class GetHelp extends Model
{
    // Properties
    // =========================================================================

    public $fromEmail;
    public $feedIssue;
    public $message;
    public $attachLogs = false;
    public $attachSettings = false;
    public $attachFeed = false;
    public $attachFields = false;
    public $attachment;


    // Public Methods
    // =========================================================================

    public function attributeLabels()
    {
        return [
            'fromEmail' => Craft::t('feed-me', 'Your Email'),
        ];
    }

    public function rules()
    {
        return [
            [['fromEmail', 'feedIssue', 'message'], 'required'],
            [['fromEmail'], 'email'],
            [['fromEmail'], 'string', 'min' => 5, 'max' => 255],
            [['attachment'], 'file', 'maxSize' => 3145728],
        ];
    }
}
