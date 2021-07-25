<?php
namespace SkyBlueSofa\Canvass\Canvass\Map\ReverseGeocode\Contract;

use SkyBlueSofa\Canvass\Support\Contract\BaseObject;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Entity as MapEntity;

abstract class ReverseGeocode extends BaseObject
{
    protected $apiUrl;
    protected $apiKey;

    protected $latitude;
    protected $longitude;

    protected $generatedUrl = '';
    protected $geocodeData = [];

    /**
     * Sets the apiUrl for the provider
     *
     * @return void
     */
    abstract public function setApiUrl();

    /**
     * Sets the apiKey for the provider
     *
     * @return void
     */
    abstract public function setApiKey();

    /**
     * Takes this object's $geocodeData and returns a MapEntity object
     *
     * @return MapEntity
     */
    abstract protected function formatGeocode();

    public function __construct()
    {
        parent::__construct();
        
        $this->setApiUrl();
        $this->setApiKey();
    }
    
    /**
     * Takes coordinates and returns a MapEntity object
     *
     * @param float $latitude
     * @param float $longitude
     * @return MapEntity
     */
    public function get($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        $this->generateGetUrl();

        $this->getData();

        if (empty($this->geocodeData)) {
            return null;
        }

        return $this->formatGeocode();
    }

    protected function generateGetUrl()
    {
        $this->generatedUrl = vsprintf(
            $this->apiUrl,
            [$this->latitude, $this->longitude, $this->apiKey]
        );
    }

    protected function getData()
    {
        $curl = curl_init();

        // set our url with curl_setopt()
        curl_setopt($curl, CURLOPT_URL, $this->generatedUrl);

        // return the transfer as a string, also with setopt()
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // curl_exec() executes the started curl session
        // $output contains the output string
        $output = curl_exec($curl);

        // close curl resource to free up system resources
        // (deletes the variable made by curl_init)
        curl_close($curl);

        $data = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Geocode returned data could not be parsed: ' . $output);
            $data = [];
        }

        $this->geocodeData = $data;
    }
}
