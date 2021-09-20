<?php
include_once ROOT_DIR . 'core/GeoBase.php';
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 09.01.2021
 * Time: 16:52
 * Облік запитів до гугла
 */

class GeoGoogle extends GeoBase
{
    public function __construct()
    {
        parent::__construct('google');
    }

    public function get_coordinate($address)
    {
        if ($this->getKey() === false) return;
        echo "{$this->key}\n";
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($this->cp1251_to_utf($address)) . '.&language=ru&key=' . $this->key;
        $this->response = $this->geoGetUrl($url);
        $sContent = json_decode($this->response);
        $this->lon = floatval($sContent->results[0]->geometry->location->lng); // долгота
        $this->lat = floatval($sContent->results[0]->geometry->location->lat); // широта
        if (isset($sContent->results[0]->address_components)) {
            foreach ($sContent->results[0]->address_components as $address_components)
                if (in_array('locality', $address_components->types)) $this->city = $this->utf_to_1251($address_components->short_name);
        }
        $city = $this->translate_city_to_ru($this->city);
        if(!empty($city)) $this->city = $city;
    }
}