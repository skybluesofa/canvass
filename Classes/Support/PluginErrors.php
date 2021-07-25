<?php

namespace SkyBlueSofa\Canvass\Support;

use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Support\Loader;
use SkyBlueSofa\Canvass\Support\Contract\BaseObject;

/**
 * Settings for the plugin
 *
 * @return void
 */
class PluginErrors extends BaseObject
{
    public static function showFor($page)
    {
        if (!(new self)->isActive()) {
            print "<div class=\"notice notice-warning is-dismissible\"><p><b>".$page->getPageTitle().":</b> The plugin has been paused. <a href=\"".Wordpress::adminUrl('admin.php?page='.$page->getPageSlug())."\">Turn it on</a></p></div>";
            return false;
        }

        self::displayAdminNoticesFor($page);
    }

    public static function hasAny()
    {
        if (!(new self)->isActive()) {
            return false;
        }
        return (count(self::getAdminNotices())) ? true : false;
    }

    /**
    * If there are configuration errors, then display them at the top of the page
    * @return void
    */
    private static function displayAdminNoticesFor($page)
    {
        self::output(self::getAdminNotices(), $page);
    }

    private static function getAdminNotices()
    {
        $notices = [];
        foreach ((new self)->loader->config('errors') as $configurationError) {
            if (self::configurationFails($configurationError)) {
                $notices = array_merge(
                    $notices,
                    self::checkEmptyFields($configurationError),
                    self::checkAtLeastOneFieldIsFilled($configurationError),
                    self::checkCustomValidation($configurationError)
                );
            }
        }
        return $notices;
    }

    private static function configurationFails($configurationError)
    {
        $toggles = (array) $configurationError['when_on'];
        if (count($toggles)>0) {
            foreach ($toggles as $toggle) {
                if (!(new self)->settings->get($toggle)) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function checkEmptyFields($configurationError)
    {
        $notices = [];
        $fields = (array) $configurationError['fields_should_not_be_empty'];
        if (count($fields)>0) {
            foreach ($fields as $field) {
                if (!(new self)->settings->get($field)) {
                    $notices[$configurationError['message']] = $configurationError;
                }
            }
        }
        return $notices;
    }

    private static function checkAtLeastOneFieldIsFilled($configurationError)
    {
        $notices = [];
        $fields = (array) $configurationError['one_field_should_be_filled'];
        if (count($fields)>0) {
            $atLeastOneFieldFilled = false;
            foreach ($fields as $field) {
                if ((new self)->settings->get($field)) {
                    $atLeastOneFieldFilled = true;
                }
            }
            if (!$atLeastOneFieldFilled) {
                $notices[$configurationError['message']] = $configurationError;
            }
        }
        return $notices;
    }

    private static function checkCustomValidation($configurationError)
    {
        $notices = [];
        if (isset($configurationError['custom_validation']) && is_callable($configurationError['custom_validation'])) {
            $hasError = call_user_func($configurationError['custom_validation'], PluginSettings::instance());
            if ($hasError) {
                $notices[$configurationError['message']] = $configurationError;
            }
        }
        return $notices;
    }

    private static function output($notices, $page)
    {
        if ($notices) {
            foreach ($notices as $notice) {
                $noticeClass = $notice['type'] ? $notice['type'] : 'info';
                $noticeTab = (isset($notice['tab']) && $notice['tab']) ? '&tab='.$notice['tab'] : '';
                print "<div class=\"notice notice-".$noticeClass." is-dismissible\"><p><b>".$page->getPageTitle().":</b> ".$notice['message']." <a href=\"".Wordpress::adminUrl('admin.php?page='.$page->getPageSlug()).$noticeTab."\">Fix it</a></p></div>";

                Wordpress::logNotice($notice['message'], $page->getPageTitle());
            }
        }
    }
}
