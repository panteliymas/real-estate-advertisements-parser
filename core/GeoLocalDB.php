<?php
include_once ROOT_DIR . 'core/GeoBase.php';
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 09.01.2021
 * Time: 16:52
 * Îáë³ê çàïèò³â äî ãóãëà
 */

class GeoLocalDB extends GeoBase
{
    public function __construct()
    {
        parent::__construct('localDB');
    }

    public function get_coordinate($address)
    {
        if($this->getKey() === false) return;

        $q=$this->db->super_query("SELECT `city`, `latitude`,`longitude` FROM `address` WHERE address='".$this->db->safesql($address)."'");
        if(!empty($q['latitude'])){
            echo "address has found in DB \n";
            $this->lon = floatval($q['longitude']); // äîëãîòà
            $this->lat = floatval($q['latitude']); // øèğîòà
            $this->city =  $q['city'];
        }
        $this->incUse($this->lat == 0); // åñëè êîîğäèíàòû íå íàéäåíû
        return;
    }

    public function save_address_DB( $address, $lat, $lon, $city, $geocoded = 1 ) {
        if(strlen($address) == 0) return false;
        if(floatval($lat) == 0) return false;
        if(floatval($lon) == 0) return false;
        echo "save address to DB \n";
        $sql = "INSERT INTO `address` (`city`, `address`, `latitude`, `longitude`, `geocoded`, `geocoded_date`) VALUES ('".$this->db->safesql($city)."', '".$this->db->safesql($address)."', '".str_replace(',','.',$lat)."', '".str_replace(',','.',$lon)."', ".$geocoded.", NOW())";
        $this->db->query($sql);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
}