<?php
namespace SkyBlueSofa\Canvass\Admin;

use SkyBlueSofa\Canvass\Support\Contract\Admin\SettingsPage;
use SkyBlueSofa\Canvass\GoogleEvents\GoogleEvents;
use SkyBlueSofa\Canvass\GoogleEvents\FormMapping;
use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\GoogleEvents\Traits\ChecksAndAlertsIfInTestMode;

/**
* Functionality that is used from within the dashboard
*/
class BasicSettings extends SettingsPage
{
    /**
    * @var string
    */
    protected $pageTitle = 'Canvass';
    /**
    * @var string
    */
    protected $menuTitle = 'Canvass';

    protected $tabs = [
        'setup' => ['title'=>'Setup', 'active'=>true, 'url'=>'#'],
        //'advanced' => ['title'=>'Advanced', 'active'=>false, 'url'=>'#advanced'],
    ];

    public function __construct()
    {
        parent::__construct();

        if ($this->isActive()) {
        }
    }

    public function setup_getTabData()
    {
        return
        [
            'hideSubmitButton' => false,
            'pluginSettings' => $this->settings,
        ];
    }

    public function advanced_getTabData()
    {
        return
        [
            'hideSubmitButton' => false,
            'pluginSettings' => $this->settings,
        ];
    }
}
