<?php
include_once ROOT_DIR . 'core/GeoBase.php';
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 09.01.2021
 * Time: 16:52
 * Облік запитів до гугла
 */

class GeoOSM extends GeoBase
{
    public function __construct()
    {
        parent::__construct('osm');
    }

    public function get_coordinate($address)
    {
        if($this->getKey() === false) return;
        echo "{$this->key}\n";
        $url = 'https://nominatim.openstreetmap.org/search.php?q=' . urlencode($this->cp1251_to_utf($address)) . '&format=json&addressdetails=1';
        $this->response = $this->geoGetUrl($url);
        $data = json_decode($this->response, true);
        $this->lon = floatval($data[0]['lon']); // долгота
        $this->lat = floatval($data[0]['lat']); // широта
        $this->city = isset($data[0]['address']['city']) ? $this->utf_to_1251($data[0]['address']['city']) : '';
        return;
    }
}