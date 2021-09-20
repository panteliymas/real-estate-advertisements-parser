<?php
include_once ROOT_DIR . 'core/AddressBase.php';
include_once ROOT_DIR . 'core/GeoYandex.php';
include_once ROOT_DIR . 'core/GeoGoogle.php';
include_once ROOT_DIR . 'core/GeoWebGoogle.php';
include_once ROOT_DIR . 'core/GeoOSM.php';
include_once ROOT_DIR . 'core/GeoLocalDB.php';
/**
 * Created by PhpStorm.
 * User: basil
 * Date: 08.04.2021
 * Time: 13:00
 * ������ ������ ���������
 * ������ � ������� ������� ������ �� ������ ����� � �����������, ��� �������� ���������� �����
 */

class Address extends  AddressBase
{
    public function extractCoordinate($geocoding = true, $debug = false)
    {
        $this->adv_text = $this->prepareTest($this->adv_text);
        if(empty($this->adv_text)) return false;
        $this->geocodingStep['forCity'] =  $this->city;
        echo 'forCity: ' .  iconv('CP1251', 'UTF-8', $this->city)."\n";

        $this->findInText();
        $this->geocodingStep['findInText'] =  $this->address;
        echo 'findInText: ' .  iconv('CP1251', 'UTF-8', $this->address)."\n";

        if($debug) $this->log('===============');
        // ���������� ���������� �� ������
        if ($geocoding && $this->isFindCoordinate()) return true;

        if($this->city == '������') {
            $this->findFixAddressOdessa_01();
            echo 'findFixAddressOdessa_01: ' .  iconv('CP1251', 'UTF-8', $this->address)."\n";
            $this->geocodingStep['findFixAddressOdessa_01'] =  $this->address;
            if(!empty($res) && $geocoding && $this->isFindCoordinate()) return true;
        }

        // ���������� �� ������ �� ������� ���� �� ����������, ��� ��� �� ��� ��� ���� ���������� ����������
        $this->findInMark();
        echo 'findInMark: ' .  iconv('CP1251', 'UTF-8', $this->address)."\n";
        $this->geocodingStep['findInMark'] =  $this->address;
        if ($geocoding && $this->check_coordinate($this->city, $this->lat, $this->lon) && !$this->getIsDowntown()) return true;

        // ���������� �� ������ �� ������� ���� �� ������ ����
        $city_street = $this->get_city_street($this->city);
        $this->findInStreet($city_street);
        echo 'findInStreet: ' .  iconv('CP1251', 'UTF-8', $this->address)."\n";
        $this->geocodingStep['findInStreet'] =  $this->address;
        if ($geocoding && $this->isFindCoordinate()) return true;

        $city_district = $this->get_city_district($this->city);
        $this->findInDistrict($city_district);
        echo 'findInDistrict: ' .  iconv('CP1251', 'UTF-8', $this->address)."\n";
        $this->geocodingStep['findInDistrict'] =  $this->address;
        if ($geocoding && $this->check_coordinate($this->city, $this->lat, $this->lon) && !$this->getIsDowntown()) return true;

        if($debug) $this->log($this->geocodingStep);
        return false;
    }

    private function isFindCoordinate()
    {
        if (!empty($this->address)) {
            if ($this->geocoding($this->address)) {
                // ���� ��������� ���������� �� ������ �� ������ ������
                if ($this->check_coordinate($this->city, $this->lat, $this->lon)) return true;
            }
        }
        return false;
    }

