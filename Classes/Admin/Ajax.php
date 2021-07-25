<?php
namespace SkyBlueSofa\Canvass\Admin;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Canvass\Map\StoreCoords as MapStoreCoords;

/**
* Functionality that is used from within the dashboard
*/
class Ajax extends BaseObject
{
    /**
    * Setup adminstrative functionality
    * @param Loader $loader
    * @return void
    */
    public function __construct()
    {
        parent::__construct();

        (new MapStoreCoords)->init();
    }
}
