<?php

namespace SkyBlueSofa\Canvass\Installation;

use SkyBlueSofa\Canvass\Support\Contract\Installation\Installer as InstallerContract;
use SkyBlueSofa\Canvass\Support\Loader;
use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Installation\SetupDefaults;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Entity as MapEntity;

/**
* Functionality associated with installing this plugin
*/
class Installer extends InstallerContract
{
    protected $pluginName = 'Canvass';
    protected $migrateVersions = [
        //'1.0.0.0'
    ];

    public function migrateSettingsTo_1_0_0_0($migrateToVersion)
    {
        // Do something that 'migrates' settings to a new format
        //$this->settings->save($this->settings->all());
    }

    public function install()
    {
        parent::install();

        $this->createDatabaseTables();
        $this->createCanvassPageType();
        $this->setupRoles();
    }
    
    /**
    * Setup default settings
    * @return void
    */
    protected function installDefaultOptions()
    {
        $options = $this->settings->all();
        Wordpress::logNotice("Installing default options", "Canvass");

        $this->settings->save($options);
    }

    protected function createDatabaseTables()
    {
        (new MapEntity)->createDbTable();
    }

    protected function createCanvassPageType()
    {
        register_post_type(
            'Canvass Map',
            [
                'public' => true,
                'capability_type' => 'canvass_editor',
                'exclude_from_search' => true,
            ]
        );
    }

    protected function setupRoles()
    {
        Wordpress::addRole(
            'canvass_editor',
            'Canvass Editor',
            [
                'edit_canvass_locations'
            ]
        );
    }
}
