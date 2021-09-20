<?php

include_once(ROOT_DIR . 'core/http/class.http.php');

class GeocogingCache
{
    public static function Debug($val){
        if ($_SERVER['REMOTE_ADDR']=='94.230.205.128'){
            var_dump($val);
        }
    }

    public static function get_geogoding_address($lat, $lon)
    {
	//echo $_SERVER['REMOTE_ADDR'];
        $lat=str_replace(',','.',$lat);
        $lon=str_replace(',','.',$lon);

        if(floatval($lat) == 0) return '';
        if(floatval($lon) == 0) return '';

        $address_array = GeocogingCache::find_coordinate_DB($lat, $lon);
        if (!$address_array) {
            $address_array = GeocogingCache::GeoDeCoderOSM($lat, $lon);
            if (!GeocogingCache::geo_check_address($address_array)) {
                $address_array = GeocogingCache::GeoDeCoderYandex($lat, $lon);
                if (!GeocogingCache::geo_check_address($address_array)) {
                    $address_array = GeocogingCache::GeoDeCoderGeoapify($lat, $lon);
                }
            }
            if(!GeocogingCache::save_coordinate_DB($address_array, $lat, $lon)){
                return [];
            }
        }
        $address_array = GeocogingCache::find_coordinate_DB($lat, $lon);
        return $address_array;
    }

    private static function find_coordinate_DB($lat, $lon)
    {
        Router::$controller->db->query('SELECT * FROM `geocoding` WHERE `lat`=' . $lat . ' AND `lon`=' . $lon);
        while ($row = Router::$controller->db->get_row()) {
            return $row;
        }
        return false;
    }