    // ���������� ������
    private function prepareTest($text)
    {
        $excluded_ar =[
            '/������\w*\s+������\w*\W+/si',
            '/���\w*\s+������\w*\W+/si',
            '/���\w*\s+������\w*\W+/si',
            '/������\w*\s+����\w*\W+/si',
            '/������\w*\s+����\w*\W+/si',
            '/������\w*\s+�����\w*\W+/si',
            '/������\w*\s+�������\w*\W+/si',
            '/��������\s+\d+/s',
            '/������\s+/si',
            '/������\s+/si',
            '/[�-߲��]\./s',
            '/ ���\W/s',
        ];

        $delWord = [
            '����������',
            '�������������',
            '��������� ����������',
            '����� �������',
            '� �����',
            '����� ����',
            '�� ����� �����',
            '� ������������',
            '����� �������',
            '������� �����',
            '- ',
            ' -',
            '����� ',
            '�� ����� ',
            '��. ��������',
            '���� ',
            '��������',
            '����',
            '����',
            '����������',
            '�������',
            '��������',
            '����',
        ];
        $delWordCase = [
            '��.',
            '�.',
            ' � ',
        ];

        $res = preg_replace($excluded_ar, '', $text);
        $res = str_ireplace($delWord,' ',$res);
        $res = str_replace($delWordCase,' ',$res);

        $res = str_replace(',', ', ', $res); // �������� ������, ���� ��� ���. ���� �� ���� - �� ����� ������ ������

        $res = str_replace('��.', '����� ', $res); // �������� ������, ���� ��� ���. ���� �� ���� - �� ����� ������ ������
        $res = str_replace('��.', '����� ', $res); // �������� ������, ���� ��� ���. ���� �� ���� - �� ����� ������ ������
        $res = str_replace('�� ', '����� ', $res); // �������� ������, ���� ��� ���. ���� �� ���� - �� ����� ������ ������
        $res = str_replace(' �� .', ' ����� ', $res);
        $res = str_replace(' �� ', ' ����� ', $res);
        $res = str_replace('����� ', '����� ', $res);

        $res = str_replace('���.', '������ ', $res); // �������� ������, ���� ��� ���. ���� �� ���� - �� ����� ������ ������
        $res = str_replace(' ��� ', '������ ', $res); // �������� ������, ���� ��� ���. ���� �� ���� - �� ����� ������ ������
        $res = str_replace('���.', '������ ', $res); // �������� ������, ���� ��� ���. ���� �� ���� - �� ����� ������ ������

        $res = str_replace(' �� ', ' �������� ', $res);
        $res = str_replace(' �� .', ' �������� ', $res);
        $res = str_replace(' ��.', ' �������� ', $res);
        $res = str_replace('��. ', '�������� ', $res);
        $res = str_replace('����� ', '�������� ', $res);
        $res = str_replace('�����. ', '�������� ', $res);
        $res = str_replace('��-�', '�������� ', $res);

        $res = str_replace('���.', '�������� ', $res);
        $res = str_replace('��������', '�������� ', $res);
        $res = str_replace('���.', '�������� ', $res);

        $res = str_replace('��.', '������� ', $res);

        $res = str_replace('�����.', '������� ', $res);
        $res = str_replace('���.', '������� ', $res);
        $res = str_replace('�-�', '������� ', $res);

        $res = str_replace('��-�', '������� ', $res);

        $res = str_replace('��� ', '���������� ', $res);
        $res = str_replace('���.', '���������� ', $res);

        $res = str_ireplace('�����', '����� ', $res);
        $res = str_ireplace('�-�', '����� ', $res);

        $listTypeStreet = [
            '������',
            '�����',
            '�����',
            '�����',
            '��������',
            '���������',
            '���������',
            '�����',
            '��������',
            '��������',
            '�������',
            '��������',
            '��������',
            '�������',
            '�������',
            '�����',
            '������',
            '�����',
            '����',
            '������',
            '����������',
            '����������',
            '�������',
            '�����',
            '����'
        ];

        // ���������� ������� ����� �����, �������, ��������.... �� ��������� � ������ �����. �-�
        // ���� ������ ����� � ������ ������ �� ������� ����� ����� ������, ���� �������� - �� �����
        // �������� ����� ��������� ����� ��������� - ����� ������� ����� ���������
        // �������� ����� ��������� ����� ���������  - ����� ������� ��������� �����
        // ��������� ������ �� 2 ����� �� ����� "�����"
        if(strpos($res, '�����')) {
            // ������� �� ������ �����, ����� �� ���� ������ ������������, ���� ������������ ������ ����� �� ��� � ������������ �����
            $resArray = explode('�����', $res);
            if (preg_match('/('.implode('|', $listTypeStreet).')/s', $resArray[0], $district)) {
                // ���� "�����" ������ �� ������������  �����
                if (preg_match('/(�����)\W+([�-߲��][�-��-�����\'\.-]{3,30})/s', $res, $district)) {
                    $res = str_replace($district[0], '', $res);
                }
            }else {
                if (preg_match('/([�-߲��][�-��-�����\'\.-]{3,30})\W+(�����)/s', $res, $district)) {
                    $res = str_replace($district[0], '', $res);
                }
            }
        }
        // �������� ����� (��� ���������) - ��� ��� ��������� �-� ������������ �� ���������  ��.
        $res = preg_replace('/�����: (.*?);/', '', $res);

        // ��� ������ ����� ���������� �������� (������� �� ����� ������, ���������, ������) ����� ������������ ������
        return preg_replace('|\s+|', ' ', $res);
    }

