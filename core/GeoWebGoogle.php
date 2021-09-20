<?php
include_once ROOT_DIR . 'core/GeoBase.php';
include_once(ROOT_DIR . 'core/http/class.http.php');
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 09.01.2021
 * Time: 16:52
 * Облік запитів до гугла
 */

class GeoWebGoogle extends GeoBase
{
    public function __construct()
    {
        parent::__construct('web_google');
    }

    public function get_coordinate($address, $city)
    {
        if($this->getKey() === false) return;
        echo "{$this->key}\n";
        $buffer = preg_replace('/ {2,}/', ' ', $address);
        $buffer = str_replace("\n", '', $buffer);
        $buffer = str_replace("\r", '', $buffer);
        $data = explode(' ',$buffer);
        $res = [];
        foreach($data as $el){
            $d = trim($el);
            if(is_numeric($d)) $res[] = $d;
            if(strlen($d)>2) $res[] = $d;
        }
        $buffer = implode('+', $res);

        $http = new Http();

        $url = "https://www.google.com/maps/search/".$this->cp1251_to_utf($buffer);
        $google_curl = curl_init();
        curl_setopt($google_curl, CURLOPT_URL, $url);
        curl_setopt($google_curl, CURLOPT_HEADER, 0);
        curl_setopt($google_curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($google_curl, CURLOPT_FOLLOWLOCATION, 1);

        $http->website_id = 1000;
        $http->thread_id = 1;
        $http->use_index = 1;
        $proxy = $http->get_proxy_pay();
        if($proxy == false){
            echo "PROXY IS EMPTY\n";
            return;
        }
        curl_setopt($google_curl, CURLOPT_PROXY, $proxy['ip'] . ':' . $proxy['port']);
        if (!empty($proxy['proxy_password'])) curl_setopt($google_curl, CURLOPT_PROXYUSERPWD, $proxy['proxy_login'] . ':' . $proxy['proxy_password']);

        $data = curl_exec($google_curl);
        curl_close($google_curl);
        $http->clear_use_proxy_pay();
        $this->lat = 0;
        $this->lon = 0;

        if(strpos($data, 'www.google.com/maps/place/') !=false) { // якщо такий URL то координати знайдено
            echo "www.google.com/maps/place/\n";
            $pos1 = strpos($data, 'APP_INITIALIZATION_STATE=[[[');
            if ($pos1 !== false) {
                $coor = substr($data, $pos1 + strlen('APP_INITIALIZATION_STATE=[[['));
                $pos2 = strpos($coor, ']');
                $coor = substr($coor, 0, $pos2);
                $coor = explode(',', $coor);
                $this->lat = floatval($coor[2]);
                $this->lon = floatval($coor[1]);
                $this->city = $city;
            }
            // якщо поза межами України
            if ($this->lat > 52.057307 || $this->lat < 46.056004 || $this->lon < 23.579433 || $this->lon > 40.321552) {
                $this->lat = 0;
                $this->lon = 0;
            }
        }
        $this->incUse($this->lat == 0); // если координаты не найдены
        return;
    }
}