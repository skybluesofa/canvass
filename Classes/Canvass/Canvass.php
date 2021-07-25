<?php
namespace SkyBlueSofa\Canvass\Canvass;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Canvass\Map\Shortcode as MapShortcode;

class Canvass extends BaseObject
{
    private $initSuccessful = false;

    public function init()
    {
        if ($this->settings->areCorrect()) {
            (new MapShortcode)->init();

            $this->initSuccessful = true;
        }
    }

    public function initSuccessful()
    {
        return (bool) $this->initSuccessful;
    }
}
