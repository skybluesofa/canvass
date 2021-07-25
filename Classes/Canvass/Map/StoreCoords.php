<?php
namespace SkyBlueSofa\Canvass\Canvass\Map;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode\Bing;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Entity as MapEntity;

class StoreCoords extends BaseObject
{
    const AJAX_ACTION = 'canvass_store_marker';

    public function init()
    {
        Wordpress::addAction(
            'wp_ajax_' . StoreCoords::AJAX_ACTION,
            function () {
                $this->storeMapEntity(
                    $this->geocodeLocation()
                );
                die();
            },
            10,
            0
        );

        Wordpress::addAction(
            'wp_ajax_nopriv_' . StoreCoords::AJAX_ACTION,
            function () {
                $this->storeMapEntity(
                    $this->geocodeLocation()
                );
                die();
            },
            10,
            0
        );
    }

    public function geocodeLocation()
    {
        return (new Bing)->get($_GET['latitude'], $_GET['longitude']);
    }

    public function storeMapEntity(MapEntity $mapEntity)
    {
        $storageStatus = ($_GET['mode']=='remove') ? 'inactive' : 'active';
        $mapEntity->setIsDeleted($storageStatus === 'inactive');
        
        $mapEntity->save();

        return $mapEntity;
    }
}
