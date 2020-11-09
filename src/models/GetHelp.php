<?php

namespace craft\feedme\models;

use Craft;
use craft\base\Model;

class GetHelp extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $fromEmail;

    /**
     * @var
     */
    public $feedIssue;

    /**
     * @var
     */
    public $message;

    /**
     * @var bool
     */
    public $attachLogs = false;

    /**
     * @var bool
     */
    public $attachSettings = false;

    /**
     * @var bool
     */
    public $attachFeed = false;

    /**
     * @var bool
     */
    public $attachFields = false;

    /**
     * @var
     */
    public $attachment;

    // Public Methods
    // =========================================================================

    /**
     * @var
     */
    public function attributeLabels()
    {
        return [
            'fromEmail' => Craft::t('feed-me', 'Your Email'),
        ];
    }

    /**
     * @var
     */
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
