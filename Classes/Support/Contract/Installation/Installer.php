<?php

namespace SkyBlueSofa\Canvass\Support\Contract\Installation;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Support\Loader;

class Installer extends BaseObject
{
    protected $pluginName = '';

    private $pluginVersion;
    private $migratedVersion;

    /**
    * A mapping of original option keys to keys used by this plugin
    * [
    *   'original_key_name' => 'new_plugin_key_name',
    *   'original_key_name_2' => [
    *       'some_sub_key_name' => 'new_plugin_toplevel_key'
    *   ]
    * ]
    * @return array
    */
    protected function coreToPluginOptionMap()
    {
        return [];
    }

    public function __construct()
    {
        parent::__construct();

        Wordpress::addAction('pluginsLoaded', [$this, 'migrateSettings']);
        Wordpress::registerActivationHook($this->loader->pluginFile(), [$this, 'install']);
        Wordpress::registerDeactivationHook($this->loader->pluginFile(), [$this, 'uninstall']);
    }

    private function setPluginVersion()
    {
        if ($pluginVersion = Wordpress::getFileData($this->loader->pluginFile(), ['Version'=>'Version'], 'plugin')) {
            $this->pluginVersion = $pluginVersion['Version'];
        } else {
            $this->pluginVersion = "0.0.0.0";
        }
    }

    private function setMigratedVersion()
    {
        $this->migratedVersion = $this->settings->get('migration') ? $this->settings->get('migration') : '0.0.0.0';
    }

    public function migrateSettings()
    {
        $this->setPluginVersion();
        $this->setMigratedVersion();

        if (version_compare($this->migratedVersion, $this->pluginVersion)<0) {
            foreach ($this->migrateVersions as $migrateToVersion) {
                if (version_compare($this->migratedVersion, $migrateToVersion)<0) {
                    $snakeVersion = str_replace('.', '_', $migrateToVersion);
                    call_user_func_array([$this, 'migrateSettingsTo_'.$snakeVersion], [$migrateToVersion]);
                }
                $this->settings->set('migration', $migrateToVersion);
                $this->migratedVersion = $migrateToVersion;
            }
        }
    }

    /**
    * Install things that this plugin needs
    * @return void
    */
    public function install()
    {
        error_log("Activating ".($this->pluginName?$this->pluginName.' ':'')."Plugin");
        $this->installDefaultOptions();
    }

    /**
    * Uninstall things that this plugin no longer needs
    * @return void
    */
    public function uninstall()
    {
        error_log("Deactivating ".($this->pluginName?$this->pluginName.' ':'')."Plugin");
    }

    /**
    * Setup default settings
    * @return void
    */
    protected function installDefaultOptions()
    {
        $options = $this->settings->all();

        $this->settings->save($options);
    }
}
