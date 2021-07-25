<?php
namespace SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode;

use SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode\Contract\ReverseGeocode;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Entity as MapEntity;

class Bing extends ReverseGeocode
{
    public function setApiUrl()
    {
        $this->apiUrl = 'http://dev.virtualearth.net/REST/v1/Locations/%s,%s?key=%s';
    }

    public function setApiKey()
    {
        $this->apiKey = $this->settings->get('bing_api_key');
    }

    protected function formatGeocode()
    {
        if (empty($this->geocodeData)) {
            return null;
        }

        $addressData = $this->geocodeData['resourceSets'][0]['resources'][0]['address'];
        $coordinateData = $this->geocodeData['resourceSets'][0]['resources'][0]['point'];

        $streetData = explode(' ', $addressData['addressLine']);
        $streetNumber = array_shift($streetData);
        $roadName = implode(' ', $streetData);

        $mapEntity = new MapEntity();
        $mapEntity->setFullAddress($addressData['formattedAddress']);
        $mapEntity->setStreet($addressData['addressLine']);
        $mapEntity->setNumber($streetNumber);
        $mapEntity->setRoad($roadName);
        $mapEntity->setState($addressData['adminDistrict']);
        $mapEntity->setCity($addressData['locality']);
        $mapEntity->setZipCode($addressData['postalCode']);
        $mapEntity->setLatitude($coordinateData['coordinates'][0]);
        $mapEntity->setLongitude($coordinateData['coordinates'][1]);

        // The city isn't properly given in bing, so we'll attempt to find it in the formatted address
        $regex = '/' . $addressData['addressLine'] . ', (.*), ' . $addressData['adminDistrict'] . ' ' . $addressData['postalCode'] . '/';
        preg_match($regex, $addressData['formattedAddress'], $matches);
        if (isset($matches[1])) {
            $mapEntity->setCity($matches[1]);
        }

        return $mapEntity;
    }
}
