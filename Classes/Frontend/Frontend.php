<?php
namespace SkyBlueSofa\Canvass\Frontend;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Canvass\Canvass;

/**
* Functionality that is used from the public facing side of the site
*/
class Frontend extends BaseObject
{
    public function __construct()
    {
        parent::__construct();
        if ($this->isActive()) {
            (new Canvass)->init();
        }
    }
}
