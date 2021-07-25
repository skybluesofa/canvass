<?php

namespace SkyBlueSofa\Canvass\Support\Contract;

use SkyBlueSofa\Canvass\Support\Loader;
use SkyBlueSofa\Canvass\Settings;

/**
* Base object for the Arnold Action Tracking project
*/
class BaseObject
{
    /**
    * @var Loader $loader
    */
    protected $loader;
    /**
    * @var Settings $settings
    */
    protected $settings;

    /**
    * Instantiate this object
    * @param Loader $loader
    * @return void
    */
    public function __construct()
    {
        $this->loader = Loader::instance();

        $this->settings = Settings::instance();

        $this->instantiateTraits();
    }

    /**
    * Hand off the rendering to the Loader class
    * @param string $view
    * @param array $data
    * @return void
    */
    public function render($view, $data = [], $variablesAsTokens = false)
    {
        $this->loader->render($view, $data, $variablesAsTokens);
    }

    public function getLoader()
    {
        return $this->loader;
    }

    protected function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        return $text ? $text : '';
    }

    private function instantiateTraits()
    {
        if ($traits = class_uses($this)) {
            foreach ($traits as $trait) {
                $trait = explode("\\", $trait);
                $loadTraitMethodName = 'load'.array_pop($trait);
                if (method_exists($this, $loadTraitMethodName)) {
                    call_user_func([$this, $loadTraitMethodName]);
                }
            }
        }
    }

    protected function isActive($isActiveKey = null)
    {
        if (is_null($isActiveKey)) {
            $isActiveKey = isset($this->settings->isActiveKey) ? $this->settings->isActiveKey : 'is_active';
        }
        return $this->settings->get($isActiveKey);
    }
}
