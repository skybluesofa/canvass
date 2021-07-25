<?php
namespace SkyBlueSofa\Canvass\Canvass\Map;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Support\Wordpress;

class Map extends BaseObject
{
    private $defaultAttributes = [];
    private $customAttributes = [];
    private $content;

    public function withAttributes($attributes)
    {
        $this->customAttributes = $attributes;
    }

    public function withContent($content)
    {
        $this->content = $content;
    }

    public function generate()
    {
        $attributes = Wordpress::shortcodeAtts($this->defaultAttributes, $this->customAttributes);
        $attributes['api_key'] = $this->settings->get('api_key');

        return $this->render('Frontend/Canvass/map.php', $attributes);
    }
}
