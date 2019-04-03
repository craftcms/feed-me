<?php

namespace verbb\feedme\web\assets\feedme;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FeedMeAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@verbb/feedme/web/assets/feedme/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/feed-me' . $this->dotJs(),
        ];

        $this->css = [
            'css/feed-me.css',
        ];

        parent::init();
    }
}
