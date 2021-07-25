<?php

namespace SkyBlueSofa\Canvass;

use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Support\PluginSettings;
use SkyBlueSofa\Canvass\Support\PluginErrors;
use SkyBlueSofa\Canvass\GoogleEvents\FormMapping;

/*
The settings class is used to make it easier to access plugin settings.

By default, you may use either of these functions to get the current value a setting:
    $settings->get('my_setting_handle'); // or
    $settings->getMySettingHandle(); //magic method

The difference between the two is that the second 'magic' method may be overwritten
by creating a concrete method here. The advantage of a concrete method is that,
for example:
* it may be written to return a default value for the setting
* it may be written to return different values based on the value of another
setting, such as the server environment

The settings class is made available in any object that extends the
    \SkyBlueSofa\Canvass\Support\Contract\BaseObject
It may also be made available by calling
    new \DealerInspire\DIPlatform\Settings();

*/
class Settings extends PluginSettings
{
    public function getLeafletLocalScriptUrl()
    {
        $min = ( Wordpress::currentUserCan('edit_posts')) ? '' : '.min';
        return $this->loader->pluginURL().'assets/js/canvassMap' . $min . '.js';
    }

    public function getLeafletBingLayerScriptUrl()
    {
        $min = ( Wordpress::currentUserCan('edit_posts')) ? '' : '.min';
        return $this->loader->pluginURL().'assets/js/leaflet-bing-layer/leaflet-bing-layer' . $min . '.js';
    }

    public function getLeafletControlCustomScriptUrl()
    {
        $min = ( Wordpress::currentUserCan('edit_posts')) ? '' : '.min';
        return $this->loader->pluginURL().'assets/js/Leaflet.Control.Custom/Leaflet.Control.Custom' . $min . '.js';
    }





    public function areCorrect()
    {
        return !PluginErrors::hasAny();
    }

    public function shouldTriggerAdminNoticeWhenServerEnvironmentIsOverridden()
    {
        return (
            !$this->get('environment') &&
            $this->getTestMode() &&
            $this->getOverrideServerEnvironment()
        );
    }

    public function setTestModeEnabledTimestamp()
    {
        $this->set('test_mode_enabled_timestamp', time(), true);
    }
    
    public function clearTestModeEnabledTimestamp()
    {
        $this->set('test_mode_enabled_timestamp', null, true);
    }
    
    public function shouldNotifyThatSiteHasBeenInTestModeTooLong($seconds = null)
    {
        if (ENVIRONMENT != 'production' || !$this->getTestMode()) {
            return false;
        }

        if (!$this->getTimeElapsedSinceTestModeEnabledIsLongerThan($seconds)) {
            return false;
        }

        if ($this->getTestModeEnabledTooLongNoticeSent()) {
            return false;
        }

        return true;
    }

    public function getTimeElapsedSinceTestModeEnabledIsLongerThan($seconds = 86400)
    {
        return $this->getTimeElapsedSinceTestModeEnabled()>=$seconds;
    }

    public function getTimeElapsedSinceTestModeEnabled()
    {
        $testModeEnabledTimestamp = $this->get('test_mode_enabled_timestamp');
        if (!$testModeEnabledTimestamp) {
            return 0;
        }
        return time()-$testModeEnabledTimestamp;
    }

    public function getTestModeEnabledTooLongNoticeSent()
    {
        return (bool) $this->get('test_mode_enabled_too_long_notice_sent');
    }

    public function setTestModeEnabledTooLongNoticeSent($sent = true)
    {
        $this->set('test_mode_enabled_too_long_notice_sent', (bool) $sent, true);
    }

    public function getFormTypes()
    {
        return (new FormMapping)->formTypes();
    }
    
    public function getFormMapping()
    {
        return (new FormMapping)->formMapping();
    }

    public function getAllowableRoxanneModules()
    {
        if ($this->getPartialEventSending()) {
            return $this->getPartialEventModulesActive();
        }
        return [];
    }

    public function getPartialEventSending()
    {
        return (bool) $this->get('partial_event_sending');
    }

    public function getPartialEventModulesActive()
    {
        $activeRoxanneModules = $this->get('partial_event_modules_active');

        if ($activeRoxanneModules===true) {
            return [];
        }

        if (is_string($activeRoxanneModules)) {
            $activeRoxanneModules = array($activeRoxanneModules);
        }

        $activeRoxanneModules = array_filter(array_values($activeRoxanneModules));
        if (count($activeRoxanneModules)==0) {
            return [];
        }

        $activeRoxanneModules[] = 'Roxanne';
        natsort($activeRoxanneModules);
        return array_values($activeRoxanneModules);
    }

    public function savePostSettings()
    {
        if (isset($_POST[$this->settingsPostKey]['test_mode']) || isset($_POST[$this->settingsPostKey]['expectations']['test_mode'])) {
            // We are saving the 'advanced' tab
            if (isset($_POST[$this->settingsPostKey]['test_mode'])) {
                // We're saving the 'advanced' tab and setting test mode to TRUE
                $currentTestModeValue = (bool) $this->get('test_mode');
                if (!$currentTestModeValue) {
                    $this->setTestModeEnabledTooLongNoticeSent(false);
                    $this->setTestModeEnabledTimestamp();
                }
            } else {
                // We're saving the 'advanced' tab and setting test mode to FALSE
                $this->setTestModeEnabledTooLongNoticeSent(false);
                $this->clearTestModeEnabledTimestamp();
            }
        }

        parent::savePostSettings();
    }
}
