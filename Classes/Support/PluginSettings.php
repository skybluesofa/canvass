<?php

namespace SkyBlueSofa\Canvass\Support;

use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Support\Loader;

/**
 * Settings for the plugin
 *
 * @return void
 */
abstract class PluginSettings
{
    /**
     * Settings for the plugin
     *
     * @var mixed
     */
    protected $loader;

    /**
     * Did the previous call to save() work?
     *
     * @var bool
     */
    protected $didSave = false;

    /**
     * Name of the key that settings will be saved under
     *
     * @var string
     */
    protected $optionKey = null;

    /**
     * Name of the key that POST settings will be sent under
     *
     * @var string
     */
    protected $settingsPostKey = null;

    /**
     * Cached values for this session
     *
     * @var array
     */
    private $cache = [];

    /**
     * Cached options from the db
     *
     * @var array
     */
    private $optionsCache = null;

    private static $instance;

    /**
     * Setup the settings object
     *
     * @param Loader $loader
     * @return void
     */
    private function __construct()
    {
        $this->loader = Loader::instance();

        $this->setPluginConfiguration();

        //Wordpress::addAction('admin_init', array($this, 'savePostSettings'));
    }

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
    * Get configurations from file and set them
    * @return void
    */
    private function setPluginConfiguration()
    {
        foreach ($this->loader->config('plugin') as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
    * Get a plugin configuration
    * @param string $key
    * @return mixed
    */
    public function getConfig($key)
    {
        if (!property_exists($this, $key)) {
            throw new \Exception("The '".$key."' property does not exist on the 'Classes\\Support\\Settings' class");
        }
        return $this->__get($key);
    }

    /**
     * Returns an individual plugin setting
     *
     * @param string $key
     * @param mixed $default
     * @param bool $skipCache
     * @return mixed
     */
    public function get($key, $default = false, $skipCache = false)
    {
        if (!$skipCache && isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $options = $this->optionsCache;
        if ($skipCache || is_null($options)) {
            $options = Wordpress::getOption($this->optionKey());
            $this->optionsCache = $options ? $options : [];
        }

        if (strpos($key, '.')===false) {
            $value = isset($options[$key]) ? $options[$key] : $default;
        } else {
            $keys = explode('.', $key);
            $currentBranch = $options;
            $currentKey = array_shift($keys);
            while ($currentKey) {
                if (array_key_exists($currentKey, $currentBranch)) {
                    $currentBranch = $currentBranch[$currentKey];
                    $value = $currentBranch;
                    $currentKey = array_shift($keys);
                } else {
                    $value = $default;
                    $currentKey = false;
                }
            }
        }
        return $this->cache($key, $value);
    }

    // get configs using getStudlyCase option name
    public function __call($methodName, $arguments)
    {
        if (strpos($methodName, 'get')===0) {
            $optionName = substr($methodName, 3);
            preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $optionName, $matches);
            $optionName = strtolower(implode('_', $matches[0]));
            $options = $this->all();
            if (array_key_exists($optionName, $options)) {
                return $this->get($optionName);
            }
        } elseif (strpos($methodName, 'set')===0) {
            if (count($arguments)==0) {
                return false;
            }
            $optionName = substr($methodName, 3);
            preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $optionName, $matches);
            $optionName = strtolower(implode('_', $matches[0]));
            $options = $this->all();
            if (array_key_exists($optionName, $options)) {
                $clearCache = false;
                if (count($arguments)>1) {
                    $clearCache = (bool) $arguments[1];
                }
                $this->set($optionName, $arguments[0], $clearCache);
            }
        } elseif (strpos($methodName, 'is')===0) {
            $optionName = substr($methodName, 2);
            preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $optionName, $matches);
            $optionName = strtolower(implode('_', $matches[0]));
            $options = $this->all();
            if (array_key_exists($optionName, $options)) {
                if (count($arguments)>0) {
                    return ($this->get($optionName)==$arguments[0]);
                }
                return (bool) $this->get($optionName);
            }
        }
    }

    /**
     * Returns all plugin Ssr_UI_TableSettings
     *
     * @return mixed
     */
    public function all()
    {
        $defaultOptions = $this->defaults();
        $currentOptions = Wordpress::getOption($this->optionKey());
        return array_replace_recursive(
            $defaultOptions,
            is_array($currentOptions) ? $currentOptions : []
        );
    }

