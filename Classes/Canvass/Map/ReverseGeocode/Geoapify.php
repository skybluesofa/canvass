<?php
namespace SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode;

use SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode\Contract\ReverseGeocode;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Entity as MapEntity;

class Geoapify extends ReverseGeocode
{
    public function setApiUrl()
    {
        $this->apiUrl = 'https://api.geoapify.com/v1/geocode/reverse?lat=%s&lon=%s&apiKey=%s';
    }

    public function setApiKey()
    {
        $this->apiKey = '5bf0ae0235df45dd926e235ce2742fc5';
    }

    protected function formatGeocode()
    {
        if (empty($this->geocodeData)) {
            return null;
        }
        
        error_log('formatGeocode is not setup for Geoapify');

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