    private function findFixAddressOdessa_01()
    {
        $this->address = '';
        // �������������� ��������� ������������� ������� / 10 ������� �. ������� / �� 16 �������
        // ���� � ������ ���� ����� "�������"
        if(stripos($this->adv_text, '�������') !== false) {
            $arrayWord = explode(' ', $this->adv_text);
            // ���� ��������� ����� ����� �� ����� "�������"
            $digit = 0; // ����� �������
            // ���������� ������� ����, ��� ���������� ���������� (� ������) ����� ����. ���� ����� ������ - �� ��� �� ��� �����
            $num_fontan = 0; // ����� ������� ����� "�������"
            $num_digit = 0; // ����� ������� �����
            foreach ($arrayWord as $key => $word) {
                if (stripos($word, '�������') !== false) {
                    $num_fontan = $key;
                    break;
                }
                if (intval($word) == 0) continue;
                $digit = intval($word);
                $num_digit = $key;
            }
            if ($digit > 0 && ($num_fontan - $num_digit) < 5) {
                $this->address = $digit . ' ������� �������� �������';
            }
        }
    }

    // ��������� �� ������ �����
    private function findInText()
    {
        $this->address='';

        $listTypeStreet = [
            '������',
            '�����',
            '�����',
            '�����',
            '��������',
            '���������',
            '���������',
            '�����',
            '��������',
            '��������',
            '�������',
            '��������',
            '��������',
            '�������',
            '�������',
            '�����',
            '������',
            '�����',
            '����',
            '������',
            '����������',
            '����������',
            '�������',
            '�����',
            '����'
        ];
        //[1-9]{0,2}\s*[�-�]+[�-��-]{2,25} - was before. Changed due to ul. M.Malinovskogo 13,
        if(preg_match('/('.implode('|', $listTypeStreet).')\s*([1-9]{0,2}\s*[�-߲��]+[�-�����\'-]{0,25}[ \.]{0,2}[�-߲���-�����\'-]{0,25}[ \.,]{0,5}(���|�������|���\.|�\.|�|�)?\s*[\d\/]*[�-߲���-�����\']?)/si',$this->adv_text,$m2)){
            //$this->log('1');
            //$this->log($m2);
            $m2[2] = str_replace('.', ',', $m2[2]);
            $st = explode(',', $m2[2]);
            // ���� ���� ������� ����� � ������, ������ ��� ����� ���� �����. ���� ��� - ����� �������, ��� ����� ���
            $st[0] = $this->correct_address($st[0], ' ');
            if(strtolower( $st[0]) !=  $st[0] && strlen($st[0])>3) {
                $this->address = $this->correct_type_street($this->country . ', ' . $this->city . ', ' . $m2[1] . ' ' . $st[0]);
            }
        }

        if(strlen($this->address) == 0) {
            if (preg_match('/([�-߲��1-9][�-��-�����\' \.-]{3,30})\W('.implode('|', $listTypeStreet).')[\D]?\s*([\d\/]*[�-߲���-�����\']?)/s', $this->adv_text, $m2)) {//removed /U switch in preg
                //$this->log('2');
                //$this->log($m2);
                // ���� ���� ������� ����� � ������, ������ ��� ����� ���� �����. ���� ��� - ����� �������, ��� ����� ���
                if (strtolower($m2[1]) != $m2[1]) {
                    $this->address = $this->correct_type_street($this->country . ', ' . $this->city . ', ' . $m2[1] . ' ' . $m2[2]);
                    $mn = preg_replace('/\D/', '', $m2[3]);
                    if(!empty($mn)) $this->address .= ' �.' . $m2[3];
                }
            }
        }

        $found = array('�-�', '��-��', '��.�', '"', '������');
        $repl = array('�����', '�����', '�����', '', '');

        $this->adv_text = str_replace($found, $repl, $this->adv_text);
        if(strlen($this->address) == 0) {
            if (preg_match('/\W(�����)\s*([1-9]{0,2}\s*[�-߲��]+[�-�����\'-]{0,25}[ \.]{0,2}[�-߲���-�����\'-]{0,25})/si',$this->adv_text, $m2)){
                $this->address=Trim($this->country . ', ' . $this->city . ', ����� '.$m2[2]);
            }
        }
        $this->address = $this->correct_address($this->address, ' ');
    }