    /**
     * Caches the value for the given key
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    private function cache($key, $value)
    {
        $this->cache[$key] = $value;
        return $value;
    }

    /**
     * Updates a single plugin setting
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value, $clearCache = false)
    {
        $setting = [];
        $this->assignArrayByPath($setting, $key, $value);
        $this->save($setting);
        if ($clearCache) {
            $this->cache = [];
        }
    }

    private function assignArrayByPath(&$arr, $path, $value, $separator = '.')
    {
        foreach (explode($separator, $path) as $key) :
            $arr = &$arr[$key];
        endforeach;
        $arr = $value;
    }

    /**
     * Updates plugin settings. If 'mergeWithCurrent' is true, the system will
     * first get current settings and then merge the given $data with the current
     * settings.
     *
     * @param mixed $data
     * @param bool $mergeWithCurrent
     * @return bool
     */
    public function save($data, $mergeWithCurrent = true)
    {
        if ($mergeWithCurrent) {
            $data = array_replace_recursive(
                $this->all(),
                $data
            );
        }
        Wordpress::updateOption($this->optionKey(), $data, false);
        $this->optionsCache = null;
        $this->didSave = true;
        return $this->didSave();
    }

    public function saveKey($key, $value)
    {
        return $this->save([$key => $value]);
    }

    /**
     * Updates plugin settings sent in a POST request
     *
     * @return bool
     */
    public function savePostSettings()
    {
        if (!Wordpress::currentUserCan('manage_options')) {
            return false;
        }

        if ($postSettings = $this->getCleanPostData()) {
            $wasSaved = $this->save($postSettings);
            if ($wasSaved) {
                Wordpress::addAction('admin_notices', function () {
                    print "<div class=\"notice notice-info is-dismissible\"><p>Plugin settings have been saved.</p></div>";
                });
            }
            return $wasSaved;
        }

        return false;
    }

    /**
    * Runs through $_POST data
    * @return array
    */
    private function getCleanPostData()
    {
        if (!isset($_POST[$this->settingsPostKey]) || !count($_POST[$this->settingsPostKey])) {
            return false;
        }

        $postSettings = stripslashes_deep($_POST[$this->settingsPostKey]);

        return $this->setExpectedKeyValues($postSettings);
    }

    /**
    * If there is an 'expectations' key within the $postSettings, then modify the
    * remaining settings accordingly. This was initially developed to allow for
    * saving checkbox form data which, if unchecked will not send data, but that
    * needs to be recorded.
    * @param array $postSettings
    * @return array
    */
    private function setExpectedKeyValues($postSettings = [])
    {
        if (isset($postSettings['expectations'])) {
            foreach (array_keys($postSettings['expectations']) as $expectedField) {
                if (strpos($expectedField, '.')===false) {
                    $postSettings[$expectedField] = (isset($postSettings[$expectedField])) ? $postSettings[$expectedField] : 0;
                } else {
                    $value = $this->translateExpectedKeyValue($postSettings['expectations'][$expectedField]);
                    eval('$expectedSetting["' . implode('"]["', explode('.', $expectedField)) . '"] = '.$value.';');
                    $postSettings = array_replace_recursive(
                        $expectedSetting,
                        $postSettings
                    );
                }
            }
            unset($postSettings['expectations']);
        }

        return $postSettings;
    }

    private function translateExpectedKeyValue($value)
    {
        if ($value=='null') {
            return null;
        }
        if ($value=='false') {
            return false;
        }
        if ($value=='true') {
            return true;
        }
        if (is_numeric($value)) {
            return $value;
        }
        return '"'.$value.'"';
    }

    /**
     * Did the previous call to save() work?
     *
     * @return bool
     */
    public function didSave()
    {
        return $this->didSave;
    }

    /**
     * Get the settings key
     *
     * @return string
     */
    protected function optionKey()
    {
        return $this->optionKey;
    }

    /**
     * Get the default options for the plugin
     *
     * @return array
     */
    public function defaults()
    {
        $defaultOptions = $this->loader->config('default_plugin_options');
        return $defaultOptions;
    }
}
