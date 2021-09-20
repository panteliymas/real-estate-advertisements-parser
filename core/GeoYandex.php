<?php
include_once ROOT_DIR . 'core/GeoBase.php';
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 09.01.2021
 * Time: 16:52
 * Облік запитів до яндекса
 */
class GeoYandex extends GeoBase
{
    public function __construct()
    {
        parent::__construct('yandex');
    }

    public function get_coordinate($address)
    {
        if(($this->getKey()) === false) return;
        echo "{$this->key}\n";
        $url = 'https://geocode-maps.yandex.ru/1.x/?geocode=';
        $url .= urlencode($this->cp1251_to_utf($address));
        $url .= '&format=json&results=1&apikey='.$this->key;
        $this->response = $this->geoGetUrl($url, 2);
        $json = json_decode( $this->response);
        $pos = $json->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
        $posArray = explode ( ' ', $pos );
        $this->lon = floatval($posArray[0]); // долгота
        $this->lat = floatval($posArray[1]); // широта
        $this->get_city($json, $city_name);
        $this->city = $this->translate_city_to_ru($this->utf_to_1251($city_name));
        return;
    }

    private function get_city($json, &$res){
        if(!$json) return;
        foreach($json as $key=>$val){
            if(strcmp($key, 'LocalityName') == 0){
                $res = $val;
                break;
            }
            if(strcmp(gettype($val), 'object') == 0){
                $this->get_city($val, $res);
            }
            if(strcmp(gettype($val), 'array') == 0){
                foreach($val as $k=>$v){
                    $this->get_city($v, $res);
                }
            }
        }
    }

}