    private static function save_coordinate_DB($address_array, $lat, $lon)
    {
        if (!GeocogingCache::geo_check_address($address_array)) return false;
        if(floatval($lat) == 0 || floatval($lon) == 0) return false;
        $address = $address_array['country'].', '.$address_array['city'].', '.$address_array['street'];
        $sql = "INSERT INTO `realty_parser`.`geocoding` (
				`lat`,
				`lon`,
				`address`,
				`code`,
				`country`,
				`region`,
				`city`,
				`district`,
				`street`,
				`house`,
				`create_time`
		) VALUES (
			'" . $lat . "',
			'" . $lon . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address)) . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address_array['code'])) . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address_array['country'])) . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address_array['region'])) . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address_array['city'])) . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address_array['district'])) . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address_array['street'])) . "',
			'" . iconv('UTF-8', 'CP1251//IGNORE',Router::$controller->db->safesql($address_array['house'])) . "',
			NOW()
		)";
        Router::$controller->db->query($sql);
        return true;
    }

    private static function geo_check_address($address_array)
    {
        if (!is_array($address_array)) return false;
        if (!isset($address_array['country']) OR (strlen($address_array['country']) == 0)) return false;
        if (!isset($address_array['city']) OR (strlen($address_array['city']) == 0)) return false;
        if (!isset($address_array['street']) OR (strlen($address_array['street']) == 0)) return false;
        return true;
    }

    private static function GeoDeCoderYandex($lat ,$lon)
    {
        $res = self::initData();
        $GeoStr = $lon . ',' . $lat;
//        $url = 'https://geocode-maps.yandex.ru/1.x/?format=json&results=1&geocode=' . $GeoStr;
        $url = 'https://geocode-maps.yandex.ru/1.x/?format=json&apikey=d1a6080f-6104-4636-afaa-1d09c1f03c63&results=1&geocode=' . $GeoStr;

        //GeocogingCache::Debug($url);
        $json = GeocogingCache::geoGetUrl($url);
        //GeocogingCache::Debug($json);
        $json = json_decode($json, true);


        $res['code']=$json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['CountryNameCode'];
        $res['country']=$json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['CountryName'];

        $Components=$json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Components'];
        if (!empty($Components)){
            foreach($Components as $ind=>$listParams){
                if ($listParams['kind']=='street'){
                    $res['street']=$listParams['name'];
                }
                if ($listParams['kind']=='house'){
                    $res['house']=$listParams['name'];
                }
                if ($listParams['kind']=='province'){
                    $res['region']=$listParams['name'];
                }
                if ($listParams['kind']=='locality'){
                    $res['city']=$listParams['name'];
                }

                if ($listParams['kind']=='district'){
                    $res['district']=$listParams['name'];
                }
            }

            if (empty($res['house'])){
                $res['house']=rand(1,150).'A';
            }
            return $res;
        }

        $res['city'] =  $json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AdministrativeArea']['Locality']['LocalityName'];
        if (empty($res['city'])){
            $res['city'] =  $json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AdministrativeArea']['SubAdministrativeArea']['Locality']['LocalityName'];
        }

        $res['street'] =  $json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AdministrativeArea']['Locality']['Thoroughfare']['ThoroughfareName'];
        if (empty($res['street'])){

            $res['street'] =  $json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AdministrativeArea']['SubAdministrativeArea']['Locality']['Thoroughfare']['ThoroughfareName'];
        }

        $res['house'] =  $json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AdministrativeArea']['Locality']['Thoroughfare']['Premise']['PremiseNumber'];
        if (empty($ret['house'])){
            $res['house']=  $json['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AdministrativeArea']['SubAdministrativeArea']['Locality']['Thoroughfare']['Premise']['PremiseNumber'];
        }
        if (empty($res['house'])){

            $res['house']=rand(1,150).'A';
        }

        return $res;
    }

    private static function GeoDeCoderOSM($lat, $lon)
    {
        $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . $lat . '&lon=' . $lon . '&addressdetails=1&accept-language=ru';
        $opts = [
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:59.0) \r\n"
            ],
            "ssl"=>[
                'verify_peer'=>false,
                'verify_peer_name'=>false,
            ]
        ];
        $context = stream_context_create($opts);
        $data = @file_get_contents($url, false, $context);
        $data = json_decode($data, true);

        $res = self::initData();

        $res['code'] = isset($data['address']['country_code']) ? strtoupper($data['address']['country_code']) : 'UA';
        $res['country'] = isset($data['address']['country']) ? $data['address']['country'] : '';
        $res['region'] = isset($data['address']['state']) ? $data['address']['state'] : '';                   // 9 область

        if(isset($data['address']['state'])) {
            $res['city'] = $data['address']['state'];        // 5 город
            $res['district'] = $data['address']['state_district'];            // 6 Район
        }

        if(isset($data['address']['city'])) {
            $res['city'] = $data['address']['city'];        // 5 город
            $res['district'] = $data['address']['city'];            // 6 Район
        }

        if(isset($data['address']['town'])) {
            $res['city'] = $data['address']['city'];        // 5 город
            $res['district'] = isset($data['address']['county']) ? $data['address']['county'] : '';            // 6 Район
        }

        if(isset($data['address']['village'])) {
            $res['city'] = $data['address']['village'];        // 5 город
            $res['district'] = isset($data['address']['county']) ? $data['address']['county'] : '';            // 6 Район
        }

        $res['street'] = isset($data['address']['road']) ? $data['address']['road'] : '';        // 7 Улица
        $res['house'] = isset($data['address']['house_number']) ? $data['address']['house_number'] : rand(1, 150) . 'A';                // 8 Дом

        if (strlen($res['city']) == 0) $res['city'] = isset($data['address']['village']) ? $data['address']['village'] : '';
        return $res;
    }

    private static function GeoDeCoderGeoapify($lat, $lon)
    {
        $res = self::initData();
        $apiKey =[
            '04fa1c062a9d4841b5a15aad9170d4b8',
            '6511bf8c9b484ef8b1e253c24552a7e1',
            '8a3949bd082b42cb8ebfd7de9c48c8c9',
            'acb1710499ff4103ae6f99623987bcb0',
        ];
        shuffle($apiKey);
        // https://www.geoapify.com/pricing
        $url = "https://api.geoapify.com/v1/geocode/reverse?lat={$lat}&lon={$lon}&apiKey={$apiKey[0]}&lang=ru";
        $data = @file_get_contents($url);
        try {
            $data = json_decode($data, true);
            $res['code'] = isset($data['features'][0]['properties']['country_code']) ? strtoupper($data['features'][0]['properties']['country_code']) : 'UA';
            $res['country'] = isset($data['features'][0]['properties']['country']) ? $data['features'][0]['properties']['country'] : '';
            $res['city'] = isset($data['features'][0]['properties']['city']) ? $res['city'] = $data['features'][0]['properties']['city'] : '';
            $res['district'] = isset($data['features'][0]['properties']['district']) ? $data['features'][0]['properties']['district'] : '';
            $res['street'] = isset($data['features'][0]['properties']['street']) ? $data['features'][0]['properties']['street'] : '';
            $res['house'] = isset($data['features'][0]['properties']['housenumber']) ? $data['features'][0]['properties']['housenumber'] : rand(1, 150) . 'A';
        }catch (Exception $e){
            return self::initData();
        }
        return $res;
    }

    private static function geoGetUrl($url)
    {
        if (strlen($url) == 0) return '';
        $response = '';

        $date_old = date("Y-m-d H:i:s", time() - (60 * 60 * 24 * 8));
        Router::$controller->db->query("SELECT * FROM " . PREFIX . "_yandex_proxy WHERE date_add>'{$date_old}' ORDER BY RAND() LIMIT 1");
        $row = Router::$controller->db->get_row();
        if (!empty($row['id'])) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, FALSE); // No need of headers
    	    curl_setopt($ch, CURLOPT_NOBODY, FALSE); // Return body
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, $row['proxy'] . ':'. $row['port']);
            if(!empty($row["password"])) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $row["login"].':'.$row["password"]);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $response= curl_exec($ch);
            $curl_info = curl_getinfo($ch);
//var_dump($curl_info);            
            curl_close($ch);
            if($curl_info['http_code'] != 200) {
                $response='';
                GeocogingCache::Debug($curl_info);
            }
        }

        return $response;
    }

    private static function initData()
    {
        return  array (
            'code' => '',
            'country' => '',
            'region' => '',
            'city' => '',
            'district' => '',
            'street' => '',
            'house' => '',
        );
    }

}