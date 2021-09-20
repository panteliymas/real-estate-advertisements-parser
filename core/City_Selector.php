<?php

class City_Selector
{
    protected $obl_city = '';

    public function __construct( $obl_city = '')
    {
        $this->obl_city = $obl_city;
    }

    public function find($text)
    {
        $text .= ' ';
        $text = str_replace('�', '�', $text);
        // ����������
        $text = str_replace('��������� ���', ' ', $text);
        $text = str_replace('�������� ����', ' ', $text);
        $text = str_replace('� ���������', ' ', $text);
        $text = str_replace('������� ������', ' ', $text);
        $text = str_replace('�������� ���', ' ', $text);
        $text = str_replace('������������ ���', ' ', $text);
        $text = str_replace('�������� ���������', ' ', $text);
        $text = str_replace('������� ���������', ' ', $text);
        $text = str_replace('�������� ���������', ' ', $text);
        $text = str_replace('������� ������', ' ', $text);
        $text = str_replace('�������� ��', ' ', $text); // �������� ����� �������� ���
        $text = str_replace('�� ��������', ' ', $text);

        if(strlen($this->obl_city) == 0){
            $this->obl_city = $this->find_obl($text);
            if($this->obl_city === false) return false;
        }

        $cities = array();
        // ������ ������� �������
        Router::$controller->db->query("SELECT `id`, `city_ua`, `city_ru`, `lat`, `lon` FROM `city` WHERE `center_obl` !=`city_ru` AND `center_obl` = '{$this->obl_city}'");
        while ($row = Router::$controller->db->get_row()) {
            $cities[] = $row;
        }

        // ���� ������ ����, �� ����� �� ��������� �����
        // ���������� ��������� ����� �� ������
        if(count($cities) == 0){
            Router::$controller->db->query("SELECT `center_obl` FROM `city` WHERE `city_ru` = '{$this->obl_city}'");
            while ($row = Router::$controller->db->get_row()) {
                $this->obl_city = $row['center_obl'];
                break;
            }
            if(strlen($this->obl_city) == 0) return false;
        }

        // ��������� ��������� ������ �������
        Router::$controller->db->query("SELECT `id`, `city_ua`, `city_ru`, `lat`, `lon` FROM `city` WHERE `center_obl` !=`city_ru` AND `center_obl` = '{$this->obl_city}'");
        while ($row = Router::$controller->db->get_row()) {
            $cities[] = $row;
        }

        $posible_city = array();
        foreach ($cities as $city) {
            $text_tmp = str_replace(' �� '.$city['city_ru'], ' ', $text);
            $text_tmp = str_replace(' � '.$city['city_ru'], ' ', $text_tmp);
            $text_tmp = str_replace(' � '.$city['city_ru'], ' ', $text_tmp);
            $text_tmp = str_replace('����� '.$city['city_ru'], ' ', $text_tmp);
            $text_tmp = str_replace('����� '.$city['city_ru'], ' ', $text_tmp);
            $text_tmp = str_replace('��. '.$city['city_ru'], ' ', $text_tmp);
            $text_tmp = str_replace('��.'.$city['city_ru'], ' ', $text_tmp);
            $text_tmp = str_replace('�� '.$city['city_ru'], ' ', $text_tmp); // �����������
            $text_tmp = str_replace('��. '.$city['city_ru'], ' ', $text_tmp);  // �������
            $text_tmp = str_replace('��.�. '.$city['city_ru'], ' ', $text_tmp); // ������� �����
            $text_tmp = str_replace('��.�.'.$city['city_ru'], ' ', $text_tmp);  // ������� �����
            $text_tmp = str_replace('����� '.$city['city_ru'], ' ', $text_tmp); // �����
            $text_tmp = str_replace('�. '.$city['city_ru'], ' ', $text_tmp); // �����
            $text_tmp = str_replace('�.'.$city['city_ru'], ' ', $text_tmp);  // �����
            $text_tmp = str_replace('������� ����', ' ', $text_tmp);  // �����
            $text_tmp = str_replace('�������� �����', ' ', $text_tmp);  // �����

            if ($text == $city['city_ru'] || preg_match('#([\s!?,:\)\.-])+(' . $city['city_ru'] . ')+([\s!?,:\)\.-])+#s', $text_tmp, $matches)) {
                echo 'City_Selector ru: '.$city['id']."\n";
                //file_put_contents('slanet.log', $text_tmp."\n\n",FILE_APPEND);
                $posible_city[] = $city;
            }
        }

        if(count($posible_city)>0){
            $posible_city = array_unique($posible_city);
            if(count($posible_city)>1){
                $res = $this->find_detail($text, $posible_city);
                if($res) return array('city'=>$res['city_ru'],'lat'=>$res['lat'],'lon'=>$res['lon']);
                return false;

            }
            return array('city'=>$posible_city[0]['city_ru'],'lat'=>$posible_city[0]['lat'],'lon'=>$posible_city[0]['lon']);
        }

        foreach ($cities as $city) {
            $text_tmp = str_replace(' �� '.$city['city_ua'], ' ', $text);
            $text_tmp = str_replace(' � '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace(' � '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('����� '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('����� '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('����� '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('��. '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('���. '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('���.'.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('�� '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('��.�. '.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('��.�.'.$city['city_ua'], ' ', $text_tmp);
            $text_tmp = str_replace('��. '.$city['city_ua'], ' ', $text_tmp); // �������
            $text_tmp = str_replace('������ ����', ' ', $text_tmp);

            if ($text == $city['city_ua'] || preg_match('#([\s!?,:\)\.-])+(' . $city['city_ua'] . ')+([\s!?,:\)\.-])+#s', $text_tmp, $matches)) {
                echo 'City_Selector ua: '.$city['id']."\n";
                $posible_city[] = $city;
            }
        }
        if(count($posible_city)>0){
            $posible_city = array_unique($posible_city);
            if(count($posible_city)>1){
                $res = $this->find_detail($text, $posible_city);
                if($res) return array('city'=>$res['city_ru'],'lat'=>$res['lat'],'lon'=>$res['lon']);
                return false;
            }
            return array('city'=>$posible_city[0]['city_ru'],'lat'=>$posible_city[0]['lat'],'lon'=>$posible_city[0]['lon']);
        }
        echo "City_Selector: not found\n";
        return false;
    }

    // ����� ����������� ������ ��������, ����� ��������, ��� ��� ��
    public function findDirect($city)
    {
        Router::$controller->db->query("SELECT * FROM `city` WHERE `center_obl` = '{$this->obl_city}' AND `city_ru` = '{$city}'");
        while ($row = Router::$controller->db->get_row()) {
            return array('city' => $row['city_ru'], 'lat' => $row['lat'], 'lon' => $row['lon']);
            break;
        }
        Router::$controller->db->query("SELECT * FROM `city` WHERE `center_obl` = '{$this->obl_city}' AND `city_ua` = '{$city}'");
        while ($row = Router::$controller->db->get_row()) {
            return array('city' => $row['city_ru'], 'lat' => $row['lat'], 'lon' => $row['lon']);
            break;
        }
        echo "City_Selector: not found\n";
        return false;
    }

    private function find_obl($text)
    {
        $obl_list = array();
        Router::$controller->db->query("SELECT `center_obl`, `obl_ua`, `obl_ru` FROM `city` GROUP BY `center_obl`");
        while ($row = Router::$controller->db->get_row()) {
            $obl_list[] = $row;
        }


        $posible_obl = array();
        foreach ($obl_list as $obl) {
            if (preg_match('#([\s!?,:\)\.-])+(' . $obl['obl_ru'] . ')+([\s!?,:\)\.-])+#s', $text, $matches)) {
                echo "=====\n";
                $posible_obl[] = $obl;
            }
        }

        if(count($posible_obl)>0){
            return $posible_obl[0]['center_obl'];
        }

        foreach ($obl_list as $obl) {
            if (preg_match('#([\s!?,:\)\.-])+(' . $obl['obl_ua'] . ')+([\s!?,:\)\.-])+#s', $text, $matches)) {
                $posible_obl[] = $obl;
            }
        }

        if(count($posible_obl)>0){
            return $posible_obl[0]['center_obl'];
        }
        return false;
    }

    private function find_detail($text, $posible_city)
    {
        foreach ($posible_city as $city) {
            if (preg_match('#(����|����|�|�|���|�����|������|���|��|���|���)([\s!?,:\)\.-])+(' . $city['city_ru'] . ')+([\s!?,:\)\.-])+#s', $text, $matches)) {
                return $city;
            }
        }

        foreach ($posible_city as $city) {
            if (preg_match('#(���|����|�|�|����|���|���|��|������|������)([\s!?,:\)\.-])+(' . $city['city_ua'] . ')+([\s!?,:\)\.-])+#s', $text, $matches)) {
                return $city;
            }
        }
        return false;
    }
}