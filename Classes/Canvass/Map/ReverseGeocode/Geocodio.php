<?php
namespace SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode;

use SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode\Contract\ReverseGeocode;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Entity as MapEntity;

class Geocodio extends ReverseGeocode
{
    public function setApiUrl()
    {
        $this->apiUrl = 'https://api.geocod.io/v1.6/reverse?q=%s,%s&api_key=%s';
    }

    public function setApiKey()
    {
        $this->apiKey = 'ccc68f0c113f1bf9b43c60e94b40f1e9b06e388';
    }

    protected function formatGeocode()
    {
        if (empty($this->geocodeData)) {
            return null;
        }

        error_log('formatGeocode is not setup for Geocodio');

        $addressData = $this->geocodeData['resourceSets'][0]['resources'][0]['address'];
        $coordinateData = $this->geocodeData['resourceSets'][0]['resources'][0]['point'];

        $mapEntity = new MapEntity();
        $mapEntity->setStreet($addressData['addressLine']);
        $mapEntity->setCity($addressData['adminDistrict']);
        $mapEntity->setState($addressData['addressLine']);
        $mapEntity->setZipCode($addressData['postalCode']);
        $mapEntity->setLatitude($coordinateData['coordinates'][0]);
        $mapEntity->setLongitude($coordinateData['coordinates'][1]);

        return $mapEntity;
    }
}
