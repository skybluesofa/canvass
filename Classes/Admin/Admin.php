<?php
namespace SkyBlueSofa\Canvass\Admin;

use SkyBlueSofa\Canvass\Support\Contract\Admin\Admin as AdminContract;
use SkyBlueSofa\Canvass\Admin\BasicSettings;
use SkyBlueSofa\Canvass\Support\Loader;
use SkyBlueSofa\Canvass\Support\Wordpress;

/**
* Functionality that is used from within the dashboard
*/
class Admin extends AdminContract
{
    /**
    * Setup adminstrative functionality
    * @param Loader $loader
    * @return void
    */
    public function __construct()
    {
        parent::__construct();

        $this->setupAdminPages();
    }

    /**
    * Adds functionality to the dashboard
    * @return void
    */
    private function setupAdminPages()
    {
        new BasicSettings();
    }
}
