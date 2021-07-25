<?php

namespace SkyBlueSofa\Canvass\Support\Contract\Admin;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Support\Loader;
use SkyBlueSofa\Canvass\Installation\Installer;

class Admin extends BaseObject
{
    /**
    * Setup adminstrative functionality
    * @param Loader $loader
    * @return void
    */
    public function __construct()
    {
        parent::__construct();

        $this->handleInstallation();
    }

    /**
    * Starts up the Activation class
    * @return void
    */
    private function handleInstallation()
    {
        new Installer();
    }
}
