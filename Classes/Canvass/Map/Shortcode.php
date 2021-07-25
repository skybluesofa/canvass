<?php
namespace SkyBlueSofa\Canvass\Canvass\Map;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Canvass\Map\Map;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Entity as MapEntity;
use SkyBlueSofa\Canvass\Canvass\Map\StoreCoords;

class Shortcode extends BaseObject
{
    private $attributes = [];
    private $content;

    public function init()
    {
        Wordpress::addShortcode(
            'canvass_map',
            function () {
                $this->attributes = func_get_arg(0);
                $this->content = func_get_arg(1);
                $this->implementMapShortcode();
            }
        );
    }

    private function implementMapShortcode()
    {
        $this->addMapJavascript();
        $this->addMapCSS();
        return $this->generateMapHtml();
    }

    private function addMapJavascript()
    {
        Wordpress::registerScript(
            'sbs-canvass-leaflet-remote-js',
            'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js'
        );
        Wordpress::enqueueScript('sbs-canvass-leaflet-remote-js');

        Wordpress::registerScript(
            'sbs-canvass-leaflet-local-js',
            $this->settings->getLeafletLocalScriptUrl(),
            ['sbs-canvass-leaflet-remote-js'],
            $this->loader->pluginVersion()
        );
        Wordpress::localizeScript(
            'sbs-canvass-leaflet-local-js',
            'canvass_settings',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'storage_action' => StoreCoords::AJAX_ACTION,
                'api_key' => $this->settings->get('api_key'),
                'existing_markers' => $this->generateExistingMarkers()
            ]
        );
        Wordpress::enqueueScript('sbs-canvass-leaflet-local-js');

        Wordpress::registerScript(
            'sbs-canvass-promises-remote-js',
            'https://cdn.polyfill.io/v2/polyfill.min.js?features=Promise',
            ['sbs-canvass-leaflet-remote-js']
        );
        Wordpress::enqueueScript('sbs-canvass-promises-remote-js');

        Wordpress::registerScript(
            'sbs-canvass-leaflet-bing-layer-js',
            $this->settings->getLeafletBingLayerScriptUrl(),
            ['sbs-canvass-promises-remote-js', 'sbs-canvass-leaflet-remote-js'],
            $this->loader->pluginVersion()
        );
        Wordpress::localizeScript(
            'sbs-canvass-leaflet-bing-layer-js',
            'bing_canvass_params',
            [
                'bing_api_key' => $this->settings->get('bing_api_key')
            ]
        );
        Wordpress::enqueueScript('sbs-canvass-leaflet-bing-layer-js');

        Wordpress::registerScript(
            'sbs-canvass-leaflet-control-custom-js',
            $this->settings->getLeafletControlCustomScriptUrl(),
            ['sbs-canvass-promises-remote-js', 'sbs-canvass-leaflet-remote-js'],
            $this->loader->pluginVersion()
        );
        Wordpress::enqueueScript('sbs-canvass-leaflet-control-custom-js');
    }

    private function addMapCSS()
    {
        Wordpress::enqueueStyle('sbs-canvass-remote-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    }

    private function generateMapHtml()
    {
        $map = new Map();
        $map->withAttributes($this->attributes);
        $map->withContent($this->content);

        return $map->generate();
    }

    private function generateExistingMarkers()
    {
        return (new MapEntity)->getLocationsForMap();
    }
}
