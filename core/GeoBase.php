<?php
include_once(ROOT_DIR . 'core/http/class.http.php');
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 09.01.2021
 * Time: 20:46
 */
class GeoBase
{
    public $db;
    public $id_key;
    public $key;
    public $source;
    public $lat;
    public $lon;
    public $response;
    public $city;

    public function __construct($source)
    {
        global $db;
        $this->db = $db;
        $this->id_key = 0;
        $this->key = '';
        $this->source = $source;
        $this->lat = 0;
        $this->lon = 0;
        $this->response = '';
        $this->city = '';
    }

    public function cp1251_to_utf($text)
    {
        return iconv('CP1251', 'UTF-8', $text);
    }

    public function utf_to_1251($text, $ignore = false)
    {
        //Change from translit to ignore IGNORE
        if($ignore) {
            return iconv('UTF-8', 'CP1251//IGNORE', $text);
        }else{
            return iconv('UTF-8', 'CP1251//TRANSLIT', $text);
        }
    }

    public function getKey()
    {
        // получаем активный ключ по минимальному количеству общих запросов
        $q = $this->db->super_query("SELECT * FROM dle_geo_key WHERE `source`='{$this->source}' AND enable = 1 ORDER BY `cnt` ASC LIMIT 1");
        if (empty($q['id'])) return false;

        $this->id_key = $q['id'];
        $this->key = $q['key'];

        // если в этот день небыло запросов - создаем запись в БД
        $dt_used = date('Y-m-d');
        $geo_used = $this->db->super_query("SELECT * FROM dle_geo_used WHERE dt_used='{$dt_used}' AND id_geo='{$this->id_key}'");
        if (empty($geo_used['id'])) {
            $sql = "INSERT INTO `dle_geo_used` (`id`, `id_geo`, `dt_used`, `request`, `error`) VALUES (NULL, '{$this->id_key}', '{$dt_used}', '0', '0');";
            $this->db->query($sql);
            return true;
        }

        // проверяем на лимит запросов в день
        if (!empty($q['day_limit'])) {
            if ($q['day_limit'] > $geo_used['request']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 0 - без прокси
     * 1 - прокси статический из списка прокси для яндекса
     * 2 - прокси из списка платных, по которым работают парсера
     * @param $url
     * @param int $withProxy
     * @return mixed|string
     */
    public function geoGetUrl($url, $withProxy = 0)
    {
        $proxyIP = '';
        $proxyPort = '';
        $proxyLogin = '';
        $proxyPassword = '';
        if (strlen($url) == 0) return '';
        if ($withProxy == 1 ) {
            $q = $this->db->super_query("SELECT * FROM " . PREFIX . "_yandex_proxy ORDER BY RAND() LIMIT 1");
            if (!empty($q['id'])) {
                $proxyIP = $q['proxy'];
                $proxyPort = $q['port'];
                $proxyLogin = $q['login'];
                $proxyPassword = $q['password'];
            }
        }

        if ($withProxy == 2) {
            $http = new Http();
            $http->website_id = 1000;
            $http->thread_id = 1;
            $http->use_index = 1;
            $proxy = $http->get_proxy_pay();
            if ($proxy == false) {
                echo "PROXY IS EMPTY\n";
                return '';
            }
            $proxyIP = $proxy['ip'];
            $proxyPort = $proxy['port'];
            $proxyLogin = $proxy['proxy_login'];
            $proxyPassword = $proxy['proxy_password'];
        }

        $ch = curl_init();
        $useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, 'CURL_HTTP_VERSION_1_1');
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        if ($withProxy>0) {
            curl_setopt($ch, CURLOPT_PROXY, $proxyIP . ':' . $proxyPort);
            if (!empty($proxyPassword)) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyLogin . ':' . $proxyPassword);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Language: ru']);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);
        //var_dump($response);

        $curl_info = curl_getinfo($ch);
        curl_close($ch);
        if ($withProxy == 2) $http->clear_use_proxy_pay();

        $notFind = false;
        if ($curl_info['http_code'] != 200) {
            $notFind = true;
            $response = '';
        }
        // учет запросов + учет ошибок
        $this->incUse($notFind);

        return $response;
    }

    // подсчет количества запросов
    public function incUse($notFind)
    {
        $dt_used = date('Y-m-d');
        $geo_used = $this->db->super_query("SELECT * FROM dle_geo_used WHERE dt_used='{$dt_used}' AND id_geo='{$this->id_key}'");
        if (empty($geo_used['id'])) {
            $sql = "INSERT INTO `dle_geo_used` (`id`, `id_geo`, `dt_used`, `request`, `error`) VALUES (NULL, '{$this->id_key}', '{$dt_used}', '1', '0');";
            $this->db->query($sql);
        } else {
            $query_str = "UPDATE dle_geo_used SET `request` = `request`+1 WHERE dt_used='{$dt_used}' AND id_geo='{$this->id_key}'";
            $this->db->query($query_str);
        }

        $query_str = "UPDATE dle_geo_key SET `cnt` = `cnt`+1 WHERE id='{$this->id_key}'";
        $this->db->query($query_str);

        if ($notFind) {
            $query_str = "UPDATE dle_geo_used SET `error` = `error`+1 WHERE dt_used='{$dt_used}' AND id_geo='{$this->id_key}'";
            $this->db->query($query_str);
        }
    }

    public function isCoordinate()
    {
        if (floatval($this->lat) == 0 || floatval($this->lat) == 0) return false;
        return true;
    }

    protected function translate_city_to_ru($city_ua)
    {
        $ar = file(dirname(__FILE__).'/streets/city.txt',FILE_IGNORE_NEW_LINES);
        if(is_array($ar)) {
            foreach ($ar as $el) {
                $city = explode(':', $el);
                if (stripos($city_ua.' ', $city[1].' ') !== false) {
                    return $city[0];
                }
            }
        }
        return '';
    }
}