    public function findInStreet($streets)
    {
        $listExludeStreet = [
            '�������',
            '�������',
            '������',
            '�������',
            '������',
            '������',
            '�����',
            '������',
            '��������',
            '������',
            '������',
            '�������',
        ];
        $this->address = '';
        // ������� ��� ����� ����������, ����� ������ ������������ � �������� (������ ���������)
        $this->adv_text = preg_replace('/\W/', ' ', $this->adv_text).' ';

        if(is_array($streets)) {
            // ������ ��� �������.
            // 1- ����������� ��� ����� ����� �������� �����;
            // 2- ����������� ��� ����� ����� �������� ������;
            // 3- �� ����������� ��� ����� ������
            for ($i = 0; $i < 3; $i++) {
                foreach ($streets as $str0) {
                    $str2 = explode(',', $str0);
                    $str3 = trim($str2[0]);

                    // ���������� ������������ ����, ������ ����� ������������ �� �������
                    // ������ ��� �����, � �� � ���������� ������������ ������, ��� ��� � ������ ����� ���� �������� ����� �������, ��� ��� ���������
                    if(in_array($str3, $listExludeStreet)) continue;


                    if (strlen($str3) > 0) {
                        if (($p2 = strpos($this->adv_text, $str3 . ' ')) !== false && preg_match('/' . $str3 . '\s*���/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                            $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                            break;
                        }
                    }

                    // ������������ �������� ���� � ������� "�� ���������� ���������" "�� ������������� �����"
                    $sub_street = explode(' ', $str2[0]);
                    $str[0] = '';
                    $str[1] = '';
                    foreach ($sub_street as $key => $val) {
                        if (strlen($val) > 4) {
                            $sub_str = trim($val);
                            //$this->log($sub_str);
                            $ok = substr($sub_str, -2);
                            if (isset($str2[2]) AND (trim($str2[2]) == '���')) {
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                            } else {
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '���', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                                if ($ok == '��') $str[$key] = substr_replace($sub_str, '��', -2, 2);
                            }
                        }
                    }
                    $str3 = trim($str[0] . ' ' . $str[1]);
                    if (strlen($str3) > 0) {
                        $type_street = trim($str2[1], ' .');
                        //$this->log('0 ------ ' . $type_street . ' ' . $str3);
                        // ������ ��� ��������� �������.
                        // ����������� ��� ����� ����� ����� � ���� � ���, ����� ������ ��� ����� � ������
                        if ($i == 0) {
                            if (($p2 = stripos($this->adv_text, $str3 . ' ' . $type_street)) !== false && preg_match('/' . $str3 . '\s*���/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                                $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                                //$this->log('1 ------ ' . $this->address);
                                $this->address = $this->prepareTest($this->address);
                                break;
                            }
                        }
                        // ����������� ��� ����� ����� ������ � ���� � ���, ����� ������ ��� ����� � ������
                        if ($i == 1) {
                            $str3 = str_replace('(', '\\(', $str3);
                            $str3 = str_replace(')', '\\)', $str3);
                            if (preg_match('/ (' . $type_street . '[�-�����\']+) ' . $str3 . '/si', $this->adv_text) != 0 && preg_match('/' . $str3 . '\s*���/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                                $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                                //$this->log('2 ------ ' . $type_street . ' ' . $str3);
                                //$this->log('2 ------ ' . $this->address);
                                $this->address = $this->prepareTest($this->address);
                                break;
                            }
                        }
                        // ���� ���������� ��� ���� �����, ������ �� ��������
                        if ($i == 2) {
                            if (($p2 = stripos($this->adv_text, $str3)) !== false && preg_match('/' . $str3 . '\s*���/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                                $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                                //$this->log('3 ------ ' . $this->address);
                                $this->address = $this->prepareTest($this->address);
                                break;
                            }
                        }
                    }
                }
            }

            if (isset($p2) && $p2 !== false && preg_match('/' . $str2 . '[�\.\s,]+(\d{1,3})([^�\|\/\\\]|$)/si', substr($this->adv_text, $p2), $m) && !empty($m[1])) {
                // ���� ���� ����� ����. ��������� ������� ����
                $mn = preg_replace('/\D/', '', $m[1]);
                if(!empty($mn)) $this->address .= ' �.' . $m[1];
                return;
            }
        }
    }

    public function findInMark()
    {
        $this->address='';
        $geo_lines = file(ROOT_DIR . self::GEO_FILE_MARK, FILE_IGNORE_NEW_LINES);
        foreach ($geo_lines as $geo_line) {
            $geo_str = explode("|", $geo_line);
            $address = trim($geo_str[1]);
            if(strlen($address) < 2) continue;
            if (Trim($geo_str[0]) == $this->city AND (strpos($this->adv_text, $address) !== false)) {
                $this->log('==>'.$address.'<===');
                $geo_new = explode(',', trim($geo_str[2]));
                $this->address = $address;
                $this->lat = trim($geo_new[0]); // ������
                $this->lon = trim($geo_new[1]); // �������
                $this->downtown = false;
                return;
            }
        }
    }

    public function findInDistrict($dist)
    {
        $this->address='';
        if (count($dist) == 0) return;
        if (is_array($dist)) {
            foreach ($dist as $el) {
                $mark = explode(',', $el); // 0 ������� ��������; 1 - lat; 2- lon
                $district = trim($mark[0]);
                if (strlen($district) > 0) {
                    if (strpos($this->adv_text_origin, $district) !== false) {
                        $this->address = $district;
                        $this->lat = trim($mark[1]); // ������
                        $this->lon = trim($mark[2]); // �������
                        $this->downtown = false;
                        return;
                    }
                }
            }
        }
    }

    public function geocoding($address)
    {
        $address = trim($address);
        $address = strip_tags($this->correct_address($address));
        $localDB = new GeoLocalDB();
        $localDB->get_coordinate($address);

        $geocoded = 1; // ID �������, ������� ������� ����������

        // ����� � ��������� �� - ���������� �������� �� �����
        if ($localDB->isCoordinate()) {
            $this->lat = $localDB->lat;
            $this->lon = $localDB->lon;
            $this->downtown = false;
            // ���� �� ������ ����� � ������������, �������� ��� ����� �� ������� ������ ���������� ���������� � ������������ ������ �� ����������� ����������
            if(empty($this->city)) $this->city = $localDB->city;
            return true;
        }

        // ����� ����� �� ����� � ��������� � �� ��������� ����������

        echo "Address in local DB not found. Try in OSM\n";
        $osm = new GeoOSM();
        $osm->get_coordinate($address);
        if (!$osm->isCoordinate()) {
            echo "Address in OSM not found. Try in Yandex\n";
            $yandex = new GeoYandex();
            $yandex->get_coordinate($address);
            if ($yandex->isCoordinate()) {
                $geocoded = $yandex->id_key;
                $this->lat = $yandex->lat;
                $this->lon = $yandex->lon;
                $this->downtown = false;
                if(empty($this->city)) $this->city = $yandex->city;
            }
        }else{
            $geocoded = $osm->id_key;
            $this->lat = $osm->lat;
            $this->lon = $osm->lon;
            $this->downtown = false;
            if(empty($this->city)) $this->city = $osm->city;
        }

        if (!$this->isCoordinate()) {
            echo "Address in Yandex not found. Try in WebGoogle\n";
            $web_google = new GeoWebGoogle();
            $web_google->get_coordinate($address, $this->city);
            if ($web_google->isCoordinate()) {
                $geocoded = $web_google->id_key;
                $this->lat = $web_google->lat;
                $this->lon = $web_google->lon;
                $this->downtown = false;
            }
        }

        if (!$this->isCoordinate()) {
            echo "Address in GeoWebGoogle not found. Try in Google\n";
            $google = new GeoGoogle();
            $google->get_coordinate($address);
            if ($google->isCoordinate()) {
                $geocoded = $google->id_key;
                $this->lat = $google->lat;
                $this->lon = $google->lon;
                $this->downtown = false;
                if(empty($this->city)) $this->city = $google->city;
            }
        }

        if (!$this->isCoordinate()) {
            echo "Address not found in geo services and local DB\n";
            file_put_contents(__DIR__ . '/../wrong_address.log', $address . "\n", FILE_APPEND);
            return false;
        }

        // �������� �� ����� ������. ����� �� ���������, ��� ��� ��� ������� ����� �� ������ ����������
        foreach ($this->interested_cities as $el) {
            if (($el[1] == $this->lat) AND ($el[2] == $this->lon)) return false;
        }
        $localDB->save_address_DB($address, $this->lat, $this->lon, $this->city, $geocoded);
        return true;
    }

    // ������� �������� �����
    public function correct_address($addr, $delimiter = ',')
    {
        $res = array();
        $a = explode($delimiter, $addr);
        if (count($a) > 1) {
            foreach ($a as $el) {
                if (strlen($el) > 2) {
                    $res[] = $el;
                } else {
                    $digit = preg_replace('/\D/', '', $el);
                    if (strlen($digit) > 0) $res[] = $el;
                }
            }
        } else {
            return $addr;
        }
        return implode($delimiter, $res);
    }

    public function correct_type_street($addr)
    {
        $found = array('�����', '���������', '��������', '������', '��������');
        $repl = array('�����', '��������', '�������', '������', '��������');
        return str_replace($found, $repl, trim($addr, '/'));
    }
}