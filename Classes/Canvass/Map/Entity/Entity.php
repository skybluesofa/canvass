<?php
namespace SkyBlueSofa\Canvass\Canvass\Map\Entity;

use SkyBlueSofa\Canvass\Support\Wordpress;
use SkyBlueSofa\Canvass\Canvass\Map\Entity\Contract\Entity as EntityContract;

class Entity extends EntityContract
{
    protected $tableName = 'canvass_markers';
    
    protected $fullAddress;
    protected $street;
    protected $number;
    protected $road;
    protected $city;
    protected $state;
    protected $zipcode;
    protected $latitude;
    protected $longitude;
    protected $isDesired = 0;
    protected $isDeleted = 0;
    protected $userId;

    protected function createDbSql()
    {
        return "CREATE TABLE " . $this->tableName() . " (
            `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
            `fullAddress` varchar(255) NOT NULL,
            `street` varchar(255) NOT NULL,
            `number` varchar(50) NOT NULL,
            `road` varchar(200) NOT NULL,
            `city` varchar(100) NOT NULL,
            `state` varchar(25) NOT NULL,
            `zipcode` varchar(10) NOT NULL,
            `latitude` decimal(10,7) NOT NULL,
            `longitude` decimal(10,7) NOT NULL,
            `isDesired` tinyint(1) DEFAULT 0,
            `isDeleted` tinyint(1) DEFAULT 0,
            `userId` bigint(20) unsigned NOT NULL,
            `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id)
            );";
    }

    protected function insert()
    {
        $this->userId = Wordpress::getCurrentUserId();

        parent::insert();
    }

    public function setFullAddress($fullAddress)
    {
        $this->fullAddress = $fullAddress;
        return $this;
    }

    public function getFullAddress()
    {
        return $this->fullAddress;
    }

    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setRoad($road)
    {
        $this->road = $road;
        return $this;
    }

    public function getRoad()
    {
        return $this->road;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
        return $this;
    }

    public function getZipcode()
    {
        return $this->zipcode;
    }

    public function getAddress()
    {
        return implode(
            ' ',
            [
                $this->getStreet() . ',',
                $this->getCity() . ',',
                $this->getState(),
                $this->getZipCode()
            ]
        );
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getCoordinates()
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }

    public function setIsDesired($isDesired)
    {
        $this->isDesired = (bool) $isDesired;
        return $this;
    }

    public function getIsDesired()
    {
        return (bool) $this->isDesired;
    }

    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = (bool) $isDeleted;
        return $this;
    }

    public function getIsDeleted()
    {
        return (bool) $this->isDeleted;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getLocationsForMap()
    {
        $sql = "SELECT fullAddress, latitude, longitude, NOT isDesired AS visited, updated
            FROM " . $this->tableName() . "
            WHERE isDeleted=0 AND 
                (isDesired=0 OR (fullAddress NOT IN (SELECT fullAddress FROM " . $this->tableName() . " WHERE isDesired=0)))";

        return $this->getSqlResults($sql);
    }
}
