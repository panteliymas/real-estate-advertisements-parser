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
 * модуль видачі координат
 * вибирає з текстту можливу адресу та виконує пошук в геосистемах, щоб отримати координати обєкта
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
        // определяем координаты по адресу
        if ($geocoding && $this->isFindCoordinate()) return true;

        if($this->city == 'Одесса') {
            $this->findFixAddressOdessa_01();
            echo 'findFixAddressOdessa_01: ' .  iconv('CP1251', 'UTF-8', $this->address)."\n";
            $this->geocodingStep['findFixAddressOdessa_01'] =  $this->address;
            if(!empty($res) && $geocoding && $this->isFindCoordinate()) return true;
        }

        // координаты по адресу не найдены ищем по ориентирам, так как по ним уже есть правильные координаты
        $this->findInMark();
        echo 'findInMark: ' .  iconv('CP1251', 'UTF-8', $this->address)."\n";
        $this->geocodingStep['findInMark'] =  $this->address;
        if ($geocoding && $this->check_coordinate($this->city, $this->lat, $this->lon) && !$this->getIsDowntown()) return true;

        // координаты по адресу не найдены ищем по списку улиц
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
                // если найденные координаты не далеко от центра города
                if ($this->check_coordinate($this->city, $this->lat, $this->lon)) return true;
            }
        }
        return false;
    }

    // Подготовка текста
    private function prepareTest($text)
    {
        $excluded_ar =[
            '/торгов\w*\s+площад\w*\W+/si',
            '/общ\w*\s+площад\w*\W+/si',
            '/жил\w*\s+площад\w*\W+/si',
            '/площад\w*\s+кухн\w*\W+/si',
            '/площад\w*\s+дома\w*\W+/si',
            '/площад\w*\s+участ\w*\W+/si',
            '/площад\w*\s+квартир\w*\W+/si',
            '/площадью\s+\d+/s',
            '/продам\s+/si',
            '/продаю\s+/si',
            '/[А-ЯІЇЄ]\./s',
            '/ дом\W/s',
        ];

        $delWord = [
            'раздельная',
            'стенКирпичный',
            'свободная планировка',
            'общая площадь',
            'в новом',
            'новом доме',
            'на тихой улице',
            'в коммунальной',
            'общая площадь',
            'садовый домик',
            '- ',
            ' -',
            'новой ',
            'до метро ',
            'им. горького',
            'парк ',
            'продаётся',
            'сдаю',
            'сдам',
            'предлагаем',
            'продажа',
            'квартиры',
            'опис',
        ];
        $delWordCase = [
            'Ак.',
            'А.',
            ' А ',
        ];

        $res = preg_replace($excluded_ar, '', $text);
        $res = str_ireplace($delWord,' ',$res);
        $res = str_replace($delWordCase,' ',$res);

        $res = str_replace(',', ', ', $res); // добавить пробел, если его нет. Если он есть - он будет удален дальше

        $res = str_replace('ул.', 'улица ', $res); // добавить пробел, если его нет. Если он есть - он будет удален дальше
        $res = str_replace('Ул.', 'улица ', $res); // добавить пробел, если его нет. Если он есть - он будет удален дальше
        $res = str_replace('Ул ', 'улица ', $res); // добавить пробел, если его нет. Если он есть - он будет удален дальше
        $res = str_replace(' ул .', ' улица ', $res);
        $res = str_replace(' ул ', ' улица ', $res);
        $res = str_replace('улицы ', 'улица ', $res);

        $res = str_replace('вул.', 'вулиця ', $res); // добавить пробел, если его нет. Если он есть - он будет удален дальше
        $res = str_replace(' вул ', 'вулиця ', $res); // добавить пробел, если его нет. Если он есть - он будет удален дальше
        $res = str_replace('Вул.', 'вулиця ', $res); // добавить пробел, если его нет. Если он есть - он будет удален дальше

        $res = str_replace(' пр ', ' проспект ', $res);
        $res = str_replace(' пр .', ' проспект ', $res);
        $res = str_replace(' пр.', ' проспект ', $res);
        $res = str_replace('пр. ', 'проспект ', $res);
        $res = str_replace('просп ', 'проспект ', $res);
        $res = str_replace('просп. ', 'проспект ', $res);
        $res = str_replace('пр-т', 'проспект ', $res);

        $res = str_replace('пер.', 'переулок ', $res);
        $res = str_replace('Переулок', 'переулок ', $res);
        $res = str_replace('Пер.', 'переулок ', $res);

        $res = str_replace('пл.', 'площадь ', $res);

        $res = str_replace('бульв.', 'бульвар ', $res);
        $res = str_replace('бул.', 'бульвар ', $res);
        $res = str_replace('б-р', 'бульвар ', $res);

        $res = str_replace('кв-л', 'квартал ', $res);

        $res = str_replace('наб ', 'набежежная ', $res);
        $res = str_replace('наб.', 'набежежная ', $res);

        $res = str_ireplace('район', 'район ', $res);
        $res = str_ireplace('р-н', 'район ', $res);

        $listTypeStreet = [
            'вулиця',
            'улица',
            'улице',
            'улицы',
            'проспект',
            'проспекту',
            'проспекте',
            'шоссе',
            'переулок',
            'переулку',
            'бульвар',
            'бульвару',
            'бульваре',
            'площадь',
            'площади',
            'сквер',
            'сквера',
            'тупик',
            'тупік',
            'проезд',
            'набежежная',
            'набежежной',
            'квартал',
            'спуск',
            'узвіз'
        ];

        // определяем позицию слова улица, бульвар, проспект.... по отношению к словам район. р-н
        // если первое слева а второе справа то удаляем слово после района, если наоборот - то слева
        // Например улица Печерская район Печерский - нужно удалить район Печерский
        // Например район Печерский улица Печерская  - нужно удалить Печерский район
        // разделяем строку на 2 части по слову "район"
        if(strpos($res, 'район')) {
            // Удаляем из текста район, чтобы не было ложных срабатываний, если наименование района такое же как и наименование улицы
            $resArray = explode('район', $res);
            if (preg_match('/('.implode('|', $listTypeStreet).')/s', $resArray[0], $district)) {
                // если "район" справа от наименования  улицы
                if (preg_match('/(район)\W+([А-ЯІЇЄ][А-Яа-яёіїє\'\.-]{3,30})/s', $res, $district)) {
                    $res = str_replace($district[0], '', $res);
                }
            }else {
                if (preg_match('/([А-ЯІЇЄ][А-Яа-яёіїє\'\.-]{3,30})\W+(район)/s', $res, $district)) {
                    $res = str_replace($district[0], '', $res);
                }
            }
        }
        // заменяем район (для бесплатки) - так как Заводской р-н переделывает на Заводская  ул.
        $res = preg_replace('/Район: (.*?);/', '', $res);

        // Для замены любых пробельных символов (перевод на новую строку, табуляция, пробел) можно использовать шаблон
        return preg_replace('|\s+|', ' ', $res);
    }

    private function findFixAddressOdessa_01()
    {
        $this->address = '';
        // преобразование различных интерпретация Фонтана / 10 станция Б. Фонтана / на 16 Фонтана
        // если в тексте есть слово "Фонтана"
        if(stripos($this->adv_text, 'фонтана') !== false) {
            $arrayWord = explode(' ', $this->adv_text);
            // ищем ближайшее число слева от слова "Фонтана"
            $digit = 0; // номер станции
            // определяем позицию слов, для пределения расстояния (в словах) между ними. Если оченб далено - то это не про улицу
            $num_fontan = 0; // номер позиции слова "фонтана"
            $num_digit = 0; // номер позиции цифры
            foreach ($arrayWord as $key => $word) {
                if (stripos($word, 'фонтана') !== false) {
                    $num_fontan = $key;
                    break;
                }
                if (intval($word) == 0) continue;
                $digit = intval($word);
                $num_digit = $key;
            }
            if ($digit > 0 && ($num_fontan - $num_digit) < 5) {
                $this->address = $digit . ' станция Большого Фонтана';
            }
        }
    }

    // вычленяем из текста адрес
    private function findInText()
    {
        $this->address='';

        $listTypeStreet = [
            'вулиця',
            'улица',
            'улице',
            'улицы',
            'проспект',
            'проспекту',
            'проспекте',
            'шоссе',
            'переулок',
            'переулку',
            'бульвар',
            'бульвару',
            'бульваре',
            'площадь',
            'площади',
            'сквер',
            'сквера',
            'тупик',
            'тупік',
            'проезд',
            'набежежная',
            'набежежной',
            'квартал',
            'спуск',
            'узвіз'
        ];
        //[1-9]{0,2}\s*[А-Я]+[а-яё-]{2,25} - was before. Changed due to ul. M.Malinovskogo 13,
        if(preg_match('/('.implode('|', $listTypeStreet).')\s*([1-9]{0,2}\s*[А-ЯІЇЄ]+[а-яёіїє\'-]{0,25}[ \.]{0,2}[А-ЯІЇЄа-яёіїє\'-]{0,25}[ \.,]{0,5}(дом|будинок|буд\.|д\.|д|№)?\s*[\d\/]*[А-ЯІЇЄа-яёіїє\']?)/si',$this->adv_text,$m2)){
            //$this->log('1');
            //$this->log($m2);
            $m2[2] = str_replace('.', ',', $m2[2]);
            $st = explode(',', $m2[2]);
            // если есть большая буква в тексте, значит это может быть улица. Если нет - тогда считает, что улицы нет
            $st[0] = $this->correct_address($st[0], ' ');
            if(strtolower( $st[0]) !=  $st[0] && strlen($st[0])>3) {
                $this->address = $this->correct_type_street($this->country . ', ' . $this->city . ', ' . $m2[1] . ' ' . $st[0]);
            }
        }

        if(strlen($this->address) == 0) {
            if (preg_match('/([А-ЯІЇЄ1-9][А-Яа-яёіїє\' \.-]{3,30})\W('.implode('|', $listTypeStreet).')[\D]?\s*([\d\/]*[А-ЯІЇЄа-яёіїє\']?)/s', $this->adv_text, $m2)) {//removed /U switch in preg
                //$this->log('2');
                //$this->log($m2);
                // если есть большая буква в тексте, значит это может быть улица. Если нет - тогда считает, что улицы нет
                if (strtolower($m2[1]) != $m2[1]) {
                    $this->address = $this->correct_type_street($this->country . ', ' . $this->city . ', ' . $m2[1] . ' ' . $m2[2]);
                    $mn = preg_replace('/\D/', '', $m2[3]);
                    if(!empty($mn)) $this->address .= ' д.' . $m2[3];
                }
            }
        }

        $found = array('м-о', 'мт-ро', 'ст.м', '"', 'метров');
        $repl = array('метро', 'метро', 'метро', '', '');

        $this->adv_text = str_replace($found, $repl, $this->adv_text);
        if(strlen($this->address) == 0) {
            if (preg_match('/\W(метро)\s*([1-9]{0,2}\s*[А-ЯІЇЄ]+[а-яёіїє\'-]{0,25}[ \.]{0,2}[А-ЯІЇЄа-яёіїє\'-]{0,25})/si',$this->adv_text, $m2)){
                $this->address=Trim($this->country . ', ' . $this->city . ', метро '.$m2[2]);
            }
        }
        $this->address = $this->correct_address($this->address, ' ');
    }


    public function findInStreet($streets)
    {
        $listExludeStreet = [
            'Высокая',
            'Высокий',
            'Викока',
            'Викокий',
            'Теплая',
            'Теплый',
            'Тепла',
            'Теплий',
            'Выгодная',
            'Вигідна',
            'Уютная',
            'Затишна',
        ];
        $this->address = '';
        // убираем все знаки препинания, чтобы искать наименование с пробелом (полное вхождение)
        $this->adv_text = preg_replace('/\W/', ' ', $this->adv_text).' ';

        if(is_array($streets)) {
            // делает три прохода.
            // 1- подставляем тип улицы после найденой улицы;
            // 2- подставляем тип улицы перед найденой улицей;
            // 3- НЕ подставляем тип улицы вообще
            for ($i = 0; $i < 3; $i++) {
                foreach ($streets as $str0) {
                    $str2 = explode(',', $str0);
                    $str3 = trim($str2[0]);

                    // пропускаем наименования улиц, которіе могут трактоваться по разному
                    // делаем это здесь, а не в фильтрации изначального текста, так как в тексте может быть написано улица Высокая, там оно сработает
                    if(in_array($str3, $listExludeStreet)) continue;


                    if (strlen($str3) > 0) {
                        if (($p2 = strpos($this->adv_text, $str3 . ' ')) !== false && preg_match('/' . $str3 . '\s*обл/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                            $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                            break;
                        }
                    }

                    // формирование названия улиц в формате "на Броварском проспекте" "на Тернопольский улице"
                    $sub_street = explode(' ', $str2[0]);
                    $str[0] = '';
                    $str[1] = '';
                    foreach ($sub_street as $key => $val) {
                        if (strlen($val) > 4) {
                            $sub_str = trim($val);
                            //$this->log($sub_str);
                            $ok = substr($sub_str, -2);
                            if (isset($str2[2]) AND (trim($str2[2]) == 'рус')) {
                                if ($ok == 'ая') $str[$key] = substr_replace($sub_str, 'ой', -2, 2);
                                if ($ok == 'ий') $str[$key] = substr_replace($sub_str, 'ом', -2, 2);
                                if ($ok == 'ый') $str[$key] = substr_replace($sub_str, 'ом', -2, 2);
                                if ($ok == 'ок') $str[$key] = substr_replace($sub_str, 'ке', -2, 2);
                            } else {
                                if ($ok == 'ка') $str[$key] = substr_replace($sub_str, 'кій', -2, 2);
                                if ($ok == 'ий') $str[$key] = substr_replace($sub_str, 'ому', -2, 2);
                                if ($ok == 'ка') $str[$key] = substr_replace($sub_str, 'кій', -2, 2);
                                if ($ok == 'на') $str[$key] = substr_replace($sub_str, 'ній', -2, 2);
                                if ($ok == 'ня') $str[$key] = substr_replace($sub_str, 'ній', -2, 2);
                                if ($ok == 'ва') $str[$key] = substr_replace($sub_str, 'вій', -2, 2);
                            }
                        }
                    }
                    $str3 = trim($str[0] . ' ' . $str[1]);
                    if (strlen($str3) > 0) {
                        $type_street = trim($str2[1], ' .');
                        //$this->log('0 ------ ' . $type_street . ' ' . $str3);
                        // делает три отдельных прохода.
                        // подставляем тип улицы после слова и ищем с ним, чтобы учесть тип улицы в тексте
                        if ($i == 0) {
                            if (($p2 = stripos($this->adv_text, $str3 . ' ' . $type_street)) !== false && preg_match('/' . $str3 . '\s*обл/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                                $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                                //$this->log('1 ------ ' . $this->address);
                                $this->address = $this->prepareTest($this->address);
                                break;
                            }
                        }
                        // подставляем тип улицы перед словом и ищем с ним, чтобы учесть тип улицы в тексте
                        if ($i == 1) {
                            $str3 = str_replace('(', '\\(', $str3);
                            $str3 = str_replace(')', '\\)', $str3);
                            if (preg_match('/ (' . $type_street . '[а-яёіїє\']+) ' . $str3 . '/si', $this->adv_text) != 0 && preg_match('/' . $str3 . '\s*обл/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                                $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                                //$this->log('2 ------ ' . $type_street . ' ' . $str3);
                                //$this->log('2 ------ ' . $this->address);
                                $this->address = $this->prepareTest($this->address);
                                break;
                            }
                        }
                        // ищем совпадение без типа улицы, только по зазванию
                        if ($i == 2) {
                            if (($p2 = stripos($this->adv_text, $str3)) !== false && preg_match('/' . $str3 . '\s*обл/', $this->adv_text) == 0) { //donetskaya obl not donetskaya street
                                $this->address = $this->country . ', ' .$this->city . ', ' . $str2[0] . ' ' . $str2[1];
                                //$this->log('3 ------ ' . $this->address);
                                $this->address = $this->prepareTest($this->address);
                                break;
                            }
                        }
                    }
                }
            }

            if (isset($p2) && $p2 !== false && preg_match('/' . $str2 . '[д№\.\s,]+(\d{1,3})([^м\|\/\\\]|$)/si', substr($this->adv_text, $p2), $m) && !empty($m[1])) {
                // если есть номер дома. Проверяем наличие цифр
                $mn = preg_replace('/\D/', '', $m[1]);
                if(!empty($mn)) $this->address .= ' д.' . $m[1];
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
                $this->lat = trim($geo_new[0]); // широта
                $this->lon = trim($geo_new[1]); // долгота
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
                $mark = explode(',', $el); // 0 элемент название; 1 - lat; 2- lon
                $district = trim($mark[0]);
                if (strlen($district) > 0) {
                    if (strpos($this->adv_text_origin, $district) !== false) {
                        $this->address = $district;
                        $this->lat = trim($mark[1]); // широта
                        $this->lon = trim($mark[2]); // долгота
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

        $geocoded = 1; // ID сервиса, который віполнил геолокацию

        // нашли в локальной БД - дальнейшие действия не нужны
        if ($localDB->isCoordinate()) {
            $this->lat = $localDB->lat;
            $this->lon = $localDB->lon;
            $this->downtown = false;
            // если не указан город в конструкторе, например нам нужно по полному адресу определить координаты и наименование города по результатам геолокации
            if(empty($this->city)) $this->city = $localDB->city;
            return true;
        }

        // нужно дойти до конца и сохранить в БД найденные координаты

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

        // проверка на центр города. Такое не сохраняем, так как под адресом будут не верные координаты
        foreach ($this->interested_cities as $el) {
            if (($el[1] == $this->lat) AND ($el[2] == $this->lon)) return false;
        }
        $localDB->save_address_DB($address, $this->lat, $this->lon, $this->city, $geocoded);
        return true;
    }

    // убирает короткие слова
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
        $found = array('улице', 'проспекте', 'бульваре', 'вулиці', 'переулке');
        $repl = array('улица', 'проспект', 'бульвар', 'вулиця', 'переулок');
        return str_replace($found, $repl, trim($addr, '/'));
    }
}