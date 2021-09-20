<?php
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 08.04.2021
 * Time: 14:19
 */

class AddressBase
{
    const GEO_FILE_MARK = "services/geocode/geo_inc.txt";

    public $adv_text_origin;
    public $adv_text;
    public $city;
    public $country;
    public $lat;
    public $lon;
    public $address;
    public $geocodingStep = [];

    protected $downtown;

    protected $interested_cities = array(
        ['Артемовск', '48.5877088', '37.9882629'],
        //['Барышевка', '50.3570845', '31.3127539'],
        //['Белая Церковь', '49.8080796', '30.1011931'],
        //['Березань', '50.3109516,31.4569557'],
        //['Богуслав', '49.548629', '30.871310'],
        ['Борисполь', '50.359846', '30.956078'],
        //['Борщи', '47.834128', '29.488636'], // Одеська область
        //['Бородянка', '50.643796', '29.919790'],
        ['Бровары', '50.509425', '30.799878'],
        ['Бузуков', '49.3139927', '32.0189099'], // Черкаська область
        ['Буча', '50.551826', '30.216912'],
        ['Васильков', '50.181315', '30.308203'],
        ['Вергуны', '49.3132587', '32.0808244'], // Черкаська область
        ['Винница', '49.230922', '28.461474'],
        ['Вишневое', '50.386797', '30.366916'],
        //['Володарка', '49.523245', '29.923502'],
        ['Вышгород', '50.583710', '30.485785'],
        ['Геронимовка', '49.4518044', '31.9477428'], // Черкаська область
        ['Глеваха', '50.258991', '30.319364'],
        ['Днепр', '48.463656', '35.008620'],
        //['Донецк', '48.463656', '35.008620'],
        ['Дружковка', '48.610876', '37.523429'],
        ['Житомир', '50.258285', '28.669167'],
        ['Запорожье', '47.883030', '35.108633'],
        ['Затока', '46.068141', '30.455319'],
        //['Згуровка', '50.504551', '31.779187'],
        //['Иванков', '50.938346', '29.897126'],
        ['Ирпень', '50.514967', '30.235777'],
        //['Кагарлык', '49.863157', '30.816480'],
        ['Каменское', '48.515175', '34.609889'],
        ['Каролино-Бугаз', '46.147802', '30.528664'],
        ['Каменец-Подольский', '48.686454', '26.584808'],
        ['Киев', '50.447106', '30.524805'],
        ['Киево-Святошинский', '50.404566', '30.202788'],
        ['Козин', '50.226884', '30.652451'],
        ['Константиновка', '48.530720', '37.704383'],
        ['Краматорск', '48.719071', '37.564310'],
        ['Красная Слобода', '49.3791611', '32.1386515'], // Черкаська область
        ['Кременчуг', '49.079841', '33.431072'],
        ['Кривой Рог', '47.908663', '33.388136'],
        ['Кропивницкий', '48.511343', '32.264288'],
        ['Львов', '49.836847', '24.011477'],
        ['Луцк', '50.754526', '25.349208'],
        ['Макаров', '50.460565', '29.810646'],
        ['Мариуполь', '47.106686', '37.559341'],
        //['Мироновка', '49.657755', '30.981879'],
        ['Николаев', '46.961999', '32.002944'],
        ['Новые Петровцы', '50.627935', '30.440887'],
        ['Обухов', '50.111609', '30.629715'],
        ['Одесса', '46.452563', '30.706827'],
        ['Переяслав-Хмельницкий', '50.074142', '31.447501'],
        ['Петропавловская Борщаговка', '50.433628', '30.328688'],
        ['Полтава', '49.600269', '34.544184'],
        //['Полесское', '51.240531', '29.386031'],
        //['Ракитное', '49.689141', '30.476230'],
        ['Ржищев', '49.968110', '31.041374'],
        ['Ровно', '50.620792', '26.234712'],
        ['Русская Поляна', '49.4189007', '31.914532'], // Черкаська область
        ['Святогорск', '49.040484', '37.569858'],
        ['Севастополь', '44.573044', '33.570632'],
        //['Сквирa', '49.730065', '29.661831'],
        ['Славутич', '51.521947', '30.748786'],
        ['Славянск', '48.851192', '37.606238'],
        ['Софиевская Борщаговка', '50.413001', '30.357383'],
        //['Ставище', '9.390888', '30.191754'],
        ['Старые Петровцы', '50.651000', '30.410639'],
        ['Сумы', '50.921967', '34.807003'],
        //['Степанки', '49.3227022', '31.9842271'], // Черкаська область
        //['Тараща', '49.561116', '30.499352'],
        ['Тернополь', '49.561948', '25.607486'],
        //['Тетиев', '49.369831', '29.666480'],
        //['Фастов', '50.078553', '29.907599'],
        ['Харьков', '49.994903', '36.274974'],
        //['Хацки', '49.3009013', '31.9428048'], // Черкаська область
        ['Херсон', '46.649517', '32.589931'],
        ['Хмельницкий', '49.421139', '26.976501'],
        ['Хотяновка', '50.607034', '30.571893'],
        //['Хутора', '49.3734056', '32.0224572'], // Черкаська область
        //['Чайки', '50.440808', '30.285358'],
        ['Черкассы', '49.4448515', '32.058132'],
        ['Черновцы', '48.2916347', '25.9355246'],
        ['Чернигов', '51.501765', '31.295947'],
        ['Червоноград', '50.391273', '24.233419'],
        ['Южный', '46.622920', '31.100650'],
        //['Яготин', '50.248322', '31.788696'],
        //['Ялта', '44.502855', '34.168834'],

        ['Северодонецк', '48.949669', '38.493106'],
        ['Лисичанск', '48.880290', '38.466235'],
        ['Покровск', '48.284542', '37.172493'], 		// Красноармейск
        ['Красноармейск', '48.282825', '37.174361'], 	// Покровск
        ['Бахмут', '48.596470', '37.996830'], 			// Артемовск
        ['Доброполье', '48.472971', '37.081043'],
        ['Изюм', '49.187112', '37.282894'],
    );


