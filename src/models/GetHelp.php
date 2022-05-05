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
    public mixed $fromEmail = null;

    /**
     * @var
     */
    public mixed $feedIssue = null;

    /**
     * @var
     */
    public mixed $message = null;

    /**
     * @var bool
     */
    public bool $attachLogs = false;

    /**
     * @var bool
     */
    public bool $attachSettings = false;

    /**
     * @var bool
     */
    public bool $attachFeed = false;

    /**
     * @var bool
     */
    public bool $attachFields = false;

    /**
     * @var
     */
    public mixed $attachment = null;

    // Public Methods
    // =========================================================================

    /**
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'fromEmail' => Craft::t('feed-me', 'Your Email'),
        ];
    }

    /**
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['fromEmail', 'feedIssue', 'message'], 'required'],
            [['fromEmail'], 'email'],
            [['fromEmail'], 'string', 'min' => 5, 'max' => 255],
            [['attachment'], 'file', 'maxSize' => 3145728],
        ];
    }
}