    // $adv_text в форматі windows-1251
    public function __construct($adv_text ='', $city = '', $country = 'Украина')
    {
        $this->adv_text = $adv_text;
        $this->adv_text_origin = $adv_text;
        $this->city = $city;
        $this->country = $country;

        // изначально координаты в центре города
        $coor = $this->get_coordinate_city($this->city);
        $this->lat = $coor['lat'];
        $this->lon = $coor['lon'];
        $this->downtown = true;
    }

    protected function get_city_street($city)
    {
        switch ($city) {
            case 'Славянск':
                $ar = file(dirname(__FILE__).'/streets/slavyansk_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Краматорск':
                $ar = file(dirname(__FILE__).'/streets/kramatorsk_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Киев':
                $ar = file(dirname(__FILE__).'/streets/kiev_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Харьков':
                $ar = file(dirname(__FILE__).'/streets/kharkov_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Днепр':
                $ar = file(dirname(__FILE__).'/streets/dnepr_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Херсон':
                $ar = file(dirname(__FILE__).'/streets/kherson_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Мариуполь':
                $ar = file(dirname(__FILE__).'/streets/mariupol_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Бровары':
                $ar = file(dirname(__FILE__).'/streets/brovary_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Дружковка':
                $ar = file(dirname(__FILE__).'/streets/druzhkovka_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Ирпень':
                $ar = file(dirname(__FILE__).'/streets/irpin_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Хмельницкий':
                $ar = file(dirname(__FILE__).'/streets/khmelnitski_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Константиновка':
                $ar = file(dirname(__FILE__).'/streets/kostiantinovka_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Кропивницкий':
                $ar = file(dirname(__FILE__).'/streets/kropyvnytckyj_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Львов':
                $ar = file(dirname(__FILE__).'/streets/lvov_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Николаев':
                $ar = file(dirname(__FILE__).'/streets/nikolaev_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Одесса':
                $ar = file(dirname(__FILE__).'/streets/odessa_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Ровно':
                $ar = file(dirname(__FILE__).'/streets/rovno_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Севастополь':
                $ar = file(dirname(__FILE__).'/streets/sevastopol_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Винница':
                $ar = file(dirname(__FILE__).'/streets/vinnitsa_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Вышгород':
                $ar = file(dirname(__FILE__).'/streets/vishgorod_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Вишневое':
                $ar = file(dirname(__FILE__).'/streets/vishneve_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Запорожье':
                $ar = file(dirname(__FILE__).'/streets/zaporoje_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Житомир':
                $ar = file(dirname(__FILE__).'/streets/zhytomyr_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Кременчуг':
                $ar = file(dirname(__FILE__).'/streets/kremenchug_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Васильков':
                $ar = file(dirname(__FILE__).'/streets/vasylkov_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Черновцы':
                $ar = file(dirname(__FILE__).'/streets/chernivtsi_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Черкассы':
                $ar = file(dirname(__FILE__).'/streets/cherkassy_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Каменец-Подольский':
                $ar = file(dirname(__FILE__).'/streets/kampod_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Кривой Рог':
                $ar = file(dirname(__FILE__).'/streets/krivoy_rog_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Червоноград':
                $ar = file(dirname(__FILE__).'/streets/chervonograd_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Чернигов':
                $ar = file(dirname(__FILE__).'/streets/chernigov_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Полтава':
                $ar = file(dirname(__FILE__).'/streets/poltava_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Сумы':
                $ar = file(dirname(__FILE__).'/streets/sumy_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Тернополь':
                $ar = file(dirname(__FILE__).'/streets/ternopol_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Каменское':
                $ar = file(dirname(__FILE__).'/streets/kamenskoe_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            case 'Луцк':
                $ar = file(dirname(__FILE__).'/streets/lutsk_street.txt',FILE_IGNORE_NEW_LINES);
                break;
            default :
                $ar = array();
                break;
        }
        return $ar;
    }

    public function get_city_district($city)
    {
        switch ($city) {
            case 'Киев':
                $dist = file(dirname(__FILE__) . '/streets/kiev_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Днепр':
                $dist = file(dirname(__FILE__) . '/streets/dnepr_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Херсон':
                $dist = file(dirname(__FILE__) . '/streets/kherson_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Мариуполь':
                $dist = file(dirname(__FILE__) . '/streets/mariupol_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Хмельницкий':
                $dist = file(dirname(__FILE__) . '/streets/khmelnitski_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Николаев':
                $dist = file(dirname(__FILE__) . '/streets/nikolaev_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Одесса':
                $dist = file(dirname(__FILE__) . '/streets/odessa_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Севастополь':
                $dist = file(dirname(__FILE__) . '/streets/sevastopol_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Запорожье':
                $dist = file(dirname(__FILE__) . '/streets/zaporoje_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Васильков':
                $dist = file(dirname(__FILE__) . '/streets/vasylkov_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Черкассы':
                $dist = file(dirname(__FILE__) . '/streets/cherkassy_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Винница':
                $dist = file(dirname(__FILE__) . '/streets/vinnitsa_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            case 'Кривой Рог':
                $dist = file(dirname(__FILE__) . '/streets/krivoy_rog_district.txt', FILE_IGNORE_NEW_LINES);
                break;
            default :
                $dist = array();
                break;
        }

        return $dist;
    }

    public function getIsDowntown()
    {
        return $this->downtown;
    }

    public function getInterestedCities()
    {
        return $this->interested_cities;
    }

    public function check_coordinate($city, $lat, $lon){
        $coor = $this->get_coordinate_city($city);
        if ($this->calcdistance($lat, $lon, $coor['lat'], $coor['lon']) < 50000) return true;
        return false;
    }

    public function get_coordinate_city($city) {
        $coor=array('lat'=>0, 'lon'=>0);
        foreach($this->interested_cities as $el){
            if($city == $el[0]) {
                $coor['lat'] = $el[1];
                $coor['lon'] = $el[2];
                break;
            }
        }
        return $coor;
    }

    public function geocodingStepAsString()
    {
        $str = '';
        foreach ($this->geocodingStep as $key => $val) {
            if(!empty($val)) $str .= $key . ': ' . $val . "\n";
        }
        return $str;
    }

    // Функция определения расстояния между точками
    public static function calcdistance($lat1, $lon1, $lat2, $lon2){
        $pi_div_180 = pi() / 180.0;
        $d_fak = 6371000.0;
        $d2 = 2.0;
        $latx = $lat1 * $pi_div_180;
        $lonx = $lon1 * $pi_div_180;
        $laty = $lat2 * $pi_div_180;
        $lony = $lon2 * $pi_div_180;
        $sinlat = sin(($latx - $laty) / $d2);
        $sinlon = sin(($lonx - $lony) / $d2);
        return $d2 * asin(sqrt($sinlat * $sinlat + $sinlon * $sinlon * cos($latx) * cos($laty))) * $d_fak;
    }

    protected function log($arr)
    {
        if (is_array($arr)) {
            array_walk($arr, function (&$value, &$key) {
                if(!empty($value)) $value = iconv('CP1251', 'UTF-8', $value);
            });
        } else {
            $arr = iconv('CP1251', 'UTF-8', $arr);
        }
        if (!empty($arr)) {
            file_put_contents('address.log', print_r($arr, true) . "\n", FILE_APPEND);
        } else {
            file_put_contents('address.log', "EMPTY\n", FILE_APPEND);
        }
    }

    public function isCoordinate()
    {
        if ($this->downtown) return false;
        return true;
    }

    // выдача первого найденого адреса по алгоритму. Нужно для тестирования
    public function firstFindAddress()
    {
        if (is_array($this->geocodingStep)) {
            foreach ($this->geocodingStep as $key => $responce) {
                if ($key == 'forCity') continue;
                if (empty($responce)) continue;
                return $responce;
            }
        }
        return '';
    }
}